<?php require_once '../../middleware/buyer.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/app.php'; ?>

<?php
$user_id = $_SESSION['user']['id'];
$q = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$where = "WHERE o.user_id='$user_id'";
if ($q !== '') {
    $qEsc = mysqli_real_escape_string($conn, $q);
    $where .= " AND (o.invoice_number LIKE '%$qEsc%' OR o.shipping_address LIKE '%$qEsc%')";
}

$allowedStatuses = ['pending', 'paid', 'processed', 'shipped', 'completed'];
if (in_array($statusFilter, $allowedStatuses, true)) {
    $where .= " AND o.status='$statusFilter'";
}

$orderRes = mysqli_query($conn, "SELECT o.*, (SELECT p.status FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS payment_status, (SELECT p.proof FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS proof_path FROM orders o $where ORDER BY o.created_at DESC");
$orders = [];
while ($order = mysqli_fetch_assoc($orderRes)) {
    $itemsRes = mysqli_query($conn, "SELECT oi.*, products.name AS product_name, products.image AS product_image FROM order_items oi JOIN products ON oi.product_id = products.id WHERE oi.order_id='{$order['id']}'");
    $order['items'] = [];
    while ($item = mysqli_fetch_assoc($itemsRes)) {
        $order['items'][] = $item;
    }
    $orders[] = $order;
}

