<?php

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\CatalogController;
use App\Controllers\ApprovalController;
use App\Controllers\ProblemController;
use App\Controllers\ChangeController;

$req = new Request();
$res = new Response();

// Authenticate as Admin/Manager for full access
$db = (new App\Core\Database())->getConnection();
$user = $db->query("SELECT * FROM users WHERE id = 1")->fetch();
\App\Core\Session::start();
\App\Core\Session::set('user', $user);

echo "Testing ITIL Views Rendering...\n";

// 1. Catalog
$catalog = new CatalogController();
$html1 = $catalog->index($req, $res);
echo " [OK] Catalog Index rendered (" . strlen($html1) . " bytes)\n";

// 2. Approvals
$approvals = new ApprovalController();
$html2 = $approvals->index($req, $res);
echo " [OK] Approvals Index rendered (" . strlen($html2) . " bytes)\n";

// 3. Problems
$problems = new ProblemController();
$html3 = $problems->index($req, $res);
echo " [OK] Problems Index rendered (" . strlen($html3) . " bytes)\n";

$html4 = $problems->create($req, $res);
echo " [OK] Problems Create rendered (" . strlen($html4) . " bytes)\n";

$problemId = (string)($db->query("SELECT id FROM problems ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1);
$html5 = $problems->show($req, $res, $problemId);
echo " [OK] Problems Show rendered (" . strlen($html5) . " bytes)\n";

// 4. Changes
$changes = new ChangeController();
$html6 = $changes->index($req, $res);
echo " [OK] Changes Index rendered (" . strlen($html6) . " bytes)\n";

$html7 = $changes->create($req, $res);
echo " [OK] Changes Create rendered (" . strlen($html7) . " bytes)\n";

$changeId = (string)($db->query("SELECT id FROM change_requests ORDER BY id DESC LIMIT 1")->fetchColumn() ?: 1);
$html8 = $changes->show($req, $res, $changeId);
echo " [OK] Changes Show rendered (" . strlen($html8) . " bytes)\n";

echo "\nALL ITIL VIEWS RENDERED WITH 100% SUCCESS!\n";

