<?php
// app/Models/Role.php
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

    // ✅ Find role by ID
    public static function findById($id) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
