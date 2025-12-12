<?php
namespace App\Controllers;

use App\Models\Notification;

class NotificationsController {
    public function index() {
        \App\Core\AuthMiddleware::web();
        $userId = $_SESSION['user_id'] ?? null;

        $notifications = Notification::getByUserId($userId, 100); // fetch all user notifications

        include __DIR__ . '/../../views/notifications.php';
    }
}
