<?php
require_once 'config.php';

echo "<h2>Setting up Database Tables...</h2>";

// 1. Create orders table
$sql_orders = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    customer_address TEXT NOT NULL,
    customer_city VARCHAR(50) NOT NULL,
    customer_pin VARCHAR(20) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_orders) === TRUE) {
    echo "<p style='color:green;'>Table 'orders' created successfully.</p>";
} else {
    echo "<p style='color:red;'>Error creating table 'orders': " . $conn->error . "</p>";
}

// 2. Create order_items table
$sql_items = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    qty INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)";

if ($conn->query($sql_items) === TRUE) {
    echo "<p style='color:green;'>Table 'order_items' created successfully.</p>";
} else {
    echo "<p style='color:red;'>Error creating table 'order_items': " . $conn->error . "</p>";
}

echo "<h3>Setup Complete! You can now safely delete this file or navigate away.</h3>";
?>
