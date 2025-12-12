<?php
namespace App\Controllers\Dashboard;

require_once __DIR__ . '/../../Helpers/helpers.php';

use App\Models\Service;
use App\Helpers\NotificationHelper;
use App\Models\Activity;
use App\Core\AuthMiddleware;
use App\Core\RoleMiddleware;

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
     * Display all services with pagination, search, and filters
     */
    public function index() {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        
        // Get filters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
        $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

        // Get paginated services
        $pagination = Service::paginate($limit, $page, $search, $sortBy, $sortOrder);
        $services = $pagination['data'];
        $totalServices = $pagination['total'];

        ob_start();
        require __DIR__ . '/../../../views/dashboard/services.php';
    }

    /**
     * Create new service
     */
    public function store() {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Get form data
        $title = trim($_POST['title'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $description = trim($_POST['description'] ?? '');

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
            $_SESSION['toast_message'] = 'Service created successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $this->adminId,
                'service_create',
                "Created service: {$title} (ID: {$serviceId})"
            );
            
            // Send notification to all clients
            $this->notifyServiceAction('add', $title, [
                'id' => $serviceId,
                'title' => $title,
                'description' => $description,
                'image' => $image
            ]);
        } else {
            $_SESSION['toast_message'] = 'Failed to create service.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    /**
     * Update existing service
     */
    public function update($id) {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/services");
            exit;
        }

        // Get form data
        $title = trim($_POST['title'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $description = trim($_POST['description'] ?? '');

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
            $_SESSION['toast_message'] = 'Service updated successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $this->adminId,
                'service_update',
                "Updated service: {$title} (ID: {$id})"
            );
            
            // Send notification to all clients
            $this->notifyServiceAction('edit', $title, [
                'id' => $id,
                'title' => $title,
                'description' => $description,
                'image' => $image
            ]);
        } else {
            $_SESSION['toast_message'] = 'Failed to update service.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    /**
     * Delete service
     */
    public function delete($id) {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        // Get service details before deletion for notification
        $service = Service::findById($id);
        $serviceTitle = $service['title'] ?? 'Unknown Service';
        
        if (Service::delete($id)) {
            $_SESSION['toast_message'] = 'Service deleted successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $this->adminId,
                'service_delete',
                "Deleted service: {$serviceTitle} (ID: {$id})"
            );
            
            // Send notification to all clients
            $this->notifyServiceAction('delete', $serviceTitle, [
                'id' => $id,
                'title' => $serviceTitle
            ]);
        } else {
            $_SESSION['toast_message'] = 'Failed to delete service.';
            $_SESSION['toast_type'] = 'danger';
        }
        
        header("Location: {$this->baseUrl}/dashboard/services");
        exit;
    }

    /**
     * Export all services to Excel
     */
    public function export() {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        $services = Service::getAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column headers
        $headers = [
            'ID', 
            'Title', 
            'Icon/Emoji', 
            'Description', 
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

        // Auto-size all columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        Activity::log(
            $this->adminId,
            'service_export',
            'Exported all services to Excel'
        );

        // Set HTTP headers for file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="services_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Helper method to send notifications for service actions
     */
    protected function notifyServiceAction($action, $serviceTitle, $serviceData = []) {
        try {
            $clientIds = NotificationHelper::getAllClientIds();
            
            if (empty($clientIds)) {
                return;
            }
            
            $notifications = [
                'add' => [
                    'title' => 'New Service Available',
                    'message' => "A new service '{$serviceTitle}' has been added to the platform.",
                    'type' => 'add'
                ],
                'edit' => [
                    'title' => 'Service Updated',
                    'message' => "The service '{$serviceTitle}' has been updated with new information.",
                    'type' => 'edit'
                ],
                'delete' => [
                    'title' => 'Service Removed',
                    'message' => "The service '{$serviceTitle}' has been removed from the platform.",
                    'type' => 'delete'
                ]
            ];
            
            $notificationData = $notifications[$action] ?? $notifications['add'];
            
            NotificationHelper::send(
                $this->adminId,
                $clientIds,
                $notificationData['title'],
                $notificationData['message'],
                $notificationData['type'],
                true,
                true 
            );
        } catch (\Exception $e) {
            error_log("Failed to send service notification: " . $e->getMessage());
        }
    }
}