<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ChangeRequest extends Model
{
    /**
     * Generate unique Change code CHG-000001
     */
    public function generateChangeCode(): string
    {
        $stmt = $this->db->query("SELECT id FROM change_requests ORDER BY id DESC LIMIT 1");
        $lastId = (int)$stmt->fetchColumn();
        return sprintf("CHG-%06d", $lastId + 1);
    }

    /**
     * Create Change Request (RFC)
     */
    public function create(array $data): int
    {
        $code = $this->generateChangeCode();

        $stmt = $this->db->prepare("
            INSERT INTO change_requests (
                change_code, title, description, reason, change_type, risk_level,
                environment_stage, status, implementation_plan, rollback_plan, test_plan,
                scheduled_start, scheduled_end, requested_by, assigned_to, created_at
            ) VALUES (
                :code, :title, :description, :reason, :change_type, :risk_level,
                :stage, :status, :impl_plan, :rb_plan, :test_plan,
                :s_start, :s_end, :req_by, :assigned_to, NOW()
            )
        ");

        $stmt->execute([
            'code'        => $code,
            'title'       => $data['title'],
            'description' => $data['description'],
            'reason'      => $data['reason'],
            'change_type' => $data['change_type'] ?? 'NORMAL',
            'risk_level'  => $data['risk_level'] ?? 'MEDIUM',
            'stage'       => $data['environment_stage'] ?? 'DEV',
            'status'      => $data['status'] ?? 'DRAFT',
            'impl_plan'   => $data['implementation_plan'] ?? null,
            'rb_plan'     => $data['rollback_plan'] ?? null,
            'test_plan'   => $data['test_plan'] ?? null,
            's_start'     => !empty($data['scheduled_start']) ? $data['scheduled_start'] : null,
            's_end'       => !empty($data['scheduled_end']) ? $data['scheduled_end'] : null,
            'req_by'      => $data['requested_by'],
            'assigned_to' => !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null
        ]);

        $changeId = (int)$this->db->lastInsertId();

        // If tickets to link
        if (!empty($data['ticket_ids']) && is_array($data['ticket_ids'])) {
            foreach ($data['ticket_ids'] as $tid) {
                $this->linkTicket($changeId, (int)$tid);
            }
        }

        return $changeId;
    }

    /**
     * Find Change by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT cr.*,
                   req.full_name as requester_name, req.email as requester_email,
                   ass.full_name as assignee_name, ass.email as assignee_email,
                   cab.full_name as cab_approver_name
            FROM change_requests cr
            JOIN users req ON cr.requested_by = req.id
            LEFT JOIN users ass ON cr.assigned_to = ass.id
            LEFT JOIN users cab ON cr.cab_approved_by = cab.id
            WHERE cr.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all changes with filters
     */
    public function getAll(array $filters = []): array
    {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND cr.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['stage'])) {
            $where .= " AND cr.environment_stage = :stage";
            $params['stage'] = $filters['stage'];
        }
        if (!empty($filters['risk_level'])) {
            $where .= " AND cr.risk_level = :risk";
            $params['risk'] = $filters['risk_level'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (cr.change_code LIKE :q OR cr.title LIKE :q2)";
            $params['q'] = '%' . $filters['search'] . '%';
            $params['q2'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare("
            SELECT cr.*,
                   req.full_name as requester_name,
                   ass.full_name as assignee_name,
                   COUNT(ct.ticket_id) as linked_tickets_count
            FROM change_requests cr
            JOIN users req ON cr.requested_by = req.id
            LEFT JOIN users ass ON cr.assigned_to = ass.id
            LEFT JOIN change_tickets ct ON cr.id = ct.change_id
            {$where}
            GROUP BY cr.id
            ORDER BY cr.id DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Update environment stage (DEV -> TEST -> MIG_UAT -> RELEASE_PROD)
     */
    public function updateStage(int $id, string $stage, ?int $userId = null, ?string $note = null): bool
    {
        $validStages = ['DEV', 'TEST', 'MIG_UAT', 'RELEASE_PROD'];
        if (!in_array($stage, $validStages)) {
            throw new \Exception("Môi trường triển khai không hợp lệ.");
        }

        $status = 'IMPLEMENTING';
        if ($stage === 'TEST') {
            $status = 'TESTING_UAT';
        } elseif ($stage === 'RELEASE_PROD') {
            $status = 'RELEASE_PROD';
        }

        $stmt = $this->db->prepare("
            UPDATE change_requests 
            SET environment_stage = :stage, status = :status, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            'stage'  => $stage,
            'status' => $status,
            'id'     => $id
        ]);
    }

    /**
     * Alias for updateStage
     */
    public function advanceStage(int $id, string $stage, ?string $note = null): bool
    {
        return $this->updateStage($id, $stage, null, $note);
    }

    /**
     * CAB Approval decision
     */
    public function cabApprove(int $id, int $userId, string $decision, ?string $note = null): bool
    {
        $status = ($decision === 'APPROVED') ? 'SCHEDULED' : 'CANCELLED';

        $stmt = $this->db->prepare("
            UPDATE change_requests 
            SET status = :status, cab_approved_by = :uid, cab_approved_at = NOW(), cab_note = :note, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'status' => $status,
            'uid'    => $userId,
            'note'   => $note,
            'id'     => $id
        ]);
    }

    /**
     * Mark Change as Successful, Failed, or Rolled Back
     */
    public function completeChange(int $id, mixed $outcome, ?string $note = null): bool
    {
        if (is_bool($outcome)) {
            $status = $outcome ? 'SUCCESSFUL' : 'ROLLED_BACK';
        } else {
            $outStr = strtoupper((string)$outcome);
            $status = in_array($outStr, ['SUCCESSFUL', 'FAILED', 'ROLLED_BACK', 'CANCELLED']) ? $outStr : 'SUCCESSFUL';
        }

        $stmt = $this->db->prepare("
            UPDATE change_requests 
            SET status = :status, actual_end = NOW(), updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            'status' => $status,
            'id'     => $id
        ]);
    }

    /**
     * Link ticket
     */
    public function linkTicket(int $changeId, int $ticketId): bool
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO change_tickets (change_id, ticket_id, created_at)
            VALUES (:cid, :tid, NOW())
        ");
        $res = $stmt->execute(['cid' => $changeId, 'tid' => $ticketId]);

        $this->db->prepare("UPDATE tickets SET change_id = :cid WHERE id = :tid")
                 ->execute(['cid' => $changeId, 'tid' => $ticketId]);

        return $res;
    }

    /**
     * Get linked tickets
     */
    public function getLinkedTickets(int $changeId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.id, t.id as ticket_id, t.ticket_code, t.title, t.status, t.ticket_type, ct.created_at as linked_at,
                   u.full_name as requester_name,
                   pri.name as priority_name, pri.color as priority_color
            FROM change_tickets ct
            JOIN tickets t ON ct.ticket_id = t.id
            JOIN users u ON t.requester_id = u.id
            JOIN ticket_priorities pri ON t.priority_id = pri.id
            WHERE ct.change_id = :cid
            ORDER BY t.id DESC
        ");
        $stmt->execute(['cid' => $changeId]);
        return $stmt->fetchAll();
    }
}
