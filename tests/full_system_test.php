<?php

/**
 * FULL SYSTEM AUTOMATED TEST SUITE
 * Simulates real user sessions across all roles and tests all routes & features.
 */

$baseUrl = 'http://localhost:3000';

function getSessionCookieFile(string $baseUrl, string $username, string $password): ?string {
    $cookieFile = sys_get_temp_dir() . "/sess_{$username}.txt";
    if (file_exists($cookieFile)) {
        unlink($cookieFile);
    }

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Extract CSRF token
    preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $response, $matches);
    $csrfToken = $matches[1] ?? '';

    if (empty($csrfToken)) {
        echo "[-] Failed to fetch CSRF token for login\n";
        return null;
    }

    // Submit login POST
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        '_csrf_token' => $csrfToken,
        'account'     => $username,
        'password'    => $password
    ]));
    $postResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    if ($httpCode === 302 && strpos($redirectUrl, '/login') === false) {
        return $cookieFile;
    }

    return null;
}

function requestRoute(string $url, ?string $cookieFile = null, string $method = 'GET', array $data = []): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    if ($cookieFile && file_exists($cookieFile)) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $body ?: '',
        'effective_url' => $effectiveUrl,
        'error' => $error
    ];
}

function inspectResponse(string $name, string $expectedUrl, array $res): array {
    $errors = [];
    $code = $res['code'];
    $body = $res['body'];
    $effectiveUrl = $res['effective_url'];

    if ($code >= 400) {
        $errors[] = "HTTP $code Error (URL: $effectiveUrl)";
    }

    // Check if unexpectedly redirected to login
    if (strpos($effectiveUrl, '/login') !== false && strpos($expectedUrl, '/login') === false && $expectedUrl !== '/') {
        $errors[] = "Unexpectedly redirected to /login (Session lost or Unauthorized)";
    }

    // Scan for PHP errors
    if (preg_match('/<b>Fatal error<\/b>:(.*?)(?:in\s+<b>|<br)/is', $body, $m)) {
        $errors[] = "Fatal Error: " . trim(strip_tags($m[1]));
    } elseif (strpos($body, 'Fatal error:') !== false) {
        $errors[] = "Fatal Error detected";
    }

    if (preg_match('/<b>Warning<\/b>:(.*?)(?:in\s+<b>|<br|\n)/is', $body, $m)) {
        $errors[] = "Warning: " . trim(strip_tags($m[1]));
    } elseif (strpos($body, 'Warning:') !== false) {
        if (preg_match('/Warning:\s*([^<\n\r]+)/i', $body, $wm)) {
            $errors[] = "Warning: " . trim($wm[1]);
        }
    }

    if (preg_match('/<b>Notice<\/b>:(.*?)(?:in\s+<b>|<br|\n)/is', $body, $m)) {
        $errors[] = "Notice: " . trim(strip_tags($m[1]));
    } elseif (strpos($body, 'Notice:') !== false) {
        if (preg_match('/Notice:\s*([^<\n\r]+)/i', $body, $nm)) {
            $errors[] = "Notice: " . trim($nm[1]);
        }
    }

    if (preg_match('/<b>Deprecated<\/b>:(.*?)(?:in\s+<b>|<br|\n)/is', $body, $m)) {
        $errors[] = "Deprecated: " . trim(strip_tags($m[1]));
    }

    if (strpos($body, 'SQLSTATE[') !== false) {
        $errors[] = "SQL Exception in body";
    }

    if (strpos($body, 'Uncaught Error') !== false) {
        $errors[] = "Uncaught Error in body";
    }

    if (strpos($body, 'Lỗi Xử Lý Hệ Thống') !== false) {
        preg_match('/<p class="text-danger fw-semibold">(.*?)<\/p>/is', $body, $em);
        $errors[] = "System Exception: " . ($em[1] ?? 'Unknown error');
    }

    return $errors;
}

echo "====================================================\n";
echo "      STARTING FULL SYSTEM HEALTH & ROUTE TESTS     \n";
echo "====================================================\n\n";

$roles = [
    'admin' => ['user' => 'admin', 'pass' => '123456'],
    'manager' => ['user' => 'manager', 'pass' => '123456'],
    'it_helpdesk' => ['user' => 'it_helpdesk', 'pass' => '123456'],
    'user1' => ['user' => 'user1', 'pass' => '123456']
];

$sessions = [];
foreach ($roles as $roleKey => $cred) {
    echo "Logging in as {$cred['user']} ($roleKey)... ";
    $cookie = getSessionCookieFile($baseUrl, $cred['user'], $cred['pass']);
    if ($cookie) {
        $sessions[$roleKey] = $cookie;
        echo "[OK]\n";
    } else {
        echo "[FAILED]\n";
    }
}

// Fetch dynamic test IDs
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';
$db = \App\Core\Database::getConnection();

