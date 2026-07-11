<?php

session_start();

require_once '../middleware/admin.php';
require_once '../config/database.php';

$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');

if (!$id || !$name) {

    header('Location: ../views/admin/categories.php');
    exit;

}

// get existing category
$res = mysqli_query(
    $conn,
    "SELECT * FROM categories WHERE id='$id' LIMIT 1"
);

$category = mysqli_fetch_assoc($res);

if (!$category) {

    header('Location: ../views/admin/categories.php');
    exit;

}

$nameEsc = mysqli_real_escape_string($conn, $name);

// prevent duplicate
$exists = mysqli_query(
    $conn,
    "SELECT id
     FROM categories
     WHERE name='$nameEsc'
     AND id != '$id'
     LIMIT 1"
);

if (mysqli_num_rows($exists)) {

    header('Location: ../views/admin/categories.php?exists=1');
    exit;

}

$iconName = $category['icon'];

// upload new icon
if (!empty($_FILES['icon']['name'])) {

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    $ext = strtolower(
        pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION)
    );

    if (in_array($ext, $allowed)) {

        $newIcon = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $uploadPath = __DIR__ . '/../uploads/categories/' . $newIcon;

        if (
            move_uploaded_file(
                $_FILES['icon']['tmp_name'],
                $uploadPath
            )
        ) {

            // delete old icon
            if (!empty($category['icon'])) {

                $oldPath = __DIR__ . '/../uploads/categories/' . $category['icon'];

                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }

            }

            $iconName = $newIcon;

        }

    }

}

mysqli_query(
    $conn,
    "UPDATE categories
     SET
        name='$nameEsc',
        icon='$iconName'
     WHERE id='$id'"
);

header('Location: ../views/admin/categories.php?updated=1');
exit;

?>