<?php


require_once '../../config/database.php';

// get categories for filter
$categoriesRes = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// search & category filter
$q = trim($_GET['q'] ?? '');
$cat = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? '';

$where = "WHERE products.status='active'";
if ($q !== '') {
  $qEsc = mysqli_real_escape_string($conn, $q);
  $where .= " AND (products.name LIKE '%$qEsc%' OR products.description LIKE '%$qEsc%')";
}

if ($cat !== '' && is_numeric($cat)) {
  $catEsc = (int)$cat;
  $where .= " AND products.category_id='$catEsc'";
}

$order = 'products.id DESC';
if ($sort === 'price_asc') $order = 'products.price ASC';
if ($sort === 'price_desc') $order = 'products.price DESC';
if ($sort === 'popular') $order = 'products.id DESC';

$perPage = 8;

$page = max(1, intval($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;
$countQuery = "
SELECT COUNT(*) AS total

FROM products

JOIN categories
ON products.category_id = categories.id

JOIN users
ON products.seller_id = users.id

$where
";

$totalProducts = mysqli_fetch_assoc(
    mysqli_query($conn, $countQuery)
)['total'];

$totalPages = ceil($totalProducts / $perPage);
$query = "SELECT products.*, categories.name AS category_name, users.name AS seller_name FROM products JOIN categories ON products.category_id = categories.id JOIN users ON products.seller_id = users.id $where ORDER BY $order LIMIT $perPage OFFSET $offset";
$products = mysqli_query($conn, $query);

?>
<?php include '../layouts/app.php'; ?>

<section class="max-w-7xl mx-auto px-4 py-10">

  <!-- Header -->
  <div class="mb-10">

    <h1 class="text-3xl lg:text-4xl font-bold mb-4">

      Semua Produk

    </h1>

    <p class="text-gray-600">

      Temukan berbagai produk UMKM terbaik Indonesia.

    </p>

  </div>

    <!-- Search & Filter -->
  <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-6 mb-10">

    <form method="GET" action="shop.php">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

      <!-- Search -->
      <div class="lg:col-span-2">

          <input
            name="q"
            value="<?= htmlspecialchars($q); ?>"
            type="text"
            placeholder="Cari produk..."
            class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

      </div>

      <!-- Category -->
      <div>

        <select
          name="category"
          onchange="this.form.submit()"
          class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >

          <option value="">Semua Kategori</option>
          <?php while($c = mysqli_fetch_assoc($categoriesRes)): ?>
            <option value="<?= $c['id']; ?>" <?= $c['id']==$cat? 'selected':''; ?>><?= htmlspecialchars($c['name']); ?></option>
          <?php endwhile; ?>

        </select>

      </div>

      <!-- Sort -->
      <div>

        <select name="sort" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500">

          <option value="" <?= $sort === '' ? 'selected' : ''; ?>>Terbaru</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
          <option value="popular" <?= $sort === 'popular' ? 'selected' : ''; ?>>Terpopuler</option>

        </select>

      </div>

    </div>
    </form>

  </div>

    <!-- Product Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
  <?php if (mysqli_num_rows($products) > 0): ?>
    <?php while($product = mysqli_fetch_assoc($products)): ?>

      <?php
        $imgPath = __DIR__ . '/../../uploads/products/' . ($product['image'] ?? '');
        $imgSrc = (isset($product['image']) && file_exists($imgPath)) ? UPLOAD_URL . '/products/' . $product['image'] : 'https://placehold.co/600x400';
        $productName = $product['name'];
        $price = 'Rp ' . number_format($product['price']);
        $image = $imgSrc;
        $seller = htmlspecialchars($product['seller_name'] ?? 'UMKM Indonesia');
        $id = $product['id'];
      ?>

      <?php include '../components/product-card.php'; ?>

    <?php endwhile; ?>

    <?php else: ?>

    <div class="col-span-full bg-white rounded-3xl shadow-sm p-8 lg:p-16 text-center">

      <h3 class="text-2xl font-bold mb-4">

        Produk Tidak Ditemukan

      </h3>

      <p class="text-gray-500">

        Coba gunakan keyword atau kategori lain.

      </p>

    </div>

    <?php endif; ?>
  </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>

      <div class="flex items-center justify-center gap-3 mt-16 flex-wrap">

        <!-- Prev -->
        <?php if ($page > 1): ?>

          <a
            href="?page=<?= $page - 1; ?>&q=<?= urlencode($q); ?>&category=<?= urlencode($cat); ?>&sort=<?= urlencode($sort); ?>"
            class="w-10 h-10 lg:w-12 lg:h-12 rounded-2xl border hover:bg-gray-100 transition flex items-center justify-center"
          >

            ←

          </a>

        <?php endif; ?>

        <!-- Numbers -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

          <a
            href="?page=<?= $i; ?>&q=<?= urlencode($q); ?>&category=<?= urlencode($cat); ?>&sort=<?= urlencode($sort); ?>"
            class="w-12 h-12 rounded-2xl flex items-center justify-center transition
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
            href="?page=<?= $page + 1; ?>&q=<?= urlencode($q); ?>&category=<?= urlencode($cat); ?>&sort=<?= urlencode($sort); ?>"
            class="w-12 h-12 rounded-2xl border hover:bg-gray-100 transition flex items-center justify-center"
          >

            →

          </a>

        <?php endif; ?>

      </div>

      <?php endif; ?>

</section>

<?php include '../layouts/footer.php'; ?>

</main>
</body>
</html>


  