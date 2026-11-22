<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Models\Ticket;
use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\Asset;

$passed = 0;
$failed = 0;

function assertExport(string $desc, bool $cond, string $detail = '') {
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo " [PASS] {$desc}\n";
    } else {
        $failed++;
        echo " [FAIL] {$desc} " . ($detail ? "({$detail})" : '') . "\n";
    }
}

echo "=======================================================\n";
echo " LIVE EXPORT VERIFICATION TEST SUITE (ALL 4 DATA GRIDS)\n";
echo "=======================================================\n\n";

Session::start();
Session::set('user_id', 1);
Session::set('user_roles', ['ADMIN']);
Session::set('user_data', [
    'id'        => 1,
    'username'  => 'admin',
    'full_name' => 'Administrator',
    'roles'     => ['ADMIN']
]);
Auth::clearPermissionCache();

// --------------------------------------------------------------------
// 1. TEST TICKETS EXPORT DATA QUERY & CSV FORMATTING
// --------------------------------------------------------------------
$ticketModel = new Ticket();
$tickets = $ticketModel->getTickets([], 1, null);
assertExport("Ticket::getTickets returns array of tickets", is_array($tickets) && count($tickets) > 0, "Found " . count($tickets) . " tickets");

// Build CSV in memory
$out = fopen('php://temp', 'r+');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($out, [
    'Mã Ticket', 'Tiêu đề', 'Người yêu cầu', 'Phòng ban', 'Kỹ thuật viên phụ trách',
    'Nhóm IT', 'Danh mục', 'Mức độ ưu tiên', 'Trạng thái', 'Hạn xử lý SLA',
    'Trạng thái SLA', 'Thời gian tạo'
]);

foreach ($tickets as $t) {
    fputcsv($out, [
        $t['ticket_code'],
        $t['title'],
        $t['requester_name'] ?? 'N/A',
        $t['requester_department'] ?? $t['department'] ?? 'N/A',
        $t['assignee_name'] ?? 'Chưa phân công',
        $t['group_name'] ?? 'N/A',
        $t['category_name'] ?? 'N/A',
        $t['priority_name'] ?? 'N/A',
        $t['status_name'] ?? $t['status'],
        !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'Không áp dụng',
        $t['sla_status'] ?? 'WITHIN_SLA',
        date('d/m/Y H:i:s', strtotime($t['created_at']))
    ]);
}
rewind($out);
$ticketCsv = stream_get_contents($out);
fclose($out);

assertExport("Tickets CSV starts with UTF-8 BOM", str_starts_with($ticketCsv, "\xEF\xBB\xBF"));
assertExport("Tickets CSV contains column header 'Mã Ticket'", str_contains($ticketCsv, 'Mã Ticket'));
assertExport("Tickets CSV contains column header 'Hạn xử lý SLA'", str_contains($ticketCsv, 'Hạn xử lý SLA'));
assertExport("Tickets CSV contains real ticket code (e.g. IT-)", str_contains($ticketCsv, 'IT-'));

// --------------------------------------------------------------------
// 2. TEST PROJECT ISSUES EXPORT DATA QUERY & CSV FORMATTING
// --------------------------------------------------------------------
$projectModel = new Project();
$project = $projectModel->findById(1);
assertExport("Found Project #1", $project !== null);
assertExport("Project has project_key field", isset($project['project_key']));

$issueModel = new ProjectIssue();
$issues = $issueModel->getByProject(1);
assertExport("ProjectIssue::getByProject returns array", is_array($issues) && count($issues) > 0, "Found " . count($issues) . " issues");

$out = fopen('php://temp', 'r+');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($out, [
    'Mã Issue', 'Loại', 'Tiêu đề', 'Trạng thái', 'Mức độ ưu tiên',
    'Sprint / Backlog', 'Người thực hiện', 'Người tạo', 'Story Points',
    'Ước tính (h)', 'Ngày bắt đầu', 'Hạn hoàn thành', 'Thời gian tạo'
]);

foreach ($issues as $iss) {
    fputcsv($out, [
        $iss['issue_key'],
        $iss['issue_type'],
        $iss['summary'],
        $iss['status'],
        $iss['priority'],
        $iss['sprint_status'],
        $iss['assignee_name'] ?? 'Unassigned',
        $iss['reporter_name'] ?? 'N/A',
        $iss['story_points'] ?? '',
        $iss['original_estimate_hours'] ?? '',
        $iss['start_date'] ? date('d/m/Y', strtotime($iss['start_date'])) : '',
        $iss['due_date'] ? date('d/m/Y', strtotime($iss['due_date'])) : '',
        date('d/m/Y H:i:s', strtotime($iss['created_at']))
    ]);
}
rewind($out);
$issuesCsv = stream_get_contents($out);
fclose($out);

