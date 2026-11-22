<?php

namespace App\Models;

use App\Core\Model;
use App\Services\SlaService;
use PDO;

class Ticket extends Model
{
    /**
     * Generate the next formatted ticket code e.g. IT-000001
     */
    public function generateTicketCode(): string
    {
        $stmt = $this->db->query("SELECT MAX(id) as max_id FROM tickets");
        $row = $stmt->fetch();
        $nextNum = ($row && $row['max_id']) ? ((int)$row['max_id'] + 1) : 1;
        return sprintf("IT-%06d", $nextNum);
    }
    
    /**
     * Calculate Priority ID (1: Normal, 2: High, 3: Urgent) from ITIL Impact x Urgency matrix
     */
    public static function calculatePriorityFromMatrix(string $impact, string $urgency): int
    {
        $impact = strtoupper($impact);
        $urgency = strtoupper($urgency);

        if ($impact === 'HIGH' && $urgency === 'HIGH') {
            return 3; // URGENT
        }
        if (($impact === 'HIGH' && $urgency === 'MEDIUM') || 
            ($impact === 'MEDIUM' && $urgency === 'HIGH') ||
            ($impact === 'HIGH' && $urgency === 'LOW')) {
            return 2; // HIGH
        }
        return 1; // NORMAL
    }

