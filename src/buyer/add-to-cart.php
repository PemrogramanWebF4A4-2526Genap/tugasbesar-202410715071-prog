<?php
session_start();
require_once '../middleware/buyer.php';
require_once '../config/database.php';

if (!isset($_SESSION['user'])) {

    header('Location: ../views/public/login.php');
    exit;

}

if ($_SESSION['user']['role'] !== 'buyer') {

    echo "
    <script>
        alert('Hanya akun buyer yang dapat membeli produk.');
        window.history.back();
    </script>
    ";

    exit;

}

$user_id = $_SESSION['user']['id'];
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
if ($quantity < 1) $quantity = 1;

// check product
$res = mysqli_query($conn, "SELECT id, stock, status FROM products WHERE id='$product_id' LIMIT 1");
$product = mysqli_fetch_assoc($res);
if (!$product || $product['status'] !== 'active') {
    echo "Produk tidak tersedia.";
    exit;
}

// if stock is limited, cap quantity
if ($product['stock'] !== null && $product['stock'] >= 0) {
    $stock = intval($product['stock']);
    if ($quantity > $stock) $quantity = $stock;
}

// check existing cart item
$existsRes = mysqli_query($conn, "SELECT id, quantity FROM carts WHERE user_id='$user_id' AND product_id='$product_id' LIMIT 1");
if ($exists = mysqli_fetch_assoc($existsRes)) {
    $newQ = $exists['quantity'] + $quantity;
    if ($product['stock'] > 0 && $newQ > $product['stock']) {
        $newQ = $product['stock'];
    }
    mysqli_query($conn, "UPDATE carts SET quantity='$newQ' WHERE id='{$exists['id']}'");
} else {
    mysqli_query($conn, "INSERT INTO carts (user_id, product_id, quantity, created_at) VALUES ('$user_id', '$product_id', '$quantity', NOW())");
}

header('Location: ../views/buyer/cart.php');
exit;
?>