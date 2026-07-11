<?php require_once '../../middleware/buyer.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/app.php'; ?>

<?php
$orderId = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user']['id'];

$orderRes = mysqli_query($conn, "SELECT o.*, (SELECT p.status FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS payment_status, (SELECT p.proof FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS proof_path FROM orders o WHERE o.id='$orderId' AND o.user_id='$user_id' LIMIT 1");
$order = mysqli_fetch_assoc($orderRes);

if (!$order) {
    header('Location: orders.php');
    exit;
}

$itemsRes = mysqli_query($conn, "SELECT oi.*, products.name AS product_name, products.image AS product_image FROM order_items oi JOIN products ON oi.product_id = products.id WHERE oi.order_id='$orderId'");
$items = [];
while ($item = mysqli_fetch_assoc($itemsRes)) {
    $items[] = $item;
}

function getStepClass($status, $step) {
    $orderSteps = ['pending', 'paid', 'processed', 'shipped', 'completed'];
    $currentIndex = array_search($status, $orderSteps, true);
    $stepIndex = array_search($step, $orderSteps, true);

    if ($stepIndex === false || $currentIndex === false) {
        return 'bg-gray-100 text-gray-700';
    }

    return $stepIndex <= $currentIndex ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500';
}

function formatPaymentStatusLabel($status, $proofPath) {
    if (!$status) {
        return 'Belum Bayar';
    }
    if ($status === 'pending' && $proofPath) {
        return 'Menunggu Konfirmasi Pembayaran';
    }
    if ($status === 'rejected') {
        return 'Pembayaran Ditolak';
    }
    return $status === 'pending' ? 'Menunggu Pembayaran' : ($status === 'confirmed' ? 'Pembayaran Dikonfirmasi' : 'Pembayaran Ditolak');
}
?>

