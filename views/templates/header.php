<?php
if (!isset($pageTitle)) { $pageTitle = 'Appliances Store'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/public/assets/css/theme.css">
    <script defer src="/public/assets/js/ui.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="page-fade-in">
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/public/index.php">Appliances Store</a>
        <nav class="nav">
            <a href="/public/index.php">Home</a>
            <a href="/public/about.php">About</a>
            <a href="/public/login.php" class="btn btn-outline">Login</a>
            <a href="/public/register.php" class="btn btn-primary">Sign Up</a>
        </nav>
    </div>
</header>
<main class="container">
