<?php
require_once __DIR__ . '/app/autoload.php';
require_once __DIR__ . '/app/Helpers.php';
\App\Core\Session::start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

$db = \App\Core\Database::getConnection();
$tickets = $db->query('SELECT id FROM tickets')->fetchAll(PDO::FETCH_COLUMN);

$userModel = new \App\Models\User();
$admin = $userModel->findById(1);
$staff = $userModel->findById(3);
$user = $userModel->findById(6);

$ticketController = new \App\Controllers\TicketController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();

echo "Testing show.php across all tickets for Admin, Staff, and EndUser...\n";

// Set error handler to catch warnings and notices as well
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "  [PHP NOTICE/WARNING] $errstr in $errfile:$errline\n";
    return false; // let normal error handling continue
});

foreach ([$admin, $staff, $user] as $testUser) {
    if (!$testUser) continue;
    \App\Core\Auth::login($testUser, $userModel->getRoles($testUser['id']));
    echo "\n--- Testing as {$testUser['username']} (Role: " . implode(',', $userModel->getRoles($testUser['id'])) . ") ---\n";
    
    foreach ($tickets as $tid) {
        try {
            ob_start();
            $out = $ticketController->show($req, $res, (string)$tid);
            ob_end_clean();
            // echo "Ticket $tid: OK (" . strlen($out) . " bytes)\n";
        } catch (\Throwable $e) {
            ob_end_clean();
            echo "  [EXCEPTION] Ticket $tid: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
            echo $e->getTraceAsString() . "\n";
        }
    }
}

echo "\nDone scanning all tickets.\n";

