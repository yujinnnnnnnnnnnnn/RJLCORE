<?php

declare(strict_types=1);

namespace App;

final class Session
{
    public static function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $cookieParams = session_get_cookie_params();
        $cookieParams['httponly'] = true;
        $cookieParams['secure'] = (Config::get('SESSION_SECURE', 'false') === 'true');
        $cookieParams['samesite'] = Config::get('SESSION_SAMESITE', 'Lax');

        session_name(Config::get('SESSION_NAME', 'appliances_sess'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => $cookieParams['secure'] ?? false,
            'httponly' => $cookieParams['httponly'] ?? true,
            'samesite' => $cookieParams['samesite'] ?? 'Lax',
        ]);

        session_start();
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
            }
            session_destroy();
        }
    }
}

