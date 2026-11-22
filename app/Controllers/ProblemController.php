<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\User;

class ProblemController extends Controller
{
    private Problem $problemModel;
    private Ticket $ticketModel;

    public function __construct()
    {
        $this->problemModel = new Problem();
        $this->ticketModel = new Ticket();
    }

    /**
     * List all Problems
     */
    public function index(Request $request, Response $response): string
    {
        $filters = [
            'status'         => $request->input('status'),
            'priority_id'    => $request->input('priority_id'),
            'is_known_error' => $request->input('is_known_error'),
            'search'         => $request->input('search'),
        ];

        $problems = $this->problemModel->getAll($filters);

        $priorityModel = new Priority();
        $priorities = $priorityModel->getAllActive();

        return $this->render('problems/index', [
            'pageTitle'  => 'Quản Lý Sự Cố Gốc (Problem Management & RCA)',
            'problems'   => $problems,
            'filters'    => $filters,
            'priorities' => $priorities
        ]);
    }

    /**
     * Show Create Problem Form
     */
    public function create(Request $request, Response $response): string
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive();

        $priorityModel = new Priority();
        $priorities = $priorityModel->getAllActive();

        $userModel = new User();
        $staffUsers = $userModel->getStaffUsers();

        $fromTicketId = $request->input('from_ticket_id');
        $prefillTicket = null;
        if ($fromTicketId) {
            $prefillTicket = $this->ticketModel->findById((int)$fromTicketId);
        }

