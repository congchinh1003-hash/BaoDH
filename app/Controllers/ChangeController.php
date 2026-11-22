<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\ChangeRequest;
use App\Models\Ticket;
use App\Models\User;

class ChangeController extends Controller
{
    private ChangeRequest $changeModel;
    private Ticket $ticketModel;

    public function __construct()
    {
        $this->changeModel = new ChangeRequest();
        $this->ticketModel = new Ticket();
    }

    /**
     * List all RFC Change Requests
     */
    public function index(Request $request, Response $response): string
    {
        $filters = [
            'status'            => $request->input('status'),
            'environment_stage' => $request->input('environment_stage'),
            'risk_level'        => $request->input('risk_level'),
            'search'            => $request->input('search'),
        ];

        $changes = $this->changeModel->getAll($filters);

        return $this->render('changes/index', [
            'pageTitle' => 'Quản Lý Thay Đổi & Phát Hành (Change & Release Management)',
            'changes'   => $changes,
            'filters'   => $filters
        ]);
    }

    /**
     * Show RFC Create Form
     */
    public function create(Request $request, Response $response): string
    {
        $userModel = new User();
        $staffUsers = $userModel->getStaffUsers();

        $fromTicketId = $request->input('from_ticket_id');
        $prefillTicket = null;
        if ($fromTicketId) {
            $prefillTicket = $this->ticketModel->findById((int)$fromTicketId);
        }

        return $this->render('changes/create', [
            'pageTitle'     => 'Tạo Yêu Cầu Thay Đổi Hệ Thống (RFC)',
            'staffUsers'    => $staffUsers,
            'prefillTicket' => $prefillTicket
        ]);
    }

    /**
     * Store new RFC
     */
    public function store(Request $request, Response $response): void
    {
        $title = trim($request->input('title', ''));
        $description = trim($request->input('description', ''));
        $reason = trim($request->input('reason', ''));
        $changeType = $request->input('change_type', 'NORMAL');
        $riskLevel = $request->input('risk_level', 'MEDIUM');
        $stage = $request->input('environment_stage', 'DEV');
        $implPlan = trim($request->input('implementation_plan', ''));
        $rollbackPlan = trim($request->input('rollback_plan', ''));
        $testPlan = trim($request->input('test_plan', ''));
        $scheduledStart = $request->input('scheduled_start') ?: null;
        $scheduledEnd = $request->input('scheduled_end') ?: null;
        $assignedTo = $request->input('assigned_to') ? (int)$request->input('assigned_to') : null;
        $linkedTicketId = $request->input('linked_ticket_id') ? (int)$request->input('linked_ticket_id') : null;

        if (empty($title) || empty($description) || empty($reason)) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ tiêu đề, mô tả và lý do thay đổi.');
            $response->redirect('/changes/create');
            return;
        }

