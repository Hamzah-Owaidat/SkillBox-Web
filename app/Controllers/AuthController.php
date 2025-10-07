<?php
// app/Controllers/AuthController.php
namespace App\Controllers;
use App\Models\User;
use App\Helpers\JWTHelper;

class AuthController {

    protected $baseUrl = '/skillbox/public';

    public function register($isApi = false) {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $data = $isApi ? json_decode(file_get_contents('php://input'), true) : $_POST;

        $errors = [];

        // Full Name: required, min 3 chars
        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Full name is required';
        } elseif (strlen($data['full_name']) < 3) {
            $errors['full_name'] = 'Full name must be at least 3 characters';
        }

        // Email: required, valid email format
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (User::findByEmail($data['email'])) {
            $errors['email'] = 'Email already used';
        }

        // Password: required, min 8 chars, 1 uppercase, 1 special char
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/', $data['password'])) {
            $errors['password'] = 'Password must have 1 uppercase, 1 special char, and be at least 8 chars';
        }

        if (!empty($errors)) {
            // For API
            if ($isApi) {
                http_response_code(422);
                echo json_encode(['errors' => $errors]);
                return;
            } else {
                // Save errors and old input in session
                $_SESSION['form_errors'] = $errors;
                $_SESSION['old_input'] = $data;
                header("Location: {$this->baseUrl}/register");
                return;
            }
        }

        // Default role
        $role = \App\Models\Role::findByName('client');
        if (!$role) {
            $_SESSION['toast_message'] = 'Server error: default role not found';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/register");
            return;
        }

        $hashed = password_hash($data['password'], PASSWORD_BCRYPT);

        User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => $hashed,
            'role_id' => $role['id']
        ]);

        $_SESSION['toast_message'] = 'Registration successful! Please login.';
        $_SESSION['toast_type'] = 'success';
        header("Location: {$this->baseUrl}/login");
    }



    // API login -> return JWT
    public function loginApi() {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = User::findByEmail($data['email'] ?? '');
        if (!$user || !password_verify($data['password'] ?? '', $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        $token = JWTHelper::generate($user);
        echo json_encode([
            'token' => $token,
            'expires_in' => (int)(getenv('JWT_EXPIRES') ?: 3600),
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
    }

    // Web login -> session
    public function loginWeb() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = [];

        // Field-level validation
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        // If there are validation errors, save in session and redirect
        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['old_email'] = $email; // preserve email input
            header("Location: {$this->baseUrl}/login");
            exit;
        }

        // Authenticate
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['toast_message'] = 'Invalid credentials';
            $_SESSION['toast_type'] = 'danger';
            $_SESSION['old_email'] = $email;
            header("Location: {$this->baseUrl}/login");
            exit;
        }

        $roleData = \App\Models\Role::findById($user['role_id']);
        $roleName = $roleData['name'] ?? 'client';

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $roleName;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $_SESSION['toast_message'] = 'Welcome back, ' . $user['full_name'] . '!';
        $_SESSION['toast_type'] = 'success';

        header("Location: {$this->baseUrl}/");
        exit;
    }

    public function logoutWeb() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
        header("Location: {$this->baseUrl}/login");
    }

    public function showLoginForm() {
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function showRegisterForm() {
        require __DIR__ . '/../../views/auth/register.php';
    }
}
