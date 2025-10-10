<?php
namespace App\Controllers;

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
}
