<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Models\JumpServer;
use App\Models\JumpSession;
use App\Core\Database;

$pdo = Database::getConnection();
$serverModel = new JumpServer();
$sessionModel = new JumpSession();

echo "========================================\n";
echo "TEST SUITE: JUMPSERVER BASTION PAM\n";
echo "========================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertTest(bool $condition, string $testName) {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        echo "[PASS] {$testName}\n";
        $testsPassed++;
    } else {
        echo "[FAIL] {$testName}\n";
    }
}

// 1. Check seed servers exist
$servers = $serverModel->getAll();
assertTest(count($servers) >= 4, "Database has seeded jump servers (Count: " . count($servers) . ")");

$ubuntuServer = null;
foreach ($servers as $s) {
    if ($s['os_type'] === 'UBUNTU' || $s['os_type'] === 'LINUX' || str_contains($s['name'], 'Production')) {
        $ubuntuServer = $s;
        break;
    }
}
assertTest($ubuntuServer !== null, "Found Linux/Ubuntu Production Server (ID: " . ($ubuntuServer['id'] ?? 'none') . ")");
$serverId = (int)$ubuntuServer['id'];

// 2. Test Authorization
// Admin can access
$adminAuth = $serverModel->checkPermission($serverId, 1, true);
assertTest($adminAuth === true, "Admin (ID: 1) has permission to access server");

// Fake regular user with no permissions
$unauthUserId = 999999;
$userAuth = $serverModel->checkPermission($serverId, $unauthUserId, false);
assertTest($userAuth === false, "Unauthorized User (ID: 999999) is denied access");

// Grant permission to a specific test user (e.g., ID 2 or 3)
$testUserId1 = 2; // Tech / Staff
$testUserId2 = 3; // Another user

// Ensure permission granted for user 2
$stmt = $pdo->prepare("DELETE FROM jump_server_permissions WHERE server_id = :sid");
$stmt->execute(['sid' => $serverId]);

$stmt = $pdo->prepare("INSERT INTO jump_server_permissions (server_id, user_id, granted_by, created_at) VALUES (:sid, :uid, 1, NOW())");
$stmt->execute(['sid' => $serverId, 'uid' => $testUserId1]);

$user2Auth = $serverModel->checkPermission($serverId, $testUserId1, false);
assertTest($user2Auth === true, "User 2 granted access has permission");

$user3Auth = $serverModel->checkPermission($serverId, $testUserId2, false);
assertTest($user3Auth === false, "User 3 without grant is rejected");

// 3. Test Exclusive Session Locking & Preemption
// Step 3a: User 1 connects
$sess1 = $sessionModel->createExclusiveSession($serverId, $testUserId1, '192.168.1.50');
assertTest(!empty($sess1['session_token']), "User 1 created session (Token: " . substr($sess1['session_token'], 0, 10) . "...)");

// Check status of User 1's session
$check1 = $sessionModel->checkSessionStatus($sess1['session_token']);
assertTest($check1['status'] === 'ACTIVE', "User 1 session is currently ACTIVE");

// Check server active occupant
$authorizedServers = $serverModel->getAuthorizedServers($testUserId1, false);
$targetServerInfo = null;
foreach ($authorizedServers as $as) {
    if ($as['id'] == $serverId) {
        $targetServerInfo = $as;
        break;
    }
}
assertTest($targetServerInfo['active_occupant'] == $testUserId1, "Server marks User 1 as active occupant");

// Step 3b: User 2 connects (PREEMPTION / KICK-OUT!)
$sess2 = $sessionModel->createExclusiveSession($serverId, $testUserId2, '192.168.1.88');
assertTest(!empty($sess2['session_token']), "User 2 connected to same server (Token: " . substr($sess2['session_token'], 0, 10) . "...)");

// Now check User 1 session status: MUST BE 'KICKED'
$check1After = $sessionModel->checkSessionStatus($sess1['session_token']);
assertTest($check1After['status'] === 'KICKED', "User 1 was KICKED out immediately! (Status: " . $check1After['status'] . ")");
assertTest($check1After['kicked_by_user_id'] == $testUserId2, "User 1 session records User 2 as the kicker");

