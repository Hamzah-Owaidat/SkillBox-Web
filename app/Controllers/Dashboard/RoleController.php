<?php
namespace App\Controllers\Dashboard;

use App\Models\Role;
use PDO;

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

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/roles");
            exit;
        }

        $name = trim($_POST['name'] ?? '');


        // Validation
        if (empty($name)) {
            $_SESSION['toast_message'] = 'Name field are required.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/roles");
            exit;
        }

        // Create user
        $roleId = Role::create([
            'name' => $name,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);

        if ($roleId) {
            $_SESSION['toast_message'] = 'Role created successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to create role.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/roles");
        exit;
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->baseUrl}/dashboard/roles");
            exit;
        }

        $name = trim($_POST['name'] ?? '');

        // Validation
        if (empty($name)) {
            $_SESSION['toast_message'] = 'Name are required.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/roles");
            exit;
        }

        // Check if email exists for another user
        $existingRole = Role::findByName($name);
        if ($existingRole && $existingRole['id'] != $id) {
            $_SESSION['toast_message'] = 'Role already exists.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/roles");
            exit;
        }

        // Prepare update data
        $updateData = [
            'name' => $name,
            'updated_by' => $_SESSION['user_id'] ?? null
        ];


        // Update user
        if (Role::update($id, $updateData)) {
            $_SESSION['toast_message'] = 'Role updated successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to update role.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/roles");
        exit;
    }

    public function delete($id)
    {
        // 1️⃣ Fetch role info
        $role = Role::find($id);

        if (!$role) {
            $_SESSION['toast_message'] = 'Role not found.';
            $_SESSION['toast_type'] = 'danger';
            header("Location: {$this->baseUrl}/dashboard/roles");
            exit;
        }

        // 2️⃣ Check if the role is "Admin"
        if (strtolower($role['name']) === 'admin') {
            // Check if any users still have this role
            $stmt = Role::db()->prepare("SELECT COUNT(*) AS count FROM users WHERE role_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($count > 0) {
                $_SESSION['toast_message'] = 'You cannot delete the Admin role because there are still Admins in the system.';
                $_SESSION['toast_type'] = 'danger';
                header("Location: {$this->baseUrl}/dashboard/roles");
                exit;
            }
        }

        // 3️⃣ Delete all users who have this role
        $stmt = Role::db()->prepare("DELETE FROM users WHERE role_id = ?");
        $stmt->execute([$id]);

        // 4️⃣ Delete the role itself
        if (Role::delete($id)) {
            $_SESSION['toast_message'] = 'Role and associated users deleted successfully.';
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_message'] = 'Failed to delete role.';
            $_SESSION['toast_type'] = 'danger';
        }

        header("Location: {$this->baseUrl}/dashboard/roles");
        exit;
    }


    public function export()
    {
        $roles = Role::getAll();

        // ✅ Load PhpSpreadsheet classes
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ✅ Set headers
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Created By');
        $sheet->setCellValue('D1', 'Updated By');
        $sheet->setCellValue('E1', 'Created At');
        $sheet->setCellValue('F1', 'Updated At');

        // ✅ Fill data
        $row = 2;
        foreach ($roles as $role) {
            $sheet->setCellValue("A{$row}", $role['id']);
            $sheet->setCellValue("B{$row}", $role['name']);
            $sheet->setCellValue("C{$row}", $role['created_by'] ?? 'null');
            $sheet->setCellValue("D{$row}", $role['updated_by'] ?? 'null');
            $sheet->setCellValue("E{$row}", $role['created_at'] ?? '');
            $sheet->setCellValue("F{$row}", $role['updated_at'] ?? '');
            $row++;
        }

        // ✅ Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ✅ Set file headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="roles_export.xlsx"');
        header('Cache-Control: max-age=0');

        // ✅ Output file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
