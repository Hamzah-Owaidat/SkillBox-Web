<?php
namespace App\Controllers\Dashboard;

class DashboardController {
    protected $baseUrl = '/skillbox/public';
    public function index() {
        require __DIR__ . '/../../../views/dashboard/index.php';
    }
}
