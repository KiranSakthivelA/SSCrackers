<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($order_id > 0 && in_array($status, ['Completed', 'Cancelled', 'Pending'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to bookings tab (or history if completed)
header("Location: index.php");
exit;
?>
