<?php
declare(strict_types=1);

/**
 * Copy this file to config.php and fill in hosting database details.
 * Keep config.php outside the public document root whenever possible.
 */
return [
    'app' => [
        'name' => 'Student Information System',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'base_path' => '', // Usually blank when deployed at the domain root.
        'session_name' => 'sis_session',
        'session_idle_timeout' => 1800,
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'CHANGE_ME',
        'username' => getenv('DB_USER') ?: 'CHANGE_ME',
        'password' => getenv('DB_PASS') ?: 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],
    'logging' => [
        'file' => dirname(__DIR__) . '/storage/logs/app.log',
    ],
];
