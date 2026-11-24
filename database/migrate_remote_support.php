<?php
require_once __DIR__.'/../app/autoload.php';
\App\Models\RemoteSession::ensureTablesExist();
echo "Remote support schema ready.\n";
