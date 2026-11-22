<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Database;
use App\Models\Approval;
use App\Models\ChangeRequest;
use App\Models\Problem;
use App\Models\Ticket;

$db = (new Database())->getConnection();

echo "=======================================================\n";
echo " ITIL & ITSM ADVANCED SUITE AUTOMATED VERIFICATION TEST\n";
echo "=======================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $name, bool $condition, ?string $detail = null): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo " [PASS] {$name}\n";
    } else {
        $failCount++;
        echo " [FAIL] {$name}" . ($detail ? " - {$detail}" : "") . "\n";
    }
}

// -----------------------------------------------------------------------------
// TEST 1: ITIL Priority Matrix (Impact x Urgency)
// -----------------------------------------------------------------------------
echo "\n--- 1. Testing Impact x Urgency Priority Matrix ---\n";
assertTest("HIGH Impact x HIGH Urgency => URGENT (3)", Ticket::calculatePriorityFromMatrix('HIGH', 'HIGH') === 3);
assertTest("HIGH Impact x MEDIUM Urgency => HIGH (2)", Ticket::calculatePriorityFromMatrix('HIGH', 'MEDIUM') === 2);
assertTest("MEDIUM Impact x HIGH Urgency => HIGH (2)", Ticket::calculatePriorityFromMatrix('MEDIUM', 'HIGH') === 2);
assertTest("HIGH Impact x LOW Urgency => HIGH (2)", Ticket::calculatePriorityFromMatrix('HIGH', 'LOW') === 2);
assertTest("MEDIUM Impact x MEDIUM Urgency => NORMAL (1)", Ticket::calculatePriorityFromMatrix('MEDIUM', 'MEDIUM') === 1);
assertTest("LOW Impact x LOW Urgency => NORMAL (1)", Ticket::calculatePriorityFromMatrix('LOW', 'LOW') === 1);

// Find a user and category for testing
$userId = (int)$db->query("SELECT u.id FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.code = 'USER' LIMIT 1")->fetchColumn() ?: 1;
$staffId = (int)$db->query("SELECT u.id FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.code = 'IT_STAFF' LIMIT 1")->fetchColumn() ?: 2;
$managerId = (int)$db->query("SELECT u.id FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.code IN ('IT_MANAGER', 'ADMIN') LIMIT 1")->fetchColumn() ?: 3;
$catId = (int)$db->query("SELECT id FROM ticket_categories LIMIT 1")->fetchColumn() ?: 1;

// -----------------------------------------------------------------------------
// TEST 2: Incident Creation with Matrix Priority & Tier L1
// -----------------------------------------------------------------------------
echo "\n--- 2. Testing Incident Creation & Tier Escalation ---\n";
$ticketModel = new Ticket();
$incidentId = $ticketModel->create([
    'ticket_type'  => 'INCIDENT',
    'title'        => 'Test ITIL Incident: Server Outage in Production',
    'description'  => 'Cụm máy chủ Database bị mất kết nối, dịch vụ ERP gián đoạn toàn công ty.',
    'requester_id' => $userId,
    'category_id'  => $catId,
    'impact'       => 'HIGH',
    'urgency'      => 'HIGH',
    'support_tier' => 'L1'
]);

assertTest("Incident created with ID > 0", $incidentId > 0);
$incident = $ticketModel->findById($incidentId);
assertTest("Auto priority assigned as URGENT (3)", (int)$incident['priority_id'] === 3);
assertTest("Ticket type is INCIDENT", $incident['ticket_type'] === 'INCIDENT');
assertTest("Initial support tier is L1", $incident['support_tier'] === 'L1');

// Test Tier Escalation L1 -> L2 -> L3 -> VENDOR
$ticketModel->escalateTier($incidentId, 'L2', $staffId, 'Cần kỹ thuật chuyên sâu kiểm tra query slow log');
$tAfterL2 = $ticketModel->findById($incidentId);
assertTest("Tier escalated to L2", $tAfterL2['support_tier'] === 'L2');

$ticketModel->escalateTier($incidentId, 'L3', $staffId, 'Cần can thiệp kernel Linux & DBA expert');
$tAfterL3 = $ticketModel->findById($incidentId);
assertTest("Tier escalated to L3", $tAfterL3['support_tier'] === 'L3');

