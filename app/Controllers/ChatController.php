<?php
namespace App\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\PusherService;

class ChatController {
    protected $baseUrl = '/skillbox/public';
    protected $userId;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->userId = $_SESSION['user_id'] ?? null;
        
        if (!$this->userId) {
            header("Location: {$this->baseUrl}/login");
            exit;
        }
    }
    
    /**
     * Show all conversations for current user
     */
    public function index() {
        $conversations = Conversation::getUserConversations($this->userId);
        
        ob_start();
        require __DIR__ . '/../../views/chat/index.php';
        $content = ob_get_clean();
        
        $title = 'My Conversations';
        require __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Start/open conversation with a specific user
     */
    public function start($otherUserId) {
        // Prevent chatting with yourself
        if ($otherUserId == $this->userId) {
            $_SESSION['toast_message'] = 'You cannot chat with yourself.';
            $_SESSION['toast_type'] = 'warning';
            header("Location: {$this->baseUrl}/chat");
            exit;
        }
        
        // Find or create conversation
        $conversation = Conversation::findOrCreate($this->userId, $otherUserId);
        
        // Redirect to conversation view
        header("Location: {$this->baseUrl}/chat/conversation/{$conversation['id']}");
        exit;
    }
    
    /**
     * View specific conversation
     */
    public function conversation($conversationId) {
        // Check if user has access to this conversation
        if (!Conversation::userHasAccess($conversationId, $this->userId)) {
            $_SESSION['toast_message'] = 'You do not have access to this conversation.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/chat");
            exit;
        }
        
        $conversation = Conversation::findById($conversationId);
        $messages = Message::getByConversation($conversationId);
        
        // Get other user info
        $otherUserId = ($conversation['user1_id'] == $this->userId) 
            ? $conversation['user2_id'] 
            : $conversation['user1_id'];
        $otherUser = User::find($otherUserId);
        
        // Mark messages as read
        Message::markConversationAsRead($conversationId, $this->userId);
        
        ob_start();
        require __DIR__ . '/../../views/chat/conversation.php';
        $content = ob_get_clean();
        
        $title = 'Chat with ' . htmlspecialchars($otherUser['full_name']);
        require __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Send a message (AJAX)
     */
    public function sendMessage() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            exit;
        }
        
        $conversationId = $_POST['conversation_id'] ?? null;
        $text = trim($_POST['message'] ?? '');
        $attachmentPath = null;
        
        // Validate
        if (!$conversationId || (empty($text) && empty($_FILES['attachment']))) {
            echo json_encode(['success' => false, 'error' => 'Message or attachment required']);
            exit;
        }
        
        // Check access
        if (!Conversation::userHasAccess($conversationId, $this->userId)) {
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        // Handle file upload
        if (!empty($_FILES['attachment']['name'])) {
            $upload = $this->handleFileUpload($_FILES['attachment']);
            if ($upload['success']) {
                $attachmentPath = $upload['path'];
            } else {
                echo json_encode(['success' => false, 'error' => $upload['error']]);
                exit;
            }
        }
        
        // Create message
        $message = Message::create($conversationId, $this->userId, $text, $attachmentPath);
        
        if ($message) {
            // Update conversation
            $snippet = $attachmentPath ? '[Attachment]' : substr($text, 0, 50);
            Conversation::updateLastMessage($conversationId, $snippet);
            
            // Send via Pusher
            $this->sendMessageViaPusher($conversationId, $message);
            
            echo json_encode([
                'success' => true,
                'message' => $message
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
        exit;
    }
    
    /**
     * Handle file upload
     */
    private function handleFileUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 
                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large (max 5MB)'];
        }
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'File type not allowed'];
        }
        
        // Create uploads directory if not exists
        $uploadDir = __DIR__ . '/../../public/uploads/chat/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('chat_') . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'path' => 'uploads/chat/' . $filename,
                'filename' => $file['name']
            ];
        }
        
        return ['success' => false, 'error' => 'Failed to save file'];
    }
    
    /**
     * Send message via Pusher real-time
     */
    private function sendMessageViaPusher($conversationId, $message) {
        try {
            $conversation = Conversation::findById($conversationId);
            $user1Id = $conversation['user1_id'];
            $user2Id = $conversation['user2_id'];
            
            $pusher = new PusherService();
            
            // Create chat channel name (same for both users)
            $users = [$user1Id, $user2Id];
            sort($users);
            $channel = "private-chat-{$users[0]}-{$users[1]}";
            
            // Send message data
            $pusher->trigger($channel, 'chat.message', [
                'id' => $message['id'],
                'conversation_id' => $conversationId,
                'sender_id' => $message['sender_id'],
                'sender_name' => $message['sender_name'],
                'text' => $message['text'],
                'attachment_path' => $message['attachment_path'],
                'send_at' => $message['send_at'],
                'is_readed' => $message['is_readed']
            ]);
            
            error_log("[Chat] Message sent via Pusher to channel: {$channel}");
            
        } catch (\Exception $e) {
            error_log("[Chat] Pusher error: " . $e->getMessage());
        }
    }
    
    /**
     * Get messages for conversation (AJAX)
     */
    public function getMessages($conversationId) {
        header('Content-Type: application/json');
        
        if (!Conversation::userHasAccess($conversationId, $this->userId)) {
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        $messages = Message::getByConversation($conversationId);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
        exit;
    }
    
    /**
     * Mark messages as read (AJAX)
     */
    public function markAsRead($conversationId) {
        header('Content-Type: application/json');
        
        if (!Conversation::userHasAccess($conversationId, $this->userId)) {
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }
        
        Message::markConversationAsRead($conversationId, $this->userId);
        
        echo json_encode(['success' => true]);
        exit;
    }
}