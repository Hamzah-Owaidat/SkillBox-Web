<?php
use App\Controllers\AuthController;
use App\Controllers\Dashboard\DashboardController;
use App\Controllers\Dashboard\RoleController;
use App\Controllers\Dashboard\UsersController;
use App\Controllers\HomeController;
use App\Controllers\PortfolioController;
use App\Controllers\ServicesController;
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
$router->delete('/portfolio/delete/{id}', [PortfolioController::class, 'deletePortfolio']);
$router->get('/portfolio/edit/{id}', [PortfolioController::class, 'showEditForm']); // NEW: Show edit form
$router->post('/portfolio/update/{id}', [PortfolioController::class, 'updatePortfolio']); // NEW: Update portfolio

// Services
$router->get('/services', [ServicesController::class, 'index']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Dashboard Users
$router->get('/dashboard/users', [UsersController::class, 'index']);
$router->post('/dashboard/users/store', [UsersController::class, 'store']);
$router->patch('/dashboard/users/{id}', [UsersController::class, 'update']);
$router->patch('/dashboard/users/{id}/toggle-status', [UsersController::class, 'toggleStatus']);
$router->delete('/dashboard/users/{id}', [UsersController::class, 'delete']);
$router->get('/dashboard/users/export', [UsersController::class, 'export']);


// Dashboard Role
$router->get('/dashboard/roles', [RoleController::class, 'index']);
$router->post('/dashboard/roles/store', [RoleController::class, 'store']);
$router->patch('/dashboard/roles/{id}', [RoleController::class, 'update']);
$router->delete('/dashboard/roles/{id}', [RoleController::class, 'delete']);
$router->get('/dashboard/roles/export', [RoleController::class, 'export']);