<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Role extends Model {
    protected static $table = 'roles';

    public static function findByName($name) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findById($id) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Alias for consistency
    public static function find($id) {
        return self::findById($id);
    }

    public static function create(array $data) {
        $stmt = self::db()->prepare("
            INSERT INTO " . static::$table . " 
            (name, created_by) 
            VALUES (?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['created_by'] ?? null
        ]);
        return self::db()->lastInsertId();
    }

    public static function update($id, array $data) {
        $fields = [];
        $values = [];

        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }

        // 👇 Add updated_by tracking
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

    public static function getAll() {
        $sql = "
            SELECT 
                r.*, 
                u1.full_name AS created_by,
                u2.full_name AS updated_by
            FROM roles r
            LEFT JOIN users u1 ON r.created_by = u1.id
            LEFT JOIN users u2 ON r.updated_by = u2.id
            ORDER BY r.id ASC
        ";
        
        $stmt = self::db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function paginate($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;

        $stmt = self::db()->prepare("SELECT COUNT(*) as total FROM " . static::$table);
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " ORDER BY id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $roles,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }
}