<?php
namespace App\Controllers;

class HomeController {
    protected $baseUrl = '/skillbox/public';
    public function index() {
        require __DIR__ . '/../../views/home.php';
    }
}
