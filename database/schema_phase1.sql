-- ====================================================================
-- IT SERVICE DESK & ASSET MANAGEMENT SYSTEM
-- PHASE 1: CORE MVP SCHEMA
-- Engine: MySQL 5.7+ / 8.0+ / MariaDB 10.3+
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `it_service_desk` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `it_service_desk`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ROLES
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. USERS
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `department` VARCHAR(100) NULL,
    `avatar` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. USER_ROLES (Many-to-Many RBAC)
CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. IT_GROUPS (Phòng ban/Nhóm IT: Helpdesk, Network, Application...)
CREATE TABLE IF NOT EXISTS `it_groups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `leader_id` INT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_group_leader` FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. IT_GROUP_MEMBERS (Thành viên nhóm IT)
CREATE TABLE IF NOT EXISTS `it_group_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `group_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `is_primary` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_group_user` (`group_id`, `user_id`),
    CONSTRAINT `fk_igm_group` FOREIGN KEY (`group_id`) REFERENCES `it_groups` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_igm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TICKET_CATEGORIES (Phân loại yêu cầu)
CREATE TABLE IF NOT EXISTS `ticket_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `default_group_id` INT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cat_default_group` FOREIGN KEY (`default_group_id`) REFERENCES `it_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TICKET_SERVICES (Dịch vụ trực thuộc Category)
CREATE TABLE IF NOT EXISTS `ticket_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_service_cat_code` (`category_id`, `code`),
    CONSTRAINT `fk_service_category` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TICKET_PRIORITIES (Mức độ ưu tiên)
CREATE TABLE IF NOT EXISTS `ticket_priorities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(50) NOT NULL,
    `color` VARCHAR(20) NOT NULL DEFAULT '#6c757d',
    `level` INT NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. SLA_CONFIGS (Cấu hình SLA theo Priority)
CREATE TABLE IF NOT EXISTS `sla_configs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `priority_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `resolution_time` INT NOT NULL,
    `resolution_unit` ENUM('HOUR', 'DAY') NOT NULL DEFAULT 'HOUR',
    `warning_threshold_percent` INT NOT NULL DEFAULT 70,
    `critical_threshold_percent` INT NOT NULL DEFAULT 90,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sla_priority` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. WORKFLOW_STATUSES (Trạng thái quy trình chuẩn)
CREATE TABLE IF NOT EXISTS `workflow_statuses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(50) NOT NULL,
    `badge_color` VARCHAR(20) NOT NULL DEFAULT '#6c757d',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_closed_state` TINYINT(1) DEFAULT 0,
    `pauses_sla` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. TICKETS (Bảng lõi Ticket)
