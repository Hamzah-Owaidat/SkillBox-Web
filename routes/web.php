<?php
use App\Controllers\AuthController;

$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->get('/register', [AuthController::class, 'showRegisterForm']); // ✅ new route
$router->post('/register', [AuthController::class, 'register']);
$router->post('/login', [AuthController::class, 'loginWeb']);
$router->post('/logout', [AuthController::class, 'logoutWeb']);
