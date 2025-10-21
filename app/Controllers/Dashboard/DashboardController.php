<?php
namespace App\Controllers\Dashboard;

use App\Models\User;
use App\Models\Role;
use App\Models\Service;
use App\Models\Activity;

class DashboardController {
    protected $baseUrl = '/skillbox/public';

    public function index() {
        // Stats
        $totalUsers = User::getCount();
        $totalServices = Service::getCount();

        $adminRole = Role::findByName('admin');
        $totalAdmins = $adminRole ? User::getCountByRole($adminRole['id']) : 0;

        $workerRole = Role::findByName('worker');
        $totalWorkers = $workerRole ? User::getCountByRole($workerRole['id']) : 0;

        // Recent activities
        $recentActivities = Activity::getRecent(5);

        require __DIR__ . '/../../../views/dashboard/index.php';
    }

    public function export() {
        $users = Activity::getAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Full Name');
        $sheet->setCellValue('C1', 'Action');
        $sheet->setCellValue('D1', 'Message');
        $sheet->setCellValue('E1', 'Created At');

        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user['id']);
            $sheet->setCellValue("B{$row}", $user['full_name']);
            $sheet->setCellValue("C{$row}", $user['action']);
            $sheet->setCellValue("D{$row}", $user['message']);
            $sheet->setCellValue("E{$row}", $user['created_at'] ?? '');

            $row++;
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ✅ Log activity
        Activity::log(
            $_SESSION['user_id'] ?? null,
            'activities_export',
            'Exported activities data to Excel'
        );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="activities_export.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
