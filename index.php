<?php
/**
 * Root Entry Point & Fallback Router
 * Supports running from root (php -S localhost:3000), Apache subfolders, or Free Hosting (InfinityFree, cPanel, 000webhost)
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));

// Static files extensions supported for direct serving when hosted from repository root
$staticExts = 'css|js|png|jpg|jpeg|gif|webp|svg|ico|woff|woff2|ttf|eot|pdf|docx|xlsx|doc|xls|txt|csv|log|zip|rar|7z|json';

if (preg_match('/\.(' . $staticExts . ')$/i', $uri)) {
    $candidateFiles = [
        __DIR__ . '/public' . $uri,
        __DIR__ . $uri,
        __DIR__ . '/public/uploads/' . basename($uri),
        __DIR__ . '/uploads/' . basename($uri)
    ];

    foreach ($candidateFiles as $file) {
        $real = realpath($file);
        if ($real && file_exists($real) && !is_dir($real)) {
            $mimes = [
                'css'   => 'text/css; charset=utf-8',
                'js'    => 'application/javascript; charset=utf-8',
                'png'   => 'image/png',
                'jpg'   => 'image/jpeg',
                'jpeg'  => 'image/jpeg',
                'gif'   => 'image/gif',
                'webp'  => 'image/webp',
                'svg'   => 'image/svg+xml',
                'ico'   => 'image/x-icon',
                'woff'  => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf'   => 'font/ttf',
                'eot'   => 'application/vnd.ms-fontobject',
                'pdf'   => 'application/pdf',
                'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'doc'   => 'application/msword',
                'xls'   => 'application/vnd.ms-excel',
                'txt'   => 'text/plain; charset=utf-8',
                'csv'   => 'text/csv; charset=utf-8',
                'log'   => 'text/plain; charset=utf-8',
                'json'  => 'application/json; charset=utf-8',
                'zip'   => 'application/zip',
                'rar'   => 'application/x-rar-compressed',
                '7z'    => 'application/x-7z-compressed'
            ];
            $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
            header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
            header('Content-Length: ' . filesize($real));
            header('Cache-Control: public, max-age=86400');
            header('X-Content-Type-Options: nosniff');
            readfile($real);
            exit;
        }
    }
}

// Forward to public front controller
require_once __DIR__ . '/public/index.php';
