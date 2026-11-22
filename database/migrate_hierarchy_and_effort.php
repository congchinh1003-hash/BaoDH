<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Database;

$pdo = Database::getConnection();

echo "Running Migration for TaskFlow / Project Issues Hierarchy & Effort...\n";

// 1. Alter project_issues table to add parent_id, notes, effort, dates, risks, tags, area, iteration
$columnsToAdd = [
    "parent_id INT NULL DEFAULT NULL",
    "notes LONGTEXT NULL",
    "original_estimate_hours DECIMAL(6,2) DEFAULT 0.00",
    "remaining_hours DECIMAL(6,2) DEFAULT 0.00",
    "completed_hours DECIMAL(6,2) DEFAULT 0.00",
    "start_date DATE NULL",
    "due_date DATE NULL",
    "risks TEXT NULL",
    "tags VARCHAR(255) NULL",
    "area VARCHAR(100) DEFAULT 'Main/Core'",
    "iteration VARCHAR(100) DEFAULT 'Sprint 1 (2026)'"
];

// Check existing columns
$existingColsStmt = $pdo->query("SHOW COLUMNS FROM project_issues");
$existingCols = $existingColsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

foreach ($columnsToAdd as $colDef) {
    $colName = explode(' ', trim($colDef))[0];
    if (!in_array($colName, $existingCols)) {
        try {
            $pdo->exec("ALTER TABLE project_issues ADD COLUMN {$colDef}");
            echo "[OK] Added column '{$colName}' to project_issues.\n";
        } catch (\Throwable $e) {
            echo "[WARN] Error adding {$colName}: " . $e->getMessage() . "\n";
        }
    } else {
        echo "[INFO] Column '{$colName}' already exists.\n";
    }
}

// 2. Modify issue_type enum to support BACKLOG
try {
    $pdo->exec("ALTER TABLE project_issues MODIFY COLUMN issue_type ENUM('BACKLOG', 'TASK', 'BUG', 'TESTCASE', 'STORY', 'EPIC') NOT NULL DEFAULT 'TASK'");
    echo "[OK] Updated issue_type ENUM to include 'BACKLOG'.\n";
} catch (\Throwable $e) {
    echo "[WARN] Notice updating ENUM: " . $e->getMessage() . "\n";
}

// 3. Add foreign key fk_pi_parent if not exists
try {
    $pdo->exec("ALTER TABLE project_issues ADD CONSTRAINT fk_pi_parent FOREIGN KEY (parent_id) REFERENCES project_issues(id) ON DELETE SET NULL");
    echo "[OK] Added foreign key fk_pi_parent.\n";
} catch (\Throwable $e) {
    echo "[INFO] Foreign key fk_pi_parent already present or constraint notice: " . $e->getMessage() . "\n";
}

