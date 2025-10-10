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

// Profile
$router->get('/profile', [UserController::class, 'index']);
$router->post('/user/update', [UserController::class, 'update']);

// Portfolio
$router->get('/submit-cv', [PortfolioController::class, 'index']);
$router->post('/portfolio/store', [PortfolioController::class, 'store']);
$router->post('/portfolio/delete/{id}', [PortfolioController::class, 'deletePortfolio']);
$router->get('/portfolio/edit/{id}', [PortfolioController::class, 'showEditForm']); // NEW: Show edit form
$router->post('/portfolio/update/{id}', [PortfolioController::class, 'updatePortfolio']); // NEW: Update portfolio