<?php
namespace App\Controllers\Dashboard;

use App\Models\User;

class UsersController {
    protected $baseUrl = '/skillbox/public';
    public function index() {

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $limit = 5;

        $pagination = User::paginate($limit, $page);
        $users = $pagination['data'];
        
        ob_start();
        require __DIR__ . '/../../../views/dashboard/users.php';
    }
}
