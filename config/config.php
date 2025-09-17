<?php
session_start();

// Application configuration
define('APP_NAME', 'Appliance Management System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/appliance_store/');

// Security settings
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Email configuration (for password reset)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@appliancestore.com');
define('FROM_NAME', 'Appliance Store');

// File upload settings
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Pagination settings
define('RECORDS_PER_PAGE', 10);

// Include database connection
require_once 'database.php';

// Utility functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function check_role($required_role) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = $_SESSION['role_name'] ?? '';
    
    if (is_array($required_role)) {
        return in_array($user_role, $required_role);
    }
    
    return $user_role === $required_role;
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_role($required_role) {
    if (!check_role($required_role)) {
        redirect('unauthorized.php');
    }
}

function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

function format_date($date) {
    return date('M d, Y', strtotime($date));
}

function format_datetime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}
?>