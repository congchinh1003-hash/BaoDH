-- ====================================================================
-- IT SERVICE DESK & ASSET MANAGEMENT SYSTEM
-- PHASE 4 & 5: KNOWLEDGE BASE & AI TRIAGE SCHEMA
-- ====================================================================

-- 1. DANH MỤC BÀI VIẾT KHO TRI THỨC (KB CATEGORIES)
CREATE TABLE IF NOT EXISTS `kb_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `icon` VARCHAR(50) DEFAULT 'bi-book',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BÀI VIẾT HƯỚNG DẪN TỰ KHẮC PHỤC (KB ARTICLES - RULE 57)
CREATE TABLE IF NOT EXISTS `kb_articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `author_id` INT NOT NULL,
    `status` ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') DEFAULT 'PUBLISHED',
    `view_count` INT DEFAULT 0,
    `helpful_count` INT DEFAULT 0,
    `unhelpful_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT KEY `ft_kb_search` (`title`, `content`),
    CONSTRAINT `fk_kb_category` FOREIGN KEY (`category_id`) REFERENCES `kb_categories` (`id`),
    CONSTRAINT `fk_kb_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SEED DỮ LIỆU DANH MỤC KB
INSERT INTO `kb_categories` (`id`, `code`, `name`, `description`, `icon`) VALUES
(1, 'ACCOUNT', 'Tài Khoản & Đăng Nhập', 'Mật khẩu Windows, Domain AD, Mail và Tài khoản phần mềm', 'bi-key-fill'),
(2, 'NETWORK', 'Mạng, WiFi & VPN', 'Kết nối mạng nội bộ LAN, WiFi công ty và truy cập VPN từ xa', 'bi-wifi'),
(3, 'PRINTER', 'Máy In & Máy Quét (Scan)', 'Cài đặt driver máy in mạng, kết nối máy in và xử lý kẹt giấy', 'bi-printer-fill'),
(4, 'SOFTWARE', 'Phần Mềm Văn Phòng & Email', 'Cấu hình Outlook, Microsoft 365, Word, Excel và ERP', 'bi-envelope-paper-fill'),
(5, 'HARDWARE', 'Phần Cứng & Máy Tính', 'Bàn phím, chuột, màn hình phụ, dọn dẹp ổ đĩa và pin laptop', 'bi-laptop')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 4. SEED DỮ LIỆU BÀI VIẾT HƯỚNG DẪN THỰC TẾ
INSERT INTO `kb_articles` (`id`, `category_id`, `title`, `content`, `author_id`, `status`, `view_count`, `helpful_count`) VALUES
(1, 1, 'Hướng dẫn tự Đổi Mật Khẩu máy tính Windows và Domain công ty', 
'### Khi nào cần đổi mật khẩu?
- Theo chính sách an toàn thông tin, mật khẩu cần thay đổi định kỳ 90 ngày.
- Mật khẩu phải gồm ít nhất 8 ký tự, có chữ hoa, chữ thường, chữ số và ký tự đặc biệt.

### Các bước thực hiện:
1. Nhấn tổ hợp phím **Ctrl + Alt + Delete** trên bàn phím.
2. Chọn dòng **"Change a password" (Đổi mật khẩu)**.
3. Nhập mật khẩu cũ tại dòng *Old password*.
4. Nhập mật khẩu mới tại dòng *New password* và xác nhận lại ở dòng *Confirm password*.
5. Nhấn phím Enter hoặc nút mũi tên để hoàn tất.

*Lưu ý: Nếu bạn quên hoàn toàn mật khẩu và không đăng nhập được vào màn hình chính, vui lòng tạo ticket yêu cầu IT Reset mật khẩu.*', 
1, 'PUBLISHED', 45, 12),

