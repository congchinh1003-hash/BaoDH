<?php

/**
 * IT SERVICE DESK & ASSET MANAGEMENT SYSTEM
 * FRONT CONTROLLER
 */

declare(strict_types=1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoload & Config
require_once __DIR__ . '/../app/autoload.php';
$appConfig = require __DIR__ . '/../app/Config/app.php';

// Set Timezone
date_default_timezone_set($appConfig['timezone'] ?? 'Asia/Ho_Chi_Minh');

// Initialize Session
\App\Core\Session::start();

// Initialize Language
require_once __DIR__ . '/../app/Helpers.php';
\App\Core\Language::load();

// Request & Response & Router
$request = new \App\Core\Request();
$response = new \App\Core\Response();
$router = new \App\Core\Router($request, $response);

// Load routes
require_once __DIR__ . '/../app/routes.php';

// Resolve route
try {
    $output = $router->resolve();
    if (is_string($output)) {
        echo $output;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Hệ thống gặp sự cố</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light p-5">
        <div class="card border-danger shadow-sm max-w-lg mx-auto" style="max-width: 600px;">
            <div class="card-header bg-danger text-white fw-bold">Lỗi Xử Lý Hệ Thống</div>
            <div class="card-body">
                <p class="text-danger fw-semibold"><?= htmlspecialchars($e->getMessage()) ?></p>
                <div class="small text-muted font-monospace bg-light p-2 rounded">
                    <?= htmlspecialchars($e->getFile()) ?> (Dòng <?= $e->getLine() ?>)
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

