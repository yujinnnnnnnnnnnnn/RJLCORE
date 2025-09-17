<?php

declare(strict_types=1);

namespace App;

final class Config
{
    public static function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    public static function dbDsn(): string
    {
        $host = self::get('DB_HOST', '127.0.0.1');
        $port = (int) self::get('DB_PORT', 3306);
        $db   = self::get('DB_DATABASE', 'appliances_db');
        return "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    }
}

