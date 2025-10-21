<?php
namespace App\Helpers;

use App\Models\Notification;
use App\Services\PusherService;
use App\Core\Database;
use App\Models\Role;

class NotificationHelper {

    public static function send($senderId, $receiverIds, $title, $message, $type = 'info', $realtime = true, $broadcastPublic = false) {
        if (!is_array($receiverIds)) {
            $receiverIds = [$receiverIds];
        }

        $notificationData = [
            'sender_id' => $senderId,
            'title' => $title,
            'message' => $message,
            'type' => $type
        ];

        $dbSuccess = Notification::createBulk($notificationData, $receiverIds);

        if ($realtime && $dbSuccess) {
            try {
                $pusher = new PusherService();
                $pusherData = [
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'sender_id' => $senderId,
                    'is_read' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Send to individual users
                $pusher->sendToMultipleUsers($receiverIds, $pusherData);

                // Optional: broadcast publicly
                if ($broadcastPublic) {
                    $pusher->broadcast('notification.received', $pusherData);
                }
            } catch (\Exception $e) {
                error_log("Failed to send real-time notification: " . $e->getMessage());
            }
        }

        return $dbSuccess;
    }


    public static function broadcast($senderId, $title, $message, $type = 'announcement', array $userIds = []) {
        if (!empty($userIds)) {
            $notificationData = [
                'sender_id' => $senderId,
                'title' => $title,
                'message' => $message,
                'type' => $type
            ];

            Notification::createBulk($notificationData, $userIds);
        }

        try {
            $pusher = new PusherService();
            $pusherData = [
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'sender_id' => $senderId,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            return $pusher->broadcast('notification.received', $pusherData);
        } catch (\Exception $e) {
            error_log("Failed to broadcast notification: " . $e->getMessage());
            return false;
        }
    }

    private static function db() {
        return Database::getConnection();
    }

    private static function getUsersByRoleId($roleId) {
        try {
            $sql = "SELECT id FROM users WHERE role_id = :role_id";
            $stmt = self::db()->prepare($sql);
            $stmt->execute([':role_id' => $roleId]);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_column($users, 'id');
        } catch (\Exception $e) {
            error_log("Error fetching users by role_id: " . $e->getMessage());
            return [];
        }
    }

    public static function getAllClientIds() {
        // Get the role record for 'client'
        $role = Role::findByName('client');
    
        // If role not found, return empty array
        if (!$role) {
            return [];
        }
    
        // Use the role id dynamically
        return self::getUsersByRoleId($role['id']);
    }

    public static function getAllAdminIds() {
        // Get the role record for 'admin'
        $role = Role::findByName('admin');
    
        // If role not found, return empty array
        if (!$role) {
            return [];
        }
    
        // Use the role id dynamically
        return self::getUsersByRoleId($role['id']);
    }

    public static function getAllActiveUserIds() {
        try {
            $sql = "SELECT id FROM users WHERE is_active = 1";
            $stmt = self::db()->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_column($users, 'id');
        } catch (\Exception $e) {
            error_log("Error fetching active users: " . $e->getMessage());
            return [];
        }
    }

    public static function getUserIdsByRole($roleName) {
        try {
            $sql = "SELECT u.id 
                    FROM users u
                    INNER JOIN roles r ON u.role_id = r.id
                    WHERE r.name = :role_name";
            $stmt = self::db()->prepare($sql);
            $stmt->execute([':role_name' => $roleName]);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return array_column($users, 'id');
        } catch (\Exception $e) {
            error_log("Error fetching users by role name: " . $e->getMessage());
            return [];
        }
    }
}
