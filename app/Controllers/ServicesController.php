<?php
namespace App\Controllers;

class ServicesController {
    protected $baseUrl = '/skillbox/public';
    public function index() {
        require __DIR__ . '/../../views/services.php';
    }
}
