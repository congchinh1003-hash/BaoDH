<?php
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../app/Helpers.php';
\App\Core\Session::start();
\App\Core\Language::load();

// Login as staff or admin
$userModel = new \App\Models\User();
$admin = $userModel->findById(1);
\App\Core\Auth::login($admin, ['ADMIN', 'STAFF']);

$request = new \App\Core\Request();
$response = new \App\Core\Response();

$controller = new \App\Controllers\AssetController();
try {
    $html = $controller->create($request, $response);
    echo "SUCCESS: Rendered " . strlen($html) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
