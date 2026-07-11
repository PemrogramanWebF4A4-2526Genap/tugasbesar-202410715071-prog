<?php require_once '../../middleware/buyer.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/app.php'; ?>

<?php
  $user_id = $_SESSION['user']['id'];
  $cartQ = "SELECT carts.*, products.name AS product_name, products.price AS product_price, products.image AS product_image, users.name AS seller_name FROM carts JOIN products ON carts.product_id = products.id JOIN users ON products.seller_id = users.id WHERE carts.user_id='$user_id'";
  $cartRes = mysqli_query($conn, $cartQ);
  $cartCount = mysqli_num_rows($cartRes);
  $subtotal = 0;
?>

<section class="max-w-7xl mx-auto px-4 py-10">

  <!-- Page Header -->
  <div class="mb-10">

    <h1 class="text-3xl lg:text-4xl font-bold mb-4">

      Keranjang Belanja

    </h1>

    <p class="text-gray-600">

      Kelola produk yang ingin kamu beli.

    </p>

  </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-10">

    <!-- Cart Items -->
    <div class="lg:col-span-2 space-y-6">
              <?php while($item = mysqli_fetch_assoc($cartRes)): ?>
                <?php
                  $imgPath = __DIR__ . '/../../uploads/products/' . ($item['product_image'] ?? '');
                  $imgSrc = (isset($item['product_image']) && file_exists($imgPath)) ? UPLOAD_URL . '/products/' . $item['product_image'] : 'https://placehold.co/300';
                  $lineTotal = $item['product_price'] * $item['quantity'];
                  $subtotal += $lineTotal;
                ?>

                <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6">

                  <div class="flex flex-col md:flex-row gap-6">

                    <!-- Product Image -->
                    <img src="<?= $imgSrc ?>" alt="Product" class="w-full md:w-40 h-40 object-cover rounded-2xl">

                    <!-- Product Info -->
                    <div class="flex-1">

                      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">

                        <div>

                          <h2 class="text-xl lg:text-2xl font-bold mb-2"><?= htmlspecialchars($item['product_name']); ?></h2>

                          <p class="text-gray-500 mb-4">Penjual: <?= htmlspecialchars($item['seller_name']); ?></p>

                        </div>

                        <!-- Remove -->
                        <a href="<?= BASE_URL ?>/src/buyer/remove-cart.php?id=<?= $item['id']; ?>" class="text-red-500 hover:text-red-600 transition">Hapus</a>

                      </div>

                      <!-- Price -->
                      <h3
                        class="text-xl lg:text-2xl font-bold text-emerald-500 mb-6 lineTotal"
                        data-price="<?= $item['product_price']; ?>"
                      >
                        Rp <?= number_format($lineTotal); ?>
                      </h3>

                      <!-- Quantity -->
                      <form action="<?= BASE_URL ?>/src/buyer/update-cart.php" method="POST" class="flex flex-wrap items-center gap-4">
                        <div
                          class="flex flex-wrap items-center gap-4 cart-control"
                          data-cart-id="<?= $item['id']; ?>"
                        >

                          <button
                            type="button"
                            class="minusBtn w-10 h-10 bg-gray-200 rounded-xl"
                          >
                            -
                          </button>

                          <input
                            type="number"
                            min="1"
                            value="<?= $item['quantity']; ?>"
                            class="quantityInput w-14 h-10 border rounded-xl text-center"
                          />

                          <button
                            type="button"
                            class="plusBtn w-10 h-10 bg-gray-200 rounded-xl"
                          >
                            +
                          </button>

                        </div>
                      </form>

                    </div>

                  </div>

                </div>
              <?php endwhile; ?>

    </div>

        <!-- Summary -->
    <div>

      <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8 lg:sticky lg:top-24">

        <h2 class="text-xl lg:text-2xl font-bold mb-8">Ringkasan Belanja</h2>

        <div class="space-y-4 mb-8">
          <div class="flex justify-between text-gray-600"><span>Subtotal</span><span id="subtotalText">Rp <?= number_format($subtotal); ?></span></div>
          <div class="flex justify-between text-gray-600"><span>Ongkir</span><span>Rp 0</span></div>
          <div class="border-t pt-4 flex justify-between text-xl font-bold"><span>Total</span><span id="totalText" class="text-emerald-500">Rp <?= number_format($subtotal); ?></span></div>
        </div>

        <?php if ($cartCount > 0): ?>
          <a
            href="checkout.php"
            class="block text-center w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl transition mb-4"
          >
            Checkout Sekarang
          </a>
        <?php else: ?>
          <button
            id="emptyCartButton"
            class="w-full bg-gray-300 text-gray-500 py-4 rounded-2xl cursor-not-allowed mb-4"
          >
            Checkout Sekarang
          </button>
        <?php endif; ?>

        <a href="../public/shop.php" class="w-full border border-gray-300 hover:bg-gray-100 py-4 rounded-2xl transition inline-block text-center">Lanjut Belanja</a>

      </div>

    </div>

  </div>

</section>

<script>

const subtotalText =
  document.getElementById('subtotalText');

const totalText =
  document.getElementById('totalText');

function formatRupiah(number) {

  return 'Rp ' + number.toLocaleString('id-ID');

}

function recalculateCart() {

  let subtotal = 0;

  document.querySelectorAll('.lineTotal').forEach(item => {

    subtotal += parseInt(item.dataset.total);

  });

  subtotalText.textContent =
    formatRupiah(subtotal);

  totalText.textContent =
    formatRupiah(subtotal);

}

document.querySelectorAll('.cart-control').forEach(control => {

  const minusBtn =
    control.querySelector('.minusBtn');

  const plusBtn =
    control.querySelector('.plusBtn');

  const input =
    control.querySelector('.quantityInput');

  const lineTotal =
    control.closest('.bg-white').querySelector('.lineTotal');

  const cartId =
    control.dataset.cartId;

  const price =
    parseInt(lineTotal.dataset.price);

  function updateCart(quantity) {

    fetch(
      '<?= BASE_URL ?>/src/buyer/update-cart.php',
      {
        method: 'POST',
        headers: {
          'Content-Type':
            'application/x-www-form-urlencoded'
        },
        body:
          `id=${cartId}&quantity=${quantity}`
      }
    )

    .then(() => {

      const total =
        quantity * price;

      lineTotal.dataset.total =
        total;

      lineTotal.textContent =
        formatRupiah(total);

      recalculateCart();

    });

  }

  plusBtn.addEventListener('click', () => {

    input.stepUp();

    updateCart(input.value);

  });

  minusBtn.addEventListener('click', () => {

    if (parseInt(input.value) > 1) {

      input.stepDown();

      updateCart(input.value);

    }

  });

  input.addEventListener('change', () => {

    if (parseInt(input.value) < 1) {

      input.value = 1;

    }

    updateCart(input.value);

  });

  lineTotal.dataset.total =
    parseInt(input.value) * price;

});

recalculateCart();

const emptyCartButton =
  document.getElementById('emptyCartButton');

if (emptyCartButton) {

  emptyCartButton.addEventListener('click', () => {

    alert('Keranjang masih kosong');

  });

}

</script>
<?php include '../layouts/footer.php'; ?>

</main>
</body>
</html>