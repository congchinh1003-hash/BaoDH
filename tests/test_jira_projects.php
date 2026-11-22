<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\User;
use App\Core\Database;

$pdo = Database::getConnection();
$projectModel = new Project();
$issueModel = new ProjectIssue();
$userModel = new User();

echo "=======================================================\n";
echo " TEST SUITE: JIRA PROJECTS, BOARD, BACKLOG & QA/QC\n";
echo "=======================================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertTest(bool $condition, string $testName) {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        echo " [PASS] {$testName}\n";
        $testsPassed++;
    } else {
        echo " [FAIL] {$testName}\n";
    }
}

// 1. Check Project Beyond Gravity exists
$bg = $projectModel->findByKey('NUC');
assertTest($bg !== null && $bg['name'] === 'Beyond Gravity', "Found seeded Jira project 'Beyond Gravity' (Key: NUC)");

// 2. Project Statistics
$stats = $projectModel->getProjectStats($bg['id']);
assertTest($stats['total'] >= 20, "Project has at least 20 seeded issues (Found: {$stats['total']})");
assertTest($stats['todo'] > 0, "Project has TO DO issues (Found: {$stats['todo']})");
assertTest($stats['in_progress'] > 0, "Project has IN PROGRESS issues (Found: {$stats['in_progress']})");
assertTest($stats['in_review'] > 0, "Project has IN REVIEW issues (Found: {$stats['in_review']})");
assertTest($stats['done'] > 0, "Project has DONE issues (Found: {$stats['done']})");
assertTest($stats['testcases'] > 0, "Project has Test Cases (Found: {$stats['testcases']})");
assertTest($stats['bugs'] > 0, "Project has Bugs (Found: {$stats['bugs']})");

// 3. Board Grouping
$boardCols = $issueModel->getBoardIssues($bg['id']);
assertTest(isset($boardCols['TO_DO']) && isset($boardCols['IN_PROGRESS']) && isset($boardCols['IN_REVIEW']) && isset($boardCols['DONE']), "Board grouping returns all 4 standard Jira columns");
assertTest(count($boardCols['TO_DO']) > 0, "TO DO column contains active cards");
assertTest(count($boardCols['DONE']) > 0, "DONE column contains completed cards");

// 4. Backlog Separation
$backlogData = $issueModel->getBacklogData($bg['id']);
assertTest(count($backlogData['active_sprint']) > 0, "Active Sprint list populated");
assertTest(count($backlogData['backlog']) > 0, "Product Backlog list populated");

// 5. Test Case Module
$testcases = $issueModel->getTestCases($bg['id']);
assertTest(count($testcases) >= 3, "Test Cases module retrieved test items");
$tcFirst = $testcases[0];
assertTest(!empty($tcFirst['test_execution_status']), "Test Case has valid execution status ({$tcFirst['test_execution_status']})");

// Test updating test status
$testUpdateSuccess = $issueModel->updateTestExecution($tcFirst['id'], 'PASSED');
assertTest($testUpdateSuccess, "Updated Test Case execution status to PASSED via 1-click test runner");
$tcUpdated = $issueModel->findById($tcFirst['id']);
assertTest($tcUpdated['test_execution_status'] === 'PASSED', "Test Case execution status verified as PASSED");

// 6. Bug Tracking Module
$bugs = $issueModel->getBugs($bg['id']);
assertTest(count($bugs) >= 3, "Bug Tracking module retrieved bugs");
$hasBlocker = false;
foreach ($bugs as $b) {
    if ($b['bug_severity'] === 'BLOCKER' || $b['bug_severity'] === 'CRITICAL') {
        $hasBlocker = true;
        break;
    }
}
assertTest($hasBlocker, "Bug Tracking has Blocker/Critical severity classifications");

// 7. Dynamic Issue Creation
$testAdmin = $userModel->findByUsername('admin') ?? ['id' => 1];
$newIssueId = $issueModel->createIssue([
    'project_id'            => $bg['id'],
    'issue_type'            => 'TESTCASE',
    'summary'               => 'Test Auto Created QA Case',
    'description'           => 'Verification description',
    'status'                => 'TO_DO',
    'priority'              => 'HIGH',
    'reporter_id'           => $testAdmin['id'],
    'component'             => 'SECURITY',
    'test_type'             => 'AUTOMATED',
    'test_steps'            => '1. Step A\n2. Step B',
    'test_expected_result'  => 'Expected Result C',
    'test_execution_status' => 'UNTESTED'
]);
assertTest($newIssueId > 0, "Successfully created new issue dynamically (ID: {$newIssueId})");
$createdIssue = $issueModel->findById($newIssueId);
assertTest(str_starts_with($createdIssue['issue_key'], 'NUC-'), "Issue Key correctly formatted with prefix NUC- ({$createdIssue['issue_key']})");

// 8. Drag & Drop Status Update (TO_DO -> IN_PROGRESS -> DONE)
$dragSuccess = $issueModel->updateStatus($newIssueId, 'IN_PROGRESS');
assertTest($dragSuccess, "Drag & Drop card status updated to IN_PROGRESS");
$afterDrag = $issueModel->findById($newIssueId);
assertTest($afterDrag['status'] === 'IN_PROGRESS', "Issue status verified as IN_PROGRESS");

// 9. Move Sprint (Active Sprint -> Backlog -> Active Sprint)
$moveSuccess = $issueModel->moveToSprint($newIssueId, 'BACKLOG');
assertTest($moveSuccess, "Moved issue to BACKLOG");
$afterMove = $issueModel->findById($newIssueId);
assertTest($afterMove['sprint_status'] === 'BACKLOG', "Sprint status verified as BACKLOG");

// Cleanup test issue
$pdo->prepare("DELETE FROM project_issues WHERE id = :id")->execute(['id' => $newIssueId]);

// 10. Project Creation Test
$newPrjId = $projectModel->create([
    'name'         => 'Payment Gateway Integration',
    'project_key'  => 'PAY',
    'lead_id'      => $testAdmin['id'],
    'project_type' => 'SOFTWARE',
    'description'  => 'Test Project Creation'
]);
assertTest($newPrjId > 0, "Created new project 'Payment Gateway Integration' (ID: {$newPrjId})");
$newPrj = $projectModel->findById($newPrjId);
assertTest($newPrj['project_key'] === 'PAY', "Project Key correctly set as PAY");

// Check members
$newPrjMembers = $projectModel->getMembers($newPrjId);
assertTest(count($newPrjMembers) >= 1 && $newPrjMembers[0]['role'] === 'ADMIN', "Project Lead automatically added as ADMIN member");

// Cleanup test project
$pdo->prepare("DELETE FROM projects WHERE id = :id")->execute(['id' => $newPrjId]);

echo "\n=======================================================\n";
echo " TOTAL TESTS: {$totalTests} | PASSED: {$testsPassed} | FAILED: " . ($totalTests - $testsPassed) . "\n";
echo "=======================================================\n";
if ($totalTests === $testsPassed) {
    echo " ALL JIRA PROJECTS & WORKSPACE TESTS PASSED 100%!\n";
} else {
    echo " SOME TESTS FAILED!\n";
}
echo "=======================================================\n";

