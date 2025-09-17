<?php
session_start();

function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /auth/login.php');
        exit;
    }
}

function require_role(array $role_ids): void {
    require_login();
    $user_role = (int)($_SESSION['role_id'] ?? 0);
    if (!in_array($user_role, $role_ids, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function logout(): void {
    session_destroy();
    header('Location: /auth/login.php');
    exit;
}

