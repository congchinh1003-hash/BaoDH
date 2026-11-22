<?php
require_once __DIR__ . '/../app/autoload.php';
$db = \App\Core\Database::getConnection();
$cats = $db->query("SELECT * FROM asset_categories")->fetchAll();
echo "ASSET CATEGORIES:\n";
foreach ($cats as $c) {
    echo "  [{$c['id']}] {$c['code']} - {$c['name']}\n";
}

$tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
echo "\nEXISTING TABLES (" . count($tables) . "):\n";
echo implode(", ", $tables) . "\n";

