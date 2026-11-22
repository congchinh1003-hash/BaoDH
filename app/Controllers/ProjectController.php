<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\User;

class ProjectController extends Controller
{
    private Project $projectModel;
    private ProjectIssue $issueModel;
    private User $userModel;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->issueModel = new ProjectIssue();
        $this->userModel = new User();
    }

    /**
     * Kiểm tra quyền truy cập module Dự án và quyền xem dự án cụ thể:
     * 1. Bắt buộc có quyền hệ thống 'menu.projects'
     * 2. Nếu không phải ADMIN hoặc IT_MANAGER, bắt buộc phải là Lead hoặc Thành viên của dự án
     */
    private function authorizeProject(int $projectId, ?array &$project = null): bool
    {
        if (!Auth::can('menu.projects')) {
            Session::setFlash('danger', 'Bạn không có quyền truy cập module Dự án.');
            return false;
        }

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            Session::setFlash('danger', 'Dự án không tồn tại.');
            return false;
        }

        if (!Auth::isManager() && !$this->projectModel->isMember($projectId, (int)Auth::id())) {
            Session::setFlash('danger', 'Bạn không phải là thành viên và không có quyền truy cập dự án này.');
            return false;
        }

        return true;
    }

    /**
     * Kiểm tra quyền AJAX
     */
    private function authorizeProjectAjax(int $projectId): bool
    {
        if (!Auth::can('menu.projects')) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền truy cập module Dự án']);
            return false;
        }
        if (!Auth::isManager() && !$this->projectModel->isMember($projectId, (int)Auth::id())) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thao tác trên dự án này']);
            return false;
        }
        return true;
    }

    /**
     * Danh sách tất cả Dự Án mà người dùng được phép xem (Hỗ trợ phân trang 20/trang)
     */
    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.projects')) {
            Session::setFlash('danger', 'Bạn không có quyền truy cập module Dự án.');
            $response->redirect('/dashboard');
            return '';
        }

        // Lọc dự án: Admin / Manager thấy toàn bộ; Người dùng khác chỉ thấy dự án do mình làm Lead hoặc Thành viên
        $allProjects = $this->projectModel->getVisibleProjects((int)Auth::id(), Auth::isManager(), 'ACTIVE');
        $page = max(1, (int)$request->input('page', 1));
        $perPage = 20;
        $total = count($allProjects);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $projects = array_slice($allProjects, $offset, $perPage);

        return $this->render('projects/index', [
            'pageTitle'  => 'Dự Án - TaskFlow Agile',
            'projects'   => $projects,
            'pagination' => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages
            ]
        ]);
    }

    /**
     * Màn hình Tạo Dự Án Mới
     */
    public function create(Request $request, Response $response): string
    {
        if (!Auth::can('menu.projects') || !Auth::can('projects.create')) {
            Session::setFlash('danger', 'Bạn không có quyền tạo dự án mới.');
            $response->redirect('/projects');
            return '';
        }

        $users = $this->userModel->getAllUsers();
        return $this->render('projects/create', [
            'pageTitle' => 'Tạo Dự Án Mới - TaskFlow Agile',
            'users'     => $users
        ]);
    }

    /**
     * Xử lý Tạo Dự Án Mới
     */
    public function store(Request $request, Response $response): void
    {
        if (!Auth::can('menu.projects') || !Auth::can('projects.create')) {
            Session::setFlash('danger', 'Bạn không có quyền tạo dự án mới.');
            $response->redirect('/projects');
            return;
        }

        $name = trim($request->input('name', ''));
        $key = strtoupper(trim($request->input('project_key', '')));
        $leadId = (int)$request->input('lead_id', Auth::id());
        $type = $request->input('project_type', 'SOFTWARE');
        $desc = trim($request->input('description', ''));
        $icon = $request->input('avatar_icon', 'bi-rocket-takeoff-fill');
        $bg = $request->input('avatar_bg', '#6554C0');
        $members = $request->input('member_ids', []);

        if (empty($name)) {
            Session::setFlash('danger', 'Vui lòng nhập tên dự án.');
            $response->redirect('/projects/create');
            return;
        }

        $projectId = $this->projectModel->create([
            'name'         => $name,
            'project_key'  => $key,
            'lead_id'      => $leadId,
            'project_type' => $type,
            'description'  => $desc,
            'avatar_icon'  => $icon,
            'avatar_bg'    => $bg,
            'member_ids'   => $members
        ]);

        Session::setFlash('success', "Đã tạo thành công dự án '{$name}'!");
        $response->redirect("/projects/{$projectId}/board");
    }

    /**
     * Bảng Agile / Kanban Board
     */
    public function board(Request $request, Response $response, string $id): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        $filters = [
            'search'      => $request->input('search', ''),
            'assignee_id' => $request->input('assignee_id', ''),
            'issue_type'  => $request->input('issue_type', ''),
            'component'   => $request->input('component', '')
        ];

        $boardColumns = $this->issueModel->getBoardIssues($projectId, $filters);
        $members = $this->projectModel->getMembers($projectId);
        $stats = $this->projectModel->getProjectStats($projectId);
        $components = ['BILLING', 'ACCOUNTS', 'FORMS', 'FEEDBACK', 'SECURITY'];

        return $this->render('projects/board', [
            'pageTitle'    => "Board - {$project['name']} - TaskFlow",
            'project'      => $project,
            'boardColumns' => $boardColumns,
            'members'      => $members,
            'stats'        => $stats,
            'components'   => $components,
            'filters'      => $filters
        ]);
    }

    /**
     * Màn hình Quản Lý Backlog (Phân trang 20 mục / trang cho Backlog chung)
     */
    public function backlog(Request $request, Response $response, string $id): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        $filters = ['search' => $request->input('search', '')];
        $page = max(1, (int)$request->input('page', 1));
        $backlogData = $this->issueModel->getBacklogData($projectId, $filters, $page, 20);
        $members = $this->projectModel->getMembers($projectId);
        $stats = $this->projectModel->getProjectStats($projectId);

        return $this->render('projects/backlog', [
            'pageTitle'    => "Backlog - {$project['name']} - TaskFlow",
            'project'      => $project,
            'activeSprint' => $backlogData['active_sprint'],
            'backlog'      => $backlogData['backlog'],
            'pagination'   => $backlogData['pagination'],
            'members'      => $members,
            'stats'        => $stats,
            'filters'      => $filters
        ]);
    }

    /**
     * Màn hình Quản Lý Ca Kiểm Thử (Test Cases) có phân trang tối đa 20 / trang
     */
    public function testcases(Request $request, Response $response, string $id): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        $filters = [
            'status' => $request->input('status', ''),
            'search' => $request->input('search', '')
        ];

        $page = max(1, (int)$request->input('page', 1));
        $tcData = $this->issueModel->getTestCases($projectId, $filters, $page, 20);
        $members = $this->projectModel->getMembers($projectId);

        // Lấy toàn bộ để tính metrics tổng quan chính xác
        $allTc = $this->issueModel->getTestCases($projectId, [], 1, 9999);
        $metrics = [
            'total'    => $allTc['total'],
            'passed'   => count(array_filter($allTc['items'], fn($t) => $t['test_execution_status'] === 'PASSED')),
            'failed'   => count(array_filter($allTc['items'], fn($t) => $t['test_execution_status'] === 'FAILED')),
            'untested' => count(array_filter($allTc['items'], fn($t) => $t['test_execution_status'] === 'UNTESTED')),
            'blocked'  => count(array_filter($allTc['items'], fn($t) => $t['test_execution_status'] === 'BLOCKED'))
        ];

        return $this->render('projects/testcases', [
            'pageTitle'  => "Test Cases - {$project['name']} - TaskFlow",
            'project'    => $project,
            'testcases'  => $tcData['items'],
            'pagination' => $tcData,
            'members'    => $members,
            'metrics'    => $metrics,
            'filters'    => $filters
        ]);
    }

    /**
     * Màn hình Quản Lý Lỗi Phần Mềm (Bug Tracking) có phân trang tối đa 20 / trang
     */
    public function bugs(Request $request, Response $response, string $id): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        $filters = [
            'severity' => $request->input('severity', ''),
            'status'   => $request->input('status', ''),
            'search'   => $request->input('search', '')
        ];

        $page = max(1, (int)$request->input('page', 1));
        $bugData = $this->issueModel->getBugs($projectId, $filters, $page, 20);
        $members = $this->projectModel->getMembers($projectId);

        // Lấy metrics toàn bộ bugs
        $allBugs = $this->issueModel->getBugs($projectId, [], 1, 9999);
        $metrics = [
            'total'    => $allBugs['total'],
            'blocker'  => count(array_filter($allBugs['items'], fn($b) => $b['bug_severity'] === 'BLOCKER')),
            'critical' => count(array_filter($allBugs['items'], fn($b) => $b['bug_severity'] === 'CRITICAL')),
            'major'    => count(array_filter($allBugs['items'], fn($b) => $b['bug_severity'] === 'MAJOR')),
            'minor'    => count(array_filter($allBugs['items'], fn($b) => in_array($b['bug_severity'], ['MINOR', 'TRIVIAL'])))
        ];

        return $this->render('projects/bugs', [
            'pageTitle'  => "Bug Tracking - {$project['name']} - TaskFlow",
            'project'    => $project,
            'bugs'       => $bugData['items'],
            'pagination' => $bugData,
            'members'    => $members,
            'metrics'    => $metrics,
            'filters'    => $filters
        ]);
    }

    /**
     * Màn hình Tất Cả Issues Dạng Danh Sách Bảng có phân trang tối đa 20 / trang
     */
    public function issues(Request $request, Response $response, string $id): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        $filters = [
            'issue_type' => $request->input('issue_type', ''),
            'status'     => $request->input('status', ''),
            'search'     => $request->input('search', '')
        ];

        $page = max(1, (int)$request->input('page', 1));
        $issueData = $this->issueModel->getAllIssues($projectId, $filters, $page, 20);
        $members = $this->projectModel->getMembers($projectId);

        return $this->render('projects/issues', [
            'pageTitle'  => "Issues - {$project['name']} - TaskFlow",
            'project'    => $project,
            'issues'     => $issueData['items'],
            'pagination' => $issueData,
            'members'    => $members,
            'filters'    => $filters
        ]);
    }

    /**
     * Màn hình Tạo mới Công Việc (Theo giao diện mẫu Ảnh 2 ALM Layout)
     */
    public function createIssue(Request $request, Response $response, string $id): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        $defaultType = strtoupper($request->input('type', 'TASK'));
        $members = $this->projectModel->getMembers($projectId);
        $eligibleParents = $this->issueModel->getEligibleParents($projectId, $defaultType);

        return $this->render('projects/issue_create', [
            'pageTitle'       => "Tạo Công Việc Mới - {$project['name']} - TaskFlow",
            'project'         => $project,
            'members'         => $members,
            'eligibleParents' => $eligibleParents,
            'defaultType'     => $defaultType
        ]);
    }

    /**
     * Chi tiết Một Issue (Theo giao diện mẫu Ảnh 2: Title lớn, Description, Notes, Discussion, Planning, Effort, Related Work)
     */
    public function showIssue(Request $request, Response $response, string $id, string $issueId): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        $issue = $this->issueModel->findById((int)$issueId);
        if (!$issue || (int)$issue['project_id'] !== $projectId) {
            Session::setFlash('danger', 'Công việc không tồn tại hoặc không thuộc dự án này.');
            $response->redirect("/projects/{$projectId}/board");
            return '';
        }

        $members = $this->projectModel->getMembers($projectId);
        $comments = $this->issueModel->getComments((int)$issueId);
        $children = $this->issueModel->getChildren((int)$issueId);
        $eligibleParents = $this->issueModel->getEligibleParents($projectId, $issue['issue_type'], (int)$issueId);

        return $this->render('projects/issue_show', [
            'pageTitle'       => "{$issue['issue_key']}: {$issue['summary']} - TaskFlow",
            'project'         => $project,
            'issue'           => $issue,
            'members'         => $members,
            'comments'        => $comments,
            'children'        => $children,
            'eligibleParents' => $eligibleParents
        ]);
    }

    /**
     * Cập nhật thông tin Issue (Lưu theo giao diện Ảnh 2)
     */
    public function updateIssue(Request $request, Response $response, string $id, string $issueId): void
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return;
        }

        $issueIdInt = (int)$issueId;
        $issue = $this->issueModel->findById($issueIdInt);
        if (!$issue || (int)$issue['project_id'] !== $projectId) {
            Session::setFlash('danger', 'Công việc không tồn tại hoặc không thuộc dự án này.');
            $response->redirect("/projects/{$projectId}/board");
            return;
        }

        $summary = trim($request->input('summary', ''));
        if (empty($summary)) {
            Session::setFlash('danger', "Trường 'Title' không được để trống!");
            $response->redirect("/projects/{$projectId}/issues/{$issueIdInt}");
            return;
        }

        $data = [
            'summary'                 => $summary,
            'description'             => $request->input('description', ''),
            'notes'                   => $request->input('notes', ''),
            'issue_type'              => $request->input('issue_type', 'TASK'),
            'parent_id'               => $request->input('parent_id', null),
            'status'                  => $request->input('status', 'TO_DO'),
            'priority'                => $request->input('priority', 'MEDIUM'),
            'assignee_id'             => $request->input('assignee_id', null),
            'component'               => $request->input('component', null),
            'story_points'            => (int)$request->input('story_points', 1),
            'original_estimate_hours' => (float)$request->input('original_estimate_hours', 0),
            'remaining_hours'         => (float)$request->input('remaining_hours', 0),
            'completed_hours'         => (float)$request->input('completed_hours', 0),
            'start_date'              => $request->input('start_date', null),
            'due_date'                => $request->input('due_date', null),
            'risks'                   => $request->input('risks', null),
            'tags'                    => $request->input('tags', null),
            'area'                    => $request->input('area') ?: ($project['name'] ?? 'Core'),
            'iteration'               => $request->input('iteration') ?: (($project['name'] ?? 'Project') . '\Sprint 1 (2026)'),
            'test_type'               => $request->input('test_type', 'MANUAL'),
            'test_steps'              => $request->input('test_steps', null),
            'test_expected_result'    => $request->input('test_expected_result', null),
            'test_execution_status'   => $request->input('test_execution_status', 'UNTESTED'),
            'bug_severity'            => $request->input('bug_severity', 'MAJOR'),
            'steps_to_reproduce'      => $request->input('steps_to_reproduce', null),
            'actual_result'           => $request->input('actual_result', null),
            'expected_result'         => $request->input('expected_result', null),
            'environment'             => $request->input('environment', null)
        ];

        $this->issueModel->updateIssue($issueIdInt, $data);

        // TRIGGER NOTIFICATIONS FOR ISSUE UPDATE
        try {
            $notifModel = new \App\Models\Notification();
            $updaterName = Auth::user()['full_name'] ?? 'Thành viên';

            // 1. If assignee changed or newly assigned
            if (!empty($data['assignee_id']) && (int)$data['assignee_id'] !== (int)($issue['assignee_id'] ?? 0) && (int)$data['assignee_id'] !== (int)Auth::id()) {
                $notifModel->notifyUser((int)$data['assignee_id'], [
                    'title'   => "Phân công công việc: {$issue['issue_key']}",
                    'message' => "{$updaterName} đã phân công bạn thực hiện: \"{$data['summary']}\"",
                    'type'    => 'issue_assigned',
                    'link'    => "/projects/{$projectId}/issues/{$issueIdInt}"
                ]);
            }

            // 2. If status changed
            if (!empty($data['status']) && $data['status'] !== ($issue['status'] ?? '')) {
                $notifyUsers = array_filter([$issue['reporter_id'] ?? null, $data['assignee_id'] ?? null], fn($u) => !empty($u) && (int)$u !== (int)Auth::id());
                foreach (array_unique($notifyUsers) as $nUserId) {
                    $notifModel->notifyUser((int)$nUserId, [
                        'title'   => "Tiến độ công việc: {$issue['issue_key']} [{$data['status']}]",
                        'message' => "{$updaterName} đã chuyển trạng thái công việc \"{$data['summary']}\" sang [{$data['status']}].",
                        'type'    => 'issue_status',
                        'link'    => "/projects/{$projectId}/issues/{$issueIdInt}"
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        Session::setFlash('success', 'Đã lưu thay đổi công việc thành công!');
        $response->redirect("/projects/{$projectId}/issues/{$issueIdInt}");
    }

    /**
     * AJAX Thêm thảo luận/bình luận (Discussion)
     */
    public function ajaxAddComment(Request $request, Response $response): void
    {
        header('Content-Type: application/json');
        $issueId = (int)$request->input('issue_id', 0);
        $content = trim($request->input('content', ''));
        $userId = Auth::id() ?: 1;

        if ($issueId <= 0 || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Nội dung bình luận không được rỗng']);
            exit;
        }

        $commentId = $this->issueModel->addComment($issueId, $userId, $content);
        $comments = $this->issueModel->getComments($issueId);

        if ($commentId > 0) {
            try {
                $issue = $this->issueModel->findById($issueId);
                if ($issue) {
                    $notifModel = new \App\Models\Notification();
                    $commenterName = Auth::user()['full_name'] ?? 'Thành viên';
                    $snippet = mb_substr($content, 0, 70) . (mb_strlen($content) > 70 ? '...' : '');
                    $notifyUsers = array_filter([$issue['reporter_id'] ?? null, $issue['assignee_id'] ?? null], fn($u) => !empty($u) && (int)$u !== (int)$userId);
                    foreach (array_unique($notifyUsers) as $nUserId) {
                        $notifModel->notifyUser((int)$nUserId, [
                            'title'   => "Bình luận mới: {$issue['issue_key']}",
                            'message' => "{$commenterName}: \"{$snippet}\"",
                            'type'    => 'issue_comment',
                            'link'    => "/projects/{$issue['project_id']}/issues/{$issueId}"
                        ]);
                    }
                }
            } catch (\Throwable $e) {}
        }

        echo json_encode([
            'success'  => ($commentId > 0),
            'comments' => $comments,
            'message'  => 'Đã đăng bình luận thành công'
        ]);
        exit;
    }

    /**
     * AJAX Lấy danh sách mục cha đủ điều kiện theo loại (Eligible Parents)
     */
    public function ajaxGetEligibleParents(Request $request, Response $response): void
    {
        header('Content-Type: application/json');
        $projectId = (int)$request->input('project_id', 0);
        $childType = trim($request->input('type', 'TASK'));
        $excludeId = (int)$request->input('exclude_id', 0);

        if ($projectId <= 0 || !$this->authorizeProjectAjax($projectId)) {
            echo json_encode(['success' => false, 'parents' => []]);
            exit;
        }

        $parents = $this->issueModel->getEligibleParents($projectId, $childType, $excludeId ?: null);
        echo json_encode(['success' => true, 'parents' => $parents]);
        exit;
    }

    /**
     * Cài Đặt Dự Án & Quản Lý Thành Viên
     */
    public function settings(Request $request, Response $response, string $id): string
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return '';
        }

        if (!Auth::isManager() && !Auth::can('projects.manage') && !$this->projectModel->canManage($projectId, (int)Auth::id())) {
            Session::setFlash('danger', 'Bạn không có quyền quản trị cài đặt dự án này.');
            $response->redirect("/projects/{$projectId}/board");
            return '';
        }

        $members = $this->projectModel->getMembers($projectId);
        $allUsers = $this->userModel->getAllUsers();

        return $this->render('projects/settings', [
            'pageTitle' => "Cài Đặt Dự Án - {$project['name']}",
            'project'   => $project,
            'members'   => $members,
            'allUsers'  => $allUsers
        ]);
    }

    public function updateSettings(Request $request, Response $response, string $id): void
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return;
        }

        if (!Auth::isManager() && !Auth::can('projects.manage') && !$this->projectModel->canManage($projectId, (int)Auth::id())) {
            Session::setFlash('danger', 'Bạn không có quyền thay đổi cài đặt dự án này.');
            $response->redirect("/projects/{$projectId}/board");
            return;
        }

        $this->projectModel->update($projectId, [
            'name'         => $request->input('name', ''),
            'description'  => $request->input('description', ''),
            'project_type' => $request->input('project_type', 'SOFTWARE'),
            'lead_id'      => (int)$request->input('lead_id', 1),
            'avatar_icon'  => $request->input('avatar_icon', 'bi-rocket-takeoff-fill'),
            'avatar_bg'    => $request->input('avatar_bg', '#6554C0')
        ]);

        $newMemberId = (int)$request->input('new_member_id', 0);
        $newMemberRole = $request->input('new_member_role', 'MEMBER');
        if ($newMemberId > 0) {
            $this->projectModel->addMember($projectId, $newMemberId, $newMemberRole);
            if ($newMemberId !== (int)Auth::id()) {
                try {
                    $notifModel = new \App\Models\Notification();
                    $adderName = Auth::user()['full_name'] ?? 'Quản lý dự án';
                    $notifModel->notifyUser($newMemberId, [
                        'title'   => "Tham gia dự án: {$project['name']}",
                        'message' => "{$adderName} đã thêm bạn vào dự án với vai trò [{$newMemberRole}].",
                        'type'    => 'project_member',
                        'link'    => "/projects/{$projectId}/board"
                    ]);
                } catch (\Throwable $e) {}
            }
        }

        Session::setFlash('success', 'Cài đặt dự án đã được cập nhật thành công.');
        $response->redirect("/projects/{$projectId}/settings");
    }

    public function removeMember(Request $request, Response $response, string $id, string $userId): void
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return;
        }

        if (!Auth::isManager() && !Auth::can('projects.manage') && !$this->projectModel->canManage($projectId, (int)Auth::id())) {
            Session::setFlash('danger', 'Bạn không có quyền xóa thành viên khỏi dự án này.');
            $response->redirect("/projects/{$projectId}/settings");
            return;
        }

        $this->projectModel->removeMember($projectId, (int)$userId);
        Session::setFlash('info', 'Đã xóa thành viên khỏi dự án.');
        $response->redirect("/projects/{$projectId}/settings");
    }

    // ==========================================
    // AJAX ACTIONS: DRAG & DROP, QUICK CREATE
    // ==========================================

    public function ajaxUpdateStatus(Request $request, Response $response): void
    {
        header('Content-Type: application/json');
        $issueId = (int)$request->input('issue_id', 0);
        $newStatus = trim($request->input('status', ''));

        if ($issueId <= 0 || empty($newStatus)) {
            echo json_encode(['success' => false, 'message' => 'Tham số không hợp lệ']);
            exit;
        }

        $issue = $this->issueModel->findById($issueId);
        if (!$issue || !$this->authorizeProjectAjax((int)$issue['project_id'])) {
            exit;
        }

        $success = $this->issueModel->updateStatus($issueId, $newStatus);

        if ($success && $newStatus !== ($issue['status'] ?? '')) {
            try {
                $notifModel = new \App\Models\Notification();
                $updaterName = Auth::user()['full_name'] ?? 'Thành viên';
                $notifyUsers = array_filter([$issue['reporter_id'] ?? null, $issue['assignee_id'] ?? null], fn($u) => !empty($u) && (int)$u !== (int)Auth::id());
                foreach (array_unique($notifyUsers) as $nUserId) {
                    $notifModel->notifyUser((int)$nUserId, [
                        'title'   => "Bảng Kanban: {$issue['issue_key']} [{$newStatus}]",
                        'message' => "{$updaterName} đã chuyển công việc \"{$issue['summary']}\" sang cột [{$newStatus}].",
                        'type'    => 'issue_status',
                        'link'    => "/projects/{$issue['project_id']}/issues/{$issueId}"
                    ]);
                }
            } catch (\Throwable $e) {}
        }

        echo json_encode(['success' => $success]);
        exit;
    }

    public function ajaxUpdateTestStatus(Request $request, Response $response): void
    {
        header('Content-Type: application/json');
        $issueId = (int)$request->input('issue_id', 0);
        $testStatus = trim($request->input('test_status', ''));

        if ($issueId <= 0 || empty($testStatus)) {
            echo json_encode(['success' => false, 'message' => 'Tham số không hợp lệ']);
            exit;
        }

        $issue = $this->issueModel->findById($issueId);
        if (!$issue || !$this->authorizeProjectAjax((int)$issue['project_id'])) {
            exit;
        }

        $success = $this->issueModel->updateTestExecution($issueId, $testStatus);
        echo json_encode(['success' => $success]);
        exit;
    }

    public function ajaxMoveSprint(Request $request, Response $response): void
    {
        header('Content-Type: application/json');
        $issueId = (int)$request->input('issue_id', 0);
        $sprintStatus = trim($request->input('sprint_status', 'ACTIVE_SPRINT'));

        if ($issueId <= 0 || empty($sprintStatus)) {
            echo json_encode(['success' => false, 'message' => 'Tham số không hợp lệ']);
            exit;
        }

        $issue = $this->issueModel->findById($issueId);
        if (!$issue || !$this->authorizeProjectAjax((int)$issue['project_id'])) {
            exit;
        }

        $success = $this->issueModel->moveToSprint($issueId, $sprintStatus);
        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * AJAX Quick Create: Hỗ trợ tạo Backlog, Task, Bug, TestCase, Story
     * Phân quyền: Phải có quyền menu.projects và là thành viên/manager của dự án
     */
    public function ajaxQuickCreate(Request $request, Response $response): void
    {
        header('Content-Type: application/json');
        $projectId = (int)$request->input('project_id', 0);
        $summary = trim($request->input('summary', ''));
        $type = strtoupper(trim($request->input('issue_type', 'TASK')));

        if ($projectId <= 0 || empty($summary)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn Dự án và nhập Tiêu đề']);
            exit;
        }

        if (!$this->authorizeProjectAjax($projectId)) {
            exit;
        }

        $project = $this->projectModel->findById($projectId);
        if (!$project) {
            echo json_encode(['success' => false, 'message' => 'Dự án không tồn tại.']);
            exit;
        }

        // Xử lý Assignee:
        // Nếu user bình thường gửi self_assign = 1 -> gán Auth::id()
        // Nếu manager chọn assignee_id cụ thể -> gán theo lựa chọn
        $assigneeId = $request->input('assignee_id', null);
        if ($request->input('self_assign') == '1' || empty($assigneeId)) {
            if ($request->input('self_assign') == '1') {
                $assigneeId = Auth::id();
            }
        }

        $parentId = $request->input('parent_id', null);
        if ($type === 'BACKLOG') {
            $parentId = null;
        }

        $data = [
            'project_id'              => $projectId,
            'issue_type'              => $type,
            'parent_id'               => !empty($parentId) ? (int)$parentId : null,
            'summary'                 => $summary,
            'description'             => $request->input('description', ''),
            'notes'                   => $request->input('notes', ''),
            'priority'                => $request->input('priority', 'MEDIUM'),
            'status'                  => $request->input('status', 'TO_DO'),
            'sprint_status'           => ($type === 'BACKLOG') ? 'BACKLOG' : $request->input('sprint_status', 'ACTIVE_SPRINT'),
            'reporter_id'             => Auth::id() ?: 1,
            'assignee_id'             => !empty($assigneeId) ? (int)$assigneeId : null,
            'component'               => $request->input('component', null),
            'story_points'            => (int)$request->input('story_points', 1),
            'original_estimate_hours' => (float)$request->input('original_estimate_hours', 0),
            'remaining_hours'         => (float)$request->input('remaining_hours', 0),
            'completed_hours'         => (float)$request->input('completed_hours', 0),
            'start_date'              => $request->input('start_date', null),
            'due_date'                => $request->input('due_date', null),
            'risks'                   => $request->input('risks', null),
            'tags'                    => $request->input('tags', null),
            'area'                    => $request->input('area') ?: ($project['name'] ?? 'Core'),
            'iteration'               => $request->input('iteration') ?: (($project['name'] ?? 'Project') . '\Sprint 1 (2026)'),
            'test_type'               => $request->input('test_type', 'MANUAL'),
            'test_steps'              => $request->input('test_steps', null),
            'test_expected_result'    => $request->input('test_expected_result', null),
            'test_execution_status'   => $request->input('test_execution_status', 'UNTESTED'),
            'bug_severity'            => $request->input('bug_severity', 'MAJOR'),
            'steps_to_reproduce'      => $request->input('steps_to_reproduce', null),
            'actual_result'           => $request->input('actual_result', null),
            'expected_result'         => $request->input('expected_result', null),
            'environment'             => $request->input('environment', null)
        ];

        $issueId = $this->issueModel->createIssue($data);
        $createdIssue = $this->issueModel->findById($issueId);

        // Notify Assignee if assigned to someone else
        if (!empty($data['assignee_id']) && (int)$data['assignee_id'] !== (int)Auth::id()) {
            try {
                $creatorName = Auth::user()['full_name'] ?? 'Quản lý dự án';
                $projectName = $project['name'] ?? 'Dự án';
                $notifModel = new \App\Models\Notification();
                $notifModel->notifyUser((int)$data['assignee_id'], [
                    'title'   => "Giao việc mới: {$createdIssue['issue_key']}",
                    'message' => "{$creatorName} đã giao cho bạn: \"{$createdIssue['summary']}\" trong dự án {$projectName}.",
                    'type'    => 'issue_assigned',
                    'link'    => "/projects/{$projectId}/issues/{$issueId}"
                ]);
            } catch (\Throwable $e) {
                error_log("Failed to notify assignee on quick create: " . $e->getMessage());
            }
        }

        // Add initial comment to discussion if provided
        $initialComment = trim($request->input('initial_comment', ''));
        if (!empty($initialComment) && $issueId > 0) {
            $this->issueModel->addComment($issueId, Auth::id() ?: 1, $initialComment);
        }

        if ($request->isAjax() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            echo json_encode([
                'success' => true,
                'issue'   => $createdIssue,
                'message' => "Đã tạo thành công {$createdIssue['issue_key']} ({$type})!"
            ]);
            exit;
        }

        Session::setFlash('success', "Đã tạo thành công công việc {$createdIssue['issue_key']}!");
        $response->redirect("/projects/{$projectId}/issues/{$issueId}");
    }

    public function exportIssues(Request $request, Response $response, string $id): void
    {
        $projectId = (int)$id;
        $project = null;
        if (!$this->authorizeProject($projectId, $project)) {
            $response->redirect(Auth::can('menu.projects') ? '/projects' : '/dashboard');
            return;
        }

        if (!Auth::can('export.issues')) {
            Session::setFlash('danger', 'Bạn không có quyền xuất dữ liệu công việc dự án.');
            $response->redirect("/projects/{$id}/issues");
            return;
        }

        $issues = $this->issueModel->getByProject($projectId);

        $filename = 'project_' . ($project['project_key'] ?? $project['id']) . '_issues_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'Mã Issue', 'Loại', 'Tiêu đề', 'Trạng thái', 'Mức độ ưu tiên',
            'Sprint / Backlog', 'Người thực hiện', 'Người tạo', 'Story Points',
            'Ước tính (h)', 'Ngày bắt đầu', 'Hạn hoàn thành', 'Thời gian tạo'
        ]);

        foreach ($issues as $iss) {
            fputcsv($out, [
                $iss['issue_key'],
                $iss['issue_type'],
                $iss['summary'],
                $iss['status'],
                $iss['priority'],
                $iss['sprint_status'],
                $iss['assignee_name'] ?? 'Unassigned',
                $iss['reporter_name'] ?? 'N/A',
                $iss['story_points'] ?? '',
                $iss['original_estimate_hours'] ?? '',
                $iss['start_date'] ? date('d/m/Y', strtotime($iss['start_date'])) : '',
                $iss['due_date'] ? date('d/m/Y', strtotime($iss['due_date'])) : '',
                date('d/m/Y H:i:s', strtotime($iss['created_at']))
            ]);
        }

        fclose($out);
        exit;
    }
}
