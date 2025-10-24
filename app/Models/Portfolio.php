<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Portfolio extends Model
{
    protected static $table = 'portfolios';

    public static function create(array $data) {
        $stmt = self::db()->prepare("
            INSERT INTO " . static::$table . " 
                (user_id, full_name, email, phone, address, linkedin, attachment_path, requested_role)
            VALUES 
                (:user_id, :full_name, :email, :phone, :address, :linkedin, :attachment_path, :requested_role)
        ");

        $stmt->execute([
            ':user_id'         => $data['user_id'],
            ':full_name'       => $data['full_name'],
            ':email'           => $data['email'],
            ':phone'           => $data['phone'],
            ':address'         => $data['address'],
            ':linkedin'        => $data['linkedin'],
            ':attachment_path' => $data['attachment_path'] ?? null,
            ':requested_role'  => $data['requested_role']
        ]);

        return self::db()->lastInsertId();
    }

    public static function findById($id) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findPendingByUser($id, $userId) {
        $stmt = self::db()->prepare("
            SELECT * FROM " . static::$table . "
            WHERE id = :id AND user_id = :user_id AND status = 'pending'
        ");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function deletePendingByUser($id, $userId) {
        $stmt = self::db()->prepare("
            DELETE FROM " . static::$table . "
            WHERE id = :id AND user_id = :user_id AND status = 'pending'
        ");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function updatePendingByUser($id, $userId, array $data) {
        $fields = [];
        $params = [];

        foreach (['full_name', 'email', 'phone', 'address', 'linkedin', 'requested_role', 'attachment_path'] as $col) {
            if (isset($data[$col])) {
                $fields[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        $params[':id'] = $id;
        $params[':user_id'] = $userId;

        if (empty($fields)) return false;

        $sql = "
            UPDATE " . static::$table . "
            SET " . implode(', ', $fields) . ", updated_at = NOW()
            WHERE id = :id AND user_id = :user_id AND status = 'pending'
        ";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public static function getByUser($userId) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll() {
        $stmt = self::db()->prepare("
            SELECT 
            p.id,
            p.user_id,
            p.full_name,
            p.email,
            p.phone,
            p.address,
            p.linkedin,
            p.attachment_path,
            p.status,
            p.created_at,
            p.updated_at,
            p.reviewed_at,
            u.full_name AS user_name,
            r.name AS requested_role_name,
            reviewer.full_name AS reviewed_by_name
        FROM portfolios p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN roles r ON p.requested_role = r.id
        LEFT JOIN users reviewer ON p.reviewed_by = reviewer.id
        ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach services to each portfolio
        foreach ($portfolios as &$portfolio) {
            $portfolio['services'] = self::getServices($portfolio['id']);
        }

        return $portfolios;
    }

    public static function paginate($limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;

        $stmt = self::db()->prepare("
            SELECT  p.id,
                    p.user_id,
                    p.full_name,
                    p.email,
                    p.phone,
                    p.address,
                    p.linkedin,
                    p.attachment_path,
                    p.requested_role,
                    r.name AS requested_role,
                    p.status,
                    p.created_at,
                    p.updated_at,
                    u.full_name AS user_name
            FROM portfolios p
                LEFT JOIN roles r ON p.requested_role = r.id
                LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach services to each portfolio
        foreach ($portfolios as &$portfolio) {
            $portfolio['services'] = self::getServices($portfolio['id']);
        }

        $countStmt = self::db()->query("SELECT COUNT(*) as total FROM " . static::$table);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'data' => $portfolios,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    // ✅ Approve portfolio and update user role
    public static function approve($id, $requestedRole, $userId, $adminId) {
        $db = self::db();
        $db->beginTransaction();

        try {
            // Update portfolio status + reviewer info
            $stmt = $db->prepare("
                UPDATE " . static::$table . "
                SET 
                    status = 'approved',
                    reviewed_by = :reviewed_by,
                    reviewed_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':reviewed_by' => $adminId,
                ':id' => $id
            ]);

            // Update user role
            $updateUser = $db->prepare("
                UPDATE users 
                SET role_id = :role_id 
                WHERE id = :user_id
            ");
            $updateUser->execute([
                ':role_id' => $requestedRole,
                ':user_id' => $userId
            ]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Portfolio approval failed: " . $e->getMessage());
            return false;
        }
    }

    // ✅ Reject portfolio
    public static function reject($id, $adminId) {
        $stmt = self::db()->prepare("
            UPDATE " . static::$table . "
            SET 
                status = 'rejected',
                reviewed_by = :reviewed_by,
                reviewed_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':reviewed_by' => $adminId,
            ':id' => $id
        ]);
    }

    // ✅ Reset portfolio back to pending (optional)
    public static function reset($id) {
        $stmt = self::db()->prepare("UPDATE " . static::$table . " SET status = 'pending', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ✅ Find by ID
    public static function find($id) {
        $stmt = self::db()->prepare("SELECT * FROM " . static::$table . " WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Delete portfolio
    public static function delete($id) {
        $stmt = self::db()->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ✅ Attach one or multiple services to a portfolio
    public static function attachServices($portfolioId, array $serviceIds) {
        if (empty($serviceIds)) return;

        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO portfolio_services (portfolio_id, service_id)
            VALUES (:portfolio_id, :service_id)
        ");

        foreach ($serviceIds as $serviceId) {
            $stmt->execute([
                ':portfolio_id' => $portfolioId,
                ':service_id' => $serviceId
            ]);
        }
    }
    
    // ✅ Remove and re-add services (used for updates)
    public static function syncServices($portfolioId, array $serviceIds) {
        $db = self::db();
        $db->prepare("DELETE FROM portfolio_services WHERE portfolio_id = :id")
        ->execute([':id' => $portfolioId]);
        self::attachServices($portfolioId, $serviceIds);
    }

    // ✅ Get all services attached to a specific portfolio
    public static function getServices($portfolioId)
    {
        $stmt = self::db()->prepare("
            SELECT s.*
            FROM services s
            INNER JOIN portfolio_services ps ON ps.service_id = s.id
            WHERE ps.portfolio_id = :portfolio_id
        ");
        $stmt->execute([':portfolio_id' => $portfolioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
