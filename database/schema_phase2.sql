-- ====================================================================
-- IT SERVICE DESK & ASSET MANAGEMENT SYSTEM
-- PHASE 2: IT ASSET MANAGEMENT (ITAM) SCHEMA
-- ====================================================================

-- 1. DANH MỤC LOẠI TÀI SẢN
CREATE TABLE IF NOT EXISTS `asset_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `icon` VARCHAR(50) DEFAULT 'bi-laptop',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. HỒ SƠ TÀI SẢN (ASSETS)
CREATE TABLE IF NOT EXISTS `assets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asset_code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `category_id` INT NOT NULL,
    `manufacturer` VARCHAR(100) NULL,
    `model` VARCHAR(100) NULL,
    `serial_number` VARCHAR(100) NULL,
    `purchase_date` DATE NULL,
    `warranty_expire` DATE NULL,
    `status` ENUM('IN_STOCK', 'IN_USE', 'REPAIR', 'LOST', 'DAMAGED', 'LIQUIDATED', 'DISPOSED') DEFAULT 'IN_STOCK',
    `department` VARCHAR(100) NULL,
    `location` VARCHAR(150) NULL,
    `current_user_id` INT NULL,
    `specifications` TEXT NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_asset_status` (`status`),
    INDEX `idx_asset_user` (`current_user_id`),
    CONSTRAINT `fk_asset_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`),
    CONSTRAINT `fk_asset_user` FOREIGN KEY (`current_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. LỊCH SỬ BÀN GIAO / THU HỒI / ĐIỀU CHUYỂN (RULE 53 - KHÔNG GHI ĐÈ)
CREATE TABLE IF NOT EXISTS `asset_handovers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asset_id` INT NOT NULL,
    `action_type` ENUM('HANDOVER', 'RECALL', 'TRANSFER', 'DISPOSAL', 'STATUS_CHANGE') NOT NULL,
    `from_user_id` INT NULL,
    `to_user_id` INT NULL,
    `from_location` VARCHAR(150) NULL,
    `to_location` VARCHAR(150) NULL,
    `reason` VARCHAR(255) NULL,
    `performed_by` INT NOT NULL,
    `handover_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_hnd_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_hnd_from_user` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_hnd_to_user` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_hnd_performer` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. LỊCH SỬ SỬA CHỮA / BẢO TRÌ (RULE 54)
CREATE TABLE IF NOT EXISTS `asset_repairs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asset_id` INT NOT NULL,
    `repair_date` DATE NOT NULL,
    `issue_description` TEXT NOT NULL,
    `root_cause` TEXT NULL,
    `solution` TEXT NULL,
    `replaced_parts` TEXT NULL,
    `cost` DECIMAL(15, 2) DEFAULT 0,
    `vendor` VARCHAR(150) NULL,
    `performed_by` INT NOT NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_rep_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rep_performer` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. LIÊN KẾT TÀI SẢN VỚI TICKET (RULE 55)
CREATE TABLE IF NOT EXISTS `ticket_assets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `asset_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_ticket_asset` (`ticket_id`, `asset_id`),
    CONSTRAINT `fk_ta_ticket_rel` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ta_asset_rel` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

