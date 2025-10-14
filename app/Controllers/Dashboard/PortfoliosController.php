<?php
namespace App\Controllers\Dashboard;

use App\Models\Portfolio;
use App\Models\Role;

class PortfoliosController {
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

        $pagination = Portfolio::paginate($limit, $page);
        $portfolios = $pagination['data'];

        ob_start();
        require __DIR__ . '/../../../views/dashboard/portfolios.php';
    }

    // Export to Excel
    public function export()
    {
        $portfolios = Portfolio::getAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'ID', 'User', 'Full Name', 'Email', 'Phone', 'Address', 
            'LinkedIn', 'Requested Role', 'Status', 
            'Reviewed By', 'Reviewed At', 'Created At', 'Updated At'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        // Data
        $row = 2;
        foreach ($portfolios as $p) {
            $sheet->setCellValue("A{$row}", $p['id']);
            $sheet->setCellValue("B{$row}", $p['user_name'] ?? 'N/A');
            $sheet->setCellValue("C{$row}", $p['full_name']);
            $sheet->setCellValue("D{$row}", $p['email']);
            $sheet->setCellValue("E{$row}", $p['phone'] ?? 'N/A');
            $sheet->setCellValue("F{$row}", $p['address'] ?? 'N/A');
            $sheet->setCellValue("G{$row}", $p['linkedin'] ?? 'N/A');
            $sheet->setCellValue("H{$row}", ucfirst($p['requested_role_name'] ?? 'N/A'));
            $sheet->setCellValue("I{$row}", ucfirst($p['status']));
            $sheet->setCellValue("J{$row}", $p['reviewed_by_name'] ?? '—');
            $sheet->setCellValue("K{$row}", $p['reviewed_at'] ?? '—');
            $sheet->setCellValue("L{$row}", $p['created_at'] ?? '');
            $sheet->setCellValue("M{$row}", $p['updated_at'] ?? '');
            $row++;
        }

        // Auto-size
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="portfolios_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    public function accept($id)
    {
        $portfolio = Portfolio::find($id);
        if (!$portfolio) {
            $_SESSION['toast_message'] = 'Portfolio not found.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/portfolios");
            exit;
        }

        // requested_role is already the role ID
        $roleId = $portfolio['requested_role'];

        // Verify the role exists
        $role = Role::findById($roleId);
        if (!$role) {
            $_SESSION['toast_message'] = 'Invalid requested role.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/portfolios");
            exit;
        }

        $success = Portfolio::approve($id, $roleId, $portfolio['user_id'], $this->adminId);

        if ($success) {
            $_SESSION['toast_message'] = 'Portfolio approved and user role updated successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to approve portfolio.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/portfolios");
        exit;
    }

    // Reject portfolio
    public function reject($id)
    {
        $success = Portfolio::reject($id, $this->adminId);

        $_SESSION['toast_message'] = $success ? 'Portfolio rejected.' : 'Failed to reject portfolio.';
        $_SESSION['toast_type'] = $success ? 'warning' : 'danger';

        header("Location: {$this->baseUrl}/dashboard/portfolios");
        exit;
    }

    // Delete portfolio
    public function delete($id) {
        // Get portfolio to delete file
        $portfolio = Portfolio::find($id);
        
        if ($portfolio && Portfolio::delete($id)) {
            // Delete file if exists
            if (!empty($portfolio['attachment_path']) && file_exists($portfolio['attachment_path'])) {
                unlink($portfolio['attachment_path']);
            }
            
            $_SESSION['toast_message'] = 'Portfolio deleted successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to delete portfolio.';
            $_SESSION['toast_type'] = 'error';
        }
        
        header("Location: {$this->baseUrl}/dashboard/portfolios");
        exit;
    }
}