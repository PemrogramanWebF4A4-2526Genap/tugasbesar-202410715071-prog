<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: orders.php');
    exit;
}

$query = "
SELECT
    orders.*,
    users.name AS buyer_name,
    users.email AS buyer_email,
    payments.payment_method,
    payments.status AS payment_status,
    payments.proof

FROM orders

JOIN users
ON orders.user_id = users.id

LEFT JOIN payments
ON payments.order_id = orders.id

WHERE orders.id='$id'

LIMIT 1
";

$res = mysqli_query($conn, $query);

$order = mysqli_fetch_assoc($res);

if (!$order) {
    header('Location: orders.php');
    exit;
}

$items = mysqli_query(
    $conn,
    "
    SELECT
        order_items.*,
        products.name AS product_name,
        products.image AS product_image,
        users.name AS seller_name

    FROM order_items

    JOIN products
    ON order_items.product_id = products.id

    JOIN users
    ON order_items.seller_id = users.id

    WHERE order_items.order_id='$id'
    "
);

function getStatusClasses($status) {

    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'paid' => ['bg-blue-100', 'text-blue-700'],
        'processed' => ['bg-indigo-100', 'text-indigo-700'],
        'shipped' => ['bg-purple-100', 'text-purple-700'],
        'completed' => ['bg-green-100', 'text-green-700'],
        'confirmed' => ['bg-green-100', 'text-green-700'],
        'rejected' => ['bg-red-100', 'text-red-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

[$orderBg, $orderText] = getStatusClasses($order['status']);
[$payBg, $payText] = getStatusClasses($order['payment_status']);

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <?php include 'sidebar.php'; ?>

  <main class="flex-1 min-w-0 p-4 lg:p-10 overflow-x-hidden">

    <!-- Header -->
    <div class="mb-10">

      <h1 class="text-3xl lg:text-4xl font-bold mb-3">
        Detail Order
      </h1>

      <p class="text-gray-600">
        Monitoring detail transaksi marketplace.
      </p>

    </div>

    <!-- Order Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

      <!-- Left -->
      <div class="bg-white rounded-3xl shadow-sm p-8 space-y-5">

        <div>

          <p class="text-gray-500 mb-2">
            Invoice
          </p>

          <h2 class="text-2xl font-bold">
            <?= htmlspecialchars($order['invoice_number']); ?>
          </h2>

        </div>

        <div>

          <p class="text-gray-500 mb-2">
            Buyer
          </p>

          <h3 class="font-bold">
            <?= htmlspecialchars($order['buyer_name']); ?>
          </h3>

          <p class="text-sm text-gray-500">
            <?= htmlspecialchars($order['buyer_email']); ?>
          </p>

        </div>

        <div>

          <p class="text-gray-500 mb-2">
            Shipping Address
          </p>

          <p class="leading-relaxed">
            <?= nl2br(htmlspecialchars($order['shipping_address'])); ?>
          </p>

        </div>

      </div>

      <!-- Right -->
      <div class="bg-white rounded-3xl shadow-sm p-8 space-y-5">

        <div>

          <p class="text-gray-500 mb-2">
            Total
          </p>

          <h2 class="text-3xl font-bold text-emerald-500">
            Rp <?= number_format($order['total_amount']); ?>
          </h2>

        </div>

        <div>

          <p class="text-gray-500 mb-2">
            Payment Method
          </p>

          <h3 class="font-bold">
            <?= htmlspecialchars($order['payment_method'] ?? '-'); ?>
          </h3>

        </div>

        <div class="flex flex-wrap gap-3">

          <span class="<?= $orderBg ?> <?= $orderText ?> px-4 py-2 rounded-full text-sm">

            <?= ucfirst($order['status']); ?>

          </span>

          <span class="<?= $payBg ?> <?= $payText ?> px-4 py-2 rounded-full text-sm">

            <?= ucfirst($order['payment_status']); ?>

          </span>

        </div>

        <?php if (!empty($order['proof'])): ?>

          <div>

            <p class="text-gray-500 mb-3">
              Bukti Pembayaran
            </p>

            <a
              href="<?= BASE_URL . '/src/' . $order['proof']; ?>"
              target="_blank"
            >

              <img
                src="<?= BASE_URL . '/src/' . $order['proof']; ?>"
                class="w-40 rounded-2xl border shadow-sm hover:opacity-80 transition"
              >

            </a>

          </div>

        <?php endif; ?>

      </div>

    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <div class="p-8 border-b">

        <h2 class="text-2xl font-bold">
          Produk Pesanan
        </h2>

      </div>

      <div class="divide-y">

        <?php while($item = mysqli_fetch_assoc($items)): ?>

          <?php
            $imgPath = __DIR__ . '/../../uploads/products/' . ($item['product_image'] ?? '');

            $imgSrc = (!empty($item['product_image']) && file_exists($imgPath))
              ? UPLOAD_URL . '/products/' . $item['product_image']
              : 'https://placehold.co/300';
          ?>

          <div class="p-8 flex flex-col lg:flex-row gap-6">

            <!-- Image -->
            <img
              src="<?= $imgSrc; ?>"
              class="w-28 h-28 object-cover rounded-2xl border"
            >

            <!-- Info -->
            <div class="flex-1">

              <h3 class="text-xl font-bold mb-2">

                <?= htmlspecialchars($item['product_name']); ?>

              </h3>

              <p class="text-gray-500 mb-3">

                Seller:
                <?= htmlspecialchars($item['seller_name']); ?>

              </p>

              <div class="flex flex-wrap gap-6 text-sm">

                <p>
                  Qty:
                  <span class="font-bold">
                    <?= $item['quantity']; ?>
                  </span>
                </p>

                <p>
                  Harga:
                  <span class="font-bold">
                    Rp <?= number_format($item['price']); ?>
                  </span>
                </p>

                <p>
                  Subtotal:
                  <span class="font-bold text-emerald-500">
                    Rp <?= number_format($item['subtotal']); ?>
                  </span>
                </p>

              </div>

            </div>

          </div>

        <?php endwhile; ?>

      </div>

    </div>

  </main>

</div>

</body>
</html>