<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/autoload.php';
$config = require __DIR__ . '/../app/Config/app.php';
require_once __DIR__ . '/../app/Helpers.php';

use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\Ticket;
use App\Controllers\AttachmentController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

echo "=======================================================\n";
echo " TEST SUITE: DIRECT ATTACHMENT PREVIEW & HOSTING COMPAT\n";
echo "=======================================================\n\n";

$passCount = 0;
function assertPass(bool $condition, string $msg): void {
    global $passCount;
    if (!$condition) {
        echo " [FAIL] {$msg}\n";
        exit(1);
    }
    echo " [PASS] {$msg}\n";
    $passCount++;
}

Session::start();

// 1. Authenticate test user (Admin)
$userModel = new User();
$adminUser = $userModel->findById(1);
$roles = $userModel->getRoles(1);
Auth::login($adminUser, $roles);
assertPass(Auth::check() && Auth::isAdmin(), "Authenticated as Admin for attachment tests");

// 2. Test Model getAttachmentById
$ticketModel = new Ticket();
$att = $ticketModel->getAttachmentById(1);
assertPass($att !== null && (int)$att['id'] === 1, "Ticket::getAttachmentById(1) returns record");
assertPass(!empty($att['file_name']), "Attachment has file_name: " . ($att['file_name'] ?? ''));
assertPass(array_key_exists('requester_id', $att) && array_key_exists('assignee_id', $att), "Attachment query joins ticket fields (requester_id, assignee_id)");

// 3. Test AttachmentController path resolution & MIME detection
$controller = new AttachmentController();
$ref = new ReflectionClass($controller);

$resolveMethod = $ref->getMethod('resolveFilePath');
$resolveMethod->setAccessible(true);

$resolvedPublic = $resolveMethod->invoke($controller, $att['file_path']);
assertPass($resolvedPublic !== null && file_exists($resolvedPublic), "Resolved physical file from public/uploads: {$resolvedPublic}");

// Test fallback resolution from root uploads
$tempRootFile = dirname(__DIR__) . '/uploads/root_fallback_test.txt';
file_put_contents($tempRootFile, 'Root upload fallback content');
$resolvedRoot = $resolveMethod->invoke($controller, 'uploads/root_fallback_test.txt');
assertPass($resolvedRoot !== null && file_exists($resolvedRoot), "Resolved physical file from root uploads fallback: {$resolvedRoot}");
@unlink($tempRootFile);

// Test MIME detection
$mimeMethod = $ref->getMethod('determineMimeType');
$mimeMethod->setAccessible(true);

$docMime = $mimeMethod->invoke($controller, $resolvedPublic, '', 'document.docx');
assertPass($docMime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', "MIME detection for .docx is correct");

$xlsMime = $mimeMethod->invoke($controller, $resolvedPublic, '', 'data.xlsx');
assertPass($xlsMime === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', "MIME detection for .xlsx is correct");

$pdfMime = $mimeMethod->invoke($controller, $resolvedPublic, '', 'report.pdf');
assertPass($pdfMime === 'application/pdf', "MIME detection for .pdf is correct");

// 4. Test Router registration
$req = new Request();
$res = new Response();
$router = new Router($req, $res);
require __DIR__ . '/../app/routes.php';

$routerRef = new ReflectionClass($router);
$routesProp = $routerRef->getProperty('routes');
$routesProp->setAccessible(true);
$allRoutes = $routesProp->getValue($router);

assertPass(isset($allRoutes['GET']['/attachments/{id}/view']), "Route /attachments/{id}/view is registered");
assertPass(isset($allRoutes['GET']['/attachments/{id}/download']), "Route /attachments/{id}/download is registered");

// 5. Test root index.php and .htaccess for free hostings
$rootIndexContent = file_get_contents(dirname(__DIR__) . '/index.php');
assertPass(strpos($rootIndexContent, 'docx') !== false && strpos($rootIndexContent, 'xlsx') !== false, "Root index.php includes docx and xlsx extensions in static regex");
assertPass(strpos($rootIndexContent, 'pdf') !== false, "Root index.php includes pdf in static regex");

$rootHtaccess = dirname(__DIR__) . '/.htaccess';
assertPass(file_exists($rootHtaccess), "Root .htaccess exists for Apache/Free hosting compatibility");
$htaccessContent = file_get_contents($rootHtaccess);
assertPass(strpos($htaccessContent, 'RewriteEngine On') !== false, "Root .htaccess contains RewriteEngine On");
assertPass(strpos($htaccessContent, 'public/index.php') !== false, "Root .htaccess forwards requests to public/index.php");

// 6. Test offline preview scripts
$mammothPublic = dirname(__DIR__) . '/public/js/mammoth.browser.min.js';
$xlsxPublic = dirname(__DIR__) . '/public/js/xlsx.full.min.js';
assertPass(file_exists($mammothPublic) && filesize($mammothPublic) > 500000, "Local mammoth.browser.min.js is present for offline Word preview");
assertPass(file_exists($xlsxPublic) && filesize($xlsxPublic) > 500000, "Local xlsx.full.min.js is present for offline Excel preview");

// 7. Test show.php view components
$showViewContent = file_get_contents(dirname(__DIR__) . '/app/Views/tickets/show.php');
assertPass(strpos($showViewContent, 'filePreviewModal') !== false, "show.php contains Universal File Preview Modal");
assertPass(strpos($showViewContent, 'preview-file-btn') !== false, "show.php contains preview-file-btn triggers");
assertPass(strpos($showViewContent, 'mammoth.convertToHtml') !== false, "show.php contains Word rendering via Mammoth.js");
assertPass(strpos($showViewContent, 'XLSX.read') !== false, "show.php contains Excel rendering via SheetJS");
assertPass(strpos($showViewContent, 'attachments/') !== false, "show.php references new /attachments/{id}/view and download routes");

echo "\n=======================================================\n";
echo " TOTAL TESTS: {$passCount} | PASSED: {$passCount} | FAILED: 0\n";
echo "=======================================================\n";
echo " ALL ATTACHMENT PREVIEW AND HOSTING COMPATIBILITY TESTS PASSED 100%!\n";