    /**
     * Create a new ticket with SLA snapshot and audit record
     */
    public function create(array $data): int
    {
        $this->beginTransaction();

        try {
            $now = date('Y-m-d H:i:s');
            $ticketCode = $this->generateTicketCode();

            $ticketType = $data['ticket_type'] ?? 'INCIDENT';
            $impact = strtoupper($data['impact'] ?? 'MEDIUM');
            $urgency = strtoupper($data['urgency'] ?? 'MEDIUM');
            $supportTier = $data['support_tier'] ?? 'L1';
            $approvalStatus = $data['approval_status'] ?? 'NONE';

            // Auto-calculate priority from Impact x Urgency matrix if not directly specified
            $priorityId = !empty($data['priority_id']) ? (int)$data['priority_id'] : self::calculatePriorityFromMatrix($impact, $urgency);

            // If Service Request has an approver assigned, initiate approval workflow
            if (!empty($data['approver_id'])) {
                $approvalStatus = 'PENDING';
            }

            // Calculate SLA snapshot
            $slaService = new SlaService();
            $sla = $slaService->calculateInitialSla($priorityId, $now);

            // Determine initial group if not specified
            $groupId = $data['group_id'] ?? null;
            if (empty($groupId) && !empty($data['category_id'])) {
                $stmtCat = $this->db->prepare("SELECT default_group_id FROM ticket_categories WHERE id = :cid");
                $stmtCat->execute(['cid' => $data['category_id']]);
                $groupId = $stmtCat->fetchColumn() ?: null;
            }

            $assetId = !empty($data['asset_id']) ? (int)$data['asset_id'] : null;

            $stmt = $this->db->prepare("
                INSERT INTO tickets (
                    ticket_code, ticket_type, title, description, requester_id, category_id, service_id, asset_id,
                    priority_id, impact, urgency, support_tier, approval_status,
                    group_id, assignee_id, status,
                    sla_id, sla_value, sla_unit, sla_started_at, sla_due_at, sla_status,
                    location, contact_info, created_at, updated_at
                ) VALUES (
                    :ticket_code, :ticket_type, :title, :description, :requester_id, :category_id, :service_id, :asset_id,
                    :priority_id, :impact, :urgency, :support_tier, :approval_status,
                    :group_id, :assignee_id, 'NEW',
                    :sla_id, :sla_value, :sla_unit, :sla_started_at, :sla_due_at, :sla_status,
                    :location, :contact_info, :created_at, :updated_at
                )
            ");

            $stmt->execute([
                'ticket_code'     => $ticketCode,
                'ticket_type'     => $ticketType,
                'title'           => $data['title'],
                'description'     => $data['description'],
                'requester_id'    => $data['requester_id'],
                'category_id'     => $data['category_id'],
                'service_id'      => !empty($data['service_id']) ? $data['service_id'] : null,
                'asset_id'        => $assetId,
                'priority_id'     => $priorityId,
                'impact'          => $impact,
                'urgency'         => $urgency,
                'support_tier'    => $supportTier,
                'approval_status' => $approvalStatus,
                'group_id'        => $groupId,
                'assignee_id'     => null, // Initially in unassigned queue
                'sla_id'          => $sla['sla_id'],
                'sla_value'       => $sla['sla_value'],
                'sla_unit'        => $sla['sla_unit'],
                'sla_started_at'  => $sla['sla_started_at'],
                'sla_due_at'      => $sla['sla_due_at'],
                'sla_status'      => $sla['sla_status'],
                'location'        => $data['location'] ?? null,
                'contact_info'    => $data['contact_info'] ?? null,
                'created_at'      => $now,
                'updated_at'      => $now
            ]);

            $ticketId = (int)$this->lastInsertId();

            // Link to ticket_assets table if asset is selected
            if ($assetId) {
                $stmtAssetRel = $this->db->prepare("INSERT IGNORE INTO ticket_assets (ticket_id, asset_id) VALUES (:ticket_id, :asset_id)");
                $stmtAssetRel->execute(['ticket_id' => $ticketId, 'asset_id' => $assetId]);
            }

            // Create initial approval step if assigned
            if (!empty($data['approver_id'])) {
                $stmtAppr = $this->db->prepare("
                    INSERT INTO ticket_approvals (ticket_id, approver_id, step_name, status, created_at)
                    VALUES (:ticket_id, :approver_id, :step_name, 'PENDING', :created_at)
                ");
                $stmtAppr->execute([
                    'ticket_id'   => $ticketId,
                    'approver_id' => (int)$data['approver_id'],
                    'step_name'   => $data['approval_step_name'] ?? 'Phê duyệt yêu cầu dịch vụ',
                    'created_at'  => $now
                ]);
            }

            // Record initial creation in history
            $initAction = ($ticketType === 'SERVICE_REQUEST') ? 'CREATE_SERVICE_REQUEST' : 'CREATE_TICKET';
            $this->addHistory($ticketId, (int)$data['requester_id'], $initAction, null, null, 'NEW', "Khởi tạo {$ticketType} (Tier: {$supportTier}, Ưu tiên: {$priorityId})");

            $this->commit();
            return $ticketId;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Get detailed ticket with all relations
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT t.*,
                   req.full_name as requester_name, req.email as requester_email, req.phone as requester_phone, req.department as requester_department,
                   ass.full_name as assignee_name, ass.email as assignee_email,
                   grp.name as group_name, grp.code as group_code,
                   cat.name as category_name, cat.code as category_code,
                   svc.name as service_name,
                   pri.name as priority_name, pri.code as priority_code, pri.color as priority_color,
                   ws.name as status_name, ws.badge_color as status_color,
                   ast.asset_code, ast.name as asset_name, ast.model as asset_model, ast.status as asset_status, ast.serial_number as asset_serial,
                   dup.ticket_code as duplicate_ticket_code, dup.title as duplicate_ticket_title,
                   prb.problem_code, prb.title as problem_title,
                   chg.change_code, chg.title as change_title,
                   appr.full_name as approved_by_name
            FROM tickets t
            JOIN users req ON t.requester_id = req.id
            LEFT JOIN users ass ON t.assignee_id = ass.id
            LEFT JOIN users appr ON t.approved_by = appr.id
            LEFT JOIN it_groups grp ON t.group_id = grp.id
            JOIN ticket_categories cat ON t.category_id = cat.id
            LEFT JOIN ticket_services svc ON t.service_id = svc.id
            JOIN ticket_priorities pri ON t.priority_id = pri.id
            LEFT JOIN workflow_statuses ws ON t.status = ws.code
            LEFT JOIN assets ast ON t.asset_id = ast.id
            LEFT JOIN tickets dup ON t.duplicate_of_id = dup.id
            LEFT JOIN problems prb ON t.problem_id = prb.id
            LEFT JOIN change_requests chg ON t.change_id = chg.id
            WHERE t.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $ticket = $stmt->fetch();

        if ($ticket) {
            // Recalculate live SLA status
            $slaService = new SlaService();
            $ticket['calculated_sla_status'] = $slaService->evaluateSlaStatus($ticket);
        }

        return $ticket ?: null;
    }

    /**
     * Find ticket by ticket_code (e.g. IT-000001)
     */
    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tickets WHERE ticket_code = :code LIMIT 1");
        $stmt->execute(['code' => strtoupper(trim($code))]);
        $ticket = $stmt->fetch();
        return $ticket ?: null;
    }

    /**
     * Query tickets list with filtering and role permission scoping
     */
    public function getTickets(array $filters = [], ?int $scopedUserId = null, ?string $roleScope = null): array
    {
        $sql = "
            SELECT t.*,
                   req.full_name as requester_name,
                   req.department as requester_department,
                   ass.full_name as assignee_name,
                   grp.name as group_name,
                   cat.name as category_name,
                   pri.name as priority_name, pri.code as priority_code, pri.color as priority_color,
                   ws.name as status_name, ws.badge_color as status_color
            FROM tickets t
            JOIN users req ON t.requester_id = req.id
            LEFT JOIN users ass ON t.assignee_id = ass.id
            LEFT JOIN it_groups grp ON t.group_id = grp.id
            JOIN ticket_categories cat ON t.category_id = cat.id
            JOIN ticket_priorities pri ON t.priority_id = pri.id
            LEFT JOIN workflow_statuses ws ON t.status = ws.code
            WHERE 1=1
        ";

        $params = [];

        // Role scoping
        if ($roleScope === 'USER') {
            $sql .= " AND t.requester_id = :scoped_user_id";
            $params['scoped_user_id'] = $scopedUserId;
        } elseif ($roleScope === 'STAFF_QUEUE') {
            // Staff sees tickets assigned to them OR tickets in their groups
            $sql .= " AND (t.assignee_id = :scoped_user_id OR t.group_id IN (
                SELECT group_id FROM it_group_members WHERE user_id = :scoped_user_id_2
            ))";
            $params['scoped_user_id'] = $scopedUserId;
            $params['scoped_user_id_2'] = $scopedUserId;
        }

        // Filters
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority_id'])) {
            $sql .= " AND t.priority_id = :priority_id";
            $params['priority_id'] = $filters['priority_id'];
        }

        if (!empty($filters['group_id'])) {
            $sql .= " AND t.group_id = :group_id";
            $params['group_id'] = $filters['group_id'];
        }

        if (!empty($filters['assignee_id'])) {
            $sql .= " AND t.assignee_id = :assignee_id";
            $params['assignee_id'] = $filters['assignee_id'];
        }

