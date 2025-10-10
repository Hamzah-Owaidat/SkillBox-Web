<?php
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PortfolioController;
use App\Controllers\UserController;

// Auth
$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->get('/register', [AuthController::class, 'showRegisterForm']); // ✅ new route
$router->post('/register', [AuthController::class, 'register']);
$router->post('/login', [AuthController::class, 'loginWeb']);
$router->get('/logout', [AuthController::class, 'logoutWeb']);


// Home
$router->get('/', [HomeController::class, 'index']);



// Portfolio
$router->get('/submit-cv', [PortfolioController::class, 'index']);
$router->post('/portfolio/store', [PortfolioController::class, 'store']);