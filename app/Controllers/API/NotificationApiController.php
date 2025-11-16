<?php

namespace App\Controllers\Api;

use App\Models\Notification;
use App\Services\PusherService;
use App\Helpers\JWTHelper;

class NotificationApiController
{
    protected $userId;
    protected $pusher;
    protected $isMobileRequest = false;

    public function __construct()
    {
        header('Content-Type: application/json');

        // Try JWT authentication first (for mobile)
        $this->userId = $this->authenticateRequest();

        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $this->pusher = new PusherService();
    }

    /**
     * Authenticate request - supports both JWT (mobile) and Session (web)
     */
    private function authenticateRequest()
    {
        // Check for JWT token in Authorization header (mobile)
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            $decoded = JWTHelper::validate($token);

            if ($decoded && isset($decoded['data']->id)) {
                $this->isMobileRequest = true;
                return $decoded['data']->id;
            }
        }

        // Fallback to session authentication (web)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['user_id'] ?? null;
    }

    public function index()
    {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

        try {
            $notifications = Notification::getByUserId($this->userId, $limit, $unreadOnly);

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => getenv('APP_DEBUG') ? $e->getTrace() : null
            ]);
        }
    }

    public function unreadCount()
    {
        try {
            $count = Notification::getUnreadCount($this->userId);
            echo json_encode(['success' => true, 'count' => $count]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function markAsRead($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $result = Notification::markAsRead($id);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function markAllAsRead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $result = Notification::markAllAsRead($this->userId);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to mark all notifications as read']);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        try {
            $result = Notification::delete($id);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Notification deleted']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete notification']);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendNotification()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Get JSON input (works for both mobile and web)
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!isset($data['receiver_id'], $data['title'], $data['message'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            return;
        }

        try {
            $notification = Notification::create([
                'sender_id' => $this->userId,
                'receiver_id' => $data['receiver_id'],
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'] ?? 'info'
            ]);

            if ($notification) {
                // ✅ Send complete notification data via Pusher
                $pusherData = [
                    'id' => $notification['id'],                      // ✅ Added
                    'sender_id' => $notification['sender_id'],
                    'receiver_id' => $notification['receiver_id'],    // ✅ Added
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'type' => $notification['type'],
                    'is_read' => $notification['is_read'],
                    'created_at' => $notification['created_at']
                ];

                $this->pusher->sendToUser($data['receiver_id'], $pusherData);

                echo json_encode([
                    'success' => true,
                    'notification' => $notification,
                    'message' => 'Notification sent successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create notification']);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
