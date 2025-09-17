<?php
declare(strict_types=1);

namespace App\Controllers;

class CustomerController
{
    public function index(): string
    {
        $this->requireRole(['customer']);
        return render('customer/index', [
            'title' => 'Customer Dashboard',
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

