<?php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = (int)(getenv('DB_PORT') ?: 3306);
$database = getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: 'it_service_desk');
$username = getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: 'root');
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$charset  = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true,
];

// Detect TiDB Cloud / Cloud MySQL SSL connections
$isCloudDb = (
    getenv('DB_SSL') === 'true' || 
    strpos($host, 'tidbcloud.com') !== false ||
    strpos($host, 'aivencloud.com') !== false ||
    $port === 4000 ||
    getenv('DB_SSL_CA')
);

if ($isCloudDb) {
    // TiDB Cloud Serverless requires SSL/TLS.
    // In PHP PDO mysqlnd, MYSQL_ATTR_SSL_CA must be specified to trigger SSL transport.
    $systemCAs = [
        getenv('DB_SSL_CA'),
        '/etc/ssl/certs/ca-certificates.crt', // Standard Debian/Ubuntu Docker container path
        '/etc/pki/tls/certs/ca-bundle.crt',   // RedHat/CentOS
        '/etc/ssl/cert.pem',                  // Alpine / macOS
    ];

    $caFound = null;
    foreach ($systemCAs as $path) {
        if ($path && file_exists($path)) {
            $caFound = $path;
            break;
        }
    }

    if ($caFound) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caFound;
    } else {
        // Fallback: set to default CA bundle or dummy path for mysqlnd TLS activation
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
    }
    
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

return [
    'host'     => $host,
    'port'     => $port,
    'database' => $database,
    'username' => $username,
    'password' => $password,
    'charset'  => $charset,
    'options'  => $options
];
