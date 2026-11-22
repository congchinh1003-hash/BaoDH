<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Category;
use App\Models\ITGroup;
use App\Models\Priority;
use App\Models\SlaConfig;
use App\Models\User;
use PDO;

class AdminController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function logAudit(string $action, string $objectType, ?int $objectId, ?string $oldVal, ?string $newVal, ?string $reason = null): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, object_type, object_id, old_value, new_value, reason, created_at)
                VALUES (:uid, :act, :obj_type, :obj_id, :old_val, :new_val, :reason, NOW())
            ");
            $stmt->execute([
                'uid'      => Auth::id(),
                'act'      => $action,
                'obj_type' => $objectType,
                'obj_id'   => $objectId,
                'old_val'  => $oldVal,
                'new_val'  => $newVal,
                'reason'   => $reason
            ]);
        } catch (\Throwable $e) {
            // Ignore audit log error if table or column issue
        }
    }

    // =========================================================================
    // 1. QUẢN LÝ NGƯỜI DÙNG (USERS CRUD)
    // =========================================================================

    public function users(Request $request, Response $response): string
    {
        $userModel = new User();
        $users = $userModel->getAllUsers();

        // Get all available roles
        $stmtRoles = $this->db->query("SELECT * FROM roles ORDER BY id ASC");
        $roles = $stmtRoles->fetchAll();

        // Get all IT groups
        $itGroupModel = new ITGroup();
        $groups = $itGroupModel->getAllActive();

        return $this->render('admin/users', [
            'pageTitle' => 'Quản lý Người dùng & Phân quyền Vai trò (RBAC)',
            'users'     => $users,
            'roles'     => $roles,
            'groups'    => $groups
        ]);
    }

    public function createUser(Request $request, Response $response): void
    {
        $username = trim($request->input('username', ''));
        $email = trim($request->input('email', ''));
        $fullName = trim($request->input('full_name', ''));
        $password = trim($request->input('password', '123456'));
        $department = trim($request->input('department', ''));
        $jobTitle = trim($request->input('job_title', ''));
        $seatLocation = trim($request->input('seat_location', ''));
        $phone = trim($request->input('phone', ''));
        $password = trim($request->input('password', ''));
        $selectedRoles = (array)$request->input('roles', []);
        $groupId = $request->input('group_id') ? (int)$request->input('group_id') : null;

        if (empty($username) || empty($email) || empty($fullName)) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc.');
            $response->redirect('/admin/users');
            return;
        }

        // Check unique username & email
        $userModel = new User();
        if ($userModel->findByUsername($username)) {
            Session::setFlash('error', "Tên đăng nhập '{$username}' đã tồn tại.");
            $response->redirect('/admin/users');
            return;
        }
        if ($userModel->findByEmail($email)) {
            Session::setFlash('error', "Email '{$email}' đã tồn tại.");
            $response->redirect('/admin/users');
            return;
        }

        try {
            $this->db->beginTransaction();

            $passwordHash = password_hash(!empty($password) ? $password : '123456', PASSWORD_BCRYPT);
            $stmtInsert = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, full_name, department, seat_location, job_title, phone, is_active, created_at)
                VALUES (:u, :e, :p, :fn, :dep, :seat, :job, :ph, 1, NOW())
            ");
            $stmtInsert->execute([
                'u'    => $username,
                'e'    => $email,
                'p'    => $passwordHash,
                'fn'   => $fullName,
                'dep'  => $department,
                'seat' => $seatLocation,
                'job'  => $jobTitle,
                'ph'   => $phone
            ]);
            $userId = (int)$this->db->lastInsertId();

            // Assign roles
            if (empty($selectedRoles)) {
                $selectedRoles = ['USER'];
            }
            $stmtRole = $this->db->prepare("SELECT id FROM roles WHERE code = :code LIMIT 1");
            $stmtAssignRole = $this->db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
            foreach ($selectedRoles as $rCode) {
                $stmtRole->execute(['code' => $rCode]);
                $rId = $stmtRole->fetchColumn();
                if ($rId) {
                    $stmtAssignRole->execute(['uid' => $userId, 'rid' => $rId]);
                }
            }

            // Assign IT group if selected
            if ($groupId) {
                $stmtGroup = $this->db->prepare("INSERT INTO it_group_members (group_id, user_id, is_primary, created_at) VALUES (:gid, :uid, 1, NOW())");
                $stmtGroup->execute(['gid' => $groupId, 'uid' => $userId]);
            }

            $this->logAudit('CREATE_USER', 'USER', $userId, null, $username, "Tạo tài khoản {$username} ({$fullName})");
            $this->db->commit();

            Session::setFlash('success', "Đã tạo tài khoản '{$username}' thành công.");
        } catch (\Throwable $e) {
            $this->db->rollBack();
            Session::setFlash('error', 'Lỗi khi tạo tài khoản: ' . $e->getMessage());
        }

        $response->redirect('/admin/users');
    }

    public function updateUser(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            Session::setFlash('error', 'Tài khoản không tồn tại.');
            $response->redirect('/admin/users');
            return;
        }

        $fullName = trim($request->input('full_name', ''));
        $email = trim($request->input('email', ''));
        $department = trim($request->input('department', ''));
        $jobTitle = trim($request->input('job_title', ''));
        $seatLocation = trim($request->input('seat_location', ''));
        $phone = trim($request->input('phone', ''));
        $password = trim($request->input('password', ''));
        $selectedRoles = (array)$request->input('roles', []);
        $groupId = $request->input('group_id') ? (int)$request->input('group_id') : null;

        if (empty($fullName) || empty($email)) {
            Session::setFlash('error', 'Họ tên và email không được để trống.');
            $response->redirect('/admin/users');
            return;
        }

        // Check email unique if changed
        if ($email !== $user['email']) {
            $stmtCheck = $this->db->prepare("SELECT id FROM users WHERE email = :e AND id != :id LIMIT 1");
            $stmtCheck->execute(['e' => $email, 'id' => $id]);
            if ($stmtCheck->fetch()) {
                Session::setFlash('error', "Email '{$email}' đã được sử dụng bởi tài khoản khác.");
                $response->redirect('/admin/users');
                return;
            }
        }

        try {
            $this->db->beginTransaction();

            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmtUpdate = $this->db->prepare("
                    UPDATE users 
                    SET full_name = :fn, email = :e, department = :dep, seat_location = :seat, job_title = :job, phone = :ph, password_hash = :p, updated_at = NOW()
                    WHERE id = :id
                ");
                $stmtUpdate->execute([
                    'fn'   => $fullName,
                    'e'    => $email,
                    'dep'  => $department,
                    'seat' => $seatLocation,
                    'job'  => $jobTitle,
                    'ph'   => $phone,
                    'p'    => $passwordHash,
                    'id'   => $id
                ]);
            } else {
                $stmtUpdate = $this->db->prepare("
                    UPDATE users 
                    SET full_name = :fn, email = :e, department = :dep, seat_location = :seat, job_title = :job, phone = :ph, updated_at = NOW()
                    WHERE id = :id
                ");
                $stmtUpdate->execute([
                    'fn'   => $fullName,
                    'e'    => $email,
                    'dep'  => $department,
                    'seat' => $seatLocation,
                    'job'  => $jobTitle,
                    'ph'   => $phone,
                    'id'   => $id
                ]);
            }

            // Sync roles if provided
            if (!empty($selectedRoles)) {
                $stmtDel = $this->db->prepare("DELETE FROM user_roles WHERE user_id = :uid");
                $stmtDel->execute(['uid' => $id]);

                $stmtRole = $this->db->prepare("SELECT id FROM roles WHERE code = :code LIMIT 1");
                $stmtAssignRole = $this->db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
                foreach ($selectedRoles as $rCode) {
                    $stmtRole->execute(['code' => $rCode]);
                    $rId = $stmtRole->fetchColumn();
                    if ($rId) {
                        $stmtAssignRole->execute(['uid' => $id, 'rid' => $rId]);
                    }
                }
            }

            // Sync Group
            if ($groupId !== null) {
                $stmtDelGroup = $this->db->prepare("DELETE FROM it_group_members WHERE user_id = :uid");
                $stmtDelGroup->execute(['uid' => $id]);
                if ($groupId > 0) {
                    $stmtAddGroup = $this->db->prepare("INSERT INTO it_group_members (group_id, user_id, is_primary, created_at) VALUES (:gid, :uid, 1, NOW())");
                    $stmtAddGroup->execute(['gid' => $groupId, 'uid' => $id]);
                }
            }

            $this->logAudit('UPDATE_USER', 'USER', $id, $user['full_name'], $fullName, "Cập nhật tài khoản {$user['username']}");
            $this->db->commit();

            Session::setFlash('success', "Đã cập nhật thông tin tài khoản '{$user['username']}'.");
        } catch (\Throwable $e) {
            $this->db->rollBack();
            Session::setFlash('error', 'Lỗi khi cập nhật tài khoản: ' . $e->getMessage());
        }

        $response->redirect('/admin/users');
    }

    public function toggleUserStatus(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        if ($id === (int)Auth::id()) {
            Session::setFlash('error', 'Không thể tự khóa tài khoản quản trị viên đang đăng nhập.');
            $response->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($id);
        if (!$user) {
            Session::setFlash('error', 'Tài khoản không tồn tại.');
            $response->redirect('/admin/users');
            return;
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE users SET is_active = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $id]);

        $statusLabel = $newStatus ? 'mở khóa kích hoạt' : 'khóa';
        $this->logAudit('TOGGLE_USER_STATUS', 'USER', $id, (string)$user['is_active'], (string)$newStatus, "Đã {$statusLabel} tài khoản {$user['username']}");

        Session::setFlash('success', "Đã {$statusLabel} tài khoản '{$user['username']}' thành công.");
        $response->redirect('/admin/users');
    }

    public function deleteUser(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        if ($id === (int)Auth::id()) {
            Session::setFlash('error', 'Không thể xóa tài khoản của chính bạn.');
            $response->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($id);
        if (!$user) {
            Session::setFlash('error', 'Tài khoản không tồn tại.');
            $response->redirect('/admin/users');
            return;
        }

        // Check if user has tickets
        $stmtCheckTickets = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE requester_id = :id OR assignee_id = :id");
        $stmtCheckTickets->execute(['id' => $id]);
        $ticketCount = (int)$stmtCheckTickets->fetchColumn();

        if ($ticketCount > 0) {
            // Soft-deactivate instead of hard delete
            $stmt = $this->db->prepare("UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->logAudit('DEACTIVATE_USER', 'USER', $id, '1', '0', "Tài khoản có {$ticketCount} ticket liên kết, chuyển trạng thái Đã khóa thay vì xóa vĩnh viễn");
            Session::setFlash('warning', "Tài khoản '{$user['username']}' đã liên kết với {$ticketCount} ticket nên hệ thống đã khóa tài khoản này thay vì xóa vĩnh viễn để bảo toàn dữ liệu kiểm toán.");
        } else {
            // Safe to hard delete
            $this->db->beginTransaction();
            try {
                $this->db->prepare("DELETE FROM user_roles WHERE user_id = :id")->execute(['id' => $id]);
                $this->db->prepare("DELETE FROM it_group_members WHERE user_id = :id")->execute(['id' => $id]);
                $this->db->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $id]);
                $this->logAudit('DELETE_USER', 'USER', $id, $user['username'], null, "Xóa tài khoản {$user['username']}");
                $this->db->commit();

                Session::setFlash('success', "Đã xóa vĩnh viễn tài khoản '{$user['username']}'.");
            } catch (\Throwable $e) {
                $this->db->rollBack();
                Session::setFlash('error', 'Lỗi khi xóa tài khoản: ' . $e->getMessage());
            }
        }

        $response->redirect('/admin/users');
    }

    // =========================================================================
    // 2. QUẢN LÝ DANH MỤC & DỊCH VỤ CON (CATEGORIES & SERVICES CRUD)
    // =========================================================================

    public function categories(Request $request, Response $response): string
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive();

        $stmt = $this->db->query("
            SELECT s.*, c.name as category_name
            FROM ticket_services s
            JOIN ticket_categories c ON s.category_id = c.id
            ORDER BY c.name ASC, s.name ASC
        ");
        $services = $stmt->fetchAll();

        // Get groups for category assignment
        $itGroupModel = new ITGroup();
        $groups = $itGroupModel->getAllActive();

        return $this->render('admin/categories', [
            'pageTitle'  => 'Quản lý Danh mục & Dịch vụ hỗ trợ',
            'categories' => $categories,
            'services'   => $services,
            'groups'     => $groups
        ]);
    }

    public function createCategory(Request $request, Response $response): void
    {
        $name = trim($request->input('name', ''));
        $code = strtoupper(trim($request->input('code', '')));
        $description = trim($request->input('description', ''));
        $defaultGroupId = $request->input('default_group_id') ? (int)$request->input('default_group_id') : null;

        if (empty($name)) {
            Session::setFlash('error', 'Tên danh mục không được để trống.');
            $response->redirect('/admin/categories');
            return;
        }

        if (empty($code)) {
            $code = 'CAT_' . strtoupper(substr(md5(uniqid()), 0, 6));
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO ticket_categories (code, name, description, default_group_id, is_active, created_at)
                VALUES (:code, :name, :desc, :gid, 1, NOW())
            ");
            $stmt->execute([
                'code' => $code,
                'name' => $name,
                'desc' => $description,
                'gid'  => $defaultGroupId
            ]);
            $catId = (int)$this->db->lastInsertId();

            $this->logAudit('CREATE_CATEGORY', 'CATEGORY', $catId, null, $name, "Tạo danh mục mới {$name} ({$code})");
            Session::setFlash('success', "Đã thêm danh mục '{$name}' thành công.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi tạo danh mục: ' . $e->getMessage());
        }

        $response->redirect('/admin/categories');
    }

    public function updateCategory(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $name = trim($request->input('name', ''));
        $code = strtoupper(trim($request->input('code', '')));
        $description = trim($request->input('description', ''));
        $defaultGroupId = $request->input('default_group_id') ? (int)$request->input('default_group_id') : null;

        if (empty($name)) {
            Session::setFlash('error', 'Tên danh mục không được để trống.');
            $response->redirect('/admin/categories');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE ticket_categories 
                SET name = :name, code = :code, description = :desc, default_group_id = :gid 
                WHERE id = :id
            ");
            $stmt->execute([
                'name' => $name,
                'code' => $code,
                'desc' => $description,
                'gid'  => $defaultGroupId,
                'id'   => $id
            ]);

            $this->logAudit('UPDATE_CATEGORY', 'CATEGORY', $id, null, $name, "Cập nhật danh mục {$name}");
            Session::setFlash('success', "Đã cập nhật danh mục '{$name}'.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi cập nhật danh mục: ' . $e->getMessage());
        }

        $response->redirect('/admin/categories');
    }

    public function deleteCategory(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $categoryModel = new Category();
        $cat = $categoryModel->findById($id);

        if (!$cat) {
            Session::setFlash('error', 'Danh mục không tồn tại.');
            $response->redirect('/admin/categories');
            return;
        }

        // Check if tickets use this category
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE category_id = :id");
        $stmtCheck->execute(['id' => $id]);
        $ticketCount = (int)$stmtCheck->fetchColumn();

        if ($ticketCount > 0) {
            // Soft deactivate
            $stmt = $this->db->prepare("UPDATE ticket_categories SET is_active = 0 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            Session::setFlash('warning', "Danh mục '{$cat['name']}' đã được dùng trong {$ticketCount} ticket nên hệ thống đã ẩn danh mục này thay vì xóa vĩnh viễn.");
        } else {
            $this->db->prepare("DELETE FROM ticket_services WHERE category_id = :id")->execute(['id' => $id]);
            $this->db->prepare("DELETE FROM ticket_categories WHERE id = :id")->execute(['id' => $id]);
            $this->logAudit('DELETE_CATEGORY', 'CATEGORY', $id, $cat['name'], null, "Xóa danh mục {$cat['name']}");
            Session::setFlash('success', "Đã xóa danh mục '{$cat['name']}'.");
        }

        $response->redirect('/admin/categories');
    }

    public function createService(Request $request, Response $response): void
    {
        $categoryId = (int)$request->input('category_id');
        $name = trim($request->input('name', ''));
        $code = strtoupper(trim($request->input('code', '')));
        $description = trim($request->input('description', ''));

        if (!$categoryId || empty($name)) {
            Session::setFlash('error', 'Vui lòng chọn danh mục và nhập tên dịch vụ con.');
            $response->redirect('/admin/categories');
            return;
        }

        if (empty($code)) {
            $code = 'SRV_' . strtoupper(substr(md5(uniqid()), 0, 6));
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO ticket_services (category_id, code, name, description, is_active, created_at)
                VALUES (:cid, :code, :name, :desc, 1, NOW())
            ");
            $stmt->execute([
                'cid'  => $categoryId,
                'code' => $code,
                'name' => $name,
                'desc' => $description
            ]);
            $srvId = (int)$this->db->lastInsertId();

            $this->logAudit('CREATE_SERVICE', 'SERVICE', $srvId, null, $name, "Tạo dịch vụ con {$name}");
            Session::setFlash('success', "Đã thêm dịch vụ '{$name}' thành công.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi tạo dịch vụ: ' . $e->getMessage());
        }

        $response->redirect('/admin/categories');
    }

    public function updateService(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $name = trim($request->input('name', ''));
        $code = strtoupper(trim($request->input('code', '')));
        $description = trim($request->input('description', ''));
        $categoryId = (int)$request->input('category_id');

        if (empty($name) || !$categoryId) {
            Session::setFlash('error', 'Tên dịch vụ và danh mục không được để trống.');
            $response->redirect('/admin/categories');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE ticket_services 
                SET category_id = :cid, name = :name, code = :code, description = :desc 
                WHERE id = :id
            ");
            $stmt->execute([
                'cid'  => $categoryId,
                'name' => $name,
                'code' => $code,
                'desc' => $description,
                'id'   => $id
            ]);

            $this->logAudit('UPDATE_SERVICE', 'SERVICE', $id, null, $name, "Cập nhật dịch vụ {$name}");
            Session::setFlash('success', "Đã cập nhật dịch vụ '{$name}'.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi cập nhật dịch vụ: ' . $e->getMessage());
        }

        $response->redirect('/admin/categories');
    }

    public function deleteService(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $categoryModel = new Category();
        $srv = $categoryModel->findServiceById($id);

        if (!$srv) {
            Session::setFlash('error', 'Dịch vụ không tồn tại.');
            $response->redirect('/admin/categories');
            return;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM ticket_services WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $this->logAudit('DELETE_SERVICE', 'SERVICE', $id, $srv['name'], null, "Xóa dịch vụ {$srv['name']}");
            Session::setFlash('success', "Đã xóa dịch vụ '{$srv['name']}'.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi xóa dịch vụ: ' . $e->getMessage());
        }

        $response->redirect('/admin/categories');
    }

    // =========================================================================
    // 3. QUẢN LÝ NHÓM KỸ THUẬT IT (IT GROUPS CRUD)
    // =========================================================================

    public function groups(Request $request, Response $response): string
    {
        $itGroupModel = new ITGroup();
        $groups = $itGroupModel->getAllActive();

        $userModel = new User();
        $staffUsers = $userModel->getStaffMembersByGroup();

        return $this->render('admin/groups', [
            'pageTitle'  => 'Quản lý Nhóm IT (IT Groups)',
            'groups'     => $groups,
            'staffUsers' => $staffUsers
        ]);
    }

    public function createGroup(Request $request, Response $response): void
    {
        $name = trim($request->input('name', ''));
        $code = strtoupper(trim($request->input('code', '')));
        $description = trim($request->input('description', ''));
        $leaderId = $request->input('leader_id') ? (int)$request->input('leader_id') : null;

        if (empty($name)) {
            Session::setFlash('error', 'Tên nhóm IT không được để trống.');
            $response->redirect('/admin/groups');
            return;
        }

        if (empty($code)) {
            $code = 'GRP_' . strtoupper(substr(md5(uniqid()), 0, 6));
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO it_groups (code, name, description, leader_id, is_active, created_at)
                VALUES (:code, :name, :desc, :lid, 1, NOW())
            ");
            $stmt->execute([
                'code' => $code,
                'name' => $name,
                'desc' => $description,
                'lid'  => $leaderId
            ]);
            $groupId = (int)$this->db->lastInsertId();

            if ($leaderId) {
                $stmtMem = $this->db->prepare("
                    INSERT INTO it_group_members (group_id, user_id, is_primary, created_at) 
                    VALUES (:gid, :uid, 1, NOW()) 
                    ON DUPLICATE KEY UPDATE is_primary = 1
                ");
                $stmtMem->execute(['gid' => $groupId, 'uid' => $leaderId]);
            }

            $this->logAudit('CREATE_IT_GROUP', 'IT_GROUP', $groupId, null, $name, "Tạo nhóm IT mới {$name} ({$code})");
            $this->db->commit();

            Session::setFlash('success', "Đã tạo nhóm IT '{$name}' thành công.");
        } catch (\Throwable $e) {
            $this->db->rollBack();
            Session::setFlash('error', 'Lỗi khi tạo nhóm IT: ' . $e->getMessage());
        }

        $response->redirect('/admin/groups');
    }

    public function updateGroup(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $name = trim($request->input('name', ''));
        $code = strtoupper(trim($request->input('code', '')));
        $description = trim($request->input('description', ''));
        $leaderId = $request->input('leader_id') ? (int)$request->input('leader_id') : null;

        if (empty($name)) {
            Session::setFlash('error', 'Tên nhóm IT không được để trống.');
            $response->redirect('/admin/groups');
            return;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE it_groups 
                SET name = :name, code = :code, description = :desc, leader_id = :lid 
                WHERE id = :id
            ");
            $stmt->execute([
                'name' => $name,
                'code' => $code,
                'desc' => $description,
                'lid'  => $leaderId,
                'id'   => $id
            ]);

            if ($leaderId) {
                $stmtMem = $this->db->prepare("
                    INSERT INTO it_group_members (group_id, user_id, is_primary, created_at) 
                    VALUES (:gid, :uid, 1, NOW()) 
                    ON DUPLICATE KEY UPDATE is_primary = 1
                ");
                $stmtMem->execute(['gid' => $id, 'uid' => $leaderId]);
            }

            $this->logAudit('UPDATE_IT_GROUP', 'IT_GROUP', $id, null, $name, "Cập nhật nhóm IT {$name}");
            $this->db->commit();

            Session::setFlash('success', "Đã cập nhật nhóm IT '{$name}'.");
        } catch (\Throwable $e) {
            $this->db->rollBack();
            Session::setFlash('error', 'Lỗi khi cập nhật nhóm IT: ' . $e->getMessage());
        }

        $response->redirect('/admin/groups');
    }

    public function deleteGroup(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $itGroupModel = new ITGroup();
        $group = $itGroupModel->findById($id);

        if (!$group) {
            Session::setFlash('error', 'Nhóm IT không tồn tại.');
            $response->redirect('/admin/groups');
            return;
        }

        // Check if tickets belong to this group
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM tickets WHERE group_id = :id");
        $stmtCheck->execute(['id' => $id]);
        $ticketCount = (int)$stmtCheck->fetchColumn();

        if ($ticketCount > 0) {
            // Soft deactivate
            $this->db->prepare("UPDATE it_groups SET is_active = 0 WHERE id = :id")->execute(['id' => $id]);
            Session::setFlash('warning', "Nhóm '{$group['name']}' đã gắn với {$ticketCount} ticket nên hệ thống đã ẩn nhóm thay vì xóa vĩnh viễn.");
        } else {
            $this->db->prepare("DELETE FROM it_group_members WHERE group_id = :id")->execute(['id' => $id]);
            $this->db->prepare("DELETE FROM it_groups WHERE id = :id")->execute(['id' => $id]);
            $this->logAudit('DELETE_IT_GROUP', 'IT_GROUP', $id, $group['name'], null, "Xóa nhóm IT {$group['name']}");
            Session::setFlash('success', "Đã xóa nhóm IT '{$group['name']}'.");
        }

        $response->redirect('/admin/groups');
    }

    // =========================================================================
    // 4. CẤU HÌNH SLA (SLA POLICIES CRUD)
    // =========================================================================

    public function sla(Request $request, Response $response): string
    {
        $slaModel = new SlaConfig();
        $slaConfigs = $slaModel->getAll();

        // Get priorities for adding new SLA rules
        $priorityModel = new Priority();
        $priorities = $priorityModel->getAllActive();

        return $this->render('admin/sla', [
            'pageTitle'  => 'Cấu hình Độ ưu tiên & SLA (Snapshot Rules)',
            'slaConfigs' => $slaConfigs,
            'priorities' => $priorities
        ]);
    }

    public function createSla(Request $request, Response $response): void
    {
        $priorityId = (int)$request->input('priority_id');
        $name = trim($request->input('name', ''));
        $resolutionTime = (int)$request->input('resolution_time', 4);
        $resolutionUnit = strtoupper(trim($request->input('resolution_unit', 'HOUR')));
        $warningPercent = (int)$request->input('warning_threshold_percent', 70);
        $criticalPercent = (int)$request->input('critical_threshold_percent', 90);

        if (!$priorityId || empty($name) || $resolutionTime <= 0) {
            Session::setFlash('error', 'Vui lòng chọn mức độ ưu tiên, nhập tên quy chuẩn và thời gian xử lý hợp lệ.');
            $response->redirect('/admin/sla');
            return;
        }

        if (!in_array($resolutionUnit, ['HOUR', 'DAY'])) {
            $resolutionUnit = 'HOUR';
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO sla_configs (priority_id, name, resolution_time, resolution_unit, warning_threshold_percent, critical_threshold_percent, is_active, created_at)
                VALUES (:pid, :name, :rtime, :runit, :wth, :cth, 1, NOW())
            ");
            $stmt->execute([
                'pid'   => $priorityId,
                'name'  => $name,
                'rtime' => $resolutionTime,
                'runit' => $resolutionUnit,
                'wth'   => $warningPercent,
                'cth'   => $criticalPercent
            ]);
            $slaId = (int)$this->db->lastInsertId();

            $this->logAudit('CREATE_SLA_CONFIG', 'SLA_CONFIG', $slaId, null, $name, "Thêm cấu hình SLA {$name}");
            Session::setFlash('success', "Đã thêm quy định SLA '{$name}' thành công.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi tạo SLA: ' . $e->getMessage());
        }

        $response->redirect('/admin/sla');
    }

    public function updateSla(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        $name = trim($request->input('name', ''));
        $resolutionTime = (int)$request->input('resolution_time', 4);
        $resolutionUnit = strtoupper(trim($request->input('resolution_unit', 'HOUR')));
        $warningPercent = (int)$request->input('warning_threshold_percent', 70);
        $criticalPercent = (int)$request->input('critical_threshold_percent', 90);
        $isActive = $request->input('is_active') !== null ? (int)$request->input('is_active') : 1;

        if (empty($name) || $resolutionTime <= 0) {
            Session::setFlash('error', 'Tên quy chuẩn và thời gian cam kết không được để trống.');
            $response->redirect('/admin/sla');
            return;
        }

        if (!in_array($resolutionUnit, ['HOUR', 'DAY'])) {
            $resolutionUnit = 'HOUR';
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE sla_configs 
                SET name = :name, resolution_time = :rtime, resolution_unit = :runit, 
                    warning_threshold_percent = :wth, critical_threshold_percent = :cth, is_active = :act
                WHERE id = :id
            ");
            $stmt->execute([
                'name'  => $name,
                'rtime' => $resolutionTime,
                'runit' => $resolutionUnit,
                'wth'   => $warningPercent,
                'cth'   => $criticalPercent,
                'act'   => $isActive,
                'id'    => $id
            ]);

            $this->logAudit('UPDATE_SLA_CONFIG', 'SLA_CONFIG', $id, null, $name, "Cập nhật cấu hình SLA {$name}");
            Session::setFlash('success', "Đã cập nhật quy định SLA '{$name}'.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi cập nhật SLA: ' . $e->getMessage());
        }

        $response->redirect('/admin/sla');
    }

    public function deleteSla(Request $request, Response $response, string $id): void
    {
        $id = (int)$id;
        try {
            $stmt = $this->db->prepare("DELETE FROM sla_configs WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $this->logAudit('DELETE_SLA_CONFIG', 'SLA_CONFIG', $id, null, null, "Xóa cấu hình SLA #{$id}");
            Session::setFlash('success', "Đã xóa quy định SLA.");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Lỗi khi xóa cấu hình SLA: ' . $e->getMessage());
        }

        $response->redirect('/admin/sla');
    }

    // =========================================================================
    // 6. MA TRẬN PHÂN QUYỀN (ROLE & PERMISSION MATRIX)
    // =========================================================================

    public function permissionsMatrix(Request $request, Response $response): string
    {
        // 1. Get all roles
        $stmtRoles = $this->db->query("SELECT * FROM roles ORDER BY id ASC");
        $roles = $stmtRoles->fetchAll();

        // 2. Get all permissions grouped
        $stmtPerms = $this->db->query("SELECT * FROM permissions ORDER BY module ASC, sort_order ASC, id ASC");
        $allPerms = $stmtPerms->fetchAll();

        $groupedPerms = [];
        foreach ($allPerms as $perm) {
            $groupedPerms[$perm['module']][] = $perm;
        }

        // 3. Get active mappings: [role_id => [permission_id => true]]
        $stmtMap = $this->db->query("SELECT role_id, permission_id FROM role_permissions");
        $activeMatrix = [];
        while ($row = $stmtMap->fetch()) {
            $activeMatrix[(int)$row['role_id']][(int)$row['permission_id']] = true;
        }

        return $this->render('admin/permissions', [
            'pageTitle'     => 'Ma Trận Phân Quyền Vai Trò Hệ Thống (RBAC Matrix)',
            'roles'         => $roles,
            'groupedPerms'  => $groupedPerms,
            'activeMatrix'  => $activeMatrix
        ]);
    }

    public function updatePermissionsMatrix(Request $request, Response $response): void
    {
        $matrix = (array)$request->input('matrix', []); // format: [role_id => [perm_id => 1]]

        try {
            $this->db->beginTransaction();

            // Clear old permissions
            $this->db->exec("DELETE FROM role_permissions");

            // Insert new mappings
            $stmtInsert = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)");

            $count = 0;
            foreach ($matrix as $roleId => $permIds) {
                $roleId = (int)$roleId;
                if ($roleId <= 0 || !is_array($permIds)) {
                    continue;
                }
                foreach ($permIds as $permId => $val) {
                    $permId = (int)$permId;
                    if ($permId > 0 && !empty($val)) {
                        $stmtInsert->execute([
                            'role_id'       => $roleId,
                            'permission_id' => $permId
                        ]);
                        $count++;
                    }
                }
            }

            $this->db->commit();
            Auth::clearPermissionCache();

            $this->logAudit('UPDATE_PERMISSION_MATRIX', 'PERMISSIONS', null, null, "{$count} permissions", "Cập nhật ma trận phân quyền RBAC");
            Session::setFlash('success', "Đã cập nhật thành công {$count} quyền hạn trong ma trận phân quyền!");
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            Session::setFlash('error', 'Lỗi khi cập nhật ma trận quyền: ' . $e->getMessage());
        }

        $response->redirect('/admin/permissions');
    }

    public function auditLogs(Request $request, Response $response): string
    {
        $stmt = $this->db->query("
            SELECT a.*, u.full_name as user_name, u.username
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT 100
        ");
        $logs = $stmt->fetchAll();

        return $this->render('admin/audit_logs', [
            'pageTitle' => 'Nhật Ký Kiểm Toán Toàn Bộ Hệ Thống',
            'logs'      => $logs,
        ]);
    }

    public function exportAuditLogs(Request $request, Response $response): void
    {
        if (!Auth::can('export.audit_logs')) {
            Session::setFlash('error', 'Bạn không có quyền xuất dữ liệu nhật ký kiểm toán.');
            $response->redirect('/admin/audit-logs');
            return;
        }

        $stmt = $this->db->query("
            SELECT a.*, u.full_name as user_name, u.username
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
        ");
        $logs = $stmt->fetchAll();

        $filename = 'audit_logs_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // Add UTF-8 BOM so Excel opens with Vietnamese characters without corrupting
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, ['ID', 'Thời gian', 'Người thực hiện', 'Tài khoản', 'Hành động', 'Đối tượng', 'ID đối tượng', 'Giá trị cũ', 'Giá trị mới', 'Lý do']);

        foreach ($logs as $row) {
            fputcsv($out, [
                $row['id'],
                $row['created_at'],
                $row['user_name'] ?? 'Hệ thống / Guest',
                $row['username'] ?? '',
                $row['action'],
                $row['object_type'],
                $row['object_id'] ?? '',
                $row['old_value'] ?? '',
                $row['new_value'] ?? '',
                $row['reason'] ?? ''
            ]);
        }
        fclose($out);
        exit;
    }
}