$projectId = $db->query("SELECT id FROM projects ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
$ticketId = $db->query("SELECT id FROM tickets ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
$st = $db->prepare("SELECT id FROM project_issues WHERE project_id = ? ORDER BY id DESC LIMIT 1");
$st->execute([$projectId]);
$issueId = $st->fetchColumn() ?: 1;
$assetId = $db->query("SELECT id FROM assets ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
$problemId = $db->query("SELECT id FROM problems ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
$changeId = $db->query("SELECT id FROM change_requests ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
$kbId = $db->query("SELECT id FROM kb_articles ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
$userId = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;

echo "\nUsing Test Entity IDs:\n";
echo "Project: $projectId, Issue: $issueId, Ticket: $ticketId, Asset: $assetId, Problem: $problemId, Change: $changeId, KB: $kbId\n\n";

$testRoutes = [
    // Public / Auth
    ['role' => null, 'url' => '/login', 'name' => 'Public: Login Page'],
    ['role' => null, 'url' => '/', 'name' => 'Public: Root Redirect'],

    // User Persona
    ['role' => 'user1', 'url' => '/dashboard', 'name' => 'User: Dashboard'],
    ['role' => 'user1', 'url' => '/profile', 'name' => 'User: Profile'],
    ['role' => 'user1', 'url' => '/tickets', 'name' => 'User: My Tickets'],
    ['role' => 'user1', 'url' => '/tickets/create', 'name' => 'User: Create Ticket'],
    ['role' => 'user1', 'url' => "/tickets/$ticketId", 'name' => 'User: Ticket Details'],
    ['role' => 'user1', 'url' => '/catalog', 'name' => 'User: Service Catalog'],
    ['role' => 'user1', 'url' => '/my-assets', 'name' => 'User: My Assets'],
    ['role' => 'user1', 'url' => '/kb', 'name' => 'User: Knowledge Base'],
    ['role' => 'user1', 'url' => "/kb/$kbId", 'name' => 'User: KB Article View'],
    ['role' => 'user1', 'url' => '/approvals', 'name' => 'User: Approvals List'],

    // Staff Persona
    ['role' => 'it_helpdesk', 'url' => '/staff/work-queue', 'name' => 'Staff: Work Queue'],
    ['role' => 'it_helpdesk', 'url' => '/tickets/board', 'name' => 'Staff: Kanban Board'],
    ['role' => 'it_helpdesk', 'url' => '/assets', 'name' => 'Staff: Assets List'],
    ['role' => 'it_helpdesk', 'url' => '/assets/create', 'name' => 'Staff: Create Asset'],
    ['role' => 'it_helpdesk', 'url' => "/assets/$assetId", 'name' => 'Staff: Asset View'],
    ['role' => 'it_helpdesk', 'url' => "/assets/$assetId/edit", 'name' => 'Staff: Edit Asset'],
    ['role' => 'it_helpdesk', 'url' => "/assets/$assetId/qr", 'name' => 'Staff: Asset QR'],
    ['role' => 'it_helpdesk', 'url' => '/problems', 'name' => 'Staff: Problems List'],
    ['role' => 'it_helpdesk', 'url' => '/problems/create', 'name' => 'Staff: Create Problem'],
    ['role' => 'it_helpdesk', 'url' => "/problems/$problemId", 'name' => 'Staff: Problem View'],
    ['role' => 'it_helpdesk', 'url' => '/changes', 'name' => 'Staff: Changes List'],
    ['role' => 'it_helpdesk', 'url' => '/changes/create', 'name' => 'Staff: Create Change'],
    ['role' => 'it_helpdesk', 'url' => "/changes/$changeId", 'name' => 'Staff: Change View'],
    ['role' => 'it_helpdesk', 'url' => '/kb/manage', 'name' => 'Staff: Manage KB'],
    ['role' => 'it_helpdesk', 'url' => '/kb/create', 'name' => 'Staff: Create KB Article'],
    ['role' => 'it_helpdesk', 'url' => '/reports', 'name' => 'Staff: Reports Dashboard'],
    ['role' => 'it_helpdesk', 'url' => '/reports/staff', 'name' => 'Staff: Reports Staff Performance'],
    ['role' => 'it_helpdesk', 'url' => '/reports/export', 'name' => 'Staff: Export CSV'],
    ['role' => 'it_helpdesk', 'url' => '/tickets/export', 'name' => 'Staff: Export Tickets Excel'],
    ['role' => 'it_helpdesk', 'url' => '/assets/export', 'name' => 'Staff: Export Assets Excel'],

    // Manager Persona
    ['role' => 'manager', 'url' => '/manager/dashboard', 'name' => 'Manager: Dashboard'],
    ['role' => 'manager', 'url' => '/tickets/unassigned', 'name' => 'Manager: Unassigned Tickets'],

    // TaskFlow / Jira Projects (Accessible to Auth)
    ['role' => 'admin', 'url' => '/projects', 'name' => 'Projects: List'],
    ['role' => 'admin', 'url' => '/projects/create', 'name' => 'Projects: Create Form'],
    ['role' => 'admin', 'url' => "/projects/$projectId/board", 'name' => 'Projects: Kanban Board'],
    ['role' => 'admin', 'url' => "/projects/$projectId/backlog", 'name' => 'Projects: Backlog'],
    ['role' => 'admin', 'url' => "/projects/$projectId/testcases", 'name' => 'Projects: Test Cases'],
    ['role' => 'admin', 'url' => "/projects/$projectId/bugs", 'name' => 'Projects: Bugs'],
    ['role' => 'admin', 'url' => "/projects/$projectId/issues", 'name' => 'Projects: Issues List'],
    ['role' => 'admin', 'url' => "/projects/$projectId/issues/create", 'name' => 'Projects: Create Issue Page'],
    ['role' => 'admin', 'url' => "/projects/$projectId/issues/$issueId", 'name' => 'Projects: Issue Details'],
    ['role' => 'admin', 'url' => "/projects/$projectId/settings", 'name' => 'Projects: Project Settings'],
    ['role' => 'admin', 'url' => "/projects/$projectId/issues/export", 'name' => 'Projects: Export Issues'],

    // JumpServer & Bastion PAM
    ['role' => 'admin', 'url' => '/jump', 'name' => 'JumpServer: User Portal'],
    ['role' => 'admin', 'url' => '/admin/jump-servers', 'name' => 'JumpServer: Admin Management'],

    // Admin Persona
    ['role' => 'admin', 'url' => '/admin/users', 'name' => 'Admin: Users Management'],
    ['role' => 'admin', 'url' => '/admin/groups', 'name' => 'Admin: Groups Management'],
    ['role' => 'admin', 'url' => '/admin/categories', 'name' => 'Admin: Categories Management'],
    ['role' => 'admin', 'url' => '/admin/sla', 'name' => 'Admin: SLA Policies'],
    ['role' => 'admin', 'url' => '/admin/permissions', 'name' => 'Admin: RBAC Permissions Matrix'],
    ['role' => 'admin', 'url' => '/admin/audit-logs', 'name' => 'Admin: Audit Logs'],
    ['role' => 'admin', 'url' => '/admin/audit-logs/export', 'name' => 'Admin: Export Audit Logs'],
    ['role' => 'admin', 'url' => '/admin/backups', 'name' => 'Admin: Backups & Restore'],

    // APIs
    ['role' => 'admin', 'url' => '/api/services', 'name' => 'API: Get Services'],
    ['role' => 'admin', 'url' => '/api/users/search?q=admin', 'name' => 'API: Search Users'],
    ['role' => 'admin', 'url' => '/api/notifications/unread', 'name' => 'API: Unread Notifications'],
    ['role' => 'admin', 'url' => "/projects/api/eligible-parents?projectId=$projectId", 'name' => 'API: Project Eligible Parents'],
];

$failedCount = 0;
$totalTests = count($testRoutes);
$report = [];

foreach ($testRoutes as $idx => $route) {
    $role = $route['role'];
    $cookieFile = $role ? ($sessions[$role] ?? null) : null;
    $url = $baseUrl . $route['url'];
    
    $res = requestRoute($url, $cookieFile);
    $errors = inspectResponse($route['name'], $route['url'], $res);

    if (empty($errors)) {
        echo sprintf("[%02d/%02d] [PASS] %s (HTTP %d)\n", $idx + 1, $totalTests, $route['name'], $res['code']);
    } else {
        $failedCount++;
        echo sprintf("[%02d/%02d] [FAIL] %s (HTTP %d)\n", $idx + 1, $totalTests, $route['name'], $res['code']);
        foreach ($errors as $err) {
            echo "       -> ERROR: $err\n";
        }
        $report[] = [
            'name' => $route['name'],
            'url' => $route['url'],
            'http' => $res['code'],
            'errors' => $errors,
            'body_snippet' => substr(strip_tags($res['body']), 0, 300)
        ];
    }
}

echo "\n====================================================\n";
echo "SUMMARY: Total: $totalTests | Passed: " . ($totalTests - $failedCount) . " | Failed: $failedCount\n";
echo "====================================================\n";

if ($failedCount > 0) {
    echo "\nFAILED DETAILS:\n";
    foreach ($report as $r) {
        echo "- {$r['name']} ({$r['url']}) [HTTP {$r['http']}]\n";
        foreach ($r['errors'] as $e) {
            echo "  * $e\n";
        }
        if (!empty($r['body_snippet'])) {
            echo "  Snippet: " . trim($r['body_snippet']) . "\n";
        }
    }
    exit(1);
}

exit(0);
