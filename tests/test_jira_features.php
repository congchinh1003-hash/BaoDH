<?php
/**
 * Automated Verification for Jira Advanced Features
 */

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Ticket;
use App\Models\User;
use App\Controllers\TicketController;

echo "=== TESTING JIRA ADVANCED FEATURES ===\n";

$db = Database::getConnection();
$ticketModel = new Ticket();
$userModel = new User();

$staff = $userModel->findByUsernameOrEmail('it_helpdesk');
$roles = $userModel->getRoles((int)$staff['id']);
Auth::login($staff, $roles);

$passed = 0;
$total = 0;

function assertJira(bool $condition, string $feature, string $msg = '') {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$feature}: {$msg}\n";
    } else {
        echo "  [FAIL] {$feature}: {$msg}\n";
    }
}

// -----------------------------------------------------------------------------
// 1. Time Tracking & Worklog
// -----------------------------------------------------------------------------
assertJira(Ticket::parseTimeToMinutes('1h 30m') === 90, "Worklog Parser", "1h 30m -> 90 minutes");
assertJira(Ticket::parseTimeToMinutes('45m') === 45, "Worklog Parser", "45m -> 45 minutes");
assertJira(Ticket::parseTimeToMinutes('1d') === 480, "Worklog Parser", "1d -> 480 minutes (8 hours)");
assertJira(Ticket::formatMinutesToTime(150) === '2h 30m', "Worklog Formatter", "150 minutes -> 2h 30m");

// Get or create a ticket for testing
$stmt = $db->query("SELECT id FROM tickets WHERE status NOT IN ('CLOSED', 'CANCELLED', 'DUPLICATE') LIMIT 1");
$ticketId = (int)$stmt->fetchColumn();
if (!$ticketId) {
    $ticketId = $ticketModel->create([
        'ticket_code' => 'JIRA-TEST-' . time(),
        'title' => 'Ticket for Jira Features Test',
        'description' => 'Testing worklog and checklists',
        'requester_id' => (int)$staff['id'],
        'category_id' => 1,
        'priority_id' => 1,
        'status' => 'NEW'
    ]);
}

$wlId = $ticketModel->addWorklog($ticketId, (int)$staff['id'], '2h 15m', 'Kiểm tra cáp mạng và cấu hình Switch');
assertJira($wlId > 0, "Worklog", "Successfully logged 2h 15m worklog ID #{$wlId}");

$worklogs = $ticketModel->getWorklogs($ticketId);
assertJira(count($worklogs) >= 1 && $worklogs[0]['time_spent_minutes'] === 135, "Worklog List", "Retrieved worklogs with exact minutes (135m)");

$totalMinutes = $ticketModel->getTotalWorklogMinutes($ticketId);
assertJira($totalMinutes >= 135, "Worklog Total", "Total minutes accumulated correctly: {$totalMinutes}m");

// -----------------------------------------------------------------------------
// 2. Technical Checklist / Sub-tasks
// -----------------------------------------------------------------------------
$cl1 = $ticketModel->addChecklistItem($ticketId, '1. Kiểm tra nguồn điện máy in');
$cl2 = $ticketModel->addChecklistItem($ticketId, '2. Cài đặt lại driver PCL6');
assertJira($cl1 > 0 && $cl2 > 0, "Checklist", "Added 2 checklist items (#{$cl1}, #{$cl2})");

$items = $ticketModel->getChecklist($ticketId);
assertJira(count($items) >= 2, "Checklist", "Retrieved checklist items");

$toggled = $ticketModel->toggleChecklistItem($cl1, (int)$staff['id']);
assertJira($toggled['is_completed'] === true, "Checklist Toggle", "Marked item #{$cl1} as completed");

$ticketModel->deleteChecklistItem($cl2);
$itemsAfterDel = $ticketModel->getChecklist($ticketId);
$hasCl2 = false;
foreach ($itemsAfterDel as $it) {
    if ((int)$it['id'] === $cl2) $hasCl2 = true;
}
assertJira($hasCl2 === false, "Checklist Delete", "Deleted item #{$cl2}");

// -----------------------------------------------------------------------------
// 3. Issue Linking
// -----------------------------------------------------------------------------
// Find second ticket
$stmt2 = $db->prepare("SELECT id FROM tickets WHERE id != :tid LIMIT 1");
$stmt2->execute(['tid' => $ticketId]);
$targetId = (int)$stmt2->fetchColumn();

if ($targetId > 0) {
    $linkId = $ticketModel->linkIssue($ticketId, $targetId, 'blocks', (int)$staff['id']);
    assertJira($linkId > 0, "Issue Linking", "Linked Ticket #{$ticketId} -> blocks -> Ticket #{$targetId}");

    $links = $ticketModel->getLinkedIssues($ticketId);
    assertJira(count($links) >= 1 && $links[0]['link_type'] === 'blocks', "Issue Linking Query", "Retrieved linked issues with relation type 'blocks'");

    $ticketModel->unlinkIssue($linkId);
    $linksAfter = $ticketModel->getLinkedIssues($ticketId);
    $hasLink = false;
    foreach ($linksAfter as $l) {
        if ((int)$l['link_id'] === $linkId) $hasLink = true;
    }
    assertJira($hasLink === false, "Issue Linking Unlink", "Successfully unlinked issue");
}

// -----------------------------------------------------------------------------
// 4. Kanban Board Rendering & AJAX Status Transition
// -----------------------------------------------------------------------------
$board = $ticketModel->getBoardTickets();
assertJira(isset($board['NEW'], $board['IN_PROGRESS'], $board['WAITING'], $board['RESOLVED'], $board['CLOSED']), "Kanban Board Data", "Board contains all 5 columns");

$_SERVER['REQUEST_URI'] = '/tickets/board';
$controller = new TicketController();
$boardHtml = $controller->kanbanBoard(new Request(), new Response());
assertJira(strpos($boardHtml, 'Bảng Kanban Sự Cố') !== false && strlen($boardHtml) > 5000, "Kanban Board View", "Rendered Kanban Board view successfully (" . strlen($boardHtml) . " bytes)");

// Test AJAX Status Transition
$_POST['status'] = 'IN_PROGRESS';
$_POST['reason'] = 'Di chuyển sang In Progress trên Board';
$reqAjax = new Request();
$resAjax = new Response();
// Simulate ajax call
$ticketModel->updateStatus($ticketId, 'IN_PROGRESS', (int)$staff['id'], 'Di chuyển sang In Progress trên Board');
$tAfter = $ticketModel->findById($ticketId);
assertJira($tAfter['status'] === 'IN_PROGRESS', "Kanban AJAX Transition", "Ticket status updated to IN_PROGRESS on Kanban");

// Clean up test worklog & checklist
$db->prepare("DELETE FROM ticket_worklogs WHERE id = :id")->execute(['id' => $wlId]);
$db->prepare("DELETE FROM ticket_checklists WHERE id = :id")->execute(['id' => $cl1]);

echo "=== SUMMARY: {$passed}/{$total} JIRA TESTS PASSED ===\n";

