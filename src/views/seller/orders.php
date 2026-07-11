<?php require_once '../../middleware/seller.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php
function getOrderStatusClasses($status) {
    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'paid' => ['bg-blue-100', 'text-blue-700'],
        'processed' => ['bg-indigo-100', 'text-indigo-700'],
        'shipped' => ['bg-orange-100', 'text-orange-700'],
        'completed' => ['bg-green-100', 'text-green-700'],
    ];
    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}


function getPaymentStatusClasses($status) {
    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700', 'Menunggu Bukti'],
        'confirmed' => ['bg-green-100', 'text-green-700', 'Dikonfirmasi'],
        'rejected' => ['bg-red-100', 'text-red-700', 'Ditolak'],
    ];
    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700', 'Belum Bayar'];
}

function formatOrderStatus($status) {

    $labels = [
        'pending' => 'Pending',
        'paid' => 'Dibayar',
        'processed' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
    ];

    return $labels[$status] ?? ucfirst($status);
}

$seller_id = intval($_SESSION['user']['id']);
$q = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$shippingFilter = $_GET['shipping'] ?? '';

$where = "WHERE oi.seller_id='$seller_id'";
if ($q !== '') {
    $qEsc = mysqli_real_escape_string($conn, $q);
    $where .= " AND (o.invoice_number LIKE '%$qEsc%' OR u.name LIKE '%$qEsc%' OR u.email LIKE '%$qEsc%' OR products.name LIKE '%$qEsc%')";
}

$allowedStatuses = ['pending', 'paid', 'processed', 'shipped', 'completed'];
if (in_array($statusFilter, $allowedStatuses, true)) {
    $where .= " AND o.status='$statusFilter'";
}

$allowedShipping = ['reguler', 'express'];
if (in_array($shippingFilter, $allowedShipping, true)) {
    $where .= " AND o.shipping_method='$shippingFilter'";
}

