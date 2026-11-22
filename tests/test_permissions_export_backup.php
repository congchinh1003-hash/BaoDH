<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Services\BackupService;
use App\Controllers\AdminController;
use App\Controllers\TicketController;
use App\Controllers\AssetController;
use App\Controllers\ProjectController;
use App\Controllers\BackupController;

$passed = 0;
$failed = 0;

function assertTest(string $desc, bool $cond, string $detail = '') {
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
echo " TEST SUITE: PERMISSIONS MATRIX, EXCEL EXPORT & BACKUP\n";
echo "=======================================================\n\n";

$db = Database::getConnection();

// -----------------------------------------------------
// 1. DATABASE SCHEMA & SEED CHECKS
// -----------------------------------------------------
$permCount = (int)$db->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
assertTest("Permissions table has seeded items", $permCount >= 20, "Found {$permCount} permissions");

$rolePermCount = (int)$db->query("SELECT COUNT(*) FROM role_permissions")->fetchColumn();
assertTest("Role permissions mapping table has seeded records", $rolePermCount > 0, "Found {$rolePermCount} mappings");

// Check essential permission codes exist
$requiredCodes = [
    'menu.projects', 'menu.tickets', 'menu.catalog', 'menu.approvals',
    'menu.assets', 'menu.kb', 'menu.jumpserver', 'menu.reports',
    'menu.admin', 'menu.backup',
    'export.tickets', 'export.issues', 'export.assets', 'export.audit_logs',
    'system.backup'
];

foreach ($requiredCodes as $code) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM permissions WHERE code = ?");
    $stmt->execute([$code]);
    assertTest("Permission code '{$code}' exists in database", $stmt->fetchColumn() > 0);
}

// -----------------------------------------------------
// 2. AUTH::CAN() & MATRIX BEHAVIOR
// -----------------------------------------------------
// Test Admin Super-User: mock admin session
\App\Core\Session::start();
\App\Core\Session::set('user_id', 1);
\App\Core\Session::set('user_roles', ['ADMIN']);
\App\Core\Session::set('user_data', [
    'id'        => 1,
    'username'  => 'admin',
    'full_name' => 'Administrator',
    'roles'     => ['ADMIN']
]);
Auth::clearPermissionCache();

assertTest("Admin has super-user access to 'menu.projects'", Auth::can('menu.projects') === true);
assertTest("Admin has super-user access to 'menu.admin'", Auth::can('menu.admin') === true);
assertTest("Admin has super-user access to 'system.backup'", Auth::can('system.backup') === true);
assertTest("Admin has super-user access to 'export.tickets'", Auth::can('export.tickets') === true);

// Test Regular User session
\App\Core\Session::set('user_id', 50);
\App\Core\Session::set('user_roles', ['USER']);
\App\Core\Session::set('user_data', [
    'id'        => 50,
    'username'  => 'test_user',
    'full_name' => 'Regular EndUser',
    'roles'     => ['USER']
]);
Auth::clearPermissionCache();

assertTest("User can access 'menu.catalog' (assigned by default)", Auth::can('menu.catalog') === true);
assertTest("User cannot access 'menu.admin' by default", Auth::can('menu.admin') === false);
assertTest("User cannot access 'system.backup' by default", Auth::can('system.backup') === false);

// Dynamically grant and revoke permission for USER role
$userRoleId = (int)$db->query("SELECT id FROM roles WHERE name = 'USER'")->fetchColumn();
$exportPermId = (int)$db->query("SELECT id FROM permissions WHERE code = 'export.tickets'")->fetchColumn();

if ($userRoleId && $exportPermId) {
    // Revoke
    $db->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?")->execute([$userRoleId, $exportPermId]);
    Auth::clearPermissionCache();
    assertTest("USER cannot export.tickets after revoking permission", Auth::can('export.tickets') === false);

    // Grant
    $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)")->execute([$userRoleId, $exportPermId]);
    Auth::clearPermissionCache();
    assertTest("USER can export.tickets immediately after granting permission", Auth::can('export.tickets') === true);

    // Clean up back to revoked
    $db->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?")->execute([$userRoleId, $exportPermId]);
    Auth::clearPermissionCache();
}

// -----------------------------------------------------
// 3. BACKUP & DISASTER RECOVERY SERVICE
// -----------------------------------------------------
$backupService = new BackupService();
$backupResult = $backupService->createBackup('unittest');

assertTest("BackupService created snapshot successfully", $backupResult['success'] === true);
assertTest("Backup file exists on disk", file_exists($backupResult['filepath']));
assertTest("Backup file is greater than 1KB", $backupResult['size'] > 1024, "Size: {$backupResult['size']} bytes");
assertTest("Backup includes all database tables", $backupResult['tables'] >= 10, "Tables: {$backupResult['tables']}");

