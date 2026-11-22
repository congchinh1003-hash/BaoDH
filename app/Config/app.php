<?php

return [
    'app_name'    => 'IT Service Desk & Asset Management',
    'app_env'     => 'development',
    'base_url'    => '', // auto-detected if empty
    'timezone'    => 'Asia/Ho_Chi_Minh',
    'upload'      => [
        'dir'             => __DIR__ . '/../../public/uploads',
        'max_size_mb'     => 15,
        'allowed_exts'    => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'xlsx', 'txt', 'zip', 'rar', 'log'],
        'allowed_mimes'   => [
            'image/jpeg', 'image/png', 'image/gif', 
            'application/pdf', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'application/zip', 'application/x-rar-compressed'
        ]
    ]
];

