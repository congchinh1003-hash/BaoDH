<?php
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use App\Models\ProjectIssue;
use App\Models\Project;

echo "=======================================================\n";
echo " TEST SUITE: PROFILE, GROUP BY, ALM CREATE & 30 ACCOUNTS\n";
echo "=======================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $msg): void {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$msg}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$msg}\n";
        $failCount++;
    }
}

$db = Database::getConnection();

// 1. Test Session CSRF Token Alias
$token1 = Session::csrfToken();
$token2 = Session::getCsrfToken();
assertTest(!empty($token1), "Session::csrfToken() generates valid token");
assertTest($token1 === $token2, "Session::getCsrfToken() is identical alias of csrfToken()");

// 2. Test create.php file doesn't crash on csrf
$createFileContent = file_get_contents(__DIR__ . '/../app/Views/projects/create.php');
assertTest(str_contains($createFileContent, 'Session::csrfToken()'), "projects/create.php uses valid Session::csrfToken()");

// 3. Test Users columns seat_location and job_title
$cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
assertTest(in_array('seat_location', $cols), "Users table contains 'seat_location' column");
assertTest(in_array('job_title', $cols), "Users table contains 'job_title' column");

// 4. Test 10 End-Users created
$endUsernames = [
    'ketoan_lan', 'ketoan_minh', 'ketoan_thao', 'ketoan_truong', 'ketoan_huyen',
    'kinhdoanh_nam', 'kinhdoanh_nga', 'kinhdoanh_quang', 'kinhdoanh_yen', 'kinhdoanh_dat'
];
$stmtUser = $db->prepare("SELECT * FROM users WHERE username = :u");
$allEndUsersFound = true;
foreach ($endUsernames as $uName) {
    $stmtUser->execute(['u' => $uName]);
    $uRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if (!$uRow || empty($uRow['seat_location']) || empty($uRow['job_title'])) {
        $allEndUsersFound = false;
        break;
    }
}
assertTest($allEndUsersFound, "All 10 End-User accounts exist with full seat_location & job_title");

// 5. Test password verify for seeded user
$stmtUser->execute(['u' => 'ketoan_lan']);
$lan = $stmtUser->fetch(PDO::FETCH_ASSOC);
assertTest(password_verify('123456', $lan['password_hash']), "Password '123456' verifies successfully for seeded user");
assertTest($lan['department'] === 'Phòng Kế Toán', "ketoan_lan department is correctly 'Phòng Kế Toán'");

// 6. Test 20 IT accounts created
$itUsernames = [
    'it_cio', 'it_director', 'it_mgr_helpdesk', 'it_mgr_network', 'it_mgr_system',
    'it_mgr_dev', 'it_mgr_qa', 'it_hd_tuan', 'it_hd_linh', 'it_hd_viet',
    'it_net_kien', 'it_net_son', 'it_sys_duc', 'it_sys_thinh', 'it_dev_phong',
    'it_dev_mai', 'it_qa_vy', 'it_qa_hieu', 'it_sec_quan', 'it_db_khang'
];
$allItFound = true;
foreach ($itUsernames as $uName) {
    $stmtUser->execute(['u' => $uName]);
    $uRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if (!$uRow || empty($uRow['seat_location']) || empty($uRow['job_title'])) {
        $allItFound = false;
        break;
    }
}
assertTest($allItFound, "All 20 IT accounts exist with complete profile data");

// 7. Test IT Roles & Group memberships
$stmtUser->execute(['u' => 'it_cio']);
$cio = $stmtUser->fetch(PDO::FETCH_ASSOC);
$userModel = new User();
$cioRoles = $userModel->getRoles((int)$cio['id']);
assertTest(in_array('ADMIN', $cioRoles), "CIO account has ADMIN role");

$stmtUser->execute(['u' => 'it_mgr_helpdesk']);
$hdMgr = $stmtUser->fetch(PDO::FETCH_ASSOC);
$hdMgrRoles = $userModel->getRoles((int)$hdMgr['id']);
assertTest(in_array('IT_MANAGER', $hdMgrRoles), "it_mgr_helpdesk has IT_MANAGER role");

$stmtUser->execute(['u' => 'it_hd_tuan']);
$hdTuan = $stmtUser->fetch(PDO::FETCH_ASSOC);
$hdTuanRoles = $userModel->getRoles((int)$hdTuan['id']);
assertTest(in_array('IT_STAFF', $hdTuanRoles), "it_hd_tuan has IT_STAFF role");

