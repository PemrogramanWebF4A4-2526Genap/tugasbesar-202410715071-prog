<?php
session_start();
require_once '../middleware/admin.php';
require_once '../config/database.php';
require_once '../helpers/mailer.php';

$order_id = intval($_POST['order_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$order_id || !$action) {
    header('Location: ../views/admin/orders.php?update_error=1');
    exit;
}

$paymentRes = mysqli_query($conn, "SELECT id, status FROM payments WHERE order_id='$order_id' ORDER BY id DESC LIMIT 1");
if (!mysqli_num_rows($paymentRes)) {
    header('Location: ../views/admin/orders.php?update_error=1');
    exit;
}

$payment = mysqli_fetch_assoc($paymentRes);
$paymentId = intval($payment['id']);
$currentStatus = $payment['status'];

$orderRes = mysqli_query(
    $conn,
    "
    SELECT
    orders.user_id,
    users.email

    FROM orders

    JOIN users
    ON users.id = orders.user_id

    WHERE orders.id='$order_id'

    LIMIT 1
    "
);

$order = mysqli_fetch_assoc($orderRes);

$buyerId = intval($order['user_id']);

if ($action === 'confirm' && $currentStatus === 'pending') {

    mysqli_query(
        $conn,
        "UPDATE payments SET status='confirmed' WHERE id='$paymentId'"
    );

    mysqli_query(
        $conn,
        "UPDATE orders SET status='processed' WHERE id='$order_id'"
    );

    $message = mysqli_real_escape_string(
        $conn,
        "Pembayaran berhasil dikonfirmasi. Pesanan sedang diproses."
    );

    mysqli_query(
        $conn,
        "
        INSERT INTO notifications (
            user_id,
            message
        )

        VALUES (
            '$buyerId',
            '$message'
        )
        "
    );

    $buyerEmail = $order['email'];

    sendEmail(
        $buyerEmail,
        'Pembayaran Dikonfirmasi',
        "
        <h2>Pembayaran berhasil dikonfirmasi</h2>

        <p>Pesanan kamu sedang diproses seller.</p>
        "
    );

    header('Location: ../views/admin/orders.php?updated=1');

    exit;
}

if ($action === 'reject' && $currentStatus === 'pending') {

    mysqli_query(
        $conn,
        "UPDATE orders SET status='payment_rejected' WHERE id='$order_id'"
    );

    mysqli_query(
        $conn,
        "UPDATE payments SET status='rejected' WHERE id='$paymentId'"
    );

    $message = mysqli_real_escape_string(
        $conn,
        "Pembayaran ditolak. Silakan upload ulang bukti pembayaran."
    );

    mysqli_query(
        $conn,
        "
        INSERT INTO notifications (
            user_id,
            message
        )

        VALUES (
            '$buyerId',
            '$message'
        )
        "
    );

    $buyerEmail = $order['email'];

    sendEmail(
        $buyerEmail,
        'Pembayaran Ditolak',
        "
        <h2>Pembayaran ditolak.</h2>

        <p>Silakan upload ulang bukti pembayaran.</p>
        "
    );

    header('Location: ../views/admin/orders.php?updated=1');

    exit;

}

header('Location: ../views/admin/orders.php?update_error=1');
exit;
