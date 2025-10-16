<?php
namespace App\Services;

use Pusher\Pusher;
use Exception;

class PusherService {
    private $pusher;
    private $debug;

    public function __construct() {
        $config = require __DIR__ . '/../../config/pusher.php';
        $this->debug = $config['debug'] ?? false;

        try {
            $this->pusher = new Pusher(
                $config['key'],
                $config['secret'],
                $config['app_id'],
                [
                    'cluster' => $config['cluster'],
                    'useTLS' => $config['use_tls'] ?? true,
                    'timeout' => $config['options']['timeout'] ?? 5,
                ]
            );
            if ($this->debug) {
                error_log("[Pusher] Initialized successfully with cluster: {$config['cluster']}");
            }
        } catch (Exception $e) {
            error_log("[Pusher] Initialization failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function trigger($channel, $event, $data) {
        try {
            $this->pusher->trigger($channel, $event, $data);
            if ($this->debug) error_log("[Pusher] Event '{$event}' sent to '{$channel}'");
            return true;
        } catch (Exception $e) {
            error_log("[Pusher] Trigger error: " . $e->getMessage());
            return false;
        }
    }

    /** Send to multiple users (each has their own channel) */
    public function sendToMultipleUsers(array $userIds, array $data) {
        foreach ($userIds as $userId) {
            $channel = "private-user-{$userId}";
            $this->trigger($channel, 'notification.received', $data);
        }
        return true;
    }

    /** Broadcast globally to all users */
    public function broadcast($event, $data) {
        return $this->trigger('notifications', $event, $data);
    }

    public function authorizeChannel($channelName, $socketId) {
        return $this->pusher->authorizeChannel($channelName, $socketId);
    }
}
