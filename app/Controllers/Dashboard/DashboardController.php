<?php

namespace App\Controllers\Dashboard;

use App\Models\User;
use App\Models\Role;
use App\Models\Service;
use App\Models\Activity;
use App\Core\AuthMiddleware;
use App\Core\RoleMiddleware;

class DashboardController
{
    protected $baseUrl = '/skillbox/public';

    public function index()
    {
        AuthMiddleware::web();
        RoleMiddleware::admin();
    // Stats
    $totalUsers = User::getCount();
    $totalServices = Service::getCount();

    $adminRole = Role::findByName('admin');
    $totalAdmins = $adminRole ? User::getCountByRole($adminRole['id']) : 0;

    $workerRole = Role::findByName('worker');
    $totalWorkers = $workerRole ? User::getCountByRole($workerRole['id']) : 0;

    // Chart 1: Users by Role
    $chartData = [
        'labels' => ['Admins', 'Workers', 'Others'],
        'values' => [
            $totalAdmins,
            $totalWorkers,
            $totalUsers - $totalAdmins - $totalWorkers
        ]
    ];

    // Chart 2: Services per Month
    $servicesStats = Service::getMonthlyStats();
    $servicesChart = [
        'labels' => array_column($servicesStats, 'month'),
        'values' => array_column($servicesStats, 'total'),
    ];

    // Chart 3: Activities per Day (Last 7 Days)
    $activityStats = Activity::getWeeklyStats();
    $activityChart = [
        'labels' => array_column($activityStats, 'day'),
        'values' => array_column($activityStats, 'total'),
    ];

    // Recent activities
    $recentActivities = Activity::getRecent(5);

    require __DIR__ . '/../../../views/dashboard/index.php';
}



    public function export()
    {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
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
