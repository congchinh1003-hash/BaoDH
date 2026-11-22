-- ====================================================================
-- IT SERVICE DESK & ASSET MANAGEMENT SYSTEM
-- PHASE 3: CSAT RATING & ADVANCED REPORTS SCHEMA
-- ====================================================================

-- 1. ĐÁNH GIÁ MỨC ĐỘ HÀI LÒNG CỦA NGƯỜI DÙNG (RULE 49)
CREATE TABLE IF NOT EXISTS `ticket_ratings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `score` INT NOT NULL CHECK (`score` BETWEEN 1 AND 5),
    `comment` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tr_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dữ liệu đánh giá mẫu cho các ticket đã đóng
INSERT INTO `ticket_ratings` (`ticket_id`, `user_id`, `score`, `comment`) VALUES
(2, 6, 5, 'Kỹ thuật viên hỗ trợ rất nhanh và nhiệt tình, máy in đã hoạt động tốt.'),
(3, 7, 4, 'Xử lý ổn thỏa, thời gian phản hồi nhanh.')
ON DUPLICATE KEY UPDATE `score`=VALUES(`score`);

