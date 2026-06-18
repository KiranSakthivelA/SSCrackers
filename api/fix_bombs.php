<?php
require_once 'config.php';

echo "<h2>Fixing Bomb Categories in Database...</h2>";

$sql = "UPDATE products SET category = 'bombs' WHERE name LIKE '%BOMB%' OR name LIKE '%PAPER%'";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green; font-weight: bold;'>Successfully updated " . $conn->affected_rows . " products!</p>";
    echo "<p>Your 'Bombs' category should now show all the bombs on the website and CMS.</p>";
    echo "<p>You can now delete this file.</p>";
} else {
    echo "<p style='color:red;'>Error updating records: " . $conn->error . "</p>";
}

$conn->close();
?>
