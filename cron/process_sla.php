<?php
/**
 * Cron Job Automation for IT Service Desk
 * FSD Rules implemented:
 * - Rule 15 & 16: SLA Warning (<30%), Critical (<10%), and Overdue detection
 * - Rule 17: SLA Escalation alerts to Managers and Leaders
 * - Rule 22: Auto Close resolved tickets after 3 days
 * - Rule 30 & 64: Unassigned ticket reminders (>30 mins without assignee)
 * - Rule 31: Stale ticket detection (>24 hours in progress with no update)
 *
 * Can be run via CLI: php cron/process_sla.php
 * Or scheduled via cron/Task Scheduler every 1 to 5 minutes.
 */

require_once __DIR__ . '/../app/autoload.php';

use App\Core\Database;
use App\Models\Notification;
use App\Services\SlaService;

echo "[" . date('Y-m-d H:i:s') . "] Starting IT Service Desk Background Processing...\n";

try {
    $db = Database::getConnection();
    $notificationModel = new Notification();
    $slaService = new SlaService();

    // System Bot / Admin User ID for automated actions
    $adminUserId = 1;

    // =========================================================================
    // 1. SLA WARNING & OVERDUE DETECTION + ESCALATION (RULES 15, 16, 17)
    // =========================================================================
    echo "--- Checking Ticket SLA & Escalations ---\n";
    
    $stmtOpen = $db->query("
        SELECT t.id, t.ticket_code, t.title, t.status, t.sla_status, t.sla_started_at, 
               t.sla_due_at, t.pause_started_at, t.assignee_id, t.group_id, t.requester_id,
               g.leader_id
        FROM tickets t
        LEFT JOIN it_groups g ON t.group_id = g.id
        WHERE t.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED', 'DUPLICATE')
          AND t.sla_due_at IS NOT NULL
    ");
    $openTickets = $stmtOpen->fetchAll();

    $overdueCount = 0;
    $warningCount = 0;

    foreach ($openTickets as $ticket) {
        // Skip tickets that are actively paused in WAITING_USER or WAITING_VENDOR
        if (!empty($ticket['pause_started_at'])) {
            continue;
        }

        $now = time();
        $due = strtotime($ticket['sla_due_at']);
        $start = !empty($ticket['sla_started_at']) ? strtotime($ticket['sla_started_at']) : $now;
        $totalSpan = max(1, $due - $start);
        $remaining = $due - $now;

        if ($now > $due) {
            // TICKET IS OVERDUE
            if ($ticket['sla_status'] !== 'OVERDUE') {
                $overdueCount++;
                $db->prepare("
                    UPDATE tickets 
                    SET sla_status = 'OVERDUE', updated_at = NOW() 
                    WHERE id = :id
                ")->execute(['id' => $ticket['id']]);

                // Record in ticket history
                $db->prepare("
                    INSERT INTO ticket_history (ticket_id, user_id, action, field_changed, old_value, new_value, note)
                    VALUES (:tid, :uid, 'SLA_OVERDUE', 'sla_status', :old_val, 'OVERDUE', 'Hệ thống tự động phát hiện vi phạm SLA cam kết (Rule 16)')
                ")->execute([
                    'tid' => $ticket['id'],
                    'uid' => $adminUserId,
                    'old_val' => $ticket['sla_status']
                ]);

                // Record Audit Log
                $db->prepare("
                    INSERT INTO audit_logs (user_id, action, object_type, object_id, old_value, new_value, reason)
                    VALUES (:uid, 'TICKET_SLA_OVERDUE', 'ticket', :obj_id, :old_val, 'OVERDUE', 'Hạn xử lý SLA đã hết')
                ")->execute([
                    'uid' => $adminUserId,
                    'obj_id' => $ticket['id'],
                    'old_val' => $ticket['sla_status']
                ]);

                // Notify Assignee
                if (!empty($ticket['assignee_id'])) {
                    $notificationModel->create([
                        'user_id' => $ticket['assignee_id'],
                        'ticket_id' => $ticket['id'],
                        'title' => 'QUÁ HẠN SLA: Ticket #' . $ticket['ticket_code'],
                        'message' => 'Yêu cầu "' . $ticket['title'] . '" đã vượt quá hạn cam kết SLA. Vui lòng khẩn trương hoàn thành.',
                        'type' => 'danger'
                    ]);
                }

                // Rule 17: Escalation alert to Group Leader and IT Managers
                $escalateUsers = [];
                if (!empty($ticket['leader_id'])) {
                    $escalateUsers[] = (int)$ticket['leader_id'];
                }
                
                // Get all IT Managers
                $stmtMgr = $db->query("
                    SELECT ur.user_id FROM user_roles ur
                    JOIN roles r ON ur.role_id = r.id
                    WHERE r.code IN ('IT_MANAGER', 'ADMIN')
                ");
                $mgrIds = $stmtMgr->fetchAll(PDO::FETCH_COLUMN);
                $escalateUsers = array_unique(array_merge($escalateUsers, $mgrIds));

                if (!empty($ticket['assignee_id'])) {
                    $escalateUsers = array_filter($escalateUsers, fn($uid) => (int)$uid !== (int)$ticket['assignee_id']);
                }

                $notificationModel->notifyUsers($escalateUsers, [
                    'ticket_id' => $ticket['id'],
                    'title' => 'CẢNH BÁO LEO THANG (ESCALATION): Ticket #' . $ticket['ticket_code'] . ' quá hạn SLA',
                    'message' => 'Ticket "' . $ticket['title'] . '" tại nhóm IT đã quá hạn cam kết xử lý SLA. Yêu cầu lãnh đạo can thiệp/điều phối.',
                    'type' => 'danger'
                ]);

                echo "  [OVERDUE] Ticket #{$ticket['ticket_code']} marked OVERDUE and escalated.\n";
            }
        } else {
            // Not overdue yet: Check warning thresholds
            $ratio = $remaining / $totalSpan;
            if ($ratio <= 0.30 && $ticket['sla_status'] === 'WITHIN_SLA') {
                $newStatus = ($ratio <= 0.10) ? 'CRITICAL' : 'WARNING';
                $warningCount++;
                
                $db->prepare("
                    UPDATE tickets SET sla_status = :status, updated_at = NOW() WHERE id = :id
                ")->execute(['status' => $newStatus, 'id' => $ticket['id']]);

                if (!empty($ticket['assignee_id'])) {
                    $pctText = round($ratio * 100);
                    $notificationModel->create([
                        'user_id' => $ticket['assignee_id'],
                        'ticket_id' => $ticket['id'],
                        'title' => 'SẮP QUÁ HẠN SLA (còn ' . $pctText . '%): Ticket #' . $ticket['ticket_code'],
                        'message' => 'Yêu cầu "' . $ticket['title'] . '" chỉ còn ' . $pctText . '% thời gian SLA. Vui lòng xử lý sớm.',
                        'type' => 'warning'
                    ]);
                }
                echo "  [WARNING] Ticket #{$ticket['ticket_code']} updated to {$newStatus}.\n";
            }
        }
    }
    echo "SLA check completed: {$overdueCount} newly overdue, {$warningCount} warnings triggered.\n";

    // =========================================================================
    // 2. AUTO-CLOSE RESOLVED TICKETS AFTER 3 DAYS (RULE 22)
    // =========================================================================
    echo "--- Checking Auto-Close for Resolved Tickets (Rule 22) ---\n";
    $stmtResolved = $db->query("
        SELECT id, ticket_code, title, requester_id, assignee_id, resolved_at 
        FROM tickets 
        WHERE status = 'RESOLVED' 
          AND resolved_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)
    ");
    $resolvedToClose = $stmtResolved->fetchAll();
    $autoCloseCount = 0;

    foreach ($resolvedToClose as $ticket) {
        $autoCloseCount++;
        $db->prepare("
            UPDATE tickets 
            SET status = 'CLOSED', closed_at = NOW(), updated_at = NOW() 
            WHERE id = :id
        ")->execute(['id' => $ticket['id']]);

        // Ticket history
        $db->prepare("
            INSERT INTO ticket_history (ticket_id, user_id, action, field_changed, old_value, new_value, note)
            VALUES (:tid, :uid, 'STATUS_CHANGE', 'status', 'RESOLVED', 'CLOSED', 'Hệ thống tự động đóng ticket sau 3 ngày kể từ khi giải quyết (Rule 22)')
        ")->execute([
            'tid' => $ticket['id'],
            'uid' => $adminUserId
        ]);

        // Audit log
        $db->prepare("
            INSERT INTO audit_logs (user_id, action, object_type, object_id, old_value, new_value, reason)
            VALUES (:uid, 'TICKET_AUTO_CLOSED', 'ticket', :obj_id, 'RESOLVED', 'CLOSED', 'Tự động đóng sau 3 ngày hoàn thành')
        ")->execute([
            'uid' => $adminUserId,
            'obj_id' => $ticket['id']
        ]);

        // Notify Requester & Assignee
        $notifyUsers = array_filter([$ticket['requester_id'], $ticket['assignee_id']]);
        $notificationModel->notifyUsers($notifyUsers, [
            'ticket_id' => $ticket['id'],
            'title' => 'ĐÃ ĐÓNG TỰ ĐỘNG: Ticket #' . $ticket['ticket_code'],
            'message' => 'Yêu cầu "' . $ticket['title'] . '" đã được hệ thống tự động hoàn tất và đóng (Closed) sau 3 ngày ở trạng thái Resolved.',
            'type' => 'success'
        ]);

        echo "  [AUTO-CLOSED] Ticket #{$ticket['ticket_code']} closed automatically.\n";
    }
    echo "Auto-close completed: {$autoCloseCount} tickets closed.\n";

    // =========================================================================
    // 3. STALE TICKET REMINDER (>24h without activity in IN_PROGRESS) (RULE 31)
    // =========================================================================
    echo "--- Checking Stale Tickets (Rule 31) ---\n";
    $stmtStale = $db->query("
        SELECT t.id, t.ticket_code, t.title, t.assignee_id, t.group_id, g.leader_id, t.updated_at
        FROM tickets t
        LEFT JOIN it_groups g ON t.group_id = g.id
        WHERE t.status = 'IN_PROGRESS'
          AND t.updated_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $staleTickets = $stmtStale->fetchAll();
    $staleCount = 0;

    foreach ($staleTickets as $ticket) {
        // Prevent duplicate spam: check if stale notification sent in last 24h
        $stmtCheckNotif = $db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE ticket_id = :tid 
              AND title LIKE 'NHẮC NHỞ TIẾN ĐỘ%' 
              AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmtCheckNotif->execute(['tid' => $ticket['id']]);
        if ($stmtCheckNotif->fetchColumn() > 0) {
            continue;
        }

        $staleCount++;
        $notifyUsers = array_filter([$ticket['assignee_id'], $ticket['leader_id']]);
        $notificationModel->notifyUsers($notifyUsers, [
            'ticket_id' => $ticket['id'],
            'title' => 'NHẮC NHỞ TIẾN ĐỘ (STALE): Ticket #' . $ticket['ticket_code'],
            'message' => 'Yêu cầu "' . $ticket['title'] . '" đang xử lý nhưng chưa có cập nhật hoạt động nào trong hơn 24 giờ. Vui lòng cập nhật trạng thái.',
            'type' => 'warning'
        ]);

        echo "  [STALE REMINDER] Sent for Ticket #{$ticket['ticket_code']}.\n";
    }
    echo "Stale ticket check completed: {$staleCount} reminders sent.\n";

    // =========================================================================
    // 4. UNASSIGNED TICKET REMINDER (>30m in NEW without assignee) (RULE 30 & 64)
    // =========================================================================
    echo "--- Checking Unassigned Tickets (Rule 30 & 64) ---\n";
    $stmtUnassigned = $db->query("
        SELECT t.id, t.ticket_code, t.title, t.group_id, g.name as group_name, g.leader_id
        FROM tickets t
        LEFT JOIN it_groups g ON t.group_id = g.id
        WHERE t.status = 'NEW' 
          AND t.assignee_id IS NULL
          AND t.created_at <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ");
    $unassignedTickets = $stmtUnassigned->fetchAll();
    $unassignedCount = 0;

    foreach ($unassignedTickets as $ticket) {
        $stmtCheckNotif = $db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE ticket_id = :tid 
              AND title LIKE 'CHƯA PHÂN CÔNG%' 
              AND created_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
        ");
        $stmtCheckNotif->execute(['tid' => $ticket['id']]);
        if ($stmtCheckNotif->fetchColumn() > 0) {
            continue;
        }

        $unassignedCount++;
        if (!empty($ticket['group_id'])) {
            $notificationModel->notifyGroup((int)$ticket['group_id'], [
                'ticket_id' => $ticket['id'],
                'title' => 'CHƯA PHÂN CÔNG: Ticket #' . $ticket['ticket_code'] . ' trong hàng đợi',
                'message' => 'Ticket "' . $ticket['title'] . '" thuộc nhóm ' . ($ticket['group_name'] ?? 'IT') . ' đã tồn tại hơn 30 phút chưa có người tiếp nhận.',
                'type' => 'warning'
            ]);
        } else {
            $notificationModel->notifyRoles(['IT_MANAGER', 'ADMIN'], [
                'ticket_id' => $ticket['id'],
                'title' => 'CHƯA PHÂN CÔNG: Ticket #' . $ticket['ticket_code'],
                'message' => 'Ticket "' . $ticket['title'] . '" chưa được phân nhóm và tiếp nhận sau 30 phút.',
                'type' => 'warning'
            ]);
        }

        echo "  [UNASSIGNED REMINDER] Sent for Ticket #{$ticket['ticket_code']}.\n";
    }
    echo "Unassigned check completed: {$unassignedCount} reminders sent.\n";

    echo "[" . date('Y-m-d H:i:s') . "] Background Processing Completed Successfully!\n";

} catch (Exception $e) {
    echo "[ERROR] Cron job failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

