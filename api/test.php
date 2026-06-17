<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP is working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

if (function_exists('mysqli_connect')) {
    echo "<p style='color:green;'>MySQLi extension is INSTALLED.</p>";
} else {
    echo "<p style='color:red;'>MySQLi extension is MISSING.</p>";
}

echo "<h2>Testing Database Connection...</h2>";
require_once 'config.php';
echo "<p style='color:green;'>config.php was loaded successfully!</p>";
?>
