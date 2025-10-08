<?php
namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Models\Role;

class UserController {
    public function me() {
        // Enforce JWT auth for API
        AuthMiddleware::api();

        $user = $GLOBALS['auth_user'];

        // Get role name using Role model
        $roleName = null;
        if (!empty($user['role_id'])) {
            $roleData = Role::findById($user['role_id']);
            $roleName = $roleData['name'] ?? 'Unknown';
        }

        echo json_encode([
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $roleName
        ]);
    }
}
