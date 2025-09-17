<?php
declare(strict_types=1);

namespace App\Controllers;

class DashboardController
{
    public function index(): string
    {
        $this->requireRole(['admin','staff']);
        return render('dashboard/index', [
            'title' => 'Admin / Staff Dashboard',
        ]);
    }

    private function requireRole(array $roles): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || !in_array($user['role'] ?? '', $roles, true)) {
            redirect('login');
        }
    }
}

