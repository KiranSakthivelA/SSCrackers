<?php
// api/config.php
// Update these details with your MilesWeb database credentials

$db_host = 'localhost';
$db_user = 'codevibe1_admin'; 
$db_pass = 'YOUR_PASSWORD_HERE'; // Please enter your DB password before uploading!
$db_name = 'codevibe1_sscrackerstest'; 

// Connect to MySQL
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Ensure UTF-8 encoding
$conn->set_charset("utf8mb4");

// Start session for admin panel auth
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
