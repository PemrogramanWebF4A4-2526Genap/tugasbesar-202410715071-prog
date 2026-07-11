<?php require_once '../../middleware/buyer.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/app.php'; ?>

<?php
$orderId = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user']['id'];

$orderRes = mysqli_query($conn, "SELECT o.*, u.name AS buyer_name, u.email AS buyer_email, (SELECT p.status FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) AS payment_status FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id='$orderId' AND o.user_id='$user_id' LIMIT 1");
$order = mysqli_fetch_assoc($orderRes);

if (!$order) {
    header('Location: orders.php');
    exit;
}

$itemsRes = mysqli_query($conn, "SELECT oi.*, products.name AS product_name FROM order_items oi JOIN products ON oi.product_id = products.id WHERE oi.order_id='$orderId'");
$items = [];
while ($item = mysqli_fetch_assoc($itemsRes)) {
    $items[] = $item;
}

$shippingFee = $order['shipping_fee'] ?? 20000;
?>

<section class="max-w-5xl mx-auto px-4 py-10">
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-10">
    <div>
      <h1 class="text-3xl lg:text-4xl font-bold">Invoice</h1>
      <p class="text-gray-600">Invoice pesanan Anda untuk referensi pembayaran dan bukti transaksi.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-4">
      <a href="orders.php" class="px-6 py-3 rounded-2xl border border-gray-300 hover:bg-gray-100 transition">Kembali</a>
      <button onclick="window.print()" class="px-6 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white transition">Cetak Invoice</button>
    </div>
  </div>

  <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-10">
      <div>
        <p class="text-sm text-gray-500">Invoice</p>
        <h2 class="text-3xl font-bold"><?= htmlspecialchars($order['invoice_number']); ?></h2>
      </div>
      <div class="text-right">
        <p class="text-sm text-gray-500">Tanggal</p>
        <p><?= date('d M Y H:i', strtotime($order['created_at'])); ?></p>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3 mb-10">
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
        <p class="text-sm text-gray-500 mb-2">Pembeli</p>
        <p class="font-semibold"><?= htmlspecialchars($order['buyer_name']); ?></p>
        <p class="text-gray-500"><?= htmlspecialchars($order['buyer_email']); ?></p>
      </div>
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
        <p class="text-sm text-gray-500 mb-2">Metode Pembayaran</p>
        <p class="font-semibold"><?= htmlspecialchars($order['payment_status'] ?? 'Belum Dibayar'); ?></p>
      </div>
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
        <p class="text-sm text-gray-500 mb-2">Alamat Pengiriman</p>
        <p class="whitespace-pre-line"><?= htmlspecialchars($order['shipping_address']); ?></p>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2 mb-10">
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
        <p class="text-sm text-gray-500 mb-2">Metode Pengiriman</p>
        <p class="font-semibold"><?= htmlspecialchars($order['shipping_method'] ?? 'Reguler'); ?></p>
      </div>
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
        <p class="text-sm text-gray-500 mb-2">Ongkir</p>
        <p class="font-semibold">Rp <?= number_format($shippingFee); ?></p>
      </div>
    </div>
    <?php if (!empty($order['tracking_number'])): ?>
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6 mb-10">
        <p class="text-sm text-gray-500 mb-2">Nomor Resi</p>
        <p class="font-semibold"><?= htmlspecialchars($order['tracking_number']); ?></p>
      </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
      <table class="w-full min-w-[700px] text-left border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-6 py-4">Produk</th>
            <th class="px-6 py-4">Qty</th>
            <th class="px-6 py-4">Harga</th>
            <th class="px-6 py-4">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr class="border-t">
              <td class="px-6 py-4"><?= htmlspecialchars($item['product_name']); ?></td>
              <td class="px-6 py-4"><?= intval($item['quantity']); ?></td>
              <td class="px-6 py-4">Rp <?= number_format($item['price']); ?></td>
              <td class="px-6 py-4">Rp <?= number_format($item['subtotal']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-10 grid gap-4 md:grid-cols-2">
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
        <p class="text-sm text-gray-500 mb-2">Catatan</p>
        <p>Terima kasih telah berbelanja di UMKM Marketplace. Simpan invoice ini sebagai bukti transaksi.</p>
      </div>
      <div class="rounded-3xl bg-gray-50 p-5 lg:p-6">
        <div class="flex justify-between text-gray-600 mb-3"><span>Subtotal</span><span>Rp <?= number_format(array_sum(array_column($items, 'subtotal'))); ?></span></div>
        <div class="flex justify-between text-gray-600 mb-3"><span>Ongkir</span><span>Rp <?= number_format($shippingFee); ?></span></div>
        <div class="border-t pt-4 flex justify-between text-xl font-bold"><span>Total</span><span class="text-emerald-500">Rp <?= number_format($order['total_amount']); ?></span></div>
      </div>
    </div>
  </div>
</section>

<?php include '../layouts/footer.php'; ?>

</main>
</body>
</html>
