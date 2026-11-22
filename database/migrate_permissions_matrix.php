<?php
/**
 * Database Migration: Permissions and Role Permissions Matrix
 */
require_once __DIR__ . '/../app/autoload.php';

$db = \App\Core\Database::getConnection();

echo "Starting migration: permissions & role_permissions...\n";

// 1. Create permissions table
$db->exec("
    CREATE TABLE IF NOT EXISTS `permissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `module` VARCHAR(50) NOT NULL,
        `code` VARCHAR(100) NOT NULL UNIQUE,
        `name` VARCHAR(150) NOT NULL,
        `description` VARCHAR(255) NULL,
        `is_menu` TINYINT(1) DEFAULT 0,
        `sort_order` INT DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// 2. Create role_permissions table
$db->exec("
    CREATE TABLE IF NOT EXISTS `role_permissions` (
        `role_id` INT NOT NULL,
        `permission_id` INT NOT NULL,
        PRIMARY KEY (`role_id`, `permission_id`),
        CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// 3. Seed default permissions
$permissions = [
    // --- MODULE: MENU VISIBILITY ---
    ['PROJECTS', 'menu.projects', 'Menu: Dự Án Agile (TaskFlow)', 'Cho phép xem và vào phân hệ Quản lý Dự án Agile', 1, 10],
    ['TICKETS', 'menu.tickets', 'Menu: Vé Hỗ Trợ (ITSM)', 'Cho phép xem và vào phân hệ Quản lý Vé Hỗ Trợ IT', 1, 20],
    ['CATALOG', 'menu.catalog', 'Menu: Cổng Dịch Vụ (Catalog)', 'Cho phép xem Cổng Dịch Vụ Công Nghệ', 1, 30],
    ['APPROVALS', 'menu.approvals', 'Menu: Xét Duyệt (Approvals)', 'Cho phép vào Trung tâm phê duyệt yêu cầu dịch vụ', 1, 40],
    ['ASSETS', 'menu.assets', 'Menu: Quản Lý Tài Sản (ITAM)', 'Cho phép xem và quản lý thiết bị/tài sản IT', 1, 50],
    ['KB', 'menu.kb', 'Menu: Cơ Sở Tri Thức (KB)', 'Cho phép đọc và tra cứu bài viết tri thức', 1, 60],
    ['JUMPSERVER', 'menu.jumpserver', 'Menu: JumpServer Bastion PAM', 'Cho phép truy cập máy chủ đặc quyền JumpServer', 1, 70],
    ['REPORTS', 'menu.reports', 'Menu: Báo Cáo & Thống Kê SLA', 'Cho phép xem bảng phân tích SLA và hiệu suất', 1, 80],
    ['ADMIN', 'menu.admin', 'Menu: Quản Trị Hệ Thống', 'Cho phép vào khu vực Quản trị Người dùng, Nhóm, Cấu hình', 1, 90],
    ['SYSTEM', 'menu.backup', 'Menu: Sao Lưu & Phục Hồi CSDL', 'Cho phép quản lý bản sao lưu và khôi phục dữ liệu', 1, 100],

    // --- MODULE: TICKETS ACTIONS ---
    ['TICKETS', 'tickets.create', 'Tạo Phiếu Hỗ Trợ Mới', 'Gửi báo cáo sự cố hoặc yêu cầu dịch vụ IT', 0, 110],
    ['TICKETS', 'tickets.assign', 'Phân Công Kỹ Thuật Viên', 'Gán hoặc chuyển giao ticket cho nhân sự xử lý', 0, 120],
    ['TICKETS', 'tickets.resolve', 'Giải Quyết & Hoàn Thành Ticket', 'Cập nhật giải pháp kỹ thuật và kết thúc ticket', 0, 130],
    ['TICKETS', 'tickets.priority', 'Điều Chỉnh Mức Độ Ưu Tiên', 'Thay đổi priority SLA của ticket', 0, 140],
    ['TICKETS', 'tickets.delete', 'Hủy / Đánh Dấu Trùng Lặp', 'Hủy ticket hoặc đánh dấu trùng lặp (Duplicate)', 0, 150],

    // --- MODULE: PROJECTS ACTIONS ---
    ['PROJECTS', 'projects.create', 'Tạo Dự Án Mới', 'Khởi tạo không gian dự án phần mềm mới', 0, 160],
    ['PROJECTS', 'projects.manage', 'Quản Trị Thiết Lập Dự Án', 'Thay đổi thành viên, workflow và cài đặt dự án', 0, 170],

    // --- MODULE: EXPORT EXCEL ---
    ['EXPORT', 'export.tickets', 'Xuất Excel: Danh Sách Ticket', 'Tải file Excel danh sách các phiếu hỗ trợ', 0, 180],
    ['EXPORT', 'export.issues', 'Xuất Excel: Công Việc Dự Án', 'Tải file Excel danh sách công việc/bugs/testcases', 0, 190],
    ['EXPORT', 'export.assets', 'Xuất Excel: Quản Lý Tài Sản', 'Tải file Excel danh mục thiết bị và tài sản CNTT', 0, 200],
    ['EXPORT', 'export.audit_logs', 'Xuất Excel: Nhật Ký Kiểm Toán', 'Tải file Excel nhật ký truy vết hệ thống', 0, 210],

    // --- MODULE: DISASTER RECOVERY ---
    ['SYSTEM', 'system.backup', 'Sao Lưu & Khôi Phục Dữ Liệu', 'Thực hiện tạo bản snapshot và restore CSDL', 0, 220],
];

$stmtInsertPerm = $db->prepare("
    INSERT INTO permissions (module, code, name, description, is_menu, sort_order)
    VALUES (:mod, :code, :name, :desc, :menu, :sort)
    ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), is_menu = VALUES(is_menu), sort_order = VALUES(sort_order)
");

foreach ($permissions as $p) {
    $stmtInsertPerm->execute([
        'mod'  => $p[0],
        'code' => $p[1],
        'name' => $p[2],
        'desc' => $p[3],
        'menu' => $p[4],
        'sort' => $p[5],
    ]);
}
echo "Seeded " . count($permissions) . " permissions.\n";

$roles = $db->query("SELECT code, id FROM roles")->fetchAll(PDO::FETCH_KEY_PAIR);
// e.g. ['ADMIN' => 1, 'IT_MANAGER' => 2, 'IT_STAFF' => 3, 'USER' => 4]

$allPermIds = $db->query("SELECT code, id FROM permissions")->fetchAll(PDO::FETCH_KEY_PAIR);

// Mapping matrix
$rolePermissionsMap = [
    'ADMIN' => array_keys($allPermIds), // ADMIN has ALL permissions
    'IT_MANAGER' => [
        'menu.projects', 'menu.tickets', 'menu.catalog', 'menu.approvals', 'menu.assets', 'menu.kb', 'menu.jumpserver', 'menu.reports',
        'tickets.create', 'tickets.assign', 'tickets.resolve', 'tickets.priority', 'tickets.delete',
        'projects.create', 'projects.manage',
        'export.tickets', 'export.issues', 'export.assets',
    ],
    'IT_STAFF' => [
        'menu.projects', 'menu.tickets', 'menu.catalog', 'menu.approvals', 'menu.assets', 'menu.kb', 'menu.jumpserver',
        'tickets.create', 'tickets.assign', 'tickets.resolve',
        'export.tickets', 'export.issues', 'export.assets'
    ],
    'USER' => [
        'menu.projects', 'menu.tickets', 'menu.catalog', 'menu.approvals', 'menu.kb',
        'tickets.create'
    ]
];

$stmtInsertRP = $db->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (:rid, :pid)");

foreach ($rolePermissionsMap as $roleCode => $permCodes) {
    $roleId = $roles[$roleCode] ?? null;
    if (!$roleId) continue;

    foreach ($permCodes as $pCode) {
        $permId = $allPermIds[$pCode] ?? null;
        if ($permId) {
            $stmtInsertRP->execute(['rid' => $roleId, 'pid' => $permId]);
        }
    }
}

echo "Role permissions mapping seeded successfully!\n";
