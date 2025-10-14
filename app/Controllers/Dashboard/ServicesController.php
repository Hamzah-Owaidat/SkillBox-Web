<?php
namespace App\Controllers\Dashboard;

use App\Models\Service;

class ServicesController {
    protected $baseUrl = '/skillbox/public';
    protected $adminId;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->adminId = $_SESSION['user_id'] ?? null;
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;

        $pagination = Service::paginate($limit, $page);
        $services = $pagination['data'];

        ob_start();
        require __DIR__ . '/../../../views/dashboard/services.php';
    }

    // Create new service
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $image = trim($_POST['image'] ?? ''); // Emoji
        $description = trim($_POST['description'] ?? '');

        // Validation
        if (empty($title) || empty($image) || empty($description)) {
            $_SESSION['toast_message'] = 'All fields are required.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Create service
        $serviceId = Service::create([
            'title' => $title,
            'image' => $image,
            'description' => $description,
            'created_by' => $this->adminId
        ]);

        if ($serviceId) {
            $_SESSION['toast_message'] = 'Service created successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to create service.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    // Update service
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // Validation
        if (empty($title) || empty($image) || empty($description)) {
            $_SESSION['toast_message'] = 'All fields are required.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Update service
        if (Service::update($id, [
            'title' => $title,
            'image' => $image,
            'description' => $description,
            'updated_by' => $this->adminId
        ])) {
            $_SESSION['toast_message'] = 'Service updated successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to update service.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    // Delete service
    public function delete($id) {
        if (Service::delete($id)) {
            $_SESSION['toast_message'] = 'Service deleted successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to delete service.';
            $_SESSION['toast_type'] = 'danger';
        }
        
        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    // Export to Excel
    public function export() {
        $services = Service::getAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['ID', 'Title', 'Icon/Emoji', 'Description', 'Created By', 'Updated By', 'Created At', 'Updated At'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        // Fill data
        $row = 2;
        foreach ($services as $service) {
            $sheet->setCellValue("A{$row}", $service['id']);
            $sheet->setCellValue("B{$row}", $service['title']);
            $sheet->setCellValue("C{$row}", $service['image']);
            $sheet->setCellValue("D{$row}", $service['description']);
            $sheet->setCellValue("E{$row}", $service['created_by_name'] ?? 'N/A');
            $sheet->setCellValue("F{$row}", $service['updated_by_name'] ?? 'N/A');
            $sheet->setCellValue("G{$row}", $service['created_at'] ?? '');
            $sheet->setCellValue("H{$row}", $service['updated_at'] ?? '');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set file headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="services_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Output file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}