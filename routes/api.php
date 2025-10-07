<?php
use App\Controllers\AuthController;
use App\Controllers\UserController;

$router->post('/api/register', [AuthController::class, 'register']);
$router->post('/api/login', [AuthController::class, 'loginApi']);
$router->get('/api/me', [UserController::class, 'me']); // protect in controller with AuthMiddleware::api()
