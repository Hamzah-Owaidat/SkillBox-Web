<?php
namespace App\Core;

use PDO;

class Model {
    protected static $db;

    public static function db() {
        if (!self::$db) {
            $config = include __DIR__ . '/../../config/database.php';

            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            self::$db = new PDO($dsn, $config['username'], $config['password']);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$db;
    }
}