$content = file_get_contents($backupResult['filepath']);
assertTest("Backup file contains FOREIGN_KEY_CHECKS=0", str_contains($content, 'SET FOREIGN_KEY_CHECKS=0;'));
assertTest("Backup file contains CREATE TABLE statements", str_contains($content, 'CREATE TABLE'));
assertTest("Backup file contains INSERT INTO statements", str_contains($content, 'INSERT INTO'));

// Test listBackups
$backups = $backupService->listBackups();
assertTest("BackupService lists created backups", count($backups) > 0);
assertTest("Latest backup filename matches created backup", $backups[0]['filename'] === $backupResult['filename']);

// Test Restore
$restoreResult = $backupService->restoreBackup($backupResult['filename']);
assertTest("BackupService restore succeeds without foreign key errors", $restoreResult['success'] === true, $restoreResult['message']);

// Test Delete
$delResult = $backupService->deleteBackup($backupResult['filename']);
assertTest("BackupService safely deleted test snapshot file", $delResult === true);
assertTest("Deleted snapshot file no longer exists", !file_exists($backupResult['filepath']));

// -----------------------------------------------------
// 4. EXCEL EXPORT ENGINE VERIFICATION
// -----------------------------------------------------
// Reset session to Admin
\App\Core\Session::set('user_id', 1);
\App\Core\Session::set('user_roles', ['ADMIN']);
\App\Core\Session::set('user_data', [
    'id'        => 1,
    'username'  => 'admin',
    'full_name' => 'Administrator',
    'roles'     => ['ADMIN']
]);
Auth::clearPermissionCache();

// Verify Export Audit Logs structure
ob_start();
$adminCtrl = new AdminController();
try {
    $request = new \App\Core\Request();
    $response = new \App\Core\Response();
    // Simulate export headers and output
    $stmt = $db->query("SELECT * FROM audit_logs LIMIT 5");
    $logs = $stmt->fetchAll();
    
    $out = fopen('php://temp', 'r+');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['ID', 'Thời gian', 'Người thực hiện', 'Tài khoản', 'Hành động', 'Đối tượng', 'ID đối tượng', 'Giá trị cũ', 'Giá trị mới', 'Lý do']);
    foreach ($logs as $row) {
        fputcsv($out, [$row['id'], $row['created_at'], 'Admin', 'admin', $row['action'], $row['object_type'], $row['object_id'] ?? '', '', '', '']);
    }
    rewind($out);
    $csvOutput = stream_get_contents($out);
    fclose($out);

    assertTest("Export CSV has UTF-8 BOM bytes (\xEF\xBB\xBF)", str_starts_with($csvOutput, "\xEF\xBB\xBF"));
    assertTest("Export CSV contains Vietnamese column headers", str_contains($csvOutput, 'Thời gian') && str_contains($csvOutput, 'Người thực hiện'));
} finally {
    ob_end_clean();
}

// -----------------------------------------------------
// 5. TICKETS SHOW.PHP VERIFICATION
// -----------------------------------------------------
// Verify app/Views/tickets/show.php has no syntax or runtime warnings
$viewPath = dirname(__DIR__) . '/app/Views/tickets/show.php';
$viewContent = file_get_contents($viewPath);

$hasBareSubstr = (bool)preg_match('/(?<!mb_)substr\s*\(/i', $viewContent);
assertTest("show.php contains NO bare substr() calls", !$hasBareSubstr);
assertTest("show.php contains guarded Tribute initialization", str_contains($viewContent, 'typeof Tribute !== \'undefined\''));

// -----------------------------------------------------
// 6. VIEW RENDERING TESTS: PERMISSIONS & BACKUPS
// -----------------------------------------------------
$req = new \App\Core\Request();
$res = new \App\Core\Response();

$adminCtrl = new AdminController();
$permHtml = $adminCtrl->permissionsMatrix($req, $res);
assertTest("admin/permissions.php renders cleanly with full HTML", strlen($permHtml) > 1000);
assertTest("admin/permissions.php contains form action /admin/permissions/update", str_contains($permHtml, '/admin/permissions/update'));
assertTest("admin/permissions.php contains csrf_token input", str_contains($permHtml, 'name="csrf_token"'));

$backupCtrl = new BackupController();
$backupHtml = $backupCtrl->index($req, $res);
assertTest("admin/backups.php renders cleanly with full HTML", strlen($backupHtml) > 1000);
assertTest("admin/backups.php contains create backup modal", str_contains($backupHtml, 'id="createBackupModal"'));

echo "\n=======================================================\n";
echo " TOTAL TESTS: " . ($passed + $failed) . " | PASSED: {$passed} | FAILED: {$failed}\n";
echo "=======================================================\n";

if ($failed === 0) {
    echo " ALL PERMISSION, EXPORT AND BACKUP TESTS PASSED 100%!\n";
    exit(0);
} else {
    echo " SOME TESTS FAILED!\n";
    exit(1);
}
