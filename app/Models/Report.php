<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Report extends Model
{
    /**
     * Get Executive Overview KPIs (Rule 39 & Rule 44)
     */
    public function getExecutiveKpis(array $filters = []): array
    {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['group_id'])) {
            $where .= " AND t.group_id = :group_id";
            $params['group_id'] = (int)$filters['group_id'];
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND t.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND t.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_tickets,
                SUM(CASE WHEN t.status IN ('NEW', 'ASSIGNED', 'IN_PROGRESS', 'WAITING_USER', 'WAITING_VENDOR', 'REOPENED') THEN 1 ELSE 0 END) as active_tickets,
                SUM(CASE WHEN t.status = 'RESOLVED' THEN 1 ELSE 0 END) as resolved_tickets,
                SUM(CASE WHEN t.status = 'CLOSED' THEN 1 ELSE 0 END) as closed_tickets,
                SUM(CASE WHEN t.assignee_id IS NULL AND t.status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE') THEN 1 ELSE 0 END) as unassigned_tickets,
                SUM(CASE WHEN t.sla_status = 'OVERDUE' OR (t.sla_due_at < NOW() AND t.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED', 'DUPLICATE')) THEN 1 ELSE 0 END) as overdue_tickets,
                SUM(CASE WHEN t.sla_status IN ('WITHIN_SLA', 'WARNING') OR (t.resolved_at IS NOT NULL AND t.resolved_at <= t.sla_due_at) THEN 1 ELSE 0 END) as sla_met_tickets,
                ROUND(AVG(CASE WHEN t.resolved_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, t.created_at, t.resolved_at) ELSE NULL END), 1) as avg_resolution_hours
            FROM tickets t
            $where
        ");
        $stmt->execute($params);
        $kpis = $stmt->fetch() ?: [];

        $total = (int)($kpis['total_tickets'] ?? 0);
        $overdue = (int)($kpis['overdue_tickets'] ?? 0);
        $closed = (int)($kpis['closed_tickets'] ?? 0);
        $resolved = (int)($kpis['resolved_tickets'] ?? 0);
        $done = $closed + $resolved;

        // SLA Compliance %
        if ($total > 0) {
            $kpis['sla_achievement_rate'] = round(max(0, ($total - $overdue) / $total) * 100, 1);
        } else {
            $kpis['sla_achievement_rate'] = 100.0;
        }

        // Average CSAT Rating
        $ratingStmt = $this->db->query("SELECT ROUND(AVG(score), 1) as avg_score, COUNT(*) as count FROM ticket_ratings");
        $ratingData = $ratingStmt->fetch();
        $kpis['avg_csat'] = $ratingData['avg_score'] ?? 5.0;
        $kpis['total_ratings'] = $ratingData['count'] ?? 0;

        return $kpis;
    }

    /**
     * Backlog Aging Report (Rule 43)
     * Groups active unresolved tickets into aging buckets
     */
    public function getBacklogAging(array $filters = []): array
    {
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) <= 1 THEN 1 ELSE 0 END) as age_0_1,
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) BETWEEN 2 AND 3 THEN 1 ELSE 0 END) as age_2_3,
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as age_4_7,
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) BETWEEN 8 AND 30 THEN 1 ELSE 0 END) as age_8_30,
                SUM(CASE WHEN DATEDIFF(NOW(), created_at) > 30 THEN 1 ELSE 0 END) as age_over_30,
                COUNT(*) as total_backlog
            FROM tickets
            WHERE status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE')
        ");
        return $stmt->fetch() ?: [
            'age_0_1' => 0, 'age_2_3' => 0, 'age_4_7' => 0, 'age_8_30' => 0, 'age_over_30' => 0, 'total_backlog' => 0
        ];
    }

    /**
     * SLA Breakdown by Priority (Rule 45)
     */
    public function getSlaByPriority(): array
    {
        $stmt = $this->db->query("
            SELECT 
                p.id, p.name, p.code, p.color,
                COUNT(t.id) as total_count,
                SUM(CASE WHEN t.sla_status = 'OVERDUE' OR (t.sla_due_at < NOW() AND t.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED', 'DUPLICATE')) THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN t.sla_status IN ('WITHIN_SLA', 'WARNING') OR (t.resolved_at IS NOT NULL AND t.resolved_at <= t.sla_due_at) THEN 1 ELSE 0 END) as within_sla_count
            FROM ticket_priorities p
            LEFT JOIN tickets t ON p.id = t.priority_id
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.code, p.color
            ORDER BY p.level DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Category Distribution (Rule 46)
     */
    public function getCategoryDistribution(): array
    {
        $stmt = $this->db->query("
            SELECT 
                c.id, c.name,
                COUNT(t.id) as ticket_count
            FROM ticket_categories c
            LEFT JOIN tickets t ON c.id = t.category_id
            WHERE c.is_active = 1
            GROUP BY c.id, c.name
            ORDER BY ticket_count DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Workload by IT Group (Rule 40)
     */
    public function getGroupWorkload(): array
    {
        $stmt = $this->db->query("
            SELECT 
                g.id, g.name, g.code,
                COUNT(t.id) as total_count,
                SUM(CASE WHEN t.status IN ('NEW', 'ASSIGNED', 'IN_PROGRESS', 'REOPENED') THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN t.status IN ('WAITING_USER', 'WAITING_VENDOR') THEN 1 ELSE 0 END) as waiting_count,
                SUM(CASE WHEN t.sla_status = 'OVERDUE' OR (t.sla_due_at < NOW() AND t.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED', 'DUPLICATE')) THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN t.assignee_id IS NULL AND t.status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE') THEN 1 ELSE 0 END) as unassigned_count
            FROM it_groups g
            LEFT JOIN tickets t ON g.id = t.group_id
            WHERE g.is_active = 1
            GROUP BY g.id, g.name, g.code
            ORDER BY total_count DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Detailed Performance by Staff Member (Rule 41 & Rule 48)
     */
    public function getStaffPerformance(): array
    {
        $stmt = $this->db->query("
            SELECT 
                u.id, u.full_name, u.email, u.department,
                COUNT(t.id) as total_assigned,
                SUM(CASE WHEN t.status IN ('NEW', 'ASSIGNED', 'IN_PROGRESS', 'WAITING_USER', 'WAITING_VENDOR', 'REOPENED') THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN t.status IN ('ASSIGNED', 'IN_PROGRESS', 'REOPENED') THEN 1 ELSE 0 END) as need_action_count,
                SUM(CASE WHEN t.status = 'RESOLVED' THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN t.status = 'CLOSED' THEN 1 ELSE 0 END) as closed_count,
                SUM(CASE WHEN t.reopen_count > 0 THEN t.reopen_count ELSE 0 END) as reopen_total,
                SUM(CASE WHEN t.sla_status = 'OVERDUE' OR (t.sla_due_at < NOW() AND t.status NOT IN ('RESOLVED', 'CLOSED', 'CANCELLED', 'DUPLICATE')) THEN 1 ELSE 0 END) as overdue_count,
                ROUND(AVG(tr.score), 1) as avg_rating,
                COUNT(tr.id) as rating_count
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            LEFT JOIN tickets t ON u.id = t.assignee_id
            LEFT JOIN ticket_ratings tr ON t.id = tr.ticket_id
            WHERE r.code IN ('IT_STAFF', 'IT_MANAGER') AND u.is_active = 1
            GROUP BY u.id, u.full_name, u.email, u.department
            ORDER BY closed_count DESC, active_count ASC
        ");
        $results = $stmt->fetchAll();

        // Calculate SLA compliance per staff
        foreach ($results as &$staff) {
            $assigned = (int)$staff['total_assigned'];
            $overdue = (int)$staff['overdue_count'];
            if ($assigned > 0) {
                $staff['sla_rate'] = round(max(0, ($assigned - $overdue) / $assigned) * 100, 1);
            } else {
                $staff['sla_rate'] = 100.0;
            }
        }

        return $results;
    }

    /**
     * Daily Ticket Trend for the last 14 days (Rule 47 - Chart.js)
     */
    public function getTrendDaily(int $days = 14): array
    {
        $dates = [];
        $createdData = [];
        $resolvedData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dates[] = date('d/m', strtotime($date));

            // Created
            $stmtC = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE DATE(created_at) = :d");
            $stmtC->execute(['d' => $date]);
            $createdData[] = (int)$stmtC->fetchColumn();

            // Resolved/Closed
            $stmtR = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE DATE(resolved_at) = :d OR (status = 'CLOSED' AND DATE(closed_at) = :d2)");
            $stmtR->execute(['d' => $date, 'd2' => $date]);
            $resolvedData[] = (int)$stmtR->fetchColumn();
        }

        return [
            'labels'   => $dates,
            'created'  => $createdData,
            'resolved' => $resolvedData
        ];
    }
}

