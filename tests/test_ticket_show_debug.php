<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Controllers\TicketController;

error_reporting(E_ALL);
ini_set('display_errors', '1');

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
$tickets = $db->query("SELECT id, ticket_code FROM tickets")->fetchAll();

echo "Found " . count($tickets) . " tickets in DB.\n";

$ctrl = new TicketController();
$req = new \App\Core\Request();
$res = new \App\Core\Response();

foreach ($tickets as $t) {
    echo "Rendering ticket #{$t['id']} ({$t['ticket_code']})...\n";
    try {
        $html = $ctrl->show($req, $res, (string)$t['id']);
        echo "OK - Length: " . strlen($html) . "\n";
    } catch (\Throwable $e) {
        echo "ERROR in ticket #{$t['id']}: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
}

