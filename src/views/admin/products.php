<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$q = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE 1=1";

if ($q !== '') {

    $qEsc = mysqli_real_escape_string($conn, $q);

    $where .= "
        AND (
            products.name LIKE '%$qEsc%'
            OR users.name LIKE '%$qEsc%'
        )
    ";
}

if ($category !== '') {

    $categoryId = intval($category);

    $where .= " AND products.category_id='$categoryId'";
}

$allowedStatus = ['active', 'draft'];

if (in_array($status, $allowedStatus)) {

    $where .= " AND products.status='$status'";
}

$categories = mysqli_query(
    $conn,
    "SELECT * FROM categories ORDER BY name ASC"
);

$perPage = 10;

$page = max(1, intval($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$countQuery = "
SELECT COUNT(*) AS total

FROM products

JOIN users
ON products.seller_id = users.id

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
    users.name AS seller_name,
    categories.name AS category_name

FROM products

JOIN users
ON products.seller_id = users.id

JOIN categories
ON products.category_id = categories.id

$where

ORDER BY products.created_at DESC
LIMIT $perPage OFFSET $offset
";

$products = mysqli_query($conn, $query);

function getStatusClasses($status) {

    $styles = [
        'active' => ['bg-green-100', 'text-green-700'],
        'draft' => ['bg-yellow-100', 'text-yellow-700'],
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
        Kelola Produk
      </h1>

      <p class="text-gray-600">
        Monitor seluruh produk marketplace.
      </p>

      <?php if (isset($_GET['deleted'])): ?>

        <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">

          Produk berhasil dihapus.

        </div>

        <?php elseif (isset($_GET['delete_error'])): ?>

        <div class="mt-6 rounded-3xl bg-red-50 border border-red-200 p-6 text-red-700">

            Gagal menghapus produk.

        </div>

      <?php endif; ?>


    </div>

    <!-- Filter -->
    <div class="bg-white rounded-3xl shadow-sm p-6 mb-10">

      <form method="GET" action="products.php">

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

          <!-- Search -->
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($q); ?>"
            placeholder="Cari produk / seller..."
            class="border border-gray-300 rounded-2xl px-4 py-3"
          >

          <!-- Category -->
          <select
            name="category"
            class="border border-gray-300 rounded-2xl px-4 py-3"
          >

            <option value="">
              Semua Kategori
            </option>

            <?php while($cat = mysqli_fetch_assoc($categories)): ?>

              <option
                value="<?= $cat['id']; ?>"
                <?= $category == $cat['id'] ? 'selected' : ''; ?>
              >
                <?= htmlspecialchars($cat['name']); ?>
              </option>

            <?php endwhile; ?>

          </select>

          <!-- Status -->
          <select
            name="status"
            class="border border-gray-300 rounded-2xl px-4 py-3"
          >

            <option value="">
              Semua Status
            </option>

            <option value="active" <?= $status === 'active' ? 'selected' : ''; ?>>
              Active
            </option>

            <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>
              Draft
            </option>

          </select>

          <!-- Submit -->
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

        <table class="w-full min-w-[1200px]">

          <thead class="bg-gray-50">

            <tr>

              <th class="text-left px-6 py-5">
                Produk
              </th>

              <th class="text-left px-6 py-5">
                Seller
              </th>

              <th class="text-left px-6 py-5">
                Kategori
              </th>

              <th class="text-left px-6 py-5">
                Harga
              </th>

              <th class="text-left px-6 py-5">
                Stock
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
            <?php if (mysqli_num_rows($products) > 0): ?>

            <?php while($product = mysqli_fetch_assoc($products)): ?>

              <?php [$bg, $text] = getStatusClasses($product['status']); ?>

              <?php
                $imgPath = __DIR__ . '/../../uploads/products/' . ($product['image'] ?? '');
                $imgSrc = (!empty($product['image']) && file_exists($imgPath))
                  ? UPLOAD_URL . '/products/' . $product['image']
                  : 'https://placehold.co/300';
              ?>

              <tr class="border-t hover:bg-gray-50 transition">

                <!-- Product -->
                <td class="px-6 py-5">

                  <div class="flex items-center gap-4">

                    <img
                      src="<?= $imgSrc; ?>"
                      class="w-16 h-16 object-cover rounded-2xl"
                    >

                    <div>

                      <h3 class="font-bold">

                        <?= htmlspecialchars($product['name']); ?>

                      </h3>

                    </div>

                  </div>

                </td>

                <!-- Seller -->
                <td class="px-6 py-5">

                  <?= htmlspecialchars($product['seller_name']); ?>

                </td>

                <!-- Category -->
                <td class="px-6 py-5">

                  <?= htmlspecialchars($product['category_name']); ?>

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

                    <?= intval($product['stock']); ?> Tersisa

                  </span>

                <?php else: ?>

                  <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-sm">

                    <?= intval($product['stock']); ?> Ready

                  </span>

                <?php endif; ?>

                </td>

                <!-- Status -->
                <td class="px-6 py-5">

                  <span class="<?= $bg ?> <?= $text ?> px-4 py-2 rounded-full text-sm">

                    <?= ucfirst($product['status']); ?>

                  </span>

                </td>

                <!-- Action -->
                <td class="px-6 py-5">

                  <a
                    href="<?= BASE_URL ?>/src/admin/delete-product.php?id=<?= $product['id']; ?>"
                    onclick="return confirm('Hapus produk ini?')"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl transition"
                  >
                    Hapus
                  </a>

                </td>

              </tr>

            <?php endwhile; ?>

            <?php else: ?>

            <tr>

              <td colspan="7" class="px-6 py-16 text-center text-gray-500">

                Produk tidak ditemukan.

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
            href="?page=<?= $page - 1; ?>&q=<?= urlencode($q); ?>&category=<?= urlencode($category); ?>&status=<?= urlencode($status); ?>"
            class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
          >

            ←

          </a>

        <?php endif; ?>

        <!-- Numbers -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

          <a
            href="?page=<?= $i; ?>&q=<?= urlencode($q); ?>&category=<?= urlencode($category); ?>&status=<?= urlencode($status); ?>"
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
            href="?page=<?= $page + 1; ?>&q=<?= urlencode($q); ?>&category=<?= urlencode($category); ?>&status=<?= urlencode($status); ?>"
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