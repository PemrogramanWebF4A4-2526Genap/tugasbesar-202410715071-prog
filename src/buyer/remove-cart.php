<?php
session_start();
require_once '../middleware/buyer.php';
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user']['id'];

if ($id) {
    mysqli_query($conn, "DELETE FROM carts WHERE id='$id' AND user_id='$user_id'");
}

header('Location: ../views/buyer/cart.php');
exit;
?>