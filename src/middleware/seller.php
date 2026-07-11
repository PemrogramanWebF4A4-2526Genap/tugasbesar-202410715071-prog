<?php

require_once 'auth.php';

if ($_SESSION['user']['role'] !== 'seller') {

    header('Location: ../views/public/home.php');
    exit;
}

?>