// 4. Create project_issue_comments table for Discussion in Image 2
$createCommentsSql = "
CREATE TABLE IF NOT EXISTS `project_issue_comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `issue_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `content` LONGTEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pic_issue` (`issue_id`),
    INDEX `idx_pic_user` (`user_id`),
    CONSTRAINT `fk_pic_issue` FOREIGN KEY (`issue_id`) REFERENCES `project_issues` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pic_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";
$pdo->exec($createCommentsSql);
echo "[OK] Table 'project_issue_comments' ready.\n";

// 5. Seed / Ensure Backlog parent items and establish Parent-Child Hierarchy for existing issues
$checkPrj = $pdo->query("SELECT id FROM projects ORDER BY id ASC LIMIT 1");
$prjId = (int)$checkPrj->fetchColumn();

if ($prjId > 0) {
    // Check if we have BACKLOG items
    $checkBacklog = $pdo->prepare("SELECT id, summary FROM project_issues WHERE project_id = :pid AND (issue_type = 'BACKLOG' OR (issue_type = 'STORY' AND sprint_status = 'BACKLOG'))");
    $checkBacklog->execute(['pid' => $prjId]);
    $backlogs = $checkBacklog->fetchAll(PDO::FETCH_ASSOC);

    if (empty($backlogs)) {
        // Create 2 Backlog parent items
        $maxStmt = $pdo->prepare("SELECT MAX(issue_number) FROM project_issues WHERE project_id = :pid");
        $maxStmt->execute(['pid' => $prjId]);
        $maxNum = (int)$maxStmt->fetchColumn();

        $prjKeyStmt = $pdo->prepare("SELECT project_key FROM projects WHERE id = :pid");
        $prjKeyStmt->execute(['pid' => $prjId]);
        $key = $prjKeyStmt->fetchColumn() ?: 'PRJ';

        $leadUserStmt = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
        $leadId = (int)$leadUserStmt->fetchColumn() ?: 1;

        $b1Num = $maxNum + 1;
        $b1Key = $key . '-' . $b1Num;
        $stmt1 = $pdo->prepare("
            INSERT INTO project_issues (
                project_id, issue_key, issue_number, issue_type, summary, description,
                status, priority, reporter_id, assignee_id, sprint_status, component, story_points,
                original_estimate_hours, remaining_hours, completed_hours, created_at
            ) VALUES (
                :pid, :key, :num, 'BACKLOG', 'Hệ thống Đặt Tour & Thanh toán Trực tuyến 2026',
                'Hạng mục Backlog gốc quản lý toàn bộ tính năng và luồng thanh toán đa kênh.',
                'TO_DO', 'HIGH', :rep, :rep, 'BACKLOG', 'BILLING', 13, 80, 60, 20, NOW()
            )
        ");
        $stmt1->execute(['pid' => $prjId, 'key' => $b1Key, 'num' => $b1Num, 'rep' => $leadId]);
        $b1Id = (int)$pdo->lastInsertId();

        $b2Num = $maxNum + 2;
        $b2Key = $key . '-' . $b2Num;
        $stmt2 = $pdo->prepare("
            INSERT INTO project_issues (
                project_id, issue_key, issue_number, issue_type, summary, description,
                status, priority, reporter_id, assignee_id, sprint_status, component, story_points,
                original_estimate_hours, remaining_hours, completed_hours, created_at
            ) VALUES (
                :pid, :key, :num, 'BACKLOG', 'Phân hệ Xác thực & Quản trị Hồ sơ Người dùng (IAM)',
                'Hạng mục Backlog gốc về tài khoản người dùng, 2FA, và phân quyền nhóm.',
                'IN_PROGRESS', 'HIGHEST', :rep, :rep, 'BACKLOG', 'ACCOUNTS', 21, 120, 45, 75, NOW()
            )
        ");
        $stmt2->execute(['pid' => $prjId, 'key' => $b2Key, 'num' => $b2Num, 'rep' => $leadId]);
        $b2Id = (int)$pdo->lastInsertId();

        echo "[OK] Seeded 2 master Backlog items (ID: {$b1Id}, {$b2Id}).\n";
    } else {
        $b1Id = (int)$backlogs[0]['id'];
        $b2Id = isset($backlogs[1]) ? (int)$backlogs[1]['id'] : $b1Id;
    }

    // Now link Tasks & Bugs to Backlogs
    $linkTasks = $pdo->prepare("
        UPDATE project_issues 
        SET parent_id = :bid 
        WHERE project_id = :pid AND issue_type IN ('TASK', 'BUG', 'STORY') AND id != :bid AND parent_id IS NULL
        LIMIT 6
    ");
    $linkTasks->execute(['bid' => $b1Id, 'pid' => $prjId]);

    $linkTasks2 = $pdo->prepare("
        UPDATE project_issues 
        SET parent_id = :bid 
        WHERE project_id = :pid AND issue_type IN ('TASK', 'BUG', 'STORY') AND id != :bid AND parent_id IS NULL
        LIMIT 6
    ");
    $linkTasks2->execute(['bid' => $b2Id, 'pid' => $prjId]);

    // Find some Bugs and Tasks to be parents of TestCases
    $bugStmt = $pdo->prepare("SELECT id FROM project_issues WHERE project_id = :pid AND issue_type = 'BUG' LIMIT 2");
    $bugStmt->execute(['pid' => $prjId]);
    $bugIds = $bugStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($bugIds)) {
        $parentBugId = $bugIds[0];
        $linkTest = $pdo->prepare("
            UPDATE project_issues 
            SET parent_id = :pbug 
            WHERE project_id = :pid AND issue_type = 'TESTCASE' AND parent_id IS NULL
            LIMIT 4
        ");
        $linkTest->execute(['pbug' => $parentBugId, 'pid' => $prjId]);
    }

    echo "[OK] Linked parent-child hierarchy for sample issues.\n";
}

echo "Migration finished successfully!\n";