        try {
            $changeId = $this->changeModel->create([
                'title'               => $title,
                'description'         => $description,
                'reason'              => $reason,
                'change_type'         => $changeType,
                'risk_level'          => $riskLevel,
                'environment_stage'   => $stage,
                'implementation_plan' => $implPlan,
                'rollback_plan'       => $rollbackPlan,
                'test_plan'           => $testPlan,
                'scheduled_start'     => $scheduledStart,
                'scheduled_end'       => $scheduledEnd,
                'requested_by'        => Auth::id(),
                'assigned_to'         => $assignedTo,
            ]);

            if ($linkedTicketId) {
                $this->changeModel->linkTicket($changeId, $linkedTicketId);
            }

            // Notification: Notify assigned engineer
            $notifModel = new \App\Models\Notification();
            $change = $this->changeModel->findById($changeId);
            $changeCode = $change['change_code'] ?? "#{$changeId}";
            $creatorName = Auth::user()['full_name'] ?? 'Quản lý';

            if ($assignedTo && (int)$assignedTo !== (int)Auth::id()) {
                $notifModel->create([
                    'user_id' => $assignedTo,
                    'title'   => "Phân công thực hiện RFC: {$changeCode}",
                    'message' => "{$creatorName} đã giao bạn thực hiện thay đổi: \"{$title}\"",
                    'type'    => 'change',
                    'link'    => "/changes/{$changeId}"
                ]);
            }

            // Notify CAB members / managers for approval
            $notifModel->notifyRoles(['IT_MANAGER', 'ADMIN'], [
                'title'   => "CAB: Yêu cầu xét duyệt RFC {$changeCode}",
                'message' => "Yêu cầu thay đổi \"{$title}\" (Mức rủi ro: {$riskLevel}) đang chờ xét duyệt.",
                'type'    => 'cab_review',
                'link'    => "/changes/{$changeId}"
            ], Auth::id());

            Session::setFlash('success', 'Đã khởi tạo yêu cầu thay đổi (RFC) thành công.');
            $response->redirect('/changes/' . $changeId);
        } catch (\Exception $e) {
            Session::setFlash('error', 'Lỗi khi tạo RFC: ' . $e->getMessage());
            $response->redirect('/changes/create');
        }
    }

    /**
     * Show Change details, stage pipeline, plans, and CAB status
     */
    public function show(Request $request, Response $response, string $id): string
    {
        $id = (int)$id;
        $change = $this->changeModel->findById($id);

        if (!$change) {
            Session::setFlash('error', 'Không tìm thấy Change Request.');
            $response->redirect('/changes');
            return '';
        }

        $linkedTickets = $this->changeModel->getLinkedTickets($id);
        $userModel = new User();
        $staffUsers = $userModel->getStaffUsers();

        return $this->render('changes/show', [
            'pageTitle'     => "Change RFC: {$change['change_code']} - {$change['title']}",
            'change'        => $change,
            'linkedTickets' => $linkedTickets,
            'staffUsers'    => $staffUsers
        ]);
    }

    /**
     * Advance Environment Stage (DEV -> TEST -> MIG_UAT -> RELEASE_PROD)
     */
    public function advanceStage(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $targetStage = $request->input('target_stage');
        $note = trim($request->input('note', ''));

        if (!in_array($targetStage, ['DEV', 'TEST', 'MIG_UAT', 'RELEASE_PROD'])) {
            Session::setFlash('error', 'Giai đoạn môi trường không hợp lệ.');
            $response->redirect('/changes/' . $id);
            return;
        }

        $this->changeModel->advanceStage($id, $targetStage, $note);
        Session::setFlash('success', "Đã chuyển môi trường thay đổi sang {$targetStage} thành công.");
        $response->redirect('/changes/' . $id);
    }

    /**
     * CAB (Change Advisory Board) Approval
     */
    public function cabApprove(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $decision = strtoupper($request->input('decision'));
        $note = trim($request->input('note', ''));

        if (!in_array($decision, ['APPROVED', 'REJECTED'])) {
            Session::setFlash('error', 'Quyết định CAB không hợp lệ.');
            $response->redirect('/changes/' . $id);
            return;
        }

        $this->changeModel->cabApprove($id, Auth::id(), $decision, $note);

        // Notify requester and assignee of CAB decision
        $change = $this->changeModel->findById($id);
        $changeCode = $change['change_code'] ?? "#{$id}";
        $approverName = Auth::user()['full_name'] ?? 'Thành viên CAB';
        $statusText = $decision === 'APPROVED' ? 'ĐỒNG Ý (Approved)' : 'TỪ CHỐI (Rejected)';
        $notifModel = new \App\Models\Notification();

        $recipients = array_filter(array_unique([(int)($change['requested_by'] ?? 0), (int)($change['assigned_to'] ?? 0)]));
        foreach ($recipients as $uid) {
            if ($uid !== (int)Auth::id()) {
                $notifModel->create([
                    'user_id' => $uid,
                    'title'   => "CAB {$statusText}: {$changeCode}",
                    'message' => "{$approverName} vừa ghi nhận quyết định {$statusText} cho RFC \"{$change['title']}\"." . ($note ? " Ghi chú: {$note}" : ''),
                    'type'    => 'cab_decision',
                    'link'    => "/changes/{$id}"
                ]);
            }
        }

        Session::setFlash('success', "Hội đồng CAB đã ghi nhận quyết định: {$decision}.");
        $response->redirect('/changes/' . $id);
    }

    /**
     * Complete change (SUCCESSFUL or FAILED / ROLLED_BACK)
     */
    public function complete(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $outcome = strtoupper($request->input('outcome'));
        $note = trim($request->input('note', ''));

        if (!in_array($outcome, ['SUCCESSFUL', 'FAILED', 'ROLLED_BACK'])) {
            Session::setFlash('error', 'Kết quả thay đổi không hợp lệ.');
            $response->redirect('/changes/' . $id);
            return;
        }

        $this->changeModel->completeChange($id, $outcome, $note);
        Session::setFlash('success', "Đã đóng Change Request với trạng thái: {$outcome}.");
        $response->redirect('/changes/' . $id);
    }

    /**
     * Link Ticket to Change
     */
    public function linkTicket(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $ticketCodeOrId = trim($request->input('ticket_identifier', ''));

        if (empty($ticketCodeOrId)) {
            Session::setFlash('error', 'Vui lòng nhập mã hoặc ID của ticket.');
            $response->redirect('/changes/' . $id);
            return;
        }

        $ticket = null;
        if (is_numeric($ticketCodeOrId)) {
            $ticket = $this->ticketModel->findById((int)$ticketCodeOrId);
        }
        if (!$ticket) {
            $stmt = (new \App\Core\Database())->getConnection()->prepare("SELECT id FROM tickets WHERE ticket_code = :code LIMIT 1");
            $stmt->execute(['code' => $ticketCodeOrId]);
            $tId = $stmt->fetchColumn();
            if ($tId) {
                $ticket = $this->ticketModel->findById((int)$tId);
            }
        }

        if (!$ticket) {
            Session::setFlash('error', "Không tìm thấy ticket '{$ticketCodeOrId}'.");
            $response->redirect('/changes/' . $id);
            return;
        }

        $this->changeModel->linkTicket($id, (int)$ticket['id']);
        Session::setFlash('success', "Đã liên kết ticket #{$ticket['ticket_code']} với Change Request.");
        $response->redirect('/changes/' . $id);
    }
}
