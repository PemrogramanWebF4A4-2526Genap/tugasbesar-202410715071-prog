<?php

session_start();

require_once '../middleware/seller.php';
require_once '../config/database.php';

$name = trim($_POST['name'] ?? '');
$category_id = $_POST['category_id'] ?? '';
$price = $_POST['price'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'draft';

$seller_id = $_SESSION['user']['id'];

// Basic validation
if (!$name || !$category_id || !$price) {
    echo "Nama, kategori, dan harga wajib diisi.";
    exit;
}

$imageName = '';
if (!empty($_FILES['image']['name'])) {
    $allowed = ['image/png', 'image/jpg', 'image/jpeg'];
    $fileType = $_FILES['image']['type'];
    $fileSize = $_FILES['image']['size'];

    if (!in_array($fileType, $allowed)) {
        echo "Format gambar tidak didukung.";
        exit;
    }

    if ($fileSize > 5 * 1024 * 1024) {
        echo "Ukuran gambar maksimal 5MB.";
        exit;
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $tmp = $_FILES['image']['tmp_name'];
    $uploadPath = __DIR__ . '/../uploads/products/' . $imageName;

    if (!move_uploaded_file($tmp, $uploadPath)) {
        echo "Gagal mengunggah gambar.";
        exit;
    }
}

$query = "INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'iissdiss', $seller_id, $category_id, $name, $description, $price, $stock, $imageName, $status);

$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    header('Location: ../views/seller/products.php');
    exit;
} else {
    echo "Gagal tambah produk: " . mysqli_error($conn);
}

?>