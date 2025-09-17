<?php
declare(strict_types=1);

use PDO;
use PDOException;

$GLOBALS['config']['database'] = [
    'driver' => env('DB_CONNECTION', 'mysql'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => (int) env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'appliance_system'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $cfg = config('database');
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['database'], $cfg['charset']);
    try {
        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
    } catch (PDOException $e) {
        if (config('app.debug')) {
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
        http_response_code(500);
        die('Service unavailable');
    }
    return $pdo;
}

