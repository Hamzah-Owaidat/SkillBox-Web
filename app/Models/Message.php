<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Message {
    
    /**
     * Create a new message
     */
    public static function create($conversationId, $senderId, $text, $attachmentPath = null) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO messages (conversation_id, sender_id, text, attachment_path, send_at) 
            VALUES (:conversation_id, :sender_id, :text, :attachment, NOW())
        ");
        
        $result = $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender_id' => $senderId,
            ':text' => $text,
            ':attachment' => $attachmentPath
        ]);
        
        if ($result) {
            return self::findById($db->lastInsertId());
        }
        
        return false;
    }
    
    /**
     * Find message by ID
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT m.*, u.full_name as sender_name 
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE m.id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get messages for a conversation
     */
    public static function getByConversation($conversationId, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT m.*, u.full_name as sender_name 
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = :conversation_id
            ORDER BY m.send_at ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':conversation_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Mark message as read
     */
    public static function markAsRead($messageId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE messages SET is_readed = 1 WHERE id = :id");
        return $stmt->execute([':id' => $messageId]);
    }
    
    /**
     * Mark all messages in conversation as read for a specific user
     */
    public static function markConversationAsRead($conversationId, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE messages 
            SET is_readed = 1 
            WHERE conversation_id = :conversation_id 
            AND sender_id != :user_id 
            AND is_readed = 0
        ");
        return $stmt->execute([
            ':conversation_id' => $conversationId,
            ':user_id' => $userId
        ]);
    }
    
    /**
     * Get unread message count for user
     */
    public static function getUnreadCount($userId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM messages m
            INNER JOIN conversations c ON m.conversation_id = c.id
            WHERE (c.user1_id = :user_id OR c.user2_id = :user_id)
            AND m.sender_id != :user_id
            AND m.is_readed = 0
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['count'];
    }
    
    /**
     * Delete message
     */
    public static function delete($messageId) {
        $db = Database::getConnection();
        
        // Get attachment path before deleting
        $message = self::findById($messageId);
        
        // Delete from database
        $stmt = $db->prepare("DELETE FROM messages WHERE id = :id");
        $result = $stmt->execute([':id' => $messageId]);
        
        // Delete attachment file if exists
        if ($result && $message && !empty($message['attachment_path'])) {
            $filePath = __DIR__ . '/../../public/' . $message['attachment_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return $result;
    }
}