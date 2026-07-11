<?php
session_start();
require_once '../../config/database.php';

$id = intval($_GET['id'] ?? 0);

$query = "
SELECT
    products.*,
    categories.name AS category_name,
    users.name AS seller_name,
    users.profile_image

FROM products

JOIN categories
ON products.category_id = categories.id

JOIN users
ON products.seller_id = users.id

WHERE products.id='$id'
";

$result = mysqli_query($conn, $query);

$product = mysqli_fetch_assoc($result);

$sellerId = intval($product['seller_id']);

$sellerStats = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT
            COUNT(DISTINCT products.id) AS total_products,
            COALESCE(SUM(order_items.quantity),0) AS total_sold

        FROM products

        LEFT JOIN order_items
        ON products.id = order_items.product_id

        WHERE products.seller_id='$sellerId'
        "
    )
);

if (!$product) {
    echo "Produk tidak ditemukan";
    exit;
}

$reviewQuery = "SELECT r.*, u.name AS reviewer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id='$id' ORDER BY r.created_at DESC";
$reviewResult = mysqli_query($conn, $reviewQuery);
$reviews = [];
while ($review = mysqli_fetch_assoc($reviewResult)) {
    $reviews[] = $review;
}

$reviewStatsRes = mysqli_query($conn, "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE product_id='$id'");
$reviewStats = mysqli_fetch_assoc($reviewStatsRes);
$avgRating = round($reviewStats['avg_rating'] ?? 0, 1);
$totalReviews = intval($reviewStats['total_reviews'] ?? 0);

$canReview = false;
$alreadyReviewed = false;
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'buyer') {
    $userId = intval($_SESSION['user']['id']);
    $purchaseRes = mysqli_query($conn, "SELECT COUNT(*) AS purchased FROM orders o JOIN order_items oi ON oi.order_id=o.id WHERE o.user_id='$userId' AND oi.product_id='$id' AND o.status IN ('paid','processed','shipped','completed')");
    $purchaseRow = mysqli_fetch_assoc($purchaseRes);
    if (intval($purchaseRow['purchased']) > 0) {
        $canReview = true;
    }
    $alreadyRes = mysqli_query($conn, "SELECT id FROM reviews WHERE product_id='$id' AND user_id='$userId' LIMIT 1");
    if (mysqli_num_rows($alreadyRes)) {
        $alreadyReviewed = true;
    }
}

?>
<?php include '../layouts/app.php'; ?>

<section class="max-w-7xl mx-auto px-4 py-10">

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

    <!-- Product Image -->
    <div>

        <?php
          $imagePath = UPLOAD_URL . '/products/' . ($product['image'] ?: '');
          $imageSrc = (isset($product['image']) && file_exists(__DIR__ . '/../../uploads/products/' . $product['image'])) ? $imagePath : 'https://placehold.co/800x600';
        ?>

        <img
          src="<?= $imageSrc ?>"
          alt="Product"
          class="w-full rounded-3xl shadow-md"
        >

    </div>

    <!-- Product Info -->
    <div>

      <!-- Category -->
      <span class="bg-emerald-100 text-emerald-600 px-4 py-2 rounded-full text-sm font-medium">

        <?= $product['category_name']; ?>

      </span>

      <!-- Product Name -->
      <h1 class="text-3xl lg:text-4xl font-bold mt-6 mb-4">

        <?= $product['name']; ?>

      </h1>

      <!-- Price -->
      <h2 class="text-3xl lg:text-4xl font-bold text-emerald-500 mb-6">

        Rp <?= number_format($product['price']); ?>

      </h2>

      <!-- Description -->
      <p class="text-gray-600 leading-relaxed mb-8">

        <?= $product['description']; ?>

      </p>

      <!-- Stock -->
      <div class="mb-6">

        <span class="font-semibold">Stok:</span>

        <span class="text-gray-600">
          <?= intval($product['stock']) > 0 ? intval($product['stock']) . ' tersedia' : 'Habis'; ?>
        </span>

      </div>

      <!-- Availability -->
      <div class="mb-6">

        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium <?= intval($product['stock']) > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
          <?= intval($product['stock']) > 0 ? 'Tersedia' : 'Stok kosong'; ?>
        </span>

      </div>

      <!-- Quantity & Action -->
      <div class="mb-8">

        <form action="<?= BASE_URL ?>/src/buyer/add-to-cart.php" method="POST" class="flex flex-col sm:flex-row sm:items-end gap-4">
          <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

          <div>
            <label class="block mb-2 font-semibold">Quantity</label>
            <input name="quantity" type="number" value="1" min="1" class="w-24 border rounded-xl px-3 py-2">
          </div>

          <div class="flex flex-wrap gap-4 mt-6">
            <button type="submit" class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white w-full sm:w-auto px-10 py-4 rounded-2xl transition">Tambah ke Keranjang</button>
          </div>

        </form>

      </div>

      <!-- Seller -->
      <div class="mt-10 p-5 lg:p-6 bg-white rounded-2xl shadow-sm">

        <h3 class="font-bold text-lg mb-2">

          Informasi Seller

        </h3>

        <div class="flex flex-col sm:flex-row sm:items-center gap-4">

          <?php
            $sellerImage = (!empty($product['profile_image']) &&
              file_exists(__DIR__ . '/../../uploads/sellers/' . $product['profile_image']))
                ? UPLOAD_URL . '/sellers/' . $product['profile_image']
                : 'https://placehold.co/100';
          ?>

          <img
            src="<?= $sellerImage; ?>"
            class="w-14 h-14 object-cover rounded-2xl border"
          >

          <div>

            <h4 class="font-semibold">
              <?= htmlspecialchars($product['seller_name']); ?>
            </h4>

            <p class="text-sm text-gray-400">

              Bergabung sejak
              <?= date('Y', strtotime($product['created_at'])); ?>

            </p>

              <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                <span>
                  <?= number_format($sellerStats['total_products']); ?>
                  Produk
                </span>

                <span>
                  <?= number_format($sellerStats['total_sold']); ?>
                  Terjual
                </span>

              </div>

          </div>

        </div>

      </div>

    </div>

  </div>

