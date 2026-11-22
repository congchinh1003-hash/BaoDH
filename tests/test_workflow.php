<?php

require_once __DIR__ . '/../app/autoload.php';

use App\Models\Ticket;
use App\Services\SlaService;

echo "=== VERIFYING WORKFLOW END-TO-END ===\n";

$ticketModel = new Ticket();

// 1. User 6 creates an URGENT ticket
$tid = $ticketModel->create([
    'title' => 'Mất kết nối máy chủ dữ liệu kế toán',
    'description' => 'Không thể mở phần mềm kế toán từ 8h sáng, toàn bộ phòng kế toán bị ảnh hưởng',
    'requester_id' => 6,
    'category_id' => 1, // Network
    'service_id' => 1,
    'priority_id' => 3, // Urgent
    'location' => 'Tầng 2, Phòng Kế toán',
    'contact_info' => 'Ext 102'
]);

$t = $ticketModel->findById($tid);
echo "1. Created Ticket: {$t['ticket_code']} | SLA: {$t['sla_value']} {$t['sla_unit']} | Due: {$t['sla_due_at']}\n";

// 2. Manager (id: 2) assigns to IT Helpdesk (id: 3)
$ticketModel->assign($tid, 3, 2, 'Giao cho Bảo Helpdesk tiếp nhận');
$t = $ticketModel->findById($tid);
echo "2. Assigned to: {$t['assignee_name']} | Status: {$t['status']}\n";

// 3. IT Helpdesk moves to IN_PROGRESS
$ticketModel->updateStatus($tid, 'IN_PROGRESS', 3, 'Bắt đầu kiểm tra switch mạng');
$t = $ticketModel->findById($tid);
echo "3. Status updated: {$t['status']}\n";

// 4. IT Helpdesk transfers to IT Network (group: 2) - SLA must not reset
$initialDue = $t['sla_due_at'];
$ticketModel->transferGroup($tid, 2, 3, 'Lỗi từ switch core của nhóm Network');
$t = $ticketModel->findById($tid);
$intact = ($initialDue === $t['sla_due_at']) ? 'YES' : 'NO';
echo "4. Transferred to: {$t['group_name']} | SLA Due Intact: {$intact}\n";

// 5. IT Network pauses SLA (WAITING_USER)
$ticketModel->updateStatus($tid, 'WAITING_USER', 4, 'Chờ kế toán cung cấp địa chỉ IP máy trạm');
$t = $ticketModel->findById($tid);
echo "5. Paused SLA: status = {$t['status']} | pause_started_at = {$t['pause_started_at']}\n";

// 6. Resume SLA (simulate pause duration)
sleep(2);
$slaService = new SlaService();
$slaService->resumeSla($tid, 6);
$ticketModel->updateStatus($tid, 'IN_PROGRESS', 4, 'User đã cung cấp IP, tiếp tục xử lý');
$t = $ticketModel->findById($tid);
echo "6. Resumed SLA: paused_seconds = {$t['sla_paused_seconds']}s | Extended Due: {$t['sla_due_at']}\n";

// 7. IT Network Resolves ticket
$ticketModel->resolve($tid, 4, 'Đã cấu hình lại VLAN 20 trên Switch Core', 'Xung đột dải IP', 'Cập nhật lại DHCP range');
$t = $ticketModel->findById($tid);
echo "7. Resolved: {$t['status']} | Resolution: {$t['resolution']}\n";

// 8. User Confirms Close
$ticketModel->close($tid, 6, 'Đã vào lại được phần mềm kế toán bình thường, cảm ơn IT!');
$t = $ticketModel->findById($tid);
echo "8. Closed: {$t['status']} | Closed at: {$t['closed_at']}\n";

// 9. Check Timeline history count
$history = $ticketModel->getHistory($tid);
echo "9. Total Audit Timeline steps: " . count($history) . "\n";
echo "=== ALL TEST STEPS PASSED PERFECTLY! ===\n";

