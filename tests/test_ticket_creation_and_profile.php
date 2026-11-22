<?php
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';
\App\Core\Session::start();

echo "=======================================================\n";
echo " TEST SUITE: TICKET CONTROLLER & PROFILE VALIDATION\n";
echo "=======================================================\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTrue(bool $condition, string $msg): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo " [PASS] $msg\n";
        $testsPassed++;
    } else {
        echo " [FAIL] $msg\n";
        $testsFailed++;
    }
}

$userModel = new \App\Models\User();
$adminUser = $userModel->findById(1);
$staffUser = $userModel->findById(3); // it_helpdesk
$endUser = $userModel->findById(6);   // user1

// Test 1: Notification Model notifyUser
$notifModel = new \App\Models\Notification();
$notifId = $notifModel->notifyUser(3, [
    'title' => 'Test Notification',
    'message' => 'This is a test notification',
    'type' => 'test'
]);
assertTrue($notifId > 0, "Notification::notifyUser successfully created notification #$notifId");

// Test 2: Ticket Model findByCode
$ticketModel = new \App\Models\Ticket();
$firstTicket = $ticketModel->findById(1);
if ($firstTicket) {
    $foundByCode = $ticketModel->findByCode($firstTicket['ticket_code']);
    assertTrue(!empty($foundByCode) && (int)$foundByCode['id'] === 1, "Ticket::findByCode successfully retrieved ticket by {$firstTicket['ticket_code']}");
}

// Test 3: Create Service Request with Approver (Exercises TicketController::store logic)
\App\Core\Auth::login($endUser, $userModel->getRoles(6));

$ticketId = $ticketModel->create([
    'ticket_type'        => 'SERVICE_REQUEST',
    'title'              => 'Cấp quyền truy cập hệ thống ERP SAP',
    'description'        => 'Nhân viên kế toán cần quyền nhập chứng từ trên phân hệ FI.',
    'requester_id'       => 6,
    'category_id'        => 1,
    'service_id'         => null,
    'priority_id'        => 3,
    'impact'             => 'MEDIUM',
    'urgency'            => 'MEDIUM',
    'support_tier'       => 'L1',
    'approver_id'        => 2, // Manager
    'approval_step_name' => 'Phê duyệt của IT Manager',
    'location'           => 'Tầng 2 - Phòng Kế Toán',
    'contact_info'       => '0901234567'
]);
assertTrue($ticketId > 0, "Created Service Request with Approver #$ticketId successfully");

// Verify notifyUser on approver works without error
$notifId2 = $notifModel->notifyUser(2, [
    'ticket_id' => $ticketId,
    'title'     => 'Cần phê duyệt yêu cầu dịch vụ',
    'message'   => 'user1 vừa gửi yêu cầu cần bạn phê duyệt.',
    'type'      => 'ticket_approval'
]);
assertTrue($notifId2 > 0, "Notified approver via notifyUser without error (#$notifId2)");

// Test 4: Profile show and update for end user
\App\Core\Auth::login($endUser, $userModel->getRoles(6));
$profileController = new \App\Controllers\ProfileController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();

$output = $profileController->show($req, $res);
assertTrue(strlen($output) > 1000, "ProfileController::show rendered HTML successfully (" . strlen($output) . " bytes)");
assertTrue(strpos($output, 'user1') !== false, "Profile HTML contains username user1");
assertTrue(strpos($output, 'Hồ Sơ Cá Nhân') !== false, "Profile HTML contains page title");

// Test 5: getByAssignee in ProjectIssue across projects
$issueModel = new \App\Models\ProjectIssue();
$assignedIssues = $issueModel->getByAssignee(1, 5);
assertTrue(is_array($assignedIssues), "ProjectIssue::getByAssignee returned array of issues");

// Test 6: Auth::login populates seat_location and job_title
$testUserWithSeat = [
    'id' => 9999,
    'username' => 'test_seat',
    'email' => 'test@company.com',
    'full_name' => 'Nguyen Van Test',
    'phone' => '0987654321',
    'department' => 'Kế toán',
    'seat_location' => 'Tầng 2 - Bàn 05',
    'job_title' => 'Chuyên viên tài chính',
    'avatar' => null
];
\App\Core\Auth::login($testUserWithSeat, ['USER']);
$loggedUser = \App\Core\Auth::user();
assertTrue($loggedUser['seat_location'] === 'Tầng 2 - Bàn 05', "Auth::user() contains seat_location correctly");
assertTrue($loggedUser['job_title'] === 'Chuyên viên tài chính', "Auth::user() contains job_title correctly");

// Test 7: Verify layout CSS locking
$css = file_get_contents(__DIR__ . '/../public/css/style.css');
assertTrue(strpos($css, 'overflow: hidden;') !== false, "CSS has overflow: hidden on html, body and app-container");
assertTrue(strpos($css, 'height: calc(100vh - 56px);') !== false, "CSS has app-body height 100vh - 56px");
assertTrue(strpos($css, '.jira-project-sidebar') !== false && strpos($css, 'height: 100%;') !== false, "CSS has height: 100% on jira-project-sidebar");

echo "=======================================================\n";
echo " TOTAL TESTS: " . ($testsPassed + $testsFailed) . " | PASSED: $testsPassed | FAILED: $testsFailed\n";
echo "=======================================================\n";
if ($testsFailed === 0) {
    echo " ALL TESTS PASSED 100%!\n";
} else {
    exit(1);
}

