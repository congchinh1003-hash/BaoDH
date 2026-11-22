<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\ITGroup;
use App\Models\Ticket;
use PDO;

class DashboardController extends Controller
{
    private PDO $db;
    private Ticket $ticketModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ticketModel = new Ticket();
    }

    /**
     * User Dashboard (Section 34)
     */
    public function userDashboard(Request $request, Response $response): string
    {
        $userId = Auth::id();

        // Query KPIs
        $stmtKpi = $this->db->prepare("
            SELECT 
                COUNT(*) as total_tickets,
                SUM(CASE WHEN status IN ('NEW', 'ASSIGNED', 'IN_PROGRESS', 'REOPENED') THEN 1 ELSE 0 END) as in_progress_tickets,
                SUM(CASE WHEN status = 'WAITING_USER' THEN 1 ELSE 0 END) as waiting_user_tickets,
                SUM(CASE WHEN status = 'RESOLVED' THEN 1 ELSE 0 END) as resolved_tickets,
                SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as closed_tickets,
                SUM(CASE WHEN sla_due_at < NOW() AND status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE', 'RESOLVED') THEN 1 ELSE 0 END) as overdue_tickets
            FROM tickets
            WHERE requester_id = :uid
        ");
        $stmtKpi->execute(['uid' => $userId]);
        $kpi = $stmtKpi->fetch();

        // Recent tickets
        $tickets = $this->ticketModel->getTickets([], $userId, 'USER');

        return $this->render('dashboard/user', [
            'pageTitle' => 'My Dashboard - Tổng quan Yêu cầu',
            'kpi'       => $kpi,
            'tickets'   => $tickets
        ]);
    }

    /**
     * IT Staff Work Queue Dashboard (Section 35, 36, 37)
     */
    public function staffWorkQueue(Request $request, Response $response): string
    {
        $userId = Auth::id();

        // Query Staff KPIs
        $stmtKpi = $this->db->prepare("
            SELECT 
                COUNT(*) as total_assigned,
                SUM(CASE WHEN status IN ('ASSIGNED', 'IN_PROGRESS', 'REOPENED') THEN 1 ELSE 0 END) as active_tickets,
                SUM(CASE WHEN sla_due_at < NOW() AND status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE', 'RESOLVED') THEN 1 ELSE 0 END) as overdue_tickets,
                SUM(CASE WHEN priority_id = 3 AND status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE', 'RESOLVED') THEN 1 ELSE 0 END) as urgent_tickets,
                SUM(CASE WHEN status = 'WAITING_USER' THEN 1 ELSE 0 END) as waiting_user_tickets,
                SUM(CASE WHEN status = 'WAITING_VENDOR' THEN 1 ELSE 0 END) as waiting_vendor_tickets,
                SUM(CASE WHEN status = 'RESOLVED' AND DATE(resolved_at) = CURDATE() THEN 1 ELSE 0 END) as resolved_today
            FROM tickets
            WHERE assignee_id = :uid OR group_id IN (SELECT group_id FROM it_group_members WHERE user_id = :uid2)
        ");
        $stmtKpi->execute(['uid' => $userId, 'uid2' => $userId]);
        $kpi = $stmtKpi->fetch();

        // Get tickets for queue
        $allTickets = $this->ticketModel->getTickets([], $userId, 'STAFF_QUEUE');

        // Group into work queues:
        // 1. Need Action Now (Overdue, Near SLA, Urgent)
        // 2. In Progress
        // 3. Waiting (Waiting user, Waiting vendor)
        // 4. Completed today
        $queueUrgent = [];
        $queueInProgress = [];
        $queueWaiting = [];
        $queueCompleted = [];
        foreach ($allTickets as $t) {
            $isOverdue = !empty($t['sla_due_at']) && strtotime($t['sla_due_at']) < time() && !in_array($t['status'], ['CLOSED', 'CANCELLED', 'DUPLICATE', 'RESOLVED']);
            $isUrgent = (($t['priority_code'] ?? '') === 'URGENT' || ((int)($t['priority_id'] ?? 0) === 3));

            if (in_array($t['status'], ['RESOLVED', 'CLOSED'])) {
                $queueCompleted[] = $t;
            } elseif ($isOverdue || $isUrgent) {
                $queueUrgent[] = $t;
            } elseif ($t['status'] === 'IN_PROGRESS' || $t['status'] === 'ASSIGNED' || $t['status'] === 'REOPENED') {
                $queueInProgress[] = $t;
            } elseif (in_array($t['status'], ['WAITING_USER', 'WAITING_VENDOR'])) {
                $queueWaiting[] = $t;
            } else {
                $queueInProgress[] = $t;
            }
        }

        return $this->render('dashboard/staff_queue', [
            'pageTitle'        => 'IT Staff - Hàng đợi Công việc (Work Queue)',
            'kpi'              => $kpi,
            'queueUrgent'      => $queueUrgent,
            'queueInProgress'  => $queueInProgress,
            'queueWaiting'     => $queueWaiting,
            'queueCompleted'   => $queueCompleted
        ]);
    }

    /**
     * Manager / Leader Dashboard (Section 38, 40, 41)
     */
    public function managerDashboard(Request $request, Response $response): string
    {
        // System-wide KPIs
        $stmtKpi = $this->db->query("
            SELECT 
                COUNT(*) as total_tickets,
                SUM(CASE WHEN status = 'NEW' OR assignee_id IS NULL THEN 1 ELSE 0 END) as unassigned_tickets,
                SUM(CASE WHEN status IN ('NEW', 'ASSIGNED', 'IN_PROGRESS', 'REOPENED') THEN 1 ELSE 0 END) as active_tickets,
                SUM(CASE WHEN status = 'WAITING_USER' THEN 1 ELSE 0 END) as waiting_user_tickets,
                SUM(CASE WHEN status = 'WAITING_VENDOR' THEN 1 ELSE 0 END) as waiting_vendor_tickets,
                SUM(CASE WHEN status = 'RESOLVED' THEN 1 ELSE 0 END) as resolved_tickets,
                SUM(CASE WHEN status = 'CLOSED' THEN 1 ELSE 0 END) as closed_tickets,
                SUM(CASE WHEN sla_due_at < NOW() AND status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE', 'RESOLVED') THEN 1 ELSE 0 END) as overdue_tickets
            FROM tickets
        ");
        $kpi = $stmtKpi->fetch();

        // Calculate SLA achievement %
        $stmtSla = $this->db->query("
            SELECT 
                COUNT(*) as total_resolved,
                SUM(CASE WHEN resolved_at <= sla_due_at THEN 1 ELSE 0 END) as on_time_resolved
            FROM tickets
            WHERE resolved_at IS NOT NULL
        ");
        $slaMetrics = $stmtSla->fetch();
        $totalResolved = (int)($slaMetrics['total_resolved'] ?? 0);
        $onTime = (int)($slaMetrics['on_time_resolved'] ?? 0);
        $slaRate = $totalResolved > 0 ? round(($onTime / $totalResolved) * 100, 1) : 100;
        $kpi['sla_rate'] = $slaRate;

        // Group Workload (Section 40)
        $stmtGroupWorkload = $this->db->query("
            SELECT 
                g.id, g.name as group_name, g.code as group_code,
                COUNT(t.id) as total,
                SUM(CASE WHEN t.status IN ('ASSIGNED', 'IN_PROGRESS', 'REOPENED') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN t.status IN ('WAITING_USER', 'WAITING_VENDOR') THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN t.sla_due_at < NOW() AND t.status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE', 'RESOLVED') THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN t.assignee_id IS NULL OR t.status = 'NEW' THEN 1 ELSE 0 END) as unassigned
            FROM it_groups g
            LEFT JOIN tickets t ON g.id = t.group_id
            WHERE g.is_active = 1
            GROUP BY g.id
            ORDER BY total DESC
        ");
        $groupWorkload = $stmtGroupWorkload->fetchAll();

        // Employee Workload (Section 41)
        $stmtStaffWorkload = $this->db->query("
            SELECT 
                u.id, u.full_name, u.email,
                COUNT(t.id) as total_assigned,
                SUM(CASE WHEN t.status IN ('ASSIGNED', 'IN_PROGRESS', 'REOPENED') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN t.status IN ('ASSIGNED', 'IN_PROGRESS', 'REOPENED') AND t.sla_due_at >= NOW() THEN 1 ELSE 0 END) as need_action,
                SUM(CASE WHEN t.sla_due_at < NOW() AND t.status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE', 'RESOLVED') THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN t.status = 'RESOLVED' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN t.status = 'CLOSED' THEN 1 ELSE 0 END) as closed,
                SUM(t.reopen_count) as total_reopens
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            LEFT JOIN tickets t ON u.id = t.assignee_id
            WHERE r.code IN ('IT_STAFF', 'IT_MANAGER') AND u.is_active = 1
            GROUP BY u.id
            ORDER BY active DESC, overdue DESC
        ");
        $staffWorkload = $stmtStaffWorkload->fetchAll();

        // Unassigned list preview
        $unassignedTickets = $this->ticketModel->getTickets(['unassigned' => true]);

        return $this->render('dashboard/manager', [
            'pageTitle'         => 'IT Manager / Leader Dashboard - Quản lý Vận hành & SLA',
            'kpi'               => $kpi,
            'groupWorkload'     => $groupWorkload,
            'staffWorkload'     => $staffWorkload,
            'unassignedTickets' => $unassignedTickets
        ]);
    }
}

