<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;

class AuthController
{
    public function login(): string
    {
        $error = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $csrf = $_POST['csrf_token'] ?? null;
            if (!verify_csrf($csrf)) {
                $error = 'Invalid session token.';
            } else {
                $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                    ];
                    if (in_array($user['role'], ['admin','staff'], true)) {
                        redirect('dashboard');
                    } else {
                        redirect('customer');
                    }
                } else {
                    $error = 'Invalid credentials.';
                }
            }
        }
        return render('auth/login', ['title' => 'Login', 'error' => $error]);
    }

    public function register(): string
    {
        $error = null; $success = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $csrf = $_POST['csrf_token'] ?? null;
            if (!verify_csrf($csrf)) {
                $error = 'Invalid session token.';
            } elseif ($name === '' || $email === '' || $password === '') {
                $error = 'All fields are required.';
            } else {
                $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already registered.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = db()->prepare('INSERT INTO users(name,email,password_hash,role,created_at) VALUES(?,?,?,?,NOW())');
                    $ins->execute([$name, $email, $hash, 'customer']);
                    $success = 'Registration successful. You can now log in.';
                }
            }
        }
        return render('auth/register', ['title' => 'Customer Sign Up', 'error' => $error, 'success' => $success]);
    }

    public function logout(): string
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('login');
        return '';
    }

    public function forgot(): string
    {
        $message = null; $error = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $csrf = $_POST['csrf_token'] ?? null;
            if (!verify_csrf($csrf)) {
                $error = 'Invalid session token.';
            } elseif ($email === '') {
                $error = 'Email is required.';
            } else {
                $token = bin2hex(random_bytes(32));
                $ins = db()->prepare('INSERT INTO password_resets(email,token,created_at) VALUES(?,?,NOW())');
                $ins->execute([$email, $token]);
                $resetLink = base_url('reset?token=' . urlencode($token));
                $subject = 'Password Reset Request';
                $body = 'To reset your password, click: ' . $resetLink;
                @mail($email, $subject, $body);
                $message = 'If this email is registered, a reset link was sent.';
            }
        }
        return render('auth/forgot', ['title' => 'Forgot Password', 'message' => $message, 'error' => $error]);
    }

    public function reset(): string
    {
        $error = null; $message = null;
        $token = $_GET['token'] ?? '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $token = $_POST['token'] ?? '';
            $password = (string)($_POST['password'] ?? '');
            $csrf = $_POST['csrf_token'] ?? null;
            if (!verify_csrf($csrf)) {
                $error = 'Invalid session token.';
            } elseif ($token === '' || $password === '') {
                $error = 'All fields are required.';
            } else {
                $stmt = db()->prepare('SELECT email FROM password_resets WHERE token = ? LIMIT 1');
                $stmt->execute([$token]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    $error = 'Invalid or expired token.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = db()->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE email = ?');
                    $upd->execute([$hash, $row['email']]);
                    $del = db()->prepare('DELETE FROM password_resets WHERE token = ?');
                    $del->execute([$token]);
                    $message = 'Password has been reset. You can log in now.';
                }
            }
        }
        return render('auth/reset', ['title' => 'Reset Password', 'token' => $token, 'message' => $message, 'error' => $error]);
    }
}