(2, 2, 'Hướng dẫn Cài đặt và Kết nối VPN làm việc từ xa (FortiClient / OpenVPN)', 
'### Chuẩn bị:
- Đường truyền Internet ổn định tại nhà.
- Tài khoản và mật khẩu Domain công ty được cấp.

### Các bước kết nối:
1. Mở phần mềm **FortiClient VPN** (hoặc OpenVPN) trên thanh tác vụ Taskbar.
2. Tại mục *Remote Gateway*, nhập địa chỉ: `vpn.company.local` (cổng 10443).
3. Nhập *Username* và *Password* tài khoản công ty của bạn.
4. Tích chọn **Save password** nếu dùng máy tính cá nhân bảo mật.
5. Bấm nút **Connect**. Khi trạng thái hiển thị **Connected 100%**, bạn đã có thể truy cập mạng nội bộ công ty để xem ERP, ổ đĩa dùng chung NAS và máy chủ.', 
1, 'PUBLISHED', 89, 28),

(3, 3, 'Cách kết nối và cài đặt Máy In Mạng Tầng 2 & Tầng 3', 
'### Thông tin máy in văn phòng:
- **Tầng 2:** Canon LBP 2900 / HP LaserJet Pro (IP: `192.168.1.200`)
- **Tầng 3:** Ricoh Aficio Photocopy (IP: `192.168.1.201`)

### Các bước cài đặt nhanh:
1. Mở hộp thoại Run bằng cách bấm phím **Windows + R**.
2. Nhập đường dẫn chia sẻ: `\\\\192.168.1.200` và nhấn **Enter**.
3. Danh sách máy in chia sẻ sẽ hiện ra, nhấp chuột phải vào tên máy in (ví dụ *Canon_LBP2900*) và chọn **Connect**.
4. Chờ 10 giây để Windows tự động nạp Driver.
5. Thử in trang kiểm tra (Print Test Page) để đảm bảo kết nối thành công.', 
1, 'PUBLISHED', 67, 19),

(4, 4, 'Khắc phục lỗi Outlook báo "Disconnected" hoặc Không gửi được thư', 
'### Hiện tượng:
Góc dưới bên phải của Outlook hiển thị chữ *Disconnected* hoặc *Trying to connect...*, thư gửi đi nằm trong hộp thư Outbox không gửi được.

### Cách xử lý:
1. **Kiểm tra mạng:** Đảm bảo máy tính đang có kết nối mạng Internet hoặc đã bật VPN nếu làm việc tại nhà.
2. **Tắt chế độ làm việc offline:**
   - Vào tab **Send / Receive** trên thanh Ribbon của Outlook.
   - Kiểm tra nút **Work Offline**. Nếu nút này đang sáng màu xanh/xám, hãy bấm vào nó một lần để chuyển về chế độ Online.
3. **Khởi động lại Outlook:** Tắt hoàn toàn Outlook, chờ 10 giây và mở lại.
4. **Kiểm tra dung lượng hòm thư:** Nếu hòm thư đầy quá 95%, Outlook sẽ từ chối gửi thư mới. Hãy xóa bớt thư rác trong mục *Deleted Items*.', 
1, 'PUBLISHED', 52, 14),

(5, 5, 'Hướng dẫn Dọn dẹp ổ đĩa C khi bị báo Đỏ (Dung lượng đầy)', 
'### Các bước giải phóng dung lượng ổ cứng an toàn:
1. Mở hộp thoại Run (**Windows + R**), nhập `%temp%` rồi nhấn Enter.
2. Bấm **Ctrl + A** để chọn toàn bộ file rác tạm thời, bấm **Shift + Delete** để xóa vĩnh viễn (nếu file nào đang chạy thì chọn *Skip*).
3. Sử dụng công cụ **Disk Cleanup** có sẵn của Windows:
   - Nhấp chuột phải vào ổ đĩa **C:** trong File Explorer $\\rightarrow$ Chọn **Properties**.
   - Bấm nút **Disk Cleanup** $\\rightarrow$ Tích chọn các mục *Temporary files*, *Thumbnails*, *Recycle Bin*.
   - Bấm **OK** để dọn dẹp.
4. Di chuyển các tài liệu nặng (video, file cài đặt) từ thư mục *Downloads* sang ổ đĩa dữ liệu D: hoặc lưu trữ trên OneDrive công ty.', 
1, 'PUBLISHED', 38, 11)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

