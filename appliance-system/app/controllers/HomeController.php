<?php
declare(strict_types=1);

namespace App\Controllers;

class HomeController
{
    public function index(): string
    {
        return render('home/index', [
            'title' => 'Home',
        ]);
    }

    public function about(): string
    {
        return render('home/about', [
            'title' => 'About Us',
        ]);
    }
}

