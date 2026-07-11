<?php require_once '../../middleware/buyer.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/app.php'; ?>

<?php
$user_id = $_SESSION['user']['id'];
$cartRes = mysqli_query($conn, "SELECT carts.*, products.name AS product_name, products.price AS product_price, products.image AS product_image, products.seller_id, products.stock, products.status FROM carts JOIN products ON carts.product_id = products.id WHERE carts.user_id='$user_id'");
$cartItems = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($cartRes)) {
    if ($item['status'] !== 'active') {
        continue;
    }
    $quantity = intval($item['quantity']);
    $price = floatval($item['product_price']);
    $lineTotal = $price * $quantity;
    $subtotal += $lineTotal;
    $cartItems[] = $item;
}
$shippingFee = 20000;
$totalAmount = $subtotal + $shippingFee;
?>

<section class="max-w-7xl mx-auto px-4 py-10">

  <!-- Header -->
  <div class="mb-10">

    <h1 class="text-3xl lg:text-4xl font-bold mb-4">

      Checkout

    </h1>

    <p class="text-gray-600">

      Lengkapi informasi pengiriman dan pembayaran.

    </p>

  </div>
  <form action="<?= BASE_URL ?>/src/buyer/process-checkout.php" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-10">

    <!-- Form -->
    <div class="lg:col-span-2 space-y-8">
              <!-- Shipping -->
      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8">

        <h2 class="text-xl lg:text-2xl font-bold mb-8">

          Alamat Pengiriman

        </h2>

        <!-- Nama -->
        <?php
          $label = 'Nama Penerima';
          $name = 'receiver';
          $type = 'text';
          $placeholder = 'Masukkan nama penerima';
          $required = true;

          include '../components/input.php';
        ?>

        <!-- Phone -->
        <?php
          $label = 'Nomor Telepon';
          $name = 'phone';
          $type = 'text';
          $placeholder = 'Masukkan nomor telepon';
          $required = true;

          include '../components/input.php';
        ?>

        <!-- Address -->
        <div class="mb-5">

          <label class="block mb-2 font-medium text-gray-700">

            Alamat Lengkap

          </label>

          <textarea
            name="address"
            rows="5"
            required
            class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            placeholder="Masukkan alamat lengkap"
          ></textarea>

        </div>

        <div class="mt-6">

        <label class="block text-sm font-semibold mb-2">

          Kota Pengiriman

        </label>

        <select
          name="city"
          required
          class="w-full border border-gray-300 rounded-2xl px-4 py-3 bg-white"
        >

          <option value="jakarta">

            Jakarta

          </option>

          <option value="bekasi">

            Bekasi

          </option>

          <option value="bandung">

            Bandung

          </option>

          <option value="luar_kota">

            Luar Kota

          </option>

        </select>
        <p class="text-xs text-gray-400 mt-1">
                  Ongkir menyesuaikan kota tujuan
                </p>

      </div>

      </div>
            <!-- Shipping Method -->
      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8">

        <h2 class="text-xl lg:text-2xl font-bold mb-8">

          Metode Pengiriman

        </h2>

        <div class="space-y-4">

          <!-- Shipping Item -->
          <label class="border rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:border-emerald-500 transition">

            <div class="flex flex-wrap items-center gap-4">

              <input type="radio" name="shipping_method" value="reguler" checked>

              <div>

                <h3 class="font-semibold">
                  Reguler
                </h3>

                <p class="text-sm text-gray-500">
                  Estimasi 3-5 Hari
                </p>

              </div>

            </div>

            <span class="font-bold">
              Mulai Rp 10.000
            </span>

          </label>

          <!-- Shipping Item -->
          <label class="border rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:border-emerald-500 transition">

            <div class="flex flex-wrap items-center gap-4">

              <input type="radio" name="shipping_method" value="express">

              <div>

                <h3 class="font-semibold">
                  Express
                </h3>

                <p class="text-sm text-gray-500">
                  Estimasi 1-2 Hari
                </p>

                
              </div>
              
            </div>

            <span class="font-bold">
              Mulai Rp 25.000
            </span>

          </label>

        </div>

      </div>
            <!-- Payment -->
      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8">

        <h2 class="text-xl lg:text-2xl font-bold mb-8">

          Metode Pembayaran

        </h2>

        <div class="space-y-4">

          <!-- Bank Transfer -->
          <label class="border rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:border-emerald-500 transition">

            <div class="flex flex-wrap items-center gap-4">

              <input type="radio" name="payment_method" value="bank_transfer" checked>

              <div>

                <h3 class="font-semibold">
                  Bank Transfer
                </h3>

                <p class="text-sm text-gray-500">
                  BCA, Mandiri, BNI, BRI
                </p>

              </div>

            </div>

          </label>

          <!-- E-Wallet -->
          <label class="border rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer hover:border-emerald-500 transition">

            <div class="flex flex-wrap items-center gap-4">

              <input type="radio" name="payment_method" value="ewallet">

              <div>

                <h3 class="font-semibold">
                  E-Wallet
                </h3>

                <p class="text-sm text-gray-500">
                  OVO, DANA, GoPay
                </p>

              </div>

            </div>

          </label>

        </div>

      </div>

    </div>

        <!-- Summary -->
    <div>

      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8 lg:sticky lg:top-24">

        <h2 class="text-xl lg:text-2xl font-bold mb-8">

          Ringkasan Pesanan

        </h2>

        <!-- Product -->
        <div class="space-y-6 mb-8">
          <?php if (count($cartItems) === 0): ?>
            <div class="text-gray-500">Keranjang Anda kosong. Tambahkan produk terlebih dahulu.</div>
          <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
              <?php
                $imgPath = __DIR__ . '/../../uploads/products/' . ($item['product_image'] ?? '');
                $imgSrc = (isset($item['product_image']) && file_exists($imgPath)) ? UPLOAD_URL . '/products/' . $item['product_image'] : 'https://placehold.co/300';
                $lineTotal = $item['product_price'] * $item['quantity'];
              ?>

              <div class="flex flex-col sm:flex-row gap-4">

                <img src="<?= $imgSrc ?>" alt="Product" class="w-full sm:w-20 h-48 sm:h-20 rounded-2xl object-cover">

                <div>

                  <h3 class="font-semibold"><?= htmlspecialchars($item['product_name']); ?></h3>

                  <p class="text-gray-500 text-sm">Qty: <?= intval($item['quantity']); ?></p>

                  <p class="font-bold text-emerald-500">Rp <?= number_format($lineTotal); ?></p>

                </div>

              </div>

            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Total -->
        <div class="space-y-4 border-t pt-6 mb-8">

          <div class="flex justify-between text-gray-600">

            <span>Subtotal</span>
            <span id="subtotalValue">Rp <?= number_format($subtotal); ?></span>

          </div>

          <div class="flex justify-between text-gray-600">

            <span>Ongkir</span>
            <span id="shippingValue">Rp <?= number_format($shippingFee); ?></span>

          </div>

          <div class="flex justify-between text-xl lg:text-2xl font-bold">

            <span>Total</span>

            <span id="totalValue" class="text-emerald-500">Rp <?= number_format($totalAmount); ?></span>

          </div>

        </div>

        <!-- Button -->
        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl transition" <?= count($cartItems) === 0 ? 'disabled' : ''; ?>>

          Buat Pesanan

        </button>

      </div>

    </div>

  </form>

