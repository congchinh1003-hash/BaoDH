<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Category;
use App\Models\ITGroup;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\User;

class TicketController extends Controller
{
    private Ticket $ticketModel;
    private \PDO $db;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->db = \App\Core\Database::getConnection();
    }

    /**
     * List all accessible tickets with filtering
     */
    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.tickets')) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập module Phiếu hỗ trợ.');
            $response->redirect('/dashboard');
            return '';
        }

        $userId = Auth::id();
        $isStaff = Auth::isStaff();
        $isManager = Auth::isManager();

        $roleScope = null;
        if (!$isStaff && !$isManager) {
            $roleScope = 'USER';
        }

        $filters = [
            'status'      => $request->input('status'),
            'priority_id' => $request->input('priority_id'),
            'group_id'    => $request->input('group_id'),
            'assignee_id' => $request->input('assignee_id'),
            'search'      => $request->input('search'),
        ];

        $tickets = $this->ticketModel->getTickets($filters, $userId, $roleScope);

        $priorityModel = new Priority();
        $priorities = $priorityModel->getAllActive();

        $itGroupModel = new ITGroup();
        $itGroups = $itGroupModel->getAllActive();

        return $this->render('tickets/index', [
            'pageTitle'  => 'Danh sách Yêu cầu (Tickets)',
            'tickets'    => $tickets,
            'filters'    => $filters,
            'priorities' => $priorities,
            'itGroups'   => $itGroups
        ]);
    }

    /**
     * Unassigned ticket queue for Manager / Leader
     */
    public function unassigned(Request $request, Response $response): string
    {
        if (!Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền xem hàng đợi chưa phân công.');
            $response->redirect('/dashboard');
            return '';
        }

        $tickets = $this->ticketModel->getTickets(['unassigned' => true]);

        return $this->render('tickets/unassigned', [
            'pageTitle' => 'Hàng đợi Ticket chưa phân công (Unassigned Queue)',
            'tickets'   => $tickets
        ]);
    }

    /**
     * Show create ticket form
     */
    public function create(Request $request, Response $response): string
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive();

        $priorityModel = new Priority();
        $priorities = $priorityModel->getAllActive();

        $userModel = new User();
        $approverUsers = $userModel->getStaffUsers();

        $assetModel = new \App\Models\Asset();
        // Load user's assets or all active assets if staff/manager
        if (Auth::isStaff() || Auth::isManager()) {
            $userAssets = $assetModel->getAll();
        } else {
            $userAssets = $assetModel->getByUser(Auth::id());
        }

        $prefillType = $request->input('type', 'INCIDENT');
        $prefillCategory = $request->input('category_id');
        $prefillService = $request->input('service_id');
        $currentUser = $userModel->findById(Auth::id() ?: 1);

        return $this->render('tickets/create', [
            'pageTitle'        => ($prefillType === 'SERVICE_REQUEST') ? 'Gửi Yêu Cầu Dịch Vụ Mới' : 'Báo Cáo Sự Cố Mới (Incident)',
            'categories'       => $categories,
            'priorities'       => $priorities,
            'userAssets'       => $userAssets,
            'approverUsers'    => $approverUsers,
            'currentUser'      => $currentUser,
            'prefillType'      => $prefillType,
            'prefillCategory'  => $prefillCategory,
            'prefillService'   => $prefillService
        ]);
    }

    /**
     * Store newly created ticket
     */
    public function store(Request $request, Response $response): void
    {
        $body = $request->getBody();

        $title = trim($body['title'] ?? '');
        $description = trim($body['description'] ?? '');
        $categoryId = (int)($body['category_id'] ?? 0);
        $serviceId = !empty($body['service_id']) ? (int)$body['service_id'] : null;
        $priorityId = (int)($body['priority_id'] ?? 0);
        $assetId = !empty($body['asset_id']) ? (int)$body['asset_id'] : null;
        $location = trim($body['location'] ?? '');
        $contactInfo = trim($body['contact_info'] ?? '');

        // ITIL parameters
        $ticketType = $body['ticket_type'] ?? 'INCIDENT';
        $impact = strtoupper($body['impact'] ?? 'MEDIUM');
        $urgency = strtoupper($body['urgency'] ?? 'MEDIUM');
        $supportTier = $body['support_tier'] ?? 'L1';
        $approverId = !empty($body['approver_id']) ? (int)$body['approver_id'] : null;
        $approvalStepName = $body['approval_step_name'] ?? 'Phê duyệt yêu cầu dịch vụ';

        // Auto-determine priority from matrix if not selected
        if (!$priorityId) {
            $priorityId = Ticket::calculatePriorityFromMatrix($impact, $urgency);
        }

        if (empty($title) || empty($description) || !$categoryId) {
            $this->setFlash('danger', 'Vui lòng điền đầy đủ các thông tin bắt buộc (*).');
            $response->redirect('/tickets/create');
            return;
        }

        try {
            $ticketId = $this->ticketModel->create([
                'ticket_type'        => $ticketType,
                'title'              => $title,
                'description'        => $description,
                'requester_id'       => Auth::id(),
                'category_id'        => $categoryId,
                'service_id'         => $serviceId,
                'asset_id'           => $assetId,
                'priority_id'        => $priorityId,
                'impact'             => $impact,
                'urgency'            => $urgency,
                'support_tier'       => $supportTier,
                'approver_id'        => $approverId,
                'approval_step_name' => $approvalStepName,
                'location'           => $location,
                'contact_info'       => $contactInfo
            ]);

            // Handle file upload if present
            $files = $request->getFiles();
            if (!empty($files['attachment']['name'])) {
                $this->handleFileUpload($ticketId, $files['attachment'], Auth::id());
            }

            // TRIGGER NOTIFICATION: Notify IT Staff / Manager of New Ticket
            $ticket = $this->ticketModel->findById($ticketId);
            $requesterName = Auth::user()['full_name'] ?? 'Người dùng';
            $notifModel = new \App\Models\Notification();

            // If approval is required, notify approver first
            if ($approverId) {
                $notifModel->notifyUser($approverId, [
                    'ticket_id' => $ticketId,
                    'title'     => 'Cần phê duyệt yêu cầu dịch vụ #' . $ticket['ticket_code'],
                    'message'   => "{$requesterName} vừa gửi yêu cầu: \"{$ticket['title']}\" cần bạn phê duyệt.",
                    'type'      => 'ticket_approval'
                ]);
            }

            if (!empty($ticket['assignee_id']) && (int)$ticket['assignee_id'] !== (int)Auth::id()) {
                $notifModel->notifyUser((int)$ticket['assignee_id'], [
                    'ticket_id' => $ticketId,
                    'title'     => 'Phân công phiếu hỗ trợ #' . $ticket['ticket_code'],
                    'message'   => "{$requesterName} đã phân công bạn xử lý: \"{$ticket['title']}\"",
                    'type'      => 'assign',
                    'link'      => "/tickets/{$ticketId}"
                ]);
            }

            if (!empty($ticket['group_id'])) {
                $notifModel->notifyGroup((int)$ticket['group_id'], [
                    'ticket_id' => $ticketId,
                    'title'     => ($ticketType === 'SERVICE_REQUEST' ? 'Yêu cầu dịch vụ mới #' : 'Sự cố mới #') . $ticket['ticket_code'],
                    'message'   => "{$requesterName} vừa gửi yêu cầu: \"{$ticket['title']}\"",
                    'type'      => 'ticket_new'
                ], Auth::id());
            } else {
                $notifModel->notifyRoles(['IT_STAFF', 'IT_MANAGER', 'ADMIN'], [
                    'ticket_id' => $ticketId,
                    'title'     => 'Ticket mới chưa phân nhóm #' . $ticket['ticket_code'],
                    'message'   => "{$requesterName} vừa gửi yêu cầu: \"{$ticket['title']}\"",
                    'type'      => 'ticket_new'
                ], Auth::id());
            }

            $this->setFlash('success', ($ticketType === 'SERVICE_REQUEST' ? 'Gửi yêu cầu dịch vụ thành công!' : 'Tạo sự cố IT thành công!') . " Mã {$ticket['ticket_code']} đã được cấp.");
            $response->redirect("/tickets/{$ticketId}");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Có lỗi khi tạo ticket: ' . $e->getMessage());
            $response->redirect('/tickets/create');
        }
    }

    /**
     * Ticket Detail Page (Timeline, SLA widget, actions)
     */
    public function show(Request $request, Response $response, string $id): string
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            $this->setFlash('danger', 'Không tìm thấy ticket yêu cầu.');
            $response->redirect('/tickets');
            return '';
        }

        // Rule 59 Security & Permission: Non-staff users can only see their own tickets
        if (!Auth::isStaff() && !Auth::isManager() && (int)$ticket['requester_id'] !== Auth::id()) {
            $this->setFlash('danger', 'Bạn không có quyền xem thông tin ticket này.');
            $response->redirect('/tickets');
            return '';
        }

        $canViewInternal = Auth::isStaff() || Auth::isManager() || Auth::isAdmin();

        $comments = $this->ticketModel->getComments($ticketId, $canViewInternal);
        $history = $this->ticketModel->getHistory($ticketId);
        $attachments = $this->ticketModel->getAttachments($ticketId);

        $itGroupModel = new ITGroup();
        $itGroups = $itGroupModel->getAllActive();

        $userModel = new User();
        // Eligible assignees in current group or all IT
        $staffMembers = $userModel->getStaffMembersByGroup($ticket['group_id'] ? (int)$ticket['group_id'] : null);

        $rating = (new \App\Models\Rating())->findByTicketId($ticketId);
        $watchers = $this->ticketModel->getWatchers($ticketId);
        $isWatching = $this->ticketModel->isWatching($ticketId, Auth::id());
        $allPriorities = (new Priority())->getAllActive();
        $allAssets = (new \App\Models\Asset())->getAll();

        // Jira Features Data
        $worklogs = $this->ticketModel->getWorklogs($ticketId);
        $totalWorklogMinutes = $this->ticketModel->getTotalWorklogMinutes($ticketId);
        $totalWorklogFormatted = Ticket::formatMinutesToTime($totalWorklogMinutes);
        $checklist = $this->ticketModel->getChecklist($ticketId);
        $linkedIssues = $this->ticketModel->getLinkedIssues($ticketId);
        $activeRemoteSession = (new \App\Models\RemoteSession())->findActiveForTicket($ticketId);

        return $this->render('tickets/show', [
            'pageTitle'              => "Ticket {$ticket['ticket_code']} - {$ticket['title']}",
            'ticket'                 => $ticket,
            'activeRemoteSession'    => $activeRemoteSession,
            'comments'               => $comments,
            'history'                => $history,
            'attachments'            => $attachments,
            'itGroups'               => $itGroups,
            'staffMembers'           => $staffMembers,
            'rating'                 => $rating,
            'watchers'               => $watchers,
            'isWatching'             => $isWatching,
            'allPriorities'          => $allPriorities,
            'allAssets'              => $allAssets,
            'worklogs'               => $worklogs,
            'totalWorklogMinutes'    => $totalWorklogMinutes,
            'totalWorklogFormatted'  => $totalWorklogFormatted,
            'checklist'              => $checklist,
            'linkedIssues'           => $linkedIssues
        ]);
    }

    /**
     * Assign ticket to IT Staff (Rule 2.4: SLA does not reset)
     */
    public function assign(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền phân công ticket.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        $toUserId = (int)$request->input('assignee_id');
        $reason = trim($request->input('reason') ?? '');

        if (!$toUserId) {
            $this->setFlash('danger', 'Vui lòng chọn nhân viên phụ trách.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->assign($ticketId, $toUserId, Auth::id(), $reason);
            
            $ticket = $this->ticketModel->findById($ticketId);
            $assignerName = Auth::user()['full_name'] ?? 'Quản trị viên';
            $notificationModel = new \App\Models\Notification();
            $notificationModel->create([
                'user_id'   => $toUserId,
                'ticket_id' => $ticketId,
                'title'     => 'Giao việc: Ticket ' . $ticket['ticket_code'],
                'message'   => "{$assignerName} đã phân công bạn xử lý: \"{$ticket['title']}\"",
                'type'      => 'assign'
            ]);

            $this->setFlash('success', 'Phân công nhân viên xử lý thành công. SLA được giữ nguyên theo quy định.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi phân công: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * Transfer ticket to another IT Group (Rule 2.3 & 2.4: SLA does not reset)
     */
    public function transferGroup(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền chuyển nhóm ticket.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        $toGroupId = (int)$request->input('to_group_id');
        $reason = trim($request->input('reason') ?? '');

        if (!$toGroupId || empty($reason)) {
            $this->setFlash('danger', 'Vui lòng chọn nhóm IT chuyển tiếp và nhập lý do chuyển nhóm.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->transferGroup($ticketId, $toGroupId, Auth::id(), $reason);

            // TRIGGER NOTIFICATION: Notify recipient group members
            $ticket = $this->ticketModel->findById($ticketId);
            $senderName = Auth::user()['full_name'] ?? 'Kỹ thuật viên';
            $notifModel = new \App\Models\Notification();
            $notifModel->notifyGroup($toGroupId, [
                'ticket_id' => $ticketId,
                'title'     => 'Chuyển giao Ticket #' . $ticket['ticket_code'],
                'message'   => "{$senderName} đã chuyển ticket \"{$ticket['title']}\" sang nhóm của bạn. Lý do: {$reason}",
                'type'      => 'transfer'
            ], Auth::id());

            $this->setFlash('success', 'Chuyển nhóm thành công. Lịch sử được lưu đầy đủ và SLA không bị reset.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi chuyển nhóm: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * Update Status (e.g. IN_PROGRESS, WAITING_USER, WAITING_VENDOR)
     */
    public function updateStatus(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền thay đổi trạng thái ticket.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        $newStatus = trim($request->input('status') ?? '');
        $note = trim($request->input('note') ?? '');

        if (empty($newStatus)) {
            $this->setFlash('danger', 'Trạng thái không hợp lệ.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->updateStatus($ticketId, $newStatus, Auth::id(), $note);

            // TRIGGER NOTIFICATION: If waiting for user
            if ($newStatus === 'WAITING_USER') {
                $ticket = $this->ticketModel->findById($ticketId);
                $notifModel = new \App\Models\Notification();
                $notifModel->create([
                    'user_id'   => (int)$ticket['requester_id'],
                    'ticket_id' => $ticketId,
                    'title'     => 'Yêu cầu phản hồi thông tin #' . $ticket['ticket_code'],
                    'message'   => "Kỹ thuật viên đang chờ phản hồi từ bạn: " . ($note ?: 'Vui lòng cung cấp thêm thông tin'),
                    'type'      => 'status'
                ]);
            }

            $this->setFlash('success', "Đã cập nhật trạng thái sang: {$newStatus}");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi đổi trạng thái: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * IT Staff resolves ticket (Rule 20: Mandatory Resolution)
     */
    public function resolve(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền hoàn thành ticket.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        $resolution = trim($request->input('resolution') ?? '');
        $rootCause = trim($request->input('root_cause') ?? '');
        $solution = trim($request->input('solution') ?? '');

        if (empty($resolution)) {
            $this->setFlash('danger', 'Bắt buộc nhập nội dung kết quả xử lý (Resolution) trước khi hoàn tất.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->resolve($ticketId, Auth::id(), $resolution, $rootCause, $solution);

            // TRIGGER NOTIFICATION: Notify Requester that ticket is resolved
            $ticket = $this->ticketModel->findById($ticketId);
            $notifModel = new \App\Models\Notification();
            $notifModel->create([
                'user_id'   => (int)$ticket['requester_id'],
                'ticket_id' => $ticketId,
                'title'     => 'Ticket đã được xử lý xong #' . $ticket['ticket_code'],
                'message'   => "Kỹ thuật viên đã xử lý xong yêu cầu của bạn. Vui lòng kiểm tra và xác nhận nghiệm thu.",
                'type'      => 'resolve'
            ]);

            $this->setFlash('success', 'Đã chuyển ticket sang RESOLVED. Chờ User xác nhận nghiệm thu để đóng.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi hoàn tất: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * User confirms resolution -> CLOSED (Rule 21)
     */
    public function confirmClose(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket || ((int)$ticket['requester_id'] !== Auth::id() && !Auth::isAdmin())) {
            $this->setFlash('danger', 'Chỉ người tạo ticket mới có quyền xác nhận hoàn thành.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        $comment = trim($request->input('confirm_note') ?? '');

        try {
            $this->ticketModel->close($ticketId, Auth::id(), $comment);

            // TRIGGER NOTIFICATION: Notify Assignee that User confirmed close
            if (!empty($ticket['assignee_id'])) {
                $notifModel = new \App\Models\Notification();
                $notifModel->create([
                    'user_id'   => (int)$ticket['assignee_id'],
                    'ticket_id' => $ticketId,
                    'title'     => 'Ticket đã được nghiệm thu #' . $ticket['ticket_code'],
                    'message'   => "Người dùng đã nghiệm thu và đóng ticket #{$ticket['ticket_code']}.",
                    'type'      => 'close'
                ]);
            }

            $this->setFlash('success', 'Bạn đã xác nhận hoàn thành ticket thành công!');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi xác nhận: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * User rates closed ticket (Rule 49 - CSAT)
     */
    public function rate(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket || ((int)$ticket['requester_id'] !== Auth::id() && !Auth::isAdmin())) {
            $this->setFlash('danger', 'Chỉ người gửi ticket mới có quyền đánh giá.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        if ($ticket['status'] !== 'CLOSED') {
            $this->setFlash('danger', 'Chỉ có thể đánh giá sau khi ticket đã được đóng hoàn tất.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        $score = (int)$request->input('score', 5);
        $comment = trim($request->input('comment') ?? '');

        try {
            $ratingModel = new \App\Models\Rating();
            $ratingModel->create($ticketId, Auth::id(), $score, $comment);

            // Notify assignee of the rating
            if (!empty($ticket['assignee_id'])) {
                $notifModel = new \App\Models\Notification();
                $notifModel->create([
                    'user_id'   => (int)$ticket['assignee_id'],
                    'ticket_id' => $ticketId,
                    'title'     => "Đánh giá dịch vụ: {$score} sao",
                    'message'   => "Người dùng đã chấm {$score}/5 sao cho ticket #{$ticket['ticket_code']}" . ($comment ? ": \"{$comment}\"" : ''),
                    'type'      => 'rating'
                ]);
            }

            $this->setFlash('success', 'Cảm ơn bạn đã đánh giá chất lượng dịch vụ hỗ trợ!');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi lưu đánh giá: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * User rejects resolution -> REOPENED (Rule 21 & 23)
     */
    public function reopen(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket || ((int)$ticket['requester_id'] !== Auth::id() && !Auth::isAdmin())) {
            $this->setFlash('danger', 'Chỉ người tạo ticket mới có quyền mở lại yêu cầu.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        $reason = trim($request->input('reopen_reason') ?? '');
        if (empty($reason)) {
            $this->setFlash('danger', 'Vui lòng nhập lý do mở lại yêu cầu.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->reopen($ticketId, Auth::id(), $reason);

            // TRIGGER NOTIFICATION: Notify Assignee or Group of Reopen
            $notifModel = new \App\Models\Notification();
            $msg = "Người dùng chưa nghiệm thu và mở lại ticket #{$ticket['ticket_code']}. Lý do: {$reason}";
            if (!empty($ticket['assignee_id'])) {
                $notifModel->create([
                    'user_id'   => (int)$ticket['assignee_id'],
                    'ticket_id' => $ticketId,
                    'title'     => 'Yêu cầu mở lại #' . $ticket['ticket_code'],
                    'message'   => $msg,
                    'type'      => 'reopen'
                ]);
            } elseif (!empty($ticket['group_id'])) {
                $notifModel->notifyGroup((int)$ticket['group_id'], [
                    'ticket_id' => $ticketId,
                    'title'     => 'Yêu cầu mở lại #' . $ticket['ticket_code'],
                    'message'   => $msg,
                    'type'      => 'reopen'
                ]);
            }

            $this->setFlash('warning', 'Yêu cầu đã được mở lại và chuyển về hàng đợi kỹ thuật viên xử lý.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi mở lại: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * Add comment (Public or Internal Note)
     */
    public function addComment(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $content = trim($request->input('content') ?? '');
        $isInternal = (bool)$request->input('is_internal');

        // Security check: Standard users cannot post internal notes
        if (!Auth::isStaff() && !Auth::isManager() && $isInternal) {
            $isInternal = false;
        }

        if (empty($content)) {
            $this->setFlash('danger', 'Nội dung bình luận không được để trống.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $commentId = $this->ticketModel->addComment($ticketId, Auth::id(), $content, $isInternal);

            // Parse mentions
            $this->processMentions($ticketId, $content, Auth::id());

            // Check file upload with comment
            $files = $request->getFiles();
            if (!empty($files['attachment']['name'])) {
                $this->handleFileUpload($ticketId, $files['attachment'], Auth::id(), $commentId);
            }

            // If ticket was WAITING_USER and requester commented, automatically resume to IN_PROGRESS
            $ticket = $this->ticketModel->findById($ticketId);
            if ($ticket['status'] === 'WAITING_USER' && (int)$ticket['requester_id'] === Auth::id()) {
                $this->ticketModel->updateStatus($ticketId, 'IN_PROGRESS', Auth::id(), 'User đã phản hồi ý kiến');
            }

            // TRIGGER NOTIFICATION: Notify the other party on new comment
            $sender = Auth::user();
            $senderName = $sender['full_name'] ?? 'Ai đó';
            $notifModel = new \App\Models\Notification();
            $snippet = mb_substr($content, 0, 70) . (mb_strlen($content) > 70 ? '...' : '');

            if ((int)$ticket['requester_id'] !== Auth::id()) {
                // IT Staff commented -> notify Requester (if not internal note)
                if (!$isInternal) {
                    $notifModel->create([
                        'user_id'   => (int)$ticket['requester_id'],
                        'ticket_id' => $ticketId,
                        'title'     => 'Phản hồi mới trên ticket #' . $ticket['ticket_code'],
                        'message'   => "{$senderName} đã phản hồi: \"{$snippet}\"",
                        'type'      => 'comment'
                    ]);
                }
            } else {
                // Requester commented -> notify Assignee or Group
                if (!empty($ticket['assignee_id'])) {
                    $notifModel->create([
                        'user_id'   => (int)$ticket['assignee_id'],
                        'ticket_id' => $ticketId,
                        'title'     => 'Người dùng phản hồi #' . $ticket['ticket_code'],
                        'message'   => "{$senderName} đã phản hồi: \"{$snippet}\"",
                        'type'      => 'comment'
                    ]);
                } elseif (!empty($ticket['group_id'])) {
                    $notifModel->notifyGroup((int)$ticket['group_id'], [
                        'ticket_id' => $ticketId,
                        'title'     => 'Người dùng phản hồi #' . $ticket['ticket_code'],
                        'message'   => "{$senderName} phản hồi trên ticket chưa phân công: \"{$snippet}\"",
                        'type'      => 'comment'
                    ], Auth::id());
                }
            }

            $this->setFlash('success', $isInternal ? 'Đã thêm ghi chú nội bộ.' : 'Đã gửi phản hồi thành công.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi khi thêm bình luận: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    private function processMentions(int $ticketId, string $content, int $senderId): void
    {
        preg_match_all('/@([a-zA-Z0-9_.-]+)/', $content, $matches);
        if (!empty($matches[1])) {
            $usernames = array_unique($matches[1]);
            $userModel = new \App\Models\User();
            $notificationModel = new \App\Models\Notification();
            
            $sender = Auth::user();
            $senderName = $sender['full_name'] ?? 'Ai đó';
            
            foreach ($usernames as $username) {
                $user = $userModel->findByUsername($username);
                if ($user) {
                    $notificationModel->create([
                        'user_id' => $user['id'],
                        'ticket_id' => $ticketId,
                        'title' => 'Bạn được nhắc đến',
                        'message' => "{$senderName} đã nhắc đến bạn (@{$username}) trong ticket #IT-" . str_pad((string)$ticketId, 6, '0', STR_PAD_LEFT),
                        'type' => 'mention'
                    ]);
                }
            }
        }
    }

    /**
     * File upload helper with strict validation (Rule 28)
     */
    private function handleFileUpload(int $ticketId, array $file, int $uploadedBy, ?int $commentId = null): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $config = require __DIR__ . '/../Config/app.php';
        $uploadConfig = $config['upload'];

        $fileName = basename($file['name']);
        $fileSize = (int)$file['size'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($ext, $uploadConfig['allowed_exts'])) {
            throw new \Exception("Định dạng file .{$ext} không được phép tải lên.");
        }

        // Validate size
        $maxBytes = $uploadConfig['max_size_mb'] * 1024 * 1024;
        if ($fileSize > $maxBytes) {
            throw new \Exception("Dung lượng file vượt quá giới hạn {$uploadConfig['max_size_mb']}MB.");
        }

        // Generate unique safe storage name
        $safeName = sprintf("%d_%s_%s.%s", $ticketId, time(), bin2hex(random_bytes(6)), $ext);
        $targetDir = $uploadConfig['dir'];
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        if (!is_writable($targetDir)) {
            $fallbackDir = dirname(__DIR__, 2) . '/uploads';
            if (!is_dir($fallbackDir)) {
                @mkdir($fallbackDir, 0777, true);
            }
            if (is_writable($fallbackDir)) {
                $targetDir = $fallbackDir;
            }
        }

        $targetPath = $targetDir . '/' . $safeName;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $relativeUrl = 'uploads/' . $safeName;
            $mimeType = mime_content_type($targetPath) ?: 'application/octet-stream';

            $this->ticketModel->addAttachment($ticketId, $uploadedBy, $fileName, $relativeUrl, $fileSize, $mimeType, $commentId);
        }
    }

    /**
     * Adjust Ticket Priority (Rule 9: Manager/Leader, audit required)
     */
    public function adjustPriority(Request $request, Response $response, string $id): void
    {
        if (!Auth::isManager() && !Auth::isAdmin()) {
            $this->setFlash('danger', 'Chỉ IT Manager / Leader mới có quyền điều chỉnh mức độ ưu tiên.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        $newPriorityId = (int)$request->input('priority_id');
        $reason = trim($request->input('reason') ?? '');

        if (!$newPriorityId || empty($reason)) {
            $this->setFlash('danger', 'Vui lòng chọn mức độ ưu tiên mới và nhập lý do điều chỉnh.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->adjustPriority($ticketId, $newPriorityId, Auth::id(), $reason);
            $this->setFlash('success', 'Đã điều chỉnh mức độ ưu tiên thành công. Lịch sử thay đổi đã được ghi nhận.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * Mark ticket as duplicate (Rule 24)
     */
    public function markDuplicate(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền đánh dấu trùng lặp.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        $duplicateOfId = (int)$request->input('duplicate_of_id');
        $reason = trim($request->input('reason') ?? '');

        if (!$duplicateOfId || $duplicateOfId === $ticketId) {
            $this->setFlash('danger', 'Vui lòng nhập ID hoặc mã ticket gốc bị trùng hợp lệ.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->markDuplicate($ticketId, $duplicateOfId, Auth::id(), $reason ?: 'Sự cố trùng lặp');
            $this->setFlash('success', 'Đã đánh dấu ticket là DUPLICATE và liên kết với ticket gốc.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * Cancel Ticket (Rule 25)
     */
    public function cancel(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            $this->setFlash('danger', 'Không tìm thấy ticket.');
            $response->redirect('/tickets');
            return;
        }

        $isOwner = (int)$ticket['requester_id'] === Auth::id();
        $canCancel = $isOwner || Auth::isManager() || Auth::isAdmin();

        if (!$canCancel) {
            $this->setFlash('danger', 'Bạn không có quyền hủy yêu cầu này.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        $reason = trim($request->input('cancel_reason') ?? '');
        if (empty($reason)) {
            $this->setFlash('danger', 'Vui lòng nhập lý do hủy yêu cầu.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->cancel($ticketId, Auth::id(), $reason);
            $this->setFlash('success', 'Đã hủy yêu cầu thành công. Trạng thái chuyển sang CANCELLED.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * Toggle Watch / Follow ticket (Rule 27)
     */
    public function toggleWatch(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $userId = Auth::id();

        if ($this->ticketModel->isWatching($ticketId, $userId)) {
            $this->ticketModel->removeWatcher($ticketId, $userId);
            $this->setFlash('info', 'Bạn đã dừng theo dõi ticket này.');
        } else {
            $this->ticketModel->addWatcher($ticketId, $userId);
            $this->setFlash('success', 'Bạn đang theo dõi ticket này. Mọi diễn biến mới sẽ được thông báo.');
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * Link/Unlink Asset to Ticket (Rule 55)
     */
    public function linkAsset(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền gán thiết bị.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        $assetId = !empty($request->input('asset_id')) ? (int)$request->input('asset_id') : null;

        try {
            $this->ticketModel->linkAsset($ticketId, $assetId, Auth::id());
            $this->setFlash('success', 'Đã cập nhật thiết bị liên quan cho ticket.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    /**
     * 1-Click Take Ticket for IT Staff (Rule 30)
     */
    public function takeTicket(Request $request, Response $response, string $id): void
    {
        if (!Auth::isStaff() && !Auth::isManager()) {
            $this->setFlash('danger', 'Bạn không có quyền tiếp nhận ticket.');
            $response->redirect("/tickets/{$id}");
            return;
        }

        $ticketId = (int)$id;
        try {
            $this->ticketModel->assign($ticketId, Auth::id(), Auth::id(), 'Kỹ thuật viên chủ động tiếp nhận xử lý');
            $this->setFlash('success', 'Bạn đã tiếp nhận xử lý ticket thành công!');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? "/tickets/{$ticketId}";
        $response->redirect($referer);
    }

    // =========================================================================
    // JIRA FEATURE 1: KANBAN BOARD
    // =========================================================================

    public function kanbanBoard(Request $request, Response $response): string
    {
        $filters = [
            'group_id'     => $request->input('group_id'),
            'assignee_id'  => $request->input('assignee_id'),
            'priority_id'  => $request->input('priority_id'),
            'urgent_only'  => $request->input('urgent_only'),
            'overdue_only' => $request->input('overdue_only'),
            'search'       => $request->input('search')
        ];

        // Quick filter "Only My Issues"
        if ($request->input('my_only')) {
            $filters['assignee_id'] = Auth::id();
        }

        $board = $this->ticketModel->getBoardTickets($filters);

        $itGroupModel = new ITGroup();
        $itGroups = $itGroupModel->getAllActive();

        $userModel = new User();
        $staffMembers = $userModel->getStaffMembersByGroup();

        return $this->render('tickets/board', [
            'pageTitle'    => 'Bảng Kanban Sự Cố (Jira Board)',
            'board'        => $board,
            'itGroups'     => $itGroups,
            'staffMembers' => $staffMembers,
            'filters'      => $filters
        ]);
    }

    /**
     * AJAX transition ticket status on Kanban Board
     */
    public function ajaxTransitionStatus(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $body = $request->getBody();
        $newStatus = trim($body['status'] ?? '');
        $reason = trim($body['reason'] ?? 'Di chuyển trên bảng Kanban');

        $allowedStatuses = ['NEW', 'ASSIGNED', 'IN_PROGRESS', 'WAITING_USER', 'WAITING_VENDOR', 'RESOLVED', 'CLOSED'];
        if (!in_array($newStatus, $allowedStatuses)) {
            $response->json(['success' => false, 'message' => 'Trạng thái không hợp lệ.'], 400);
            return;
        }

        try {
            if ($newStatus === 'RESOLVED') {
                $this->ticketModel->resolve($ticketId, Auth::id(), $reason ?: 'Giải quyết trên Kanban');
            } elseif ($newStatus === 'CLOSED') {
                $this->ticketModel->updateStatus($ticketId, 'CLOSED', Auth::id(), $reason ?: 'Đóng trên Kanban');
            } else {
                $this->ticketModel->updateStatus($ticketId, $newStatus, Auth::id(), $reason);
            }

            $ticket = $this->ticketModel->findById($ticketId);
            $response->json([
                'success' => true,
                'message' => "Đã cập nhật trạng thái sang {$ticket['status_name']}",
                'ticket'  => $ticket
            ]);
        } catch (\Exception $e) {
            $response->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // =========================================================================
    // JIRA FEATURE 2: WORKLOG / TIME TRACKING ACTIONS
    // =========================================================================

    public function addWorklog(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $timeSpent = trim($request->input('time_spent') ?? '');
        $description = trim($request->input('description') ?? '');
        $loggedDate = trim($request->input('logged_date') ?? date('Y-m-d'));

        if (empty($timeSpent)) {
            $this->setFlash('danger', 'Vui lòng nhập thời gian làm việc (ví dụ: 1h 30m, 45m).');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->addWorklog($ticketId, Auth::id(), $timeSpent, $description, $loggedDate);
            $this->setFlash('success', "Đã ghi nhận thời gian làm việc ({$timeSpent}) thành công.");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi ghi worklog: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    // =========================================================================
    // JIRA FEATURE 3: SUB-TASKS / CHECKLIST ACTIONS
    // =========================================================================

    public function addChecklistItem(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $itemText = trim($request->input('item_text') ?? '');

        if (empty($itemText)) {
            $this->setFlash('danger', 'Vui lòng nhập nội dung đầu việc.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $itemId = $this->ticketModel->addChecklistItem($ticketId, $itemText);
            if ($request->isAjax()) {
                $response->json(['success' => true, 'id' => $itemId, 'item_text' => $itemText]);
                return;
            }
            $this->setFlash('success', 'Đã thêm mục checklist mới.');
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                $response->json(['success' => false, 'message' => $e->getMessage()], 400);
                return;
            }
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    public function toggleChecklistItem(Request $request, Response $response, string $id, string $itemId): void
    {
        try {
            $result = $this->ticketModel->toggleChecklistItem((int)$itemId, Auth::id());
            $response->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            $response->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function deleteChecklistItem(Request $request, Response $response, string $id, string $itemId): void
    {
        try {
            $this->ticketModel->deleteChecklistItem((int)$itemId);
            $response->json(['success' => true]);
        } catch (\Exception $e) {
            $response->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // =========================================================================
    // JIRA FEATURE 4: ISSUE LINKING ACTIONS
    // =========================================================================

    public function linkIssue(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $targetTicketCode = trim($request->input('target_ticket') ?? '');
        $linkType = trim($request->input('link_type') ?? 'relates_to');

        if (empty($targetTicketCode)) {
            $this->setFlash('danger', 'Vui lòng nhập mã ticket cần liên kết (ví dụ: IT-000001).');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        // Look up ticket by code or ID
        $targetTicket = null;
        if (is_numeric($targetTicketCode)) {
            $targetTicket = $this->ticketModel->findById((int)$targetTicketCode);
        } else {
            $targetTicket = $this->ticketModel->findByCode($targetTicketCode);
        }

        if (!$targetTicket) {
            $this->setFlash('danger', "Không tìm thấy ticket nào với mã \"{$targetTicketCode}\".");
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        try {
            $this->ticketModel->linkIssue($ticketId, (int)$targetTicket['id'], $linkType, Auth::id());
            $this->setFlash('success', "Đã liên kết thành công với ticket #{$targetTicket['ticket_code']}.");
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi liên kết ticket: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    public function unlinkIssue(Request $request, Response $response, string $id, string $linkId): void
    {
        $ticketId = (int)$id;
        try {
            $this->ticketModel->unlinkIssue((int)$linkId);
            $this->setFlash('success', 'Đã gỡ liên kết ticket.');
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $response->redirect("/tickets/{$ticketId}");
    }

    // =========================================================================
    // ITIL ADVANCED ACTIONS
    // =========================================================================

    public function escalateTier(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $tier = strtoupper($request->input('target_tier', ''));
        $note = trim($request->input('note', ''));

        if (!in_array($tier, ['L1', 'L2', 'L3', 'VENDOR'])) {
            $this->setFlash('danger', 'Cấp độ hỗ trợ không hợp lệ.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        $this->ticketModel->escalateTier($ticketId, $tier, Auth::id(), $note);
        $this->setFlash('success', "Đã chuyển cấp hỗ trợ sang {$tier} thành công.");
        $response->redirect("/tickets/{$ticketId}");
    }

    public function linkProblem(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $problemId = (int)$request->input('problem_id');
        if ($problemId) {
            $this->ticketModel->linkProblem($ticketId, $problemId, Auth::id());
            $this->setFlash('success', 'Đã liên kết ticket với Problem thành công.');
        }
        $response->redirect("/tickets/{$ticketId}");
    }

    public function linkChange(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $changeId = (int)$request->input('change_id');
        if ($changeId) {
            $this->ticketModel->linkChange($ticketId, $changeId, Auth::id());
            $this->setFlash('success', 'Đã liên kết ticket với Change Request thành công.');
        }
        $response->redirect("/tickets/{$ticketId}");
    }

    public function exportExcel(Request $request, Response $response): void
    {
        if (!Auth::can('export.tickets')) {
            $this->setFlash('danger', 'Bạn không có quyền xuất dữ liệu danh sách Ticket.');
            $response->redirect('/tickets');
            return;
        }

        $userId = Auth::id();
        $isStaff = Auth::isStaff() || Auth::isAdmin();
        $roleScope = $isStaff ? null : 'USER';

        $tickets = $this->ticketModel->getTickets([], $userId, $roleScope);

        $filename = 'tickets_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'Mã Ticket', 'Tiêu đề', 'Người yêu cầu', 'Phòng ban', 'Kỹ thuật viên phụ trách',
            'Nhóm IT', 'Danh mục', 'Mức độ ưu tiên', 'Trạng thái', 'Hạn xử lý SLA',
            'Trạng thái SLA', 'Thời gian tạo'
        ]);

        foreach ($tickets as $t) {
            fputcsv($out, [
                $t['ticket_code'],
                $t['title'],
                $t['requester_name'] ?? 'N/A',
                $t['requester_department'] ?? 'N/A',
                $t['assignee_name'] ?? 'Chưa phân công',
                $t['group_name'] ?? 'N/A',
                $t['category_name'] ?? 'N/A',
                $t['priority_name'] ?? 'N/A',
                $t['status_name'] ?? $t['status'],
                !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'Không áp dụng',
                $t['sla_status'] ?? 'WITHIN_SLA',
                date('d/m/Y H:i:s', strtotime($t['created_at']))
            ]);
        }

        fclose($out);
        exit;
    }
}