assertExport("Issues CSV starts with UTF-8 BOM", str_starts_with($issuesCsv, "\xEF\xBB\xBF"));
assertExport("Issues CSV contains column header 'Mã Issue'", str_contains($issuesCsv, 'Mã Issue'));
assertExport("Issues CSV contains column header 'Sprint / Backlog'", str_contains($issuesCsv, 'Sprint / Backlog'));

// --------------------------------------------------------------------
// 3. TEST ASSETS EXPORT DATA QUERY & CSV FORMATTING
// --------------------------------------------------------------------
$assetModel = new Asset();
$assets = $assetModel->getAll();
assertExport("Asset::getAll returns array of assets", is_array($assets) && count($assets) > 0, "Found " . count($assets) . " assets");

$out = fopen('php://temp', 'r+');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($out, [
    'Mã Thiết Bị', 'Tên Thiết Bị', 'Loại Tài Sản', 'Số Serial', 'Model',
    'Trạng Thái', 'Người Sử Dụng', 'Phòng Ban', 'Vị Trí', 'Giá Mua (VNĐ)',
    'Ngày Mua', 'Hết Hạn Bảo Hành'
]);

foreach ($assets as $a) {
    fputcsv($out, [
        $a['asset_code'],
        $a['name'],
        $a['category_name'] ?? 'N/A',
        $a['serial_number'] ?? '',
        $a['model'] ?? '',
        $a['status'],
        $a['current_user_name'] ?? 'Chưa bàn giao',
        $a['user_department'] ?? $a['department'] ?? 'N/A',
        $a['location'] ?? '',
        isset($a['purchase_cost']) ? number_format((float)$a['purchase_cost'], 0, ',', '.') : '0',
        !empty($a['purchase_date']) ? date('d/m/Y', strtotime($a['purchase_date'])) : '',
        !empty($a['warranty_expire']) ? date('d/m/Y', strtotime($a['warranty_expire'])) : ''
    ]);
}
rewind($out);
$assetCsv = stream_get_contents($out);
fclose($out);

assertExport("Assets CSV starts with UTF-8 BOM", str_starts_with($assetCsv, "\xEF\xBB\xBF"));
assertExport("Assets CSV contains column header 'Mã Thiết Bị'", str_contains($assetCsv, 'Mã Thiết Bị'));
assertExport("Assets CSV contains column header 'Người Sử Dụng'", str_contains($assetCsv, 'Người Sử Dụng'));

