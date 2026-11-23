<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Approval;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\User;

class MobileController extends Controller
{
    private Ticket $ticketModel;
    private Approval $approvalModel;
    private Asset $assetModel;
    private Category $categoryModel;
    private Priority $priorityModel;
    private \PDO $db;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->approvalModel = new Approval();
        $this->assetModel = new Asset();
        $this->categoryModel = new Category();
        $this->priorityModel = new Priority();
        $this->db = \App\Core\Database::getConnection();
    }

    /**
     * Mobile Home Page (App View)
     */
    public function index(Request $request, Response $response): string
    {
        $user = Auth::user();
        $userId = (int)Auth::id();
        $isStaff = Auth::isStaff() || Auth::isAdmin() || Auth::isManager();

        $categories = $this->categoryModel->getAllActive();
        $priorities = $this->priorityModel->getAllActive();

        // Assets
        if ($isStaff) {
            $userAssets = $this->assetModel->getAll();
        } else {
            $userAssets = $this->assetModel->getByUser($userId);
        }

        // Quick statistics for current user
        $stats = $this->getUserStats($userId, $isStaff);

        // Pending approvals count for current user
        $pendingApprovals = $this->getPendingApprovalsForUser($userId, $isStaff);

        // Recent tickets for current user
        $recentTickets = $this->getRecentTicketsForUser($userId, $isStaff, 15);

        return $this->render('mobile/index', [
            'pageTitle'        => 'TaskFlow Mobile - IT Helpdesk & Assets',
            'user'             => $user,
            'isStaff'          => $isStaff,
            'categories'       => $categories,
            'priorities'       => $priorities,
            'userAssets'       => $userAssets,
            'stats'            => $stats,
            'pendingApprovals' => $pendingApprovals,
            'recentTickets'    => $recentTickets
        ], ''); // layout: '' means standalone view
    }

    /**
     * API: Get tickets for mobile list
     */
    public function apiTickets(Request $request, Response $response): void
    {
        $userId = (int)Auth::id();
        $isStaff = Auth::isStaff() || Auth::isAdmin() || Auth::isManager();
        $status = trim($request->input('status', ''));
        $search = trim($request->input('search', ''));

        $query = "
            SELECT t.id, t.ticket_code, t.title, t.status, t.impact, t.urgency, t.created_at,
                   c.name as category_name,
                   p.name as priority_name, p.color as priority_color, p.level as priority_level,
                   u.full_name as requester_name,
                   a.full_name as assignee_name
            FROM tickets t
            LEFT JOIN ticket_categories c ON t.category_id = c.id
            LEFT JOIN ticket_priorities p ON t.priority_id = p.id
            LEFT JOIN users u ON t.requester_id = u.id
            LEFT JOIN users a ON t.assignee_id = a.id
            WHERE 1=1
        ";
        $params = [];

        if (!$isStaff) {
            $query .= " AND t.requester_id = :uid";
            $params['uid'] = $userId;
        }

        if ($status && $status !== 'ALL') {
            if ($status === 'OPEN') {
                $query .= " AND t.status IN ('NEW', 'ASSIGNED', 'IN_PROGRESS')";
            } elseif ($status === 'CLOSED') {
                $query .= " AND t.status IN ('RESOLVED', 'CLOSED', 'CANCELLED')";
            } else {
                $query .= " AND t.status = :status";
                $params['status'] = $status;
            }
        }

        if ($search) {
            $query .= " AND (t.ticket_code LIKE :search OR t.title LIKE :search OR t.description LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $query .= " ORDER BY t.id DESC LIMIT 50";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $response->json(['success' => true, 'tickets' => $tickets]);
    }

    /**
     * API: Get single ticket detail
     */
    public function apiTicketDetail(Request $request, Response $response, string $id): void
    {
        $ticketId = (int)$id;
        $ticket = $this->ticketModel->findById($ticketId);

        if (!$ticket) {
            $response->json(['success' => false, 'message' => 'Không tìm thấy phiếu yêu cầu.'], 404);
            return;
        }

        // Get comments
        $commentStmt = $this->db->prepare("
            SELECT tc.*, u.full_name, u.avatar, u.role
            FROM ticket_comments tc
            JOIN users u ON tc.user_id = u.id
            WHERE tc.ticket_id = :tid
            ORDER BY tc.id ASC
        ");
        $commentStmt->execute(['tid' => $ticketId]);
        $comments = $commentStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get checklists
        $chkStmt = $this->db->prepare("
            SELECT * FROM ticket_checklists WHERE ticket_id = :tid ORDER BY sort_order ASC, id ASC
        ");
        $chkStmt->execute(['tid' => $ticketId]);
        $checklists = $chkStmt->fetchAll(\PDO::FETCH_ASSOC);

        $response->json([
            'success'    => true,
            'ticket'     => $ticket,
            'comments'   => $comments,
            'checklists' => $checklists
        ]);
    }

    /**
     * API: Create ticket from mobile
     */
    public function apiCreateTicket(Request $request, Response $response): void
    {
        $body = $request->getBody();
        if (empty($body)) {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) $body = $decoded;
            }
        }

        $title = trim($body['title'] ?? '');
        $description = trim($body['description'] ?? '');
        $categoryId = (int)($body['category_id'] ?? 0);
        $priorityId = (int)($body['priority_id'] ?? 0);
        $assetId = !empty($body['asset_id']) ? (int)$body['asset_id'] : null;
        $ticketType = in_array($body['ticket_type'] ?? '', ['INCIDENT', 'SERVICE_REQUEST']) ? $body['ticket_type'] : 'INCIDENT';
        $location = trim($body['location'] ?? '');
        $contactInfo = trim($body['contact_info'] ?? '');

        if (empty($title) || empty($description) || !$categoryId) {
            $response->json(['success' => false, 'message' => 'Vui lòng điền đầy đủ Tiêu đề, Danh mục và Mô tả chi tiết.'], 400);
            return;
        }

        if (!$priorityId) {
            $priorityId = 3; // Default Medium priority
        }

        try {
            $ticketId = $this->ticketModel->create([
                'ticket_type'        => $ticketType,
                'title'              => $title,
                'description'        => $description,
                'requester_id'       => Auth::id(),
                'category_id'        => $categoryId,
                'service_id'         => null,
                'asset_id'           => $assetId,
                'priority_id'        => $priorityId,
                'impact'             => 'MEDIUM',
                'urgency'            => 'MEDIUM',
                'support_tier'       => 'L1',
                'approver_id'        => null,
                'approval_step_name' => 'Phê duyệt di động',
                'location'           => $location,
                'contact_info'       => $contactInfo
            ]);

            $createdTicket = $this->ticketModel->findById($ticketId);

            $response->json([
                'success'     => true,
                'message'     => "Đã tạo thành công phiếu yêu cầu #{$createdTicket['ticket_code']}",
                'ticket_id'   => $ticketId,
                'ticket_code' => $createdTicket['ticket_code']
            ]);
        } catch (\Throwable $e) {
            $response->json(['success' => false, 'message' => 'Lỗi tạo phiếu: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Get approvals list
     */
    public function apiApprovals(Request $request, Response $response): void
    {
        $userId = (int)Auth::id();
        $isManagerOrAdmin = Auth::isManager() || Auth::isAdmin();
        $status = trim($request->input('status', 'PENDING'));

        $filters = ['status' => $status];
        if (!$isManagerOrAdmin) {
            $filters['approver_id'] = $userId;
        }

        $approvals = $this->approvalModel->getAll($filters);

        $response->json(['success' => true, 'approvals' => $approvals]);
    }

    /**
     * API: Approve or Reject
     */
    public function apiDecideApproval(Request $request, Response $response): void
    {
        $body = $request->getBody();
        if (empty($body)) {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) $body = $decoded;
            }
        }

        $approvalId = (int)($body['approval_id'] ?? 0);
        $decision = strtoupper(trim($body['decision'] ?? ''));
        $comment = trim($body['comment'] ?? '');
        $userId = (int)Auth::id();

        if (!$approvalId || !in_array($decision, ['APPROVED', 'REJECTED', 'RETURNED'])) {
            $response->json(['success' => false, 'message' => 'Dữ liệu phê duyệt không hợp lệ.'], 400);
            return;
        }

        $success = $this->approvalModel->decide($approvalId, $userId, $decision, $comment);

        if ($success) {
            $labels = [
                'APPROVED' => 'Đã phê duyệt thành công!',
                'REJECTED' => 'Đã từ chối yêu cầu.',
                'RETURNED' => 'Đã yêu cầu bổ sung thông tin.'
            ];
            $response->json([
                'success' => true,
                'message' => $labels[$decision] ?? 'Đã hoàn tất xử lý.'
            ]);
        } else {
            $response->json(['success' => false, 'message' => 'Xử lý phê duyệt thất bại hoặc bạn không có quyền.'], 500);
        }
    }

    /**
     * API: Scan Asset QR code and return equipment info
     */
    public function apiScanAsset(Request $request, Response $response, string $code): void
    {
        $cleanCode = trim(urldecode($code));
        // If code is a URL (e.g. https://.../asset/AST-000001 or .../assets/5)
        if (preg_match('/asset(?:s)?\/([A-Za-z0-9\-_]+)/i', $cleanCode, $matches)) {
            $cleanCode = $matches[1];
        }

        $asset = null;
        if (is_numeric($cleanCode)) {
            $asset = $this->assetModel->findById((int)$cleanCode);
        } else {
            $asset = $this->assetModel->findByCode($cleanCode);
        }

        if (!$asset) {
            $response->json([
                'success' => false,
                'message' => "Không tìm thấy thiết bị với mã quét được: '{$cleanCode}'"
            ], 404);
            return;
        }

        $response->json([
            'success' => true,
            'asset'   => $asset
        ]);
    }

    /**
     * Helper: user stats
     */
    private function getUserStats(int $userId, bool $isStaff): array
    {
        $whereUser = $isStaff ? "1=1" : "requester_id = {$userId}";

        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('NEW', 'ASSIGNED', 'IN_PROGRESS') THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN status = 'PENDING_APPROVAL' THEN 1 ELSE 0 END) as pending_approval_count,
                SUM(CASE WHEN status IN ('RESOLVED', 'CLOSED') THEN 1 ELSE 0 END) as closed_count
            FROM tickets
            WHERE {$whereUser}
        ");
        $ticketStats = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [
            'total' => 0, 'open_count' => 0, 'pending_approval_count' => 0, 'closed_count' => 0
        ];

        // My assets count
        $assetStmt = $this->db->prepare("SELECT COUNT(*) FROM assets WHERE current_user_id = :uid");
        $assetStmt->execute(['uid' => $userId]);
        $myAssetCount = (int)$assetStmt->fetchColumn();

        return array_merge($ticketStats, ['my_asset_count' => $myAssetCount]);
    }

    /**
     * Helper: pending approvals for user
     */
    private function getPendingApprovalsForUser(int $userId, bool $isStaff): array
    {
        $filters = ['status' => 'PENDING'];
        if (!Auth::isManager() && !Auth::isAdmin()) {
            $filters['approver_id'] = $userId;
        }
        return $this->approvalModel->getAll($filters);
    }

    /**
     * Helper: recent tickets
     */
    private function getRecentTicketsForUser(int $userId, bool $isStaff, int $limit = 10): array
    {
        $query = "
            SELECT t.id, t.ticket_code, t.title, t.status, t.created_at,
                   c.name as category_name,
                   p.name as priority_name, p.color as priority_color, p.level as priority_level
            FROM tickets t
            LEFT JOIN ticket_categories c ON t.category_id = c.id
            LEFT JOIN ticket_priorities p ON t.priority_id = p.id
            WHERE " . ($isStaff ? "1=1" : "t.requester_id = :uid") . "
            ORDER BY t.id DESC LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($query);
        if (!$isStaff) {
            $stmt->execute(['uid' => $userId]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Download Android APK
     */
    public function downloadApk(Request $request, Response $response): void
    {
        $apkPath = dirname(__DIR__, 2) . '/public/TaskFlow-v1.3.apk';
        if (!file_exists($apkPath)) {
            $apkPath = dirname(__DIR__, 2) . '/TaskFlow-v1.3.apk';
        }
        if (!file_exists($apkPath)) {
            $apkPath = dirname(__DIR__, 2) . '/public/TaskFlow-v1.2.apk';
        }

        if (!file_exists($apkPath)) {
            $response->status(404)->json(['success' => false, 'message' => 'File APK chưa sẵn sàng']);
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.android.package-archive');
        header('Content-Disposition: attachment; filename="TaskFlow-v1.3.apk"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($apkPath));
        readfile($apkPath);
        exit;
    }
}
