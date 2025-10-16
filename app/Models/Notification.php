<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Notification {
    public static function create($data) {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            INSERT INTO notifications (sender_id, receiver_id, title, message, type, is_read, created_at)
            VALUES (:sender_id, :receiver_id, :title, :message, :type, 0, NOW())
        ");

        $stmt->execute($data);

        $data['id'] = $db->lastInsertId();
        $data['is_read'] = 0;
        return $data;
    }

    /**
     * Create notifications for multiple receivers at once
     */
    public static function createBulk($data, $receiverIds) {
        if (empty($receiverIds)) return false;

        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO notifications (sender_id, receiver_id, title, message, type, is_read, created_at)
            VALUES (:sender_id, :receiver_id, :title, :message, :type, 0, NOW())
        ");

        $db->beginTransaction();
        try {
            foreach ($receiverIds as $receiverId) {
                $stmt->execute([
                    ':sender_id' => $data['sender_id'],
                    ':receiver_id' => $receiverId,
                    ':title' => $data['title'],
                    ':message' => $data['message'],
                    ':type' => $data['type']
                ]);
            }
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Notification::createBulk error: " . $e->getMessage());
            return false;
        }
    }

    public static function getByUserId($userId, $limit = 20, $unreadOnly = false) {
        $db = Database::getConnection();
        $sql = "SELECT * FROM notifications WHERE receiver_id = :user_id";
        if ($unreadOnly) $sql .= " AND is_read = 0";
        $sql .= " ORDER BY created_at DESC LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnreadCount($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE receiver_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function markAsRead($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function markAllAsRead($userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_id = :user_id");
        return $stmt->execute(['user_id' => $userId]);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM notifications WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
