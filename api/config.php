<?php
// api/config.php
// Update these details with your MilesWeb database credentials

$db_host = 'localhost';
$db_user = 'dealsfor2_sscrackers'; 
$db_pass = 'DBSScrackers@2026'; 
$db_name = 'dealsfor2_SSCrackers_DB'; 

// Connect to MySQL (wrapped in try-catch for PHP 8.1+ exception handling)
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection (for older PHP versions)
    if ($conn->connect_error) {
        die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
    }
} catch (mysqli_sql_exception $e) {
    die("<h2>Database Connection Error</h2><p style='color:red;'>Access Denied. Please check your database username, password, and database name in <b>api/config.php</b>.</p><p>Error details: " . $e->getMessage() . "</p>");
}

// Ensure UTF-8 encoding
$conn->set_charset("utf8mb4");

// Start session for admin panel auth
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