</section>

<section class="max-w-7xl mx-auto px-4 pb-20">

  <div class="bg-white rounded-3xl shadow-sm p-8 mb-10">

    <?php if (isset($_GET['review_success'])): ?>
      <div class="mb-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-5 lg:p-6 text-emerald-700">
        Review berhasil dikirim. Terima kasih atas ulasan Anda.
      </div>
    <?php elseif (isset($_GET['review_error'])): ?>
      <div class="mb-6 rounded-3xl bg-red-50 border border-red-200 p-5 lg:p-6 text-red-700">
        Gagal mengirim review. Pastikan Anda sudah membeli produk ini dan data diisi dengan benar.
      </div>
    <?php elseif (isset($_GET['review_exists'])): ?>
      <div class="mb-6 rounded-3xl bg-yellow-50 border border-yellow-200 p-5 lg:p-6 text-yellow-700">
        Anda sudah menulis review untuk produk ini.
      </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-2xl lg:text-3xl font-bold mb-3">Review Produk</h2>
        <?php if ($totalReviews > 0): ?>
          <div class="flex items-center gap-4 flex-wrap">
            <div class="text-4xl lg:text-5xl font-bold text-emerald-500"><?= number_format($avgRating, 1); ?></div>
            <div>
              <div class="flex items-center gap-1 text-amber-400 text-lg">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="<?= $i <= round($avgRating) ? 'text-amber-400' : 'text-gray-300'; ?>">★</span>
                <?php endfor; ?>
              </div>
              <p class="text-gray-500 mt-2"><?= $totalReviews; ?> review</p>
            </div>
          </div>
        <?php else: ?>
          <p class="text-gray-500">Belum ada review untuk produk ini.</p>
        <?php endif; ?>
      </div>
    </div>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'buyer'): ?>
      <?php if ($alreadyReviewed): ?>
        <div class="rounded-3xl bg-emerald-50 border border-emerald-200 p-5 lg:p-6 text-emerald-700">
          Anda sudah menulis review untuk produk ini.
        </div>
      <?php elseif ($canReview): ?>
        <form action="<?= BASE_URL ?>/src/buyer/submit-review.php" method="POST" enctype="multipart/form-data" class="space-y-6">
          <input type="hidden" name="product_id" value="<?= intval($product['id']); ?>">
          <div>
            <label class="font-semibold mb-2 block">Rating</label>
            <select name="rating" required class="w-full border border-gray-300 rounded-2xl px-4 py-3">
              <option value="5">5 – Sangat Baik</option>
              <option value="4">4 – Baik</option>
              <option value="3">3 – Cukup</option>
              <option value="2">2 – Kurang</option>
              <option value="1">1 – Buruk</option>
            </select>
          </div>
          <div>
            <label class="font-semibold mb-2 block">Komentar</label>
            <textarea name="comment" rows="4" class="w-full border border-gray-300 rounded-2xl px-4 py-3" placeholder="Tulis ulasan Anda..."></textarea>
          </div>
          <div>

    <label class="font-semibold mb-3 block">

        Foto (opsional)

    </label>

    <label
        for="reviewImage"
        class="
            border-2
            border-dashed
            border-gray-300
            hover:border-emerald-400
            rounded-3xl
            p-8
            bg-gray-50
            transition
            cursor-pointer
            block
        "
    >

        <input
            id="reviewImage"
            type="file"
            name="image"
            accept="image/*"
            class="hidden"
        />

        <div class="flex flex-col items-center justify-center text-center">

            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mb-4">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-8 h-8 text-emerald-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                    />

                </svg>

            </div>

            <h3 class="font-semibold text-lg mb-2">

                Upload Foto Review

            </h3>

            <p class="text-gray-500 text-sm mb-4">

                PNG, JPG atau JPEG

            </p>

            <span
                class="
                    inline-flex
                    items-center
                    px-5
                    py-2
                    rounded-2xl
                    bg-emerald-500
                    text-white
                    text-sm
                    font-medium
                "
            >

                Pilih Gambar

            </span>

        </div>

        <!-- Preview -->
        <div
            id="previewContainer"
            class="hidden mt-6"
        >

            <img
                id="previewImage"
                class="
                    w-full
                    max-h-80
                    object-cover
                    rounded-3xl
                    border
                "
            >

            <p
                id="fileName"
                class="mt-3 text-sm text-gray-500"
            ></p>

        </div>

    </label>

