<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->safeLoad();
}

// Set default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Error reporting
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
}

// Ensure storage directories exist
$storagePath = $envPath . '/storage';
if (!is_dir($storagePath)) {
    @mkdir($storagePath, 0775, true);
}
if (!is_dir($storagePath . '/logs')) {
    @mkdir($storagePath . '/logs', 0775, true);
}
if (!is_dir($storagePath . '/mail')) {
    @mkdir($storagePath . '/mail', 0775, true);
}

// Secure session initialization
require_once __DIR__ . '/Session.php';
App\Session::startSecureSession();

