<?php
use App\Controllers\Api\NotificationApiController;
use App\Controllers\Api\PusherAuthController;
use App\Controllers\AuthController;
use App\Controllers\UserController;

$router->post('/api/register', [AuthController::class, 'registerApi']);
$router->post('/api/login', [AuthController::class, 'loginApi']);
$router->get('/api/me', [UserController::class, 'me']); // protect in controller with AuthMiddleware::api()

$router->post('/pusher/auth', [PusherAuthController::class, 'authenticate']);

// Get list of notifications (supports ?limit= & ?unread_only=true)
$router->get('/api/notifications', [NotificationApiController::class, 'index']);

// Get unread count
$router->get('/api/notifications/unread-count', [NotificationApiController::class, 'unreadCount']);

// Mark specific notification as read
$router->post('/api/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);

// Mark all user notifications as read
$router->post('/api/notifications/mark-all-read', [NotificationApiController::class, 'markAllAsRead']);

// Delete a notification
$router->delete('/api/notifications/{id}', [NotificationApiController::class, 'delete']);

// Send a notification to another user (via Pusher + DB)
$router->post('/api/notifications/send', [NotificationApiController::class, 'sendNotification']);
