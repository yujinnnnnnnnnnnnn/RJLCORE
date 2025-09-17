<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Auth;
Auth::logout();
header('Location: /public/login.php');
exit;
