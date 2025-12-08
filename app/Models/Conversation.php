<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Conversation {
    
    /**
     * Find or create conversation between two users
     */
    public static function findOrCreate($user1Id, $user2Id) {
        $db = Database::getConnection();
        
        // Ensure user1_id is always smaller (for consistency)
        $users = [$user1Id, $user2Id];
        sort($users);
        
        // Try to find existing conversation
        $stmt = $db->prepare("
            SELECT * FROM conversations 
            WHERE (user1_id = :user1 AND user2_id = :user2) 
               OR (user1_id = :user2 AND user2_id = :user1)
            LIMIT 1
        ");
        $stmt->execute([':user1' => $users[0], ':user2' => $users[1]]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conversation) {
            return $conversation;
        }
        
        // Create new conversation
        $stmt = $db->prepare("
            INSERT INTO conversations (user1_id, user2_id, updated_at) 
            VALUES (:user1, :user2, NOW())
        ");
        $stmt->execute([':user1' => $users[0], ':user2' => $users[1]]);
        
        return self::findById($db->lastInsertId());
    }
    
    /**
     * Find conversation by ID
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM conversations WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all conversations for a user with other user info
     */
    public static function getUserConversations($userId) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT 
                c.*,
                COALESCE(c.last_message_snippet, 'No messages yet') as last_message_snippet,
                CASE 
                    WHEN c.user1_id = :user_id THEN u2.full_name
                    ELSE u1.full_name
                END as other_user_name,
                CASE 
                    WHEN c.user1_id = :user_id THEN c.user2_id
                    ELSE c.user1_id
                END as other_user_id,
                CASE 
                    WHEN c.user1_id = :user_id THEN u2.email
                    ELSE u1.email
                END as other_user_email,
                (SELECT COUNT(*) FROM messages 
                 WHERE conversation_id = c.id 
                 AND sender_id != :user_id 
                 AND is_readed = 0) as unread_count
            FROM conversations c
            LEFT JOIN users u1 ON c.user1_id = u1.id
            LEFT JOIN users u2 ON c.user2_id = u2.id
            WHERE c.user1_id = :user_id OR c.user2_id = :user_id
            ORDER BY c.updated_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update conversation timestamp and last message
     */
    public static function updateLastMessage($conversationId, $messageSnippet) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE conversations 
            SET last_message_snippet = :snippet, 
                updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':snippet' => $messageSnippet,
            ':id' => $conversationId
        ]);
    }
    
    /**
     * Check if user is part of conversation
     */
    public static function userHasAccess($conversationId, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as count FROM conversations 
            WHERE id = :id 
            AND (user1_id = :user_id OR user2_id = :user_id)
        ");
        $stmt->execute([':id' => $conversationId, ':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    /**
     * Delete conversation and all messages
     */
    public static function delete($conversationId) {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();
            
            // Delete messages first
            $stmt = $db->prepare("DELETE FROM messages WHERE conversation_id = :id");
            $stmt->execute([':id' => $conversationId]);
            
            // Delete conversation
            $stmt = $db->prepare("DELETE FROM conversations WHERE id = :id");
            $stmt->execute([':id' => $conversationId]);
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error deleting conversation: " . $e->getMessage());
            return false;
        }
    }
}