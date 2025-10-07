<?php
// Enable CORS for API testing
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Start session for web auth
if (session_status() === PHP_SESSION_NONE) session_start();

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

$AuthController = new \App\Controllers\AuthController();
$UserController = new \App\Controllers\UserController();
$HomeController = new \App\Controllers\HomeController();

// ------------------ ROUTER ------------------

// Get the full request URI and remove query string
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');

// Detect if request is JSON (API/mobile)
$inputIsJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

// ------------------ STRIP BASE PATH ------------------
// Adjust this to your project folder inside www
$basePath = '/skillbox/public'; 
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
if ($requestUri === '') $requestUri = '/';

// ------------------ ROUTES ------------------

// Register (web + mobile)
if (($requestUri === '/register' || $requestUri === '/api/register') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $AuthController->register($inputIsJson);
    exit;
}

if ($requestUri === '/register' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $AuthController->showRegisterForm();
    exit;
}

// Login
if (($requestUri === '/login' || $requestUri === '/api/login') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($inputIsJson) {
        $AuthController->loginApi();
    } else {
        $AuthController->loginWeb();
    }
    exit;
}

if ($requestUri === '/login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $AuthController->showLoginForm();
    exit;
}

// Get current user (API only)
if ($requestUri === '/api/me' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $UserController->me();
    exit;
}

// Web logout
if ($requestUri === '/logout' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $AuthController->logoutWeb();
    exit;
}

if ($requestUri === '/' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $HomeController->index();
    exit;
}

// Default response
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
