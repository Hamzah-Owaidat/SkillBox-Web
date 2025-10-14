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
    
    /**
     * Display all services with pagination
     */
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 5;

        // Get paginated services
        $pagination = Service::paginate($limit, $page);
        $services = $pagination['data'];
        
        // Get all available supervisors for the dropdown
        $allSupervisors = Service::getAllSupervisors();

        ob_start();
        require __DIR__ . '/../../../views/dashboard/services.php';
    }

    /**
     * Create new service with supervisors
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Get form data
        $title = trim($_POST['title'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $supervisorIds = $_POST['supervisors'] ?? [];

        // Validate required fields
        if (empty($title) || empty($image) || empty($description)) {
            $_SESSION['toast_message'] = 'Title, Icon and Description are required.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Create the service
        $serviceId = Service::create([
            'title' => $title,
            'image' => $image,
            'description' => $description,
            'created_by' => $this->adminId
        ]);

        if ($serviceId) {
            // Assign supervisors if any were selected
            if (!empty($supervisorIds) && is_array($supervisorIds)) {
                $assignResult = Service::assignSupervisors($serviceId, $supervisorIds);
                
                if ($assignResult) {
                    $supervisorCount = count($supervisorIds);
                    $_SESSION['toast_message'] = "Service created successfully with {$supervisorCount} supervisor(s).";
                } else {
                    $_SESSION['toast_message'] = 'Service created but failed to assign supervisors.';
                }
            } else {
                $_SESSION['toast_message'] = 'Service created successfully (no supervisors assigned).';
            }
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to create service.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    /**
     * Update existing service and its supervisors
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Get form data
        $title = trim($_POST['title'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $supervisorIds = $_POST['supervisors'] ?? [];

        // Validate required fields
        if (empty($title) || empty($image) || empty($description)) {
            $_SESSION['toast_message'] = 'Title, Icon and Description are required.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Update the service
        $updateResult = Service::update($id, [
            'title' => $title,
            'image' => $image,
            'description' => $description,
            'updated_by' => $this->adminId
        ]);

        if ($updateResult) {
            // Update supervisors (even if empty array to remove all)
            $assignResult = Service::assignSupervisors($id, $supervisorIds);
            
            if ($assignResult) {
                if (!empty($supervisorIds)) {
                    $supervisorCount = count($supervisorIds);
                    $_SESSION['toast_message'] = "Service updated successfully with {$supervisorCount} supervisor(s).";
                } else {
                    $_SESSION['toast_message'] = 'Service updated successfully (all supervisors removed).';
                }
            } else {
                $_SESSION['toast_message'] = 'Service updated but failed to assign supervisors.';
            }
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to update service.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    /**
     * Delete service (supervisors automatically deleted via cascade)
     */
    public function delete($id) {
        if (Service::delete($id)) {
            $_SESSION['toast_message'] = 'Service and its supervisor assignments deleted successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to delete service.';
            $_SESSION['toast_type'] = 'danger';
        }
        
        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    /**
     * Export all services to Excel including supervisors
     */
    public function export() {
        $services = Service::getAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $headers = [
            'ID', 
            'Title', 
            'Icon/Emoji', 
            'Description', 
            'Supervisors', 
            'Created By', 
            'Updated By', 
            'Created At', 
            'Updated At'
        ];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }

        // Fill data rows
        $row = 2;
        foreach ($services as $service) {
            // Format supervisors as comma-separated names
            $supervisorNames = [];
            if (!empty($service['supervisors'])) {
                foreach ($service['supervisors'] as $supervisor) {
                    $supervisorNames[] = $supervisor['full_name'];
                }
            }
            $supervisorsText = !empty($supervisorNames) ? implode(', ', $supervisorNames) : 'No Supervisors';
            
            // Fill cells
            $sheet->setCellValue("A{$row}", $service['id']);
            $sheet->setCellValue("B{$row}", $service['title']);
            $sheet->setCellValue("C{$row}", $service['image']);
            $sheet->setCellValue("D{$row}", $service['description']);
            $sheet->setCellValue("E{$row}", $supervisorsText);
            $sheet->setCellValue("F{$row}", $service['created_by_name'] ?? 'N/A');
            $sheet->setCellValue("G{$row}", $service['updated_by_name'] ?? 'N/A');
            $sheet->setCellValue("H{$row}", $service['created_at'] ?? '');
            $sheet->setCellValue("I{$row}", $service['updated_at'] ?? '');
            $row++;
        }

        // Auto-size all columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set HTTP headers for file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="services_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1'); // For IE
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        // Generate and output Excel file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}