$ticketModel->escalateTier($incidentId, 'VENDOR', $staffId, 'Chuyển nhà thầu phần cứng Dell');
$tAfterVendor = $ticketModel->findById($incidentId);
assertTest("Tier escalated to VENDOR", $tAfterVendor['support_tier'] === 'VENDOR');
assertTest("Status auto-transitioned to WAITING_VENDOR", $tAfterVendor['status'] === 'WAITING_VENDOR');

// -----------------------------------------------------------------------------
// TEST 3: Service Request with Approval Workflow
// -----------------------------------------------------------------------------
echo "\n--- 3. Testing Service Request & Approval Lifecycle ---\n";
$serviceReqId = $ticketModel->create([
    'ticket_type'        => 'SERVICE_REQUEST',
    'title'              => 'Test Service Request: Cấp quyền VPN & Laptop mới',
    'description'        => 'Yêu cầu cấp phát laptop Dell Latitude và tài khoản VPN cho nhân viên mới.',
    'requester_id'       => $userId,
    'category_id'        => $catId,
    'impact'             => 'LOW',
    'urgency'            => 'MEDIUM',
    'support_tier'       => 'L1',
    'approver_id'        => $managerId,
    'approval_step_name' => 'Phê duyệt trang thiết bị'
]);

$svcTicket = $ticketModel->findById($serviceReqId);
assertTest("Service Request ticket created", $serviceReqId > 0);
assertTest("Approval status is PENDING", $svcTicket['approval_status'] === 'PENDING');

$approvalModel = new Approval();
$pendingList = $approvalModel->getPendingForUser($managerId, true);
$foundApproval = null;
foreach ($pendingList as $p) {
    if ((int)$p['ticket_id'] === $serviceReqId) {
        $foundApproval = $p;
        break;
    }
}
assertTest("Pending approval record found in Approvals Queue", $foundApproval !== null);

// Approve the request
if ($foundApproval) {
    $decideResult = $approvalModel->decide((int)$foundApproval['id'], $managerId, 'APPROVED', 'Đã đồng ý cấp theo ngân sách quý');
    assertTest("Approval decision recorded as APPROVED", $decideResult === true);
    $svcTicketAfter = $ticketModel->findById($serviceReqId);
    assertTest("Ticket approval_status updated to APPROVED", $svcTicketAfter['approval_status'] === 'APPROVED');
}

// -----------------------------------------------------------------------------
// TEST 4: Problem Management, RCA & KEDB
// -----------------------------------------------------------------------------
echo "\n--- 4. Testing Problem Management, RCA & KEDB Publishing ---\n";
$problemModel = new Problem();
$problemId = $problemModel->create([
    'title'       => 'Sự cố deadlock định kỳ trên module hóa đơn ERP',
    'description' => 'Hệ thống thường xuyên lock bảng invoices vào ngày cuối tháng do các batch job chạy đồng thời.',
    'category_id' => $catId,
    'priority_id' => 3,
    'assigned_to' => $staffId,
    'created_by'  => $staffId
]);

assertTest("Problem created with ID > 0", $problemId > 0);
$problem = $problemModel->findById($problemId);
assertTest("Problem code starts with PRB-", str_starts_with($problem['problem_code'], 'PRB-'));

// Link Incident to Problem
$linkOk = $problemModel->linkIncident($problemId, $incidentId);
assertTest("Linked Incident to Problem successfully", $linkOk === true);
$linkedIncidents = $problemModel->getLinkedIncidents($problemId);
assertTest("Problem has linked incident in list", count($linkedIncidents) >= 1);

// Update RCA & Workaround & Known Error flag
$problemModel->update($problemId, [
    'root_cause'         => 'Lock escalation trên InnoDB do transaction lớn kéo dài không index cột customer_id.',
    'workaround'         => 'Chạy lệnh kill query chậm và restart worker queue batch job.',
    'permanent_solution' => 'Thêm compound index (customer_id, created_at) và tách batch 500 rows.',
    'is_known_error'     => 1,
    'status'             => 'KNOWN_ERROR'
]);
$prbUpdated = $problemModel->findById($problemId);
assertTest("Problem marked as Known Error (is_known_error = 1)", (int)$prbUpdated['is_known_error'] === 1);
assertTest("Workaround saved in KEDB", !empty($prbUpdated['workaround']));

// Publish to Knowledge Base
$kbArticleId = $problemModel->publishToKnowledgeBase($problemId, $staffId);
assertTest("Published to Knowledge Base (Article ID: {$kbArticleId})", $kbArticleId !== null && $kbArticleId > 0);

