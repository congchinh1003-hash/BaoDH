<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Controllers\TicketController;

// Convert ALL errors/notices/warnings into exceptions
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "NOTICE/WARNING/ERROR: [$errno] $errstr on $errfile:$errline\n";
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

Session::start();
Session::set('user_id', 1);
Session::set('user_roles', ['ADMIN']);
Session::set('user_data', [
    'id'        => 1,
    'username'  => 'admin',
    'full_name' => 'Administrator',
    'roles'     => ['ADMIN']
]);
Auth::clearPermissionCache();

$db = Database::getConnection();
$tickets = $db->query("SELECT id FROM tickets")->fetchAll(PDO::FETCH_COLUMN);

$ctrl = new TicketController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();

$errorsCaught = 0;
foreach ($tickets as $tId) {
    try {
        $html = $ctrl->show($req, $res, (string)$tId);
    } catch (\Throwable $e) {
        $errorsCaught++;
        echo "Ticket #$tId: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// Now test with non-admin (e.g. Regular USER who is requester)
Session::set('user_id', 6);
Session::set('user_roles', ['USER']);
Session::set('user_data', ['id' => 6, 'username' => 'user1', 'full_name' => 'User One', 'roles' => ['USER']]);
Auth::clearPermissionCache();

foreach ($tickets as $tId) {
    try {
        $html = $ctrl->show($req, $res, (string)$tId);
    } catch (\Throwable $e) {
        $errorsCaught++;
        echo "User view Ticket #$tId: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// Now test with non-admin (e.g. Regular USER who is NOT requester)
Session::set('user_id', 9999);
Session::set('user_roles', ['USER']);
Session::set('user_data', ['id' => 9999, 'username' => 'stranger', 'full_name' => 'Stranger', 'roles' => ['USER']]);
Auth::clearPermissionCache();

foreach ($tickets as $tId) {
    try {
        $html = $ctrl->show($req, $res, (string)$tId);
    } catch (\Throwable $e) {
        $errorsCaught++;
        echo "Stranger view Ticket #$tId: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

echo "\nTotal notices/warnings/errors caught: $errorsCaught\n";

