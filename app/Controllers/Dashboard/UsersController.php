<?php
namespace App\Controllers\Dashboard;

use App\Models\User;
use App\Models\Role;
use App\Models\Activity;
use App\Core\AuthMiddleware;
use App\Core\RoleMiddleware;

class UsersController {
    protected $baseUrl = '/skillbox/public';

    public function index() {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        
        // Get filters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $roleFilter = isset($_GET['role']) ? (int)$_GET['role'] : null;
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

        $pagination = User::paginate($limit, $page, $search, $roleFilter, $statusFilter);
        $users = $pagination['data'];
        $roles = Role::getAll();
        $totalUsers = $pagination['total'];

        ob_start();
        require __DIR__ . '/../../../views/dashboard/users.php';
    }

    // ✅ Create new user
    public function store() {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 2);

        // Validation
        if (empty($fullName) || empty($email) || empty($password)) {
            $_SESSION['toast_message'] = 'All fields are required.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['toast_message'] = 'Invalid email format.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }
        if (strlen($password) < 6) {
            $_SESSION['toast_message'] = 'Password must be at least 6 characters.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }
        if (User::findByEmail($email)) {
            $_SESSION['toast_message'] = 'Email already exists.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }
        if (!Role::findById($roleId)) {
            $_SESSION['toast_message'] = 'Invalid role selected.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        // Create user
        $userId = User::create([
            'full_name' => $fullName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role_id' => $roleId,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);

        if ($userId) {
            $_SESSION['toast_message'] = 'User created successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $_SESSION['user_id'] ?? null,
                'user_create',
                "Created user: {$fullName} ({$email})"
            );
        } else {
            $_SESSION['toast_message'] = 'Failed to create user.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }

    // ✅ Update user
    public function update($id) {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 2);

        $updateData = [
            'full_name' => $fullName,
            'email' => $email,
            'role_id' => $roleId,
            'updated_by' => $_SESSION['user_id'] ?? null
        ];
        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        if (User::update($id, $updateData)) {
            $_SESSION['toast_message'] = 'User updated successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $_SESSION['user_id'] ?? null,
                'user_update',
                "Updated user ID {$id}: {$fullName} ({$email})"
            );
        } else {
            $_SESSION['toast_message'] = 'Failed to update user.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }

    // ✅ Toggle user status
    public function toggleStatus($id) {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['toast_message'] = 'You cannot deactivate your own account.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        if (User::toggleStatus($id)) {
            $_SESSION['toast_message'] = 'User status updated successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $_SESSION['user_id'] ?? null,
                'user_toggle_status',
                "Toggled status for user ID {$id}"
            );
        } else {
            $_SESSION['toast_message'] = 'Failed to update user status.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }

    // ✅ Delete user
    public function delete($id) {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
            $_SESSION['toast_message'] = 'You cannot delete your own account.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/users");
            exit;
        }

        $user = User::find($id);
        if (User::delete($id)) {
            $_SESSION['toast_message'] = 'User deleted successfully.';
            $_SESSION['toast_type'] = 'success';

            Activity::log(
                $_SESSION['user_id'] ?? null,
                'user_delete',
                "Deleted user: {$user['full_name']} ({$user['email']})"
            );
        } else {
            $_SESSION['toast_message'] = 'Failed to delete user.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/users");
        exit;
    }

    // ✅ Export users
    public function export() {
        AuthMiddleware::web();
        RoleMiddleware::admin();
        
        $users = User::getAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Full Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Role');
        $sheet->setCellValue('E1', 'Status');
        $sheet->setCellValue('F1', 'Created By');
        $sheet->setCellValue('G1', 'Updated By');
        $sheet->setCellValue('H1', 'Created At');
        $sheet->setCellValue('I1', 'Updated At');

        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user['id']);
            $sheet->setCellValue("B{$row}", $user['full_name']);
            $sheet->setCellValue("C{$row}", $user['email']);
            $sheet->setCellValue("D{$row}", $user['role_name'] ?? 'N/A');
            $sheet->setCellValue("E{$row}", ucfirst($user['status']));
            $sheet->setCellValue("F{$row}", $user['created_by_name'] ?? 'N/A');
            $sheet->setCellValue("G{$row}", $user['updated_by_name'] ?? 'N/A');
            $sheet->setCellValue("H{$row}", $user['created_at'] ?? '');
            $sheet->setCellValue("I{$row}", $user['updated_at'] ?? '');
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        Activity::log(
            $_SESSION['user_id'] ?? null,
            'user_export',
            'Exported users data to Excel'
        );

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="users_export.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}