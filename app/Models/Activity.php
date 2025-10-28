<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Activity extends Model
{
    protected static $table = 'activities';

    public static function getAll()
    {
        $stmt = self::db()->prepare("
            SELECT a.*, 
                u.full_name AS full_name
            FROM activities a
                LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRecent($limit = 5)
    {
        // ✅ Always cast to int to prevent SQL injection
        $limit = (int)$limit;

        $sql = "
            SELECT a.*, u.full_name 
            FROM " . static::$table . " a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT $limit
        ";

        $stmt = self::db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function log($userId, $action, $message)
    {
        $stmt = self::db()->prepare("INSERT INTO " . static::$table . " (user_id, action, message) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $action, $message]);
    }

    public static function getWeeklyStats()
    {
        $stmt = self::db()->prepare("
            SELECT DATE(created_at) AS day, COUNT(*) AS total
            FROM activities
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY day
            ORDER BY day ASC
        ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
