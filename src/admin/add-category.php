<?php

session_start();

require_once '../middleware/admin.php';
require_once '../config/database.php';

$name = trim($_POST['name'] ?? '');

if (!$name) {

    header('Location: ../views/admin/categories.php');
    exit;

}

$nameEsc = mysqli_real_escape_string($conn, $name);

// prevent duplicate
$exists = mysqli_query(
    $conn,
    "SELECT id FROM categories WHERE name='$nameEsc' LIMIT 1"
);

if (mysqli_num_rows($exists)) {

    header('Location: ../views/admin/categories.php?exists=1');
    exit;

}

$iconName = null;

if (!empty($_FILES['icon']['name'])) {

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    $ext = strtolower(
        pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION)
    );

    if (in_array($ext, $allowed)) {

        $iconName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        move_uploaded_file(
            $_FILES['icon']['tmp_name'],
            __DIR__ . '/../uploads/categories/' . $iconName
        );

    }

}

mysqli_query(
    $conn,
    "INSERT INTO categories (name, icon, created_at)
     VALUES ('$nameEsc', '$iconName', NOW())"
);

header('Location: ../views/admin/categories.php?created=1');
exit;

?>