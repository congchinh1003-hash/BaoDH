<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Database;

$pdo = Database::getConnection();

echo "Running Jira Projects & Issues Migration...\n";

// 1. Run Schema SQL
$schemaSql = file_get_contents(__DIR__ . '/schema_projects_jira.sql');
$statements = array_filter(array_map('trim', explode(';', $schemaSql)));

foreach ($statements as $stmt) {
    if (!empty($stmt)) {
        try {
            $pdo->exec($stmt);
        } catch (\Throwable $e) {
            echo "Notice on statement: " . $e->getMessage() . "\n";
        }
    }
}
echo "[OK] Tables 'projects', 'project_members', 'project_issues' verified.\n";

// 2. Fetch Users for assignments
$usersStmt = $pdo->query("SELECT id, username, full_name, avatar FROM users ORDER BY id ASC");
$allUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($allUsers)) {
    die("Error: No users found in database to assign.\n");
}

$adminUser = $allUsers[0];
$dev1 = $allUsers[1] ?? $adminUser;
$dev2 = $allUsers[2] ?? $adminUser;
$tester = $allUsers[3] ?? $adminUser;

// 3. Create or Update Seed Project: Beyond Gravity (Key: NUC / BG)
$checkProject = $pdo->prepare("SELECT id FROM projects WHERE project_key = 'NUC' OR name = 'Beyond Gravity'");
$checkProject->execute();
$existingProject = $checkProject->fetch(PDO::FETCH_ASSOC);

