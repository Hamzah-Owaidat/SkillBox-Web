<?php
use App\Controllers\AuthController;
use App\Controllers\Dashboard\DashboardController;
use App\Controllers\Dashboard\PortfoliosController;
use App\Controllers\Dashboard\RoleController;
use App\Controllers\Dashboard\UsersController;
use App\Controllers\Dashboard\ServicesController;
use App\Controllers\HomeController;
use App\Controllers\PortfolioController;
use App\Controllers\ServiceController;
use App\Controllers\UserController;
use App\Controllers\ChatController;
use App\Controllers\NotificationsController;

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
$router->get('/services', [ServiceController::class, 'index']);
$router->get('/services/{id}', [ServiceController::class, 'show']);

// Notifications 
$router->get('/notifications', [NotificationsController::class, 'index']);

// Chats
$router->get('/chat', [ChatController::class, 'index']);
$router->get('/chat/start/{id}', [ChatController::class, 'start']);
$router->get('/chat/conversation/{id}', [ChatController::class, 'conversation']);
$router->post('/chat/send', [ChatController::class, 'sendMessage']);
$router->get('/chat/messages/{id}', [ChatController::class, 'getMessages']);
$router->post('/chat/mark-read/{id}', [ChatController::class, 'markAsRead']);



// =================== START DASHBOARD ROUTES ===================
// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/activities/export', [DashboardController::class, 'export']);



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

// Dashboard Portfolios
$router->get('/dashboard/portfolios', [PortfoliosController::class, 'index']);
$router->get('/dashboard/portfolios/export', [PortfoliosController::class, 'export']);
$router->patch('/dashboard/portfolios/{id}/accept', [PortfoliosController::class, 'accept']);
$router->patch('/dashboard/portfolios/{id}/reject', [PortfoliosController::class, 'reject']);
$router->delete('/dashboard/portfolios/{id}', [PortfoliosController::class, 'delete']);

// Dashboard Services
$router->get('/dashboard/services', [ServicesController::class, 'index']);
$router->post('/dashboard/services/store', [ServicesController::class, 'store']);
$router->patch('/dashboard/services/{id}', [ServicesController::class, 'update']);
$router->delete('/dashboard/services/{id}', [ServicesController::class, 'delete']);
$router->get('/dashboard/services/export', [ServicesController::class, 'export']);