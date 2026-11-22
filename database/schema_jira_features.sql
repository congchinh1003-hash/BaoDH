-- ====================================================================
-- JIRA ADVANCED FEATURES SCHEMA: WORKLOGS, CHECKLISTS, ISSUE LINKS
-- ====================================================================

-- 1. BẢNG GHI NHẬN THỜI GIAN LÀM VIỆC (JIRA WORKLOG / TIME TRACKING)
CREATE TABLE IF NOT EXISTS `ticket_worklogs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `time_spent_minutes` INT NOT NULL,
    `time_spent_formatted` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `logged_date` DATE NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_twl_ticket` (`ticket_id`),
    INDEX `idx_twl_user` (`user_id`),
    CONSTRAINT `fk_twl_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_twl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG CHECKLIST ĐẦU VIỆC (SUB-TASKS / TECHNICAL CHECKLIST)
CREATE TABLE IF NOT EXISTS `ticket_checklists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `item_text` VARCHAR(255) NOT NULL,
    `is_completed` TINYINT(1) DEFAULT 0,
    `completed_by` INT NULL,
    `completed_at` DATETIME NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tcl_ticket` (`ticket_id`),
    CONSTRAINT `fk_tcl_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tcl_user` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG LIÊN KẾT ISSUE (JIRA ISSUE LINKING)
CREATE TABLE IF NOT EXISTS `ticket_links` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `source_ticket_id` INT NOT NULL,
    `target_ticket_id` INT NOT NULL,
    `link_type` ENUM('relates_to', 'blocks', 'is_blocked_by', 'causes') NOT NULL DEFAULT 'relates_to',
    `created_by` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_ticket_link` (`source_ticket_id`, `target_ticket_id`, `link_type`),
    INDEX `idx_tl_src` (`source_ticket_id`),
    INDEX `idx_tl_tgt` (`target_ticket_id`),
    CONSTRAINT `fk_tl_src` FOREIGN KEY (`source_ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tl_tgt` FOREIGN KEY (`target_ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tl_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

