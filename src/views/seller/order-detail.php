<?php require_once '../../middleware/seller.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$orderId = intval($_GET['id'] ?? 0);
$sellerId = intval($_SESSION['user']['id']);

$query = "
SELECT
    orders.*,
    users.name AS buyer_name,
    users.email AS buyer_email,
    payments.status AS payment_status,
    payments.proof AS payment_proof

FROM orders

JOIN users
ON orders.user_id = users.id

LEFT JOIN payments
ON payments.order_id = orders.id

WHERE orders.id='$orderId'

LIMIT 1
";

$orderRes = mysqli_query($conn, $query);

$order = mysqli_fetch_assoc($orderRes);

if (!$order) {

    echo 'Pesanan tidak ditemukan.';
    exit;

}

// seller items only
$itemsQuery = "
SELECT
    order_items.*,
    products.name,
    products.image

FROM order_items

JOIN products
ON order_items.product_id = products.id

WHERE order_items.order_id='$orderId'
AND order_items.seller_id='$sellerId'
";

$itemsRes = mysqli_query($conn, $itemsQuery);

if (mysqli_num_rows($itemsRes) <= 0) {

    echo 'Pesanan tidak ditemukan.';
    exit;

}

function getStatusClasses($status) {

    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'paid' => ['bg-blue-100', 'text-blue-700'],
        'processed' => ['bg-indigo-100', 'text-indigo-700'],
        'shipped' => ['bg-orange-100', 'text-orange-700'],
        'completed' => ['bg-green-100', 'text-green-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

[$statusBg, $statusText] = getStatusClasses($order['status']);

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <?php include 'sidebar.php'; ?>

  <main class="flex-1 min-w-0 overflow-x-hidden p-4 lg:p-10">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-10">

      <div>

        <h1 class="text-3xl lg:text-4xl font-bold mb-3">

          Detail Pesanan

        </h1>

        <p class="text-gray-600">

          Invoice:
          <?= htmlspecialchars($order['invoice_number']); ?>

        </p>

      </div>

      <a
        href="orders.php"
        class="border border-gray-300 hover:bg-gray-100 px-6 py-3 rounded-2xl transition"
      >

        Kembali

      </a>

    </div>

    <!-- Top Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">

      <!-- Buyer -->
      <div class="bg-white rounded-3xl shadow-sm p-8">

        <h2 class="text-2xl font-bold mb-6">

          Customer

        </h2>

        <div class="space-y-3">

          <p class="font-semibold">
            <?= htmlspecialchars($order['buyer_name']); ?>
          </p>

          <p class="text-gray-500">
            <?= htmlspecialchars($order['buyer_email']); ?>
          </p>

        </div>

      </div>

      <!-- Shipping -->
      <div class="bg-white rounded-3xl shadow-sm p-8">

        <h2 class="text-2xl font-bold mb-6">

          Pengiriman

        </h2>

        <div class="space-y-3">

          <p>
            <?= nl2br(htmlspecialchars($order['shipping_address'])); ?>
          </p>

          <?php if (!empty($order['tracking_number'])): ?>

            <div class="pt-4">

              <span class="font-semibold">
                No. Resi:
              </span>

              <?= htmlspecialchars($order['tracking_number']); ?>

            </div>

          <?php endif; ?>

        </div>

      </div>

      <!-- Status -->
      <div class="bg-white rounded-3xl shadow-sm p-8">

        <h2 class="text-2xl font-bold mb-6">

          Status

        </h2>

        <div class="space-y-4">

          <span class="<?= $statusBg ?> <?= $statusText ?> px-4 py-2 rounded-full text-sm">

            <?= ucfirst($order['status']); ?>

          </span>

          <div>

            <p class="text-gray-500 text-sm mb-2">

              Pembayaran

            </p>

            <p class="font-semibold">

              <?= ucfirst($order['payment_status'] ?? 'pending'); ?>

            </p>

          </div>

        </div>

      </div>

    </div>

    <!-- Products -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden mb-10">

      <div class="p-8 border-b">

        <h2 class="text-2xl font-bold">

          Produk

        </h2>

      </div>

      <div class="divide-y">

        <?php
        $sellerTotal = 0;
        while($item = mysqli_fetch_assoc($itemsRes)):
            $sellerTotal += $item['subtotal'];

            $img = (!empty($item['image']) &&
                file_exists(__DIR__ . '/../../uploads/products/' . $item['image']))
                ? UPLOAD_URL . '/products/' . $item['image']
                : 'https://placehold.co/300';
        ?>

        <div class="p-8 flex flex-col lg:flex-row gap-6">

          <img
            src="<?= $img; ?>"
            class="w-32 h-32 object-cover rounded-3xl border"
          >

          <div class="flex-1">

            <h3 class="text-2xl font-bold mb-3">

              <?= htmlspecialchars($item['name']); ?>

            </h3>

            <p class="text-gray-500 mb-3">

              Quantity:
              <?= number_format($item['quantity']); ?>

            </p>

            <p class="text-gray-500 mb-3">

              Harga:
              Rp <?= number_format($item['price']); ?>

            </p>

          </div>

          <div class="lg:text-right">

            <p class="text-2xl font-bold text-emerald-500">

              Rp <?= number_format($item['subtotal']); ?>

            </p>

          </div>

        </div>

        <?php endwhile; ?>

      </div>

    </div>

    <!-- Summary -->
    <div class="bg-white rounded-3xl shadow-sm p-8">

      <div class="flex items-center justify-between text-2xl font-bold">

        <span>Total Seller</span>

        <span class="text-emerald-500">

          Rp <?= number_format($sellerTotal); ?>

        </span>

      </div>

    </div>

  </main>

</div>

</body>
</html>