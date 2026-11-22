<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SlaConfig extends Model
{
    public function getByPriorityId(int $priorityId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM sla_configs 
            WHERE priority_id = :priority_id AND is_active = 1 
            LIMIT 1
        ");
        $stmt->execute(['priority_id' => $priorityId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT s.*, p.name as priority_name, p.code as priority_code, p.color as priority_color
            FROM sla_configs s
            JOIN ticket_priorities p ON s.priority_id = p.id
            ORDER BY p.level ASC
        ");
        return $stmt->fetchAll();
    }
}