        if (!empty($filters['ticket_type'])) {
            $sql .= " AND t.ticket_type = :ticket_type";
            $params['ticket_type'] = $filters['ticket_type'];
        }

        if (!empty($filters['support_tier'])) {
            $sql .= " AND t.support_tier = :support_tier";
            $params['support_tier'] = $filters['support_tier'];
        }

        if (!empty($filters['approval_status'])) {
            $sql .= " AND t.approval_status = :approval_status";
            $params['approval_status'] = $filters['approval_status'];
        }

        if (!empty($filters['unassigned'])) {
            $sql .= " AND (t.assignee_id IS NULL OR t.status = 'NEW')";
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $sql .= " AND (t.ticket_code LIKE :s1 OR t.title LIKE :s2 OR req.full_name LIKE :s3)";
            $params['s1'] = $search;
            $params['s2'] = $search;
            $params['s3'] = $search;
        }

        // Order by SLA urgency, then newest
        $sql .= " ORDER BY 
            CASE 
                WHEN t.status IN ('CLOSED', 'CANCELLED', 'DUPLICATE') THEN 3
                WHEN t.sla_due_at < NOW() THEN 1
                ELSE 2
            END ASC,
            t.sla_due_at ASC,
            t.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Assign / Reassign ticket to staff
     */
    public function assign(int $ticketId, int $toUserId, int $assignedByUserId, ?string $reason = null): void
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return;

        $oldAssigneeId = $ticket['assignee_id'];
        $oldAssigneeName = $ticket['assignee_name'] ?? 'Chưa phân công';

        $stmtUser = $this->db->prepare("SELECT full_name FROM users WHERE id = :id");
        $stmtUser->execute(['id' => $toUserId]);
        $newAssigneeName = $stmtUser->fetchColumn() ?: "ID #{$toUserId}";

