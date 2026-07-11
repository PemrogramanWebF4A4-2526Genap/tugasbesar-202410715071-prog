<?php
    require_once '../../middleware/seller.php';
    require_once '../../config/database.php';

    $categories = mysqli_query(
        $conn,
        "SELECT * FROM categories"
    );

    $seller_id = $_SESSION['user']['id'];
    $search = trim($_GET['q'] ?? '');
    $categoryFilter = $_GET['category'] ?? '';
    $statusFilter = $_GET['status'] ?? '';

    $where = "WHERE seller_id='$seller_id'";
    if ($search !== '') {
        $searchEsc = mysqli_real_escape_string($conn, $search);
        $where .= " AND (products.name LIKE '%$searchEsc%' OR products.description LIKE '%$searchEsc%')";
    }

    if ($categoryFilter !== '' && is_numeric($categoryFilter)) {
        $where .= " AND products.category_id='" . (int)$categoryFilter . "'";
    }

    if ($statusFilter !== '') {
        $allowedStatus = ['active', 'draft'];
        if (in_array($statusFilter, $allowedStatus, true)) {
            $where .= " AND products.status='$statusFilter'";
        }
    }
    
    $perPage = 8;

    $page = max(1, intval($_GET['page'] ?? 1));

    $offset = ($page - 1) * $perPage;

    $countQuery = "
    SELECT COUNT(*) AS total

    FROM products

    JOIN categories
    ON products.category_id = categories.id

    $where
    ";

    $totalProducts = mysqli_fetch_assoc(
        mysqli_query($conn, $countQuery)
    )['total'];

    $totalPages = ceil($totalProducts / $perPage);

    $query = "
    SELECT
        products.*,
        categories.name AS category_name

    FROM products

    JOIN categories
    ON products.category_id = categories.id

    $where

    ORDER BY products.id DESC
    LIMIT $perPage OFFSET $offset
    ";

    $products = mysqli_query($conn, $query);
