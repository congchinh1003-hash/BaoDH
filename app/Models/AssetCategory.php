<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AssetCategory extends Model
{
    public function getAllActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM asset_categories WHERE is_active = 1 ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM asset_categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}

