<?php
namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;
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
    // Show profile page
    public function index() {
        // Ensure web session auth
        AuthMiddleware::web();
        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        // Fetch portfolios
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM portfolios WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        $portfolios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Load view
        require __DIR__ . '/../../views/profile.php';
    }

    // Update profile
    public function update() {
        // Web session auth
        AuthMiddleware::web();
        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        if (session_status() === PHP_SESSION_NONE) session_start();

        // Collect POST data
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

        $db = Database::getConnection();

        // Prepare update
        $params = [
            ':full_name' => $full_name,
            ':email' => $email,
            ':id' => $userId
        ];

        $passwordSql = '';
        if (!empty($new_password)) {
            if (empty($old_password)) {
                $_SESSION['toast_message'] = 'Old password required to change password';
                $_SESSION['toast_type'] = 'danger';
                header('Location: /skillbox/public/profile');
                exit;
            }

            // Verify old password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!password_verify($old_password, $userData['password'])) {
                $_SESSION['toast_message'] = 'Old password is incorrect';
                $_SESSION['toast_type'] = 'danger';
                header('Location: /skillbox/public/profile');
                exit;
            }

            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $passwordSql = ", password = :password";
            $params[':password'] = $hashedPassword;
        }

        // Execute update
        $stmt = $db->prepare("
            UPDATE users
            SET full_name = :full_name, email = :email $passwordSql
            WHERE id = :id
        ");
        $stmt->execute($params);

        $_SESSION['toast_message'] = 'Profile updated successfully';
        $_SESSION['toast_type'] = 'success';
        header('Location: /skillbox/public/profile');
        exit;
    }

    

}
