<?php
namespace App\Controllers\Api;

use Pusher\Pusher;

class PusherAuthController {
    
    public function authenticate() {
        
        header('Content-Type: application/json');
        
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $socketId = $_POST['socket_id'] ?? null;
        $channelName = $_POST['channel_name'] ?? null;
        
        // Validate required parameters
        if (!$socketId || !$channelName) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing socket_id or channel_name']);
            exit;
        }
        
        // Verify the user is authorized for this channel
        $expectedChannel = "private-user-{$userId}";
        
        if ($channelName !== $expectedChannel) {
            http_response_code(403);
            echo json_encode(['error' => 'Not authorized for this channel']);
            error_log("❌ Pusher Auth Failed: User {$userId} tried to access {$channelName}");
            exit;
        }
        
        try {
            // Load Pusher config
            $config = require __DIR__ . '/../../../config/pusher.php';
            
            // Initialize Pusher
            $pusher = new Pusher(
                $config['key'],
                $config['secret'],
                $config['app_id'],
                [
                    'cluster' => $config['cluster'],
                    'useTLS' => $config['use_tls'] ?? true,
                ]
            );
            
            // Authorize the channel
            $auth = $pusher->authorizeChannel($channelName, $socketId);
            
            // Log success
            if ($config['debug'] ?? false) {
                error_log("✅ Pusher Auth Success: User {$userId} authorized for {$channelName}");
            }
            
            // Return authentication signature
            echo $auth;
            
        } catch (\Exception $e) {
            error_log("❌ Pusher Auth Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Authentication failed']);
        }
    }
}