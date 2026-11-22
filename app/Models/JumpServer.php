<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class JumpServer extends Model
{
    /**
     * Get servers accessible to a user. Admins see all active servers.
     */
    public function getAuthorizedServers(int $userId, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            $stmt = $this->db->query("
                SELECT s.*, 
                       curr.user_id as active_user_id,
                       curr.user_id as active_occupant,
                       u.full_name as active_user_name,
                       u.full_name as occupant_name,
                       u.username as active_username,
                       curr.started_at as session_started_at,
                       curr.started_at as occupant_started_at,
                       curr.session_token as active_session_token
                FROM jump_servers s
                LEFT JOIN jump_server_sessions curr 
                       ON s.id = curr.server_id AND curr.status = 'ACTIVE'
                LEFT JOIN users u 
                       ON curr.user_id = u.id
                WHERE s.is_active = 1
                ORDER BY s.id ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Normal User / Staff: only servers explicitly granted or granted to user's IT groups
        $stmt = $this->db->prepare("
            SELECT DISTINCT s.*,
                   curr.user_id as active_user_id,
                   curr.user_id as active_occupant,
                   u.full_name as active_user_name,
                   u.full_name as occupant_name,
                   u.username as active_username,
                   curr.started_at as session_started_at,
                   curr.started_at as occupant_started_at,
                   curr.session_token as active_session_token
            FROM jump_servers s
            JOIN jump_server_permissions p ON s.id = p.server_id
            LEFT JOIN jump_server_sessions curr 
                   ON s.id = curr.server_id AND curr.status = 'ACTIVE'
            LEFT JOIN users u 
                   ON curr.user_id = u.id
            WHERE s.is_active = 1
              AND (
                  p.user_id = :uid 
                  OR p.group_id IN (
                      SELECT group_id FROM it_group_members WHERE user_id = :uid2
                  )
              )
            ORDER BY s.id ASC
        ");
        $stmt->execute([
            'uid'  => $userId,
            'uid2' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM jump_servers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function checkPermission(int $serverId, int $userId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            return true;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM jump_server_permissions p
            WHERE p.server_id = :sid
              AND (
                  p.user_id = :uid 
                  OR p.group_id IN (
                      SELECT group_id FROM it_group_members WHERE user_id = :uid2
                  )
              )
        ");
        $stmt->execute([
            'sid'  => $serverId,
            'uid'  => $userId,
            'uid2' => $userId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT s.*, 
                   COUNT(DISTINCT p.id) as permission_count,
                   curr.user_id as active_user_id,
                   u.full_name as active_user_name,
                   curr.session_token as active_session_token
            FROM jump_servers s
            LEFT JOIN jump_server_permissions p ON s.id = p.server_id
            LEFT JOIN jump_server_sessions curr ON s.id = curr.server_id AND curr.status = 'ACTIVE'
            LEFT JOIN users u ON curr.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['os_type'])) {
            $sql .= " AND s.os_type = :os_type";
            $params['os_type'] = $filters['os_type'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (s.name LIKE :q OR s.ip_address LIKE :q OR s.description LIKE :q)";
            $params['q'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY s.id ORDER BY s.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPermissions(int $serverId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   u.full_name as user_name, u.username,
                   g.name as group_name, g.code as group_code,
                   granter.full_name as granter_name
            FROM jump_server_permissions p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN it_groups g ON p.group_id = g.id
            LEFT JOIN users granter ON p.granted_by = granter.id
            WHERE p.server_id = :sid
            ORDER BY p.id ASC
        ");
        $stmt->execute(['sid' => $serverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
