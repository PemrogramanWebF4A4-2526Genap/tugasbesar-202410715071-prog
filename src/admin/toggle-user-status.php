<?php

session_start();

require_once '../middleware/admin.php';
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

if (!$id || !$status) {

    header('Location: ../views/admin/users.php');
    exit;

}

$allowedStatus = ['active', 'suspended'];

if (!in_array($status, $allowedStatus)) {

    header('Location: ../views/admin/users.php');
    exit;

}

$userRes = mysqli_query(
    $conn,
    "SELECT id, role FROM users WHERE id='$id' LIMIT 1"
);

$user = mysqli_fetch_assoc($userRes);

if (!$user) {

    header('Location: ../views/admin/users.php');
    exit;

}

$currentAdminId = $_SESSION['user']['id'];

if ($user['id'] == $currentAdminId) {

    header('Location: ../views/admin/users.php?self_error=1');
    exit;

}

mysqli_query(
    $conn,
    "UPDATE users SET status='$status' WHERE id='$id'"
);

header('Location: ../views/admin/users.php?updated=1');
exit;

?>