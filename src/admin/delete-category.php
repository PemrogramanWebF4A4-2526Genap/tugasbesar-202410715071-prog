<?php

session_start();

require_once '../middleware/admin.php';
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {

    header('Location: ../views/admin/categories.php');
    exit;

}

// get category
$res = mysqli_query(
    $conn,
    "SELECT * FROM categories WHERE id='$id' LIMIT 1"
);

$category = mysqli_fetch_assoc($res);

if (!$category) {

    header('Location: ../views/admin/categories.php');
    exit;

}

// prevent delete if category still used
$productRes = mysqli_query(
    $conn,
    "SELECT id
     FROM products
     WHERE category_id='$id'
     LIMIT 1"
);

if (mysqli_num_rows($productRes)) {

    header('Location: ../views/admin/categories.php?used=1');
    exit;

}

// delete icon
if (!empty($category['icon'])) {

    $iconPath = __DIR__ . '/../uploads/categories/' . $category['icon'];

    if (file_exists($iconPath)) {
        @unlink($iconPath);
    }

}

// delete category
mysqli_query(
    $conn,
    "DELETE FROM categories WHERE id='$id'"
);

header('Location: ../views/admin/categories.php?deleted=1');
exit;

?>