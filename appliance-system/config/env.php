<?php
declare(strict_types=1);

function load_env(string $filePath): array
{
    $variables = [];
    if (!is_file($filePath)) {
        return $variables;
    }
    $lines = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, " \t\n\r\0\x0B\"'{}");
        $variables[$key] = $value;
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
    return $variables;
}

function env(string $key, $default = null)
{
    if (isset($GLOBALS['env']) && array_key_exists($key, $GLOBALS['env'])) {
        $value = $GLOBALS['env'][$key];
        return $value === '' ? $default : $value;
    }
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