<section class="max-w-7xl mx-auto px-4 py-10">
  <div class="mb-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-3xl lg:text-4xl font-bold mb-2">Detail Pesanan</h1>
        <p class="text-gray-600">Lihat status, produk, dan informasi pembayaran pesanan Anda.</p>
      </div>
      <a href="orders.php" class="text-emerald-500 hover:text-emerald-700 font-semibold">Kembali ke Daftar Pesanan</a>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm p-5 lg:p-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
          <p class="text-sm text-gray-500">Invoice</p>
          <h2 class="text-xl lg:text-2xl font-bold"><?= htmlspecialchars($order['invoice_number']); ?></h2>
        </div>
        <div class="flex flex-wrap gap-3 items-center">
          <span class="bg-emerald-100 text-emerald-700 px-5 py-2 rounded-full text-sm font-medium">Status: <?= ucfirst($order['status']); ?></span>
          <a href="invoice.php?id=<?= intval($order['id']); ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2 rounded-2xl transition text-sm">Download Invoice</a>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-2 mb-8">
        <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
          <p class="text-sm text-gray-500 mb-2">Tanggal Pesanan</p>
          <p><?= date('d M Y H:i', strtotime($order['created_at'])); ?></p>
        </div>
        <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
          <p class="text-sm text-gray-500 mb-2">Status Pembayaran</p>
          <p><?= htmlspecialchars(formatPaymentStatusLabel($order['payment_status'], $order['proof_path'])); ?></p>
        </div>
      </div>
      <div class="grid gap-6 md:grid-cols-2 mb-8">
        <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
          <p class="text-sm text-gray-500 mb-2">Metode Pengiriman</p>
          <p><?= htmlspecialchars($order['shipping_method'] ?? 'Reguler'); ?></p>
        </div>
        <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
          <p class="text-sm text-gray-500 mb-2">Ongkir</p>
          <p>Rp <?= number_format($order['shipping_fee'] ?? 20000); ?></p>
        </div>
      </div>
      <?php if (!empty($order['tracking_number'])): ?>
        <div class="rounded-3xl bg-gray-50 p-5 lg:p-6 mb-8">
          <p class="text-sm text-gray-500 mb-2">Nomor Resi</p>
          <p class="font-semibold"><?= htmlspecialchars($order['tracking_number']); ?></p>
        </div>
      <?php endif; ?>
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-10">
        <?php foreach (['pending','paid','processed','shipped','completed'] as $step): ?>
          <div class="rounded-3xl p-5 text-center <?= getStepClass($order['status'], $step); ?>">
            <p class="font-semibold text-sm capitalize"><?= $step === 'paid' ? 'dibayar' : $step; ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="space-y-6">
        <?php foreach ($items as $item): ?>
          <?php
            $imgPath = __DIR__ . '/../../uploads/products/' . ($item['product_image'] ?? '');
            $imgSrc = (isset($item['product_image']) && file_exists($imgPath)) ? UPLOAD_URL . '/products/' . $item['product_image'] : 'https://placehold.co/300';
          ?>
          <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6 flex flex-col md:flex-row gap-6">
            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['product_name']); ?>" class="w-full md:w-40 h-40 object-cover rounded-2xl">
            <div class="flex-1">
              <h3 class="text-xl lg:text-2xl font-bold mb-2"><?= htmlspecialchars($item['product_name']); ?></h3>
              <p class="text-gray-500 mb-4">Qty: <?= intval($item['quantity']); ?></p>
              <p class="text-emerald-500 font-bold text-xl">Rp <?= number_format($item['subtotal']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <aside class="bg-white rounded-3xl shadow-sm p-5 lg:p-8">
      <div class="space-y-6">
        <div>
          <h3 class="text-xl font-bold mb-4">Ringkasan</h3>
          <div class="flex justify-between text-gray-600 mb-3"><span>Subtotal</span><span>Rp <?= number_format(array_sum(array_column($items, 'subtotal'))); ?></span></div>
          <div class="flex justify-between text-gray-600 mb-3"><span>Ongkir</span><span>Rp <?= number_format($order['shipping_fee'] ?? 20000); ?></span></div>
          <div class="border-t pt-4 flex justify-between text-xl font-bold"><span>Total</span><span class="text-emerald-500">Rp <?= number_format($order['total_amount']); ?></span></div>
        </div>

        <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
          <p class="text-sm text-gray-500 mb-2">Alamat Pengiriman</p>
          <p class="whitespace-pre-line"><?= htmlspecialchars($order['shipping_address']); ?></p>
        </div>

        <?php if (!empty($order['proof_path'])): ?>

    <?php
      $proofFile = basename($order['proof_path']);
      $proofUrl = BASE_URL . '/src/uploads/payments/' . $proofFile;
    ?>

    <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">

      <p class="text-sm text-gray-500 mb-2">

        Bukti Pembayaran

      </p>

      <a
        href="<?= $proofUrl; ?>"
        target="_blank"
        class="text-emerald-600 hover:underline"
      >

        Lihat Bukti Pembayaran
      </a>

    </div>

<?php endif; ?>

        <?php if ($order['payment_status'] === 'pending' && empty($order['proof_path'])): ?>
          <div class="rounded-3xl bg-yellow-50 p-5 lg:p-6">
            <p class="text-sm font-semibold text-yellow-700 mb-4">Upload Bukti Pembayaran</p>
            <form action="<?= BASE_URL ?>/src/buyer/upload-payment-proof.php" method="POST" enctype="multipart/form-data" class="space-y-4">
              <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
              <input type="file" name="payment_proof" accept="image/*,application/pdf" required class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white" />
              <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition">Upload Bukti Pembayaran</button>
            </form>
          </div>
        <?php elseif ($order['payment_status'] === 'pending' && !empty($order['proof_path'])): ?>
          <div class="rounded-3xl bg-yellow-50 p-5 lg:p-6">
            <p class="text-sm text-yellow-700">Bukti pembayaran sudah diunggah dan menunggu konfirmasi admin.</p>
          </div>
        <?php elseif ($order['payment_status'] === 'rejected' && !empty($order['proof_path'])): ?>
          <div class="rounded-3xl bg-red-50 border border-red-200 p-5 lg:p-6 text-red-700">
            <p class="text-sm font-semibold mb-3">Pembayaran ditolak oleh admin.</p>
            <p class="text-sm mb-4">Silakan unggah kembali bukti pembayaran yang benar.</p>
            <form action="<?= BASE_URL ?>/src/buyer/upload-payment-proof.php" method="POST" enctype="multipart/form-data" class="space-y-4">
              <input type="hidden" name="order_id" value="<?= intval($order['id']); ?>">
              <input type="file" name="payment_proof" accept="image/*,application/pdf" required class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white" />
              <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition">Unggah Ulang Bukti Pembayaran</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</section>

<?php include '../layouts/footer.php'; ?>

</main>
</body>
</html>