if (!$existingProject) {
    $insertPrj = $pdo->prepare("
        INSERT INTO projects (project_key, name, description, project_type, lead_id, avatar_icon, avatar_bg, status)
        VALUES ('NUC', 'Beyond Gravity', 'Nền tảng thương mại điện tử du lịch & đặt chuyến bay không gian', 'SOFTWARE', :lead_id, 'bi-rocket-takeoff-fill', '#6554C0', 'ACTIVE')
    ");
    $insertPrj->execute(['lead_id' => $adminUser['id']]);
    $projectId = (int)$pdo->lastInsertId();
    echo "[OK] Created project 'Beyond Gravity' (Key: NUC, ID: {$projectId})\n";
} else {
    $projectId = (int)$existingProject['id'];
    echo "[INFO] Project 'Beyond Gravity' exists (ID: {$projectId})\n";
}

// 4. Add Members to Project
foreach ($allUsers as $u) {
    $role = ($u['id'] == $adminUser['id']) ? 'ADMIN' : 'MEMBER';
    $addMember = $pdo->prepare("
        INSERT IGNORE INTO project_members (project_id, user_id, role)
        VALUES (:pid, :uid, :role)
    ");
    $addMember->execute([
        'pid'  => $projectId,
        'uid'  => $u['id'],
        'role' => $role
    ]);
}
echo "[OK] Project members added.\n";

// 5. Check if issues already seeded
$countIssues = $pdo->prepare("SELECT COUNT(*) FROM project_issues WHERE project_id = :pid");
$countIssues->execute(['pid' => $projectId]);
$issueCount = (int)$countIssues->fetchColumn();

if ($issueCount < 20) {
    echo "Seeding initial issues matching Jira reference board...\n";

    // Data matching user's screenshot
    $seedIssues = [
        // TO DO
        [
            'num' => 344, 'type' => 'STORY', 'component' => 'BILLING', 'status' => 'TO_DO',
            'summary' => 'Optimize experience for mobile web',
            'desc' => 'Tối ưu hóa luồng thanh toán và trải nghiệm người dùng trên thiết bị di động màn hình nhỏ.',
            'priority' => 'HIGH', 'points' => 3, 'assignee' => $dev1['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 360, 'type' => 'TASK', 'component' => 'ACCOUNTS', 'status' => 'TO_DO',
            'summary' => 'Onboard workout options (OWO)',
            'desc' => 'Tích hợp mô-đun hướng dẫn đăng ký tài khoản và thiết lập cấu hình onboarding ban đầu.',
            'priority' => 'MEDIUM', 'points' => 2, 'assignee' => $dev2['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 337, 'type' => 'TASK', 'component' => 'ACCOUNTS', 'status' => 'TO_DO',
            'summary' => 'Multi-dest search UI mobileweb',
            'desc' => 'Giao diện tìm kiếm đa điểm đến trên mobile responsive với bộ lọc nâng cao.',
            'priority' => 'MEDIUM', 'points' => 5, 'assignee' => $adminUser['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 339, 'type' => 'TASK', 'component' => 'FORMS', 'status' => 'TO_DO',
            'summary' => 'Billing system integration - frontend',
            'desc' => 'Ghép nối API cổng thanh toán VNPay và thẻ tín dụng quốc tế vào form đặt chỗ.',
            'priority' => 'HIGH', 'points' => 3, 'assignee' => $dev1['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 340, 'type' => 'TASK', 'component' => 'ACCOUNTS', 'status' => 'TO_DO',
            'summary' => 'Account settings defaults',
            'desc' => 'Thiết lập các tham số mặc định về ngôn ngữ, múi giờ và tiền tệ cho tài khoản mới.',
            'priority' => 'LOW', 'points' => 1, 'assignee' => $dev2['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 345, 'type' => 'TESTCASE', 'component' => 'BILLING', 'status' => 'TO_DO',
            'summary' => 'Kiểm thử xác thực OTP thanh toán thẻ tín dụng 3D-Secure',
            'desc' => 'Đảm bảo giao dịch thẻ visa/mastercard gửi mã OTP chính xác và xác thực thành công.',
            'priority' => 'HIGH', 'points' => 2, 'assignee' => $tester['id'], 'sprint' => 'ACTIVE_SPRINT',
            'test_type' => 'REGRESSION',
            'test_steps' => "1. Chọn tour du lịch và vào thanh toán\n2. Nhập thông tin thẻ test Visa 4000...\n3. Nhập mã OTP '123456' trên màn hình xác thực\n4. Kiểm tra trang kết quả xác nhận",
            'test_expected_result' => 'Hệ thống báo trừ tiền thành công, mã giao dịch xuất hiện và gửi email vé điện tử.',
            'test_execution_status' => 'UNTESTED'
        ],

        // IN PROGRESS
        [
            'num' => 342, 'type' => 'STORY', 'component' => 'ACCOUNTS', 'status' => 'IN_PROGRESS',
            'summary' => 'Fast trip search',
            'desc' => 'Cơ chế tìm kiếm siêu tốc chuyến bay và tour du lịch với Elasticsearch và bộ nhớ đệm Redis.',
            'priority' => 'HIGHEST', 'points' => 8, 'assignee' => $dev1['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 336, 'type' => 'TASK', 'component' => 'BILLING', 'status' => 'IN_PROGRESS',
            'summary' => 'Affelite links integration - frontend',
            'desc' => 'Gắn mã theo dõi hoa hồng tiếp thị liên kết (affiliate) vào các liên kết giới thiệu tour.',
            'priority' => 'MEDIUM', 'points' => 3, 'assignee' => $adminUser['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 341, 'type' => 'BUG', 'component' => 'FORMS', 'status' => 'IN_PROGRESS',
            'summary' => 'Shopping cart purchasing error - quick fix required',
            'desc' => 'Nút giỏ hàng bị đứng khi người dùng bấm thanh toán 2 lần liên tiếp.',
            'priority' => 'HIGHEST', 'points' => 2, 'assignee' => $dev2['id'], 'sprint' => 'ACTIVE_SPRINT',
            'bug_severity' => 'CRITICAL',
            'steps_to_reproduce' => "1. Thêm 1 sản phẩm vào giỏ\n2. Bấm nút 'Đặt tour ngay' 2 lần liên tục nhanh\n3. Trình duyệt hiển thị vòng xoay vô tận",
            'actual_result' => 'Giao dịch bị lock và hiển thị lỗi 409 Conflict',
            'expected_result' => 'Nút cần tự động vô hiệu hóa sau 1 lần bấm (Debounce), chỉ gửi 1 request lên server.',
            'environment' => 'Chrome 122 Windows 11 & Safari iOS 17'
        ],

        // IN REVIEW
        [
            'num' => 367, 'type' => 'STORY', 'component' => 'ACCOUNTS', 'status' => 'IN_REVIEW',
            'summary' => 'Revise and streamline booking flow',
            'desc' => 'Rút ngắn quy trình đặt vé từ 5 bước xuống còn 3 bước để tăng tỷ lệ chuyển đổi đơn hàng.',
            'priority' => 'HIGH', 'points' => 5, 'assignee' => $dev2['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 358, 'type' => 'TASK', 'component' => 'ACCOUNTS', 'status' => 'IN_REVIEW',
            'summary' => 'Travel suggestion experiments',
            'desc' => 'Thử nghiệm thuật toán gợi ý hành trình du lịch phù hợp dựa trên sở thích cá nhân.',
            'priority' => 'MEDIUM', 'points' => 3, 'assignee' => $dev1['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 354, 'type' => 'TASK', 'component' => 'ACCOUNTS', 'status' => 'IN_REVIEW',
            'summary' => 'Ongoing customer satisfaction',
            'desc' => 'Khảo sát CSAT tự động gửi sau khi hoàn thành chuyến đi để thu thập ý kiến khách hàng.',
            'priority' => 'LOW', 'points' => 2, 'assignee' => $adminUser['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 351, 'type' => 'TASK', 'component' => 'FEEDBACK', 'status' => 'IN_REVIEW',
            'summary' => 'Planet Taxi Device exploration & research',
            'desc' => 'Nghiên cứu tích hợp API của các đối tác vận tải đưa đón khách từ sân bay về khách sạn.',
            'priority' => 'MEDIUM', 'points' => 3, 'assignee' => $dev2['id'], 'sprint' => 'ACTIVE_SPRINT'
        ],
        [
            'num' => 352, 'type' => 'TESTCASE', 'component' => 'ACCOUNTS', 'status' => 'IN_REVIEW',
            'summary' => 'Kiểm tra độ tương thích responsive trên Safari iOS và Chrome Android',
            'desc' => 'Kiểm thử toàn diện các nút bấm, menu điều hướng và thanh tìm kiếm trên di động.',
            'priority' => 'MEDIUM', 'points' => 2, 'assignee' => $tester['id'], 'sprint' => 'ACTIVE_SPRINT',
            'test_type' => 'MANUAL',
            'test_steps' => "1. Mở trang chủ trên iPhone Safari\n2. Mở thanh menu và chọn 'Dự án'\n3. Kiểm tra khung hiển thị không bị tràn viền ngang",
            'test_expected_result' => 'Giao diện co giãn hoàn hảo, chữ và icon hiển thị sắc nét không lỗi layout.',
            'test_execution_status' => 'PASSED'
        ],

        // DONE
        [
            'num' => 348, 'type' => 'BUG', 'component' => 'BILLING', 'status' => 'DONE',
            'summary' => 'High outage: Software bug fix - BG Web-store app crashing',
            'desc' => 'Sửa lỗi sập ứng dụng web khi tải danh sách voucher khuyến mãi vào giờ cao điểm.',
            'priority' => 'HIGHEST', 'points' => 5, 'assignee' => $adminUser['id'], 'sprint' => 'ACTIVE_SPRINT',
            'bug_severity' => 'BLOCKER',
            'steps_to_reproduce' => 'Truy cập tab Voucher lúc có hơn 500 người dùng trực tuyến.',
            'actual_result' => 'Database connection pool cạn kiệt, trả về lỗi 500',
            'expected_result' => 'Đã cấu hình connection pool và lưu cache voucher 5 phút.',
            'environment' => 'Production Server Ubuntu 22.04'
        ],
        [
            'num' => 349, 'type' => 'BUG', 'component' => 'FORMS', 'status' => 'DONE',
            'summary' => 'Web-store purchasing performance issue fix',
            'desc' => 'Tối ưu truy vấn SQL tính tổng giá tiền và thuế cho giỏ hàng giảm thời gian tải từ 2.4s xuống 150ms.',
            'priority' => 'HIGH', 'points' => 3, 'assignee' => $dev1['id'], 'sprint' => 'ACTIVE_SPRINT',
            'bug_severity' => 'MAJOR',
            'steps_to_reproduce' => 'Giỏ hàng có nhiều hơn 5 dịch vụ đi kèm.',
            'actual_result' => 'Thời gian phản hồi chậm hơn 2.5s',
            'expected_result' => 'Đã thêm index và viết lại câu lệnh JOIN, tốc độ dưới 200ms.',
            'environment' => 'Staging & Production'
        ],
        [
            'num' => 343, 'type' => 'TESTCASE', 'component' => 'ACCOUNTS', 'status' => 'DONE',
            'summary' => 'Kiểm thử đăng nhập một chạm Single Sign-On (SSO)',
            'desc' => 'Xác thực luồng đăng nhập bằng tài khoản nội bộ Google Workspace và Microsoft 365.',
            'priority' => 'MEDIUM', 'points' => 2, 'assignee' => $tester['id'], 'sprint' => 'ACTIVE_SPRINT',
            'test_type' => 'SMOKE',
            'test_steps' => "1. Bấm 'Đăng nhập với Google'\n2. Chọn email công ty @corp.internal\n3. Chấp nhận quyền cơ bản",
            'test_expected_result' => 'Hệ thống tự động đăng nhập và chuyển hướng thẳng vào Dashboard người dùng.',
            'test_execution_status' => 'PASSED'
        ],

        // BACKLOG ITEMS
        [
            'num' => 370, 'type' => 'STORY', 'component' => 'BILLING', 'status' => 'TO_DO',
            'summary' => 'Hỗ trợ thanh toán qua Apple Pay và Google Wallet',
            'desc' => 'Cung cấp phương thức thanh toán không chạm cho ứng dụng web di động.',
            'priority' => 'HIGH', 'points' => 5, 'assignee' => $dev1['id'], 'sprint' => 'BACKLOG'
        ],
        [
            'num' => 371, 'type' => 'TASK', 'component' => 'SECURITY', 'status' => 'TO_DO',
            'summary' => 'Nâng cấp bảo mật SSL/TLS và cấu hình HSTS Preload',
            'desc' => 'Cập nhật chứng chỉ số TLS 1.3 và bật chính sách bảo mật HTTP Strict Transport Security.',
            'priority' => 'HIGHEST', 'points' => 3, 'assignee' => $adminUser['id'], 'sprint' => 'BACKLOG'
        ],
        [
            'num' => 372, 'type' => 'BUG', 'component' => 'ACCOUNTS', 'status' => 'TO_DO',
            'summary' => 'Avatar người dùng hiển thị méo hình trên màn hình Retina',
            'desc' => 'Ảnh đại diện người dùng khi upload tỉ lệ 16:9 bị bóp méo thành hình tròn không đều.',
            'priority' => 'LOW', 'points' => 1, 'assignee' => $dev2['id'], 'sprint' => 'BACKLOG',
            'bug_severity' => 'MINOR',
            'steps_to_reproduce' => 'Tải lên ảnh 1200x800 tại trang hồ sơ cá nhân.',
            'actual_result' => 'Ảnh bị dẹt theo chiều dọc',
            'expected_result' => 'Áp dụng CSS object-fit: cover để giữ nguyên tỷ lệ ảnh.',
            'environment' => 'All browsers'
        ],
        [
            'num' => 373, 'type' => 'TESTCASE', 'component' => 'FORMS', 'status' => 'TO_DO',
            'summary' => 'Kiểm thử tải trọng Form đặt chỗ với 1.000 yêu cầu/giây (Stress Test)',
            'desc' => 'Sử dụng k6 hoặc Apache JMeter mô phỏng tải truy cập đồng thời.',
            'priority' => 'HIGH', 'points' => 5, 'assignee' => $tester['id'], 'sprint' => 'BACKLOG',
            'test_type' => 'AUTOMATED',
            'test_steps' => "1. Thiết lập k6 test script với 200 Virtual Users (VU)\n2. Chạy kịch bản gửi đơn hàng liên tục trong 5 phút\n3. Giám sát CPU/RAM máy chủ qua JumpServer",
            'test_expected_result' => 'Tỷ lệ lỗi dưới 0.1%, độ trễ phản hồi p95 dưới 500ms.',
            'test_execution_status' => 'UNTESTED'
        ],
        [
            'num' => 374, 'type' => 'TASK', 'component' => 'FEEDBACK', 'status' => 'TO_DO',
            'summary' => 'Tích hợp chatbot tự động giải đáp thắc mắc dịch vụ 24/7',
            'desc' => 'Kết nối API trợ lý AI trả lời câu hỏi thường gặp về thủ tục visa và lịch bay.',
            'priority' => 'MEDIUM', 'points' => 8, 'assignee' => $dev1['id'], 'sprint' => 'BACKLOG'
        ]
    ];

    $insertIssueStmt = $pdo->prepare("
        INSERT INTO project_issues (
            project_id, issue_key, issue_number, issue_type, summary, description,
            status, priority, reporter_id, assignee_id, sprint_status, component, story_points,
            test_type, test_steps, test_expected_result, test_execution_status,
            bug_severity, steps_to_reproduce, actual_result, expected_result, environment, created_at
        ) VALUES (
            :pid, :key, :num, :type, :summary, :desc,
            :status, :priority, :reporter, :assignee, :sprint, :component, :points,
            :test_type, :test_steps, :test_expected_result, :test_execution_status,
            :bug_severity, :steps_to_reproduce, :actual_result, :expected_result, :environment, NOW()
        )
    ");

    $pdo->prepare("DELETE FROM project_issues WHERE project_id = :pid")->execute(['pid' => $projectId]);

    $idx = 0;
    foreach ($seedIssues as $iss) {
        $idx++;
        $issueKey = "NUC-" . $iss['num'];

        $insertIssueStmt->execute([
            'pid'                  => $projectId,
            'key'                  => $issueKey,
            'num'                  => $iss['num'],
            'type'                 => $iss['type'],
            'summary'              => $iss['summary'],
            'desc'                 => $iss['desc'],
            'status'               => $iss['status'],
            'priority'             => $iss['priority'],
            'reporter'             => $adminUser['id'],
            'assignee'             => $iss['assignee'],
            'sprint'               => $iss['sprint'],
            'component'            => $iss['component'],
            'points'               => $iss['points'],
            'test_type'            => $iss['test_type'] ?? 'MANUAL',
            'test_steps'           => $iss['test_steps'] ?? null,
            'test_expected_result' => $iss['test_expected_result'] ?? null,
            'test_execution_status'=> $iss['test_execution_status'] ?? 'UNTESTED',
            'bug_severity'         => $iss['bug_severity'] ?? 'MAJOR',
            'steps_to_reproduce'   => $iss['steps_to_reproduce'] ?? null,
            'actual_result'        => $iss['actual_result'] ?? null,
            'expected_result'      => $iss['expected_result'] ?? null,
            'environment'          => $iss['environment'] ?? null
        ]);
    }
    echo "[OK] Successfully seeded " . count($seedIssues) . " issues into project 'Beyond Gravity'.\n";
} else {
    echo "[INFO] Project already has {$issueCount} issues.\n";
}

echo "========================================\n";
echo "JIRA PROJECT MIGRATION COMPLETED SUCCESSFULLY!\n";
echo "========================================\n";