        $this->beginTransaction();
        try {
            $newStatus = ($ticket['status'] === 'NEW') ? 'ASSIGNED' : $ticket['status'];

            $stmt = $this->db->prepare("
                UPDATE tickets 
                SET assignee_id = :to_user_id, status = :status, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute([
                'to_user_id' => $toUserId,
                'status'     => $newStatus,
                'id'         => $ticketId
            ]);

            // Record assignment log
            $stmtAss = $this->db->prepare("
                INSERT INTO ticket_assignments (ticket_id, from_user_id, to_user_id, assigned_by_user_id, reason)
                VALUES (:tid, :from_uid, :to_uid, :by_uid, :reason)
            ");
            $stmtAss->execute([
                'tid'      => $ticketId,
                'from_uid' => $oldAssigneeId,
                'to_uid'   => $toUserId,
                'by_uid'   => $assignedByUserId,
                'reason'   => $reason
            ]);

            // Add history
            $this->addHistory(
                $ticketId,
                $assignedByUserId,
                'ASSIGN',
                'assignee_id',
                $oldAssigneeName,
                $newAssigneeName,
                $reason ?: 'Phân công nhân viên phụ trách'
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Transfer ticket to another IT Group (Rule 2.3 & 2.4: SLA does NOT reset)
     */
    public function transferGroup(int $ticketId, int $toGroupId, int $transferredByUserId, string $reason): void
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return;

        $oldGroupId = $ticket['group_id'];
        $oldGroupName = $ticket['group_name'] ?? 'Chưa có nhóm';

        $stmtGrp = $this->db->prepare("SELECT name FROM it_groups WHERE id = :id");
        $stmtGrp->execute(['id' => $toGroupId]);
        $newGroupName = $stmtGrp->fetchColumn() ?: "Nhóm #{$toGroupId}";

        $this->beginTransaction();
        try {
            // Update group and unassign current staff if from different group
            $stmt = $this->db->prepare("
                UPDATE tickets 
                SET group_id = :to_group_id, assignee_id = NULL, status = 'ASSIGNED', updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute([
                'to_group_id' => $toGroupId,
                'id'          => $ticketId
            ]);

            // Insert transfer record
            $stmtTransfer = $this->db->prepare("
                INSERT INTO ticket_transfers (ticket_id, from_group_id, to_group_id, transferred_by_user_id, reason)
                VALUES (:tid, :from_gid, :to_gid, :by_uid, :reason)
            ");
            $stmtTransfer->execute([
                'tid'      => $ticketId,
                'from_gid' => $oldGroupId,
                'to_gid'   => $toGroupId,
                'by_uid'   => $transferredByUserId,
                'reason'   => $reason
            ]);

            // Add history
            $this->addHistory(
                $ticketId,
                $transferredByUserId,
                'TRANSFER_GROUP',
                'group_id',
                $oldGroupName,
                $newGroupName,
                "Chuyển nhóm: {$reason}"
            );

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Update ticket status with SLA pause/resume handling
     */
    public function updateStatus(int $ticketId, string $newStatus, int $userId, ?string $note = null): void
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return;

        $oldStatus = $ticket['status'];
        if ($oldStatus === $newStatus) return;

        $slaService = new SlaService();

        // Check if transitioning into WAITING_USER or WAITING_VENDOR -> Pause SLA
        if ($newStatus === 'WAITING_USER' || $newStatus === 'WAITING_VENDOR') {
            $slaService->pauseSla($ticketId, $newStatus, $userId, $note);
        }

        // Check if exiting WAITING_USER or WAITING_VENDOR -> Resume SLA
        if (($oldStatus === 'WAITING_USER' || $oldStatus === 'WAITING_VENDOR') && !in_array($newStatus, ['WAITING_USER', 'WAITING_VENDOR'])) {
            $slaService->resumeSla($ticketId, $userId);
        }

        $stmt = $this->db->prepare("
            UPDATE tickets 
            SET status = :status, updated_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([
            'status' => $newStatus,
            'id'     => $ticketId
        ]);

        $this->addHistory($ticketId, $userId, 'CHANGE_STATUS', 'status', $oldStatus, $newStatus, $note);
    }

    /**
     * Resolve ticket by IT Staff (requires resolution details)
     */
    public function resolve(int $ticketId, int $userId, string $resolution, ?string $rootCause = null, ?string $solution = null): void
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return;

        // Resume SLA if was paused
        if ($ticket['status'] === 'WAITING_USER') {
            (new SlaService())->resumeSla($ticketId, $userId);
        }

        $stmt = $this->db->prepare("
            UPDATE tickets 
            SET status = 'RESOLVED',
                resolution = :resolution,
                root_cause = :root_cause,
                solution = :solution,
                resolved_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'resolution'  => $resolution,
            'root_cause'  => $rootCause,
            'solution'    => $solution,
            'id'          => $ticketId
        ]);

        $this->addHistory($ticketId, $userId, 'RESOLVE', 'status', $ticket['status'], 'RESOLVED', "Giải pháp: {$resolution}");
    }

    /**
     * User confirms resolution -> CLOSED
     */
    public function close(int $ticketId, int $userId, ?string $comment = null): void
    {
        $stmt = $this->db->prepare("
            UPDATE tickets 
            SET status = 'CLOSED', closed_at = NOW(), updated_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $ticketId]);

        $this->addHistory($ticketId, $userId, 'CLOSE', 'status', 'RESOLVED', 'CLOSED', $comment ?: 'Người dùng xác nhận hoàn thành');
    }

    /**
     * User rejects resolution -> REOPENED
     */
    public function reopen(int $ticketId, int $userId, string $reason): void
    {
        $stmt = $this->db->prepare("
            UPDATE tickets 
            SET status = 'REOPENED',
                reopen_count = reopen_count + 1,
                reopen_reason = :reason,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['reason' => $reason, 'id' => $ticketId]);

        $this->addHistory($ticketId, $userId, 'REOPEN', 'status', 'RESOLVED', 'REOPENED', "Lý do mở lại: {$reason}");
    }

    /**
     * Add comment (public or internal note)
     */
    public function addComment(int $ticketId, int $userId, string $content, bool $isInternal = false): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_comments (ticket_id, user_id, is_internal, content)
            VALUES (:tid, :uid, :internal, :content)
        ");
        $stmt->execute([
            'tid'      => $ticketId,
            'uid'      => $userId,
            'internal' => $isInternal ? 1 : 0,
            'content'  => $content
        ]);

        $commentId = (int)$this->lastInsertId();

        $action = $isInternal ? 'INTERNAL_NOTE' : 'COMMENT';
        $this->addHistory($ticketId, $userId, $action, null, null, null, $isInternal ? 'Thêm ghi chú nội bộ' : 'Thêm bình luận trao đổi');

        return $commentId;
    }

    /**
     * Get comments for a ticket (filters out internal notes for normal users)
     */
    public function getComments(int $ticketId, bool $canViewInternal = false): array
    {
        $sql = "
            SELECT c.*, u.full_name as author_name, u.avatar as author_avatar
            FROM ticket_comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.ticket_id = :ticket_id
        ";

        if (!$canViewInternal) {
            $sql .= " AND c.is_internal = 0";
        }

        $sql .= " ORDER BY c.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ticket_id' => $ticketId]);
        return $stmt->fetchAll();
    }

    /**
     * Get timeline history
     */
    public function getHistory(int $ticketId): array
    {
        $stmt = $this->db->prepare("
            SELECT h.*, u.full_name as user_name
            FROM ticket_history h
            LEFT JOIN users u ON h.user_id = u.id
            WHERE h.ticket_id = :tid
            ORDER BY h.created_at ASC, h.id ASC
        ");
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    /**
     * Add entry to history timeline
     */
    public function addHistory(int $ticketId, ?int $userId, string $action, ?string $field = null, ?string $old = null, ?string $new = null, ?string $note = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_history (ticket_id, user_id, action, field_changed, old_value, new_value, note)
            VALUES (:tid, :uid, :action, :field, :old, :new, :note)
        ");
        $stmt->execute([
            'tid'    => $ticketId,
            'uid'    => $userId,
            'action' => $action,
            'field'  => $field,
            'old'    => $old,
            'new'    => $new,
            'note'   => $note
        ]);
    }

    /**
     * Add attachment metadata
     */
    public function addAttachment(int $ticketId, int $uploadedBy, string $fileName, string $filePath, int $fileSize, string $mimeType, ?int $commentId = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_attachments (ticket_id, comment_id, file_name, file_path, file_size, mime_type, uploaded_by)
            VALUES (:tid, :cid, :fname, :fpath, :fsize, :mime, :uid)
        ");
        $stmt->execute([
            'tid'   => $ticketId,
            'cid'   => $commentId,
            'fname' => $fileName,
            'fpath' => $filePath,
            'fsize' => $fileSize,
            'mime'  => $mimeType,
            'uid'   => $uploadedBy
        ]);
        return (int)$this->lastInsertId();
    }

    public function getAttachments(int $ticketId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name as uploader_name 
            FROM ticket_attachments a
            JOIN users u ON a.uploaded_by = u.id
            WHERE a.ticket_id = :tid
            ORDER BY a.created_at ASC
        ");
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function getAttachmentById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, u.full_name as uploader_name, t.requester_id, t.assignee_id, c.is_internal as comment_is_internal
            FROM ticket_attachments a
            JOIN users u ON a.uploaded_by = u.id
            JOIN tickets t ON a.ticket_id = t.id
            LEFT JOIN ticket_comments c ON a.comment_id = c.id
            WHERE a.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Adjust Ticket Priority (Rule 9: Manager/Leader, audit required)
     */
    public function adjustPriority(int $ticketId, int $newPriorityId, int $userId, string $reason): void
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return;

        $oldPriorityId = (int)$ticket['priority_id'];
        if ($oldPriorityId === $newPriorityId) return;

        $oldPriorityName = $ticket['priority_name'];

        $priorityModel = new Priority();
        $newPriority = $priorityModel->findById($newPriorityId);
        $newPriorityName = $newPriority['name'] ?? "Priority #{$newPriorityId}";

        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE tickets 
                SET priority_id = :pri_id, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute([
                'pri_id' => $newPriorityId,
                'id'     => $ticketId
            ]);

            // Add history
            $this->addHistory(
                $ticketId,
                $userId,
                'ADJUST_PRIORITY',
                'priority_id',
                $oldPriorityName,
                $newPriorityName,
                "Điều chỉnh mức độ ưu tiên: {$reason}"
            );

            // Add audit log
            $stmtAudit = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, object_type, object_id, old_value, new_value, reason, created_at)
                VALUES (:uid, 'ADJUST_PRIORITY', 'TICKET', :obj_id, :old_val, :new_val, :reason, NOW())
            ");
            $stmtAudit->execute([
                'uid'     => $userId,
                'obj_id'  => $ticketId,
                'old_val' => $oldPriorityName,
                'new_val' => $newPriorityName,
                'reason'  => $reason
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Mark ticket as duplicate (Rule 24: Link duplicate_of_id, do not delete)
     */
    public function markDuplicate(int $ticketId, int $duplicateOfId, int $userId, string $reason): void
    {
        $ticket = $this->findById($ticketId);
        $parent = $this->findById($duplicateOfId);
        if (!$ticket || !$parent) {
            throw new \Exception("Ticket không tồn tại.");
        }

        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE tickets 
                SET status = 'DUPLICATE', duplicate_of_id = :dup_id, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute([
                'dup_id' => $duplicateOfId,
                'id'     => $ticketId
            ]);

            $note = "Đánh dấu trùng với ticket #{$parent['ticket_code']}. Lý do: {$reason}";

            $this->addHistory($ticketId, $userId, 'DUPLICATE', 'status', $ticket['status'], 'DUPLICATE', $note);

            // Audit
            $stmtAudit = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, object_type, object_id, old_value, new_value, reason, created_at)
                VALUES (:uid, 'MARK_DUPLICATE', 'TICKET', :obj_id, :old_val, :new_val, :reason, NOW())
            ");
            $stmtAudit->execute([
                'uid'     => $userId,
                'obj_id'  => $ticketId,
                'old_val' => $ticket['status'],
                'new_val' => "DUPLICATE OF #{$parent['ticket_code']}",
                'reason'  => $reason
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Cancel ticket (Rule 25: Save reason, do not delete)
     */
    public function cancel(int $ticketId, int $userId, string $reason): void
    {
        $ticket = $this->findById($ticketId);
        if (!$ticket) return;

        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE tickets 
                SET status = 'CANCELLED', cancelled_reason = :reason, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute([
                'reason' => $reason,
                'id'     => $ticketId
            ]);

            $this->addHistory($ticketId, $userId, 'CANCEL', 'status', $ticket['status'], 'CANCELLED', "Lý do hủy: {$reason}");

            // Audit
            $stmtAudit = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, object_type, object_id, old_value, new_value, reason, created_at)
                VALUES (:uid, 'CANCEL_TICKET', 'TICKET', :obj_id, :old_val, 'CANCELLED', :reason, NOW())
            ");
            $stmtAudit->execute([
                'uid'     => $userId,
                'obj_id'  => $ticketId,
                'old_val' => $ticket['status'],
                'reason'  => $reason
            ]);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Link/Unlink Asset to Ticket (Rule 55)
     */
    public function linkAsset(int $ticketId, ?int $assetId, int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE tickets SET asset_id = :aid, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'aid' => $assetId ?: null,
            'id'  => $ticketId
        ]);

        $this->addHistory($ticketId, $userId, 'LINK_ASSET', 'asset_id', null, $assetId ? "Asset #{$assetId}" : 'Gỡ liên kết', 'Cập nhật thiết bị liên quan');
    }

    /**
     * Watchers management (Rule 27)
     */
    public function getWatchers(int $ticketId): array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.full_name, u.email, tw.created_at 
            FROM ticket_watchers tw
            JOIN users u ON tw.user_id = u.id
            WHERE tw.ticket_id = :tid
            ORDER BY tw.created_at ASC
        ");
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function isWatching(int $ticketId, int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ticket_watchers WHERE ticket_id = :tid AND user_id = :uid");
        $stmt->execute(['tid' => $ticketId, 'uid' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function addWatcher(int $ticketId, int $userId): bool
    {
        if ($this->isWatching($ticketId, $userId)) return true;
        $stmt = $this->db->prepare("INSERT IGNORE INTO ticket_watchers (ticket_id, user_id, created_at) VALUES (:tid, :uid, NOW())");
        return $stmt->execute(['tid' => $ticketId, 'uid' => $userId]);
    }

    public function removeWatcher(int $ticketId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM ticket_watchers WHERE ticket_id = :tid AND user_id = :uid");
        return $stmt->execute(['tid' => $ticketId, 'uid' => $userId]);
    }

    public function getWatcherUserIds(int $ticketId): array
    {
        $stmt = $this->db->prepare("SELECT user_id FROM ticket_watchers WHERE ticket_id = :tid");
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // =========================================================================
    // JIRA FEATURE 1: WORKLOG / TIME TRACKING
    // =========================================================================

    public static function parseTimeToMinutes(string $input): int
    {
        $input = trim(strtolower($input));
        if (is_numeric($input)) {
            return (int)$input;
        }

        $totalMinutes = 0;
        // Check days (1d = 8h = 480m)
        if (preg_match('/(\d+(?:\.\d+)?)\s*d/', $input, $m)) {
            $totalMinutes += (float)$m[1] * 8 * 60;
        }
        // Check hours
        if (preg_match('/(\d+(?:\.\d+)?)\s*h/', $input, $m)) {
            $totalMinutes += (float)$m[1] * 60;
        }
        // Check minutes
        if (preg_match('/(\d+)\s*m/', $input, $m)) {
            $totalMinutes += (int)$m[1];
        }

        return max(1, (int)round($totalMinutes));
    }

    public static function formatMinutesToTime(int $minutes): string
    {
        if ($minutes <= 0) return '0m';
        $days = floor($minutes / 480);
        $remainder = $minutes % 480;
        $hours = floor($remainder / 60);
        $mins = $remainder % 60;

        $parts = [];
        if ($days > 0) $parts[] = "{$days}d";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($mins > 0 || empty($parts)) $parts[] = "{$mins}m";

        return implode(' ', $parts);
    }

    public function addWorklog(int $ticketId, int $userId, string $timeSpentStr, ?string $description = null, ?string $loggedDate = null): int
    {
        $minutes = self::parseTimeToMinutes($timeSpentStr);
        $formatted = self::formatMinutesToTime($minutes);
        $date = $loggedDate ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            INSERT INTO ticket_worklogs (ticket_id, user_id, time_spent_minutes, time_spent_formatted, description, logged_date, created_at)
            VALUES (:tid, :uid, :mins, :formatted, :desc, :ldate, NOW())
        ");
        $stmt->execute([
            'tid'       => $ticketId,
            'uid'       => $userId,
            'mins'      => $minutes,
            'formatted' => $formatted,
            'desc'      => $description,
            'ldate'     => $date
        ]);

        $this->addHistory($ticketId, $userId, 'LOG_WORK', 'worklog', null, $formatted, $description);

        return (int)$this->db->lastInsertId();
    }

    public function getWorklogs(int $ticketId): array
    {
        $stmt = $this->db->prepare("
            SELECT tw.*, u.full_name, u.email
            FROM ticket_worklogs tw
            JOIN users u ON tw.user_id = u.id
            WHERE tw.ticket_id = :tid
            ORDER BY tw.created_at DESC
        ");
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function getTotalWorklogMinutes(int $ticketId): int
    {
        $stmt = $this->db->prepare("SELECT SUM(time_spent_minutes) FROM ticket_worklogs WHERE ticket_id = :tid");
        $stmt->execute(['tid' => $ticketId]);
        return (int)$stmt->fetchColumn();
    }

    // =========================================================================
    // JIRA FEATURE 2: SUB-TASKS / TECHNICAL CHECKLIST
    // =========================================================================

    public function getChecklist(int $ticketId): array
    {
        $stmt = $this->db->prepare("
            SELECT tc.*, u.full_name as completed_by_name
            FROM ticket_checklists tc
            LEFT JOIN users u ON tc.completed_by = u.id
            WHERE tc.ticket_id = :tid
            ORDER BY tc.sort_order ASC, tc.id ASC
        ");
        $stmt->execute(['tid' => $ticketId]);
        return $stmt->fetchAll();
    }

    public function addChecklistItem(int $ticketId, string $itemText): int
    {
        $stmtOrder = $this->db->prepare("SELECT MAX(sort_order) FROM ticket_checklists WHERE ticket_id = :tid");
        $stmtOrder->execute(['tid' => $ticketId]);
        $maxOrder = (int)$stmtOrder->fetchColumn();

        $stmt = $this->db->prepare("
            INSERT INTO ticket_checklists (ticket_id, item_text, sort_order, created_at)
            VALUES (:tid, :txt, :ord, NOW())
        ");
        $stmt->execute([
            'tid' => $ticketId,
            'txt' => trim($itemText),
            'ord' => $maxOrder + 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function toggleChecklistItem(int $itemId, int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM ticket_checklists WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $itemId]);
        $item = $stmt->fetch();
        if (!$item) {
            throw new \Exception("Không tìm thấy mục checklist");
        }

        $newCompleted = $item['is_completed'] ? 0 : 1;
        $completedBy = $newCompleted ? $userId : null;
        $completedAt = $newCompleted ? date('Y-m-d H:i:s') : null;

        $stmtUpd = $this->db->prepare("
            UPDATE ticket_checklists 
            SET is_completed = :comp, completed_by = :by, completed_at = :at 
            WHERE id = :id
        ");
        $stmtUpd->execute([
            'comp' => $newCompleted,
            'by'   => $completedBy,
            'at'   => $completedAt,
            'id'   => $itemId
        ]);

        return [
            'id' => $itemId,
            'is_completed' => (bool)$newCompleted,
            'ticket_id' => $item['ticket_id']
        ];
    }

    public function deleteChecklistItem(int $itemId): void
    {
        $stmt = $this->db->prepare("DELETE FROM ticket_checklists WHERE id = :id");
        $stmt->execute(['id' => $itemId]);
    }

    // =========================================================================
    // JIRA FEATURE 3: ISSUE LINKING
    // =========================================================================

    public function linkIssue(int $sourceTicketId, int $targetTicketId, string $linkType, int $userId): int
    {
        if ($sourceTicketId === $targetTicketId) {
            throw new \Exception("Không thể liên kết ticket với chính nó.");
        }

        $validTypes = ['relates_to', 'blocks', 'is_blocked_by', 'causes'];
        if (!in_array($linkType, $validTypes)) {
            $linkType = 'relates_to';
        }

        $stmt = $this->db->prepare("
            INSERT INTO ticket_links (source_ticket_id, target_ticket_id, link_type, created_by, created_at)
            VALUES (:src, :tgt, :type, :uid, NOW())
            ON DUPLICATE KEY UPDATE link_type = VALUES(link_type)
        ");
        $stmt->execute([
            'src'  => $sourceTicketId,
            'tgt'  => $targetTicketId,
            'type' => $linkType,
            'uid'  => $userId
        ]);

        $this->addHistory($sourceTicketId, $userId, 'LINK_ISSUE', 'ticket_links', null, "Liên kết với ticket #{$targetTicketId}", "Loại liên kết: {$linkType}");

        return (int)$this->db->lastInsertId();
    }

    public function unlinkIssue(int $linkId): void
    {
        $stmt = $this->db->prepare("DELETE FROM ticket_links WHERE id = :id");
        $stmt->execute(['id' => $linkId]);
    }

    public function getLinkedIssues(int $ticketId): array
    {
        // Outgoing links
        $stmtOut = $this->db->prepare("
            SELECT tl.id as link_id, tl.link_type, 'outward' as direction,
                   t.id as target_id, t.ticket_code, t.title, t.status,
                   p.name as priority_name, p.code as priority_code, p.color as priority_color,
                   u.full_name as assignee_name
            FROM ticket_links tl
            JOIN tickets t ON tl.target_ticket_id = t.id
            JOIN ticket_priorities p ON t.priority_id = p.id
            LEFT JOIN users u ON t.assignee_id = u.id
            WHERE tl.source_ticket_id = :tid
        ");
        $stmtOut->execute(['tid' => $ticketId]);
        $outward = $stmtOut->fetchAll();

        // Inward links
        $stmtIn = $this->db->prepare("
            SELECT tl.id as link_id, tl.link_type, 'inward' as direction,
                   t.id as target_id, t.ticket_code, t.title, t.status,
                   p.name as priority_name, p.code as priority_code, p.color as priority_color,
                   u.full_name as assignee_name
            FROM ticket_links tl
            JOIN tickets t ON tl.source_ticket_id = t.id
            JOIN ticket_priorities p ON t.priority_id = p.id
            LEFT JOIN users u ON t.assignee_id = u.id
            WHERE tl.target_ticket_id = :tid
        ");
        $stmtIn->execute(['tid' => $ticketId]);
        $inward = $stmtIn->fetchAll();

        return array_merge($outward, $inward);
    }

    // =========================================================================
    // JIRA FEATURE 4: KANBAN BOARD TICKETS GROUPED BY COLUMNS
    // =========================================================================

    public function getBoardTickets(array $filters = []): array
    {
        $where = "WHERE t.status NOT IN ('CANCELLED', 'DUPLICATE')";
        $params = [];

        if (!empty($filters['group_id'])) {
            $where .= " AND t.group_id = :group_id";
            $params['group_id'] = (int)$filters['group_id'];
        }
        if (!empty($filters['assignee_id'])) {
            $where .= " AND t.assignee_id = :assignee_id";
            $params['assignee_id'] = (int)$filters['assignee_id'];
        }
        if (!empty($filters['priority_id'])) {
            $where .= " AND t.priority_id = :priority_id";
            $params['priority_id'] = (int)$filters['priority_id'];
        }
        if (!empty($filters['urgent_only'])) {
            $where .= " AND (p.code = 'URGENT' OR t.priority_id = 3)";
        }
        if (!empty($filters['overdue_only'])) {
            $where .= " AND (t.sla_status = 'OVERDUE' OR (t.sla_due_at < NOW() AND t.status NOT IN ('RESOLVED', 'CLOSED')))";
        }
        if (!empty($filters['search'])) {
            $where .= " AND (t.ticket_code LIKE :q OR t.title LIKE :q2)";
            $params['q'] = '%' . $filters['search'] . '%';
            $params['q2'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare("
            SELECT t.id, t.ticket_code, t.title, t.status, t.sla_status, t.sla_due_at, t.created_at,
                   t.assignee_id, t.group_id, t.priority_id,
                   p.name as priority_name, p.code as priority_code, p.color as priority_color,
                   u.full_name as assignee_name,
                   req.full_name as requester_name,
                   g.name as group_name
            FROM tickets t
            JOIN ticket_priorities p ON t.priority_id = p.id
            JOIN users req ON t.requester_id = req.id
            LEFT JOIN users u ON t.assignee_id = u.id
            LEFT JOIN it_groups g ON t.group_id = g.id
            {$where}
            ORDER BY t.priority_id DESC, t.id DESC
        ");
        $stmt->execute($params);
        $tickets = $stmt->fetchAll();

        // Group into 5 Jira columns
        $board = [
            'NEW'         => [],
            'IN_PROGRESS' => [],
            'WAITING'     => [],
            'RESOLVED'    => [],
            'CLOSED'      => []
        ];

        foreach ($tickets as $t) {
            $st = $t['status'];
            if ($st === 'NEW') {
                $board['NEW'][] = $t;
            } elseif (in_array($st, ['ASSIGNED', 'IN_PROGRESS', 'REOPENED'])) {
                $board['IN_PROGRESS'][] = $t;
            } elseif (in_array($st, ['WAITING_USER', 'WAITING_VENDOR'])) {
                $board['WAITING'][] = $t;
            } elseif ($st === 'RESOLVED') {
                $board['RESOLVED'][] = $t;
            } elseif ($st === 'CLOSED') {
                $board['CLOSED'][] = $t;
            }
        }

        return $board;
    }

    // =========================================================================
    // ITIL / ITSM METHODS
    // =========================================================================

    /**
     * Escalate Support Tier (L1 -> L2 -> L3 -> VENDOR)
     */
    public function escalateTier(int $ticketId, string $targetTier, int $userId, string $note = ''): bool
    {
        $targetTier = strtoupper($targetTier);
        if (!in_array($targetTier, ['L1', 'L2', 'L3', 'VENDOR'])) {
            return false;
        }

        $ticket = $this->findById($ticketId);
        if (!$ticket) return false;

        $oldTier = $ticket['support_tier'] ?? 'L1';
        if ($oldTier === $targetTier) return true;

        $now = date('Y-m-d H:i:s');
        $newStatus = $ticket['status'];
        if ($targetTier === 'VENDOR' && in_array($newStatus, ['ASSIGNED', 'IN_PROGRESS', 'NEW'])) {
            $newStatus = 'WAITING_VENDOR';
        }

        $stmt = $this->db->prepare("
            UPDATE tickets 
            SET support_tier = :tier, status = :status, updated_at = :updated_at 
            WHERE id = :id
        ");
        $stmt->execute([
            'tier'       => $targetTier,
            'status'     => $newStatus,
            'updated_at' => $now,
            'id'         => $ticketId
        ]);

        $detail = "Chuyển cấp hỗ trợ từ {$oldTier} lên {$targetTier}";
        if ($note) {
            $detail .= ". Lý do: " . $note;
        }
        $this->addHistory($ticketId, $userId, 'TIER_ESCALATED', $oldTier, $targetTier, $newStatus, $detail);

        return true;
    }

    /**
     * Link Ticket to Problem (RCA)
     */
    public function linkProblem(int $ticketId, int $problemId, int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE tickets SET problem_id = :problem_id WHERE id = :ticket_id");
        $stmt->execute(['problem_id' => $problemId, 'ticket_id' => $ticketId]);

        $stmtRel = $this->db->prepare("INSERT IGNORE INTO problem_incidents (problem_id, ticket_id) VALUES (:problem_id, :ticket_id)");
        $stmtRel->execute(['problem_id' => $problemId, 'ticket_id' => $ticketId]);

        $this->addHistory($ticketId, $userId, 'LINK_PROBLEM', null, (string)$problemId, null, "Liên kết Incident với Problem #{$problemId}");
        return true;
    }

    /**
     * Link Ticket to Change Request
     */
    public function linkChange(int $ticketId, int $changeId, int $userId): bool
    {
        $stmt = $this->db->prepare("UPDATE tickets SET change_id = :change_id WHERE id = :ticket_id");
        $stmt->execute(['change_id' => $changeId, 'ticket_id' => $ticketId]);

        $stmtRel = $this->db->prepare("INSERT IGNORE INTO change_tickets (change_id, ticket_id) VALUES (:change_id, :ticket_id)");
        $stmtRel->execute(['change_id' => $changeId, 'ticket_id' => $ticketId]);

        $this->addHistory($ticketId, $userId, 'LINK_CHANGE', null, (string)$changeId, null, "Liên kết với Change Request #{$changeId}");
        return true;
    }

    /**
     * Update Approval Status on Ticket
     */
    public function updateApprovalStatus(int $ticketId, string $status, int $userId, ?string $note = null): bool
    {
        $status = strtoupper($status);
        if (!in_array($status, ['NONE', 'PENDING', 'APPROVED', 'REJECTED', 'RETURNED'])) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            UPDATE tickets
            SET approval_status = :status,
                approved_by = :approved_by,
                approved_at = :approved_at,
                approval_note = :approval_note,
                updated_at = :updated_at
            WHERE id = :id
        ");
        $stmt->execute([
            'status'        => $status,
            'approved_by'   => $userId,
            'approved_at'   => $now,
            'approval_note' => $note,
            'updated_at'    => $now,
            'id'            => $ticketId
        ]);

        $this->addHistory($ticketId, $userId, 'APPROVAL_DECIDED', null, $status, null, "Phê duyệt: {$status}" . ($note ? " - Ghi chú: {$note}" : ""));
        return true;
    }
}

