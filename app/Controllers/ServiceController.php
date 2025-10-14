<?php
namespace App\Controllers;

use App\Models\Service;

class ServiceController {
    protected $baseUrl = '/skillbox/public';
    public function index() {
        $services = Service::getAll();

        ob_start();
        require __DIR__ . '/../../views/services.php';
    }
}
