<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Service extends Model
{
    protected static $table = 'services';

    public static function findById($id) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function find($id) {
        return self::findById($id);
    }

    public static function create(array $data) {
        $stmt = self::db()->prepare("
            INSERT INTO " . static::$table . " 
            (title, image, description, created_by) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['image'], // This will be an emoji
            $data['description'],
            $data['created_by'] ?? null
        ]);
        return self::db()->lastInsertId();
    }

    public static function update($id, array $data) {
        $fields = [];
        $values = [];

        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $values[] = $data['title'];
        }

        if (isset($data['image'])) {
            $fields[] = "image = ?";
            $values[] = $data['image']; // Emoji
        }

        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $values[] = $data['description'];
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

    public static function getAll() {
        $stmt = self::db()->prepare("
            SELECT s.*, 
                c.full_name AS created_by_name, 
                u.full_name AS updated_by_name
            FROM services s
            LEFT JOIN users c ON s.created_by = c.id
            LEFT JOIN users u ON s.updated_by = u.id
            ORDER BY s.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function paginate($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;

        $stmt = self::db()->prepare("
            SELECT s.*, 
                c.full_name AS created_by_name, 
                u.full_name AS updated_by_name
            FROM services s
            LEFT JOIN users c ON s.created_by = c.id
            LEFT JOIN users u ON s.updated_by = u.id
            ORDER BY s.id ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = self::db()->query("SELECT COUNT(*) as total FROM " . static::$table);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'data' => $services,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
}