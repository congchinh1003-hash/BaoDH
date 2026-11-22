<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class BackupService
{
    private PDO $db;
    private string $backupDir;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->backupDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Generate complete MySQL SQL dump (Structure + Data) without relying on external mysqldump CLI.
     */
    public function createBackup(?string $customName = null): array
    {
        $timestamp = date('Ymd_His');
        $cleanCustom = $customName ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $customName) . '_' : '';
        $filename = "backup_{$cleanCustom}{$timestamp}.sql";
        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        $fp = fopen($filepath, 'w');
        if (!$fp) {
            throw new \RuntimeException("Không thể tạo file sao lưu tại: {$filepath}");
        }

        // Header metadata
        fwrite($fp, "-- ============================================================\n");
        fwrite($fp, "-- IT Service Desk - Automatic & Disaster Recovery Database Backup\n");
        fwrite($fp, "-- Generated at: " . date('Y-m-d H:i:s') . "\n");
        fwrite($fp, "-- Host: " . ($_ENV['DB_HOST'] ?? '127.0.0.1') . "\n");
        fwrite($fp, "-- Database: " . ($_ENV['DB_NAME'] ?? 'it_service_desk') . "\n");
        fwrite($fp, "-- ============================================================\n\n");

        fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($fp, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($fp, "SET NAMES utf8mb4;\n\n");

        // Fetch all tables
        $tablesStmt = $this->db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        while ($row = $tablesStmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            fwrite($fp, "\n-- ------------------------------------------------------------\n");
            fwrite($fp, "-- Table structure for `{$table}`\n");
            fwrite($fp, "-- ------------------------------------------------------------\n");
            fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createStmt = $this->db->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
            if (!empty($createRow[1])) {
                fwrite($fp, $createRow[1] . ";\n\n");
            }

            // Dump data
            $rowsStmt = $this->db->query("SELECT * FROM `{$table}`");
            $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                fwrite($fp, "-- Dumping data for table `{$table}`\n");

                $columnNames = array_keys($rows[0]);
                $escapedColumns = array_map(fn($col) => "`{$col}`", $columnNames);
                $columnsSql = implode(', ', $escapedColumns);

                // Chunk insert batches by 50 to optimize memory & packet size
                $chunks = array_chunk($rows, 50);
                foreach ($chunks as $chunk) {
                    $insertValues = [];
                    foreach ($chunk as $row) {
                        $escapedValues = [];
                        foreach ($row as $val) {
                            if ($val === null) {
                                $escapedValues[] = 'NULL';
                            } else {
                                $escapedValues[] = $this->db->quote((string)$val);
                            }
                        }
                        $insertValues[] = '(' . implode(', ', $escapedValues) . ')';
                    }
                    fwrite($fp, "INSERT INTO `{$table}` ({$columnsSql}) VALUES\n" . implode(",\n", $insertValues) . ";\n");
                }
                fwrite($fp, "\n");
            }
        }

        fwrite($fp, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($fp, "-- Dump completed at " . date('Y-m-d H:i:s') . "\n");
        fclose($fp);

        $fileSize = filesize($filepath);

        return [
            'success'  => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'size'     => $fileSize,
            'tables'   => count($tables)
        ];
    }

    /**
     * List all available backup files.
     */
    public function listBackups(): array
    {
        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . '*.sql');
        if (!$files) {
            return [];
        }

        $backups = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $mtime = filemtime($file);

            $backups[] = [
                'filename'   => $filename,
                'path'       => $file,
                'size'       => $size,
                'size_human' => $this->formatBytes($size),
                'created_at' => date('Y-m-d H:i:s', $mtime),
                'timestamp'  => $mtime,
            ];
        }

        // Sort descending by created timestamp
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Restore database from a specific SQL backup file.
     */
    public function restoreBackup(string $filename): array
    {
        // Sanitize filename to prevent directory traversal
        $cleanName = basename($filename);
        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $cleanName;

        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'message' => "Không tìm thấy file sao lưu: {$cleanName}"
            ];
        }

        $sql = file_get_contents($filepath);
        if ($sql === false || trim($sql) === '') {
            return [
                'success' => false,
                'message' => "File sao lưu rỗng hoặc không thể đọc được."
            ];
        }

        try {
            // Disable foreign key checks for restore
            $this->db->exec("SET FOREIGN_KEY_CHECKS=0;");

            // Split SQL by semicolons while respecting multi-statement batches
            // PDO query can execute multi-queries if configured, or we can execute via exec()
            $this->db->exec($sql);

            $this->db->exec("SET FOREIGN_KEY_CHECKS=1;");

            return [
                'success' => true,
                'message' => "Phục hồi cơ sở dữ liệu thành công từ bản sao lưu {$cleanName}!"
            ];
        } catch (\Throwable $e) {
            // Re-enable foreign key checks in case of error
            try { $this->db->exec("SET FOREIGN_KEY_CHECKS=1;"); } catch (\Throwable $t) {}

            return [
                'success' => false,
                'message' => "Lỗi khi phục hồi cơ sở dữ liệu: " . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $cleanName = basename($filename);
        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $cleanName;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    /**
     * Housekeeping: keep only the latest N backups, delete older ones.
     */
    public function cleanupOldBackups(int $keepMax = 15): int
    {
        $backups = $this->listBackups();
        if (count($backups) <= $keepMax) {
            return 0;
        }

        $deletedCount = 0;
        $toDelete = array_slice($backups, $keepMax);
        foreach ($toDelete as $b) {
            if ($this->deleteBackup($b['filename'])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

