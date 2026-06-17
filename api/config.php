<?php
// api/config.php
// Update these details with your MilesWeb database credentials

$db_host = 'localhost';
$db_user = 'root'; // e.g., 'your_cpanel_username_user'
$db_pass = ''; // your database password
$db_name = 'ss_crackers'; // e.g., 'your_cpanel_username_db'

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
