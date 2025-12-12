<?php

namespace App\Controllers\Dashboard;

use App\Models\Portfolio;
use App\Models\Role;
use App\Helpers\NotificationHelper;
use App\Models\Activity;
use App\Core\AuthMiddleware;
use App\Core\RoleMiddleware;

class PortfoliosController
{
    protected $baseUrl = '/skillbox/public';
    protected $adminId;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->adminId = $_SESSION['user_id'] ?? null;
    }

    public function index()
    {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

        // Get filters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
        $roleFilter = isset($_GET['role']) ? (int)$_GET['role'] : null;
        $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
        $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

        $pagination = Portfolio::paginate($limit, $page, $search, $statusFilter, $roleFilter, $sortBy, $sortOrder);
        $portfolios = $pagination['data'];
        $roles = Role::getAll();
        // ✅ Get status counts for summary stats
        $totalPortfolios = Portfolio::getStatusCounts();

        ob_start();
        require __DIR__ . '/../../../views/dashboard/portfolios.php';
    }

    // Export to Excel
    public function export()
    {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        $portfolios = Portfolio::getAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'ID',
            'User',
            'Full Name',
            'Email',
            'Phone',
            'Address',
            'LinkedIn',
            'Requested Role',
            'Services',
            'Status',
            'Reviewed By',
            'Reviewed At',
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

        // Data
        $row = 2;
        foreach ($portfolios as $p) {
            $services = isset($p['services']) ? implode(", ", array_column($p['services'], 'title')) : 'N/A';

            $sheet->setCellValue("A{$row}", $p['id']);
            $sheet->setCellValue("B{$row}", $p['user_name'] ?? 'N/A');
            $sheet->setCellValue("C{$row}", $p['full_name']);
            $sheet->setCellValue("D{$row}", $p['email']);
            $sheet->setCellValue("E{$row}", $p['phone'] ?? 'N/A');
            $sheet->setCellValue("F{$row}", $p['address'] ?? 'N/A');
            $sheet->setCellValue("G{$row}", $p['linkedin'] ?? 'N/A');
            $sheet->setCellValue("H{$row}", ucfirst($p['requested_role_name'] ?? 'N/A'));
            $sheet->setCellValue("I{$row}", $services);
            $sheet->setCellValue("J{$row}", ucfirst($p['status']));
            $sheet->setCellValue("K{$row}", $p['reviewed_by_name'] ?? '—');
            $sheet->setCellValue("L{$row}", $p['reviewed_at'] ?? '—');
            $sheet->setCellValue("M{$row}", $p['created_at'] ?? '');
            $sheet->setCellValue("N{$row}", $p['updated_at'] ?? '');
            $row++;
        }

        // Auto-size
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        Activity::log(
            $this->adminId,
            'portfolio_export',
            'Exported all portfolios to Excel'
        );

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
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
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

            Activity::log(
                $this->adminId,
                'portfolio_approve',
                "Approved portfolio ID: {$id} for user ID: {$portfolio['user_id']}, assigned role ID: {$roleId}"
            );

            // Send notification
            NotificationHelper::send(
                $this->adminId,
                $portfolio['user_id'],
                'Portfolio Approved! 🎉',
                'Your portfolio has been approved. You can now access your new role.',
                'accept',
                true,
                false
            );
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
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        $portfolio = Portfolio::find($id);

        if (!$portfolio) {
            $_SESSION['toast_message'] = 'Portfolio not found.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/portfolios");
            exit;
        }

        // Reject the portfolio
        $result = Portfolio::reject($id, $this->adminId);

        if ($result) {
            $_SESSION['toast_message'] = 'Portfolio rejected.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $this->adminId,
                'portfolio_reject',
                "Rejected portfolio ID: {$id} for user ID: {$portfolio['user_id']}"
            );

            // Send notification
            NotificationHelper::send(
                $this->adminId,
                $portfolio['user_id'],
                'Portfolio Rejected',
                'Unfortunately, your portfolio has been rejected. Please review and resubmit.',
                'reject',
                true,
                false
            );
        } else {
            $_SESSION['toast_message'] = 'Failed to reject portfolio.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/portfolios");
        exit;
    }

    // Delete portfolio
    public function delete($id)
    {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        // Get portfolio to delete file
        $portfolio = Portfolio::find($id);

        if ($portfolio && Portfolio::delete($id)) {
            // Delete file if exists
            if (!empty($portfolio['attachment_path']) && file_exists($portfolio['attachment_path'])) {
                unlink($portfolio['attachment_path']);
            }

            $_SESSION['toast_message'] = 'Portfolio deleted successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $this->adminId,
                'portfolio_delete',
                "Deleted portfolio ID: {$id}, user ID: {$portfolio['user_id']}"
            );
        } else {
            $_SESSION['toast_message'] = 'Failed to delete portfolio.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/portfolios");
        exit;
    }

    /**
     * Send notification with custom recipients
     */
    public function notifySpecificUsers($portfolioId)
    {
        $portfolio = Portfolio::find($portfolioId);

        // Get specific users to notify
        $adminIds = NotificationHelper::getAllAdminIds();

        // Combine the IDs
        $recipientIds = array_merge($adminIds);

        // Send PRIVATE notification to specific users
        NotificationHelper::send(
            $this->adminId,
            $recipientIds,
            'Urgent: Portfolio Requires Attention',
            "Portfolio #{$portfolioId} requires immediate review.",
            'warning',
            true,
            false
        );
    }
}
