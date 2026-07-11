<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';

$where = "WHERE 1=1";

if ($q !== '') {

    $qEsc = mysqli_real_escape_string($conn, $q);

    $where .= "
        AND (
            orders.invoice_number LIKE '%$qEsc%'
            OR users.name LIKE '%$qEsc%'
        )
    ";
}

$allowedStatuses = [
    'pending',
    'paid',
    'processed',
    'shipped',
    'completed'
];

if (in_array($status, $allowedStatuses)) {

    $where .= " AND orders.status='$status'";
}
$perPage = 10;

$page = max(1, intval($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$countQuery = "
SELECT COUNT(*) AS total

FROM orders

JOIN users
ON orders.user_id = users.id

$where
";

$totalOrders = mysqli_fetch_assoc(
    mysqli_query($conn, $countQuery)
)['total'];

$totalPages = ceil($totalOrders / $perPage);

$query = "
SELECT
    orders.*,
    users.name AS buyer_name,
    payments.status AS payment_status

FROM orders

JOIN users
ON orders.user_id = users.id

LEFT JOIN payments
ON payments.order_id = orders.id

$where

ORDER BY orders.created_at DESC
LIMIT $perPage OFFSET $offset
";

$orders = mysqli_query($conn, $query);

function getOrderStatusClasses($status) {

    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'paid' => ['bg-blue-100', 'text-blue-700'],
        'processed' => ['bg-indigo-100', 'text-indigo-700'],
        'shipped' => ['bg-purple-100', 'text-purple-700'],
        'completed' => ['bg-green-100', 'text-green-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

function getPaymentStatusClasses($status) {

    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'confirmed' => ['bg-green-100', 'text-green-700'],
        'rejected' => ['bg-red-100', 'text-red-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <?php include 'sidebar.php'; ?>

  <main class="flex-1 min-w-0 p-4 lg:p-10 overflow-x-hidden">

    <!-- Header -->
    <div class="mb-10">

      <h1 class="text-3xl lg:text-4xl font-bold mb-3">
        Monitoring Order
      </h1>

      <p class="text-gray-600">
        Pantau seluruh transaksi marketplace.
      </p>

    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl shadow-sm p-6 mb-10">

      <form method="GET" action="orders.php">

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

          <!-- Search -->
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($q); ?>"
            placeholder="Cari invoice / buyer..."
            class="border border-gray-300 rounded-2xl px-4 py-3"
          >

          <!-- Status -->
          <select
            name="status"
            class="border border-gray-300 rounded-2xl px-4 py-3"
          >

            <option value="">
              Semua Status
            </option>

            <?php foreach ($allowedStatuses as $s): ?>

              <option
                value="<?= $s; ?>"
                <?= $status === $s ? 'selected' : ''; ?>
              >
                <?= ucfirst($s); ?>
              </option>

            <?php endforeach; ?>

          </select>

          <!-- Button -->
          <button
            type="submit"
            class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl transition"
          >
            Terapkan
          </button>

        </div>

      </form>

    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <div class="overflow-x-auto">

        <table class="w-full min-w-[1100px]">

          <thead class="bg-gray-50">

            <tr>

              <th class="text-left px-6 py-5">
                Invoice
              </th>

              <th class="text-left px-6 py-5">
                Buyer
              </th>

              <th class="text-left px-6 py-5">
                Total
              </th>

              <th class="text-left px-6 py-5">
                Payment
              </th>

              <th class="text-left px-6 py-5">
                Order Status
              </th>

              <th class="text-left px-6 py-5">
                Tanggal
              </th>

              <th class="text-left px-6 py-5">
                Detail
              </th>

            </tr>

          </thead>

          <tbody>
            <?php if (mysqli_num_rows($orders) > 0): ?>

            <?php while($order = mysqli_fetch_assoc($orders)): ?>

              <?php [$orderBg, $orderText] = getOrderStatusClasses($order['status']); ?>
              <?php [$payBg, $payText] = getPaymentStatusClasses($order['payment_status']); ?>

              <tr class="border-t hover:bg-gray-50 transition">

                <!-- Invoice -->
                <td class="px-6 py-5 font-bold">

                  <?= htmlspecialchars($order['invoice_number']); ?>

                </td>

                <!-- Buyer -->
                <td class="px-6 py-5">

                  <?= htmlspecialchars($order['buyer_name']); ?>

                </td>

                <!-- Total -->
                <td class="px-6 py-5 font-bold text-emerald-500">

                  Rp <?= number_format($order['total_amount']); ?>

                </td>

                <!-- Payment -->
                <td class="px-6 py-5">

                  <span class="<?= $payBg ?> <?= $payText ?> inline-flex items-center px-4 py-2 rounded-full text-sm font-medium">

                    <?= ucfirst($order['payment_status'] ?? 'pending'); ?>

                  </span>

                </td>

                <!-- Order -->
                <td class="px-6 py-5">

                  <span class="<?= $orderBg ?> <?= $orderText ?> inline-flex items-center px-4 py-2 rounded-full text-sm font-medium">

                    <?= ucfirst($order['status']); ?>

                  </span>

                </td>

                <!-- Date -->
                <td class="px-6 py-5">

                  <?= date('d M Y', strtotime($order['created_at'])); ?>

                </td>

                <!-- Detail -->
                <td class="px-6 py-5">

                  <a
                    href="<?= BASE_URL ?>/src/views/admin/order-detail.php?id=<?= $order['id']; ?>"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl transition"
                  >
                    Detail
                  </a>

                </td>

              </tr>

            <?php endwhile; ?>

            <?php else: ?>

            <tr>

              <td colspan="7" class="px-6 py-16 text-center text-gray-500">

                Order tidak ditemukan.

              </td>

            </tr>

            <?php endif; ?>

          </tbody>

        </table>

      </div>

    </div>

    <?php if ($totalPages > 1): ?>

    <div class="flex items-center justify-center gap-3 mt-10 flex-wrap">

      <!-- Prev -->
      <?php if ($page > 1): ?>

        <a
          href="?page=<?= $page - 1; ?>&q=<?= urlencode($q); ?>&status=<?= urlencode($status); ?>"
          class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
        >

          ←

        </a>

      <?php endif; ?>

      <!-- Numbers -->
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>

        <a
          href="?page=<?= $i; ?>&q=<?= urlencode($q); ?>&status=<?= urlencode($status); ?>"
          class="w-12 h-12 rounded-2xl transition flex items-center justify-center
          <?= $i == $page
              ? 'bg-emerald-500 text-white'
              : 'border hover:bg-gray-100'
          ?>"
        >

          <?= $i; ?>

        </a>

      <?php endfor; ?>

      <!-- Next -->
      <?php if ($page < $totalPages): ?>

        <a
          href="?page=<?= $page + 1; ?>&q=<?= urlencode($q); ?>&status=<?= urlencode($status); ?>"
          class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
        >

          →

        </a>

      <?php endif; ?>

    </div>

    <?php endif; ?>

  </main>

</div>

</body>
</html>