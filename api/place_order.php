<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data payload.']);
    exit;
}

$customer = $data['customer'];
$items = $data['items'];
$total = $data['total'];

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert into orders table with a placeholder order number
    $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, customer_address, customer_city, customer_pin, total_amount) VALUES ('TEMP', ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssd", 
        $customer['name'], 
        $customer['phone'], 
        $customer['email'], 
        $customer['address'], 
        $customer['city'], 
        $customer['pin'], 
        $total
    );
    
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();
    
    // Update the order number to use the auto-increment ID
    $order_number = "ORD-" . str_pad($order_id, 4, '0', STR_PAD_LEFT);
    $conn->query("UPDATE orders SET order_number = '$order_number' WHERE id = $order_id");

    // Insert order items
    $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, qty) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($items as $item) {
        $stmt_items->bind_param("iisdi", 
            $order_id, 
            $item['id'], 
            $item['name'], 
            $item['price'], 
            $item['qty']
        );
        $stmt_items->execute();
    }
    $stmt_items->close();

    // Commit
    $conn->commit();
    echo json_encode(['success' => true, 'order_number' => $order_number, 'message' => 'Order placed successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()]);
}
?>
