<?php

namespace App\Controllers\Api;

use Pusher\Pusher;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PusherAuthController
{
    public function authenticate()
    {
        header('Content-Type: application/json');

        // ✅ Get JWT from Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing or invalid token']);
            exit;
        }

        $jwt = $matches[1];

        // ✅ Decode JWT
        try {
            $secretKey = $_ENV['JWT_SECRET'] ?? 'default_secret_key';
            $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

            $userId = $decoded->data->id ?? null;
            if (!$userId) {
                throw new \Exception('Invalid token payload');
            }
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized', 'message' => $e->getMessage()]);
            exit;
        }

        // ✅ Read POST data - Support both JSON and form data
        $socketId = null;
        $channelName = null;

        // Try form data first (web)
        if (!empty($_POST['socket_id']) && !empty($_POST['channel_name'])) {
            $socketId = $_POST['socket_id'];
            $channelName = $_POST['channel_name'];
        } else {
            // Try JSON data (Flutter)
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $socketId = $data['socket_id'] ?? null;
            $channelName = $data['channel_name'] ?? null;
        }

        if (!$socketId || !$channelName) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing socket_id or channel_name']);
            exit;
        }

        // ✅ Verify user's channel permission
        $allowedUserPrefix = "private-user-{$userId}";
        $allowedChatPrefix = "private-chat-";

        if ($channelName !== $allowedUserPrefix && strpos($channelName, $allowedChatPrefix) !== 0) {
            http_response_code(403);
            echo json_encode(['error' => 'Not authorized for this channel']);
            error_log("❌ Pusher Auth Failed: User {$userId} tried to access {$channelName}");
            exit;
        }

        try {
            $config = require __DIR__ . '/../../../config/pusher.php';

            $pusher = new Pusher(
                $config['key'],
                $config['secret'],
                $config['app_id'],
                [
                    'cluster' => $config['cluster'],
                    'useTLS' => $config['use_tls'] ?? true,
                ]
            );

            // ✅ Get auth signature (returns JSON string like '{"auth":"key:signature"}')
            $authData = $pusher->authorizeChannel($channelName, $socketId);

            // ✅ Decode to extract just the auth string
            $authDecoded = json_decode($authData, true);

            // ✅ Return the proper format
            echo json_encode([
                'auth' => $authDecoded['auth'],
                'shared_secret' => base64_encode(random_bytes(16))
            ]);
        } catch (\Exception $e) {
            error_log("❌ Pusher Auth Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Authentication failed', 'message' => $e->getMessage()]);
        }
    }
}