</div>
          <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition">Kirim Review</button>
        </form>
      <?php else: ?>
        <div class="rounded-3xl bg-yellow-50 border border-yellow-200 p-5 lg:p-6 text-yellow-700">
          Untuk mengirim review, Anda harus membeli produk ini terlebih dahulu.
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="rounded-3xl bg-blue-50 border border-blue-200 p-5 lg:p-6 text-blue-700">
        <a href="<?= BASE_URL ?>/src/views/public/login.php" class="font-semibold underline">Login</a> untuk menulis review dan rating.
      </div>
    <?php endif; ?>

  </div>

  <?php if ($totalReviews > 0): ?>
    <div class="space-y-6">
      <?php foreach ($reviews as $review): ?>
        <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6">
          <div class="flex items-center justify-between gap-4 mb-4">
            <div>
              <h3 class="font-semibold"><?= htmlspecialchars($review['reviewer_name']); ?></h3>
              <p class="text-sm text-gray-500"><?= date('d M Y', strtotime($review['created_at'])); ?></p>
            </div>
            <div class="flex items-center gap-1 text-amber-400 text-lg">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="<?= $i <= intval($review['rating']) ? 'text-amber-400' : 'text-gray-300'; ?>">★</span>
              <?php endfor; ?>
            </div>
          </div>
          <?php if (!empty($review['comment'])): ?>
            <p class="text-gray-700 mb-4"><?= nl2br(htmlspecialchars($review['comment'])); ?></p>
          <?php endif; ?>
          <?php if (!empty($review['image']) && file_exists(__DIR__ . '/../../uploads/reviews/' . $review['image'])): ?>
            <img src="<?= UPLOAD_URL . '/reviews/' . htmlspecialchars($review['image']); ?>" alt="Review image" class="w-full rounded-3xl object-cover max-h-72 lg:max-h-96">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<section class="max-w-7xl mx-auto px-4 pb-20">

  <div class="flex items-center justify-between mb-10">

    <h2 class="text-2xl lg:text-3xl font-bold">

      Produk Terkait

    </h2>

  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <?php
      $catId = $product['category_id'];
      $related = mysqli_query($conn, "SELECT products.*, users.name AS seller_name FROM products JOIN users ON products.seller_id = users.id WHERE products.category_id='$catId' AND products.id!='{$product['id']}' AND products.status='active' LIMIT 4");
      if (mysqli_num_rows($related) == 0) {
        echo '<div class="text-gray-500">Tidak ada produk terkait.</div>';
      } else {
        while($p = mysqli_fetch_assoc($related)) {
          $productName = $p['name'];
          $price = 'Rp ' . number_format($p['price']);
          $seller = htmlspecialchars($p['seller_name'] ?? 'UMKM Indonesia');
          $image = (isset($p['image']) && file_exists(__DIR__ . '/../../uploads/products/' . $p['image'])) ? UPLOAD_URL . '/products/' . $p['image'] : 'https://placehold.co/400';
          $id = $p['id'];
          include '../components/product-card.php';
        }
      }
    ?>

  </div>

</section>

<?php include '../layouts/footer.php'; ?>

</main>
<script>

const reviewImage =
    document.getElementById('reviewImage');

const previewContainer =
    document.getElementById('previewContainer');

const previewImage =
    document.getElementById('previewImage');

const fileName =
    document.getElementById('fileName');

if (reviewImage) {

    reviewImage.addEventListener('change', function(e) {

        const file = e.target.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function(event) {

            previewImage.src = event.target.result;

            previewContainer.classList.remove('hidden');

            fileName.textContent = file.name;

        };

        reader.readAsDataURL(file);

    });

}

</script>
</body>
</html>