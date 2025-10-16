<?php
namespace App\Controllers\Api;

use App\Models\Notification;
use App\Services\PusherService;

class NotificationApiController {
    protected $userId;
    protected $pusher;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->userId = $_SESSION['user_id'] ?? null;
        $this->pusher = new PusherService();

        header('Content-Type: application/json');

        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    public function index() {
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

    public function unreadCount() {
        $count = Notification::getUnreadCount($this->userId);
        echo json_encode(['success' => true, 'count' => $count]);
    }

    public function markAsRead($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $result = Notification::markAsRead($id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark notification as read']);
        }
    }

    public function markAllAsRead() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $result = Notification::markAllAsRead($this->userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark all notifications as read']);
        }
    }

    public function delete($id) {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $result = Notification::delete($id);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Notification deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete notification']);
        }
    }

    // Example of sending (triggering) a notification via Pusher
    public function sendNotification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['receiver_id'], $data['title'], $data['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $notification = Notification::create([
            'sender_id' => $this->userId,
            'receiver_id' => $data['receiver_id'],
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info'
        ]);

        if ($notification) {
            $this->pusher->trigger('notifications-' . $data['receiver_id'], 'new-notification', $notification);
            echo json_encode(['success' => true, 'notification' => $notification]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create notification']);
        }
    }
}
