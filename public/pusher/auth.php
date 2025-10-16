<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Services\PusherService;

session_start();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$channelName = $_POST['channel_name'] ?? '';
$socketId = $_POST['socket_id'] ?? '';

$pusher = new PusherService();

// Authorize the private channel
$auth = $pusher->authorizeChannel($channelName, $socketId);
header('Content-Type: application/json');
echo json_encode($auth);
