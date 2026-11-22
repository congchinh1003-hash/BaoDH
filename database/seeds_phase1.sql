-- ====================================================================
-- IT SERVICE DESK & ASSET MANAGEMENT SYSTEM
-- PHASE 1: SEED DATA
-- Default password for all seed accounts: 123456
-- ====================================================================

-- 1. ROLES
INSERT IGNORE INTO `roles` (`id`, `code`, `name`, `description`) VALUES
(1, 'ADMIN', 'Quản trị hệ thống', 'Toàn quyền cấu hình, tài khoản, SLA và danh mục'),
(2, 'IT_MANAGER', 'IT Manager / Leader', 'Quản lý phân công, điều phối nhóm, giám sát SLA và báo cáo'),
(3, 'IT_STAFF', 'Nhân viên IT', 'Tiếp nhận, xử lý, comment nội bộ và hoàn thành ticket'),
(4, 'USER', 'Người dùng nội bộ', 'Tạo yêu cầu hỗ trợ, theo dõi tiến độ và nghiệm thu');

-- 2. USERS (Mật khẩu mặc định: 123456)
INSERT IGNORE INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `department`, `is_active`) VALUES
(1, 'admin', 'admin@itdesk.local', '$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76', 'System Administrator', '0901000001', 'IT Department', 1),
(2, 'manager', 'manager@itdesk.local', '$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76', 'Trần Văn Hoàng (IT Leader)', '0901000002', 'IT Management', 1),
(3, 'it_helpdesk', 'helpdesk@itdesk.local', '$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76', 'Lê Quốc Bảo (Helpdesk)', '0901000003', 'IT Helpdesk', 1),
(4, 'it_network', 'network@itdesk.local', '$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76', 'Nguyễn Minh Tuấn (Network)', '0901000004', 'IT Network', 1),
(5, 'it_app', 'app@itdesk.local', '$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76', 'Phạm Hồng Phúc (Application)', '0901000005', 'IT Application', 1),
(6, 'user1', 'user1@company.local', '$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76', 'Đỗ Mai Phương', '0902000001', 'Phòng Kế Toán', 1),
(7, 'user2', 'user2@company.local', '$2y$10$9e9GylE6bazFxwbWe74BEeZgCCpEsx.ro7A0xQucQ/hDpUivgoo76', 'Vũ Đình Trọng', '0902000002', 'Phòng Nhân Sự', 1);

-- 3. USER_ROLES
INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1), -- admin -> ADMIN
(2, 2), -- manager -> IT_MANAGER
(3, 3), -- it_helpdesk -> IT_STAFF
(4, 3), -- it_network -> IT_STAFF
(5, 3), -- it_app -> IT_STAFF
(6, 4), -- user1 -> USER
(7, 4); -- user2 -> USER

-- 4. IT_GROUPS
INSERT IGNORE INTO `it_groups` (`id`, `code`, `name`, `description`, `leader_id`, `is_active`) VALUES
(1, 'HELPDESK', 'IT Helpdesk', 'Hỗ trợ kỹ thuật người dùng cuối, sự cố văn phòng và tài khoản', 2, 1),
(2, 'NETWORK', 'IT Network & Hạ tầng', 'Quản lý mạng LAN, WiFi, VPN, Firewall, Router', 2, 1),
(3, 'SYSTEM', 'IT Hệ thống máy chủ', 'Quản trị Servers, Cloud, Virtualization, Backup', 2, 1),
(4, 'APPLICATION', 'IT Phần mềm & Ứng dụng', 'Bảo trì ERP, CRM, HRM, Website nội bộ', 2, 1),
(5, 'HARDWARE', 'IT Phần cứng & Thiết bị', 'Bảo dưỡng, sửa chữa máy tính, máy in, scan', 2, 1);

-- 5. IT_GROUP_MEMBERS
INSERT IGNORE INTO `it_group_members` (`group_id`, `user_id`, `is_primary`) VALUES
(1, 2, 0), -- Manager trong Helpdesk
(1, 3, 1), -- it_helpdesk trong Helpdesk
(2, 4, 1), -- it_network trong Network
(4, 5, 1); -- it_app trong Application

-- 6. TICKET_CATEGORIES
INSERT IGNORE INTO `ticket_categories` (`id`, `code`, `name`, `description`, `default_group_id`, `is_active`) VALUES
(1, 'NETWORK', 'Mạng & Kết nối', 'Các sự cố WiFi, VPN, mạng Internet, chập chờn đường truyền', 2, 1),
(2, 'HARDWARE', 'Thiết bị & Phần cứng', 'Hỏng chuột, bàn phím, màn hình, máy in, PC/Laptop không khởi động', 5, 1),
(3, 'SOFTWARE', 'Phần mềm ứng dụng', 'Lỗi phần mềm kế toán, ERP, HRM, Office, cài đặt ứng dụng', 4, 1),
(4, 'ACCOUNT', 'Tài khoản & Quyền hạn', 'Cấp mới tài khoản, reset mật khẩu, mở khóa email, cấp quyền truy cập', 1, 1);

