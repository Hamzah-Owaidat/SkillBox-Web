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
        $stmt = self::db()->prepare(
            "INSERT INTO " . static::$table . " (full_name, email, password, role_id, status) VALUES (?, ?, ?, ?, 'active')"
        );
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['password'],
            $data['role_id'] ?? 2 // Default to client role
        ]);
        return self::db()->lastInsertId();
    }

    // ✅ NEW: Update user
    public static function update($id, array $data) {
        // Build dynamic query based on provided data
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

    // ✅ Toggle user status (active/inactive)
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

    // ✅ Update status specifically
    // public static function updateStatus($id, $status) {
    //     $stmt = self::db()->prepare("UPDATE " . static::$table . " SET status = ? WHERE id = ?");
    //     return $stmt->execute([$status, $id]);
    // }

    public static function getAll() {
        $stmt = self::db()->prepare("
            SELECT users.*, roles.name AS role_name
            FROM users
            LEFT JOIN roles ON users.role_id = roles.id
            ORDER BY users.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function paginate($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;

        $stmt = self::db()->prepare("
            SELECT users.*, roles.name AS role_name
            FROM users
            LEFT JOIN roles ON users.role_id = roles.id
            ORDER BY users.id ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = self::db()->query("SELECT COUNT(*) as total FROM " . static::$table);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
}