<?php require_once '../../middleware/seller.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$sellerId = intval($_SESSION['user']['id']);

// total products
$totalProducts = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT COUNT(*) AS total
        FROM products
        WHERE seller_id='$sellerId'
        "
    )
)['total'];

// total orders
$totalOrders = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT COUNT(*) AS total

        FROM order_items

        WHERE seller_id='$sellerId'
        "
    )
)['total'];

// revenue
$totalRevenue = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT COALESCE(SUM(subtotal),0) AS total

        FROM order_items

        WHERE seller_id='$sellerId'
        "
    )
)['total'];

// average review
$reviewStats = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT AVG(reviews.rating) AS avg_rating

        FROM reviews

        JOIN products
        ON reviews.product_id = products.id

        WHERE products.seller_id='$sellerId'
        "
    )
);

$avgRating = round($reviewStats['avg_rating'] ?? 0, 1);

// latest orders
$latestOrders = mysqli_query(
    $conn,
    "
    SELECT
        orders.invoice_number,
        orders.status,
        users.name AS buyer_name,
        products.name AS product_name,
        order_items.subtotal

    FROM order_items

    JOIN orders
    ON order_items.order_id = orders.id

    JOIN users
    ON orders.user_id = users.id

    JOIN products
    ON order_items.product_id = products.id

    WHERE order_items.seller_id='$sellerId'

    ORDER BY orders.created_at DESC

    LIMIT 5
    "
);

// top products
$topProducts = mysqli_query(
    $conn,
    "
    SELECT
        products.*,
        COALESCE(SUM(order_items.quantity),0) AS total_sold,
        COALESCE(SUM(order_items.subtotal),0) AS revenue

    FROM products

    LEFT JOIN order_items
    ON products.id = order_items.product_id

    WHERE products.seller_id='$sellerId'

    GROUP BY products.id

    ORDER BY total_sold DESC

    LIMIT 5
    "
);

function getStatusClasses($status) {

    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'paid' => ['bg-blue-100', 'text-blue-700'],
        'processed' => ['bg-indigo-100', 'text-indigo-700'],
        'shipped' => ['bg-purple-100', 'text-purple-700'],
        'completed' => ['bg-green-100', 'text-green-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main -->
  <main class="flex-1 min-w-0 overflow-x-hidden p-4 lg:p-10">
        <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

      <div>

        <h1 class="text-3xl lg:text-4xl font-bold mb-3">

          Dashboard Seller

        </h1>

        <p class="text-gray-600">

          Kelola produk dan pantau penjualan toko Anda.

        </p>

      </div>

      <a
        href="add-product.php"
        class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-4 rounded-2xl transition"
      >
        Tambah Produk
      </a>

    </div>
        <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">

      <!-- Card -->
      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6">

        <p class="text-gray-500 mb-3">

          Total Produk

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-emerald-500">

          <?= number_format($totalProducts); ?>

        </h2>

      </div>

      <!-- Card -->
      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6">

        <p class="text-gray-500 mb-3">

          Total Pesanan

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-blue-500">

          <?= number_format($totalOrders); ?>

        </h2>

      </div>

      <!-- Card -->
      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6">

        <p class="text-gray-500 mb-3">

          Pendapatan

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-yellow-500">

          Rp <?= number_format($totalRevenue); ?>

        </h2>

      </div>

      <!-- Card -->
      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6">

        <p class="text-gray-500 mb-3">

          Review Produk

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-purple-500">

          <?= number_format($avgRating, 1); ?>

        </h2>

      </div>

    </div>
        <!-- Recent Orders -->
    <div class="bg-white rounded-3xl shadow-sm p-8 mb-10">

      <div class="flex items-center justify-between mb-8">

        <h2 class="text-2xl font-bold">

          Pesanan Terbaru

        </h2>

        <a href="orders.php" class="text-emerald-500 font-medium">

          Lihat Semua

        </a>

      </div>

      <!-- Table -->
      <div class="overflow-x-auto">

        <table class="w-full min-w-[1100px]">

          <thead>

            <tr class="border-b">

              <th class="text-left py-4">Invoice</th>
              <th class="text-left py-4">Pembeli</th>
              <th class="text-left py-4">Produk</th>
              <th class="text-left py-4">Status</th>
              <th class="text-left py-4">Total</th>

            </tr>

          </thead>

          <tbody>

            <?php if (mysqli_num_rows($latestOrders) > 0): ?>

              <?php while($order = mysqli_fetch_assoc($latestOrders)): ?>

                <?php [$bg, $text] = getStatusClasses($order['status']); ?>

                <tr class="border-b hover:bg-gray-50 transition">

                  <td class="py-5 font-medium">

                    <?= htmlspecialchars($order['invoice_number']); ?>

                  </td>

                  <td class="py-5">

                    <?= htmlspecialchars($order['buyer_name']); ?>

                  </td>

                  <td class="py-5">

                    <?= htmlspecialchars($order['product_name']); ?>

                  </td>

                  <td class="py-5">

                    <span class="<?= $bg ?> <?= $text ?> px-4 py-2 rounded-full text-sm">

                      <?= ucfirst($order['status']); ?>

                    </span>

                  </td>

                  <td class="py-5 font-bold text-emerald-500">

                    Rp <?= number_format($order['subtotal']); ?>

                  </td>

                </tr>

              <?php endwhile; ?>

            <?php else: ?>

            <tr>

              <td colspan="5" class="py-10 text-center text-gray-500">

                Belum ada pesanan.

              </td>

            </tr>

            <?php endif; ?>

            </tbody>

        </table>

      </div>

    </div>
        <!-- Top Products -->
    <div class="bg-white rounded-3xl shadow-sm p-8">

      <div class="flex items-center justify-between mb-8">

        <h2 class="text-2xl font-bold">

          Produk Terlaris

        </h2>

      </div>

      <div class="space-y-6">

        <?php if (mysqli_num_rows($topProducts) > 0): ?>

          <?php while($product = mysqli_fetch_assoc($topProducts)): ?>

            <?php
              $imgPath = __DIR__ . '/../../uploads/products/' . ($product['image'] ?? '');

              $imgSrc = (!empty($product['image']) && file_exists($imgPath))
                ? UPLOAD_URL . '/products/' . $product['image']
                : 'https://placehold.co/300';
            ?>

            <div class="flex items-center gap-5">

              <img
                src="<?= $imgSrc; ?>"
                class="w-20 h-20 rounded-2xl object-cover border"
              >

              <div class="flex-1">

                <h3 class="font-bold text-lg">

                  <?= htmlspecialchars($product['name']); ?>

                </h3>

                <p class="text-gray-500">

                  <?= number_format($product['total_sold']); ?> Terjual

                </p>

              </div>

              <p class="font-bold text-emerald-500">

                Rp <?= number_format($product['revenue']); ?>

              </p>

            </div>

          <?php endwhile; ?>

        <?php else: ?>

          <div class="text-center py-10 text-gray-500">

            Belum ada produk terjual.

          </div>

        <?php endif; ?>

        </div>

    </div>
  </main>

</div>

</body>
</html>