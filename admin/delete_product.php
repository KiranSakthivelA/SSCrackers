<?php
require_once 'auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    // Optionally fetch image and delete it
    $sql = "SELECT image_url FROM products WHERE id = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['image_url'] && file_exists('../' . $row['image_url'])) {
            unlink('../' . $row['image_url']);
        }
    }
    
    $del_sql = "DELETE FROM products WHERE id = $id";
    $conn->query($del_sql);
}

header("Location: index.php");
exit;
?>
