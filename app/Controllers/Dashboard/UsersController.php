<?php
namespace App\Controllers\Dashboard;

use App\Models\User;
use App\Models\Role;

class UsersController {
    protected $baseUrl = '/skillbox/public';
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;

        $pagination = User::paginate($limit, $page);
        $users = $pagination['data'];
        $roles = Role::getAll(); // ✅ Get all roles for the dropdown

        ob_start();
        require __DIR__ . '/../../../views/dashboard/users.php';
    }

    // ✅ Create new user
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 2);

        // Validation
        if (empty($fullName) || empty($email) || empty($password)) {
            $_SESSION['toast_message'] = 'All fields are required.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast_message'] = 'Invalid email format.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['toast_message'] = 'Password must be at least 6 characters.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        // Check if email exists
        if (User::findByEmail($email)) {
            $_SESSION['toast_message'] = 'Email already exists.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        // Verify role exists
        if (!Role::findById($roleId)) {
            $_SESSION['toast_message'] = 'Invalid role selected.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        // Create user
        $userId = User::create([
            'full_name' => $fullName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role_id' => $roleId
        ]);

        if ($userId) {
            $_SESSION['toast_message'] = 'User created successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to create user.';
            $_SESSION['toast_type'] = 'error';
        }

        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }

    // ✅ Update user
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 2);

        // Validation
        if (empty($fullName) || empty($email)) {
            $_SESSION['toast_message'] = 'Full name and email are required.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast_message'] = 'Invalid email format.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        // Check if email exists for another user
        $existingUser = User::findByEmail($email);
        if ($existingUser && $existingUser['id'] != $id) {
            $_SESSION['toast_message'] = 'Email already exists.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        // Verify role exists
        if (!Role::findById($roleId)) {
            $_SESSION['toast_message'] = 'Invalid role selected.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        // Prepare update data
        $updateData = [
            'full_name' => $fullName,
            'email' => $email,
            'role_id' => $roleId
        ];

        // Only update password if provided and valid
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['toast_message'] = 'Password must be at least 6 characters.';
                $_SESSION['toast_type'] = 'error';
                header("Location: {$this->baseUrl}/dashboard/users");
                exit;
            }
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        // Update user
        if (User::update($id, $updateData)) {
            $_SESSION['toast_message'] = 'User updated successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to update user.';
            $_SESSION['toast_type'] = 'error';
        }

        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }

    // ✅ Toggle user status (active/inactive)
    public function toggleStatus($id) {
        // Prevent deactivating yourself
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['toast_message'] = 'You cannot deactivate your own account.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        if (User::toggleStatus($id)) {
            $_SESSION['toast_message'] = 'User status updated successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to update user status.';
            $_SESSION['toast_type'] = 'error';
        }
        
        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }

    // ✅ Delete user
    public function delete($id) {
        // Prevent deleting yourself
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['toast_message'] = 'You cannot delete your own account.';
            $_SESSION['toast_type'] = 'error';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        if (User::delete($id)) {
            $_SESSION['toast_message'] = 'User deleted successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to delete user.';
            $_SESSION['toast_type'] = 'error';
        }
        
        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }
}