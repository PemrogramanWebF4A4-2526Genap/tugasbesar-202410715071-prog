<?php
session_start();
require_once '../middleware/buyer.php';
require_once '../config/database.php';
require_once '../helpers/mailer.php';

$user_id = $_SESSION['user']['id'];
$order_id = intval($_POST['order_id'] ?? 0);

if (!$order_id || !isset($_FILES['payment_proof'])) {
    header('Location: ../views/buyer/orders.php');
    exit;
}

$proof = $_FILES['payment_proof'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
$extension = strtolower(pathinfo($proof['name'], PATHINFO_EXTENSION));

if ($proof['error'] !== UPLOAD_ERR_OK || !in_array($extension, $allowedExtensions, true)) {
    header('Location: ../views/buyer/orders.php?upload_error=1');
    exit;
}

$orderRes = mysqli_query($conn, "SELECT id FROM orders WHERE id='$order_id' AND user_id='$user_id' LIMIT 1");
if (!mysqli_num_rows($orderRes)) {
    header('Location: ../views/buyer/orders.php');
    exit;
}

$paymentRes = mysqli_query(
    $conn,
    "
    SELECT id, status

    FROM payments

    WHERE order_id='$order_id'

    ORDER BY id DESC

    LIMIT 1
    "
);

if ($paymentRow = mysqli_fetch_assoc($paymentRes)) {

    if ($paymentRow['status'] === 'confirmed') {

        header('Location: ../views/buyer/orders.php?already_paid=1');

        exit;

    }

}

$uploadDir = __DIR__ . '/../uploads/payments';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'payment_' . $order_id . '_' . time() . '.' . $extension;
$targetPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($proof['tmp_name'], $targetPath)) {
    header('Location: ../views/buyer/orders.php?upload_error=1');
    exit;
}

$proofPath = 'uploads/payments/' . $filename;
$proofEscaped = mysqli_real_escape_string($conn, $proofPath);

$paymentRes = mysqli_query($conn, "SELECT id, proof FROM payments WHERE order_id='$order_id' ORDER BY id DESC LIMIT 1");
if ($paymentRow = mysqli_fetch_assoc($paymentRes)) {
    $paymentId = intval($paymentRow['id']);
    if (!empty($paymentRow['proof'])) {
        $oldFile = __DIR__ . '/../' . $paymentRow['proof']; 
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }
    mysqli_query($conn, "UPDATE payments SET proof='$proofEscaped', status='pending' WHERE id='$paymentId'");
} else {
    mysqli_query($conn, "INSERT INTO payments (order_id, payment_method, proof, status, created_at) VALUES ('$order_id', 'bank_transfer', '$proofEscaped', 'pending', NOW())");
}

mysqli_query($conn, "UPDATE orders SET status='pending' WHERE id='$order_id'");
$adminRes = mysqli_query(
    $conn,
    "
    SELECT id

    FROM users

    WHERE role='admin'
    "
);

while ($admin = mysqli_fetch_assoc($adminRes)) {

    $adminId = intval($admin['id']);

    $message = mysqli_real_escape_string(
        $conn,
        "Ada bukti pembayaran baru menunggu verifikasi."
    );

    mysqli_query(
        $conn,
        "
        INSERT INTO notifications (
            user_id,
            message
        )

        VALUES (
            '$adminId',
            '$message'
        )
        "
    );

    $adminData = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT name, email FROM users WHERE id='$adminId'"
        )
    );

    if ($adminData) {

        sendEmail(
            $adminData['email'],
            'Konfirmasi Pembayaran Baru',
            "
            <h2>Pembayaran Baru</h2>
            <p>Ada bukti pembayaran baru yang perlu diverifikasi admin.</p>
            <p>Order ID: <b>$order_id</b></p>
            "
        );
    }
}

header('Location: ../views/buyer/orders.php?uploaded=1');
exit;
