<?php
namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Models\User;
use App\Models\Role;
use App\Models\Activity;

class UserController {

    /**
     * Return the authenticated user for API
     */
    public function me() {
        AuthMiddleware::api();

        $user = $GLOBALS['auth_user'];

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

    /**
     * Show profile page
     */
    public function index() {
        AuthMiddleware::web();

        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        // Fetch user portfolios via User model
        $portfolios = User::getPortfolios($userId);

        require __DIR__ . '/../../views/profile.php';
    }

    /**
     * Update profile
     */
    public function update() {
        AuthMiddleware::web();

        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        if (session_status() === PHP_SESSION_NONE) session_start();

        $full_name = htmlspecialchars($_POST['full_name'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if (empty($full_name) || empty($email)) {
            $_SESSION['toast_message'] = 'Full name and email are required';
            $_SESSION['toast_type'] = 'danger';
            header('Location: /skillbox/public/profile');
            exit;
        }

        $updateData = [
            'full_name' => $full_name,
            'email' => $email,
            'updated_by' => $userId
        ];

        // Handle password change
        if (!empty($new_password)) {
            if (empty($old_password)) {
                $_SESSION['toast_message'] = 'Old password required to change password';
                $_SESSION['toast_type'] = 'danger';
                header('Location: /skillbox/public/profile');
                exit;
            }

            // Verify old password via User model
            if (!User::verifyPassword($userId, $old_password)) {
                $_SESSION['toast_message'] = 'Old password is incorrect';
                $_SESSION['toast_type'] = 'danger';
                header('Location: /skillbox/public/profile');
                exit;
            }

            $updateData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Update user via model
        $success = User::updateProfile($userId, $updateData);

        if ($success) {
            
            $_SESSION['toast_message'] = 'Profile updated successfully';
            $_SESSION['toast_type'] = 'success';

            // Log activity
            Activity::Log(
                $userId, 
                "Updated profile",
                "User Updated his profile"
            );
        } else {
            $_SESSION['toast_message'] = 'No changes were made';
            $_SESSION['toast_type'] = 'info';
        }

        header('Location: /skillbox/public/profile');
        exit;
    }
}
