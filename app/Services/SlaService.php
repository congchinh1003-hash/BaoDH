<?php

namespace App\Services;

use App\Core\Database;
use App\Models\SlaConfig;
use PDO;
use DateTime;

class SlaService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Calculate initial SLA snapshot when creating a ticket
     */
    public function calculateInitialSla(int $priorityId, string $createdAt): array
    {
        $slaConfigModel = new SlaConfig();
        $slaConfig = $slaConfigModel->getByPriorityId($priorityId);

        $startedAt = new DateTime($createdAt);
        $dueAt = clone $startedAt;

        if ($slaConfig) {
            $value = (int)$slaConfig['resolution_time'];
            $unit = strtoupper($slaConfig['resolution_unit']);

            if ($unit === 'HOUR') {
                $dueAt->modify("+{$value} hours");
            } elseif ($unit === 'DAY') {
                $dueAt->modify("+{$value} days");
            }

            return [
                'sla_id'             => $slaConfig['id'],
                'sla_value'          => $value,
                'sla_unit'           => $unit,
                'sla_started_at'     => $startedAt->format('Y-m-d H:i:s'),
                'sla_due_at'         => $dueAt->format('Y-m-d H:i:s'),
                'sla_status'         => 'WITHIN_SLA',
                'sla_paused_seconds' => 0
            ];
        }

        // Fallback default: 48 hours
        $dueAt->modify('+48 hours');
        return [
            'sla_id'             => null,
            'sla_value'          => 48,
            'sla_unit'           => 'HOUR',
            'sla_started_at'     => $startedAt->format('Y-m-d H:i:s'),
            'sla_due_at'         => $dueAt->format('Y-m-d H:i:s'),
            'sla_status'         => 'WITHIN_SLA',
            'sla_paused_seconds' => 0
        ];
    }

    /**
     * Pause SLA when status enters WAITING_USER or similar
     */
    public function pauseSla(int $ticketId, string $statusTrigger, int $userId, ?string $reason = null): void
    {
        $now = date('Y-m-d H:i:s');

        // Set pause_started_at on ticket
        $stmt = $this->db->prepare("
            UPDATE tickets 
            SET pause_started_at = :now, updated_at = NOW() 
            WHERE id = :ticket_id AND pause_started_at IS NULL
        ");
        $stmt->execute(['now' => $now, 'ticket_id' => $ticketId]);

        // Insert pause log
        $stmtLog = $this->db->prepare("
            INSERT INTO ticket_sla_pauses (ticket_id, status_trigger, paused_at, paused_by_user_id, reason)
            VALUES (:ticket_id, :trigger, :paused_at, :user_id, :reason)
        ");
        $stmtLog->execute([
            'ticket_id' => $ticketId,
            'trigger'   => $statusTrigger,
            'paused_at' => $now,
            'user_id'   => $userId,
            'reason'    => $reason
        ]);
    }

    /**
     * Resume SLA when status exits WAITING_USER, extending due date by paused duration
     */
    public function resumeSla(int $ticketId, int $userId): void
    {
        // Get current pause start
        $stmt = $this->db->prepare("
            SELECT pause_started_at, sla_due_at, sla_paused_seconds 
            FROM tickets 
            WHERE id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $ticketId]);
        $ticket = $stmt->fetch();

        if (!$ticket || empty($ticket['pause_started_at'])) {
            return;
        }

        $nowTime = time();
        $pauseStartTime = strtotime($ticket['pause_started_at']);
        $diffSeconds = max(0, $nowTime - $pauseStartTime);

        // Adjust due date by adding paused seconds
        $currentDueTime = strtotime($ticket['sla_due_at']);
        $newDueTime = $currentDueTime + $diffSeconds;
        $newDueDate = date('Y-m-d H:i:s', $newDueTime);
        $totalPausedSeconds = (int)$ticket['sla_paused_seconds'] + $diffSeconds;

        // Update ticket
        $stmtUpdate = $this->db->prepare("
            UPDATE tickets 
            SET sla_due_at = :new_due,
                sla_paused_seconds = :total_paused,
                pause_started_at = NULL,
                updated_at = :now
            WHERE id = :id
        ");
        $stmtUpdate->execute([
            'new_due'      => $newDueDate,
            'total_paused' => $totalPausedSeconds,
            'now'          => date('Y-m-d H:i:s'),
            'id'           => $ticketId
        ]);

        // Update latest open pause log
        $stmtLog = $this->db->prepare("
            UPDATE ticket_sla_pauses 
            SET resumed_at = :now, duration_seconds = :duration 
            WHERE ticket_id = :ticket_id AND resumed_at IS NULL
            ORDER BY id DESC LIMIT 1
        ");
        $stmtLog->execute([
            'now'       => date('Y-m-d H:i:s'),
            'duration'  => $diffSeconds,
            'ticket_id' => $ticketId
        ]);
    }

    /**
     * Refresh SLA status (check for OVERDUE, WARNING, CRITICAL)
     */
    public function evaluateSlaStatus(array $ticket): string
    {
        // Closed states do not turn overdue
        if (in_array($ticket['status'], ['RESOLVED', 'CLOSED', 'CANCELLED', 'DUPLICATE'])) {
            return $ticket['sla_status'] ?? 'WITHIN_SLA';
        }

        if (empty($ticket['sla_due_at'])) {
            return 'WITHIN_SLA';
        }

        $now = time();
        $due = strtotime($ticket['sla_due_at']);

        if ($now > $due) {
            return 'OVERDUE';
        }

        $start = !empty($ticket['sla_started_at']) ? strtotime($ticket['sla_started_at']) : $now;
        $totalSpan = max(1, $due - $start);
        $remaining = $due - $now;
        $ratio = $remaining / $totalSpan;

        if ($ratio <= 0.10) {
            return 'CRITICAL';
        }
        if ($ratio <= 0.30) {
            return 'WARNING';
        }

        return 'WITHIN_SLA';
    }

    /**
     * Format overdue duration e.g. "Quá 02:15:30" or "Quá 1 ngày 4 giờ" (Rule 16)
     */
    public static function formatOverdueTime(?string $dueAt): string
    {
        if (empty($dueAt)) return '';
        $diff = time() - strtotime($dueAt);
        if ($diff <= 0) return '';

        $days = floor($diff / 86400);
        $hours = floor(($diff % 86400) / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $seconds = $diff % 60;

        if ($days > 0) {
            return sprintf("Quá %d ngày %d giờ", $days, $hours);
        }
        return sprintf("Quá %02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    /**
     * Calculate SLA elapsed percentage
     */
    public static function getSlaUsagePercent(?string $startedAt, ?string $dueAt): int
    {
        if (empty($startedAt) || empty($dueAt)) return 0;
        $start = strtotime($startedAt);
        $due = strtotime($dueAt);
        $total = max(1, $due - $start);
        $elapsed = time() - $start;

        return (int)round(min(100, max(0, ($elapsed / $total) * 100)));
    }
}
