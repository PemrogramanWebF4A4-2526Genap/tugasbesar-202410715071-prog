<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: categories.php');
    exit;
}

$res = mysqli_query(
    $conn,
    "SELECT * FROM categories WHERE id='$id' LIMIT 1"
);

$category = mysqli_fetch_assoc($res);

if (!$category) {
    header('Location: categories.php');
    exit;
}

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <?php include 'sidebar.php'; ?>

  <main class="flex-1 min-w-0 p-4 lg:p-10 overflow-x-hidden">

    <div class="max-w-3xl mx-auto">

      <div class="bg-white rounded-3xl shadow-sm p-8">

        <div class="mb-10">

          <h1 class="text-3xl font-bold mb-3">
            Edit Kategori
          </h1>

          <p class="text-gray-600">
            Perbarui kategori marketplace.
          </p>

        </div>

        <form
          action="<?= BASE_URL ?>/src/admin/update-category.php"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6"
        >

          <input
            type="hidden"
            name="id"
            value="<?= $category['id']; ?>"
          >

          <!-- Name -->
          <div>

            <label class="block font-semibold mb-3">
              Nama Kategori
            </label>

            <input
              type="text"
              name="name"
              value="<?= htmlspecialchars($category['name']); ?>"
              required
              class="w-full border border-gray-300 rounded-2xl px-4 py-4"
            >

          </div>

          <!-- Icon Upload -->
          <div>

            <label class="block font-semibold mb-3">
              Icon Kategori
            </label>

            <?php
              $iconPath = __DIR__ . '/../../uploads/categories/' . ($category['icon'] ?? '');

              $iconSrc = (!empty($category['icon']) && file_exists($iconPath))
                ? UPLOAD_URL . '/categories/' . $category['icon']
                : 'https://placehold.co/100';
            ?>

            <div class="flex items-center gap-4">

              <!-- Preview -->
              <img
                id="icon-preview"
                src="<?= $iconSrc; ?>"
                class="w-20 h-20 object-cover rounded-2xl border shadow-sm"
              >

              <!-- Upload -->
              <label
                for="icon-upload"
                class="flex-1 border-2 border-dashed border-gray-300 hover:border-emerald-500 bg-gray-50 hover:bg-emerald-50 rounded-2xl px-5 py-4 cursor-pointer transition"
              >

                <div>

                  <h3 class="font-bold">
                    Upload Icon Baru
                  </h3>

                  <p class="text-sm text-gray-500 mt-1">
                    PNG, JPG, WEBP
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
            class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-4 rounded-2xl transition"
          >
            Update Kategori
          </button>

        </form>

      </div>

    </div>

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