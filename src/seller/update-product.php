<?php

session_start();

require_once '../middleware/seller.php';
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo "Invalid request";
    exit;
}

// fetch existing
$userId = $_SESSION['user']['id'];
$res = mysqli_query(
    $conn,
    "SELECT * FROM products
     WHERE id='$id'
     AND seller_id='$userId'
     LIMIT 1"
);
$product = mysqli_fetch_assoc($res);
if (!$product) {
    echo "Produk tidak ditemukan";
    exit;
}

$name = trim($_POST['name'] ?? '');
$category_id = $_POST['category_id'] ?? '';
$category_id = intval($category_id);
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'draft';


$allowedStatus = ['active', 'draft'];

if (!in_array($status, $allowedStatus)) {
    $status = 'draft';
}

if (!$name || !$category_id || !$price) {
    echo "Nama, kategori, dan harga wajib diisi.";
    exit;
}

$imageName = $product['image'];
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

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $imageName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $tmp = $_FILES['image']['tmp_name'];
    $uploadPath = __DIR__ . '/../uploads/products/' . $imageName;

    if (!move_uploaded_file($tmp, $uploadPath)) {
        echo "Gagal mengunggah gambar.";
        exit;
    }

    // remove old file
    if (!empty($product['image'])) {
        $old = __DIR__ . '/../uploads/products/' . $product['image'];
        if (file_exists($old)) @unlink($old);
    }
}

$query = "UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, image=?, status=? WHERE id=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'issdissi', $category_id, $name, $description, $price, $stock, $imageName, $status, $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
    header('Location: ../views/seller/products.php');
    exit;
} else {
    echo "Gagal update produk: " . mysqli_error($conn);
}

?>