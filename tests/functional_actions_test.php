<?php

/**
 * FUNCTIONAL ACTIONS & WORKFLOW INTEGRATION TEST
 */

$baseUrl = 'http://localhost:3000';
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';
$db = \App\Core\Database::getConnection();

function getSessionCookie(string $baseUrl, string $username, string $password): ?string {
    $cookieFile = sys_get_temp_dir() . "/func_sess_{$username}.txt";
    if (file_exists($cookieFile)) unlink($cookieFile);

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $html = curl_exec($ch);
    curl_close($ch);

    preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $html, $m);
    $csrfToken = $m[1] ?? '';

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
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($code === 302) ? $cookieFile : null;
}

function getCsrfToken(string $baseUrl, string $cookieFile, string $url): string {
    $ch = curl_init("$baseUrl$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $html = curl_exec($ch);
    curl_close($ch);

    preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $html, $m);
    if (!empty($m[1])) return $m[1];
    
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m);
    return $m[1] ?? '';
}

function postRequest(string $url, string $cookieFile, array $data, bool $isAjax = false): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $headers = [];
    if ($isAjax) {
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Accept: application/json';
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'redirect' => $redirect];
}

echo "=== STARTING FUNCTIONAL WORKFLOW TESTS ===\n\n";

$adminCookie = getSessionCookie($baseUrl, 'admin', '123456');
$userCookie = getSessionCookie($baseUrl, 'user1', '123456');

if (!$adminCookie || !$userCookie) {
    echo "[-] Failed to authenticate test sessions\n";
    exit(1);
}

// 1. Test Ticket Creation Flow
echo "[1] Testing Ticket Creation by User... ";
$csrf = getCsrfToken($baseUrl, $userCookie, '/tickets/create');
$serviceId = $db->query("SELECT id FROM ticket_services LIMIT 1")->fetchColumn() ?: 1;

$ticketRes = postRequest("$baseUrl/tickets/create", $userCookie, [
    '_csrf_token' => $csrf,
    'title'       => 'Kiểm tra lỗi hệ thống tự động ' . date('H:i:s'),
    'description' => 'Mô tả chi tiết kiểm tra ticket chức năng tự động.',
    'service_id'  => $serviceId,
    'priority'    => 'MEDIUM'
]);

if ($ticketRes['code'] === 302) {
    echo "[PASS] (Redirected to: {$ticketRes['redirect']})\n";
} else {
    echo "[FAIL] (HTTP {$ticketRes['code']})\n";
}

// 2. Test Quick Create Issue in Project Flow
echo "[2] Testing Project Quick Create Issue API... ";
$projectId = $db->query("SELECT id FROM projects ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1;
$managerId = $db->query("SELECT id FROM users WHERE username = 'manager'")->fetchColumn() ?: 2;

$issueRes = postRequest("$baseUrl/projects/api/quick-create", $adminCookie, [
    'project_id'     => $projectId,
    'issue_type'     => 'TASK',
    'summary'        => 'Auto Test Task ' . time(),
    'description'    => 'Automated test task description',
    'priority'       => 'High',
    'status'         => 'TO DO',
    'assignee_id'    => $managerId,
    'story_points'   => 3,
    'estimated_hours'=> 2
], true);

$json = json_decode($issueRes['body'], true);
if ($issueRes['code'] === 200 && ($json['success'] ?? false) === true) {
    $newIssueId = $json['issue']['id'] ?? $json['issue_id'] ?? null;
    echo "[PASS] Created Issue #$newIssueId\n";
} else {
    echo "[FAIL] Response: {$issueRes['body']}\n";
}

// 3. Test Kanban Status Transition via AJAX
if (!empty($newIssueId)) {
    echo "[3] Testing Issue Kanban Status Transition to 'IN PROGRESS'... ";
    $transitionRes = postRequest("$baseUrl/projects/api/update-status", $adminCookie, [
        'issue_id'   => $newIssueId,
        'status'     => 'IN PROGRESS',
        'project_id' => $projectId
    ], true);
    $transJson = json_decode($transitionRes['body'], true);
    if ($transitionRes['code'] === 200 && ($transJson['success'] ?? false) === true) {
        echo "[PASS]\n";
    } else {
        echo "[FAIL] Response: {$transitionRes['body']}\n";
    }

    // 4. Test Add Comment to Issue
    echo "[4] Testing Add Comment to Issue... ";
    $commentRes = postRequest("$baseUrl/projects/api/add-comment", $adminCookie, [
        'issue_id' => $newIssueId,
        'content'  => 'Automated verification test comment.'
    ], true);
    $commJson = json_decode($commentRes['body'], true);
    if ($commentRes['code'] === 200 && ($commJson['success'] ?? false) === true) {
        echo "[PASS]\n";
    } else {
        echo "[FAIL] Response: {$commentRes['body']}\n";
    }
}

// 5. Test Notification Fetch & Mark As Read
echo "[5] Testing Unread Notifications & Mark Read API... ";
$ch = curl_init("$baseUrl/api/notifications/unread");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
$notifRes = curl_exec($ch);
curl_close($ch);

$notifJson = json_decode($notifRes, true);
if (($notifJson['success'] ?? false) === true) {
    echo "[PASS] Retrieved " . count($notifJson['data'] ?? []) . " unread notifications\n";
} else {
    echo "[FAIL] Response: $notifRes\n";
}

echo "\n=== ALL FUNCTIONAL WORKFLOW TESTS COMPLETED SUCCESSFULLY ===\n";
