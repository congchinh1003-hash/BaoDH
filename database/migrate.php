<?php
/**
 * Database Migration Runner for IT Service Desk
 */
require_once __DIR__ . '/../app/Config/database.php';

echo "=== IT SERVICE DESK DATABASE MIGRATION ===\n";

try {
    $dbConfig = require __DIR__ . '/../app/Config/database.php';
    
    // Connect to MySQL server first without selecting DB
    $dsnServer = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsnServer, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database if not exists
    $dbName = $dbConfig['database'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "[OK] Database `{$dbName}` verified.\n";

    // Reconnect to target database
    $dsnDb = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbName};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsnDb, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Run Schema
    $schemaFile = __DIR__ . '/schema_phase1.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
        echo "[OK] Schema Phase 1 imported successfully.\n";
    }

    // Run Seeds
    $seedFile = __DIR__ . '/seeds_phase1.sql';
    if (file_exists($seedFile)) {
        $sql = file_get_contents($seedFile);
        $pdo->exec($sql);
        echo "[OK] Seed Data Phase 1 imported successfully.\n";
    }

    echo "=== MIGRATION COMPLETED SUCCESSFULLY! ===\n";

} catch (PDOException $e) {
    echo "[ERROR] Database migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

