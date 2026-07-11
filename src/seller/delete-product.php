<?php
require_once '../middleware/seller.php';
require_once '../config/database.php';

$id = $_GET['id'];

// fetch product to delete image file
$res = mysqli_query($conn, "SELECT image FROM products WHERE id='$id' LIMIT 1");
$prod = mysqli_fetch_assoc($res);
if ($prod && !empty($prod['image'])) {
    $path = __DIR__ . '/../uploads/products/' . $prod['image'];
    if (file_exists($path)) {
        @unlink($path);
    }
}

mysqli_query($conn, "DELETE FROM products WHERE id='$id'");

header('Location: ../views/seller/products.php');
exit;

?>