        return $this->render('problems/create', [
            'pageTitle'     => 'Khai Báo Problem Mới',
            'categories'    => $categories,
            'priorities'    => $priorities,
            'staffUsers'    => $staffUsers,
            'prefillTicket' => $prefillTicket
        ]);
    }

    /**
     * Store new Problem
     */
    public function store(Request $request, Response $response): void
    {
        $title = trim($request->input('title', ''));
        $description = trim($request->input('description', ''));
        $categoryId = (int)$request->input('category_id');
        $priorityId = (int)$request->input('priority_id');
        $assignedTo = $request->input('assigned_to') ? (int)$request->input('assigned_to') : null;
        $linkedTicketId = $request->input('linked_ticket_id') ? (int)$request->input('linked_ticket_id') : null;

        if (empty($title) || empty($description) || !$categoryId || !$priorityId) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ tiêu đề, mô tả, danh mục và mức độ ưu tiên.');
            $response->redirect('/problems/create');
            return;
        }

        try {
            $problemId = $this->problemModel->create([
                'title'       => $title,
                'description' => $description,
                'category_id' => $categoryId,
                'priority_id' => $priorityId,
                'assigned_to' => $assignedTo,
                'created_by'  => Auth::id()
            ]);

            if ($linkedTicketId) {
                $this->problemModel->linkIncident($problemId, $linkedTicketId);
            }

            // Notify assigned lead
            if ($assignedTo && (int)$assignedTo !== (int)Auth::id()) {
                $prob = $this->problemModel->findById($problemId);
                $probCode = $prob['problem_code'] ?? "#{$problemId}";
                $assignerName = Auth::user()['full_name'] ?? 'Quản lý';
                (new \App\Models\Notification())->create([
                    'user_id' => $assignedTo,
                    'title'   => "Giao điều tra Problem: {$probCode}",
                    'message' => "{$assignerName} đã phân công bạn chủ trì điều tra nguyên nhân gốc (RCA) cho: \"{$title}\"",
                    'type'    => 'problem',
                    'link'    => "/problems/{$problemId}"
                ]);
            }

            Session::setFlash('success', 'Đã khởi tạo Problem thành công.');
            $response->redirect('/problems/' . $problemId);
        } catch (\Exception $e) {
            Session::setFlash('error', 'Lỗi khi tạo Problem: ' . $e->getMessage());
            $response->redirect('/problems/create');
        }
    }

    /**
     * Show Problem details & RCA
     */
    public function show(Request $request, Response $response, string $id): string
    {
        $id = (int)$id;
        $problem = $this->problemModel->findById($id);

        if (!$problem) {
            Session::setFlash('error', 'Không tìm thấy Problem yêu cầu.');
            $response->redirect('/problems');
            return '';
        }

        $linkedIncidents = $this->problemModel->getLinkedIncidents($id);

        $userModel = new User();
        $staffUsers = $userModel->getStaffUsers();

        return $this->render('problems/show', [
            'pageTitle'       => "Problem: {$problem['problem_code']} - {$problem['title']}",
            'problem'         => $problem,
            'linkedIncidents' => $linkedIncidents,
            'staffUsers'      => $staffUsers
        ]);
    }

    /**
     * Update RCA (Root Cause, Workaround, Known Error, Status)
     */
    public function updateRca(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $rootCause = trim($request->input('root_cause', ''));
        $workaround = trim($request->input('workaround', ''));
        $permanentSolution = trim($request->input('permanent_solution', ''));
        $status = $request->input('status');
        $isKnownError = $request->input('is_known_error') ? 1 : 0;
        $assignedTo = $request->input('assigned_to') ? (int)$request->input('assigned_to') : null;

        $updateData = [
            'root_cause'         => $rootCause,
            'workaround'         => $workaround,
            'permanent_solution' => $permanentSolution,
            'is_known_error'     => $isKnownError,
        ];

        if ($status) {
            $updateData['status'] = $status;
        }
        if ($assignedTo) {
            $updateData['assigned_to'] = $assignedTo;
        }

        $this->problemModel->update($id, $updateData);
        Session::setFlash('success', 'Đã cập nhật hồ sơ RCA & giải pháp tạm thời thành công.');
        $response->redirect('/problems/' . $id);
    }

    /**
     * Link an Incident Ticket to this Problem
     */
    public function linkIncident(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $ticketCodeOrId = trim($request->input('ticket_identifier', ''));

        if (empty($ticketCodeOrId)) {
            Session::setFlash('error', 'Vui lòng nhập mã hoặc ID của Incident ticket.');
            $response->redirect('/problems/' . $id);
            return;
        }

        // Look up ticket
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
            $response->redirect('/problems/' . $id);
            return;
        }

        $this->problemModel->linkIncident($id, (int)$ticket['id']);
        Session::setFlash('success', "Đã liên kết sự cố #{$ticket['ticket_code']} với Problem.");
        $response->redirect('/problems/' . $id);
    }

    /**
     * Resolve Problem and batch-resolve linked Incidents
     */
    public function resolve(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $permanentSolution = trim($request->input('permanent_solution', ''));
        $batchResolveIncidents = $request->input('batch_resolve_incidents') ? true : false;

        if (empty($permanentSolution)) {
            Session::setFlash('error', 'Vui lòng nhập giải pháp triệt để trước khi đóng Problem.');
            $response->redirect('/problems/' . $id);
            return;
        }

        $this->problemModel->resolve($id, $permanentSolution, Auth::id(), $batchResolveIncidents);

        Session::setFlash('success', 'Problem đã được giải quyết triệt để' . ($batchResolveIncidents ? ' và đã cập nhật đóng hàng loạt Incident liên kết.' : '.'));
        $response->redirect('/problems/' . $id);
    }

    /**
     * Publish Workaround / RCA to Knowledge Base (KEDB)
     */
    public function publishKb(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $articleId = $this->problemModel->publishToKnowledgeBase($id, Auth::id());

        if ($articleId) {
            Session::setFlash('success', "Đã tạo bài viết KEDB thành công trên Knowledge Base (#{$articleId}).");
        } else {
            Session::setFlash('error', 'Không thể tạo bài viết KB. Vui lòng đảm bảo Problem có mô tả và giải pháp.');
        }

        $response->redirect('/problems/' . $id);
    }
}