// Resolve Problem & Batch resolve incidents
$problemModel->resolve($problemId, 'Đã release hotfix patch v3.2.1 áp dụng index mới và split batch.', $staffId, true);
$prbResolved = $problemModel->findById($problemId);
assertTest("Problem status updated to RESOLVED", $prbResolved['status'] === 'RESOLVED');

$incAfterPrb = $ticketModel->findById($incidentId);
assertTest("Linked incident automatically resolved via batch resolution", $incAfterPrb['status'] === 'RESOLVED');

// -----------------------------------------------------------------------------
// TEST 5: Change & Release Management (RFC) Lifecycle
// -----------------------------------------------------------------------------
echo "\n--- 5. Testing Change Request (RFC) Pipeline & CAB Approval ---\n";
$changeModel = new ChangeRequest();
$changeId = $changeModel->create([
    'title'               => 'Nâng cấp cụm Database MySQL Cluster lên 8.0.36',
    'description'         => 'Cập nhật phiên bản MySQL trên 3 node Master-Slave.',
    'reason'              => 'Khắc phục lỗ hổng bảo mật CVE và hỗ trợ JSON native improvements.',
    'change_type'         => 'NORMAL',
    'risk_level'          => 'HIGH',
    'environment_stage'   => 'DEV',
    'implementation_plan' => '1. Snapshot DB. 2. Backup full. 3. Upgrade Secondary nodes. 4. Failover. 5. Upgrade Primary.',
    'rollback_plan'       => 'Nếu quá trình upgrade lỗi trong 15p: Chuyển hướng traffic sang cụm Backup Standby và restore snapshot.',
    'test_plan'           => 'Chạy test suite tự động 250 API tests trên Staging.',
    'requested_by'        => $staffId,
    'assigned_to'         => $staffId
]);

assertTest("Change Request created with ID > 0", $changeId > 0);
$change = $changeModel->findById($changeId);
assertTest("Change code starts with CHG-", str_starts_with($change['change_code'], 'CHG-'));
assertTest("Initial stage is DEV", $change['environment_stage'] === 'DEV');

// Link Ticket to Change
$changeModel->linkTicket($changeId, $serviceReqId);
$linkedChanges = $changeModel->getLinkedTickets($changeId);
assertTest("Ticket successfully linked to Change Request", count($linkedChanges) >= 1);

// Advance Stage: DEV -> TEST -> MIG_UAT -> RELEASE_PROD
$changeModel->advanceStage($changeId, 'TEST', 'Hoàn tất test trên môi trường Local Dev');
$cTest = $changeModel->findById($changeId);
assertTest("Stage advanced to TEST", $cTest['environment_stage'] === 'TEST');

$changeModel->advanceStage($changeId, 'MIG_UAT', 'Pass toàn bộ QA regression suite');
$cUat = $changeModel->findById($changeId);
assertTest("Stage advanced to MIG_UAT", $cUat['environment_stage'] === 'MIG_UAT');

// CAB Approval
$cabOk = $changeModel->cabApprove($changeId, $managerId, 'APPROVED', 'Hội đồng CAB đồng ý lịch bảo trì cuối tuần');
assertTest("CAB approval successful", $cabOk === true);
$cCab = $changeModel->findById($changeId);
assertTest("CAB approved at timestamp recorded", !empty($cCab['cab_approved_at']));

// Advance to PROD & Complete
$changeModel->advanceStage($changeId, 'RELEASE_PROD', 'Bắt đầu window bảo trì sản phẩm');
$changeModel->completeChange($changeId, 'SUCCESSFUL', 'Triển khai thành công, không phát sinh lỗi.');
$cFinal = $changeModel->findById($changeId);
assertTest("Change final status is SUCCESSFUL", $cFinal['status'] === 'SUCCESSFUL');

// -----------------------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------------------
echo "\n=======================================================\n";
echo " TOTAL TESTS: " . ($passCount + $failCount) . " | PASSED: {$passCount} | FAILED: {$failCount}\n";
echo "=======================================================\n";

if ($failCount === 0) {
    echo " ALL ITIL & ITSM TESTS PASSED CLEANLY!\n";
    exit(0);
} else {
    echo " SOME TESTS FAILED. PLEASE CHECK OUTPUT ABOVE.\n";
    exit(1);
}