// 8. Test Project Membership for TaskFlow
$stmtPrjMember = $db->prepare("SELECT COUNT(*) FROM project_members WHERE project_id = 1 AND user_id = :uid");
$stmtPrjMember->execute(['uid' => $cio['id']]);
assertTest((int)$stmtPrjMember->fetchColumn() > 0, "CIO is member of Project 1 Beyond Gravity");

// 9. Test Kanban Board markup for Group By
$boardContent = file_get_contents(__DIR__ . '/../app/Views/projects/board.php');
assertTest(str_contains($boardContent, 'id="groupByDropdownBtn"'), "board.php has Group By dropdown button");
assertTest(str_contains($boardContent, "setGroupBy('assignee')"), "board.php has setGroupBy('assignee')");
assertTest(str_contains($boardContent, "setGroupBy('epic')"), "board.php has setGroupBy('epic')");
assertTest(str_contains($boardContent, "setGroupBy('type')"), "board.php has setGroupBy('type')");
assertTest(str_contains($boardContent, "setGroupBy('priority')"), "board.php has setGroupBy('priority')");
assertTest(str_contains($boardContent, 'id="swimlanesContainer"'), "board.php has dynamic swimlanesContainer");
assertTest(str_contains($boardContent, 'function toggleSwimlane'), "board.php implements toggleSwimlane");

// 10. Test CSS for Swimlanes
$cssContent = file_get_contents(__DIR__ . '/../css/style.css');
assertTest(str_contains($cssContent, '.jira-swimlane'), "css/style.css contains .jira-swimlane styles");
assertTest(str_contains($cssContent, '.jira-swimlane-header'), "css/style.css contains .jira-swimlane-header styles");

// 11. Test Image 2 ALM Work Item creation view & modal
$modalContent = file_get_contents(__DIR__ . '/../app/Views/layouts/main.php');
assertTest(str_contains($modalContent, 'modal-xl modal-dialog-scrollable'), "Quick Create modal is modal-xl scrollable");
assertTest(str_contains($modalContent, 'placeholder="Enter title"'), "Quick Create modal has large 'Enter title' input");
assertTest(str_contains($modalContent, 'Original Estimate'), "Quick Create modal has Original Estimate effort");
assertTest(str_contains($modalContent, 'qc_parent_wrap'), "Quick Create modal has Parent Work Item hierarchy section");

$issueCreateContent = file_get_contents(__DIR__ . '/../app/Views/projects/issue_create.php');
assertTest(str_contains($issueCreateContent, 'New Work Item'), "issue_create.php has New Work Item title");
assertTest(str_contains($issueCreateContent, 'placeholder="Enter title"'), "issue_create.php has large 'Enter title' input");
assertTest(str_contains($issueCreateContent, 'Effort (Hours)'), "issue_create.php has Effort (Hours) block");
assertTest(str_contains($issueCreateContent, 'Related Work'), "issue_create.php has Related Work block");

// 12. Test Ticket Create Auto-Fill
$ticketCreateContent = file_get_contents(__DIR__ . '/../app/Views/tickets/create.php');
assertTest(str_contains($ticketCreateContent, '$currentUser[\'seat_location\']'), "tickets/create.php auto-fills seat_location from profile");
assertTest(str_contains($ticketCreateContent, '$currentUser[\'phone\']'), "tickets/create.php auto-fills phone from profile");
assertTest(str_contains($ticketCreateContent, 'Chỗ ngồi profile:'), "tickets/create.php displays user profile info card");

// 13. Test Profile Controller & View
$profileContent = file_get_contents(__DIR__ . '/../app/Views/user/profile.php');
assertTest(str_contains($profileContent, 'Hồ sơ cá nhân'), "profile.php has profile title");
assertTest(str_contains($profileContent, 'VỊ TRÍ CHỖ NGỒI (SEAT / OFFICE LOCATION)'), "profile.php allows viewing & editing seat_location");
assertTest(str_contains($profileContent, 'CHỨC DANH / CHỨC VỤ'), "profile.php allows viewing & editing job_title");

// 14. Test Admin Users View
$adminUsersContent = file_get_contents(__DIR__ . '/../app/Views/admin/users.php');
assertTest(str_contains($adminUsersContent, 'editSeatLocation'), "admin/users.php has editSeatLocation input");
assertTest(str_contains($adminUsersContent, 'editJobTitle'), "admin/users.php has editJobTitle input");

echo "\n=======================================================\n";
echo " TOTAL TESTS: " . ($passCount + $failCount) . " | PASSED: {$passCount} | FAILED: {$failCount}\n";
echo "=======================================================\n";

if ($failCount === 0) {
    echo " ALL ENHANCEMENTS AND SEED CHECKS PASSED 100%!\n";
    exit(0);
} else {
    echo " SOME TESTS FAILED!\n";
    exit(1);
}

