<?php
namespace App\Controllers\Dashboard;

use App\Models\Role;

class RoleController {
    protected $baseUrl = '/skillbox/public';

    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10; // rows per page

        $pagination = Role::paginate($limit, $page);
        $roles = $pagination['data'];

        $title = "Roles";
        ob_start();
        require __DIR__ . '/../../../views/dashboard/roles.php';
    }
}
