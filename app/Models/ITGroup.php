<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ITGroup extends Model
{
    public function getAllActive(): array
    {
        $stmt = $this->db->query("
            SELECT g.*, u.full_name as leader_name,
                   (SELECT COUNT(*) FROM it_group_members WHERE group_id = g.id) as member_count
            FROM it_groups g
            LEFT JOIN users u ON g.leader_id = u.id
            WHERE g.is_active = 1
            ORDER BY g.name ASC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT g.*, u.full_name as leader_name 
            FROM it_groups g 
            LEFT JOIN users u ON g.leader_id = u.id 
            WHERE g.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getMembers(int $groupId): array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.full_name, u.email, igm.is_primary
            FROM it_group_members igm
            JOIN users u ON igm.user_id = u.id
            WHERE igm.group_id = :group_id AND u.is_active = 1
            ORDER BY igm.is_primary DESC, u.full_name ASC
        ");
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll();
    }

    public function getUserGroupIds(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT group_id FROM it_group_members WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

