<?php
require_once __DIR__ . '/../app/autoload.php';
$db = \App\Core\Database::getConnection();

echo "Creating JumpServer PAM tables...\n";

// 1. JUMP_SERVERS
$db->exec("
CREATE TABLE IF NOT EXISTS `jump_servers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(50) NOT NULL,
    `port` INT NOT NULL DEFAULT 22,
    `protocol` ENUM('SSH', 'RDP', 'VNC', 'TELNET') NOT NULL DEFAULT 'SSH',
    `os_type` ENUM('LINUX', 'UBUNTU', 'CENTOS', 'WINDOWS', 'DEBIAN') NOT NULL DEFAULT 'LINUX',
    `default_username` VARCHAR(50) NOT NULL DEFAULT 'root',
    `description` VARCHAR(255) NULL,
    `asset_id` INT NULL,
    `status` ENUM('ONLINE', 'OFFLINE', 'MAINTENANCE') NOT NULL DEFAULT 'ONLINE',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_js_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// 2. JUMP_SERVER_PERMISSIONS
$db->exec("
CREATE TABLE IF NOT EXISTS `jump_server_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `server_id` INT NOT NULL,
    `user_id` INT NULL,
    `group_id` INT NULL,
    `granted_by` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_jsp_server` FOREIGN KEY (`server_id`) REFERENCES `jump_servers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jsp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jsp_group` FOREIGN KEY (`group_id`) REFERENCES `it_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// 3. JUMP_SERVER_SESSIONS (Exclusive Lock)
$db->exec("
CREATE TABLE IF NOT EXISTS `jump_server_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_token` VARCHAR(64) NOT NULL UNIQUE,
    `server_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `client_ip` VARCHAR(50) NULL,
    `status` ENUM('ACTIVE', 'KICKED', 'TERMINATED') NOT NULL DEFAULT 'ACTIVE',
    `started_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ended_at` DATETIME NULL,
    `last_heartbeat_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `kicked_by_user_id` INT NULL,
    CONSTRAINT `fk_jss_server` FOREIGN KEY (`server_id`) REFERENCES `jump_servers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jss_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jss_kicked_by` FOREIGN KEY (`kicked_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// 4. JUMP_COMMAND_LOGS
$db->exec("
CREATE TABLE IF NOT EXISTS `jump_command_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` INT NOT NULL,
    `server_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `command` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_jcl_session` FOREIGN KEY (`session_id`) REFERENCES `jump_server_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tables created successfully!\n";

// Seed initial servers if none exist
$count = $db->query("SELECT COUNT(*) FROM jump_servers")->fetchColumn();
if ($count == 0) {
    echo "Seeding initial servers...\n";
    $db->exec("
    INSERT INTO `jump_servers` (`name`, `ip_address`, `port`, `protocol`, `os_type`, `default_username`, `description`, `status`, `is_active`) VALUES
    ('Production Web App Server (Nginx/PHP)', '192.168.1.100', 22, 'SSH', 'UBUNTU', 'ubuntu', 'Máy chủ ứng dụng Web chính môi trường Production', 'ONLINE', 1),
    ('Database Master Cluster (MySQL 8.0)', '192.168.1.105', 22, 'SSH', 'CENTOS', 'root', 'Cụm máy chủ cơ sở dữ liệu giao dịch cốt lõi', 'ONLINE', 1),
    ('Windows Server 2022 AD & File Services', '192.168.1.10', 3389, 'RDP', 'WINDOWS', 'Administrator', 'Máy chủ miền Active Directory và lưu trữ tài liệu nội bộ', 'ONLINE', 1),
    ('Core Gateway & Security Firewall', '192.168.1.1', 22, 'SSH', 'DEBIAN', 'admin', 'Hệ thống tường lửa phân vùng DMZ và cân bằng tải', 'ONLINE', 1);
    ");

    // Seed permissions for IT Helpdesk (user 3) and Manager (user 2)
    // Server 1 -> user 3 (helpdesk) and user 2 (manager)
    // Server 2 -> user 2 (manager only)
    // Server 3 -> user 3 (helpdesk)
    // Server 4 -> user 2 (manager only)
    $db->exec("
    INSERT INTO `jump_server_permissions` (`server_id`, `user_id`, `granted_by`) VALUES
    (1, 3, 1),
    (1, 2, 1),
    (2, 2, 1),
    (3, 3, 1),
    (3, 2, 1),
    (4, 2, 1);
    ");

    echo "Seeded 4 jump servers and initial permissions!\n";
} else {
    echo "Servers already seeded.\n";
}

