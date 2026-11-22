-- ====================================================================
-- JIRA SOFTWARE & PROJECT MANAGEMENT EXTENSION SCHEMA
-- 1. PROJECTS (Quản lý Dự án Agile / Software / Service Desk)
-- 2. PROJECT_MEMBERS (Thành viên & Phân quyền dự án)
-- 3. PROJECT_ISSUES (Công việc dự án: Task, Bug, TestCase, Story, Epic)
-- ====================================================================

-- 1. BẢNG DỰ ÁN (PROJECTS)
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_key` VARCHAR(10) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `project_type` ENUM('SOFTWARE', 'BUSINESS', 'SERVICE_DESK') NOT NULL DEFAULT 'SOFTWARE',
    `lead_id` INT NOT NULL,
    `avatar_icon` VARCHAR(50) NOT NULL DEFAULT 'bi-rocket-takeoff-fill',
    `avatar_bg` VARCHAR(20) NOT NULL DEFAULT '#6554C0',
    `status` ENUM('ACTIVE', 'ARCHIVED') NOT NULL DEFAULT 'ACTIVE',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_prj_key` (`project_key`),
    INDEX `idx_prj_lead` (`lead_id`),
    CONSTRAINT `fk_prj_lead` FOREIGN KEY (`lead_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. THÀNH VIÊN DỰ ÁN (PROJECT_MEMBERS)
CREATE TABLE IF NOT EXISTS `project_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role` ENUM('ADMIN', 'MEMBER', 'VIEWER') NOT NULL DEFAULT 'MEMBER',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_prj_user` (`project_id`, `user_id`),
    CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CÔNG VIỆC DỰ ÁN (PROJECT_ISSUES - TASK, BUG, TESTCASE, STORY, EPIC)
CREATE TABLE IF NOT EXISTS `project_issues` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `issue_key` VARCHAR(30) NOT NULL UNIQUE,
    `issue_number` INT NOT NULL,
    `issue_type` ENUM('TASK', 'BUG', 'TESTCASE', 'STORY', 'EPIC') NOT NULL DEFAULT 'TASK',
    `summary` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NULL,
    `status` ENUM('TO_DO', 'IN_PROGRESS', 'IN_REVIEW', 'DONE') NOT NULL DEFAULT 'TO_DO',
    `priority` ENUM('HIGHEST', 'HIGH', 'MEDIUM', 'LOW', 'LOWEST') NOT NULL DEFAULT 'MEDIUM',
    `reporter_id` INT NOT NULL,
    `assignee_id` INT NULL,
    `sprint_status` ENUM('BACKLOG', 'ACTIVE_SPRINT', 'COMPLETED') NOT NULL DEFAULT 'ACTIVE_SPRINT',
    `component` VARCHAR(50) NULL,
    `story_points` INT NULL DEFAULT 1,
    `epic_id` INT NULL,
    
    -- Trường nghiệp vụ chuyên sâu cho TESTCASE
    `test_type` ENUM('MANUAL', 'AUTOMATED', 'REGRESSION', 'SMOKE') DEFAULT 'MANUAL',
    `test_steps` TEXT NULL,
    `test_expected_result` TEXT NULL,
    `test_execution_status` ENUM('UNTESTED', 'PASSED', 'FAILED', 'BLOCKED') DEFAULT 'UNTESTED',
    
    -- Trường nghiệp vụ chuyên sâu cho BUG
    `bug_severity` ENUM('BLOCKER', 'CRITICAL', 'MAJOR', 'MINOR', 'TRIVIAL') DEFAULT 'MAJOR',
    `steps_to_reproduce` TEXT NULL,
    `actual_result` TEXT NULL,
    `expected_result` TEXT NULL,
    `environment` VARCHAR(255) NULL,
    
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pi_prj` (`project_id`),
    INDEX `idx_pi_status` (`status`),
    INDEX `idx_pi_type` (`issue_type`),
    INDEX `idx_pi_assignee` (`assignee_id`),
    INDEX `idx_pi_sprint` (`sprint_status`),
    CONSTRAINT `fk_pi_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pi_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`),
    CONSTRAINT `fk_pi_assignee` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

