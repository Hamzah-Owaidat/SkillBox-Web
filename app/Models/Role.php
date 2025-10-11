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

    // ✅ Paginate roles
    public static function paginate($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;

        // Total count
        $stmt = self::db()->prepare("SELECT COUNT(*) as total FROM " . static::$table);
        $stmt->execute();
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Fetch roles
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " ORDER BY id DESC LIMIT :limit OFFSET :offset");
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
