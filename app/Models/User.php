<?php
namespace App\Models;
use App\Core\Model;
use PDO;

class User extends Model {
    protected static $table = 'users';

    public static function findByEmail($email) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create(array $data) {
        $stmt = self::db()->prepare("
            INSERT INTO " . static::$table . " 
            (full_name, email, password, role_id, status, created_by) 
            VALUES (?, ?, ?, ?, 'active', ?)
        ");
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['password'],
            $data['role_id'] ?? 2,
            $data['created_by'] ?? null
        ]);
        return self::db()->lastInsertId();
    }

    public static function update($id, array $data) {
        $fields = [];
        $values = [];

        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $values[] = $data['full_name'];
        }

        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $values[] = $data['password'];
        }

        if (isset($data['role_id'])) {
            $fields[] = "role_id = ?";
            $values[] = $data['role_id'];
        }

        if (isset($data['updated_by'])) {
            $fields[] = "updated_by = ?";
            $values[] = $data['updated_by'];
        }

        $fields[] = "updated_at = NOW()";

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($values);
    }

    public static function delete($id) {
        $stmt = self::db()->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function toggleStatus($id) {
        $stmt = self::db()->prepare("
            UPDATE " . static::$table . "
            SET status = CASE 
                WHEN status = 'active' THEN 'inactive'
                ELSE 'active'
            END
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    public static function getAll() {
        $stmt = self::db()->prepare("
            SELECT u.*, 
                r.name AS role_name, 
                c.full_name AS created_by_name, 
                up.full_name AS updated_by_name
            FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN users c ON u.created_by = c.id
                LEFT JOIN users up ON u.updated_by = up.id
            ORDER BY u.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function paginate($limit = 10, $page = 1, $search = '', $roleFilter = null, $statusFilter = '') {
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($roleFilter !== null && $roleFilter > 0) {
            $whereConditions[] = "u.role_id = ?";
            $params[] = $roleFilter;
        }
        
        if (!empty($statusFilter)) {
            $whereConditions[] = "u.status = ?";
            $params[] = $statusFilter;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM users u {$whereClause}";
        $countStmt = self::db()->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated data
        $sql = "
            SELECT u.*, 
                r.name AS role_name,
                c.full_name AS created_by_name,
                up.full_name AS updated_by_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN users c ON u.created_by = c.id
            LEFT JOIN users up ON u.updated_by = up.id
            {$whereClause}
            ORDER BY u.id ASC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = self::db()->prepare($sql);
        
        // Bind search and filter params
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }
        
        // Bind pagination params
        $stmt->bindValue(count($params) + 1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    public static function getCount() {
        $stmt = self::db()->query("SELECT COUNT(*) as total FROM " . static::$table);
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public static function getCountByRole($roleId) {
        $stmt = self::db()->prepare("SELECT COUNT(*) as total FROM " . static::$table . " WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public static function findWithRole($id) {
        $stmt = self::db()->prepare("
            SELECT u.*, r.name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateProfile($id, array $data) {
        $fields = [];
        $values = [];
    
        if (!empty($data['full_name'])) {
            $fields[] = "full_name = ?";
            $values[] = $data['full_name'];
        }
    
        if (!empty($data['email'])) {
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }
    
        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $values[] = $data['password'];
        }
    
        if (empty($fields)) {
            return false;
        }
    
        $values[] = $id;
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($values);
    }

    public static function getPortfolios($userId) {
        $stmt = self::db()->prepare("SELECT * FROM portfolios WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function verifyPassword($userId, $password) {
        $stmt = self::db()->prepare("SELECT password FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return false;
        return password_verify($password, $user['password']);
    }
}