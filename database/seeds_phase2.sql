-- ====================================================================
-- IT SERVICE DESK & ASSET MANAGEMENT SYSTEM
-- PHASE 2: SAMPLE DATA / SEEDS
-- ====================================================================

-- 1. LOẠI TÀI SẢN
INSERT INTO `asset_categories` (`id`, `code`, `name`, `description`, `icon`) VALUES
(1, 'LAPTOP', 'Máy tính xách tay (Laptop)', 'Laptop cấp cho nhân viên làm việc văn phòng và di động', 'bi-laptop'),
(2, 'DESKTOP', 'Máy tính để bàn (PC)', 'Máy trạm, PC văn phòng cố định', 'bi-pc-display'),
(3, 'MONITOR', 'Màn hình hiển thị', 'Màn hình rời 24-27 inch', 'bi-display'),
(4, 'PRINTER', 'Máy in / Máy quét', 'Máy in mạng, photocopy văn phòng', 'bi-printer'),
(5, 'NETWORK', 'Thiết bị Mạng', 'Switch, Router, Access Point WiFi', 'bi-router'),
(6, 'SERVER', 'Máy chủ hệ thống', 'Máy chủ vật lý, Rack Server phòng Data Center', 'bi-hdd-rack'),
(7, 'SOFTWARE', 'Bản quyền Phần mềm', 'License Windows, Microsoft 365, AutoCAD...', 'bi-key')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. TÀI SẢN MẪU
INSERT INTO `assets` (`id`, `asset_code`, `name`, `category_id`, `manufacturer`, `model`, `serial_number`, `purchase_date`, `warranty_expire`, `status`, `department`, `location`, `current_user_id`, `specifications`, `notes`) VALUES
(1, 'AST-LAP-001', 'Dell Latitude 5420 i5', 1, 'Dell', 'Latitude 5420', 'SN-DL5420-9981', '2025-01-15', '2028-01-15', 'IN_USE', 'Kinh doanh', 'Tầng 3 - Phòng Kinh doanh', 6, 'CPU: Core i5 1145G7 | RAM: 16GB DDR4 | SSD: 512GB NVMe | Màn hình: 14 inch FHD', 'Cấp cho Đỗ Mai Phương khi nhận việc'),
(2, 'AST-LAP-002', 'MacBook Pro 14 M2 Pro', 1, 'Apple', 'MacBook Pro 14 2023', 'SN-APMBP-7721', '2024-06-10', '2026-06-10', 'IN_USE', 'Marketing', 'Tầng 4 - Phòng Marketing', 7, 'CPU: Apple M2 Pro 10-core | RAM: 16GB | SSD: 512GB | Retina XDR', 'Cấp cho Vũ Đình Trọng làm đồ họa'),
(3, 'AST-PC-001', 'HP ProDesk 400 G7 MT', 2, 'HP', 'ProDesk 400 G7', 'SN-HPPD-1029', '2023-11-20', '2026-11-20', 'IN_USE', 'Kế toán', 'Tầng 2 - Phòng Kế toán', NULL, 'CPU: Core i7 10700 | RAM: 16GB | SSD: 256GB + HDD 1TB', 'Máy để bàn phòng Kế toán dùng chung'),
(4, 'AST-MON-001', 'Dell UltraSharp 24 U2422H', 3, 'Dell', 'U2422H', 'SN-DU2422-5532', '2025-01-15', '2028-01-15', 'IN_USE', 'Kinh doanh', 'Tầng 3 - Bàn 12', 6, '24 inch IPS, FHD 1920x1080, 100% sRGB, Type-C, DP, HDMI', 'Màn hình phụ cấp kèm laptop của Mai Phương'),
(5, 'AST-PRN-001', 'Canon LBP 2900 Laser', 4, 'Canon', 'LBP 2900', 'SN-CN2900-4411', '2023-05-10', '2025-05-10', 'IN_USE', 'Hành chính', 'Tầng 2 - Sảnh chung', NULL, 'In laser đen trắng A4, cổng USB + Print Server LAN', 'Máy in dùng chung toàn tầng 2'),
(6, 'AST-LAP-003', 'ThinkPad T14 Gen 3 i5', 1, 'Lenovo', 'ThinkPad T14 Gen 3', 'SN-LNT14-3321', '2024-08-15', '2027-08-15', 'IN_STOCK', 'Kho IT', 'Kho IT - Kệ A2', NULL, 'CPU: Core i5 1240P | RAM: 16GB | SSD: 512GB', 'Máy dự phòng trong kho IT'),
(7, 'AST-NET-001', 'Cisco Catalyst 2960X 48P', 5, 'Cisco', 'WS-C2960X-48TD-L', 'SN-CS2960-0091', '2022-03-01', '2027-03-01', 'IN_USE', 'Hạ tầng IT', 'Server Room Tầng 1 - Rack 1', NULL, 'Switch Layer 2, 48 port Gigabit PoE+, 2 x 10G SFP+', 'Switch mạng chính tòa nhà'),
(8, 'AST-LAP-004', 'Asus ExpertBook B1', 1, 'Asus', 'B1400CEAE', 'SN-ASB1-8842', '2023-02-10', '2025-02-10', 'REPAIR', 'Kho IT', 'Trung tâm bảo hành Asus', NULL, 'CPU: Core i3 1115G4 | RAM: 8GB | SSD: 256GB', 'Đang gửi hãng thay quạt tản nhiệt và bàn phím')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. LỊCH SỬ BÀN GIAO MẪU
INSERT INTO `asset_handovers` (`id`, `asset_id`, `action_type`, `from_user_id`, `to_user_id`, `from_location`, `to_location`, `reason`, `performed_by`, `handover_date`) VALUES
(1, 1, 'HANDOVER', NULL, 6, 'Kho IT', 'Tầng 3 - Phòng Kinh doanh', 'Bàn giao laptop mới cho nhân viên Đỗ Mai Phương khi thử việc', 1, '2025-01-16 09:00:00'),
(2, 4, 'HANDOVER', NULL, 6, 'Kho IT', 'Tầng 3 - Bàn 12', 'Cấp bổ sung màn hình rời hỗ trợ nhập liệu', 1, '2025-01-16 09:30:00'),
(3, 2, 'HANDOVER', NULL, 7, 'Kho IT', 'Tầng 4 - Phòng Marketing', 'Bàn giao máy đồ họa cho nhân viên Vũ Đình Trọng', 1, '2024-06-11 10:00:00'),
(4, 8, 'RECALL', 7, NULL, 'Tầng 4', 'Kho IT', 'Thu hồi laptop cũ của Trọng trước khi nâng cấp máy mới', 1, '2024-06-10 16:30:00')
ON DUPLICATE KEY UPDATE `reason`=VALUES(`reason`);

-- 4. LỊCH SỬ SỬA CHỮA MẪU
INSERT INTO `asset_repairs` (`id`, `asset_id`, `repair_date`, `issue_description`, `root_cause`, `solution`, `replaced_parts`, `cost`, `vendor`, `performed_by`, `notes`) VALUES
(1, 8, '2026-09-15', 'Máy kêu to, quạt phát tiếng rít, liệt phím cách (Spacebar)', 'Bụi bẩn bám kẹt quạt, bàn phím bị đổ nước ngọt nhẹ', 'Gửi hãng bảo hành thay quạt và cụm phím mới', 'Quạt tản nhiệt Asus B1, Bàn phím LED', 850000.00, 'Trung tâm Bảo hành Asus Việt Nam', 3, 'Chờ linh kiện hãng báo về')
ON DUPLICATE KEY UPDATE `issue_description`=VALUES(`issue_description`);

-- 5. LIÊN KẾT TÀI SẢN VỚI TICKET MẪU
INSERT INTO `ticket_assets` (`ticket_id`, `asset_id`) VALUES
(1, 1),
(5, 1)
ON DUPLICATE KEY UPDATE `asset_id`=VALUES(`asset_id`);

