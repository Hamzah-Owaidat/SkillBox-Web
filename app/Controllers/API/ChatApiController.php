<?php

namespace App\Controllers\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\PusherService;
use App\Helpers\JWTHelper;

class ChatApiController
{
    /**
     * Get user ID from session or JWT
     */
    private function getUserId()
    {
        // Check session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }

        // Check Authorization: Bearer <token>
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            $token = $matches[1];

            // Validate JWT
            $decoded = JWTHelper::validate($token);

            if ($decoded && isset($decoded['data']->id)) {
                return $decoded['data']->id;
            }
        }

        return null;
    }

    /**
     * Get all conversations for current user
     */
    public function getConversations()
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $conversations = Conversation::getUserConversations($userId);

        echo json_encode([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    /**
     * Start or get conversation with a user
     */
    public function startConversation()
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $otherUserId = $data['other_user_id'] ?? null;

        if (!$otherUserId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'other_user_id is required']);
            exit;
        }

        // Prevent chatting with yourself
        if ($otherUserId == $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cannot chat with yourself']);
            exit;
        }

        // Find or create conversation
        $conversation = Conversation::findOrCreate($userId, $otherUserId);

        // Get other user info
        $otherUser = User::find($otherUserId);

        echo json_encode([
            'success' => true,
            'conversation' => $conversation,
            'other_user' => [
                'id' => $otherUser['id'],
                'full_name' => $otherUser['full_name'],
                'email' => $otherUser['email']
            ]
        ]);
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages($conversationId)
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Check access
        if (!Conversation::userHasAccess($conversationId, $userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }

        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;

        $messages = Message::getByConversation($conversationId, $limit, $offset);

        // Mark messages as read
        Message::markConversationAsRead($conversationId, $userId);

        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Send a message
     */
    /**
     * Send a message
     */
    public function sendMessage()
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Log for debugging
        error_log("=== Chat Send Message ===");
        error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'none'));
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));

        // Handle multipart/form-data or JSON
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'multipart/form-data') !== false) {
            // Form data with file
            $conversationId = $_POST['conversation_id'] ?? null;
            $text = trim($_POST['text'] ?? $_POST['message'] ?? '');
            $attachment = $_FILES['attachment'] ?? null;

            error_log("Multipart request - conversationId: $conversationId, text: $text");
            error_log("Attachment: " . ($attachment ? $attachment['name'] : 'none'));
        } else {
            // JSON data (text only)
            $input = file_get_contents('php://input');
            error_log("JSON input: $input");

            $data = json_decode($input, true);
            $conversationId = $data['conversation_id'] ?? null;
            $text = trim($data['text'] ?? $data['message'] ?? '');
            $attachment = null;
        }

        // Validate
        if (!$conversationId) {
            error_log("ERROR: Missing conversation_id");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'conversation_id is required']);
            exit;
        }

        if (empty($text) && empty($attachment)) {
            error_log("ERROR: Empty message and no attachment");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Message text or attachment required']);
            exit;
        }

        // Check access
        if (!Conversation::userHasAccess($conversationId, $userId)) {
            error_log("ERROR: Access denied for user $userId to conversation $conversationId");
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }

        // Handle attachment
        $attachmentPath = null;
        if (!empty($attachment) && $attachment['error'] === UPLOAD_ERR_OK) {
            error_log("Processing file upload...");
            $upload = $this->handleFileUpload($attachment);
            if (!$upload['success']) {
                error_log("File upload failed: " . $upload['error']);
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $upload['error']]);
                exit;
            }
            $attachmentPath = $upload['path'];
            error_log("File uploaded successfully: $attachmentPath");
        }

        // Create message
        $message = Message::create($conversationId, $userId, $text, $attachmentPath);

        if ($message) {
            error_log("Message created successfully: ID " . $message['id']);

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
            error_log("ERROR: Failed to create message");
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to send message']);
        }
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead($conversationId)
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Check access
        if (!Conversation::userHasAccess($conversationId, $userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied']);
            exit;
        }

        Message::markConversationAsRead($conversationId, $userId);

        echo json_encode(['success' => true]);
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount()
    {
        header('Content-Type: application/json');

        $userId = $this->getUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $count = Message::getUnreadCount($userId);

        echo json_encode([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * ⚡ Send message via Pusher real-time
     */
    private function sendMessageViaPusher($conversationId, $message)
    {
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

            error_log("[Chat API] Message sent via Pusher to channel: {$channel}");
        } catch (\Exception $e) {
            error_log("[Chat API] Pusher error: " . $e->getMessage());
        }
    }

    /**
     * Handle file upload
     */
    /**
     * Handle file upload
     */
    private function handleFileUpload($file)
    {
        // Log file details for debugging
        error_log("=== File Upload Debug ===");
        error_log("File name: " . $file['name']);
        error_log("File type (declared): " . $file['type']);
        error_log("File size: " . $file['size']);
        error_log("File error: " . $file['error']);
        error_log("Temp path: " . $file['tmp_name']);

        $allowedTypes = [
            'image/jpeg',
            'image/jpg',
            'image/pjpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("Upload error code: " . $file['error']);
            return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
        }

        if ($file['size'] > $maxSize) {
            error_log("File too large: " . $file['size']);
            return ['success' => false, 'error' => 'File too large (max 5MB)'];
        }

        // Check the actual MIME type from the file itself
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        error_log("Detected MIME type: " . $detectedMimeType);

        // Use detected MIME type instead of declared type
        if (!in_array($detectedMimeType, $allowedTypes)) {
            error_log("File type not allowed. Detected: $detectedMimeType, Declared: " . $file['type']);

            // If detected type is not in list, check if it's an image variant
            if (strpos($detectedMimeType, 'image/') === 0) {
                error_log("Detected as image, allowing...");
                // Allow it if it's any image type
            } else {
                return ['success' => false, 'error' => "File type not allowed: $detectedMimeType"];
            }
        }

        $uploadDir = __DIR__ . '/../../../public/uploads/chat/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Get extension from filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // If no extension, try to guess from MIME type
        if (empty($extension)) {
            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/pjpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
            ];
            $extension = $mimeToExt[$detectedMimeType] ?? 'bin';
        }

        $filename = uniqid('chat_') . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log("File uploaded successfully: $filename");
            return [
                'success' => true,
                'path' => 'uploads/chat/' . $filename
            ];
        }

        error_log("Failed to move uploaded file");
        return ['success' => false, 'error' => 'Failed to save file'];
    }
}
