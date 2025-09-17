<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/app/helpers.php';

$env = load_env(BASE_PATH . '/.env');
$GLOBALS['env'] = $env;

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';

date_default_timezone_set(env('APP_TIMEZONE', config('app.timezone', 'UTC')));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