$orderQuery = "SELECT o.*, u.name AS buyer_name, u.email AS buyer_email, (SELECT p.status FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS payment_status, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', products.name) SEPARATOR ', ') AS product_list, SUM(oi.subtotal) AS seller_total FROM orders o JOIN users u ON o.user_id = u.id JOIN order_items oi ON oi.order_id = o.id JOIN products ON oi.product_id = products.id $where GROUP BY o.id ORDER BY o.created_at DESC";
$orderRes = mysqli_query($conn, $orderQuery);
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

          Kelola Pesanan

        </h1>

        <p class="text-gray-600">

          Pantau dan proses pesanan customer.

        </p>

        <?php if (isset($_GET['updated'])): ?>
          <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">
            Status pesanan berhasil diperbarui.
          </div>
        <?php elseif (isset($_GET['update_error'])): ?>
          <div class="mt-6 rounded-3xl bg-red-50 border border-red-200 p-5 lg:p-6 text-red-700">
            Gagal memperbarui status pesanan. Pastikan status saat ini valid.
          </div>
        <?php endif; ?>

      </div>

    </div>
        <!-- Filter -->
    <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6 mb-10">

      <form method="GET" action="orders.php">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

          <!-- Search -->
          <input
            name="q"
            value="<?= htmlspecialchars($q); ?>"
            type="text"
            placeholder="Cari invoice, customer, atau produk..."
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

          <!-- Status -->
          <select
            name="status"
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

            <option value="">Semua Status</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : ''; ?>>Dibayar</option>
            <option value="processed" <?= $statusFilter === 'processed' ? 'selected' : ''; ?>>Diproses</option>
            <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
            <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : ''; ?>>Selesai</option>

          </select>

          <!-- Shipping -->
          <select
            name="shipping"
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

            <option value="">Semua Pengiriman</option>
            <option value="reguler" <?= $shippingFilter === 'reguler' ? 'selected' : ''; ?>>Reguler</option>
            <option value="express" <?= $shippingFilter === 'express' ? 'selected' : ''; ?>>Express</option>

          </select>

          <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-3 rounded-2xl transition">Terapkan</button>

        </div>
      </form>

    </div>
        <!-- Orders Table -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <div class="overflow-x-auto">

        <table class="w-full min-w-[1200px]">

          <!-- Head -->
          <thead class="bg-gray-50">

            <tr>

              <th class="text-left px-6 py-5">
                Invoice
              </th>

              <th class="text-left px-6 py-5">
                Customer
              </th>

              <th class="text-left px-6 py-5">
                Produk
              </th>

              <th class="text-left px-6 py-5">
                Total
              </th>

              <th class="text-left px-6 py-5">
                Status
              </th>

              <th class="text-left px-6 py-5">
                Pembayaran
              </th>

              <th class="text-left px-6 py-5">
                Aksi
              </th>

            </tr>

          </thead>

          <tbody>
            <?php if (mysqli_num_rows($orderRes) > 0): ?>
            <?php while ($order = mysqli_fetch_assoc($orderRes)): ?>
              <?php [$statusBg, $statusText] = getOrderStatusClasses($order['status']); ?>
              <tr class="border-t hover:bg-gray-50 transition">

                <!-- Invoice -->
                <td class="px-6 py-5 font-bold">
                  <?= htmlspecialchars($order['invoice_number']); ?>
                </td>

                <!-- Customer -->
                <td class="px-6 py-5">
                  <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($order['buyer_name']); ?></h3>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($order['buyer_email']); ?></p>
                  </div>
                </td>

                <!-- Product -->
                <td class="px-6 py-5">
                  <?= htmlspecialchars(mb_strimwidth($order['product_list'] ?: '-', 0, 80, '...')); ?>
                </td>

                <!-- Total -->
                <td class="px-6 py-5 font-bold text-emerald-500">
                  Rp <?= number_format($order['seller_total']); ?>
                </td>

                <!-- Status -->
                <td class="px-6 py-5">
                  <span class="<?= $statusBg ?> <?= $statusText ?> px-4 py-2 rounded-full text-sm">
                    <?= formatOrderStatus($order['status']); ?>
                  </span>
                </td>

                <td class="px-6 py-5">
                  <?php [$payBg, $payText, $payLabel] = getPaymentStatusClasses($order['payment_status']); ?>
                  <span
                    title="Status Pembayaran"
                    class="<?= $payBg ?> <?= $payText ?> inline-flex items-center px-4 py-2 rounded-full text-sm font-medium"
                  >
                <?= $payLabel ?></span>
                </td>

                <!-- Action -->
                <td class="px-6 py-5">
                  <div class="flex flex-col gap-3 min-w-[220px]">
                    <a
                      href="<?= BASE_URL ?>/src/views/seller/order-detail.php?id=<?= intval($order['id']); ?>"
                      class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition text-center"
                    >
                      Detail
                    </a>
                    <?php if ($order['status'] === 'paid'): ?>
                      <form action="<?= BASE_URL ?>/src/seller/update-order-status.php" method="POST">
                        <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
                        <button name="action" value="process" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl transition">Proses</button>
                      </form>
                    <?php elseif ($order['status'] === 'processed'): ?>
                      <form action="<?= BASE_URL ?>/src/seller/update-order-status.php" method="POST" class="space-y-3">
                        <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
                        <input type="text" name="tracking_number" placeholder="Masukkan no. resi" class="w-full border border-gray-300 rounded-2xl px-4 py-3" required>
                        <button name="action" value="ship" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl transition">Kirim</button>
                      </form>
                    <?php elseif ($order['status'] === 'shipped'): ?>
                      <form action="<?= BASE_URL ?>/src/seller/update-order-status.php" method="POST">
                        <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
                        <button name="action" value="complete" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-xl transition">Selesai</button>
                      </form>
                    <?php elseif ($order['status'] === 'completed'): ?>
                      <span class="text-sm text-emerald-600 font-medium">
                        Pesanan selesai
                      </span>
                    <?php elseif ($order['status'] === 'pending'): ?>
                      <span class="text-sm text-yellow-600">
                        Menunggu pembayaran
                      </span>
                    <?php else: ?>
                      <span class="text-sm text-gray-500">
                        Tidak bisa diproses
                      </span>
                    <?php endif; ?>
                  </div>
                </td>

              </tr>
            <?php endwhile; ?>
            <?php else: ?>

            <tr>

              <td colspan="7" class="px-6 py-16 text-center text-gray-500">

                Belum ada pesanan masuk.

              </td>

            </tr>

            <?php endif; ?>
          </tbody>

        </table>

      </div>

    </div>
      </main>

</div>

</body>
</html>