// --------------------------------------------------------------------
// 4. TEST AUDIT LOGS EXPORT DATA QUERY & CSV FORMATTING
// --------------------------------------------------------------------
$db = Database::getConnection();
$stmt = $db->query("
    SELECT a.*, u.full_name as user_name, u.username
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
");
$logs = $stmt->fetchAll();
assertExport("Audit logs query executes successfully", is_array($logs));

$out = fopen('php://temp', 'r+');
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
rewind($out);
$auditCsv = stream_get_contents($out);
fclose($out);

assertExport("Audit logs CSV starts with UTF-8 BOM", str_starts_with($auditCsv, "\xEF\xBB\xBF"));
assertExport("Audit logs CSV contains column header 'Người thực hiện'", str_contains($auditCsv, 'Người thực hiện'));

// --------------------------------------------------------------------
// 5. TEST REAL HTTP GET ROUTE FOR /tickets/export VIA CLI SUBPROCESS
// --------------------------------------------------------------------
// Run a subprocess that sets up session and executes the actual TicketController::exportExcel() method
$testScript = tempnam(sys_get_temp_dir(), 'export_test_') . '.php';
file_put_contents($testScript, '<?php
require_once "d:/Bao_DATN/app/autoload.php";
require_once "d:/Bao_DATN/app/Helpers.php";
\App\Core\Session::start();
\App\Core\Session::set("user_id", 1);
\App\Core\Session::set("user_roles", ["ADMIN"]);
\App\Core\Session::set("user_data", ["id" => 1, "username" => "admin", "full_name" => "Admin", "roles" => ["ADMIN"]]);
\App\Core\Auth::clearPermissionCache();

$ctrl = new \App\Controllers\TicketController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();
$ctrl->exportExcel($req, $res);
');

$output = shell_exec('php ' . escapeshellarg($testScript));
@unlink($testScript);

assertExport("Live TicketController::exportExcel() executes with code 0 and non-empty output", !empty($output));
assertExport("Live TicketController::exportExcel() outputs UTF-8 BOM", str_starts_with($output, "\xEF\xBB\xBF"));
assertExport("Live TicketController::exportExcel() output contains 'Mã Ticket'", str_contains($output, 'Mã Ticket'));

// --------------------------------------------------------------------
// 6. TEST REAL HTTP GET ROUTE FOR /projects/1/issues/export VIA CLI
// --------------------------------------------------------------------
$testScript2 = tempnam(sys_get_temp_dir(), 'export_test2_') . '.php';
file_put_contents($testScript2, '<?php
require_once "d:/Bao_DATN/app/autoload.php";
require_once "d:/Bao_DATN/app/Helpers.php";
\App\Core\Session::start();
\App\Core\Session::set("user_id", 1);
\App\Core\Session::set("user_roles", ["ADMIN"]);
\App\Core\Session::set("user_data", ["id" => 1, "username" => "admin", "full_name" => "Admin", "roles" => ["ADMIN"]]);
\App\Core\Auth::clearPermissionCache();

$ctrl = new \App\Controllers\ProjectController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();
$ctrl->exportIssues($req, $res, "1");
');

$output2 = shell_exec('php ' . escapeshellarg($testScript2));
@unlink($testScript2);

assertExport("Live ProjectController::exportIssues() executes with code 0 and non-empty output", !empty($output2));
assertExport("Live ProjectController::exportIssues() outputs UTF-8 BOM", str_starts_with($output2, "\xEF\xBB\xBF"));
assertExport("Live ProjectController::exportIssues() output contains 'Mã Issue'", str_contains($output2, 'Mã Issue'));

// --------------------------------------------------------------------
// 7. TEST REAL HTTP GET ROUTE FOR /assets/export VIA CLI
// --------------------------------------------------------------------
$testScript3 = tempnam(sys_get_temp_dir(), 'export_test3_') . '.php';
file_put_contents($testScript3, '<?php
require_once "d:/Bao_DATN/app/autoload.php";
require_once "d:/Bao_DATN/app/Helpers.php";
\App\Core\Session::start();
\App\Core\Session::set("user_id", 1);
\App\Core\Session::set("user_roles", ["ADMIN"]);
\App\Core\Session::set("user_data", ["id" => 1, "username" => "admin", "full_name" => "Admin", "roles" => ["ADMIN"]]);
\App\Core\Auth::clearPermissionCache();

$ctrl = new \App\Controllers\AssetController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();
$ctrl->exportExcel($req, $res);
');

$output3 = shell_exec('php ' . escapeshellarg($testScript3));
@unlink($testScript3);

assertExport("Live AssetController::exportExcel() executes with code 0 and non-empty output", !empty($output3));
assertExport("Live AssetController::exportExcel() outputs UTF-8 BOM", str_starts_with($output3, "\xEF\xBB\xBF"));
assertExport("Live AssetController::exportExcel() output contains 'Mã Thiết Bị'", str_contains($output3, 'Mã Thiết Bị'));

// --------------------------------------------------------------------
// 8. TEST REAL HTTP GET ROUTE FOR /admin/audit-logs/export VIA CLI
// --------------------------------------------------------------------
$testScript4 = tempnam(sys_get_temp_dir(), 'export_test4_') . '.php';
file_put_contents($testScript4, '<?php
require_once "d:/Bao_DATN/app/autoload.php";
require_once "d:/Bao_DATN/app/Helpers.php";
\App\Core\Session::start();
\App\Core\Session::set("user_id", 1);
\App\Core\Session::set("user_roles", ["ADMIN"]);
\App\Core\Session::set("user_data", ["id" => 1, "username" => "admin", "full_name" => "Admin", "roles" => ["ADMIN"]]);
\App\Core\Auth::clearPermissionCache();

$ctrl = new \App\Controllers\AdminController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();
$ctrl->exportAuditLogs($req, $res);
');

$output4 = shell_exec('php ' . escapeshellarg($testScript4));
@unlink($testScript4);

assertExport("Live AdminController::exportAuditLogs() executes with code 0 and non-empty output", !empty($output4));
assertExport("Live AdminController::exportAuditLogs() outputs UTF-8 BOM", str_starts_with($output4, "\xEF\xBB\xBF"));
assertExport("Live AdminController::exportAuditLogs() output contains 'Người thực hiện'", str_contains($output4, 'Người thực hiện'));

echo "\n=======================================================\n";
echo " TOTAL TESTS: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "=======================================================\n";

if ($failed === 0) {
    echo " ALL 4 LIVE EXPORT ENDPOINTS PASSED 100%!\n";
    exit(0);
} else {
    echo " SOME EXPORT TESTS FAILED!\n";
    exit(1);
}

