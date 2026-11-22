<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Category extends Model
{
    public function getAllActive(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, g.name as default_group_name,
                   (SELECT COUNT(*) FROM ticket_services WHERE category_id = c.id AND is_active = 1) as service_count
            FROM ticket_categories c
            LEFT JOIN it_groups g ON c.default_group_id = g.id
            WHERE c.is_active = 1
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM ticket_categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getServicesByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM ticket_services 
            WHERE category_id = :cat_id AND is_active = 1 
            ORDER BY name ASC
        ");
        $stmt->execute(['cat_id' => $categoryId]);
        return $stmt->fetchAll();
    }

    public function findServiceById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM ticket_services WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}