</section>

<script>

  const shippingRadios = document.querySelectorAll(
    'input[name="shipping_method"]'
  );

  const citySelect = document.querySelector(
    'select[name="city"]'
  );

  const shippingValue = document.getElementById(
    'shippingValue'
  );

  const totalValue = document.getElementById(
    'totalValue'
  );

  const subtotal = <?= json_encode($subtotal); ?>;

  function formatRupiah(value) {

    return 'Rp ' + value.toLocaleString('id-ID');

  }

  function calculateShipping() {

    const selectedShipping =
      document.querySelector(
        'input[name="shipping_method"]:checked'
      ).value;

    const selectedCity = citySelect.value;

    let shipping = 0;

    // ONGKIR PER KOTA
    if (selectedCity === 'jakarta') {

      shipping = 10000;

    } else if (selectedCity === 'bekasi') {

      shipping = 15000;

    } else if (selectedCity === 'bandung') {

      shipping = 20000;

    } else {

      shipping = 30000;

    }

    // EXPRESS TAMBAHAN
    if (selectedShipping === 'express') {

      shipping += 15000;

    }

    shippingValue.textContent =
      formatRupiah(shipping);

    totalValue.textContent =
      formatRupiah(subtotal + shipping);

  }

  shippingRadios.forEach(radio => {

    radio.addEventListener(
      'change',
      calculateShipping
    );

  });

  citySelect.addEventListener(
    'change',
    calculateShipping
  );

  calculateShipping();

</script>

<?php include '../layouts/footer.php'; ?>

</main>
</body>
</html>