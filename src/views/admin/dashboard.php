<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php
  $totalUsers = mysqli_fetch_assoc(
      mysqli_query($conn, "SELECT COUNT(*) AS total FROM users")
  )['total'];

  $totalSellers = mysqli_fetch_assoc(
      mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='seller'")
  )['total'];

  $totalProducts = mysqli_fetch_assoc(
      mysqli_query($conn, "SELECT COUNT(*) AS total FROM products")
  )['total'];

  $totalOrders = mysqli_fetch_assoc(
      mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders")
  )['total'];

  $totalRevenue = mysqli_fetch_assoc(
      mysqli_query($conn, "
          SELECT COALESCE(SUM(total_amount), 0) AS total
          FROM orders
          WHERE status IN ('paid', 'processed', 'shipped', 'completed')
      ")
  )['total'];

  $recentOrders = mysqli_query($conn, "
      SELECT
          orders.invoice_number,
          orders.total_amount,
          orders.status,
          users.name AS buyer_name
      FROM orders
      JOIN users ON orders.user_id = users.id
      ORDER BY orders.created_at DESC
      LIMIT 5
  ");
  $topCategories = mysqli_query($conn, "
      SELECT
          categories.name,
          COUNT(products.id) AS total_products
      FROM categories
      LEFT JOIN products
      ON products.category_id = categories.id
      GROUP BY categories.id
      ORDER BY total_products DESC
      LIMIT 5
  ");
?>

<div class="flex bg-gray-100 min-h-screen">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main -->
  <main class="flex-1 p-6 lg:p-10">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

      <div>

        <h1 class="text-3xl lg:text-4xl font-bold mb-3">

          Dashboard Admin

        </h1>

        <p class="text-gray-600">

          Kelola marketplace dan pantau seluruh aktivitas platform.

        </p>

      </div>

    </div>
        <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">

      <!-- Users -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Users

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-blue-500">

          <?= number_format($totalUsers); ?>

        </h2>

      </div>

      <!-- Sellers -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Seller

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-emerald-500">

          <?= number_format($totalSellers); ?>

        </h2>

      </div>

      <!-- Products -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Produk

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-yellow-500">

          <?= number_format($totalProducts); ?>

        </h2>

      </div>

      <!-- Transactions -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Transaksi

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-purple-500">

          Rp <?= number_format($totalRevenue); ?>

        </h2>

      </div>

    </div>
        <!-- Recent Activity -->
    <div class="bg-white rounded-3xl shadow-sm p-8 mb-10">

      <div class="flex items-center justify-between mb-8">

        <h2 class="text-2xl font-bold">

          Aktivitas Terbaru

        </h2>

      </div>

      <div class="space-y-6">

        <?php while($order = mysqli_fetch_assoc($recentOrders)): ?>

          <div class="flex items-start gap-4">

            <div class="w-4 h-4 bg-emerald-500 rounded-full mt-2"></div>

            <div>

              <h3 class="font-semibold">

                Pesanan baru masuk

              </h3>

              <p class="text-gray-500 text-sm">

                <?= htmlspecialchars($order['buyer_name']); ?>
                membuat invoice
                <?= htmlspecialchars($order['invoice_number']); ?>

              </p>

            </div>

          </div>

        <?php endwhile; ?>

      </div>

    </div>
        <!-- Marketplace Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Top Category -->
      <div class="bg-white rounded-3xl shadow-sm p-8">

        <h2 class="text-2xl font-bold mb-8">

          Kategori Terpopuler

        </h2>

        <div class="space-y-5">

          <?php while($category = mysqli_fetch_assoc($topCategories)): ?>

            <div class="flex items-center justify-between">

              <span>

                <?= htmlspecialchars($category['name']); ?>

              </span>

              <span class="font-bold text-emerald-500">

                <?= number_format($category['total_products']); ?> Produk

              </span>

            </div>

          <?php endwhile; ?>

        </div>

      </div>

      <!-- Platform Status -->
      <div class="bg-white rounded-3xl shadow-sm p-8">

        <h2 class="text-2xl font-bold mb-8">

          Status Platform

        </h2>

        <div class="space-y-5">

          <div class="flex items-center justify-between">

            <span>Server Status</span>

            <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm">

              Online

            </span>

          </div>

          <div class="flex items-center justify-between">

            <span>Pembayaran</span>

            <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm">

              Aktif

            </span>

          </div>

          <div class="flex items-center justify-between">

            <span>Marketplace</span>

            <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm">

              Stabil

            </span>

          </div>

        </div>

      </div>

    </div>
      </main>

</div>

</body>
</html>