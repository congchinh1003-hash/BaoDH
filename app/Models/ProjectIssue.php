<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ProjectIssue extends Model
{
    /**
     * Lấy danh sách công việc hiển thị trên bảng Kanban / Scrum Board (Active Sprint)
     */
    public function getBoardIssues(int $projectId, array $filters = []): array
    {
        $sql = "
            SELECT pi.*,
                   u_assignee.username as assignee_username,
                   u_assignee.full_name as assignee_name,
                   u_assignee.avatar as assignee_avatar,
                   u_reporter.username as reporter_username,
                   u_reporter.full_name as reporter_name,
                   p_parent.issue_key as parent_key,
                   p_parent.summary as parent_summary,
                   p_parent.issue_type as parent_type
            FROM project_issues pi
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            WHERE pi.project_id = :pid AND pi.sprint_status = 'ACTIVE_SPRINT'
        ";

        $params = ['pid' => $projectId];

        // Lọc theo từ khóa tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (pi.summary LIKE :search OR pi.issue_key LIKE :search OR pi.component LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        // Lọc theo người thực hiện
        if (!empty($filters['assignee_id'])) {
            $sql .= " AND pi.assignee_id = :assignee_id";
            $params['assignee_id'] = (int)$filters['assignee_id'];
        }

        // Lọc theo loại công việc
        if (!empty($filters['issue_type'])) {
            $sql .= " AND pi.issue_type = :issue_type";
            $params['issue_type'] = strtoupper(trim($filters['issue_type']));
        }

        // Lọc theo component
        if (!empty($filters['component'])) {
            $sql .= " AND pi.component = :component";
            $params['component'] = trim($filters['component']);
        }

        $sql .= " ORDER BY pi.sort_order ASC, pi.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $allIssues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Nhóm theo 4 cột: TO DO, IN PROGRESS, IN REVIEW, DONE
        $columns = [
            'TO_DO'       => [],
            'IN_PROGRESS' => [],
            'IN_REVIEW'   => [],
            'DONE'        => []
        ];

        foreach ($allIssues as $iss) {
            $col = $iss['status'];
            if (isset($columns[$col])) {
                $columns[$col][] = $iss;
            } else {
                $columns['TO_DO'][] = $iss;
            }
        }

        return $columns;
    }

    /**
     * Lấy danh sách Backlog (gồm phần Active Sprint và phần Backlog chung có phân trang)
     */
    public function getBacklogData(int $projectId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        // 1. Active Sprint issues
        $activeSql = "
            SELECT pi.*,
                   u_assignee.username as assignee_username,
                   u_assignee.full_name as assignee_name,
                   u_assignee.avatar as assignee_avatar,
                   u_reporter.full_name as reporter_name,
                   p_parent.issue_key as parent_key,
                   p_parent.summary as parent_summary,
                   (SELECT COUNT(*) FROM project_issues child WHERE child.parent_id = pi.id) as children_count
            FROM project_issues pi
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            WHERE pi.project_id = :pid AND pi.sprint_status = 'ACTIVE_SPRINT'
        ";
        $activeParams = ['pid' => $projectId];
        if (!empty($filters['search'])) {
            $activeSql .= " AND (pi.summary LIKE :search OR pi.issue_key LIKE :search)";
            $activeParams['search'] = '%' . trim($filters['search']) . '%';
        }
        $activeSql .= " ORDER BY pi.sort_order ASC, pi.created_at DESC";
        $activeStmt = $this->db->prepare($activeSql);
        $activeStmt->execute($activeParams);
        $activeSprint = $activeStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Backlog items with pagination
        $countSql = "
            SELECT COUNT(*)
            FROM project_issues pi
            WHERE pi.project_id = :pid AND pi.sprint_status = 'BACKLOG'
        ";
        $countParams = ['pid' => $projectId];
        if (!empty($filters['search'])) {
            $countSql .= " AND (pi.summary LIKE :search OR pi.issue_key LIKE :search)";
            $countParams['search'] = '%' . trim($filters['search']) . '%';
        }
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($countParams);
        $totalBacklog = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalBacklog / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $backlogSql = "
            SELECT pi.*,
                   u_assignee.username as assignee_username,
                   u_assignee.full_name as assignee_name,
                   u_assignee.avatar as assignee_avatar,
                   u_reporter.full_name as reporter_name,
                   p_parent.issue_key as parent_key,
                   p_parent.summary as parent_summary,
                   (SELECT COUNT(*) FROM project_issues child WHERE child.parent_id = pi.id) as children_count
            FROM project_issues pi
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            WHERE pi.project_id = :pid AND pi.sprint_status = 'BACKLOG'
        ";
        $backlogParams = ['pid' => $projectId];
        if (!empty($filters['search'])) {
            $backlogSql .= " AND (pi.summary LIKE :search OR pi.issue_key LIKE :search)";
            $backlogParams['search'] = '%' . trim($filters['search']) . '%';
        }
        $backlogSql .= " ORDER BY pi.sort_order ASC, pi.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $backlogStmt = $this->db->prepare($backlogSql);
        $backlogStmt->execute($backlogParams);
        $backlogItems = $backlogStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'active_sprint' => $activeSprint,
            'backlog'       => $backlogItems,
            'pagination'    => [
                'total'       => $totalBacklog,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $totalPages
            ]
        ];
    }

    /**
     * Lấy danh sách ca kiểm thử (TestCase) trong dự án (hỗ trợ phân trang nếu truyền $page)
     */
    public function getTestCases(int $projectId, array $filters = [], ?int $page = null, int $perPage = 20): array
    {
        $params = ['pid' => $projectId];
        $whereSql = "WHERE pi.project_id = :pid AND pi.issue_type = 'TESTCASE'";

        if (!empty($filters['status'])) {
            $whereSql .= " AND pi.test_execution_status = :test_status";
            $params['test_status'] = strtoupper(trim($filters['status']));
        }

        if (!empty($filters['search'])) {
            $whereSql .= " AND (pi.summary LIKE :search OR pi.issue_key LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if ($page === null) {
            $sql = "
                SELECT pi.*,
                       u_assignee.full_name as assignee_name,
                       u_assignee.avatar as assignee_avatar,
                       u_reporter.full_name as reporter_name,
                       p_parent.issue_key as parent_key,
                       p_parent.summary as parent_summary,
                       p_parent.issue_type as parent_type
                FROM project_issues pi
                LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
                LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
                LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
                {$whereSql}
                ORDER BY pi.created_at DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Count for pagination
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM project_issues pi {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT pi.*,
                   u_assignee.full_name as assignee_name,
                   u_assignee.avatar as assignee_avatar,
                   u_reporter.full_name as reporter_name,
                   p_parent.issue_key as parent_key,
                   p_parent.summary as parent_summary,
                   p_parent.issue_type as parent_type
            FROM project_issues pi
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            {$whereSql}
            ORDER BY pi.created_at DESC LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Lấy danh sách lỗi phần mềm (Bug) trong dự án (hỗ trợ phân trang nếu truyền $page)
     */
    public function getBugs(int $projectId, array $filters = [], ?int $page = null, int $perPage = 20): array
    {
        $params = ['pid' => $projectId];
        $whereSql = "WHERE pi.project_id = :pid AND pi.issue_type = 'BUG'";

        if (!empty($filters['severity'])) {
            $whereSql .= " AND pi.bug_severity = :severity";
            $params['severity'] = strtoupper(trim($filters['severity']));
        }

        if (!empty($filters['status'])) {
            $whereSql .= " AND pi.status = :status";
            $params['status'] = strtoupper(trim($filters['status']));
        }

        if (!empty($filters['search'])) {
            $whereSql .= " AND (pi.summary LIKE :search OR pi.issue_key LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if ($page === null) {
            $sql = "
                SELECT pi.*,
                       u_assignee.full_name as assignee_name,
                       u_assignee.avatar as assignee_avatar,
                       u_reporter.full_name as reporter_name,
                       p_parent.issue_key as parent_key,
                       p_parent.summary as parent_summary,
                       p_parent.issue_type as parent_type
                FROM project_issues pi
                LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
                LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
                LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
                {$whereSql}
                ORDER BY (pi.bug_severity = 'BLOCKER') DESC, (pi.bug_severity = 'CRITICAL') DESC, pi.created_at DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM project_issues pi {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT pi.*,
                   u_assignee.full_name as assignee_name,
                   u_assignee.avatar as assignee_avatar,
                   u_reporter.full_name as reporter_name,
                   p_parent.issue_key as parent_key,
                   p_parent.summary as parent_summary,
                   p_parent.issue_type as parent_type
            FROM project_issues pi
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            {$whereSql}
            ORDER BY (pi.bug_severity = 'BLOCKER') DESC, (pi.bug_severity = 'CRITICAL') DESC, pi.created_at DESC LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Lấy tất cả issues kèm bộ lọc và phân trang (nếu truyền $page)
     */
    public function getAllIssues(int $projectId, array $filters = [], ?int $page = null, int $perPage = 20): array
    {
        $params = ['pid' => $projectId];
        $whereSql = "WHERE pi.project_id = :pid";

        if (!empty($filters['issue_type'])) {
            $whereSql .= " AND pi.issue_type = :type";
            $params['type'] = strtoupper(trim($filters['issue_type']));
        }

        if (!empty($filters['status'])) {
            $whereSql .= " AND pi.status = :status";
            $params['status'] = strtoupper(trim($filters['status']));
        }

        if (!empty($filters['search'])) {
            $whereSql .= " AND (pi.summary LIKE :search OR pi.issue_key LIKE :search OR pi.component LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        if (!empty($filters['assignee_id'])) {
            $whereSql .= " AND pi.assignee_id = :assignee_id";
            $params['assignee_id'] = (int)$filters['assignee_id'];
        }

        if ($page === null) {
            $sql = "
                SELECT pi.*,
                       u_assignee.full_name as assignee_name,
                       u_assignee.avatar as assignee_avatar,
                       u_reporter.full_name as reporter_name,
                       p_parent.issue_key as parent_key,
                       p_parent.summary as parent_summary,
                       p_parent.issue_type as parent_type
                FROM project_issues pi
                LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
                LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
                LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
                {$whereSql}
                ORDER BY pi.created_at DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM project_issues pi {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT pi.*,
                   u_assignee.full_name as assignee_name,
                   u_assignee.avatar as assignee_avatar,
                   u_reporter.full_name as reporter_name,
                   p_parent.issue_key as parent_key,
                   p_parent.summary as parent_summary,
                   p_parent.issue_type as parent_type
            FROM project_issues pi
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            {$whereSql}
            ORDER BY pi.created_at DESC LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Lấy toàn bộ danh sách issues của một dự án (không giới hạn phân trang) cho xuất file/xử lý hàng loạt
     */
    public function getByProject(int $projectId, array $filters = []): array
    {
        return $this->getAllIssues($projectId, $filters, null);
    }

    /**
     * Tìm kiếm Issue theo ID kèm thông tin mục cha
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT pi.*,
                   p.name as project_name, p.project_key,
                   u_assignee.full_name as assignee_name, u_assignee.username as assignee_username, u_assignee.avatar as assignee_avatar, u_assignee.email as assignee_email,
                   u_reporter.full_name as reporter_name, u_reporter.username as reporter_username, u_reporter.avatar as reporter_avatar, u_reporter.email as reporter_email,
                   p_parent.issue_key as parent_key, p_parent.summary as parent_summary, p_parent.issue_type as parent_type
            FROM project_issues pi
            JOIN projects p ON pi.project_id = p.id
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            WHERE pi.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $issue = $stmt->fetch(PDO::FETCH_ASSOC);
        return $issue ?: null;
    }

    /**
     * Tìm kiếm Issue theo Issue Key
     */
    public function findByKey(string $key): ?array
    {
        $stmt = $this->db->prepare("
            SELECT pi.*, p.name as project_name, p.project_key,
                   u_assignee.full_name as assignee_name, u_reporter.full_name as reporter_name,
                   p_parent.issue_key as parent_key, p_parent.summary as parent_summary, p_parent.issue_type as parent_type
            FROM project_issues pi
            JOIN projects p ON pi.project_id = p.id
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            LEFT JOIN users u_reporter ON pi.reporter_id = u_reporter.id
            LEFT JOIN project_issues p_parent ON pi.parent_id = p_parent.id
            WHERE pi.issue_key = :key
            LIMIT 1
        ");
        $stmt->execute(['key' => trim($key)]);
        $issue = $stmt->fetch(PDO::FETCH_ASSOC);
        return $issue ?: null;
    }

    /**
     * Lấy danh sách công việc được phân công cho một nhân sự qua mọi dự án
     */
    public function getByAssignee(int $userId, int $limit = 5): array
    {
        $sql = "
            SELECT pi.*, p.name as project_name, p.project_key
            FROM project_issues pi
            JOIN projects p ON pi.project_id = p.id
            WHERE pi.assignee_id = :uid
            ORDER BY pi.updated_at DESC
            LIMIT {$limit}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy các danh mục đủ điều kiện làm Cha (Eligible Parents) theo nghiệp vụ:
     * - BUG, TASK, STORY: Cha là BACKLOG
     * - TESTCASE: Cha là BUG, TASK, STORY
     * - BACKLOG: Không có cha
     */
    public function getEligibleParents(int $projectId, string $childType, ?int $excludeId = null): array
    {
        $childType = strtoupper(trim($childType));

        if ($childType === 'BACKLOG') {
            return [];
        }

        $params = ['pid' => $projectId];
        $excludeSql = "";
        if ($excludeId) {
            $excludeSql = " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        if ($childType === 'TESTCASE') {
            // TestCase có cha là BUG, TASK, hoặc STORY/ISSUE
            $sql = "
                SELECT id, issue_key, summary, issue_type, status
                FROM project_issues
                WHERE project_id = :pid AND issue_type IN ('BUG', 'TASK', 'STORY') {$excludeSql}
                ORDER BY (issue_type = 'BUG') DESC, created_at DESC
            ";
        } else {
            // BUG, TASK, STORY có cha là BACKLOG
            $sql = "
                SELECT id, issue_key, summary, issue_type, status
                FROM project_issues
                WHERE project_id = :pid AND (issue_type = 'BACKLOG' OR (issue_type = 'STORY' AND sprint_status = 'BACKLOG')) {$excludeSql}
                ORDER BY created_at DESC
            ";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách các issue con (Children) trực tiếp liên kết
     */
    public function getChildren(int $parentId): array
    {
        $stmt = $this->db->prepare("
            SELECT pi.*,
                   u_assignee.full_name as assignee_name,
                   u_assignee.avatar as assignee_avatar
            FROM project_issues pi
            LEFT JOIN users u_assignee ON pi.assignee_id = u_assignee.id
            WHERE pi.parent_id = :pid
            ORDER BY pi.issue_type ASC, pi.created_at ASC
        ");
        $stmt->execute(['pid' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo Issue mới hỗ trợ đầy đủ Parent-Child, Effort, Dates, Notes theo Image 2
     */
    public function createIssue(array $data): int
    {
        $projectId = (int)$data['project_id'];

        // Lấy thông tin Project Key
        $prjStmt = $this->db->prepare("SELECT project_key FROM projects WHERE id = :pid LIMIT 1");
        $prjStmt->execute(['pid' => $projectId]);
        $prjKey = $prjStmt->fetchColumn() ?: 'PRJ';

        // Lấy số thứ tự lớn nhất hiện tại
        $maxStmt = $this->db->prepare("SELECT MAX(issue_number) FROM project_issues WHERE project_id = :pid");
        $maxStmt->execute(['pid' => $projectId]);
        $maxNum = (int)$maxStmt->fetchColumn();
        $nextNum = max(1, $maxNum + 1);

        $issueKey = $prjKey . '-' . $nextNum;
        $issueType = strtoupper($data['issue_type'] ?? 'TASK');

        // Parent ID theo quy tắc
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        if ($issueType === 'BACKLOG') {
            $parentId = null; // Backlog không có cha
        }

        // Tự động gán sprint_status = BACKLOG nếu loại là BACKLOG
        $sprintStatus = ($issueType === 'BACKLOG') ? 'BACKLOG' : ($data['sprint_status'] ?? 'ACTIVE_SPRINT');

        $stmt = $this->db->prepare("
            INSERT INTO project_issues (
                project_id, issue_key, issue_number, issue_type, parent_id, summary, description, notes,
                status, priority, reporter_id, assignee_id, sprint_status, component, story_points,
                original_estimate_hours, remaining_hours, completed_hours, start_date, due_date, risks, tags, area, iteration,
                test_type, test_steps, test_expected_result, test_execution_status,
                bug_severity, steps_to_reproduce, actual_result, expected_result, environment, created_at
            ) VALUES (
                :pid, :key, :num, :type, :parent_id, :summary, :desc, :notes,
                :status, :priority, :reporter, :assignee, :sprint, :component, :points,
                :orig_est, :rem_hrs, :comp_hrs, :start_date, :due_date, :risks, :tags, :area, :iteration,
                :test_type, :test_steps, :test_expected_result, :test_execution_status,
                :bug_severity, :steps_to_reproduce, :actual_result, :expected_result, :environment, NOW()
            )
        ");

        $stmt->execute([
            'pid'                   => $projectId,
            'key'                   => $issueKey,
            'num'                   => $nextNum,
            'type'                  => $issueType,
            'parent_id'             => $parentId,
            'summary'               => trim($data['summary']),
            'desc'                  => trim($data['description'] ?? ''),
            'notes'                 => trim($data['notes'] ?? ''),
            'status'                => !empty($data['status']) ? str_replace(' ', '_', strtoupper(trim($data['status']))) : 'TO_DO',
            'priority'              => $data['priority'] ?? 'MEDIUM',
            'reporter'              => (int)($data['reporter_id'] ?? 1),
            'assignee'              => !empty($data['assignee_id']) ? (int)$data['assignee_id'] : null,
            'sprint'                => $sprintStatus,
            'component'             => !empty($data['component']) ? strtoupper(trim($data['component'])) : null,
            'points'                => (int)($data['story_points'] ?? 1),
            'orig_est'              => (float)($data['original_estimate_hours'] ?? 0.0),
            'rem_hrs'               => (float)($data['remaining_hours'] ?? 0.0),
            'comp_hrs'              => (float)($data['completed_hours'] ?? 0.0),
            'start_date'            => !empty($data['start_date']) ? $data['start_date'] : null,
            'due_date'              => !empty($data['due_date']) ? $data['due_date'] : null,
            'risks'                 => !empty($data['risks']) ? trim($data['risks']) : null,
            'tags'                  => !empty($data['tags']) ? trim($data['tags']) : null,
            'area'                  => !empty($data['area']) ? trim($data['area']) : 'Main/Core',
            'iteration'             => !empty($data['iteration']) ? trim($data['iteration']) : 'Sprint 1 (2026)',
            'test_type'             => $data['test_type'] ?? 'MANUAL',
            'test_steps'            => $data['test_steps'] ?? null,
            'test_expected_result'  => $data['test_expected_result'] ?? null,
            'test_execution_status' => $data['test_execution_status'] ?? 'UNTESTED',
            'bug_severity'          => $data['bug_severity'] ?? 'MAJOR',
            'steps_to_reproduce'    => $data['steps_to_reproduce'] ?? null,
            'actual_result'         => $data['actual_result'] ?? null,
            'expected_result'       => $data['expected_result'] ?? null,
            'environment'           => $data['environment'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật Issue với đầy đủ trường theo thiết kế Image 2
     */
    public function updateIssue(int $id, array $data): bool
    {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        $stmt = $this->db->prepare("
            UPDATE project_issues 
            SET summary = :summary,
                description = :desc,
                notes = :notes,
                issue_type = :type,
                parent_id = :parent_id,
                status = :status,
                priority = :priority,
                assignee_id = :assignee,
                component = :component,
                story_points = :points,
                original_estimate_hours = :orig_est,
                remaining_hours = :rem_hrs,
                completed_hours = :comp_hrs,
                start_date = :start_date,
                due_date = :due_date,
                risks = :risks,
                tags = :tags,
                area = :area,
                iteration = :iteration,
                test_type = :test_type,
                test_steps = :test_steps,
                test_expected_result = :test_expected_result,
                test_execution_status = :test_execution_status,
                bug_severity = :bug_severity,
                steps_to_reproduce = :steps_to_reproduce,
                actual_result = :actual_result,
                expected_result = :expected_result,
                environment = :environment,
                updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute([
            'id'                   => $id,
            'summary'              => trim($data['summary']),
            'desc'                 => trim($data['description'] ?? ''),
            'notes'                => trim($data['notes'] ?? ''),
            'type'                 => $data['issue_type'] ?? 'TASK',
            'parent_id'            => $parentId,
            'status'               => $data['status'] ?? 'TO_DO',
            'priority'             => $data['priority'] ?? 'MEDIUM',
            'assignee'             => !empty($data['assignee_id']) ? (int)$data['assignee_id'] : null,
            'component'            => !empty($data['component']) ? strtoupper(trim($data['component'])) : null,
            'points'               => (int)($data['story_points'] ?? 1),
            'orig_est'             => (float)($data['original_estimate_hours'] ?? 0.0),
            'rem_hrs'              => (float)($data['remaining_hours'] ?? 0.0),
            'comp_hrs'             => (float)($data['completed_hours'] ?? 0.0),
            'start_date'           => !empty($data['start_date']) ? $data['start_date'] : null,
            'due_date'             => !empty($data['due_date']) ? $data['due_date'] : null,
            'risks'                => !empty($data['risks']) ? trim($data['risks']) : null,
            'tags'                 => !empty($data['tags']) ? trim($data['tags']) : null,
            'area'                 => !empty($data['area']) ? trim($data['area']) : 'Main/Core',
            'iteration'            => !empty($data['iteration']) ? trim($data['iteration']) : 'Sprint 1 (2026)',
            'test_type'            => $data['test_type'] ?? 'MANUAL',
            'test_steps'           => $data['test_steps'] ?? null,
            'test_expected_result' => $data['test_expected_result'] ?? null,
            'test_execution_status'=> $data['test_execution_status'] ?? 'UNTESTED',
            'bug_severity'         => $data['bug_severity'] ?? 'MAJOR',
            'steps_to_reproduce'   => $data['steps_to_reproduce'] ?? null,
            'actual_result'        => $data['actual_result'] ?? null,
            'expected_result'      => $data['expected_result'] ?? null,
            'environment'          => $data['environment'] ?? null
        ]);
    }

    public function updateStatus(int $id, string $newStatus): bool
    {
        $newStatus = str_replace(' ', '_', strtoupper(trim($newStatus)));
        $allowed = ['TO_DO', 'IN_PROGRESS', 'IN_REVIEW', 'DONE'];
        if (!in_array($newStatus, $allowed)) return false;

        $stmt = $this->db->prepare("UPDATE project_issues SET status = :status, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id, 'status' => $newStatus]);
    }

    public function updateTestExecution(int $id, string $testStatus): bool
    {
        $allowed = ['UNTESTED', 'PASSED', 'FAILED', 'BLOCKED'];
        if (!in_array($testStatus, $allowed)) return false;

        $stmt = $this->db->prepare("
            UPDATE project_issues 
            SET test_execution_status = :test_status,
                status = CASE WHEN :test_status2 = 'PASSED' THEN 'DONE' ELSE status END,
                updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'           => $id,
            'test_status'  => $testStatus,
            'test_status2' => $testStatus
        ]);
    }

    public function moveToSprint(int $id, string $sprintStatus): bool
    {
        $allowed = ['ACTIVE_SPRINT', 'BACKLOG', 'COMPLETED'];
        if (!in_array($sprintStatus, $allowed)) return false;

        $stmt = $this->db->prepare("UPDATE project_issues SET sprint_status = :sprint, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id, 'sprint' => $sprintStatus]);
    }

    /**
     * Thảo luận / Bình luận (Discussion in Image 2)
     */
    public function getComments(int $issueId): array
    {
        $stmt = $this->db->prepare("
            SELECT pic.*, u.full_name, u.username, u.avatar
            FROM project_issue_comments pic
            JOIN users u ON pic.user_id = u.id
            WHERE pic.issue_id = :issue_id
            ORDER BY pic.created_at ASC
        ");
        $stmt->execute(['issue_id' => $issueId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addComment(int $issueId, int $userId, string $content): int
    {
        $content = trim($content);
        if (empty($content)) return 0;

        $stmt = $this->db->prepare("
            INSERT INTO project_issue_comments (issue_id, user_id, content, created_at)
            VALUES (:issue_id, :user_id, :content, NOW())
        ");
        $stmt->execute([
            'issue_id' => $issueId,
            'user_id'  => $userId,
            'content'  => $content
        ]);
        return (int)$this->db->lastInsertId();
    }
}
