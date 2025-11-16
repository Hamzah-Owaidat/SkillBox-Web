<?php

namespace App\Controllers\Api;

use App\Models\Service;

class ServiceApiController
{
    public function index()
    {
        header('Content-Type: application/json');

        $services = Service::getAll();

        echo json_encode([
            "status" => "success",
            "data" => $services
        ]);
    }

    public function show($id)
    {
        header('Content-Type: application/json');

        $service = Service::findById($id);

        if (!$service) {
            echo json_encode([
                "status" => "error",
                "message" => "Service not found."
            ]);
            return;
        }

        // Fetch workers for this service
        $workers = Service::getWorkers($id);

        echo json_encode([
            "status" => "success",
            "data" => [
                "service" => $service,
                "workers" => $workers
            ]
        ]);
    }
}
