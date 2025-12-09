<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Models\PasswordReset;
use App\Services\MailService;

class ForgotPasswordController
{
    /**
     * Send reset code to user's email
     * POST /api/forgot-password
     */
    public function sendResetCode()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');

        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $user = User::findByEmail($email);

        // Always return success (security: don't reveal if email exists)
        if (!$user) {
            echo json_encode([
                'success' => true,
                'message' => 'If an account exists with this email, a reset code has been sent.'
            ]);
            return;
        }

        // Generate 6-digit code
        $code = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Save code to database
        PasswordReset::createOrUpdate($user['id'], $code);

        // Send email
        $mailService = new MailService();
        $result = $mailService->sendPasswordResetCode($user['email'], $user['full_name'], $code);

        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Reset code has been sent to your email!',
                'user_id' => $user['id'] // Return user_id for mobile to use in next step
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to send email. Please try again later.'
            ]);
        }
    }

    /**
     * Verify reset code
     * POST /api/verify-reset-code
     */
    public function verifyResetCode()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim($input['code'] ?? '');
        $userId = (int)($input['user_id'] ?? 0);

        $errors = [];

        if (empty($code)) {
            $errors['code'] = 'Code is required';
        } elseif (!preg_match('/^\d{6}$/', $code)) {
            $errors['code'] = 'Code must be 6 digits';
        }

        if (empty($userId)) {
            $errors['user_id'] = 'User ID is required';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Verify code
        $reset = PasswordReset::verifyCode($userId, $code);

        if (!$reset) {
            PasswordReset::incrementAttempts($userId, $code);
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid or expired code. Please try again.'
            ]);
            return;
        }

        // Code verified - return token for password reset
        $token = bin2hex(random_bytes(32));
        
        // Store token in database (we'll use the code as the token identifier)
        // In a real app, you might want a separate tokens table
        
        echo json_encode([
            'success' => true,
            'message' => 'Code verified successfully!',
            'reset_token' => $token, // This should be used in reset password step
            'user_id' => $userId
        ]);
    }

    /**
     * Reset password with verified code
     * POST /api/reset-password
     */
    public function resetPassword()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim($input['code'] ?? '');
        $userId = (int)($input['user_id'] ?? 0);
        $password = $input['password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        $errors = [];

        if (empty($code)) {
            $errors['code'] = 'Code is required';
        }

        if (empty($userId)) {
            $errors['user_id'] = 'User ID is required';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/', $password)) {
            $errors['password'] = 'Password must have 1 uppercase, 1 special char, and be at least 8 chars';
        }

        if (empty($confirmPassword)) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Verify code one more time
        $reset = PasswordReset::verifyCode($userId, $code);
        if (!$reset) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Code expired or invalid. Please request a new one.'
            ]);
            return;
        }

        // Update password
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        User::update($userId, ['password' => $hashed]);

        // Delete used code
        PasswordReset::deleteCode($userId, $code);

        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully! Please login with your new password.'
        ]);
    }
}

