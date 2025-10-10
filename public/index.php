<?php
// Enable CORS for API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


// Start session for web auth
if (session_status() === PHP_SESSION_NONE) session_start();

// Autoload
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Instantiate router (simple router class or your own)
$router = new \App\Core\Router();

// Load routes
require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/api.php';

// Dispatch request
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path
$basePath = '/skillbox/public';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
if ($requestUri === '') $requestUri = '/';

// Dispatch via router
if (!$router->dispatch($requestUri, $method)) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}
