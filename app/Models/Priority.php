<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Priority extends Model
{
    public function getAllActive(): array
    {
        $stmt = $this->db->query("
            SELECT p.*, s.id as sla_id, s.resolution_time, s.resolution_unit, s.warning_threshold_percent
            FROM ticket_priorities p
            LEFT JOIN sla_configs s ON p.id = s.priority_id AND s.is_active = 1
            WHERE p.is_active = 1
            ORDER BY p.level ASC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, s.id as sla_id, s.resolution_time, s.resolution_unit 
            FROM ticket_priorities p 
            LEFT JOIN sla_configs s ON p.id = s.priority_id AND s.is_active = 1
            WHERE p.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}

