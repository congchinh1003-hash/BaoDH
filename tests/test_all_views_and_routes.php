<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Models\User;

echo "=== TESTING LOGIN AND ALL PHASE 1 ROUTES ===\n";

// 1. Test User model login query
$userModel = new User();
$testAccounts = ['admin', 'manager', 'it_helpdesk', 'user1', 'admin@itdesk.local'];
foreach ($testAccounts as $acc) {
    $u = $userModel->findByUsernameOrEmail($acc);
    if (!$u) {
        throw new Exception("Failed to find account: {$acc}");
    }
    if (!password_verify('123456', $u['password_hash'])) {
        throw new Exception("Password verify failed for: {$acc}");
    }
    echo "[OK] Account '{$acc}' verified. ID: {$u['id']}, Full name: {$u['full_name']}\n";
}

// 2. Test simulating login as admin
$adminUser = $userModel->findByUsernameOrEmail('admin');
$roles = $userModel->getRoles((int)$adminUser['id']);
Auth::login($adminUser, $roles);
echo "[OK] Authenticated as Admin. Has ADMIN role: " . (Auth::isAdmin() ? 'YES' : 'NO') . "\n";

// 3. Test rendering views directly
$controllers = [
    new \App\Controllers\DashboardController(),
    new \App\Controllers\TicketController(),
    new \App\Controllers\AdminController()
];

$req = new Request();
$res = new Response();

// Test User Dashboard
$_SERVER['REQUEST_URI'] = '/dashboard';
$out = (new \App\Controllers\DashboardController())->userDashboard($req, $res);
echo "[OK] User Dashboard rendered (" . strlen($out) . " bytes)\n";

// Test Staff Work Queue
$_SERVER['REQUEST_URI'] = '/staff/work-queue';
$out = (new \App\Controllers\DashboardController())->staffWorkQueue($req, $res);
echo "[OK] Staff Work Queue rendered (" . strlen($out) . " bytes)\n";

// Test Manager Dashboard
$_SERVER['REQUEST_URI'] = '/manager/dashboard';
$out = (new \App\Controllers\DashboardController())->managerDashboard($req, $res);
echo "[OK] Manager Dashboard rendered (" . strlen($out) . " bytes)\n";

// Test Tickets Index
$_SERVER['REQUEST_URI'] = '/tickets';
$out = (new \App\Controllers\TicketController())->index($req, $res);
echo "[OK] Tickets Index rendered (" . strlen($out) . " bytes)\n";

// Test Tickets Create
$_SERVER['REQUEST_URI'] = '/tickets/create';
$out = (new \App\Controllers\TicketController())->create($req, $res);
echo "[OK] Tickets Create rendered (" . strlen($out) . " bytes)\n";

// Test Admin Groups
$_SERVER['REQUEST_URI'] = '/admin/groups';
$out = (new \App\Controllers\AdminController())->groups($req, $res);
echo "[OK] Admin Groups rendered (" . strlen($out) . " bytes)\n";

// Test Admin Categories
$_SERVER['REQUEST_URI'] = '/admin/categories';
$out = (new \App\Controllers\AdminController())->categories($req, $res);
echo "[OK] Admin Categories rendered (" . strlen($out) . " bytes)\n";

// Test Admin SLA
$_SERVER['REQUEST_URI'] = '/admin/sla';
$out = (new \App\Controllers\AdminController())->sla($req, $res);
echo "[OK] Admin SLA rendered (" . strlen($out) . " bytes)\n";

// Test Admin Users
$_SERVER['REQUEST_URI'] = '/admin/users';
$out = (new \App\Controllers\AdminController())->users($req, $res);
echo "[OK] Admin Users rendered (" . strlen($out) . " bytes)\n";

// Test Admin Audit Logs
$_SERVER['REQUEST_URI'] = '/admin/audit-logs';
$out = (new \App\Controllers\AdminController())->auditLogs($req, $res);
echo "[OK] Admin Audit Logs rendered (" . strlen($out) . " bytes)\n";

echo "=== ALL CONTROLLERS, VIEWS, AND QUERIES TESTED WITH ZERO ERRORS! ===\n";

