<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Services\PusherService;
use App\Core\Database;
use App\Models\Role;

class NotificationHelper
{

    public static function send($senderId, $receiverIds, $title, $message, $type = 'info', $realtime = true, $broadcastPublic = false)
    {
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

                // ✅ Get the created notifications from database to include IDs
                foreach ($receiverIds as $receiverId) {
                    // Fetch the most recent notification for this receiver
                    $notifications = Notification::getByUserId($receiverId, 1, false);

                    if (!empty($notifications)) {
                        $notification = $notifications[0];

                        // ✅ Send complete notification data with ID and receiver_id
                        $pusherData = [
                            'id' => $notification['id'],                    // ✅ Added
                            'sender_id' => $notification['sender_id'],
                            'receiver_id' => $notification['receiver_id'],   // ✅ Added
                            'title' => $notification['title'],
                            'message' => $notification['message'],
                            'type' => $notification['type'],
                            'is_read' => $notification['is_read'],
                            'created_at' => $notification['created_at']
                        ];

                        $pusher->sendToUser($receiverId, $pusherData);
                    }
                }

                // Optional: broadcast publicly
                if ($broadcastPublic) {
                    $pusher->broadcast('notification.received', [
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'sender_id' => $senderId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } catch (\Exception $e) {
                error_log("Failed to send real-time notification: " . $e->getMessage());
            }
        }

        return $dbSuccess;
    }


    public static function broadcast($senderId, $title, $message, $type = 'announcement', array $userIds = [])
    {
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

    private static function db()
    {
        return Database::getConnection();
    }

    private static function getUsersByRoleId($roleId)
    {
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

    public static function getAllClientIds()
    {
        // Get the role record for 'client'
        $role = Role::findByName('client');

        // If role not found, return empty array
        if (!$role) {
            return [];
        }

        // Use the role id dynamically
        return self::getUsersByRoleId($role['id']);
    }

    public static function getAllAdminIds()
    {
        // Get the role record for 'admin'
        $role = Role::findByName('admin');

        // If role not found, return empty array
        if (!$role) {
            return [];
        }

        // Use the role id dynamically
        return self::getUsersByRoleId($role['id']);
    }

    public static function getAllActiveUserIds()
    {
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

    public static function getUserIdsByRole($roleName)
    {
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
