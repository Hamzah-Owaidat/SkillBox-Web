<?php
namespace App\Services;

use Pusher\Pusher;
use Exception;

class PusherService {
    private $pusher;
    private $config;
    private $debug;

    public function __construct() {
        $this->config = require __DIR__ . '/../../config/pusher.php';
        $this->debug = $this->config['debug'] ?? false;

        try {
            $this->pusher = new Pusher(
                $this->config['key'],
                $this->config['secret'],
                $this->config['app_id'],
                [
                    'cluster' => $this->config['cluster'],
                    'useTLS' => $this->config['use_tls'] ?? true,
                    'timeout' => $this->config['options']['timeout'] ?? 5,
                ]
            );
            
            if ($this->debug) {
                error_log("[Pusher] ✅ Initialized with cluster: {$this->config['cluster']}");
            }
        } catch (Exception $e) {
            error_log("[Pusher] ❌ Initialization failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send notification to a single user (private channel)
     * 
     * @param int $userId User ID
     * @param array $data Notification data
     * @return bool Success status
     */
    public function sendToUser($userId, array $data) {
        try {
            $channel = "private-user-{$userId}";
            $event = 'notification.received';
            
            $this->pusher->trigger($channel, $event, $data);
            
            if ($this->debug) {
                error_log("[Pusher] 🔒 Sent to private channel: {$channel}");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("[Pusher] ❌ Error sending to user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple users (each gets their own private channel)
     * 
     * @param array $userIds Array of user IDs
     * @param array $data Notification data
     * @return bool Success status
     */
    public function sendToMultipleUsers(array $userIds, array $data) {
        if (empty($userIds)) {
            error_log("[Pusher] ⚠️ No users to send to");
            return false;
        }

        try {
            $channels = [];
            foreach ($userIds as $userId) {
                $channels[] = "private-user-{$userId}";
            }
            
            $event = 'notification.received';
            
            // Pusher supports up to 10 channels per trigger
            $batches = array_chunk($channels, 10);
            
            foreach ($batches as $batch) {
                $this->pusher->trigger($batch, $event, $data);
            }
            
            if ($this->debug) {
                $count = count($userIds);
                error_log("[Pusher] 🔒 Sent to {$count} private channels");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("[Pusher] ❌ Error sending to multiple users: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Broadcast to all users (public channel)
     * 
     * @param string $event Event name
     * @param array $data Notification data
     * @return bool Success status
     */
    public function broadcast($event, $data) {
        try {
            // Use the simple 'notifications' channel (matches frontend)
            $channel = 'notifications';
            
            $this->pusher->trigger($channel, $event, $data);
            
            if ($this->debug) {
                error_log("[Pusher] 📢 Broadcast to public channel: {$channel}, event: {$event}");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("[Pusher] ❌ Broadcast error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generic trigger method for custom channels
     * 
     * @param string $channel Channel name
     * @param string $event Event name
     * @param array $data Data to send
     * @return bool Success status
     */
    public function trigger($channel, $event, $data) {
        try {
            $this->pusher->trigger($channel, $event, $data);
            
            if ($this->debug) {
                error_log("[Pusher] ✉️ Triggered '{$event}' on '{$channel}'");
            }
            
            return true;
        } catch (Exception $e) {
            error_log("[Pusher] ❌ Trigger error on {$channel}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authorize private channel (for future use with auth endpoint)
     * 
     * @param string $channelName Channel name
     * @param string $socketId Socket ID
     * @return string Authorization signature
     */
    public function authorizeChannel($channelName, $socketId) {
        return $this->pusher->authorizeChannel($channelName, $socketId);
    }

    /**
     * Get connection info (for debugging)
     * 
     * @return array Config info (without sensitive data)
     */
    public function getInfo() {
        return [
            'app_id' => $this->config['app_id'],
            'cluster' => $this->config['cluster'],
            'debug' => $this->debug,
        ];
    }

    /**
     * Send a chat message from one user to another
     */
    public function sendMessage(int $senderId, int $receiverId, string $message) {
        $users = [$senderId, $receiverId];
        sort($users); // ensure same channel for both users

        $channel = "private-chat-{$users[0]}-{$users[1]}";
        $event = 'chat.message';

        $data = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        return $this->pusher->trigger($channel, $event, $data);
    }

}