function getOrderStatusStyles($status) {
    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'paid' => ['bg-blue-100', 'text-blue-700'],
        'processed' => ['bg-indigo-100', 'text-indigo-700'],
        'shipped' => ['bg-orange-100', 'text-orange-700'],
        'completed' => ['bg-green-100', 'text-green-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

function formatPaymentStatus($status, $proofPath = null) {
    if (!$status) {
        return 'Belum ada pembayaran';
    }
    if ($status === 'pending' && $proofPath) {
        return 'Bukti pembayaran telah dikirim, menunggu konfirmasi';
    }
    if ($status === 'rejected') {
        return 'Pembayaran ditolak, silakan unggah ulang bukti';
    }
    return $status === 'pending' ? 'Menunggu Pembayaran' : ($status === 'confirmed' ? 'Pembayaran Dikonfirmasi' : 'Pembayaran Ditolak');
}
?>

<section class="max-w-7xl mx-auto px-4 py-10">

  <!-- Header -->
  <div class="mb-10">

    <h1 class="text-3xl lg:text-4xl font-bold mb-4">

      Pesanan Saya

    </h1>

    <p class="text-gray-600">

      Pantau status pesanan dan pembayaran Anda.

    </p>

    <?php if (isset($_GET['created'])): ?>
      <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-5 lg:p-6 text-emerald-700">
        Pesanan berhasil dibuat. Silakan upload bukti pembayaran untuk memproses pesanan.
      </div>
    <?php elseif (isset($_GET['uploaded'])): ?>
      <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-5 lg:p-6 text-emerald-700">
        Bukti pembayaran berhasil diunggah. Silakan tunggu konfirmasi admin.
      </div>
    <?php elseif (isset($_GET['upload_error'])): ?>
      <div class="mt-6 rounded-3xl bg-red-50 border border-red-200 p-5 lg:p-6 text-red-700">
        Gagal mengunggah bukti pembayaran. Pastikan file valid dan coba lagi.
      </div>
    <?php endif; ?>

  </div>

  <form method="GET" action="orders.php" class="flex flex-col md:flex-row gap-4 mb-10">

    <div class="flex-1 min-w-[210px]">
      <input
        name="q"
        value="<?= htmlspecialchars($q); ?>"
        type="text"
        placeholder="Cari invoice atau alamat..."
        class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
      >
    </div>

    <div>
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
    </div>

    <button type="submit" class="w-full md:w-auto bg-emerald-500 text-white px-6 py-3 rounded-2xl">Terapkan</button>

  </form>

  <?php if (count($orders) === 0): ?>
    <div class="bg-white rounded-3xl shadow-sm p-10 text-center text-gray-600">
      <h2 class="text-xl lg:text-2xl font-bold mb-4">Belum ada pesanan</h2>
      <p>Silakan tambahkan produk ke keranjang dan lakukan checkout untuk membuat pesanan.</p>
    </div>
  <?php else: ?>
    <div class="space-y-8">
      <?php foreach ($orders as $order): ?>
        <?php [$bg, $text] = getOrderStatusStyles($order['status']); ?>

        <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

          <div class="border-b p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
              <div>
                <p class="text-sm text-gray-500 mb-2">Invoice</p>
                <h2 class="font-bold text-xl"><?= htmlspecialchars($order['invoice_number']); ?></h2>
              </div>

              <div class="flex flex-wrap gap-3 items-center">
                <span class="<?= $bg ?> <?= $text ?> px-5 py-2 rounded-full text-sm font-medium w-fit">
                  <?= ucfirst($order['status']); ?>
                </span>
                <span class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($order['created_at'])); ?></span>
              </div>
            </div>
          </div>

          <div class="p-5 lg:p-6 space-y-6">
            <?php foreach ($order['items'] as $item): ?>
              <?php
                $imgPath = __DIR__ . '/../../uploads/products/' . ($item['product_image'] ?? '');
                $imgSrc = (isset($item['product_image']) && file_exists($imgPath)) ? UPLOAD_URL . '/products/' . $item['product_image'] : 'https://placehold.co/300';
              ?>

              <div class="flex flex-col md:flex-row gap-6">
                <img src="<?= $imgSrc ?>" alt="Product" class="w-full md:w-32 h-32 object-cover rounded-2xl">
                <div class="flex-1">
                  <h3 class="text-xl lg:text-2xl font-bold mb-2"><?= htmlspecialchars($item['product_name']); ?></h3>
                  <p class="text-gray-500 mb-4">Qty: <?= intval($item['quantity']); ?></p>
                  <p class="text-emerald-500 font-bold text-xl">Rp <?= number_format($item['subtotal']); ?></p>
                </div>
              </div>
            <?php endforeach; ?>

            <div class="grid gap-4 lg:grid-cols-2">
              <div class="rounded-2xl bg-gray-50 p-5 lg:p-6">
                <p class="text-sm text-gray-500 mb-2">Alamat Pengiriman</p>
                <p class="text-gray-700"><?= nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
              </div>

              <div class="rounded-2xl bg-gray-50 p-5 lg:p-6">
                <p class="text-sm text-gray-500 mb-2">Status Pembayaran</p>
                <p class="text-gray-700"><?= htmlspecialchars(formatPaymentStatus($order['payment_status'], $order['proof_path'])); ?></p>
              </div>
            </div>

            <?php if ($order['payment_status'] === 'pending' && empty($order['proof_path'])): ?>
              <div class="bg-gray-50 rounded-2xl p-5 lg:p-6">
                <h3 class="text-xl font-bold mb-4">Upload Bukti Pembayaran</h3>
                <form action="<?= BASE_URL ?>/src/buyer/upload-payment-proof.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                  <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
                  <div class="space-y-4">
                    <label
                      for="payment_proof_<?= $order['id']; ?>"
                      class="flex flex-col items-center justify-center border-2 border-dashed border-emerald-200 hover:border-emerald-400 bg-emerald-50 hover:bg-emerald-100 transition rounded-3xl p-10 cursor-pointer text-center"
                    >
                      <div class="text-5xl mb-4">
                        📤
                      </div>
                      <h3 class="text-xl font-bold mb-2 text-gray-800">
                        Upload Bukti Pembayaran
                      </h3>
                      <p class="text-gray-500 text-sm">
                        PNG, JPG, PDF • Maksimal 5MB
                      </p>
                      <span
                        id="file-name-<?= $order['id']; ?>"
                        class="mt-4 text-sm text-emerald-600 font-medium"
                      >
                        Belum ada file dipilih

                      </span>

                    </label>

                    <input
                      id="payment_proof_<?= $order['id']; ?>"
                      type="file"
                      name="payment_proof"
                      accept=".jpg,.jpeg,.png,.gif,.pdf"
                      required
                      class="hidden"
                      onchange="updateFileName(this, <?= $order['id']; ?>)"
                    >

                  </div>
                  <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition">Upload Bukti</button>
                </form>
              </div>
            <?php elseif (!empty($order['proof_path'])): ?>
              <div class="bg-gray-50 rounded-2xl p-5 lg:p-6">
                <p class="text-sm text-gray-500 mb-2">Bukti Pembayaran</p>
                <a href="<?= BASE_URL . '/' . htmlspecialchars($order['proof_path']); ?>" target="_blank" class="text-emerald-600 hover:underline">Lihat Bukti Pembayaran</a>
              </div>
              <?php if ($order['payment_status'] === 'rejected'): ?>
                <div class="mt-4 rounded-2xl bg-red-50 border border-red-200 p-5 lg:p-6 text-red-700">
                  <p class="font-semibold mb-3">Pembayaran ditolak oleh admin.</p>
                  <p class="mb-4">Silakan unggah ulang bukti pembayaran yang benar.</p>
                  <form action="<?= BASE_URL ?>/src/buyer/upload-payment-proof.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
                    <div class="space-y-4">
                      <label
                        for="payment_reupload_<?= $order['id']; ?>"
                        class="flex flex-col items-center justify-center border-2 border-dashed border-red-200 hover:border-red-400 bg-red-50 hover:bg-red-100 transition rounded-3xl p-10 cursor-pointer text-center"
                      >
                        <div class="text-5xl mb-4">
                          📤
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-gray-800">
                          Upload Ulang Bukti
                        </h3>
                        <p class="text-gray-500 text-sm">
                          PNG, JPG, PDF • Maksimal 5MB
                        </p>
                        <span
                          id="reupload-file-name-<?= $order['id']; ?>"
                          class="mt-4 text-sm text-red-600 font-medium"
                        >
                          Belum ada file dipilih
                        </span>

                      </label>

                      <input
                        id="payment_reupload_<?= $order['id']; ?>"
                        type="file"
                        name="payment_proof"
                        accept=".jpg,.jpeg,.png,.gif,.pdf"
                        required
                        class="hidden"
                        onchange="updateReuploadFileName(this, <?= $order['id']; ?>)"
                      >

                    </div>
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition">Unggah Ulang Bukti</button>
                  </form>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <div class="border-t pt-6 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
              <div>
                <p class="text-gray-500 mb-2">Total Pembayaran</p>
                <h2 class="text-3xl font-bold text-emerald-500">Rp <?= number_format($order['total_amount']); ?></h2>
              </div>
              <div class="flex flex-col sm:flex-row gap-4">
                <a href="order-detail.php?id=<?= intval($order['id']); ?>" class="border border-gray-300 hover:bg-gray-100 px-6 py-3 rounded-2xl transition">Detail Pesanan</a>
                <a href="order-detail.php?id=<?= intval($order['id']); ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition">Lacak Pesanan</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<?php include '../layouts/footer.php'; ?>

</main>
<script>

function updateFileName(input, orderId) {

    const fileName = input.files[0]
        ? input.files[0].name
        : 'Belum ada file dipilih';

    document.getElementById(
        'file-name-' + orderId
    ).innerText = fileName;

}

function updateReuploadFileName(input, orderId) {

    const fileName = input.files[0]
        ? input.files[0].name
        : 'Belum ada file dipilih';

    document.getElementById(
        'reupload-file-name-' + orderId
    ).innerText = fileName;

}

</script>
</body>
</html>