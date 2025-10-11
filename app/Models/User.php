<?php
// app/Models/User.php
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
            "INSERT INTO " . static::$table . " (full_name, email, password, role_id) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['password'],
            $data['role_id']
        ]);
        return self::db()->lastInsertId();
    }

    public static function getAll() {
        $stmt = self::db()->prepare("
            SELECT users.*, roles.name AS role_name
            FROM users
            LEFT JOIN roles ON users.role_id = roles.id
            ORDER BY users.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function paginate($limit = 10, $page = 1)
    {
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

        // Get total count of users for pagination links
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
