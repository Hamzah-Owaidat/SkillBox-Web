<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Service extends Model
{
    protected static $table = 'services';

    public static function findById($id)
    {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        return self::findById($id);
    }

    public static function create(array $data)
    {
        $stmt = self::db()->prepare("
            INSERT INTO " . static::$table . " 
            (title, image, description, created_by) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['image'],
            $data['description'],
            $data['created_by'] ?? null
        ]);
        return self::db()->lastInsertId();
    }

    public static function update($id, array $data)
    {
        $fields = [];
        $values = [];

        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $values[] = $data['title'];
        }

        if (isset($data['image'])) {
            $fields[] = "image = ?";
            $values[] = $data['image'];
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

    public static function delete($id)
    {
        $stmt = self::db()->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getAll()
    {
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

    public static function paginate($limit = 10, $page = 1, $search = '', $sortBy = 'id', $sortOrder = 'ASC')
    {
        $offset = ($page - 1) * $limit;

        // Build WHERE clause
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(s.title LIKE ? OR s.description LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Validate sort parameters
        $allowedSortColumns = ['id', 'title', 'created_at', 'updated_at'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM services s {$whereClause}";
        $countStmt = self::db()->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated data
        $sql = "
            SELECT s.*, 
                c.full_name AS created_by_name,
                u.full_name AS updated_by_name
            FROM services s
            LEFT JOIN users c ON s.created_by = c.id
            LEFT JOIN users u ON s.updated_by = u.id
            {$whereClause}
            ORDER BY s.{$sortBy} {$sortOrder}
            LIMIT ? OFFSET ?
        ";

        $stmt = self::db()->prepare($sql);

        // Bind search params
        foreach ($params as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }

        // Bind pagination params
        $stmt->bindValue(count($params) + 1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $services,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    public static function getCount()
    {
        $stmt = self::db()->query("SELECT COUNT(*) as total FROM " . static::$table);
        return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
    }

    public static function getRecent($limit = 5)
    {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getWorkers($serviceId)
    {
        $stmt = self::db()->prepare("
            SELECT 
                u.id, 
                u.full_name,
                u.email, 
                p.phone, 
                p.linkedin,
                p.attachment_path AS cv
            FROM users u
            INNER JOIN portfolios p ON p.user_id = u.id AND p.status = 'approved'
            INNER JOIN service_workers sw ON sw.user_id = u.id
            WHERE sw.service_id = :service_id
        ");
        $stmt->execute([':service_id' => $serviceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMonthlyStats()
    {

        $stmt = self::db()->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
        FROM services
        GROUP BY month
        ORDER BY month ASC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
