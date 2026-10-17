<?php
declare(strict_types=1);

$config = [
    'database' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'seshbank',
        'user' => getenv('DB_USER') ?: 'seshbank',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],
];

$local = __DIR__ . '/local.php';
return is_file($local) ? array_replace_recursive($config, require $local) : $config;
