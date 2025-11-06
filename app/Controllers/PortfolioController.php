<?php

namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;
use App\Models\Portfolio;
use App\Models\Role;
use App\Models\Service;

class PortfolioController
{
    protected $baseUrl = '/skillbox/public';

    public function index()
    {
        $allRoles = Role::getAll();

        // Only keep worker and supervisor
        $roles = array_filter($allRoles, fn($role) => in_array($role['name'], ['worker']));

        $services = Service::getAll();

        require __DIR__ . '/../../views/submitCv.php';
    }

    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // Get the role ID from form
        $roleId = $_POST['requested_role'] ?? null;


        if (!$roleId) {
            $_SESSION['toast_message'] = 'Invalid role selected';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/portfolio");
            exit;
        }

        $data = [
            'user_id'        => $_SESSION['user_id'],
            'full_name'      => htmlspecialchars($_POST['fullname'] ?? ''),
            'email'          => htmlspecialchars($_POST['email'] ?? ''),
            'phone'          => htmlspecialchars($_POST['phone'] ?? ''),
            'address'        => htmlspecialchars($_POST['address'] ?? ''),
            'linkedin'       => htmlspecialchars($_POST['linkedin'] ?? ''),
            'requested_role' => (int)$roleId,
            'attachment_path' => null,
        ];

        // Upload file
        if (!empty($_FILES['attachment']['name'])) {
            $userId = $_SESSION['user_id'];
            $uploadDir = __DIR__ . "/../../public/uploads/portfolios/{$userId}/";

            // Create user folder if not exists
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // Rename file: userId_YYYYMMDD_CV.pdf
            $date = date('Ymd');
            $fileName = "{$userId}_{$date}_CV.pdf";
            $target = $uploadDir . $fileName;

            // Check file type
            $type = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if ($type !== 'pdf') {
                $_SESSION['toast_message'] = 'Only PDF files allowed';
                $_SESSION['toast_type'] = 'danger';
                header("Location: {$this->baseUrl}/portfolio");
                exit;
            }

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                // Store relative path
                $data['attachment_path'] = "public/uploads/portfolios/{$userId}/{$fileName}";
            }
        }


        $portfolioId = Portfolio::create($data);

        // ✅ Attach selected services
        Portfolio::attachServices($portfolioId, $_POST['services'] ?? []);

        $_SESSION['toast_message'] = 'Portfolio submitted successfully';
        $_SESSION['toast_type'] = 'success';
        header("Location: {$this->baseUrl}/");
    }

    // Delete a portfolio (only if pending)
    public function deletePortfolio($id)
    {
        AuthMiddleware::web();
        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        // Only allow POST with _method=DELETE
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['_method'] ?? '') !== 'DELETE') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        if (Portfolio::deletePendingByUser($id, $userId)) {
            $_SESSION['toast_message'] = 'Portfolio deleted successfully';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Cannot delete portfolio (only pending ones can be deleted)';
            $_SESSION['toast_type'] = 'warning';
        }

        header('Location: /skillbox/public/profile');
        exit;
    }

    // Show edit form (reuses submit-cv view)
    public function showEditForm($id)
    {
        AuthMiddleware::web();
        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        $db = Database::getConnection();

        // Fetch the portfolio (only if it belongs to user and is pending)
        $stmt = $db->prepare("SELECT * FROM portfolios WHERE id = :id AND user_id = :user_id AND status = 'pending'");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $portfolio = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$portfolio) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['toast_message'] = 'Portfolio not found or cannot be edited';
            $_SESSION['toast_type'] = 'danger';
            header('Location: /skillbox/public/profile');
            exit;
        }

        if ($portfolio['requested_role']) {
            $role = Role::findById($portfolio['requested_role']);
            if ($role) {
                $portfolio['requested_role_id'] = $role['id'];
            }
        }


        // Get available roles for the form
        $allRoles = Role::getAll();
        $roles = array_filter($allRoles, fn($role) => in_array($role['name'], ['worker']));

        $services = Service::getAll();
        $selectedServiceIds = array_column(Portfolio::getServices($id), 'id');

        // Pass portfolio data to view
        $isEdit = true;
        require __DIR__ . '/../../views/submitCv.php';
    }

    // Update portfolio
    public function updatePortfolio($id)
    {
        AuthMiddleware::web();
        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        if (session_status() === PHP_SESSION_NONE) session_start();

        // Collect POST data
        $full_name = htmlspecialchars($_POST['fullname'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $phone = htmlspecialchars($_POST['phone'] ?? '');
        $address = htmlspecialchars($_POST['address'] ?? '');
        $linkedin = htmlspecialchars($_POST['linkedin'] ?? '');
        $roleId = $_POST['requested_role'] ?? '';
        $selectedServices = $_POST['services'] ?? [];

        $requestedRoleId = null;
        if ($roleId) {
            $role = Role::findById($roleId);
            if ($role && in_array($role['name'], ['worker'])) {
                $requestedRoleId = (int)$role['id'];
            }
        };

        if (empty($full_name) || empty($email) || empty($phone) || empty($address) || !$requestedRoleId) {
            $_SESSION['toast_message'] = 'All required fields must be filled';
            $_SESSION['toast_type'] = 'danger';
            header("Location: /skillbox/public/portfolio/edit/$id");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast_message'] = 'Invalid email format';
            $_SESSION['toast_type'] = 'danger';
            header("Location: /skillbox/public/portfolio/edit/$id");
            exit;
        }

        // Handle file upload
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $userId = $GLOBALS['auth_user']['id'];
            $uploadDir = __DIR__ . "/../../public/uploads/portfolios/{$userId}/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $date = date('Ymd');
            $fileName = "{$userId}_{$date}_CV.pdf";
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                $attachmentPath = "public/uploads/portfolios/{$userId}/{$fileName}";
                $data['attachment_path'] = $attachmentPath;
            }
        }

        // Build the data array for update
        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'linkedin' => $linkedin,
            'requested_role' => $requestedRoleId
        ];

        if ($attachmentPath) {
            $data['attachment_path'] = $attachmentPath;
        }

        // Use the model method
        $updated = Portfolio::updatePendingByUser($id, $userId, $data);

        Portfolio::syncServices($id, $selectedServices);

        if ($updated) {
            $_SESSION['toast_message'] = 'Portfolio updated successfully';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'No changes were made or portfolio cannot be edited';
            $_SESSION['toast_type'] = 'warning';
        }

        header('Location: /skillbox/public/profile');
        exit;
    }
}