CREATE TABLE IF NOT EXISTS `tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_code` VARCHAR(20) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `requester_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `service_id` INT NULL,
    `priority_id` INT NOT NULL,
    `group_id` INT NULL,
    `assignee_id` INT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'NEW',
    
    -- SLA Snapshot Fields
    `sla_id` INT NULL,
    `sla_value` INT NOT NULL DEFAULT 0,
    `sla_unit` VARCHAR(10) NOT NULL DEFAULT 'HOUR',
    `sla_started_at` DATETIME NULL,
    `sla_due_at` DATETIME NULL,
    `sla_status` ENUM('WITHIN_SLA', 'WARNING', 'CRITICAL', 'OVERDUE') DEFAULT 'WITHIN_SLA',
    `sla_paused_seconds` INT DEFAULT 0,
    `pause_started_at` DATETIME NULL,
    
    -- Resolution details
    `resolution` TEXT NULL,
    `root_cause` TEXT NULL,
    `solution` TEXT NULL,
    
    -- Life cycle metrics
    `reopen_count` INT DEFAULT 0,
    `reopen_reason` TEXT NULL,
    `cancelled_reason` TEXT NULL,
    `duplicate_of_id` INT NULL,
    `location` VARCHAR(255) NULL,
    `contact_info` VARCHAR(255) NULL,
    
    -- Timestamps
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `resolved_at` DATETIME NULL,
    `closed_at` DATETIME NULL,
    
    INDEX `idx_tickets_status` (`status`),
    INDEX `idx_tickets_requester` (`requester_id`),
    INDEX `idx_tickets_group` (`group_id`),
    INDEX `idx_tickets_assignee` (`assignee_id`),
    INDEX `idx_tickets_sla_due` (`sla_due_at`),
    INDEX `idx_tickets_sla_status` (`sla_status`),
    CONSTRAINT `fk_tk_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`),
    CONSTRAINT `fk_tk_category` FOREIGN KEY (`category_id`) REFERENCES `ticket_categories` (`id`),
    CONSTRAINT `fk_tk_service` FOREIGN KEY (`service_id`) REFERENCES `ticket_services` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tk_priority` FOREIGN KEY (`priority_id`) REFERENCES `ticket_priorities` (`id`),
    CONSTRAINT `fk_tk_group` FOREIGN KEY (`group_id`) REFERENCES `it_groups` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tk_assignee` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tk_sla` FOREIGN KEY (`sla_id`) REFERENCES `sla_configs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tk_duplicate` FOREIGN KEY (`duplicate_of_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. TICKET_ASSIGNMENTS (Lịch sử phân công)
CREATE TABLE IF NOT EXISTS `ticket_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `from_user_id` INT NULL,
    `to_user_id` INT NOT NULL,
    `assigned_by_user_id` INT NOT NULL,
    `reason` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ta_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ta_from_user` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ta_to_user` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`),
    CONSTRAINT `fk_ta_by_user` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. TICKET_TRANSFERS (Lịch sử chuyển nhóm IT)
CREATE TABLE IF NOT EXISTS `ticket_transfers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `from_group_id` INT NULL,
    `to_group_id` INT NOT NULL,
    `transferred_by_user_id` INT NOT NULL,
    `reason` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tt_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tt_from_group` FOREIGN KEY (`from_group_id`) REFERENCES `it_groups` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tt_to_group` FOREIGN KEY (`to_group_id`) REFERENCES `it_groups` (`id`),
    CONSTRAINT `fk_tt_by_user` FOREIGN KEY (`transferred_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. TICKET_SLA_PAUSES (Lịch sử các đợt tạm dừng SLA)
CREATE TABLE IF NOT EXISTS `ticket_sla_pauses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `status_trigger` VARCHAR(50) NOT NULL,
    `paused_at` DATETIME NOT NULL,
    `resumed_at` DATETIME NULL,
    `duration_seconds` INT DEFAULT 0,
    `reason` VARCHAR(255) NULL,
    `paused_by_user_id` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tsp_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tsp_user` FOREIGN KEY (`paused_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. TICKET_COMMENTS (Bình luận công khai & Ghi chú nội bộ)
CREATE TABLE IF NOT EXISTS `ticket_comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `is_internal` TINYINT(1) DEFAULT 0,
    `content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tc_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. TICKET_ATTACHMENTS (Metadata tệp đính kèm)
CREATE TABLE IF NOT EXISTS `ticket_attachments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `comment_id` INT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_att_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_att_comment` FOREIGN KEY (`comment_id`) REFERENCES `ticket_comments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_att_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. TICKET_HISTORY (Audit timeline chi tiết)
CREATE TABLE IF NOT EXISTS `ticket_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `field_changed` VARCHAR(50) NULL,
    `old_value` TEXT NULL,
    `new_value` TEXT NULL,
    `note` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_history_ticket` (`ticket_id`),
    CONSTRAINT `fk_th_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_th_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. TICKET_WATCHERS (Người theo dõi ticket)
CREATE TABLE IF NOT EXISTS `ticket_watchers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_watcher` (`ticket_id`, `user_id`),
    CONSTRAINT `fk_tw_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tw_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. NOTIFICATIONS (Thông báo in-app)
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `ticket_id` INT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` VARCHAR(50) DEFAULT 'info',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user_read` (`user_id`, `is_read`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notif_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. AUDIT_LOGS (Nhật ký kiểm toán toàn hệ thống)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `object_type` VARCHAR(50) NOT NULL,
    `object_id` INT NULL,
    `old_value` LONGTEXT NULL,
    `new_value` LONGTEXT NULL,
    `reason` VARCHAR(255) NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_obj` (`object_type`, `object_id`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

