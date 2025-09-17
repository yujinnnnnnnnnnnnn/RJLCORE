<?php
declare(strict_types=1);

// Update credentials for your XAMPP MySQL environment
$DB_CONFIG = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'appliancepro',
    'username' => 'root',
    'password' => '', // default in XAMPP is empty; set a strong password in production
    'charset' => 'utf8mb4',
];

function db(): mysqli {
    static $conn = null;
    global $DB_CONFIG;
    if ($conn instanceof mysqli) {
        return $conn;
    }
    $conn = new mysqli(
        $DB_CONFIG['host'],
        $DB_CONFIG['username'],
        $DB_CONFIG['password'],
        $DB_CONFIG['database'],
        $DB_CONFIG['port']
    );
    if ($conn->connect_error) {
        http_response_code(500);
        die('Database connection failed');
    }
    $conn->set_charset($DB_CONFIG['charset']);
    return $conn;
}

function safe_query(string $sql, string $types = '', array $params = []): mysqli_stmt {
    $conn = db();
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        die('Query prepare failed');
    }
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        http_response_code(500);
        die('Query execute failed');
    }
    return $stmt;
}

