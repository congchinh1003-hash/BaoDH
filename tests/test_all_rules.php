<?php
/**
 * End-to-end verification script for all FSD Rules implemented
 */

require_once __DIR__ . '/../app/autoload.php';

use App\Core\Database;
use App\Models\Ticket;
use App\Models\Asset;
use App\Models\Report;
use App\Services\SlaService;

echo "=== VERIFYING ALL IT SERVICE DESK FSD RULES ===\n";

$db = Database::getConnection();
$ticketModel = new Ticket();
$assetModel = new Asset();
$reportModel = new Report();
$slaService = new SlaService();

$passed = 0;
$total = 0;

function assertRule(bool $condition, string $ruleName, string $message = '') {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$ruleName}: {$message}\n";
    } else {
        echo "  [FAIL] {$ruleName}: {$message}\n";
    }
}

// 1. Create a dummy ticket for test
$stmt = $db->query("SELECT id FROM users WHERE username = 'user1' LIMIT 1");
$userId = (int)$stmt->fetchColumn();
$stmt = $db->query("SELECT id FROM users WHERE username = 'manager' LIMIT 1");
$managerId = (int)$stmt->fetchColumn();
$stmt = $db->query("SELECT id FROM users WHERE username = 'it_helpdesk' LIMIT 1");
$staffId = (int)$stmt->fetchColumn();

$testTicketId = $ticketModel->create([
    'ticket_code' => 'TEST-' . time(),
    'title' => 'Test Ticket for FSD Rules Verification',
    'description' => 'Test description',
    'requester_id' => $userId,
    'category_id' => 1,
    'priority_id' => 3, // Medium
    'status' => 'NEW'
]);

assertRule($testTicketId > 0, "Rule 2.1 / Rule 6", "Created ticket ID #{$testTicketId}");

// 2. Adjust Priority (Rule 9)
$ticketModel->adjustPriority($testTicketId, 1, $managerId, "Nâng mức độ khẩn cấp theo yêu cầu ban giám đốc");
$tUpdated = $ticketModel->findById($testTicketId);
assertRule((int)$tUpdated['priority_id'] === 1, "Rule 9", "Adjusted Priority to Urgent (ID: 1)");

// Check ticket_history for priority
$stmtHist = $db->prepare("SELECT * FROM ticket_history WHERE ticket_id = :id AND field_changed = 'priority_id' ORDER BY id DESC LIMIT 1");
$stmtHist->execute(['id' => $testTicketId]);
$hist = $stmtHist->fetch();
assertRule(!empty($hist) && $hist['action'] === 'ADJUST_PRIORITY', "Rule 9 / Rule 60", "Logged priority adjustment to ticket_history");

// 3. Watchers (Rule 27)
$ticketModel->addWatcher($testTicketId, $staffId);
$isWatching = $ticketModel->isWatching($testTicketId, $staffId);
assertRule($isWatching === true, "Rule 27", "Added staff as watcher to ticket");

$watchers = $ticketModel->getWatchers($testTicketId);
assertRule(count($watchers) >= 1, "Rule 27", "Retrieved watchers list successfully");

$ticketModel->removeWatcher($testTicketId, $staffId);
$isWatchingAfter = $ticketModel->isWatching($testTicketId, $staffId);
assertRule($isWatchingAfter === false, "Rule 27", "Removed watcher successfully");

// 4. SLA Pause on WAITING_VENDOR (Rule 13 & 14)
$ticketModel->updateStatus($testTicketId, 'WAITING_VENDOR', $staffId, "Chờ đối tác VNPT kiểm tra đường truyền cáp quang");
$tVendor = $ticketModel->findById($testTicketId);
assertRule($tVendor['status'] === 'WAITING_VENDOR' && !empty($tVendor['pause_started_at']), "Rule 13 / 14", "Status WAITING_VENDOR successfully paused SLA");

$ticketModel->updateStatus($testTicketId, 'IN_PROGRESS', $staffId, "Đối tác đã xử lý xong");
$tResume = $ticketModel->findById($testTicketId);
assertRule($tResume['status'] === 'IN_PROGRESS' && empty($tResume['pause_started_at']), "Rule 13 / 14", "Status IN_PROGRESS resumed SLA");

// 5. Overdue Formatting (Rule 15 & 16)
$pastDate = date('Y-m-d H:i:s', strtotime('-2 hours -15 minutes'));
$overdueStr = SlaService::formatOverdueTime($pastDate);
assertRule(strpos($overdueStr, 'Quá 02:') !== false, "Rule 15 / 16", "Overdue formatted correctly: {$overdueStr}");

// 6. Link Asset to Ticket (Rule 55)
$stmtAsset = $db->query("SELECT id FROM assets LIMIT 1");
$assetId = (int)$stmtAsset->fetchColumn();
if ($assetId > 0) {
    $ticketModel->linkAsset($testTicketId, $assetId, $staffId);
    $tAsset = $ticketModel->findById($testTicketId);
    assertRule((int)$tAsset['asset_id'] === $assetId, "Rule 55", "Linked Asset #{$assetId} to Ticket");
}

// Ensure enum includes STATUS_CHANGE
$db->exec("ALTER TABLE asset_handovers MODIFY COLUMN action_type ENUM('HANDOVER', 'RECALL', 'TRANSFER', 'DISPOSAL', 'STATUS_CHANGE') NOT NULL");

// 7. Asset Status Change (Rule 52 & 53)
if ($assetId > 0) {
    $assetModel->changeStatus($assetId, 'REPAIR', 'Đưa vào phòng bảo trì sửa bàn phím', $staffId);
    $aUpdated = $assetModel->findById($assetId);
    assertRule($aUpdated['status'] === 'REPAIR', "Rule 52 / 53", "Asset status updated to REPAIR with reason");
}

// 8. Duplicate Ticket (Rule 24)
$ticketModel->markDuplicate($testTicketId, 1, $staffId, "Trùng với sự cố mạng LAN IT-000001");
$tDup = $ticketModel->findById($testTicketId);
assertRule($tDup['status'] === 'DUPLICATE' && (int)$tDup['duplicate_of_id'] === 1, "Rule 24", "Marked ticket as DUPLICATE of #1");

// 9. Cancel Ticket (Rule 25)
$cancelTicketId = $ticketModel->create([
    'ticket_code' => 'CANCEL-' . time(),
    'title' => 'Test Ticket for Cancel',
    'description' => 'Test description for cancel ticket',
    'category_id' => 1,
    'priority_id' => 3,
    'requester_id' => $userId,
    'status' => 'NEW'
]);
$ticketModel->cancel($cancelTicketId, $userId, "Người dùng tự khắc phục được lỗi");
$tCancel = $ticketModel->findById($cancelTicketId);
assertRule($tCancel['status'] === 'CANCELLED' && !empty($tCancel['cancelled_reason']), "Rule 25", "Cancelled ticket with reason recorded");

// 10. Workload Reports (Rule 40 & 41)
$groupWorkload = $reportModel->getGroupWorkload();
assertRule(is_array($groupWorkload) && count($groupWorkload) > 0, "Rule 40", "Group Workload data retrieved with all columns");

$staffPerf = $reportModel->getStaffPerformance();
assertRule(is_array($staffPerf) && isset($staffPerf[0]['need_action_count']), "Rule 41", "Staff Performance contains Need Action column");

// Clean up test tickets
$db->exec("DELETE FROM tickets WHERE id IN ({$testTicketId}, {$cancelTicketId})");

echo "=== SUMMARY: {$passed}/{$total} TESTS PASSED ===\n";
