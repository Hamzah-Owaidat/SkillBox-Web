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

        if ($isApi) {
            echo json_encode([
                'message' => 'Registration successful! Please login.'
            ]);
            return;
        } else {
            $_SESSION['toast_message'] = 'Registration successful! Please login.';
            $_SESSION['toast_type'] = 'success';
            header("Location: {$this->baseUrl}/login");
        }
    }

    public function registerApi() {
        // Ensure JSON input
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        $errors = [];

        if (empty($data['full_name'])) {
            $errors['full_name'] = 'Full name is required';
        } elseif (strlen($data['full_name']) < 3) {
            $errors['full_name'] = 'Full name must be at least 3 characters';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (User::findByEmail($data['email'])) {
            $errors['email'] = 'Email already used';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/', $data['password'])) {
            $errors['password'] = 'Password must have 1 uppercase, 1 special char, and be at least 8 chars';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        $role = \App\Models\Role::findByName('client');
        if (!$role) {
            http_response_code(500);
            echo json_encode(['error' => 'Default role not found']);
            return;
        }

        $hashed = password_hash($data['password'], PASSWORD_BCRYPT);

        User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => $hashed,
            'role_id' => $role['id']
        ]);

        echo json_encode([
            'message' => 'Registration successful! Please login.'
        ]);
    }

    // API login -> return JWT
    public function loginApi()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $user = User::findByEmail($data['email'] ?? '');

        if (!$user || !password_verify($data['password'] ?? '', $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        // 🚫 Check if user is inactive
        if (isset($user['status']) && $user['status'] === 'inactive') {
            http_response_code(403);
            echo json_encode(['error' => 'Your account has been banned.']);
            return;
        }

        // ✅ Use Role model to get role name
        $role = null;
        if (!empty($user['role_id'])) {
            $roleData = \App\Models\Role::findById($user['role_id']);
            $role = $roleData['name'] ?? 'Unknown';
        }

        // ✅ Generate JWT
        $token = JWTHelper::generate([
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $role,
        ]);

        // ✅ Send response
        echo json_encode([
            'token' => $token,
            'expires_in' => (int)($_ENV['JWT_EXPIRES_IN'] ?? 3600),
            'user' => [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $role,
            ]
        ]);
    }

    // Web login -> session
    public function loginWeb()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['old_email'] = $email;
            $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
            $redirectParam = !empty($redirect) ? '?redirect=' . urlencode($redirect) : '';
            header("Location: {$this->baseUrl}/login{$redirectParam}");
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['toast_message'] = 'Invalid credentials';
            $_SESSION['toast_type'] = 'danger';
            $_SESSION['old_email'] = $email;
            $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
            $redirectParam = !empty($redirect) ? '?redirect=' . urlencode($redirect) : '';
            header("Location: {$this->baseUrl}/login{$redirectParam}");
            exit;
        }

        // 🚫 Check if user is inactive (banned)
        if (isset($user['status']) && $user['status'] === 'inactive') {
            $_SESSION['toast_message'] = 'Your account has been banned. Please contact support.';
            $_SESSION['toast_type'] = 'danger';
            $_SESSION['old_email'] = $email;
            $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
            $redirectParam = !empty($redirect) ? '?redirect=' . urlencode($redirect) : '';
            header("Location: {$this->baseUrl}/login{$redirectParam}");
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

        // Handle redirect after login
        $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? null;
        if ($redirect) {
            // Sanitize and validate redirect URL
            $redirect = urldecode($redirect);
            // Only allow internal redirects (must start with baseUrl)
            if (strpos($redirect, $this->baseUrl) === 0 || strpos($redirect, '/') === 0) {
                header("Location: {$redirect}");
                exit;
            }
        }

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
