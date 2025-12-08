<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Models\Role;
use App\Models\Activity;
use App\Helpers\JWTHelper;

class UserApiController
{
    /**
     * Get user ID from session or JWT
     */
    private function getUserId()
    {
        // Check session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }

        // Check Authorization: Bearer <token>
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            $token = $matches[1];

            // Validate JWT
            $decoded = JWTHelper::validate($token);

            if ($decoded && isset($decoded['data']->id)) {
                return $decoded['data']->id;
            }
        }

        return null;
    }

    /**
     * Update user profile via API (for mobile)
     */
    public function updateProfile()
    {
        header('Content-Type: application/json');
        
        $userId = $this->getUserId();
        
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            exit;
        }

        $full_name = trim($input['full_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $old_password = $input['old_password'] ?? '';
        $new_password = $input['new_password'] ?? '';

        // Validate required fields
        if (empty($full_name) || empty($email)) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => 'Full name and email are required'
            ]);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => 'Invalid email format'
            ]);
            exit;
        }

        // Check if email is already taken by another user
        $existingUser = User::findByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => 'Email is already taken by another user'
            ]);
            exit;
        }

        $updateData = [
            'full_name' => htmlspecialchars($full_name),
            'email' => htmlspecialchars($email),
            'updated_by' => $userId
        ];

        // Handle password change
        if (!empty($new_password)) {
            if (empty($old_password)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Old password is required to change password'
                ]);
                exit;
            }

            // Verify old password
            if (!User::verifyPassword($userId, $old_password)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Old password is incorrect'
                ]);
                exit;
            }

            // Validate new password length
            if (strlen($new_password) < 6) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'New password must be at least 6 characters long'
                ]);
                exit;
            }

            $updateData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Update user profile
        $success = User::updateProfile($userId, $updateData);

        if ($success) {
            // Get updated user data
            $updatedUser = User::find($userId);
            $roleName = null;
            if (!empty($updatedUser['role_id'])) {
                $roleData = Role::findById($updatedUser['role_id']);
                $roleName = $roleData['name'] ?? 'Unknown';
            }

            // Log activity
            Activity::log(
                $userId, 
                'profile_update',
                'User updated profile via API'
            );

            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $updatedUser['id'],
                    'full_name' => $updatedUser['full_name'],
                    'email' => $updatedUser['email'],
                    'role' => $roleName
                ]
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'error' => 'No changes were made or update failed'
            ]);
        }
    }
}

