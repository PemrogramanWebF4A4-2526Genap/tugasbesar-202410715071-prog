<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {

    $role = $_SESSION['user']['role'];

    if ($role === 'admin') {

        header('Location: ../admin/dashboard.php');

    } elseif ($role === 'seller') {

        header('Location: ../seller/dashboard.php');

    } else {

        header('Location: ../views/public/home.php');

    }

    exit;
}

?>