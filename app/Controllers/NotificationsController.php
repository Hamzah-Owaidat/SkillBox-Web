<?php
namespace App\Controllers;

use App\Models\Notification;

class NotificationsController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /skillbox/public/login');
            exit;
        }

        $notifications = Notification::getByUserId($userId, 100); // fetch all user notifications

        include __DIR__ . '/../../views/notifications.php';
    }
}
