<?php

require_once __DIR__ . '/../app/autoload.php';
$baseUrl = 'http://localhost:3000';
$cookieFile = sys_get_temp_dir() . '/func_sess_admin.txt';

$ch = curl_init("$baseUrl/projects/api/quick-create");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'project_id'     => 10,
    'issue_type'     => 'TASK',
    'summary'        => 'Test issue direct',
    'description'    => 'Test description',
    'priority'       => 'High',
    'status'         => 'TO DO'
]));
$res = curl_exec($ch);
echo $res;

