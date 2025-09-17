-- Appliance Management System Database Schema
-- Created for XAMPP MySQL

CREATE DATABASE IF NOT EXISTS appliance_store;
USE appliance_store;

-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    reset_token VARCHAR(255) NULL,
    reset_expires DATETIME NULL
);

-- Roles table for role-based access control
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(20) UNIQUE NOT NULL,
    description TEXT
);

-- Insert default roles
INSERT INTO roles (role_name, description) VALUES 
('admin', 'Full system access and management'),
('staff', 'Sales and inventory management'),
('customer', 'Customer portal access');

-- Products/Appliances table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50),
    category VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 5,
    image_path VARCHAR(255),
    warranty_months INT DEFAULT 12,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Sales table for recording transactions
CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    staff_id INT NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('full', 'installment') NOT NULL,
    payment_status ENUM('paid', 'partial', 'pending') DEFAULT 'pending',
    discount DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (staff_id) REFERENCES users(id)
);

-- Sale items table for individual products in a sale
CREATE TABLE sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Installments table for payment plans
CREATE TABLE installments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0,
    payment_date TIMESTAMP NULL,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
);

-- Transactions table for payment records
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    installment_id INT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer', 'check') NOT NULL,
    reference_number VARCHAR(100),
    processed_by INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (installment_id) REFERENCES installments(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

-- Notifications table for customer communications
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('payment_reminder', 'payment_received', 'general', 'overdue') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Inventory logs for tracking stock changes
CREATE TABLE inventory_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    action ENUM('add', 'remove', 'adjust', 'sale') NOT NULL,
    quantity_change INT NOT NULL,
    previous_stock INT NOT NULL,
    new_stock INT NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Add foreign key constraint for users table
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id);

-- Create default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role_id) VALUES 
('admin', 'admin@appliancestore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 1);

-- Sample product data
INSERT INTO products (name, brand, model, category, description, price, cost_price, stock_quantity, image_path) VALUES 
('Samsung Refrigerator', 'Samsung', 'RF28R7351SG', 'Refrigerator', '28 cu. ft. 4-Door French Door Refrigerator with FlexZone Drawer', 2499.99, 1800.00, 15, 'images/samsung-fridge.jpg'),
('LG Washing Machine', 'LG', 'WM3900HWA', 'Washing Machine', '4.5 cu. ft. Ultra Large Capacity Smart Front Load Washer', 899.99, 650.00, 12, 'images/lg-washer.jpg'),
('Whirlpool Dishwasher', 'Whirlpool', 'WDT750SAKZ', 'Dishwasher', 'Stainless Steel Tub Dishwasher with Third Level Rack', 649.99, 480.00, 8, 'images/whirlpool-dishwasher.jpg'),
('GE Microwave', 'GE', 'JVM6175SKSS', 'Microwave', '1.7 Cu. Ft. Over-the-Range Microwave Oven', 299.99, 220.00, 20, 'images/ge-microwave.jpg'),
('KitchenAid Stand Mixer', 'KitchenAid', 'KSM150PSER', 'Small Appliance', 'Artisan Series 5-Quart Tilt-Head Stand Mixer', 399.99, 280.00, 25, 'images/kitchenaid-mixer.jpg');