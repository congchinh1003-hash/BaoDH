-- ====================================================================
-- ITIL & ITSM ADVANCED SUITE SCHEMA
-- 1. TICKETS EXTENSION (Classification, Impact x Urgency, Tier, Approval)
-- 2. APPROVAL WORKFLOW (Single & Multi-step)
-- 3. PROBLEM MANAGEMENT & RCA (KEDB)
-- 4. CHANGE & RELEASE MANAGEMENT (RFC, Rollback, DEV->PROD, CAB)
-- ====================================================================

-- 1. EXTEND TICKETS TABLE
ALTER TABLE `tickets` 
ADD COLUMN IF NOT EXISTS `ticket_type` ENUM('INCIDENT', 'SERVICE_REQUEST', 'PROBLEM', 'CHANGE') NOT NULL DEFAULT 'INCIDENT' AFTER `ticket_code`,
ADD COLUMN IF NOT EXISTS `impact` ENUM('HIGH', 'MEDIUM', 'LOW') NOT NULL DEFAULT 'MEDIUM' AFTER `priority_id`,
ADD COLUMN IF NOT EXISTS `urgency` ENUM('HIGH', 'MEDIUM', 'LOW') NOT NULL DEFAULT 'MEDIUM' AFTER `impact`,
ADD COLUMN IF NOT EXISTS `support_tier` ENUM('L1', 'L2', 'L3', 'VENDOR') NOT NULL DEFAULT 'L1' AFTER `urgency`,
ADD COLUMN IF NOT EXISTS `approval_status` ENUM('NONE', 'PENDING', 'APPROVED', 'REJECTED', 'RETURNED') NOT NULL DEFAULT 'NONE' AFTER `support_tier`,
ADD COLUMN IF NOT EXISTS `approved_by` INT NULL AFTER `approval_status`,
ADD COLUMN IF NOT EXISTS `approved_at` DATETIME NULL AFTER `approved_by`,
ADD COLUMN IF NOT EXISTS `approval_note` TEXT NULL AFTER `approved_at`,
ADD COLUMN IF NOT EXISTS `problem_id` INT NULL AFTER `approval_note`,
ADD COLUMN IF NOT EXISTS `change_id` INT NULL AFTER `problem_id`;

-- 2. APPROVAL WORKFLOW TABLE
CREATE TABLE IF NOT EXISTS `ticket_approvals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NULL,
    `change_id` INT NULL,
    `approver_id` INT NOT NULL,
    `step_name` VARCHAR(100) NOT NULL DEFAULT 'Quản lý phê duyệt',
    `status` ENUM('PENDING', 'APPROVED', 'REJECTED', 'RETURNED') NOT NULL DEFAULT 'PENDING',
    `comment` TEXT NULL,
    `decided_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_appr_ticket` (`ticket_id`),
    INDEX `idx_appr_change` (`change_id`),
    INDEX `idx_appr_user` (`approver_id`),
    CONSTRAINT `fk_appr_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_appr_user` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PROBLEM MANAGEMENT & RCA (KEDB)
CREATE TABLE IF NOT EXISTS `problems` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `problem_code` VARCHAR(30) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category_id` INT NOT NULL,
    `priority_id` INT NOT NULL,
    `status` ENUM('LOGGED', 'INVESTIGATING', 'WORKAROUND_FOUND', 'KNOWN_ERROR', 'RESOLVED', 'CLOSED') NOT NULL DEFAULT 'LOGGED',
    `root_cause` TEXT NULL,
    `workaround` TEXT NULL,
    `permanent_solution` TEXT NULL,
    `is_known_error` TINYINT(1) DEFAULT 0,
    `assigned_to` INT NULL,
    `created_by` INT NOT NULL,
    `resolved_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_prb_status` (`status`),
    INDEX `idx_prb_assignee` (`assigned_to`),
    CONSTRAINT `fk_prb_cat` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`),
    CONSTRAINT `fk_prb_pri` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`),
    CONSTRAINT `fk_prb_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_prb_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `problem_incidents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `problem_id` INT NOT NULL,
    `ticket_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_problem_ticket` (`problem_id`, `ticket_id`),
    INDEX `idx_pi_ticket` (`ticket_id`),
    CONSTRAINT `fk_pi_problem` FOREIGN KEY (`problem_id`) REFERENCES `problems` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pi_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. CHANGE & RELEASE MANAGEMENT (RFC - REQUEST FOR CHANGE)
CREATE TABLE IF NOT EXISTS `change_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `change_code` VARCHAR(30) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `reason` TEXT NOT NULL,
    `change_type` ENUM('STANDARD', 'NORMAL', 'EMERGENCY') NOT NULL DEFAULT 'NORMAL',
    `risk_level` ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') NOT NULL DEFAULT 'MEDIUM',
    `environment_stage` ENUM('DEV', 'TEST', 'MIG_UAT', 'RELEASE_PROD') NOT NULL DEFAULT 'DEV',
    `status` ENUM('DRAFT', 'SUBMITTED', 'CAB_APPROVAL', 'SCHEDULED', 'IMPLEMENTING', 'TESTING_UAT', 'RELEASE_PROD', 'SUCCESSFUL', 'FAILED', 'ROLLED_BACK', 'CANCELLED') NOT NULL DEFAULT 'DRAFT',
    `implementation_plan` TEXT NULL,
    `rollback_plan` TEXT NULL,
    `test_plan` TEXT NULL,
    `scheduled_start` DATETIME NULL,
    `scheduled_end` DATETIME NULL,
    `actual_start` DATETIME NULL,
    `actual_end` DATETIME NULL,
    `requested_by` INT NOT NULL,
    `assigned_to` INT NULL,
    `cab_approved_by` INT NULL,
    `cab_approved_at` DATETIME NULL,
    `cab_note` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cr_status` (`status`),
    INDEX `idx_cr_env` (`environment_stage`),
    INDEX `idx_cr_req` (`requested_by`),
    INDEX `idx_cr_ass` (`assigned_to`),
    CONSTRAINT `fk_cr_req` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
    CONSTRAINT `fk_cr_ass` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_cr_cab` FOREIGN KEY (`cab_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `change_tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `change_id` INT NOT NULL,
    `ticket_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_change_ticket` (`change_id`, `ticket_id`),
    INDEX `idx_ct_ticket` (`ticket_id`),
    CONSTRAINT `fk_ct_change` FOREIGN KEY (`change_id`) REFERENCES `change_requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ct_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. SEED CONFIGURATION ITEM CATEGORIES (CMDB EXPANSION)
INSERT IGNORE INTO `asset_categories` (`id`, `code`, `name`, `description`, `is_active`) VALUES
(6, 'SERVER', 'Máy chủ & Điện toán đám mây (Server/Cloud)', 'Hệ thống máy chủ vật lý, Virtual Machine, AWS, Azure, GCP', 1),
(7, 'DATABASE', 'Cơ sở dữ liệu (Database)', 'Cụm CSDL MySQL, Oracle, PostgreSQL, MS SQL Server', 1),
(8, 'APPLICATION', 'Ứng dụng & Dịch vụ số (Systems/Apps)', 'Hệ thống ERP, CRM, HRM, Website nội bộ, Mail Server', 1),
(9, 'NETWORK_DEVICE', 'Thiết bị mạng & An ninh (Network/Firewall)', 'Switch Core, Router, Firewall Fortinet/Cisco, VPN Gateway', 1);

