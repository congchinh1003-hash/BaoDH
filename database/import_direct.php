<?php
/**
 * Direct importer to TiDB Cloud
 * Usage: php database/import_direct.php <host> <user> <password> [port] [database]
 */

$host = $argv[1] ?? getenv('DB_HOST');
$user = $argv[2] ?? getenv('DB_USERNAME');
$pass = $argv[3] ?? getenv('DB_PASSWORD');
$port = (int)($argv[4] ?? (getenv('DB_PORT') ?: 4000));
$db   = $argv[5] ?? (getenv('DB_DATABASE') ?: 'it_service_desk');

if (!$host || !$user) {
    die("Usage: php database/import_direct.php <host> <user> <password>\n");
}

echo "Connecting to TiDB Cloud at $host:$port...\n";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, $options);
    echo "Connected successfully!\n";
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`; USE `$db`;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    echo "Reading database/it_service_desk_tidb.sql...\n";
    $sql = file_get_contents(__DIR__ . '/it_service_desk_tidb.sql');
    
    // Split statements safely
    echo "Executing SQL statements...\n";
    $pdo->exec($sql);
    
    echo "SUCCESS: All tables, data, and constraints imported into TiDB Cloud!\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
