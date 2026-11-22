<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Project extends Model
{
    public function getAll(string $status = 'ACTIVE'): array
    {
        $sql = "
            SELECT p.*, u.full_name as lead_name, u.username as lead_username, u.avatar as lead_avatar,
                   COUNT(pi.id) as total_issues,
                   SUM(CASE WHEN pi.status = 'DONE' THEN 1 ELSE 0 END) as done_issues,
                   SUM(CASE WHEN pi.issue_type = 'BUG' THEN 1 ELSE 0 END) as bug_count,
                   SUM(CASE WHEN pi.issue_type = 'TESTCASE' THEN 1 ELSE 0 END) as testcase_count,
                   SUM(CASE WHEN pi.issue_type = 'TASK' THEN 1 ELSE 0 END) as task_count,
                   SUM(CASE WHEN pi.issue_type = 'STORY' THEN 1 ELSE 0 END) as story_count
            FROM projects p
            LEFT JOIN users u ON p.lead_id = u.id
            LEFT JOIN project_issues pi ON p.id = pi.project_id
            WHERE p.status = :status
            GROUP BY p.id
            ORDER BY p.updated_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($projects as &$prj) {
            $total = (int)$prj['total_issues'];
            $done = (int)$prj['done_issues'];
            $prj['progress_percent'] = ($total > 0) ? round(($done / $total) * 100) : 0;
        }

        return $projects;
    }

    /**
     * Lấy danh sách dự án mà người dùng được phép thấy:
     * - Admin / Manager (canSeeAll = true): Thấy toàn bộ dự án
     * - Người dùng thường / Kỹ thuật viên: Chỉ thấy dự án do mình làm Lead hoặc là Thành viên
     */
    public function getVisibleProjects(?int $userId = null, bool $canSeeAll = false, string $status = 'ACTIVE'): array
    {
        if ($canSeeAll || $userId === null) {
            return $this->getAll($status);
        }

        $sql = "
            SELECT p.*, u.full_name as lead_name, u.username as lead_username, u.avatar as lead_avatar,
                   COUNT(pi.id) as total_issues,
                   SUM(CASE WHEN pi.status = 'DONE' THEN 1 ELSE 0 END) as done_issues,
                   SUM(CASE WHEN pi.issue_type = 'BUG' THEN 1 ELSE 0 END) as bug_count,
                   SUM(CASE WHEN pi.issue_type = 'TESTCASE' THEN 1 ELSE 0 END) as testcase_count,
                   SUM(CASE WHEN pi.issue_type = 'TASK' THEN 1 ELSE 0 END) as task_count,
                   SUM(CASE WHEN pi.issue_type = 'STORY' THEN 1 ELSE 0 END) as story_count
            FROM projects p
            LEFT JOIN users u ON p.lead_id = u.id
            LEFT JOIN project_issues pi ON p.id = pi.project_id
            WHERE p.status = :status
              AND (
                  p.lead_id = :uid 
                  OR EXISTS (SELECT 1 FROM project_members pm WHERE pm.project_id = p.id AND pm.user_id = :uid)
              )
            GROUP BY p.id
            ORDER BY p.updated_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status, 'uid' => $userId]);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($projects as &$prj) {
            $total = (int)$prj['total_issues'];
            $done = (int)$prj['done_issues'];
            $prj['progress_percent'] = ($total > 0) ? round(($done / $total) * 100) : 0;
        }

        return $projects;
    }

    /**
     * Kiểm tra người dùng có phải là thành viên hoặc Lead của dự án hay không
     */
    public function isMember(int $projectId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM projects p 
            WHERE p.id = :pid AND (
                p.lead_id = :uid 
                OR EXISTS (SELECT 1 FROM project_members pm WHERE pm.project_id = p.id AND pm.user_id = :uid)
            )
            LIMIT 1
        ");
        $stmt->execute(['pid' => $projectId, 'uid' => $userId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Kiểm tra người dùng có quyền quản trị cài đặt dự án (Lead hoặc vai trò ADMIN trong dự án)
     */
    public function canManage(int $projectId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM projects p 
            WHERE p.id = :pid AND (
                p.lead_id = :uid 
                OR EXISTS (SELECT 1 FROM project_members pm WHERE pm.project_id = p.id AND pm.user_id = :uid AND pm.role = 'ADMIN')
            )
            LIMIT 1
        ");
        $stmt->execute(['pid' => $projectId, 'uid' => $userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.full_name as lead_name, u.username as lead_username, u.avatar as lead_avatar, u.email as lead_email
            FROM projects p
            LEFT JOIN users u ON p.lead_id = u.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }

    public function findByKey(string $key): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.full_name as lead_name, u.username as lead_username
            FROM projects p
            LEFT JOIN users u ON p.lead_id = u.id
            WHERE p.project_key = :key
            LIMIT 1
        ");
        $stmt->execute(['key' => strtoupper(trim($key))]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }

    public function create(array $data): int
    {
        $key = strtoupper(trim($data['project_key']));
        // Tự động rút gọn Key nếu người dùng không nhập
        if (empty($key)) {
            $words = explode(' ', trim($data['name']));
            if (count($words) >= 2) {
                $key = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            } else {
                $key = strtoupper(substr($data['name'], 0, 3));
            }
        }
        $key = preg_replace('/[^A-Z0-9]/', '', $key);
        if (strlen($key) < 2) $key = 'PRJ';

        // Check if key already exists, if so append number
        $baseKey = $key;
        $suffix = 1;
        while ($this->findByKey($key)) {
            $key = $baseKey . $suffix++;
        }

        $stmt = $this->db->prepare("
            INSERT INTO projects (project_key, name, description, project_type, lead_id, avatar_icon, avatar_bg, status, created_at)
            VALUES (:key, :name, :description, :type, :lead_id, :icon, :bg, 'ACTIVE', NOW())
        ");
        $stmt->execute([
            'key'         => $key,
            'name'        => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'type'        => $data['project_type'] ?? 'SOFTWARE',
            'lead_id'     => (int)$data['lead_id'],
            'icon'        => $data['avatar_icon'] ?? 'bi-rocket-takeoff-fill',
            'bg'          => $data['avatar_bg'] ?? '#6554C0'
        ]);

        $projectId = (int)$this->db->lastInsertId();

        // Tự động gán Project Lead làm thành viên ADMIN của dự án
        $this->addMember($projectId, (int)$data['lead_id'], 'ADMIN');

        // Gán các thành viên được chọn
        if (!empty($data['member_ids']) && is_array($data['member_ids'])) {
            foreach ($data['member_ids'] as $uid) {
                if ((int)$uid !== (int)$data['lead_id']) {
                    $this->addMember($projectId, (int)$uid, 'MEMBER');
                }
            }
        }

        return $projectId;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE projects 
            SET name = :name, 
                description = :description, 
                project_type = :type, 
                lead_id = :lead_id, 
                avatar_icon = :icon, 
                avatar_bg = :bg,
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'          => $id,
            'name'        => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'type'        => $data['project_type'] ?? 'SOFTWARE',
            'lead_id'     => (int)$data['lead_id'],
            'icon'        => $data['avatar_icon'] ?? 'bi-rocket-takeoff-fill',
            'bg'          => $data['avatar_bg'] ?? '#6554C0'
        ]);
    }

    public function getMembers(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT pm.*, u.username, u.full_name, u.email, u.avatar, u.department
            FROM project_members pm
            JOIN users u ON pm.user_id = u.id
            WHERE pm.project_id = :pid
            ORDER BY (pm.role = 'ADMIN') DESC, u.full_name ASC
        ");
        $stmt->execute(['pid' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMember(int $projectId, int $userId, string $role = 'MEMBER'): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_members (project_id, user_id, role, created_at)
            VALUES (:pid, :uid, :role, NOW())
            ON DUPLICATE KEY UPDATE role = :role_up
        ");
        return $stmt->execute([
            'pid'     => $projectId,
            'uid'     => $userId,
            'role'    => $role,
            'role_up' => $role
        ]);
    }

    public function removeMember(int $projectId, int $userId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM project_members WHERE project_id = :pid AND user_id = :uid");
        return $stmt->execute(['pid' => $projectId, 'uid' => $userId]);
    }

    public function getProjectStats(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'TO_DO' THEN 1 ELSE 0 END) as todo,
                SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'IN_REVIEW' THEN 1 ELSE 0 END) as in_review,
                SUM(CASE WHEN status = 'DONE' THEN 1 ELSE 0 END) as done,
                SUM(CASE WHEN issue_type = 'TASK' THEN 1 ELSE 0 END) as tasks,
                SUM(CASE WHEN issue_type = 'BUG' THEN 1 ELSE 0 END) as bugs,
                SUM(CASE WHEN issue_type = 'TESTCASE' THEN 1 ELSE 0 END) as testcases,
                SUM(CASE WHEN issue_type = 'STORY' THEN 1 ELSE 0 END) as stories,
                SUM(CASE WHEN sprint_status = 'BACKLOG' THEN 1 ELSE 0 END) as backlog
            FROM project_issues
            WHERE project_id = :pid
        ");
        $stmt->execute(['pid' => $projectId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $total = (int)($stats['total'] ?? 0);
        $done = (int)($stats['done'] ?? 0);
        $stats['progress_percent'] = ($total > 0) ? round(($done / $total) * 100) : 0;
        return $stats;
    }
}

