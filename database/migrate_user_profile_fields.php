<?php
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "Connected to database successfully.\n";

    // 1. Add seat_location and job_title columns to users table
    $columns = $db->query("SHOW COLUMNS FROM `users`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('seat_location', $columns)) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `seat_location` VARCHAR(150) NULL DEFAULT NULL AFTER `department`");
        echo "[SUCCESS] Added 'seat_location' column to users.\n";
    } else {
        echo "[INFO] 'seat_location' column already exists.\n";
    }

    if (!in_array('job_title', $columns)) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `job_title` VARCHAR(150) NULL DEFAULT NULL AFTER `seat_location`");
        echo "[SUCCESS] Added 'job_title' column to users.\n";
    } else {
        echo "[INFO] 'job_title' column already exists.\n";
    }

    // 2. Update existing initial seed users with realistic seat_location and job_title
    $updates = [
        1 => ['seat_location' => 'Tầng 5 - P.501 (Phòng Giám Đốc)', 'job_title' => 'Quản trị viên Hệ thống Cấp cao'],
        2 => ['seat_location' => 'Tầng 4 - P.402 (Phòng IT Leader)', 'job_title' => 'Trưởng nhóm IT Service Desk'],
        3 => ['seat_location' => 'Tầng 4 - Bàn HD-01 (Quầy Hỗ trợ)', 'job_title' => 'Chuyên viên Hỗ trợ Kỹ thuật L1'],
        4 => ['seat_location' => 'Tầng 4 - Bàn NET-01 (Khu Hạ tầng)', 'job_title' => 'Kỹ sư Mạng & Viễn thông'],
        5 => ['seat_location' => 'Tầng 4 - Bàn APP-01 (Khu Phần mềm)', 'job_title' => 'Kỹ sư Phần mềm Ứng dụng'],
        6 => ['seat_location' => 'Tầng 2 - P.KT-01 (Phòng Kế Toán)', 'job_title' => 'Chuyên viên Kế toán Chi tiết'],
        7 => ['seat_location' => 'Tầng 2 - P.NS-02 (Phòng Nhân Sự)', 'job_title' => 'Chuyên viên Nhân sự & C&B']
    ];

    $stmtUpdate = $db->prepare("UPDATE `users` SET `seat_location` = :seat, `job_title` = :title WHERE `id` = :id");
    foreach ($updates as $userId => $info) {
        $stmtUpdate->execute([
            'seat'  => $info['seat_location'],
            'title' => $info['job_title'],
            'id'    => $userId
        ]);
    }
    echo "[SUCCESS] Initial users profile info updated.\n";

} catch (\Throwable $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

