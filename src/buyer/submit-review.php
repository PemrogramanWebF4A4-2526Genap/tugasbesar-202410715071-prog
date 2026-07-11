<?php
require_once '../middleware/buyer.php';
require_once '../config/database.php';

$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$user_id = intval($_SESSION['user']['id']);

if (!$product_id || $rating < 1 || $rating > 5) {
    header('Location: ../views/public/product-detail.php?id=' . $product_id . '&review_error=1');
    exit;
}

$purchaseCheck = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS purchased FROM orders o JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id='$user_id' AND oi.product_id='$product_id' AND o.status IN ('paid','processed','shipped','completed')"
);
$purchaseRow = mysqli_fetch_assoc($purchaseCheck);
if (intval($purchaseRow['purchased']) === 0) {
    header('Location: ../views/public/product-detail.php?id=' . $product_id . '&review_error=1');
    exit;
}

$existingReview = mysqli_query($conn, "SELECT id FROM reviews WHERE product_id='$product_id' AND user_id='$user_id' LIMIT 1");
if (mysqli_num_rows($existingReview) > 0) {
    header('Location: ../views/public/product-detail.php?id=' . $product_id . '&review_exists=1');
    exit;
}

$imageFile = $_FILES['image'] ?? null;
$imageName = null;
if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
    if (in_array($extension, $allowed, true)) {
        $uploadDir = __DIR__ . '/../uploads/reviews';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $imageName = 'review_' . $product_id . '_' . $user_id . '_' . time() . '.' . $extension;
        move_uploaded_file($imageFile['tmp_name'], $uploadDir . '/' . $imageName);
    }
}

$commentEsc = mysqli_real_escape_string($conn, $comment);
$imageEsc = $imageName ? mysqli_real_escape_string($conn, $imageName) : 'NULL';

$insertReview = "INSERT INTO reviews (product_id, user_id, rating, comment, image) VALUES ('$product_id', '$user_id', '$rating', '$commentEsc', " . ($imageName ? "'$imageEsc'" : 'NULL') . ")";
if (!mysqli_query($conn, $insertReview)) {
    header('Location: ../views/public/product-detail.php?id=' . $product_id . '&review_error=1');
    exit;
}

header('Location: ../views/public/product-detail.php?id=' . $product_id . '&review_success=1');
exit;