?>
<?php include '../layouts/header.php'; ?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main -->
  <main class="flex-1 min-w-0 overflow-x-hidden p-4 lg:p-10">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

      <div>

        <h1 class="text-3xl lg:text-4xl font-bold mb-3">

          Kelola Produk

        </h1>

        <p class="text-gray-600">

          Tambah dan kelola produk UMKM Anda.

        </p>

      </div>

      <!-- Add Product -->
        <a
        href="add-product.php"
        class="w-full lg:w-auto text-center inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-4 rounded-2xl transition"
        >
        Tambah Produk
        </a>

    </div>
        <!-- Search -->
    <div class="bg-white rounded-3xl shadow-sm p-6 mb-10">

      <form method="GET" action="products.php">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

          <!-- Search -->
          <input
            name="q"
            value="<?= htmlspecialchars($search); ?>"
            type="text"
            placeholder="Cari produk..."
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

          <!-- Category -->
          <select
            name="category"
            onchange="this.form.submit()"
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

            <option value="">Semua Kategori</option>
            <?php while($category = mysqli_fetch_assoc($categories)): ?>

              <option value="<?= $category['id']; ?>" <?= $categoryFilter == $category['id'] ? 'selected' : ''; ?>>

                <?= htmlspecialchars($category['name']); ?>

              </option>

            <?php endwhile; ?>

          </select>

          <!-- Status -->
          <select
            name="status"
            onchange="this.form.submit()"
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

            <option value="">Semua Status</option>
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : ''; ?>>Aktif</option>
            <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>

          </select>

        </div>
      </form>

    </div>
        <!-- Product Table -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <div class="overflow-x-auto">

        <table class="w-full min-w-[1200px]">

          <!-- Head -->
          <thead class="bg-gray-50">

            <tr>

              <th class="text-left px-6 py-5">
                Produk
              </th>

              <th class="text-left px-6 py-5">
                Kategori
              </th>

              <th class="text-left px-6 py-5">
                Harga
              </th>

              <th class="text-left px-6 py-5">
                Stok
              </th>

              <th class="text-left px-6 py-5">
                Status
              </th>

              <th class="text-left px-6 py-5">
                Aksi
              </th>

            </tr>

          </thead>

          <!-- Body -->
          <tbody>
            <?php if (mysqli_num_rows($products) === 0): ?>

              <tr>
                <td class="px-6 py-10" colspan="6">
                  <div class="text-center text-gray-500">
                    Belum ada produk. <a href="add-product.php" class="text-emerald-500">Tambah produk</a>
                  </div>
                </td>
              </tr>

            <?php else: ?>

            <?php while($product = mysqli_fetch_assoc($products)): ?>

              <tr class="border-t hover:bg-gray-50 transition">

              <!-- Product -->
              <td class="px-6 py-5">

                <div class="flex flex-col sm:flex-row sm:items-center gap-5">

                  <?php
                    $imgPath = __DIR__ . '/../../uploads/products/' . ($product['image'] ?? '');
                    $imgSrc = (isset($product['image']) && file_exists($imgPath)) ? UPLOAD_URL . '/products/' . $product['image'] : 'https://placehold.co/80';
                  ?>

                  <img
                    src="<?= $imgSrc ?>"
                    alt="Product"
                    class="w-16 h-16 rounded-2xl object-cover"
                  >

                  <div>

                    <h3 class="font-bold">

                      <?= htmlspecialchars($product['category_name']); ?>

                    </h3>

                    <p class="text-sm text-gray-500">

                      <?= htmlspecialchars(substr($product['description'], 0, 50)); ?>

                    </p>

                  </div>

                </div>

              </td>

              <!-- Category -->
              <td class="px-6 py-5">

                <?= htmlspecialchars($product['name']); ?>

              </td>

              <!-- Price -->
              <td class="px-6 py-5 font-bold text-emerald-500">

                Rp <?= number_format($product['price']); ?>

              </td>

              <!-- Stock -->
              <td class="px-6 py-5">

                <?php if ($product['stock'] <= 0): ?>

                  <span class="bg-red-100 text-red-700 px-4 py-2 rounded-full text-sm">

                    Habis

                  </span>

                <?php elseif ($product['stock'] <= 5): ?>

                  <span class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-full text-sm">

                    <?= $product['stock']; ?> Tersisa

                  </span>

                <?php else: ?>

                  <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm">

                    <?= $product['stock']; ?> Ready

                  </span>

                <?php endif; ?>

              </td>

              <!-- Status -->
              <td class="px-6 py-5">

                <?php if ($product['status'] === 'active'): ?>

                  <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm">

                    Aktif

                  </span>

                <?php else: ?>

                  <span class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full text-sm">

                    Draft

                  </span>

                <?php endif; ?>

              </td>

              <!-- Action -->
              <td class="px-6 py-5">

                <div class="flex flex-wrap gap-3">

                  <a href="edit-product.php?id=<?= $product['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl transition">Edit</a>

                  <a
                    href="<?= BASE_URL ?>/src/seller/delete-product.php?id=<?= $product['id']; ?>"
                    onclick="return confirm('Yakin ingin menghapus produk ini?');"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl transition"
                  >
                    Hapus
                  </a>

                </div>

              </td>

            </tr>

            <?php endwhile; ?>

            <?php endif; ?>

            </tbody>
        </table>

      </div>

    </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>

          <div class="flex items-center justify-center gap-3 mt-10 flex-wrap">

            <!-- Prev -->
            <?php if ($page > 1): ?>

              <a
                href="?page=<?= $page - 1; ?>&q=<?= urlencode($search); ?>&category=<?= urlencode($categoryFilter); ?>&status=<?= urlencode($statusFilter); ?>"
                class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
              >

                ←

              </a>

            <?php endif; ?>

            <!-- Numbers -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

              <a
                href="?page=<?= $i; ?>&q=<?= urlencode($search); ?>&category=<?= urlencode($categoryFilter); ?>&status=<?= urlencode($statusFilter); ?>"
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
                href="?page=<?= $page + 1; ?>&q=<?= urlencode($search); ?>&category=<?= urlencode($categoryFilter); ?>&status=<?= urlencode($statusFilter); ?>"
                class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
              >

                →

              </a>

            <?php endif; ?>

          </div>

          <?php endif; ?>

</body>
</html>