-- 7. TICKET_SERVICES
INSERT IGNORE INTO `ticket_services` (`id`, `category_id`, `code`, `name`, `description`, `is_active`) VALUES
-- Network
(1, 1, 'VPN', 'Kết nối VPN từ xa', 'Cấp quyền hoặc xử lý lỗi không truy cập được VPN', 1),
(2, 1, 'WIFI', 'Sự cố WiFi văn phòng', 'Mất sóng WiFi, không xác thực được tài khoản WiFi', 1),
(3, 1, 'LAN', 'Mạng có dây LAN', 'Dây mạng đứt, không nhận IP mạng nội bộ', 1),
-- Hardware
(4, 2, 'LAPTOP', 'Sửa chữa Laptop / PC', 'Máy tính chậm, đơ màn hình xanh, không lên nguồn', 1),
(5, 2, 'PRINTER', 'Máy in / Máy scan', 'Kẹt giấy, không in được tài liệu, chia sẻ máy in', 1),
(6, 2, 'MONITOR', 'Màn hình hiển thị', 'Màn hình nhấp nháy, sọc màu, không lên tín hiệu', 1),
-- Software
(7, 3, 'ERP', 'Hệ thống ERP', 'Lỗi phân hệ kế toán, kho vận, bán hàng trên ERP', 1),
(8, 3, 'EMAIL', 'Email doanh nghiệp', 'Hộp thư đầy, không gửi nhận được thư', 1),
(9, 3, 'OFFICE', 'Microsoft Office / OS', 'Lỗi Windows, Excel, Word, bản quyền', 1),
-- Account
(10, 4, 'PASS_RESET', 'Khôi phục mật khẩu', 'Quên mật khẩu máy tính, email hoặc ứng dụng', 1),
(11, 4, 'NEW_USER', 'Cấp tài khoản nhân sự mới', 'Tạo email, tài khoản AD, cấp phân quyền theo phòng ban', 1);

-- 8. TICKET_PRIORITIES (3 mức độ mặc định quy định trong tài liệu)
INSERT IGNORE INTO `ticket_priorities` (`id`, `code`, `name`, `color`, `level`, `is_active`) VALUES
(1, 'NORMAL', 'Thường', '#0ea5e9', 1, 1),
(2, 'HIGH', 'Ưu tiên', '#f59e0b', 2, 1),
(3, 'URGENT', 'Khẩn cấp', '#ef4444', 3, 1);

-- 9. SLA_CONFIGS (Quy tắc 2 ngày - 1 ngày - 3 giờ)
INSERT IGNORE INTO `sla_configs` (`id`, `priority_id`, `name`, `resolution_time`, `resolution_unit`, `warning_threshold_percent`, `critical_threshold_percent`, `is_active`) VALUES
(1, 1, 'SLA Mức Thường', 2, 'DAY', 70, 90, 1),     -- 2 ngày (48 giờ)
(2, 2, 'SLA Mức Ưu tiên', 1, 'DAY', 70, 90, 1),    -- 1 ngày (24 giờ)
(3, 3, 'SLA Mức Khẩn cấp', 3, 'HOUR', 70, 90, 1);  -- 3 giờ

-- 10. WORKFLOW_STATUSES
INSERT IGNORE INTO `workflow_statuses` (`id`, `code`, `name`, `badge_color`, `sort_order`, `is_closed_state`, `pauses_sla`) VALUES
(1, 'NEW', 'Mới tạo', '#64748b', 1, 0, 0),
(2, 'ASSIGNED', 'Đã phân công', '#3b82f6', 2, 0, 0),
(3, 'IN_PROGRESS', 'Đang xử lý', '#0284c7', 3, 0, 0),
(4, 'WAITING_USER', 'Chờ phản hồi', '#f59e0b', 4, 0, 1),
(5, 'WAITING_VENDOR', 'Chờ đối tác', '#8b5cf6', 5, 0, 0),
(6, 'RESOLVED', 'Đã xử lý xong', '#10b981', 6, 0, 0),
(7, 'CLOSED', 'Đã đóng (Nghiệm thu)', '#059669', 7, 1, 0),
(8, 'REOPENED', 'Mở lại yêu cầu', '#ec4899', 8, 0, 0),
(9, 'CANCELLED', 'Đã hủy', '#94a3b8', 9, 1, 0),
(10, 'DUPLICATE', 'Trùng lặp', '#6b7280', 10, 1, 0);
