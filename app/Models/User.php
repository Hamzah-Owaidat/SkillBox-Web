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

}