// Check User 2 session status: MUST BE 'ACTIVE'
$check2 = $sessionModel->checkSessionStatus($sess2['session_token']);
assertTest($check2['status'] === 'ACTIVE', "User 2 session is currently ACTIVE");

// 4. Test Command Logging
$sessionModel->logCommand($sess2['session_id'], $serverId, $testUserId2, 'docker ps');
$sessionModel->logCommand($sess2['session_id'], $serverId, $testUserId2, 'df -h');

$logs = $sessionModel->getCommandLogs(10, $serverId);
$foundDocker = false;
foreach ($logs as $l) {
    if ($l['command'] === 'docker ps' && $l['user_id'] == $testUserId2) {
        $foundDocker = true;
        break;
    }
}
assertTest($foundDocker === true, "Command 'docker ps' successfully audited in command logs");

// 5. Test Active Sessions retrieval & Force Kick
$activeSessions = $sessionModel->getActiveSessions();
$hasActive = false;
foreach ($activeSessions as $act) {
    if ($act['id'] == $sess2['session_id']) {
        $hasActive = true;
        break;
    }
}
assertTest($hasActive === true, "Active sessions list includes User 2's session");

// Admin force kicks User 2
$sessionModel->forceKick($sess2['session_id'], 1);
$check2AfterKick = $sessionModel->checkSessionStatus($sess2['session_token']);
assertTest($check2AfterKick['status'] === 'KICKED', "Admin Force Kick sets User 2 session to KICKED");

// 6. Test Real SQL Database Simulation
$controller = new \App\Controllers\JumpServerController();
$reflector = new ReflectionClass($controller);
$simMethod = $reflector->getMethod('simulateCommandOutput');
$simMethod->setAccessible(true);

// Test 6a: Direct SQL query execution against live DB
$sqlResult = $simMethod->invoke($controller, 'SELECT id, username, full_name, email FROM users LIMIT 3;', $ubuntuServer, $sess1, 'bash');
assertTest(str_contains($sqlResult['output'], '+') && str_contains($sqlResult['output'], 'admin'), "Live DB query returned authentic ASCII table with real user 'admin'");
assertTest(str_contains($sqlResult['output'], 'row'), "Output has authentic MySQL 'rows in set' summary");

// Test 6b: Enter interactive mysql CLI
$mysqlEntry = $simMethod->invoke($controller, 'mysql -u root -p', $ubuntuServer, $sess1, 'bash');
assertTest($mysqlEntry['mode'] === 'mysql', "Command 'mysql' transitions mode to 'mysql'");
assertTest(str_contains($mysqlEntry['prompt'], 'mysql>'), "Prompt changes to 'mysql>'");
assertTest(str_contains($mysqlEntry['output'], 'Welcome to the MySQL monitor'), "Outputs authentic MySQL monitor banner");

// Test 6c: Execute SQL within mysql mode
$showTables = $simMethod->invoke($controller, 'SHOW TABLES;', $ubuntuServer, $sess1, 'mysql');
assertTest(str_contains($showTables['output'], 'jump_servers') && str_contains($showTables['output'], 'tickets'), "SHOW TABLES in mysql mode queries real database tables");
assertTest($showTables['mode'] === 'mysql', "Mode stays 'mysql' after query");

// Test 6d: Exit mysql mode back to bash
$exitMysql = $simMethod->invoke($controller, 'exit', $ubuntuServer, $sess1, 'mysql');
assertTest($exitMysql['mode'] === 'bash', "Typing 'exit' in mysql mode returns to 'bash'");
assertTest(str_contains($exitMysql['output'], 'Bye'), "MySQL exit outputs 'Bye'");

echo "\n========================================\n";
echo "TEST RESULTS: {$testsPassed}/{$totalTests} PASSED\n";
echo "========================================\n";

if ($testsPassed === $totalTests) {
    exit(0);
} else {
    exit(1);
}

