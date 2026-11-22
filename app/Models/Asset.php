<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Asset extends Model
{
    /**
     * Get aggregate asset counts for dashboard cards
     */
    public function getKPIs(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'IN_USE' THEN 1 ELSE 0 END) as in_use,
                SUM(CASE WHEN status = 'IN_STOCK' THEN 1 ELSE 0 END) as in_stock,
                SUM(CASE WHEN status = 'REPAIR' THEN 1 ELSE 0 END) as in_repair,
                SUM(CASE WHEN status IN ('LIQUIDATED', 'DISPOSED') THEN 1 ELSE 0 END) as liquidated
            FROM assets
        ");
        return $stmt->fetch() ?: [
            'total' => 0,
            'in_use' => 0,
            'in_stock' => 0,
            'in_repair' => 0,
            'liquidated' => 0
        ];
    }

    /**
     * Get filtered list of assets
     */
    public function getAll(array $filters = []): array
    {
        $sql = "
            SELECT a.*, 
                   ac.name as category_name, ac.code as category_code, ac.icon as category_icon,
                   u.full_name as current_user_name, u.email as current_user_email, u.department as user_department
            FROM assets a
            JOIN asset_categories ac ON a.category_id = ac.id
            LEFT JOIN users u ON a.current_user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (a.asset_code LIKE :search OR a.name LIKE :search OR a.serial_number LIKE :search OR a.model LIKE :search OR u.full_name LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND a.current_user_id = :user_id";
            $params['user_id'] = (int)$filters['user_id'];
        }

        $sql .= " ORDER BY a.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find asset by ID with relations
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   ac.name as category_name, ac.code as category_code, ac.icon as category_icon,
                   u.full_name as current_user_name, u.email as current_user_email, u.phone as current_user_phone, u.department as user_department
            FROM assets a
            JOIN asset_categories ac ON a.category_id = ac.id
            LEFT JOIN users u ON a.current_user_id = u.id
            WHERE a.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $asset = $stmt->fetch();
        return $asset ?: null;
    }

    /**
     * Find asset by asset_code
     */
    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   ac.name as category_name, ac.code as category_code,
                   u.full_name as current_user_name, u.email as current_user_email
            FROM assets a
            JOIN asset_categories ac ON a.category_id = ac.id
            LEFT JOIN users u ON a.current_user_id = u.id
            WHERE a.asset_code = :code
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $asset = $stmt->fetch();
        return $asset ?: null;
    }

    /**
     * Create new asset
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO assets (
                asset_code, name, category_id, manufacturer, model, serial_number,
                purchase_date, warranty_expire, status, department, location,
                current_user_id, specifications, notes
            ) VALUES (
                :asset_code, :name, :category_id, :manufacturer, :model, :serial_number,
                :purchase_date, :warranty_expire, :status, :department, :location,
                :current_user_id, :specifications, :notes
            )
        ");

        $stmt->execute([
            'asset_code'      => $data['asset_code'],
            'name'            => $data['name'],
            'category_id'     => $data['category_id'],
            'manufacturer'    => $data['manufacturer'] ?? null,
            'model'           => $data['model'] ?? null,
            'serial_number'   => $data['serial_number'] ?? null,
            'purchase_date'   => !empty($data['purchase_date']) ? $data['purchase_date'] : null,
            'warranty_expire' => !empty($data['warranty_expire']) ? $data['warranty_expire'] : null,
            'status'          => $data['status'] ?? 'IN_STOCK',
            'department'      => $data['department'] ?? null,
            'location'        => $data['location'] ?? null,
            'current_user_id' => !empty($data['current_user_id']) ? (int)$data['current_user_id'] : null,
            'specifications'  => $data['specifications'] ?? null,
            'notes'           => $data['notes'] ?? null,
        ]);

        $assetId = (int)$this->db->lastInsertId();

        // If assigned initially, create handover record
        if (!empty($data['current_user_id'])) {
            $this->logHandover([
                'asset_id'      => $assetId,
                'action_type'   => 'HANDOVER',
                'from_user_id'  => null,
                'to_user_id'    => (int)$data['current_user_id'],
                'from_location' => 'Kho IT',
                'to_location'   => $data['location'] ?? 'Văn phòng',
                'reason'        => 'Khởi tạo tài sản và cấp phát ban đầu',
                'performed_by'  => $data['created_by_user_id'] ?? 1
            ]);
        }

        return $assetId;
    }

    /**
     * Update asset metadata
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE assets SET
                name = :name,
                category_id = :category_id,
                manufacturer = :manufacturer,
                model = :model,
                serial_number = :serial_number,
                purchase_date = :purchase_date,
                warranty_expire = :warranty_expire,
                department = :department,
                location = :location,
                specifications = :specifications,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'name'            => $data['name'],
            'category_id'     => $data['category_id'],
            'manufacturer'    => $data['manufacturer'] ?? null,
            'model'           => $data['model'] ?? null,
            'serial_number'   => $data['serial_number'] ?? null,
            'purchase_date'   => !empty($data['purchase_date']) ? $data['purchase_date'] : null,
            'warranty_expire' => !empty($data['warranty_expire']) ? $data['warranty_expire'] : null,
            'department'      => $data['department'] ?? null,
            'location'        => $data['location'] ?? null,
            'specifications'  => $data['specifications'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'id'              => $id
        ]);
    }

    /**
     * Handover asset to an employee (Rule 53)
     */
    public function handover(int $assetId, int $toUserId, string $toLocation, string $reason, int $performedBy): void
    {
        $asset = $this->findById($assetId);
        if (!$asset) {
            throw new \Exception("Không tìm thấy tài sản #{$assetId}");
        }

        $this->beginTransaction();
        try {
            // 1. Update asset current_user_id, status = IN_USE, and location
            $stmt = $this->db->prepare("
                UPDATE assets SET
                    current_user_id = :user_id,
                    status = 'IN_USE',
                    location = :location,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'user_id'  => $toUserId,
                'location' => $toLocation,
                'id'       => $assetId
            ]);

            // 2. Insert immutable handover audit record
            $this->logHandover([
                'asset_id'      => $assetId,
                'action_type'   => 'HANDOVER',
                'from_user_id'  => $asset['current_user_id'],
                'to_user_id'    => $toUserId,
                'from_location' => $asset['location'],
                'to_location'   => $toLocation,
                'reason'        => $reason,
                'performed_by'  => $performedBy
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Recall asset back to IT stock (Rule 53)
     */
    public function recall(int $assetId, string $toLocation, string $reason, int $performedBy): void
    {
        $asset = $this->findById($assetId);
        if (!$asset) {
            throw new \Exception("Không tìm thấy tài sản #{$assetId}");
        }

        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE assets SET
                    current_user_id = NULL,
                    status = 'IN_STOCK',
                    location = :location,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'location' => $toLocation ?: 'Kho IT',
                'id'       => $assetId
            ]);

            $this->logHandover([
                'asset_id'      => $assetId,
                'action_type'   => 'RECALL',
                'from_user_id'  => $asset['current_user_id'],
                'to_user_id'    => NULL,
                'from_location' => $asset['location'],
                'to_location'   => $toLocation ?: 'Kho IT',
                'reason'        => $reason,
                'performed_by'  => $performedBy
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Transfer asset between users/departments (Rule 53)
     */
    public function transfer(int $assetId, ?int $toUserId, string $toLocation, string $reason, int $performedBy): void
    {
        $asset = $this->findById($assetId);
        if (!$asset) {
            throw new \Exception("Không tìm thấy tài sản #{$assetId}");
        }

        $this->beginTransaction();
        try {
            $newStatus = $toUserId ? 'IN_USE' : 'IN_STOCK';
            $stmt = $this->db->prepare("
                UPDATE assets SET
                    current_user_id = :user_id,
                    status = :status,
                    location = :location,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'user_id'  => $toUserId,
                'status'   => $newStatus,
                'location' => $toLocation,
                'id'       => $assetId
            ]);

            $this->logHandover([
                'asset_id'      => $assetId,
                'action_type'   => 'TRANSFER',
                'from_user_id'  => $asset['current_user_id'],
                'to_user_id'    => $toUserId,
                'from_location' => $asset['location'],
                'to_location'   => $toLocation,
                'reason'        => $reason,
                'performed_by'  => $performedBy
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Add repair history (Rule 54)
     */
    public function addRepair(array $data): int
    {
        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO asset_repairs (
                    asset_id, repair_date, issue_description, root_cause,
                    solution, replaced_parts, cost, vendor, performed_by, notes
                ) VALUES (
                    :asset_id, :repair_date, :issue_description, :root_cause,
                    :solution, :replaced_parts, :cost, :vendor, :performed_by, :notes
                )
            ");

            $stmt->execute([
                'asset_id'          => (int)$data['asset_id'],
                'repair_date'       => $data['repair_date'] ?: date('Y-m-d'),
                'issue_description' => $data['issue_description'],
                'root_cause'        => $data['root_cause'] ?? null,
                'solution'          => $data['solution'] ?? null,
                'replaced_parts'    => $data['replaced_parts'] ?? null,
                'cost'              => (float)($data['cost'] ?? 0),
                'vendor'            => $data['vendor'] ?? null,
                'performed_by'      => (int)$data['performed_by'],
                'notes'             => $data['notes'] ?? null,
            ]);

            $repairId = (int)$this->db->lastInsertId();

            // Optionally change status to REPAIR if specified
            if (!empty($data['set_status_repair'])) {
                $this->db->prepare("UPDATE assets SET status = 'REPAIR', updated_at = NOW() WHERE id = :id")
                         ->execute(['id' => (int)$data['asset_id']]);
            }

            $this->commit();
            return $repairId;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Mark repair finished, change status to IN_STOCK or IN_USE
     */
    public function completeRepair(int $assetId, string $nextStatus = 'IN_STOCK'): void
    {
        $stmt = $this->db->prepare("UPDATE assets SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['status' => $nextStatus, 'id' => $assetId]);
    }

    /**
     * Change asset status (e.g. DAMAGED, LOST, LIQUIDATED, DISPOSED) with immutable audit
     */
    public function changeStatus(int $assetId, string $newStatus, string $reason, int $performedBy): void
    {
        $asset = $this->findById($assetId);
        if (!$asset) {
            throw new \Exception("Không tìm thấy tài sản #{$assetId}");
        }

        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE assets SET status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute(['status' => $newStatus, 'id' => $assetId]);

            $this->logHandover([
                'asset_id'      => $assetId,
                'action_type'   => in_array($newStatus, ['LIQUIDATED', 'DISPOSED']) ? 'DISPOSAL' : 'STATUS_CHANGE',
                'from_user_id'  => $asset['current_user_id'],
                'to_user_id'    => in_array($newStatus, ['LOST', 'LIQUIDATED', 'DISPOSED']) ? null : $asset['current_user_id'],
                'from_location' => $asset['location'],
                'to_location'   => $asset['location'],
                'reason'        => "Đổi trạng thái sang {$newStatus}: {$reason}",
                'performed_by'  => $performedBy
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Get handover history for asset (Rule 53)
     */
    public function getHandovers(int $assetId): array
    {
        $stmt = $this->db->prepare("
            SELECT h.*,
                   fu.full_name as from_user_name,
                   tu.full_name as to_user_name,
                   pu.full_name as performer_name
            FROM asset_handovers h
            LEFT JOIN users fu ON h.from_user_id = fu.id
            LEFT JOIN users tu ON h.to_user_id = tu.id
            JOIN users pu ON h.performed_by = pu.id
            WHERE h.asset_id = :asset_id
            ORDER BY h.handover_date DESC, h.id DESC
        ");
        $stmt->execute(['asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    /**
     * Get repair history for asset (Rule 54)
     */
    public function getRepairs(int $assetId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   pu.full_name as performer_name
            FROM asset_repairs r
            JOIN users pu ON r.performed_by = pu.id
            WHERE r.asset_id = :asset_id
            ORDER BY r.repair_date DESC, r.id DESC
        ");
        $stmt->execute(['asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    /**
     * Get linked tickets for asset (Rule 55)
     */
    public function getTickets(int $assetId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT t.id, t.ticket_code, t.title, t.status, t.created_at,
                            pri.name as priority_name, pri.color as priority_color,
                            ws.name as status_name, ws.badge_color as status_color,
                            req.full_name as requester_name
            FROM tickets t
            LEFT JOIN ticket_assets ta ON t.id = ta.ticket_id
            JOIN ticket_priorities pri ON t.priority_id = pri.id
            LEFT JOIN workflow_statuses ws ON t.status = ws.code
            JOIN users req ON t.requester_id = req.id
            WHERE t.asset_id = :aid1 OR ta.asset_id = :aid2
            ORDER BY t.id DESC
        ");
        $stmt->execute(['aid1' => $assetId, 'aid2' => $assetId]);
        return $stmt->fetchAll();
    }

    /**
     * Get assets assigned to specific user (User's assets)
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   ac.name as category_name, ac.code as category_code, ac.icon as category_icon
            FROM assets a
            JOIN asset_categories ac ON a.category_id = ac.id
            WHERE a.current_user_id = :user_id
            ORDER BY a.id DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Generate next sequential asset code: AST-LAP-005, etc.
     */
    public function generateAssetCode(int $categoryId): string
    {
        $catStmt = $this->db->prepare("SELECT code FROM asset_categories WHERE id = :id LIMIT 1");
        $catStmt->execute(['id' => $categoryId]);
        $catCode = $catStmt->fetchColumn() ?: 'GEN';
        $prefix = 'AST-' . substr($catCode, 0, 3) . '-';

        $stmt = $this->db->prepare("SELECT asset_code FROM assets WHERE asset_code LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastCode = $stmt->fetchColumn();

        if ($lastCode && preg_match('/-(\d+)$/', $lastCode, $m)) {
            $nextNum = (int)$m[1] + 1;
        } else {
            $nextNum = 1;
        }

        return sprintf("%s%03d", $prefix, $nextNum);
    }

    private function logHandover(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO asset_handovers (
                asset_id, action_type, from_user_id, to_user_id,
                from_location, to_location, reason, performed_by, handover_date
            ) VALUES (
                :asset_id, :action_type, :from_user_id, :to_user_id,
                :from_location, :to_location, :reason, :performed_by, NOW()
            )
        ");
        $stmt->execute([
            'asset_id'      => $data['asset_id'],
            'action_type'   => $data['action_type'],
            'from_user_id'  => $data['from_user_id'],
            'to_user_id'    => $data['to_user_id'],
            'from_location' => $data['from_location'] ?? null,
            'to_location'   => $data['to_location'] ?? null,
            'reason'        => $data['reason'] ?? null,
            'performed_by'  => $data['performed_by']
        ]);
    }
}

