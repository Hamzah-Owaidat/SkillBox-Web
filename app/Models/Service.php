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
            $data['image'],
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

    public static function delete($id) {
        // First delete related supervisors from junction table
        self::deleteSupervisors($id);
        
        $stmt = self::db()->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =====================================================
    // SUPERVISOR MANAGEMENT METHODS
    // =====================================================
    
    /**
     * Get all supervisors assigned to a specific service
     * @param int $serviceId
     * @return array List of supervisors with their details
     */
    public static function getSupervisors($serviceId) {
        $stmt = self::db()->prepare("
            SELECT 
                u.id, 
                u.full_name, 
                u.email, 
                u.role_id, 
                r.name as role_name
            FROM service_supervisor ss
            INNER JOIN users u ON ss.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE ss.service_id = ?
            ORDER BY u.full_name ASC
        ");
        $stmt->execute([$serviceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assign supervisors to a service (replaces existing assignments)
     * @param int $serviceId
     * @param array $supervisorIds Array of user IDs to assign as supervisors
     * @return bool Success status
     */
    public static function assignSupervisors($serviceId, array $supervisorIds) {
        try {
            // Begin transaction for data consistency
            self::db()->beginTransaction();
            
            // Step 1: Remove all existing supervisor assignments for this service
            self::deleteSupervisors($serviceId);

            // Step 2: Insert new supervisor assignments
            if (!empty($supervisorIds)) {
                $stmt = self::db()->prepare("
                    INSERT INTO service_supervisor (service_id, user_id) 
                    VALUES (?, ?)
                ");
                
                foreach ($supervisorIds as $supervisorId) {
                    // Skip empty values
                    if (!empty($supervisorId) && is_numeric($supervisorId)) {
                        $stmt->execute([$serviceId, $supervisorId]);
                    }
                }
            }
            
            // Commit transaction
            self::db()->commit();
            return true;
            
        } catch (\Exception $e) {
            // Rollback on error
            self::db()->rollBack();
            error_log("Error assigning supervisors: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove all supervisor assignments from a service
     * @param int $serviceId
     * @return bool Success status
     */
    public static function deleteSupervisors($serviceId) {
        $stmt = self::db()->prepare("DELETE FROM service_supervisor WHERE service_id = ?");
        return $stmt->execute([$serviceId]);
    }

    /**
     * Get all users with supervisor role (for dropdown selection)
     * @return array List of supervisors available for assignment
     */
    public static function getAllSupervisors() {
        $stmt = self::db()->prepare("
            SELECT 
                u.id, 
                u.full_name, 
                u.email,
                u.status
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'supervisor' AND u.status = 'active'
            ORDER BY u.full_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a user is assigned as supervisor to a service
     * @param int $serviceId
     * @param int $userId
     * @return bool
     */
    public static function isSupervisor($serviceId, $userId) {
        $stmt = self::db()->prepare("
            SELECT COUNT(*) as count 
            FROM service_supervisor 
            WHERE service_id = ? AND user_id = ?
        ");
        $stmt->execute([$serviceId, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Get all services for a specific supervisor
     * @param int $supervisorId
     * @return array
     */
    public static function getServicesBySupervisor($supervisorId) {
        $stmt = self::db()->prepare("
            SELECT s.*
            FROM services s
            INNER JOIN service_supervisor ss ON s.id = ss.service_id
            WHERE ss.user_id = ?
            ORDER BY s.title ASC
        ");
        $stmt->execute([$supervisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================================
    // STANDARD CRUD METHODS WITH SUPERVISOR DATA
    // =====================================================

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
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach supervisors to each service
        foreach ($services as &$service) {
            $service['supervisors'] = self::getSupervisors($service['id']);
        }

        return $services;
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

        // Attach supervisors to each service
        foreach ($services as &$service) {
            $service['supervisors'] = self::getSupervisors($service['id']);
        }

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