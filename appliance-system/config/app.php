<?php
declare(strict_types=1);

$GLOBALS['config']['app'] = [
    'name' => env('APP_NAME', 'Appliance Management System'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', 'true') === 'true',
    'url' => env('APP_URL', ''),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'theme' => [
        'green' => '#C6D870',
        'beige' => '#E6CFA9',
        'navy' => '#113F67',
        'black' => '#000000',
    ],
];

