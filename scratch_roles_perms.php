<?php
require_once __DIR__ . '/app/autoload.php';
$db = \App\Core\Database::getConnection();

echo "=== TABLES ===\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if (strpos($t, 'role') !== false || strpos($t, 'perm') !== false || strpos($t, 'user') !== false) {
        echo "- $t\n";
    }
}

echo "\n=== ROLES ===\n";
$roles = $db->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
print_r($roles);

