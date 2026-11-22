<?php
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Database;

$db = Database::getConnection();
echo "Seeding 30 Enterprise Accounts...\n";

// Password hash for '123456'
$defaultPasswordHash = password_hash('123456', PASSWORD_BCRYPT);

// 1. 10 End-User accounts (Accounting & Business)
$endUsers = [
    [
        'username'      => 'ketoan_lan',
        'email'         => 'lan.nguyen@company.local',
        'full_name'     => 'Nguyễn Thị Hương Lan',
        'department'    => 'Phòng Kế Toán',
        'seat_location' => 'Tầng 2 - Phòng KT-01 (Kế Toán Tổng Hợp)',
        'job_title'     => 'Chuyên viên Kế toán Tổng hợp',
        'phone'         => '0903001001',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'ketoan_minh',
        'email'         => 'minh.tran@company.local',
        'full_name'     => 'Trần Đức Minh',
        'department'    => 'Phòng Kế Toán',
        'seat_location' => 'Tầng 2 - Phòng KT-02 (Kế Toán Thuế)',
        'job_title'     => 'Kế toán Thuế & Ngân sách',
        'phone'         => '0903001002',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'ketoan_thao',
        'email'         => 'thao.pham@company.local',
        'full_name'     => 'Phạm Thu Thảo',
        'department'    => 'Phòng Kế Toán',
        'seat_location' => 'Tầng 2 - Phòng KT-03 (Công Nợ)',
        'job_title'     => 'Kế toán Thanh toán & Công nợ',
        'phone'         => '0903001003',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'ketoan_truong',
        'email'         => 'truong.hoang@company.local',
        'full_name'     => 'Hoàng Văn Trường',
        'department'    => 'Phòng Kế Toán',
        'seat_location' => 'Tầng 2 - Phòng KT-Trưởng (Kế Toán Trưởng)',
        'job_title'     => 'Kế toán Trưởng',
        'phone'         => '0903001004',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'ketoan_huyen',
        'email'         => 'huyen.vu@company.local',
        'full_name'     => 'Vũ Khánh Huyền',
        'department'    => 'Phòng Kế Toán',
        'seat_location' => 'Tầng 2 - Phòng KT-04 (Thủ Quỹ)',
        'job_title'     => 'Thủ quỹ & Kế toán Kho',
        'phone'         => '0903001005',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'kinhdoanh_nam',
        'email'         => 'nam.dang@company.local',
        'full_name'     => 'Đặng Hải Nam',
        'department'    => 'Phòng Kinh Doanh',
        'seat_location' => 'Tầng 3 - P.KD-01 (Trưởng Phòng KD)',
        'job_title'     => 'Trưởng phòng Kinh doanh',
        'phone'         => '0904001001',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'kinhdoanh_nga',
        'email'         => 'nga.bui@company.local',
        'full_name'     => 'Bùi Thanh Nga',
        'department'    => 'Phòng Kinh Doanh',
        'seat_location' => 'Tầng 3 - Bàn KD-02 (Sales B2B)',
        'job_title'     => 'Chuyên viên Kinh doanh B2B',
        'phone'         => '0904001002',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'kinhdoanh_quang',
        'email'         => 'quang.le@company.local',
        'full_name'     => 'Lê Nhật Quang',
        'department'    => 'Phòng Kinh Doanh',
        'seat_location' => 'Tầng 3 - Bàn KD-03 (Thị Trường)',
        'job_title'     => 'Chuyên viên Phát triển Thị trường',
        'phone'         => '0904001003',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'kinhdoanh_yen',
        'email'         => 'yen.ngo@company.local',
        'full_name'     => 'Ngô Hải Yến',
        'department'    => 'Phòng Kinh Doanh',
        'seat_location' => 'Tầng 3 - Bàn KD-04 (CSKH Doanh Nghiệp)',
        'job_title'     => 'Quản lý Khách hàng Doanh nghiệp',
        'phone'         => '0904001004',
        'role'          => 'USER',
        'it_group'      => null
    ],
    [
        'username'      => 'kinhdoanh_dat',
        'email'         => 'dat.do@company.local',
        'full_name'     => 'Đỗ Thành Đạt',
        'department'    => 'Phòng Kinh Doanh',
        'seat_location' => 'Tầng 3 - Bàn KD-05 (Dự Án)',
        'job_title'     => 'Nhân viên Kinh doanh Dự án',
        'phone'         => '0904001005',
        'role'          => 'USER',
        'it_group'      => null
    ],
];

