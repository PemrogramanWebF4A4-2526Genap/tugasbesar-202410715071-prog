<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$q = trim($_GET['q'] ?? '');

$where = "WHERE 1=1";

if ($q !== '') {

    $qEsc = mysqli_real_escape_string($conn, $q);

    $where .= "
        AND categories.name LIKE '%$qEsc%'
    ";
}

$totalCategories = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories")
)['total'];

$totalProducts = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM products")
)['total'];

$perPage = 10;

$page = max(1, intval($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$countQuery = "
SELECT COUNT(*) AS total

FROM categories

$where
";

$totalData = mysqli_fetch_assoc(
    mysqli_query($conn, $countQuery)
)['total'];

$totalPages = ceil($totalData / $perPage);

$query = "
SELECT
    categories.*,
    COUNT(products.id) AS total_products

FROM categories

LEFT JOIN products
ON categories.id = products.category_id

$where

GROUP BY categories.id

ORDER BY categories.created_at DESC
LIMIT $perPage OFFSET $offset
";

$categories = mysqli_query($conn, $query);

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main -->
  <main class="flex-1 min-w-0 p-4 lg:p-10 overflow-x-hidden">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

      <div>

        <h1 class="text-3xl lg:text-4xl font-bold mb-3">

          Kelola Kategori

        </h1>

        <p class="text-gray-600">

          Tambah dan kelola kategori marketplace.

        </p>

        <?php if (isset($_GET['created'])): ?>

        <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">

          Kategori berhasil ditambahkan.

        </div>

        <?php elseif (isset($_GET['exists'])): ?>

          <div class="mt-6 rounded-3xl bg-yellow-50 border border-yellow-200 p-6 text-yellow-700">

            Kategori sudah ada.

          </div>

        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>

          <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">

            Kategori berhasil diperbarui.

          </div>

        <?php elseif (isset($_GET['deleted'])): ?>

          <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">

            Kategori berhasil dihapus.

          </div>

        <?php elseif (isset($_GET['used'])): ?>

          <div class="mt-6 rounded-3xl bg-red-50 border border-red-200 p-6 text-red-700">

            Kategori masih digunakan produk.

          </div>

        <?php endif; ?>

      </div>


    </div>
        <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

      <!-- Total -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Kategori

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-emerald-500">

          <?= number_format($totalCategories); ?>

        </h2>

      </div>

      <!-- Active -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Kategori Aktif

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-blue-500">

          <?= number_format($totalCategories); ?>

        </h2>

      </div>

      <!-- Products -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Produk

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-yellow-500">

          <?= number_format($totalProducts); ?>

        </h2>

      </div>

    </div>
        <!-- Add Category -->
    <div class="bg-white rounded-3xl shadow-sm p-8 mb-10">

      <h2 class="text-2xl font-bold mb-8">

        Tambah Kategori

      </h2>

      <form action="<?= BASE_URL ?>/src/admin/add-category.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Name -->
        <input
          type="text"
          name="name"
          placeholder="Nama kategori"
          required
          class="border border-gray-300 rounded-2xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >

        <!-- Icon -->
        <div>

          <div class="flex items-center gap-2">

            <!-- Preview -->
            <img
              id="icon-preview"
              src="https://placehold.co/100x100/E2E8F0/64748B?text=Icon"
              class="w-14 h-14 object-cover rounded-xl border border-gray-200 shadow-sm"
            >

            <!-- Upload Area -->
            <label
              for="icon-upload"
              class="flex-1 border-2 border-dashed border-gray-300 hover:border-emerald-500 bg-gray-50 hover:bg-emerald-50 rounded-2xl px-4 py-2 cursor-pointer transition"
            >

              <div class="flex flex-col">

                <h3 class="font-bold group-hover:text-emerald-600 transition">
                  Upload Icon
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                  PNG, JPG, WEBP • Max 2MB
                </p>

              </div>

              <input
                id="icon-upload"
                type="file"
                name="icon"
                accept="image/png,image/jpg,image/jpeg,image/webp"
                class="hidden"
                onchange="previewCategoryIcon(event)"
              >

            </label>

          </div>

        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl transition"
        >

          Simpan Kategori

        </button>

      </form>

    </div>
        <!-- Category Table -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <div class="overflow-x-auto">

        <table class="w-full min-w-[900px]">

          <!-- Head -->
          <thead class="bg-gray-50">

            <tr>

              <th class="text-left px-6 py-5">
                Kategori
              </th>

              <th class="text-left px-6 py-5">
                Total Produk
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

          <?php if (mysqli_num_rows($categories) > 0): ?>

            <?php while($category = mysqli_fetch_assoc($categories)): ?>

            <tr class="border-t hover:bg-gray-50 transition">

              <!-- Name -->
              <td class="px-6 py-5">

                <div class="flex items-center gap-4">

                  <?php
                    $iconPath = __DIR__ . '/../../uploads/categories/' . ($category['icon'] ?? '');

                    $iconSrc = (!empty($category['icon']) && file_exists($iconPath))
                      ? UPLOAD_URL . '/categories/' . $category['icon']
                      : 'https://placehold.co/100';
                  ?>

                  <img
                    src="<?= $iconSrc; ?>"
                    class="w-14 h-14 object-cover rounded-2xl border"
                  >

                  <div>

                    <h3 class="font-bold">

                      <?= htmlspecialchars($category['name']); ?>

                    </h3>

                    <p class="text-sm text-gray-500">

                      Kategori marketplace

                    </p>

                  </div>

                </div>

              </td>

              <!-- Product Count -->
              <td class="px-6 py-5 font-bold text-emerald-500">

                <?= number_format($category['total_products']); ?> Produk

              </td>

              <!-- Status -->
              <td class="px-6 py-5">

                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm">

                  Aktif

                </span>

              </td>

              <!-- Action -->
              <td class="px-6 py-5">

                <div class="flex flex-wrap gap-3">

                  <a
                    href="<?= BASE_URL ?>/src/views/admin/edit-category.php?id=<?= $category['id']; ?>"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl transition"
                  >
                    Edit
                  </a>

                  <a
                    href="<?= BASE_URL ?>/src/admin/delete-category.php?id=<?= $category['id']; ?>"
                    onclick="return confirm('Hapus kategori ini?')"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl transition"
                  >
                    Hapus
                  </a>

                </div>

              </td>

            </tr>

            <?php endwhile; ?>
            <?php else: ?>

            <tr>

              <td colspan="4" class="px-6 py-16 text-center text-gray-500">

                Kategori tidak ditemukan.

              </td>

            </tr>

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
            href="?page=<?= $page - 1; ?>&q=<?= urlencode($q); ?>"
            class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
          >

            ←

          </a>

        <?php endif; ?>

        <!-- Numbers -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

          <a
            href="?page=<?= $i; ?>&q=<?= urlencode($q); ?>"
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
            href="?page=<?= $page + 1; ?>&q=<?= urlencode($q); ?>"
            class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
          >

            →

          </a>

        <?php endif; ?>

      </div>

      <?php endif; ?>
      </main>

</div>
<script>

function previewCategoryIcon(event) {

    const input = event.target;

    const preview = document.getElementById('icon-preview');

    if (input.files && input.files[0]) {

        const reader = new FileReader();

        reader.onload = function(e) {

            preview.src = e.target.result;

        };

        reader.readAsDataURL(input.files[0]);

    }

}

</script>
</body>
</html>