<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class PasswordReset extends Model
{
    protected static $table = 'password_resets';

    /**
     * Create or update password reset code for user
     */
    public static function createOrUpdate($userId, $code)
    {
        $db = self::db();
        
        // Check if code exists for this user
        $stmt = $db->prepare("SELECT id FROM " . static::$table . " WHERE user_id = ? AND expires_at > NOW()");
        $stmt->execute([$userId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing code
            $stmt = $db->prepare("
                UPDATE " . static::$table . " 
                SET code = ?, expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE), attempts = 0, created_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$code, $existing['id']]);
        } else {
            // Create new code
            $stmt = $db->prepare("
                INSERT INTO " . static::$table . " (user_id, code, expires_at, attempts, created_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), 0, NOW())
            ");
            return $stmt->execute([$userId, $code]);
        }
    }

    /**
     * Verify reset code
     */
    public static function verifyCode($userId, $code)
    {
        $stmt = self::db()->prepare("
            SELECT * FROM " . static::$table . " 
            WHERE user_id = ? 
            AND code = ? 
            AND expires_at > NOW()
            AND attempts < 5
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId, $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Increment attempts
     */
    public static function incrementAttempts($userId, $code)
    {
        $stmt = self::db()->prepare("
            UPDATE " . static::$table . " 
            SET attempts = attempts + 1
            WHERE user_id = ? AND code = ?
        ");
        return $stmt->execute([$userId, $code]);
    }

    /**
     * Delete used or expired codes
     */
    public static function deleteCode($userId, $code)
    {
        $stmt = self::db()->prepare("
            DELETE FROM " . static::$table . " 
            WHERE user_id = ? AND code = ?
        ");
        return $stmt->execute([$userId, $code]);
    }

    /**
     * Clean expired codes
     */
    public static function cleanExpired()
    {
        $stmt = self::db()->prepare("
            DELETE FROM " . static::$table . " 
            WHERE expires_at < NOW() OR attempts >= 5
        ");
        return $stmt->execute();
    }

    /**
     * Get active code for user
     */
    public static function getActiveCode($userId)
    {
        $stmt = self::db()->prepare("
            SELECT * FROM " . static::$table . " 
            WHERE user_id = ? 
            AND expires_at > NOW()
            AND attempts < 5
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

