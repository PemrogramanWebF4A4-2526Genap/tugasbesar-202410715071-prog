<?php
session_start();
require_once '../middleware/buyer.php';
require_once '../config/database.php';

$id = intval($_POST['id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
$user_id = $_SESSION['user']['id'];

if ($id && $quantity > 0) {
    mysqli_query($conn, "UPDATE carts SET quantity='$quantity' WHERE id='$id' AND user_id='$user_id'");
}

echo json_encode([
    'success' => true
]);
exit;
?>