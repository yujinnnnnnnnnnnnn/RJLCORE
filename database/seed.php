<?php
// Simple seeder to create an admin user and a sample product.
// IMPORTANT: Delete this file after running once.
require_once __DIR__ . '/../config/db.php';

// Create admin if not exists
$email = 'admin@example.com';
$exists = safe_query('SELECT id FROM users WHERE email = ?', 's', [$email])->get_result()->fetch_assoc();
if (!$exists) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    safe_query('INSERT INTO users (role_id, email, password_hash, full_name) VALUES (1, ?, ?, ?)', 'sss', [$email, $hash, 'System Admin']);
    echo "Created admin user: $email / admin123\n";
} else {
    echo "Admin user already exists: $email\n";
}

// Create sample product
$sku = 'TV-42-001';
$p = safe_query('SELECT id FROM products WHERE sku = ?', 's', [$sku])->get_result()->fetch_assoc();
if (!$p) {
    safe_query('INSERT INTO products (sku, name, brand, category, price, stock) VALUES (?,?,?,?,?,?)', 'ssssdi', [$sku, '42" 4K Smart TV', 'Acme', 'Television', 299.99, 10]);
    echo "Inserted sample product.\n";
} else {
    echo "Sample product already exists.\n";
}

echo "Seed complete.\n";

