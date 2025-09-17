<?php
if (!isset($page_title)) { $page_title = 'AppliancePro'; }
require_once __DIR__ . '/../config/app.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#113F67">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    <link rel="icon" href="<?php echo asset('assets/favicon.png'); ?>">
</head>
<body>

