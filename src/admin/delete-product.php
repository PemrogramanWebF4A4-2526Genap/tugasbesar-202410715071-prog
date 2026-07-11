<?php

session_start();

require_once '../middleware/admin.php';
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {

    header('Location: ../views/admin/products.php');
    exit;

}

$productRes = mysqli_query(
    $conn,
    "SELECT image FROM products WHERE id='$id' LIMIT 1"
);

$product = mysqli_fetch_assoc($productRes);

if (!$product) {

    header('Location: ../views/admin/products.php');
    exit;

}

mysqli_begin_transaction($conn);

try {

    // delete review images
    $reviewRes = mysqli_query(
        $conn,
        "SELECT image FROM reviews WHERE product_id='$id'"
    );

    while ($review = mysqli_fetch_assoc($reviewRes)) {

        if (!empty($review['image'])) {

            $reviewImage = __DIR__ . '/../uploads/reviews/' . $review['image'];

            if (file_exists($reviewImage)) {
                @unlink($reviewImage);
            }

        }

    }

    // delete product image
    if (!empty($product['image'])) {

        $imagePath = __DIR__ . '/../uploads/products/' . $product['image'];

        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }

    }

    // delete related reviews
    mysqli_query(
        $conn,
        "DELETE FROM reviews WHERE product_id='$id'"
    );

    // delete cart items
    mysqli_query(
        $conn,
        "DELETE FROM carts WHERE product_id='$id'"
    );

    // delete order items
    mysqli_query(
        $conn,
        "DELETE FROM order_items WHERE product_id='$id'"
    );

    // finally delete product
    mysqli_query(
        $conn,
        "DELETE FROM products WHERE id='$id'"
    );

    mysqli_commit($conn);

    header('Location: ../views/admin/products.php?deleted=1');
    exit;

} catch (Exception $e) {

    mysqli_rollback($conn);

    header('Location: ../views/admin/products.php?delete_error=1');
    exit;

}

?>