<?php
/**
 * Setup Script for Appliances Management System
 * Run this once to initialize the database and create sample data
 */

require_once 'config/config.php';

// Check if database connection works
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<h1>Appliances Management System - Setup</h1>";
    echo "<p>Setting up the database and creating sample data...</p>";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/database/appliances_management.sql');
    
    if (!$sql) {
        throw new Exception("Could not read SQL file");
    }
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $db->exec($statement);
            $success_count++;
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "<p style='color: red;'>Error executing statement: " . htmlspecialchars($e->getMessage()) . "</p>";
                $error_count++;
            }
        }
    }
    
    echo "<p style='color: green;'>Database setup completed! Executed $success_count statements successfully.</p>";
    
    if ($error_count > 0) {
        echo "<p style='color: orange;'>$error_count statements had errors (may be normal if database already exists).</p>";
    }
    
    // Create sample products
    $sample_products = [
        [
            'category_id' => 1, // Refrigerators
            'product_name' => 'Samsung French Door Refrigerator',
            'brand' => 'Samsung',
            'model' => 'RF28T5001SR',
            'description' => '28 cu. ft. 3-Door French Door Refrigerator with CoolSelect Pantry',
            'price' => 1299.99,
            'cost_price' => 899.99,
            'stock_quantity' => 15,
            'specifications' => json_encode([
                'capacity' => '28 cu. ft.',
                'type' => 'French Door',
                'energy_star' => true,
                'color' => 'Stainless Steel'
            ])
        ],
        [
            'category_id' => 2, // Washing Machines
            'product_name' => 'LG Front Load Washing Machine',
            'brand' => 'LG',
            'model' => 'WM3900HWA',
            'description' => '4.5 cu. ft. Ultra Large Capacity Smart Front Load Washer',
            'price' => 899.99,
            'cost_price' => 649.99,
            'stock_quantity' => 12,
            'specifications' => json_encode([
                'capacity' => '4.5 cu. ft.',
                'type' => 'Front Load',
                'smart_features' => true,
                'color' => 'White'
            ])
        ],
        [
            'category_id' => 3, // Air Conditioners
            'product_name' => 'Frigidaire Window Air Conditioner',
            'brand' => 'Frigidaire',
            'model' => 'FFRA051WAE',
            'description' => '5,000 BTU Window-Mounted Room Air Conditioner',
            'price' => 199.99,
            'cost_price' => 149.99,
            'stock_quantity' => 25,
            'specifications' => json_encode([
                'btu' => '5,000',
                'room_size' => 'Up to 150 sq. ft.',
                'energy_star' => true,
                'type' => 'Window Mount'
            ])
        ],
        [
            'category_id' => 4, // Kitchen Appliances
            'product_name' => 'KitchenAid Stand Mixer',
            'brand' => 'KitchenAid',
            'model' => 'KSM150PSER',
            'description' => 'Artisan Series 5-Quart Tilt-Head Stand Mixer',
            'price' => 379.99,
            'cost_price' => 279.99,
            'stock_quantity' => 20,
            'specifications' => json_encode([
                'capacity' => '5 Quarts',
                'power' => '325 Watts',
                'attachments_included' => true,
                'color' => 'Empire Red'
            ])
        ],
        [
            'category_id' => 5, // Television & Audio
            'product_name' => 'Sony 55" 4K Smart TV',
            'brand' => 'Sony',
            'model' => 'XBR55X900H',
            'description' => '55" X900H 4K Ultra HD Full Array LED Smart Android TV',
            'price' => 999.99,
            'cost_price' => 749.99,
            'stock_quantity' => 8,
            'specifications' => json_encode([
                'screen_size' => '55 inches',
                'resolution' => '4K Ultra HD',
                'smart_platform' => 'Android TV',
                'hdr' => true
            ])
        ]
    ];
    
    // Insert sample products
    $product_sql = "INSERT INTO products (category_id, product_name, brand, model, description, price, cost_price, stock_quantity, specifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($product_sql);
    
    foreach ($sample_products as $product) {
        try {
            $stmt->execute([
                $product['category_id'],
                $product['product_name'],
                $product['brand'],
                $product['model'],
                $product['description'],
                $product['price'],
                $product['cost_price'],
                $product['stock_quantity'],
                $product['specifications']
            ]);
        } catch (PDOException $e) {
            // Product might already exist, ignore
        }
    }
    
    // Create sample users for testing
    $auth = new Auth();
    
    // Create staff user
    $staff_data = [
        'username' => 'staff',
        'email' => 'staff@appliances.com',
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'role' => 'staff'
    ];
    
    $result = $auth->createUser($staff_data, 'admin');
    if ($result['success']) {
        echo "<p style='color: green;'>Sample staff user created: staff / staff123</p>";
    }
    
    // Create customer user
    $customer_data = [
        'username' => 'customer',
        'email' => 'customer@example.com',
        'password' => 'customer123',
        'first_name' => 'John',
        'last_name' => 'Customer',
        'phone' => '555-0123',
        'address' => '123 Customer Street, City, State 12345'
    ];
    
    $result = $auth->register($customer_data);
    if ($result['success']) {
        echo "<p style='color: green;'>Sample customer user created: customer / customer123</p>";
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>Your Appliances Management System is now ready to use.</p>";
    echo "<h3>Default Login Accounts:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin / admin123</li>";
    echo "<li><strong>Staff:</strong> staff / staff123</li>";
    echo "<li><strong>Customer:</strong> customer / customer123</li>";
    echo "</ul>";
    echo "<p><a href='index.php' style='color: #113F67; font-weight: bold;'>Go to Homepage</a></p>";
    echo "<p><a href='login.php' style='color: #113F67; font-weight: bold;'>Go to Login</a></p>";
    
    // Security: Remove or rename this file after setup
    echo "<hr>";
    echo "<p style='color: red;'><strong>Security Note:</strong> Please delete or rename this setup.php file after setup is complete!</p>";
    
} catch (Exception $e) {
    echo "<h1 style='color: red;'>Setup Failed</h1>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database configuration in config/database.php</p>";
}
?>