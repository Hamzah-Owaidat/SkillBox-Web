<?php
// app/Core/RoleMiddleware.php
namespace App\Core;

use App\Models\Role;

class RoleMiddleware {
    /**
     * Check if the authenticated user has admin role
     * Must be called after AuthMiddleware::web()
     */
    public static function admin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Check if user is authenticated
        if (empty($_SESSION['user_id'])) {
            $baseUrl = '/skillbox/public';
            header("Location: {$baseUrl}/login");
            exit;
        }
        
        // Check if user has admin role
        $userRole = $_SESSION['role'] ?? null;
        
        if ($userRole !== 'admin') {
            // User is logged in but not admin - show unauthorized page
            http_response_code(403);
            require __DIR__ . '/../../views/errors/unauthorized.php';
            exit;
        }
        
        // User is admin, continue
    }
}