// 2. 20 IT accounts (Directors, Managers, Specialists across groups)
$itUsers = [
    // SẾP / DIRECTORS
    [
        'username'      => 'it_cio',
        'email'         => 'tri.phan@itdesk.local',
        'full_name'     => 'PGS. TS. Phan Đình Trí',
        'department'    => 'Ban Giám Đốc CNTT',
        'seat_location' => 'Tầng 5 - Phòng CIO-01 (Giám Đốc)',
        'job_title'     => 'Giám đốc Công nghệ Thông tin (CIO)',
        'phone'         => '0901999001',
        'role'          => 'ADMIN',
        'it_group'      => 1
    ],
    [
        'username'      => 'it_director',
        'email'         => 'bach.dao@itdesk.local',
        'full_name'     => 'Đào Xuân Bách',
        'department'    => 'Ban Giám Đốc CNTT',
        'seat_location' => 'Tầng 5 - Phòng IT-02 (Phó Giám Đốc)',
        'job_title'     => 'Phó Giám đốc Khối Vận hành IT',
        'phone'         => '0901999002',
        'role'          => 'ADMIN',
        'it_group'      => 3
    ],

    // QUẢN LÝ / IT MANAGERS & TEAM LEADS
    [
        'username'      => 'it_mgr_helpdesk',
        'email'         => 'dung.nguyen@itdesk.local',
        'full_name'     => 'Nguyễn Anh Dũng',
        'department'    => 'IT Helpdesk & Hỗ Trợ',
        'seat_location' => 'Tầng 4 - P.HD-Lead (Trưởng Nhóm Helpdesk)',
        'job_title'     => 'Trưởng nhóm IT Helpdesk & Support',
        'phone'         => '0901999003',
        'role'          => 'IT_MANAGER',
        'it_group'      => 1
    ],
    [
        'username'      => 'it_mgr_network',
        'email'         => 'long.trinh@itdesk.local',
        'full_name'     => 'Trịnh Bá Long',
        'department'    => 'IT Network & Hạ Tầng',
        'seat_location' => 'Tầng 4 - P.NET-01 (Trưởng Phòng Mạng)',
        'job_title'     => 'Trưởng phòng Mạng & Hạ tầng',
        'phone'         => '0901999004',
        'role'          => 'IT_MANAGER',
        'it_group'      => 2
    ],
    [
        'username'      => 'it_mgr_system',
        'email'         => 'dang.vu@itdesk.local',
        'full_name'     => 'Vũ Hải Đăng',
        'department'    => 'IT Hệ Thống Máy Chủ',
        'seat_location' => 'Tầng 4 - P.SYS-01 (Trưởng Nhóm Hệ Thống)',
        'job_title'     => 'Trưởng nhóm Quản trị Hệ thống & Cloud',
        'phone'         => '0901999005',
        'role'          => 'IT_MANAGER',
        'it_group'      => 3
    ],
    [
        'username'      => 'it_mgr_dev',
        'email'         => 'hung.mai@itdesk.local',
        'full_name'     => 'Mai Thế Hùng',
        'department'    => 'IT Phần Mềm & Ứng Dụng',
        'seat_location' => 'Tầng 4 - P.DEV-01 (Trưởng Phòng Phần Mềm)',
        'job_title'     => 'Trưởng phòng Phát triển Phần mềm',
        'phone'         => '0901999006',
        'role'          => 'IT_MANAGER',
        'it_group'      => 4
    ],
    [
        'username'      => 'it_mgr_qa',
        'email'         => 'trang.ho@itdesk.local',
        'full_name'     => 'Hồ Quỳnh Trang',
        'department'    => 'IT Đảm Bảo Chất Lượng QA/QC',
        'seat_location' => 'Tầng 4 - P.QA-01 (Trưởng Nhóm QA)',
        'job_title'     => 'Trưởng nhóm Đảm bảo Chất lượng (QA Lead)',
        'phone'         => '0901999007',
        'role'          => 'IT_MANAGER',
        'it_group'      => 4
    ],

    // NHÂN VIÊN KỸ THUẬT / SPECIALISTS & ENGINEERS
    [
        'username'      => 'it_hd_tuan',
        'email'         => 'tuan.luu@itdesk.local',
        'full_name'     => 'Lưu Minh Tuấn',
        'department'    => 'IT Helpdesk & Hỗ Trợ',
        'seat_location' => 'Tầng 4 - Bàn HD-01 (Quầy Helpdesk)',
        'job_title'     => 'Kỹ thuật viên Helpdesk L1',
        'phone'         => '0901999008',
        'role'          => 'IT_STAFF',
        'it_group'      => 1
    ],
    [
        'username'      => 'it_hd_linh',
        'email'         => 'linh.dinh@itdesk.local',
        'full_name'     => 'Đinh Mỹ Linh',
        'department'    => 'IT Helpdesk & Hỗ Trợ',
        'seat_location' => 'Tầng 4 - Bàn HD-02 (Quầy Helpdesk)',
        'job_title'     => 'Kỹ thuật viên Helpdesk L1',
        'phone'         => '0901999009',
        'role'          => 'IT_STAFF',
        'it_group'      => 1
    ],
    [
        'username'      => 'it_hd_viet',
        'email'         => 'viet.chu@itdesk.local',
        'full_name'     => 'Chu Quốc Việt',
        'department'    => 'IT Phần Cứng & Thiết Bị',
        'seat_location' => 'Tầng 4 - Bàn HD-03 (Hỗ Trợ On-site)',
        'job_title'     => 'Kỹ thuật viên Hỗ trợ On-site L2',
        'phone'         => '0901999010',
        'role'          => 'IT_STAFF',
        'it_group'      => 5
    ],
    [
        'username'      => 'it_net_kien',
        'email'         => 'kien.duong@itdesk.local',
        'full_name'     => 'Dương Trung Kiên',
        'department'    => 'IT Network & Hạ Tầng',
        'seat_location' => 'Tầng 4 - Bàn NET-02 (Kỹ Sư Mạng)',
        'job_title'     => 'Kỹ sư Mạng & Firewall Cisco/Fortinet',
        'phone'         => '0901999011',
        'role'          => 'IT_STAFF',
        'it_group'      => 2
    ],
    [
        'username'      => 'it_net_son',
        'email'         => 'son.doan@itdesk.local',
        'full_name'     => 'Đoàn Ngọc Sơn',
        'department'    => 'IT Network & Hạ Tầng',
        'seat_location' => 'Tầng 4 - Bàn NET-03 (Hạ Tầng)',
        'job_title'     => 'Kỹ sư Hạ tầng Mạng & Viễn thông',
        'phone'         => '0901999012',
        'role'          => 'IT_STAFF',
        'it_group'      => 2
    ],
    [
        'username'      => 'it_sys_duc',
        'email'         => 'duc.nguyen@itdesk.local',
        'full_name'     => 'Nguyễn Văn Đức',
        'department'    => 'IT Hệ Thống Máy Chủ',
        'seat_location' => 'Tầng 4 - Bàn SYS-02 (Quản Trị Linux)',
        'job_title'     => 'Kỹ sư Hệ thống Linux & Kubernetes',
        'phone'         => '0901999013',
        'role'          => 'IT_STAFF',
        'it_group'      => 3
    ],
    [
        'username'      => 'it_sys_thinh',
        'email'         => 'thinh.tran@itdesk.local',
        'full_name'     => 'Trần Quang Thịnh',
        'department'    => 'IT Hệ Thống Máy Chủ',
        'seat_location' => 'Tầng 4 - Bàn SYS-03 (Windows Server)',
        'job_title'     => 'Chuyên viên Windows Server & AD',
        'phone'         => '0901999014',
        'role'          => 'IT_STAFF',
        'it_group'      => 3
    ],
    [
        'username'      => 'it_dev_phong',
        'email'         => 'phong.hoang@itdesk.local',
        'full_name'     => 'Hoàng Nam Phong',
        'department'    => 'IT Phần Mềm & Ứng Dụng',
        'seat_location' => 'Tầng 4 - Bàn DEV-02 (Lập Trình Backend)',
        'job_title'     => 'Lập trình viên Backend PHP/MySQL',
        'phone'         => '0901999015',
        'role'          => 'IT_STAFF',
        'it_group'      => 4
    ],
    [
        'username'      => 'it_dev_mai',
        'email'         => 'mai.ta@itdesk.local',
        'full_name'     => 'Tạ Phương Mai',
        'department'    => 'IT Phần Mềm & Ứng Dụng',
        'seat_location' => 'Tầng 4 - Bàn DEV-03 (Lập Trình UI/UX)',
        'job_title'     => 'Lập trình viên Frontend UI/UX',
        'phone'         => '0901999016',
        'role'          => 'IT_STAFF',
        'it_group'      => 4
    ],
    [
        'username'      => 'it_qa_vy',
        'email'         => 'vy.luong@itdesk.local',
        'full_name'     => 'Lương Thảo Vy',
        'department'    => 'IT Đảm Bảo Chất Lượng QA/QC',
        'seat_location' => 'Tầng 4 - Bàn QA-02 (Kiểm Thử Manual)',
        'job_title'     => 'Kỹ sư Kiểm thử Phần mềm (QA Tester)',
        'phone'         => '0901999017',
        'role'          => 'IT_STAFF',
        'it_group'      => 4
    ],
    [
        'username'      => 'it_qa_hieu',
        'email'         => 'hieu.bui@itdesk.local',
        'full_name'     => 'Bùi Trung Hiếu',
        'department'    => 'IT Đảm Bảo Chất Lượng QA/QC',
        'seat_location' => 'Tầng 4 - Bàn QA-03 (Kiểm Thử Tự Động)',
        'job_title'     => 'Kỹ sư Kiểm thử Tự động (Automation QA)',
        'phone'         => '0901999018',
        'role'          => 'IT_STAFF',
        'it_group'      => 4
    ],
    [
        'username'      => 'it_sec_quan',
        'email'         => 'quan.pham@itdesk.local',
        'full_name'     => 'Phạm Anh Quân',
        'department'    => 'IT An Toàn Thông Tin SOC',
        'seat_location' => 'Tầng 4 - Bàn SEC-01 (Bảo Mật SOC)',
        'job_title'     => 'Chuyên viên An toàn Thông tin & SOC',
        'phone'         => '0901999019',
        'role'          => 'IT_STAFF',
        'it_group'      => 2
    ],
    [
        'username'      => 'it_db_khang',
        'email'         => 'khang.vo@itdesk.local',
        'full_name'     => 'Võ Duy Khang',
        'department'    => 'IT Hệ Thống Máy Chủ',
        'seat_location' => 'Tầng 4 - Bàn DBA-01 (Quản Trị CSDL)',
        'job_title'     => 'Chuyên viên Quản trị Cơ sở Dữ liệu (DBA)',
        'phone'         => '0901999020',
        'role'          => 'IT_STAFF',
        'it_group'      => 3
    ]
];

