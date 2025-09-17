<?php
$appName = config('app.name');
$theme = config('app.theme');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? $appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
    <script defer src="<?= asset_url('js/app.js') ?>"></script>
    <style>
        :root{
            --green: <?= htmlspecialchars($theme['green']) ?>;
            --beige: <?= htmlspecialchars($theme['beige']) ?>;
            --navy: <?= htmlspecialchars($theme['navy']) ?>;
            --black: <?= htmlspecialchars($theme['black']) ?>;
        }
    </style>
</head>
<body>
    <header class="site-header fade-in">
        <div class="container nav">
            <a class="brand" href="<?= base_url('home') ?>">
                <span class="logo">⚡</span> <?= htmlspecialchars($appName) ?>
            </a>
            <nav>
                <a href="<?= base_url('home') ?>">Home</a>
                <a href="<?= base_url('about') ?>">About</a>
                <a href="<?= base_url('login') ?>" class="btn btn-primary">Portal</a>
            </nav>
        </div>
    </header>
    <main class="container page-content">
        <?= $content ?>
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>© <?= date('Y') ?> <?= htmlspecialchars($appName) ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

