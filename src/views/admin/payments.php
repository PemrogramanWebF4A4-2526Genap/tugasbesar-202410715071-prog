<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

function getPaymentStatusClasses($status) {
    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'confirmed' => ['bg-green-100', 'text-green-700'],
        'rejected' => ['bg-red-100', 'text-red-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

$q = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$where = "WHERE 1=1";

if ($q !== '') {
    $qEsc = mysqli_real_escape_string($conn, $q);

    $where .= "
        AND (
            orders.invoice_number LIKE '%$qEsc%'
            OR users.name LIKE '%$qEsc%'
            OR users.email LIKE '%$qEsc%'
        )
    ";
}

$allowedStatuses = ['pending', 'confirmed', 'rejected'];

if (in_array($statusFilter, $allowedStatuses)) {
    $where .= " AND payments.status='$statusFilter'";
}

$query = "
SELECT
    payments.*,
    orders.invoice_number,
    orders.total_amount,
    users.name AS buyer_name,
    users.email AS buyer_email

FROM payments

JOIN orders
ON payments.order_id = orders.id

JOIN users
ON orders.user_id = users.id

$where

ORDER BY payments.created_at DESC
";

$res = mysqli_query($conn, $query);

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <?php include 'sidebar.php'; ?>

  <main class="flex-1 min-w-0 p-4 lg:p-10 overflow-x-hidden">

    <!-- Header -->
    <div class="mb-10">

      <h1 class="text-3xl lg:text-4xl font-bold mb-3">
        Konfirmasi Pembayaran
      </h1>

      <p class="text-gray-600">
        Kelola dan verifikasi pembayaran customer marketplace.
      </p>

      <?php if (isset($_GET['updated'])): ?>
        <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">
          Status pembayaran berhasil diperbarui.
        </div>
      <?php elseif (isset($_GET['update_error'])): ?>
        <div class="mt-6 rounded-3xl bg-red-50 border border-red-200 p-6 text-red-700">
          Gagal memperbarui pembayaran.
        </div>
      <?php endif; ?>

    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl shadow-sm p-6 mb-10">

      <form method="GET" action="payments.php">

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($q); ?>"
            placeholder="Cari invoice atau customer..."
            class="border border-gray-300 rounded-2xl px-4 py-3"
          >

          <select
            name="status"
            class="border border-gray-300 rounded-2xl px-4 py-3"
          >

            <option value="">Semua Status</option>

            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : ''; ?>>
              Pending
            </option>

            <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : ''; ?>>
              Confirmed
            </option>

            <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : ''; ?>>
              Rejected
            </option>

          </select>

          <button
            type="submit"
            class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl px-4 py-3 transition"
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
                Customer
              </th>

              <th class="text-left px-6 py-5">
                Total
              </th>

              <th class="text-left px-6 py-5">
                Metode
              </th>

              <th class="text-left px-6 py-5">
                Bukti
              </th>

              <th class="text-left px-6 py-5">
                Status
              </th>

              <th class="text-left px-6 py-5">
                Aksi
              </th>

            </tr>

          </thead>

          <tbody>

            <?php while($payment = mysqli_fetch_assoc($res)): ?>

              <?php [$bg, $text] = getPaymentStatusClasses($payment['status']); ?>

              <tr class="border-t hover:bg-gray-50 transition">

                <!-- Invoice -->
                <td class="px-6 py-5 font-bold">
                  <?= htmlspecialchars($payment['invoice_number']); ?>
                </td>

                <!-- Customer -->
                <td class="px-6 py-5">

                  <div>

                    <h3 class="font-semibold">
                      <?= htmlspecialchars($payment['buyer_name']); ?>
                    </h3>

                    <p class="text-sm text-gray-500">
                      <?= htmlspecialchars($payment['buyer_email']); ?>
                    </p>

                  </div>

                </td>

                <!-- Total -->
                <td class="px-6 py-5 font-bold text-emerald-500">
                  Rp <?= number_format($payment['total_amount']); ?>
                </td>

                <!-- Method -->
                <td class="px-6 py-5">
                  <?= htmlspecialchars($payment['payment_method']); ?>
                </td>

                <!-- Proof -->
                <td class="px-6 py-5">

                  <?php if (!empty($payment['proof'])): ?>

                    <a
                      href="<?= BASE_URL . '/src/' . $payment['proof']; ?>"
                      target="_blank"
                      class="text-blue-500 hover:underline"
                    >
                      Lihat Bukti
                    </a>

                  <?php else: ?>

                    <span class="text-gray-400">
                      Belum Upload
                    </span>

                  <?php endif; ?>

                </td>

                <!-- Status -->
                <td class="px-6 py-5">

                  <span class="<?= $bg ?> <?= $text ?> px-4 py-2 rounded-full text-sm">

                    <?= ucfirst($payment['status']); ?>

                  </span>

                </td>

                <!-- Action -->
                <td class="px-6 py-5">

                  <?php if ($payment['status'] === 'pending'): ?>

                    <div class="flex gap-3">

                      <form
                        action="<?= BASE_URL ?>/src/admin/update-payment-status.php"
                        method="POST"
                      >

                        <input
                          type="hidden"
                          name="order_id"
                          value="<?= intval($payment['order_id']); ?>"
                        >

                        <button
                          name="action"
                          value="confirm"
                          class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl transition"
                        >
                          Confirm
                        </button>

                      </form>

                      <form
                        action="<?= BASE_URL ?>/src/admin/update-payment-status.php"
                        method="POST"
                      >

                        <input
                          type="hidden"
                          name="order_id"
                          value="<?= intval($payment['order_id']); ?>"
                        >

                        <button
                          name="action"
                          value="reject"
                          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl transition"
                        >
                          Reject
                        </button>

                      </form>

                    </div>

                  <?php else: ?>

                    <span class="text-sm text-gray-500">
                      Tidak ada aksi
                    </span>

                  <?php endif; ?>

                </td>

              </tr>

            <?php endwhile; ?>

          </tbody>

        </table>

      </div>

    </div>

  </main>

</div>

</body>
</html>