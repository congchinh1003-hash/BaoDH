<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\User;

echo "=======================================================\n";
echo " TEST SUITE: HIERARCHY, BACKLOG, EFFORT & PAGINATION\n";
echo "=======================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(bool $condition, string $msg): void {
    global $passCount, $failCount;
    if ($condition) {
        echo " [\033[32mPASS\033[0m] {$msg}\n";
        $passCount++;
    } else {
        echo " [\033[31mFAIL\033[0m] {$msg}\n";
        $failCount++;
    }
}

$projectModel = new Project();
$issueModel = new ProjectIssue();
$userModel = new User();

// 1. Get project and users
$bg = $projectModel->findByKey('NUC');
assertCheck($bg !== null, "Found Project Beyond Gravity (Key: NUC)");

$admin = $userModel->findByUsername('admin');
$devUser = $userModel->findByUsername('dev_tech') ?? $userModel->findByUsername('tech_support') ?? $admin;
assertCheck($admin !== null, "Found Admin User (ID: {$admin['id']})");

// 2. User creates Backlog item
$backlogId = $issueModel->createIssue([
    'project_id'              => $bg['id'],
    'issue_type'              => 'BACKLOG',
    'summary'                 => 'Backlog Master Epic: Nâng cấp Core API 2026',
    'description'             => 'Hạng mục Backlog cấp cao quản lý toàn bộ việc nâng cấp API',
    'notes'                   => 'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis',
    'priority'                => 'HIGH',
    'status'                  => 'TO_DO',
    'sprint_status'           => 'BACKLOG',
    'reporter_id'             => $devUser['id'],
    'assignee_id'             => $devUser['id'], // Self-assigned
    'original_estimate_hours' => 120.0,
    'remaining_hours'         => 120.0,
    'completed_hours'         => 0.0,
    'start_date'              => '2026-10-01',
    'due_date'                => '2026-12-31',
    'area'                    => 'TC8',
    'iteration'               => 'TC8\\IDMS Bảo trì 2026-2027'
]);
assertCheck($backlogId > 0, "Created Backlog item successfully (ID: {$backlogId})");

$backlogItem = $issueModel->findById($backlogId);
assertCheck($backlogItem['issue_type'] === 'BACKLOG', "Work Item type confirmed as BACKLOG");
assertCheck($backlogItem['sprint_status'] === 'BACKLOG', "Sprint status confirmed as BACKLOG");
assertCheck($backlogItem['parent_id'] === null, "Backlog item has parent_id = NULL (top-level root)");
assertCheck((float)$backlogItem['original_estimate_hours'] == 120.0, "Effort original estimate is 120.0 hours");
assertCheck($backlogItem['notes'] === 'Ghi chú kiến trúc hệ thống: Dùng JWT và Redis', "Notes field properly persisted");

// 3. Create Task as child of Backlog item
$taskId = $issueModel->createIssue([
    'project_id'              => $bg['id'],
    'issue_type'              => 'TASK',
    'parent_id'               => $backlogId, // Parent is Backlog
    'summary'                 => 'Task: Viết unit tests cho Authentication API',
    'description'             => 'Chi tiết các ca test PHPUnit cho AuthController',
    'notes'                   => 'Lưu ý mock database connection',
    'priority'                => 'MEDIUM',
    'status'                  => 'IN_PROGRESS',
    'sprint_status'           => 'ACTIVE_SPRINT',
    'reporter_id'             => $admin['id'], // Manager created
    'assignee_id'             => $devUser['id'], // Manager assigned to devUser
    'original_estimate_hours' => 16.0,
    'remaining_hours'         => 8.0,
    'completed_hours'         => 8.0
]);
assertCheck($taskId > 0, "Manager created Task assigned to devUser (ID: {$taskId})");

$taskItem = $issueModel->findById($taskId);
assertCheck((int)$taskItem['parent_id'] === $backlogId, "Task correctly points to parent Backlog (parent_id: {$backlogId})");
assertCheck($taskItem['parent_key'] === $backlogItem['issue_key'], "Task parent_key correctly resolved ({$taskItem['parent_key']})");

// 4. Create Bug as child of Backlog item
$bugId = $issueModel->createIssue([
    'project_id'              => $bg['id'],
    'issue_type'              => 'BUG',
    'parent_id'               => $backlogId, // Parent is Backlog
    'summary'                 => 'Bug: Lỗi timeout khi gọi API Gateway',
    'description'             => 'Khi tải cao, gateway timeout sau 30 giây',
    'bug_severity'            => 'CRITICAL',
    'priority'                => 'HIGHEST',
    'status'                  => 'TO_DO',
    'reporter_id'             => $devUser['id'],
    'assignee_id'             => $admin['id'],
    'original_estimate_hours' => 6.0,
    'remaining_hours'         => 6.0,
    'completed_hours'         => 0.0
]);
assertCheck($bugId > 0, "Created Bug as child of Backlog (ID: {$bugId})");

$bugItem = $issueModel->findById($bugId);
assertCheck((int)$bugItem['parent_id'] === $backlogId, "Bug parent_id points to Backlog");

// 5. Create TestCase as child of Bug
$testcaseId = $issueModel->createIssue([
    'project_id'              => $bg['id'],
    'issue_type'              => 'TESTCASE',
    'parent_id'               => $bugId, // TestCase is child of Bug
    'summary'                 => 'TestCase: Kiểm thử gateway timeout dưới tải 500 RPS',
    'description'             => 'Dùng k6 load test',
    'test_type'               => 'AUTOMATED',
    'test_execution_status'   => 'PASSED',
    'reporter_id'             => $devUser['id'],
    'assignee_id'             => $devUser['id']
]);
assertCheck($testcaseId > 0, "Created TestCase as child of Bug (ID: {$testcaseId})");

