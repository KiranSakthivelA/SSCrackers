<?php
require_once 'config.php';

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Truncate the tables
$success1 = $conn->query("TRUNCATE TABLE order_items");
$success2 = $conn->query("TRUNCATE TABLE orders");

// Enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

if ($success1 && $success2) {
    echo "<h2 style='color: green; font-family: sans-serif; text-align: center; padding: 50px;'>Orders have been reset! New orders will start from ID 1.</h2>";
    echo "<div style='text-align:center;'><a href='../admin/index.php' style='padding: 10px 20px; background: #006838; color: white; text-decoration: none; border-radius: 8px; font-family: sans-serif;'>Go back to Admin Panel</a></div>";
} else {
    echo "<h2 style='color: red;'>Failed to reset orders. " . $conn->error . "</h2>";
}
?>
