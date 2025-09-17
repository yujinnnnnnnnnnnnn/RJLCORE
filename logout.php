<?php
require_once 'config/config.php';
require_once 'classes/Auth.php';

$auth = new Auth();
$result = $auth->logout();

// Redirect to home page with logout message
redirect('index.php?message=logged_out');
?>