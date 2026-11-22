<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Problem extends Model
{
    /**
     * Generate unique Problem code PRB-000001
     */
    public function generateProblemCode(): string
    {
        $stmt = $this->db->query("SELECT id FROM problems ORDER BY id DESC LIMIT 1");
        $lastId = (int)$stmt->fetchColumn();
        return sprintf("PRB-%06d", $lastId + 1);
    }

    /**
     * Create Problem
     */
    public function create(array $data): int
    {
        $code = $this->generateProblemCode();

        $stmt = $this->db->prepare("
            INSERT INTO problems (
                problem_code, title, description, category_id, priority_id,
                status, root_cause, workaround, permanent_solution, is_known_error,
                assigned_to, created_by, created_at
            ) VALUES (
                :code, :title, :description, :cat_id, :pri_id,
                :status, :root_cause, :workaround, :perm_sol, :is_ke,
                :assigned_to, :created_by, NOW()
            )
        ");

        $stmt->execute([
            'code'        => $code,
            'title'       => $data['title'],
            'description' => $data['description'],
            'cat_id'      => $data['category_id'],
            'pri_id'      => $data['priority_id'],
            'status'      => $data['status'] ?? 'LOGGED',
            'root_cause'  => $data['root_cause'] ?? null,
            'workaround'  => $data['workaround'] ?? null,
            'perm_sol'    => $data['permanent_solution'] ?? null,
            'is_ke'       => !empty($data['is_known_error']) ? 1 : 0,
            'assigned_to' => !empty($data['assigned_to']) ? (int)$data['assigned_to'] : null,
            'created_by'  => $data['created_by']
        ]);

        $problemId = (int)$this->db->lastInsertId();

        // If incidents provided to link immediately
        if (!empty($data['incident_ids']) && is_array($data['incident_ids'])) {
            foreach ($data['incident_ids'] as $tid) {
                $this->linkIncident($problemId, (int)$tid);
            }
        }

        return $problemId;
    }

    /**
     * Find by ID with relations
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   c.name as category_name,
                   pri.name as priority_name, pri.code as priority_code, pri.color as priority_color,
                   ass.full_name as assignee_name, ass.email as assignee_email,
                   crt.full_name as creator_name
            FROM problems p
            JOIN ticket_categories c ON p.category_id = c.id
            JOIN ticket_priorities pri ON p.priority_id = pri.id
            LEFT JOIN users ass ON p.assigned_to = ass.id
            JOIN users crt ON p.created_by = crt.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all problems with filters
     */
    public function getAll(array $filters = []): array
    {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $where .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['priority_id'])) {
            $where .= " AND p.priority_id = :pri_id";
            $params['pri_id'] = (int)$filters['priority_id'];
        }
        if (!empty($filters['assigned_to'])) {
            $where .= " AND p.assigned_to = :assigned_to";
            $params['assigned_to'] = (int)$filters['assigned_to'];
        }
        if (!empty($filters['is_known_error'])) {
            $where .= " AND p.is_known_error = 1";
        }
        if (!empty($filters['search'])) {
            $where .= " AND (p.problem_code LIKE :q OR p.title LIKE :q2 OR p.root_cause LIKE :q3)";
            $params['q'] = '%' . $filters['search'] . '%';
            $params['q2'] = '%' . $filters['search'] . '%';
            $params['q3'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare("
            SELECT p.*,
                   c.name as category_name,
                   pri.name as priority_name, pri.color as priority_color,
                   ass.full_name as assignee_name,
                   COUNT(pi.ticket_id) as linked_incidents_count
            FROM problems p
            JOIN ticket_categories c ON p.category_id = c.id
            JOIN ticket_priorities pri ON p.priority_id = pri.id
            LEFT JOIN users ass ON p.assigned_to = ass.id
            LEFT JOIN problem_incidents pi ON p.id = pi.problem_id
            {$where}
            GROUP BY p.id
            ORDER BY p.id DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Update Problem details (Root cause, workaround, status)
     */
    public function updateProblem(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['title'])) { $fields[] = "title = :title"; $params['title'] = $data['title']; }
        if (isset($data['description'])) { $fields[] = "description = :description"; $params['description'] = $data['description']; }
        if (isset($data['status'])) { $fields[] = "status = :status"; $params['status'] = $data['status']; }
        if (isset($data['root_cause'])) { $fields[] = "root_cause = :root_cause"; $params['root_cause'] = $data['root_cause']; }
        if (isset($data['workaround'])) { $fields[] = "workaround = :workaround"; $params['workaround'] = $data['workaround']; }
        if (isset($data['permanent_solution'])) { $fields[] = "permanent_solution = :perm"; $params['perm'] = $data['permanent_solution']; }
        if (isset($data['is_known_error'])) { $fields[] = "is_known_error = :is_ke"; $params['is_ke'] = (int)$data['is_known_error']; }
        if (isset($data['assigned_to'])) { $fields[] = "assigned_to = :assigned"; $params['assigned'] = $data['assigned_to'] ? (int)$data['assigned_to'] : null; }
        if (isset($data['priority_id'])) { $fields[] = "priority_id = :pri"; $params['pri'] = (int)$data['priority_id']; }

        if (empty($fields)) return false;

        $sql = "UPDATE problems SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Alias for updateProblem
     */
    public function update(int $id, array $data): bool
    {
        return $this->updateProblem($id, $data);
    }

    /**
     * Resolve Problem and optionally batch resolve all linked incidents
     */
    public function resolveProblem(int $id, string $solution, int $userId, bool $closeLinkedIncidents = true): bool
    {
        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE problems 
                SET status = 'RESOLVED', permanent_solution = :sol, resolved_at = NOW(), updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute(['sol' => $solution, 'id' => $id]);

            if ($closeLinkedIncidents) {
                // Batch resolve all linked incidents
                $incidents = $this->getLinkedIncidents($id);
                $ticketModel = new Ticket();
                foreach ($incidents as $inc) {
                    if (!in_array($inc['status'], ['RESOLVED', 'CLOSED', 'CANCELLED'])) {
                        $ticketModel->resolve($inc['id'], $userId, "Đã giải quyết theo sự cố gốc (Problem #{$inc['problem_code']}): {$solution}");
                    }
                }
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Alias for resolveProblem
     */
    public function resolve(int $id, string $solution, int $userId, bool $closeLinkedIncidents = true): bool
    {
        return $this->resolveProblem($id, $solution, $userId, $closeLinkedIncidents);
    }

    /**
     * Link an incident ticket to problem
     */
    public function linkIncident(int $problemId, int $ticketId): bool
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO problem_incidents (problem_id, ticket_id, created_at)
            VALUES (:pid, :tid, NOW())
        ");
        $res = $stmt->execute(['pid' => $problemId, 'tid' => $ticketId]);

        // Also update ticket problem_id
        $this->db->prepare("UPDATE tickets SET problem_id = :pid WHERE id = :tid")
                 ->execute(['pid' => $problemId, 'tid' => $ticketId]);

        return $res;
    }

    /**
     * Unlink incident
     */
    public function unlinkIncident(int $problemId, int $ticketId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM problem_incidents WHERE problem_id = :pid AND ticket_id = :tid");
        $res = $stmt->execute(['pid' => $problemId, 'tid' => $ticketId]);

        $this->db->prepare("UPDATE tickets SET problem_id = NULL WHERE id = :tid AND problem_id = :pid")
                 ->execute(['pid' => $problemId, 'tid' => $ticketId]);

        return $res;
    }

    /**
     * Get linked incidents for problem
     */
    public function getLinkedIncidents(int $problemId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.id, t.id as ticket_id, t.ticket_code, t.title, t.status, t.created_at, pi.created_at as linked_at,
                   u.full_name as requester_name,
                   ass.full_name as assignee_name,
                   pri.name as priority_name, pri.color as priority_color,
                   p.problem_code
            FROM problem_incidents pi
            JOIN problems p ON pi.problem_id = p.id
            JOIN tickets t ON pi.ticket_id = t.id
            JOIN users u ON t.requester_id = u.id
            LEFT JOIN users ass ON t.assignee_id = ass.id
            JOIN ticket_priorities pri ON t.priority_id = pri.id
            WHERE pi.problem_id = :pid
            ORDER BY t.id DESC
        ");
        $stmt->execute(['pid' => $problemId]);
        return $stmt->fetchAll();
    }

    /**
     * Publish Problem as Known Error article in Knowledge Base
     */
    public function publishToKnowledgeBase(int $problemId, int $userId): int
    {
        $problem = $this->findById($problemId);
        if (!$problem) throw new \Exception("Không tìm thấy Problem.");

        $title = "[Known Error] " . $problem['title'];
        $content = "### 1. Mô tả sự cố (Issue Description)\n" . $problem['description'] . "\n\n"
                 . "### 2. Nguyên nhân gốc rễ (Root Cause Analysis - RCA)\n" . ($problem['root_cause'] ?: 'Đang cập nhật') . "\n\n"
                 . "### 3. Giải pháp tạm thời (Workaround)\n" . ($problem['workaround'] ?: 'Chưa có giải pháp tạm thời') . "\n\n"
                 . "### 4. Giải pháp triệt để (Permanent Solution)\n" . ($problem['permanent_solution'] ?: 'Đang cập nhật');

        $kbModel = new KnowledgeBase();
        // Look up or fallback to a valid kb_categories id
        $stmtKbCat = $this->db->query("SELECT id FROM kb_categories WHERE is_active = 1 LIMIT 1");
        $kbCatId = $stmtKbCat ? (int)$stmtKbCat->fetchColumn() : 1;
        if (!$kbCatId) $kbCatId = 1;

        $kbId = $kbModel->createArticle([
            'title'       => $title,
            'content'     => $content,
            'category_id' => $kbCatId,
            'status'      => 'PUBLISHED',
            'author_id'   => $userId
        ]);

        $this->updateProblem($problemId, ['is_known_error' => 1]);
        return $kbId;
    }
}
