<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function createCustomer(string $fullName, string $email, string $password, ?string $phone = null): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::pdo()->prepare('INSERT INTO users (role_id, full_name, email, password_hash, phone) VALUES (3, ?, ?, ?, ?)');
        $stmt->execute([$fullName, $email, $hash, $phone]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function createStaff(string $fullName, string $email, string $password, int $roleId): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = Database::pdo()->prepare('INSERT INTO users (role_id, full_name, email, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([$roleId, $fullName, $email, $hash]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = Database::pdo()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$hash, $userId]);
    }
}

