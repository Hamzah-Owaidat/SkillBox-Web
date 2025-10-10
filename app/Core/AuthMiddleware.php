<?php
// app/Core/AuthMiddleware.php
namespace App\Core;
use App\Helpers\JWTHelper;
use App\Models\User;

class AuthMiddleware {
    // For API endpoints
    public static function api() {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$auth || !preg_match('/Bearer\s(\S+)/', $auth, $m)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $token = $m[1];
        $payload = JWTHelper::validate($token);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }

        // attach user to request scope for controllers
        $user = User::find($payload['sub']);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        // make available globally (simple approach)
        $GLOBALS['auth_user'] = $user;
    }

    // For web (session)
    public static function web() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            // redirect to login or show error
            header('Location: /login');
            exit;
        }
        $GLOBALS['auth_user'] = User::find($_SESSION['user_id']);
    }
}
