<?php

namespace App\Controllers\Api;

use App\Core\AuthMiddleware;
use App\Models\Portfolio;
use App\Models\Role;
use App\Models\Service;

class PortfolioApiController
{
    /**
     * Submit a portfolio (CV) from mobile.
     * Accepts multipart/form-data (for PDF) or JSON (without file).
     */
    public function store()
    {
        header('Content-Type: application/json');

        AuthMiddleware::api();
        $user = $GLOBALS['auth_user'] ?? null;

        if (!$user || empty($user['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $userId = $user['id'];

        // Support both form-data and JSON bodies
        $input = [];
        if (empty($_POST)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }

        $get = function ($key, $default = '') use ($input) {
            return trim($_POST[$key] ?? $input[$key] ?? $default);
        };

        $fullName = $get('full_name');
        $email = $get('email');
        $phone = $get('phone');
        $address = $get('address');
        $linkedin = $get('linkedin');
        $requestedRole = $_POST['requested_role'] ?? $input['requested_role'] ?? null;
        $servicesInput = $_POST['services'] ?? $input['services'] ?? [];

        // Normalize services to array of IDs
        if (is_string($servicesInput)) {
            $jsonServices = json_decode($servicesInput, true);
            if (is_array($jsonServices)) {
                $servicesInput = $jsonServices;
            } else {
                $servicesInput = array_filter(array_map('trim', explode(',', $servicesInput)));
            }
        }
        $serviceIds = array_map('intval', (array)$servicesInput);

        // Validation
        if (!$fullName || !$email || !$phone || !$address || !$requestedRole) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'full_name, email, phone, address, requested_role are required']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid email format']);
            return;
        }

        $role = Role::findById($requestedRole);
        if (!$role || !in_array(($role['name'] ?? ''), ['worker'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid requested_role']);
            return;
        }

        // Handle file upload (optional, multipart/form-data)
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/portfolios/' . $userId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed']);
                return;
            }

            $fileName = "{$userId}_" . date('Ymd_His') . "_CV.pdf";
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
                return;
            }

            $attachmentPath = "public/uploads/portfolios/{$userId}/{$fileName}";
        }

        // Create portfolio
        $portfolioId = Portfolio::create([
            'user_id'        => $userId,
            'full_name'      => htmlspecialchars($fullName),
            'email'          => htmlspecialchars($email),
            'phone'          => htmlspecialchars($phone),
            'address'        => htmlspecialchars($address),
            'linkedin'       => htmlspecialchars($linkedin),
            'requested_role' => (int)$requestedRole,
            'attachment_path'=> $attachmentPath,
        ]);

        // Attach selected services
        if (!empty($serviceIds)) {
            Portfolio::attachServices($portfolioId, $serviceIds);
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'portfolio_id' => (int)$portfolioId,
            'message' => 'Portfolio submitted successfully'
        ]);
    }

    /**
     * Update a pending portfolio (mobile).
     * Accepts multipart/form-data (for PDF) or JSON (without file).
     */
    public function update($id)
    {
        header('Content-Type: application/json');

        AuthMiddleware::api();
        $user = $GLOBALS['auth_user'] ?? null;

        if (!$user || empty($user['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $userId = $user['id'];

        // Ensure portfolio exists, belongs to user, and is pending
        $portfolio = Portfolio::findPendingByUser($id, $userId);
        if (!$portfolio) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Portfolio not found or cannot be edited']);
            return;
        }

        // Support both form-data and JSON bodies
        $input = [];
        if (empty($_POST)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }

        $get = function ($key, $default = '') use ($input) {
            return trim($_POST[$key] ?? $input[$key] ?? $default);
        };

        $fullName = $get('full_name');
        $email = $get('email');
        $phone = $get('phone');
        $address = $get('address');
        $linkedin = $get('linkedin');
        $requestedRole = $_POST['requested_role'] ?? $input['requested_role'] ?? null;
        $servicesInput = $_POST['services'] ?? $input['services'] ?? [];

        // Normalize services to array of IDs
        if (is_string($servicesInput)) {
            $jsonServices = json_decode($servicesInput, true);
            if (is_array($jsonServices)) {
                $servicesInput = $jsonServices;
            } else {
                $servicesInput = array_filter(array_map('trim', explode(',', $servicesInput)));
            }
        }
        $serviceIds = array_map('intval', (array)$servicesInput);

        // Validation
        if (!$fullName || !$email || !$phone || !$address || !$requestedRole) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'full_name, email, phone, address, requested_role are required']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid email format']);
            return;
        }

        $role = Role::findById($requestedRole);
        if (!$role || !in_array(($role['name'] ?? ''), ['worker'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Invalid requested_role']);
            return;
        }

        // Handle file upload (optional, multipart/form-data)
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/portfolios/' . $userId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed']);
                return;
            }

            $fileName = "{$userId}_" . date('Ymd_His') . "_CV.pdf";
            $targetPath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
                return;
            }

            $attachmentPath = "public/uploads/portfolios/{$userId}/{$fileName}";
        }

        // Build update data
        $data = [
            'full_name'      => htmlspecialchars($fullName),
            'email'          => htmlspecialchars($email),
            'phone'          => htmlspecialchars($phone),
            'address'        => htmlspecialchars($address),
            'linkedin'       => htmlspecialchars($linkedin),
            'requested_role' => (int)$requestedRole,
        ];

        if ($attachmentPath) {
            $data['attachment_path'] = $attachmentPath;
        }

        $updated = Portfolio::updatePendingByUser($id, $userId, $data);

        // Sync services
        Portfolio::syncServices($id, $serviceIds);

        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Portfolio updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made or portfolio cannot be edited']);
        }
    }

    /**
     * Get a pending portfolio (owned by user) for editing.
     */
    public function show($id)
    {
        header('Content-Type: application/json');

        AuthMiddleware::api();
        $user = $GLOBALS['auth_user'] ?? null;

        if (!$user || empty($user['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $userId = $user['id'];

        // Only allow fetching pending portfolios that belong to the user
        $portfolio = Portfolio::findPendingByUser($id, $userId);
        if (!$portfolio) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Portfolio not found or cannot be edited']);
            return;
        }

        // Attach services
        $services = Portfolio::getServices($id);
        $portfolio['services'] = $services;

        echo json_encode([
            'success' => true,
            'data' => $portfolio
        ]);
    }
}

