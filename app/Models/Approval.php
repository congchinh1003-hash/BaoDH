<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Approval extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM ticket_approvals WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get pending approvals assigned to a specific user (or if Manager/Admin, all pending)
     */
    public function getPendingForUser(int $userId, bool $isManagerOrAdmin = false): array
    {
        $where = "WHERE a.status = 'PENDING'";
        $params = [];

        if (!$isManagerOrAdmin) {
            $where .= " AND a.approver_id = :uid";
            $params['uid'] = $userId;
        }

        $stmt = $this->db->prepare("
            SELECT a.*,
                   t.ticket_code, t.title as ticket_title, t.ticket_type, t.status as ticket_status,
                   req.full_name as requester_name, req.email as requester_email,
                   cr.change_code, cr.title as change_title, cr.change_type, cr.risk_level
            FROM ticket_approvals a
            LEFT JOIN tickets t ON a.ticket_id = t.id
            LEFT JOIN users req ON t.requester_id = req.id
            LEFT JOIN change_requests cr ON a.change_id = cr.id
            {$where}
            ORDER BY a.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get all approvals with filters
     */
    public function getAll(array $filters = []): array
    {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND a.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['approver_id'])) {
            $where .= " AND a.approver_id = :approver_id";
            $params['approver_id'] = (int)$filters['approver_id'];
        }

        $stmt = $this->db->prepare("
            SELECT a.*,
                   u.full_name as approver_name,
                   t.ticket_code, t.title as ticket_title, t.ticket_type,
                   req.full_name as requester_name,
                   cr.change_code, cr.title as change_title
            FROM ticket_approvals a
            JOIN users u ON a.approver_id = u.id
            LEFT JOIN tickets t ON a.ticket_id = t.id
            LEFT JOIN users req ON t.requester_id = req.id
            LEFT JOIN change_requests cr ON a.change_id = cr.id
            {$where}
            ORDER BY a.created_at DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create approval request
     */
    public function createApproval(?int $ticketId, ?int $changeId, int $approverId, string $stepName = 'Quản lý phê duyệt'): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_approvals (ticket_id, change_id, approver_id, step_name, status, created_at)
            VALUES (:tid, :cid, :uid, :sname, 'PENDING', NOW())
        ");
        $stmt->execute([
            'tid'   => $ticketId,
            'cid'   => $changeId,
            'uid'   => $approverId,
            'sname' => $stepName
        ]);
        $approvalId = (int)$this->db->lastInsertId();

        // Update ticket approval_status if applicable
        if ($ticketId) {
            $this->db->prepare("UPDATE tickets SET approval_status = 'PENDING' WHERE id = :tid")
                     ->execute(['tid' => $ticketId]);
        }
        // Update change request status if applicable
        if ($changeId) {
            $this->db->prepare("UPDATE change_requests SET status = 'CAB_APPROVAL' WHERE id = :cid")
                     ->execute(['cid' => $changeId]);
        }

        return $approvalId;
    }

    /**
     * Decide approval (APPROVED, REJECTED, RETURNED)
     */
    public function decide(int $approvalId, int $userId, string $decision, ?string $comment = null): bool
    {
        $decision = strtoupper($decision);
        if (!in_array($decision, ['APPROVED', 'REJECTED', 'RETURNED'])) {
            throw new \Exception("Quyết định phê duyệt không hợp lệ.");
        }

        $stmt = $this->db->prepare("SELECT * FROM ticket_approvals WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $approvalId]);
        $appr = $stmt->fetch();
        if (!$appr) {
            throw new \Exception("Không tìm thấy bản ghi phê duyệt.");
        }

        $this->beginTransaction();
        try {
            // Update approval record
            $stmtUpd = $this->db->prepare("
                UPDATE ticket_approvals 
                SET status = :status, comment = :comment, decided_at = NOW()
                WHERE id = :id
            ");
            $stmtUpd->execute([
                'status'  => $decision,
                'comment' => $comment,
                'id'      => $approvalId
            ]);

            // Sync Ticket status
            if (!empty($appr['ticket_id'])) {
                $tStatus = ($decision === 'APPROVED') ? 'ASSIGNED' : (($decision === 'REJECTED') ? 'CANCELLED' : 'NEW');
                $stmtTicket = $this->db->prepare("
                    UPDATE tickets 
                    SET approval_status = :appr_st, 
                        approved_by = :uid, 
                        approved_at = NOW(), 
                        approval_note = :note,
                        status = :tstatus,
                        updated_at = NOW()
                    WHERE id = :tid
                ");
                $stmtTicket->execute([
                    'appr_st' => $decision,
                    'uid'     => $userId,
                    'note'    => $comment,
                    'tstatus' => $tStatus,
                    'tid'     => $appr['ticket_id']
                ]);

                // Record history
                $this->db->prepare("
                    INSERT INTO ticket_history (ticket_id, user_id, action, field_changed, old_value, new_value, note)
                    VALUES (:tid, :uid, 'APPROVAL', 'approval_status', :old_val, :new_val, :note)
                ")->execute([
                    'tid'     => $appr['ticket_id'],
                    'uid'     => $userId,
                    'old_val' => $appr['status'],
                    'new_val' => $decision,
                    'note'    => "Phê duyệt: {$decision} - Lý do: {$comment}"
                ]);
            }

            // Sync Change Request status
            if (!empty($appr['change_id'])) {
                $crStatus = ($decision === 'APPROVED') ? 'SCHEDULED' : (($decision === 'REJECTED') ? 'CANCELLED' : 'DRAFT');
                $stmtCR = $this->db->prepare("
                    UPDATE change_requests 
                    SET status = :cr_st, 
                        cab_approved_by = :uid, 
                        cab_approved_at = NOW(), 
                        cab_note = :note,
                        updated_at = NOW()
                    WHERE id = :cid
                ");
                $stmtCR->execute([
                    'cr_st' => $crStatus,
                    'uid'   => $userId,
                    'note'  => $comment,
                    'cid'   => $appr['change_id']
                ]);
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Get approvals for a ticket
     */
    public function getByTicketId(int $ticketId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name as approver_name, u.email as approver_email
            FROM ticket_approvals a
            JOIN users u ON a.approver_id = u.id
            WHERE a.ticket_id = :tid
            ORDER BY a.id ASC
        ");
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll();
    }
}
