<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ITGroup;
use App\Models\Report;
use App\Models\Ticket;

class ReportController extends Controller
{
    private Report $reportModel;

    public function __construct()
    {
        $this->reportModel = new Report();
    }

    /**
     * Executive Overview & Analytics Dashboard (Rule 39, 43, 44, 47)
     */
    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.reports')) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập trung tâm báo cáo.');
            $response->redirect('/dashboard');
            return '';
        }

        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập trung tâm báo cáo.');
            $response->redirect('/dashboard');
            return '';
        }

        $filters = [
            'group_id'  => $request->input('group_id'),
            'date_from' => $request->input('date_from'),
            'date_to'   => $request->input('date_to'),
        ];

        $kpis = $this->reportModel->getExecutiveKpis($filters);
        $backlogAging = $this->reportModel->getBacklogAging($filters);
        $slaPriorities = $this->reportModel->getSlaByPriority();
        $catDistribution = $this->reportModel->getCategoryDistribution();
        $groupWorkload = $this->reportModel->getGroupWorkload();
        $trendData = $this->reportModel->getTrendDaily(14);

        $itGroupModel = new ITGroup();
        $groups = $itGroupModel->getAllActive();

        return $this->render('reports/index', [
            'pageTitle'       => 'Báo Cáo & Phân Tích Hiệu Suất (Analytics & SLA)',
            'kpis'            => $kpis,
            'backlogAging'    => $backlogAging,
            'slaPriorities'   => $slaPriorities,
            'catDistribution' => $catDistribution,
            'groupWorkload'   => $groupWorkload,
            'trendData'       => $trendData,
            'groups'          => $groups,
            'filters'         => $filters
        ]);
    }

    /**
     * Staff Performance Scorecard (Rule 41 & 48)
     */
    public function staff(Request $request, Response $response): string
    {
        if (!Auth::can('menu.reports') || (!Auth::isStaff() && !Auth::isManager())) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập trang này.');
            $response->redirect('/dashboard');
            return '';
        }

        $staffData = $this->reportModel->getStaffPerformance();

        return $this->render('reports/staff', [
            'pageTitle' => 'Báo Cáo Hiệu Suất & Đánh Giá Kỹ Thuật Viên',
            'staffData' => $staffData
        ]);
    }

    /**
     * Export Report to Excel/CSV with UTF-8 BOM (Rule 50)
     */
    public function exportCsv(Request $request, Response $response): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $response->redirect('/dashboard');
            return;
        }

        $type = $request->input('type', 'tickets');
        $filename = "ServiceDesk_" . ucfirst($type) . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if ($type === 'staff') {
            // Staff Scorecard Export
            fputcsv($output, ['Kỹ thuật viên', 'Email', 'Phòng ban', 'Tổng giao', 'Đang xử lý', 'Đã hoàn thành', 'Đã đóng', 'Quá SLA', 'SLA Đạt %', 'Đánh giá TB (Sao)', 'Số lượt đánh giá']);
            $data = $this->reportModel->getStaffPerformance();
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['full_name'],
                    $row['email'],
                    $row['department'] ?: 'N/A',
                    $row['total_assigned'],
                    $row['active_count'],
                    $row['resolved_count'],
                    $row['closed_count'],
                    $row['overdue_count'],
                    $row['sla_rate'] . '%',
                    $row['avg_rating'] ?: 'Chưa có',
                    $row['rating_count']
                ]);
            }
        } else {
            // Tickets Export
            fputcsv($output, ['Mã Ticket', 'Tiêu đề', 'Người yêu cầu', 'Nhóm IT', 'KTV phụ trách', 'Mức độ', 'Trạng thái', 'Hạn SLA', 'Trạng thái SLA', 'Khởi tạo']);
            $ticketModel = new Ticket();
            $tickets = $ticketModel->getTickets();
            foreach ($tickets as $t) {
                fputcsv($output, [
                    $t['ticket_code'],
                    $t['title'],
                    $t['requester_name'],
                    $t['group_name'] ?: 'Chưa phân nhóm',
                    $t['assignee_name'] ?: 'Chờ phân công',
                    $t['priority_name'],
                    $t['status_name'] ?? $t['status'],
                    $t['sla_due_at'],
                    $t['sla_status'],
                    $t['created_at']
                ]);
            }
        }

        fclose($output);
        exit;
    }
}

