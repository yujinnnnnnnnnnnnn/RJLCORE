<?php

declare(strict_types=1);

namespace App;

use App\Models\User;

final class Auth
{
    public static function user(): ?array
    {
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) { return null; }
        return User::findById((int) $id);
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function hasRole(string $roleName): bool
    {
        $user = self::user();
        if (!$user) { return false; }
        $roleIdToName = [1 => 'Admin', 2 => 'Staff', 3 => 'Customer'];
        $name = $roleIdToName[(int)$user['role_id']] ?? '';
        return $name === $roleName;
    }

    public static function requireRole(array $roles): void
    {
        $user = self::user();
        if (!$user) {
            header('Location: /public/login.php');
            exit;
        }
        $roleIdToName = [1 => 'Admin', 2 => 'Staff', 3 => 'Customer'];
        $name = $roleIdToName[(int)$user['role_id']] ?? '';
        if (!in_array($name, $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user || $user['status'] !== 'active') { return false; }
        if (password_verify($password, $user['password_hash'])) {
            Session::regenerate();
            $_SESSION['user_id'] = (int) $user['id'];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}

