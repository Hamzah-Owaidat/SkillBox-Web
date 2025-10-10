<?php
namespace App\Controllers;

use App\Core\AuthMiddleware;
use App\Core\Database;

class PortfolioController {
    protected $baseUrl = '/skillbox/public';

    public function index() {
        require __DIR__ . '/../../views/submitCv.php';
    }

    public function store() {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Collect & sanitize POST data
        $full_name = htmlspecialchars($_POST['fullname'] ?? '');
        $email     = htmlspecialchars($_POST['email'] ?? '');
        $phone     = htmlspecialchars($_POST['phone'] ?? '');
        $address   = htmlspecialchars($_POST['address'] ?? '');
        $linkedin  = htmlspecialchars($_POST['linkedin'] ?? '');
        $role      = $_POST['role'] ?? '';

        // Handle file upload
        $attachment_path = null;
        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/portfolios/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = time() . '_' . basename($_FILES['attachment']['name']);
            $targetFile = $uploadDir . $fileName;

            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            if ($fileType !== 'pdf') {
                echo json_encode(['error' => 'Only PDF files allowed']);
                exit;
            }

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                $attachment_path = "public/uploads/portfolios/$fileName"; // relative path
            } else {
                echo json_encode(['error' => 'Failed to upload file']);
                exit;
            }
        }

        // Save to database
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO portfolios
                (user_id, full_name, email, phone, address, linkedin, attachment_path, requested_role)
            VALUES
                (:user_id, :full_name, :email, :phone, :address, :linkedin, :attachment_path, :requested_role)
        ");

        $stmt->execute([
            ':user_id'        => $user_id,
            ':full_name'      => $full_name,
            ':email'          => $email,
            ':phone'          => $phone,
            ':address'        => $address,
            ':linkedin'       => $linkedin,
            ':attachment_path'=> $attachment_path,
            ':requested_role' => $role
        ]);

        // Redirect or send JSON response
        $_SESSION['toast_message'] = 'Portfolio submitted successfully';
        $_SESSION['toast_type'] = 'success';
        header("Location: {$this->baseUrl}/");        
    }

    // Delete a portfolio (only if pending)
    public function deletePortfolio($id) {
        AuthMiddleware::web();
        $user = $GLOBALS['auth_user'];
        $userId = $user['id'];

        // Only allow POST with _method=DELETE
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['_method'] ?? '') !== 'DELETE') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM portfolios WHERE id = :id AND user_id = :user_id AND status = 'pending'");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['toast_message'] = 'Portfolio deleted successfully';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Cannot delete portfolio (only pending portfolios can be deleted)';
            $_SESSION['toast_type'] = 'warning';
        }

        header('Location: /skillbox/public/profile');
        exit;
    }

        // Show edit form (reuses submit-cv view)
    public function showEditForm($id) {
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

        // Pass portfolio data to view
        $isEdit = true;
        require __DIR__ . '/../../views/submitCv.php';
    }

    // Update portfolio
    public function updatePortfolio($id) {
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
        $role = $_POST['role'] ?? '';

        if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($role)) {
            $_SESSION['toast_message'] = 'All required fields must be filled';
            $_SESSION['toast_type'] = 'danger';
            header('Location: /skillbox/public/portfolio/edit/' . $id);
            exit;
        }

        $db = Database::getConnection();

        // Handle file upload if new file is provided
        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['attachment']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
                $attachmentPath = 'uploads/' . $fileName;
            }
        }

        // Build update query
        if ($attachmentPath) {
            $stmt = $db->prepare("
                UPDATE portfolios
                SET full_name = :full_name, email = :email, phone = :phone, 
                    address = :address, linkedin = :linkedin, requested_role = :role,
                    attachment_path = :attachment_path, updated_at = NOW()
                WHERE id = :id AND user_id = :user_id AND status = 'pending'
            ");
            $params = [
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':linkedin' => $linkedin,
                ':role' => $role,
                ':attachment_path' => $attachmentPath,
                ':id' => $id,
                ':user_id' => $userId
            ];
        } else {
            $stmt = $db->prepare("
                UPDATE portfolios
                SET full_name = :full_name, email = :email, phone = :phone, 
                    address = :address, linkedin = :linkedin, requested_role = :role,
                    updated_at = NOW()
                WHERE id = :id AND user_id = :user_id AND status = 'pending'
            ");
            $params = [
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':linkedin' => $linkedin,
                ':role' => $role,
                ':id' => $id,
                ':user_id' => $userId
            ];
        }

        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
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
