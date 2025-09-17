<?php
declare(strict_types=1);

$GLOBALS['config'] = $GLOBALS['config'] ?? [];

function config(string $key, $default = null)
{
    $store = $GLOBALS['config'] ?? [];
    if ($key === '*') {
        return $store;
    }
    $segments = explode('.', $key);
    $value = $store;
    foreach ($segments as $segment) {
        if (is_array($value) && array_key_exists($segment, $value)) {
            $value = $value[$segment];
        } else {
            return $default;
        }
    }
    return $value;
}

function base_url(string $path = ''): string
{
    $configured = config('app.url', '');
    if ($configured !== '') {
        return rtrim($configured, '/') . '/' . ltrim($path, '/');
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $base = $scheme . '://' . $host . ($scriptDir === '' ? '' : $scriptDir);
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function render(string $view, array $data = [], string $layout = 'main'): string
{
    $viewFile = BASE_PATH . '/views/' . ltrim($view, '/') . '.php';
    if (!file_exists($viewFile)) {
        return 'View not found: ' . htmlspecialchars($view);
    }
    extract($data, EXTR_SKIP);
    ob_start();
    include $viewFile;
    $content = ob_get_clean();

    $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';
    if (!file_exists($layoutFile)) {
        return $content;
    }

    ob_start();
    include $layoutFile;
    return (string) ob_get_clean();
}

function redirect(string $to): void
{
    header('Location: ' . base_url($to));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function verify_csrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

