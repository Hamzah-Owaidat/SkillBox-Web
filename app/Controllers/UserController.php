<?php
namespace App\Controllers;
use App\Core\AuthMiddleware;

class UserController {
    public function me() {
        // For API call, enforce JWT auth
        AuthMiddleware::api();
        $user = $GLOBALS['auth_user'];
        echo json_encode([
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name']
        ]);
    }
}
