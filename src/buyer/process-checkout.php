<?php
session_start();
require_once '../middleware/buyer.php';
require_once '../config/database.php';
require_once '../helpers/mailer.php';
mysqli_begin_transaction($conn);

$user_id = $_SESSION['user']['id'];
$receiver = trim($_POST['receiver'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$shipping_method = $_POST['shipping_method'] ?? 'reguler';
$payment_method = $_POST['payment_method'] ?? 'bank_transfer';

if (!$receiver || !$phone || !$address) {
    mysqli_rollback($conn);
    header('Location: ../views/buyer/checkout.php');
    exit;
}

$cartRes = mysqli_query($conn, "SELECT carts.*, products.seller_id, products.price, products.stock, products.status FROM carts JOIN products ON carts.product_id = products.id WHERE carts.user_id='$user_id'");
$cartItems = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($cartRes)) {
    if ($item['status'] !== 'active') {
        continue;
    }

    $quantity = intval($item['quantity']);
    $price = floatval($item['price']);
    $lineTotal = $quantity * $price;
    $subtotal += $lineTotal;
    $cartItems[] = $item;
}

if (count($cartItems) === 0) {
    header('Location: ../views/buyer/cart.php');
    exit;
}

$city = $_POST['city'] ?? 'luar_kota';

if ($city === 'jakarta') {

    $shippingFee = 10000;

} elseif ($city === 'bekasi') {

    $shippingFee = 15000;

} elseif ($city === 'bandung') {

    $shippingFee = 20000;

} else {

    $shippingFee = 30000;

}

if ($shipping_method === 'express') {

    $shippingFee += 15000;

}
$totalAmount = $subtotal + $shippingFee;

$invoiceNumber = 'INV-' . date('YmdHis') . '-' . rand(100, 999);
$shippingAddress = mysqli_real_escape_string($conn, $address);

$hasShippingColumns = false;
$checkShipping = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'shipping_method'");
if ($checkShipping && mysqli_num_rows($checkShipping)) {
    $checkFee = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'shipping_fee'");
    if ($checkFee && mysqli_num_rows($checkFee)) {
        $hasShippingColumns = true;
    }
}

if ($hasShippingColumns) {
    $orderSql = "INSERT INTO orders (user_id, invoice_number, shipping_address, shipping_method, shipping_fee, total_amount, status, created_at) VALUES ('$user_id', '$invoiceNumber', '$shippingAddress', '$shipping_method', '$shippingFee', '$totalAmount', 'pending', NOW())";
} else {
    $orderSql = "INSERT INTO orders (user_id, invoice_number, shipping_address, total_amount, status, created_at) VALUES ('$user_id', '$invoiceNumber', '$shippingAddress', '$totalAmount', 'pending', NOW())";
}
if (!mysqli_query($conn, $orderSql)) {
    mysqli_rollback($conn);
    header('Location: ../views/buyer/checkout.php');
    exit;
}
$orderId = mysqli_insert_id($conn);

$paymentSql = "INSERT INTO payments (order_id, payment_method, status, created_at) VALUES ('$orderId', '$payment_method', 'pending', NOW())";
mysqli_query($conn, $paymentSql);

foreach ($cartItems as $item) {
    $productId = intval($item['product_id']);
    $sellerId = intval($item['seller_id']);
    $sellerMessage = mysqli_real_escape_string(
        $conn,
        "Pesanan baru masuk untuk produk Anda."
    );

    mysqli_query(
        $conn,
        "
        INSERT INTO notifications (
            user_id,
            message
        )

        VALUES (
            '$sellerId',
            '$sellerMessage'
        )
        "
    );
    $quantity = intval($item['quantity']);
    $price = floatval($item['price']);
    $subtotalLine = $quantity * $price;

    if ($quantity > $item['stock']) {
        mysqli_rollback($conn);
        echo "Stock produk tidak mencukupi.";
        exit;
    }

    mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price, subtotal) VALUES ('$orderId', '$productId', '$sellerId', '$quantity', '$price', '$subtotalLine')");

    if (is_numeric($item['stock'])) {
        $newStock = max(0, intval($item['stock']) - $quantity);
        mysqli_query($conn, "UPDATE products SET stock='$newStock' WHERE id='$productId'");
    }
}

$message = mysqli_real_escape_string(
    $conn,
    "Pesanan baru berhasil dibuat."
);

mysqli_query(
    $conn,
    "
    INSERT INTO notifications (
        user_id,
        message
    )

    VALUES (
        '$user_id',
        '$message'
    )
    "
);

mysqli_query($conn, "DELETE FROM carts WHERE user_id='$user_id'");
$userEmail = $_SESSION['user']['email'];

sendEmail(
    $userEmail,
    'Pesanan Berhasil Dibuat',
    "
    <h2>Pesanan berhasil dibuat.</h2>

    <p>Invoice: $invoiceNumber</p>

    <p>Total: Rp " . number_format($totalAmount) . "</p>

    <p>Terima kasih sudah berbelanja di UMKM Marketplace.</p>
    "
);
mysqli_commit($conn);
header('Location: ../views/buyer/orders.php?created=1');
exit;
