<?php
/**
 * Logout Handler for Appliances Management System
 */

require_once 'config/config.php';

// Only process logout if user is logged in
if (isLoggedIn()) {
    $auth = new Auth();
    $auth->logout();
}

// Redirect to login page with logout message
redirectTo('login.php?logout=1');
?>