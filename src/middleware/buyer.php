<?php

require_once 'auth.php';

if ($_SESSION['user']['role'] !== 'buyer') {

    header('Location: ../views/public/home.php');
    exit;
}

?>