$testcaseItem = $issueModel->findById($testcaseId);
assertCheck((int)$testcaseItem['parent_id'] === $bugId, "TestCase parent_id correctly points to Bug (parent_id: {$bugId})");
assertCheck($testcaseItem['parent_key'] === $bugItem['issue_key'], "TestCase parent_key matches Bug key");

// 6. Test Children retrieval
$backlogChildren = $issueModel->getChildren($backlogId);
assertCheck(count($backlogChildren) >= 2, "Backlog item has at least 2 child items (Task & Bug) (Found: " . count($backlogChildren) . ")");

$bugChildren = $issueModel->getChildren($bugId);
assertCheck(count($bugChildren) >= 1, "Bug item has child TestCase linked (Found: " . count($bugChildren) . ")");

// 7. Test Eligible Parents Rule
$taskEligibleParents = $issueModel->getEligibleParents($bg['id'], 'TASK');
$allAreBacklog = true;
foreach ($taskEligibleParents as $p) {
    if (!in_array($p['issue_type'], ['BACKLOG', 'STORY'])) {
        $allAreBacklog = false;
        break;
    }
}
assertCheck($allAreBacklog && count($taskEligibleParents) > 0, "Eligible parents for TASK/BUG are BACKLOG/STORY items");

$tcEligibleParents = $issueModel->getEligibleParents($bg['id'], 'TESTCASE');
$allAreBugOrTask = true;
foreach ($tcEligibleParents as $p) {
    if (!in_array($p['issue_type'], ['BUG', 'TASK', 'STORY'])) {
        $allAreBugOrTask = false;
        break;
    }
}
assertCheck($allAreBugOrTask && count($tcEligibleParents) > 0, "Eligible parents for TESTCASE are BUG, TASK, STORY items");

$backlogEligibleParents = $issueModel->getEligibleParents($bg['id'], 'BACKLOG');
assertCheck(empty($backlogEligibleParents), "BACKLOG items have NO eligible parents (top level)");

// 8. Test Discussion Comments (Image 2)
$commentId = $issueModel->addComment($taskId, $admin['id'], "Vui lòng hoàn thành trước buổi demo ngày mai!");
assertCheck($commentId > 0, "Added Discussion comment to Task (ID: {$commentId})");

$comments = $issueModel->getComments($taskId);
assertCheck(count($comments) >= 1, "Retrieved comments for Task (Count: " . count($comments) . ")");
assertCheck($comments[0]['content'] === "Vui lòng hoàn thành trước buổi demo ngày mai!", "Comment text properly matches");
assertCheck(!empty($comments[0]['full_name']), "Comment user full_name joined successfully");

// 9. Test Pagination (Cap at 20 per page)
$paginatedIssues = $issueModel->getAllIssues($bg['id'], [], 1, 20);
assertCheck(isset($paginatedIssues['items']), "getAllIssues with page returns pagination array");
assertCheck(count($paginatedIssues['items']) <= 20, "Issues page 1 count <= 20 (Found: " . count($paginatedIssues['items']) . ")");
assertCheck($paginatedIssues['per_page'] === 20, "Issues per_page is strictly 20");
assertCheck($paginatedIssues['total'] >= 24, "Total issues tracked in pagination: {$paginatedIssues['total']}");
assertCheck($paginatedIssues['total_pages'] >= 2, "Total pages is at least 2 for > 20 issues ({$paginatedIssues['total_pages']} pages)");

$paginatedTestCases = $issueModel->getTestCases($bg['id'], [], 1, 20);
assertCheck(isset($paginatedTestCases['items']), "getTestCases with page returns pagination array");
assertCheck(count($paginatedTestCases['items']) <= 20, "TestCases count <= 20");

$paginatedBugs = $issueModel->getBugs($bg['id'], [], 1, 20);
assertCheck(isset($paginatedBugs['items']), "getBugs with page returns pagination array");
assertCheck(count($paginatedBugs['items']) <= 20, "Bugs count <= 20");

$backlogWithPage = $issueModel->getBacklogData($bg['id'], [], 1, 20);
assertCheck(isset($backlogWithPage['pagination']), "getBacklogData returns pagination array");
assertCheck(count($backlogWithPage['backlog']) <= 20, "Backlog items count <= 20 per page");

// 10. Verify Topbar Rebrand & No Jira Logo
$layoutContent = file_get_contents(__DIR__ . '/../app/Views/layouts/main.php');
assertCheck(strpos($layoutContent, 'TaskFlow') !== false, "Layout contains brand 'TaskFlow'");
assertCheck(strpos($layoutContent, '<svg width="17" height="17"') !== false, "Layout contains modern custom SVG icon");
assertCheck(strpos($layoutContent, 'tf-nav-item') !== false, "Layout uses borderless flat .tf-nav-item buttons");

$cssContent = file_get_contents(__DIR__ . '/../css/style.css');
assertCheck(strpos($cssContent, 'button.jira-nav-item,') !== false && strpos($cssContent, 'border: 0 !important;') !== false, "CSS explicitly removes native browser borders on buttons");

echo "\n=======================================================\n";
echo " TOTAL TESTS: " . ($passCount + $failCount) . " | PASSED: {$passCount} | FAILED: {$failCount}\n";
echo "=======================================================\n";

if ($failCount === 0) {
    echo " ALL HIERARCHY, BACKLOG, EFFORT & PAGINATION TESTS PASSED 100%!\n";
} else {
    exit(1);
}

