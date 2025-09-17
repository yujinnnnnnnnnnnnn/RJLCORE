<?php
declare(strict_types=1);

$APP_CONFIG = [
    'app_name' => 'AppliancePro',
    // Adjust base_url if the app is not served from the web root
    'base_url' => '/',
    'env' => 'development',
];

function asset(string $path): string {
    global $APP_CONFIG;
    $base = rtrim($APP_CONFIG['base_url'], '/');
    return $base . '/' . ltrim($path, '/');
}

function url(string $path): string {
    return asset($path);
}

