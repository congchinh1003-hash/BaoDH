<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../Config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                
                // Disable ONLY_FULL_GROUP_BY for MySQL 8 & TiDB Cloud compatibility
                self::$instance->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                $detail = (getenv('APP_DEBUG') === 'true' || getenv('RENDER')) 
                    ? ("<br><small style='color:red;'>Chi tiết lỗi: " . htmlspecialchars($e->getMessage()) . "</small>")
                    : "";
                die("Không thể kết nối Cơ sở dữ liệu. Vui lòng kiểm tra lại cấu hình kết nối MySQL." . $detail);
            }
        }
        return self::$instance;
    }
}