$allUsersToCreate = array_merge($endUsers, $itUsers);

// Prepared statements
$stmtCheck = $db->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
$stmtInsert = $db->prepare("
    INSERT INTO users (username, email, password_hash, full_name, department, seat_location, job_title, phone, is_active, created_at)
    VALUES (:u, :e, :p, :fn, :dep, :seat, :title, :ph, 1, NOW())
");
$stmtUpdate = $db->prepare("
    UPDATE users 
    SET email = :e, password_hash = :p, full_name = :fn, department = :dep, seat_location = :seat, job_title = :title, phone = :ph, is_active = 1
    WHERE id = :id
");

$stmtRoleLookup = $db->prepare("SELECT id FROM roles WHERE code = :c LIMIT 1");
$stmtAssignRole = $db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
$stmtAssignGroup = $db->prepare("INSERT IGNORE INTO it_group_members (group_id, user_id, is_primary) VALUES (:gid, :uid, 1)");
$stmtAssignProject = $db->prepare("INSERT IGNORE INTO project_members (project_id, user_id, role, created_at) VALUES (1, :uid, :r, NOW())");

$createdCount = 0;
$updatedCount = 0;

foreach ($allUsersToCreate as $u) {
    $stmtCheck->execute(['u' => $u['username']]);
    $existingId = $stmtCheck->fetchColumn();

    if ($existingId) {
        $stmtUpdate->execute([
            'e'     => $u['email'],
            'p'     => $defaultPasswordHash,
            'fn'    => $u['full_name'],
            'dep'   => $u['department'],
            'seat'  => $u['seat_location'],
            'title' => $u['job_title'],
            'ph'    => $u['phone'],
            'id'    => $existingId
        ]);
        $userId = (int)$existingId;
        $updatedCount++;
    } else {
        $stmtInsert->execute([
            'u'     => $u['username'],
            'e'     => $u['email'],
            'p'     => $defaultPasswordHash,
            'fn'    => $u['full_name'],
            'dep'   => $u['department'],
            'seat'  => $u['seat_location'],
            'title' => $u['job_title'],
            'ph'    => $u['phone']
        ]);
        $userId = (int)$db->lastInsertId();
        $createdCount++;
    }

    // Role assignment
    $stmtRoleLookup->execute(['c' => $u['role']]);
    $roleId = $stmtRoleLookup->fetchColumn();
    if ($roleId) {
        $stmtAssignRole->execute(['uid' => $userId, 'rid' => $roleId]);
    }

    // IT Group assignment
    if (!empty($u['it_group'])) {
        $stmtAssignGroup->execute(['gid' => $u['it_group'], 'uid' => $userId]);
    }

    // Assign to Project 1 (Beyond Gravity) for TaskFlow collaboration
    $projectRole = match ($u['role']) {
        'ADMIN'      => 'ADMIN',
        'IT_MANAGER' => 'ADMIN',
        default      => 'MEMBER'
    };
    $stmtAssignProject->execute(['uid' => $userId, 'r' => $projectRole]);
}

echo "[SUCCESS] Finished seeding 30 enterprise accounts. Created: {$createdCount}, Updated: {$updatedCount}.\n";

