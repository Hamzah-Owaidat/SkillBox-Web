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

    public function show($id) {
        $service = Service::findById($id);
        if (!$service) {
            header("Location: {$this->baseUrl}/services");
            exit;
        }
    
        // Fetch workers assigned to this service
        $workers = Service::getWorkers($id);
    
        ob_start();
        require __DIR__ . '/../../views/service_details.php';
    }
    
    
}
