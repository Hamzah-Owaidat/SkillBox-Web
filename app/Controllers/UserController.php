<?php
namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;
use App\Models\Role;

class UserController {

    public function index () {
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /skillbox/public/login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Fetch user info from database
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Fetch portfolios of the user
        $stmt = $db->prepare("SELECT * FROM portfolios WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        $portfolios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Include the view
        require __DIR__ . '/../../views/profile.php';
    }

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

    public function update() {
        AuthMiddleware::api();
        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        // Collect POST data
        $full_name = htmlspecialchars($_POST['full_name'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        // Validation
        if (empty($full_name) || empty($email)) {
            echo json_encode(['error' => 'Full name and email are required']);
            exit;
        }

        $db = Database::getConnection();

        // Check old password if changing password
        $passwordSql = '';
        $params = [
            ':full_name' => $full_name,
            ':email' => $email,
            ':id' => $userId
        ];

        if (!empty($new_password)) {
            if (empty($old_password)) {
                echo json_encode(['error' => 'Old password required to change password']);
                exit;
            }

            // Verify old password
            $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!password_verify($old_password, $userData['password'])) {
                echo json_encode(['error' => 'Old password is incorrect']);
                exit;
            }

            // Hash new password
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $passwordSql = ", password = :password";
            $params[':password'] = $hashedPassword;
        }

        // Update user
        $stmt = $db->prepare("
            UPDATE users 
            SET full_name = :full_name, email = :email $passwordSql
            WHERE id = :id
        ");
        $stmt->execute($params);

        echo json_encode(['success' => 'Profile updated successfully']);
    }

    public function myPortfolios() {
        AuthMiddleware::api();
        $userId = $GLOBALS['auth_user']['id'];

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM portfolios WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        $portfolios = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($portfolios);
    }

    public function deletePortfolio($id) {
        AuthMiddleware::api();
        $userId = $GLOBALS['auth_user']['id'];

        $db = Database::getConnection();
        // Only delete if status is pending
        $stmt = $db->prepare("DELETE FROM portfolios WHERE id = :id AND user_id = :user_id AND status = 'pending'");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);

        echo json_encode(['success' => 'Portfolio deleted']);
    }

    public function editPortfolio($id) {
        AuthMiddleware::api();
        $userId = $GLOBALS['auth_user']['id'];

        // Collect data
        $full_name = htmlspecialchars($_POST['full_name'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $phone = htmlspecialchars($_POST['phone'] ?? '');
        $address = htmlspecialchars($_POST['address'] ?? '');
        $linkedin = htmlspecialchars($_POST['linkedin'] ?? '');
        $role = $_POST['role'] ?? '';

        $db = Database::getConnection();

        // Only update if status is pending
        $stmt = $db->prepare("
            UPDATE portfolios
            SET full_name = :full_name, email = :email, phone = :phone, address = :address, linkedin = :linkedin, requested_role = :role
            WHERE id = :id AND user_id = :user_id AND status = 'pending'
        ");
        $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address,
            ':linkedin' => $linkedin,
            ':role' => $role,
            ':id' => $id,
            ':user_id' => $userId
        ]);

        echo json_encode(['success' => 'Portfolio updated']);
    }
}
