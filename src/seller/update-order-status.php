<?php
session_start();
require_once '../middleware/seller.php';
require_once '../config/database.php';

$seller_id = intval($_SESSION['user']['id']);
$order_id = intval($_POST['order_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$order_id || !$action) {
    header('Location: ../views/seller/orders.php?update_error=1');
    exit;
}

$orderCheck = mysqli_query($conn, "SELECT o.status FROM orders o JOIN order_items oi ON oi.order_id=o.id WHERE o.id='$order_id' AND oi.seller_id='$seller_id' LIMIT 1");
if (!mysqli_num_rows($orderCheck)) {
    header('Location: ../views/seller/orders.php?update_error=1');
    exit;
}
$orderData = mysqli_fetch_assoc($orderCheck);
$currentStatus = $orderData['status'];

$allowedTransitions = [
    'process' => ['from' => 'paid', 'to' => 'processed'],
    'ship' => ['from' => 'processed', 'to' => 'shipped'],
    'complete' => ['from' => 'shipped', 'to' => 'completed'],
];

if (!isset($allowedTransitions[$action])) {
    header('Location: ../views/seller/orders.php?update_error=1');
    exit;
}

$transition = $allowedTransitions[$action];
if ($currentStatus !== $transition['from']) {
    header('Location: ../views/seller/orders.php?update_error=1');
    exit;
}

$newStatus = $transition['to'];

if ($action === 'ship') {
    $trackingNumber = trim($_POST['tracking_number'] ?? '');
    $trackingEscaped = mysqli_real_escape_string($conn, $trackingNumber);
    $hasTrackingColumn = false;
    $checkTracking = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'tracking_number'");
    if ($checkTracking && mysqli_num_rows($checkTracking)) {
        $hasTrackingColumn = true;
    }

    if ($hasTrackingColumn) {
        mysqli_query($conn, "UPDATE orders SET status='$newStatus', tracking_number='$trackingEscaped' WHERE id='$order_id'");
    } else {
        mysqli_query($conn, "UPDATE orders SET status='$newStatus' WHERE id='$order_id'");
    }
} else {
    mysqli_query($conn, "UPDATE orders SET status='$newStatus' WHERE id='$order_id'");
}

header('Location: ../views/seller/orders.php?updated=1');
exit;
