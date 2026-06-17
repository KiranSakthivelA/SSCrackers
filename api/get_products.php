<?php
// api/get_products.php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once 'config.php';

// Fetch all products from the database
$products = [];
try {
    $sql = "SELECT * FROM products ORDER BY id ASC";
    $result = $conn->query($sql);
} catch (mysqli_sql_exception $e) {
    $result = false;
}

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Cast numeric values appropriately
        $row['id'] = (int)$row['id'];
        $row['mrp'] = (int)$row['mrp'];
        $row['price'] = (int)$row['price'];
        
        // If an image URL exists, optionally we can provide it.
        // We ensure it falls back if empty or keep it as is.
        $products[] = $row;
    }
}

echo json_encode($products);

$conn->close();
?>
