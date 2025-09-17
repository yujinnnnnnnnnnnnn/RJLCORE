<?php

declare(strict_types=1);

namespace App;

final class Util
{
    public static function jsonInput(): array
    {
        $data = file_get_contents('php://input');
        $decoded = json_decode($data ?? '[]', true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function requirePostJson(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false)) {
            http_response_code(415);
            exit('Unsupported Media Type');
        }
    }

    public static function uploadImage(array $file, string $destDir): ?string
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return null;
        }
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $target = rtrim($destDir, '/') . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $name;
        }
        return null;
    }
}

