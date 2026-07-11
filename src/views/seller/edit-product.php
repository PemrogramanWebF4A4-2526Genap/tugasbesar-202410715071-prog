<?php require_once '../../middleware/seller.php'; ?>
<?php require_once '../../config/database.php'; ?>

<?php
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: products.php');
    exit;
}
$userId = $_SESSION['user']['id'];

$res = mysqli_query(
    $conn,
    "SELECT * FROM products
     WHERE id='$id'
     AND seller_id='$userId'
     LIMIT 1"
);
$product = mysqli_fetch_assoc($res);
if (!$product) {
    header('Location: products.php');
    exit;
}

$categories = mysqli_query($conn, "SELECT * FROM categories");
?>
<?php include '../layouts/header.php'; ?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main -->
  <main class="flex-1 min-w-0 overflow-x-hidden p-4 lg:p-10">

    <!-- Header -->
    <div class="mb-10">

      <h1 class="text-3xl lg:text-4xl font-bold mb-3">

        Edit Produk

      </h1>

      <p class="text-gray-600">

        Perbarui informasi produk Anda.

      </p>

    </div>
    
    <!-- Form -->
    <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8">

      <form action="<?= BASE_URL ?>/src/seller/update-product.php?id=<?= $product['id']; ?>" method="POST" enctype="multipart/form-data" class="space-y-8">
                <!-- Upload -->
        <div>

          <label class="block font-semibold mb-4">

            Gambar Produk Saat Ini

          </label>

          <div class="grid lg:grid-cols-[auto_1fr] gap-6 items-center mb-6">
            <?php
            $currentImage = (!empty($product['image']) &&
                file_exists(__DIR__ . '/../../uploads/products/' . $product['image']))
                ? UPLOAD_URL . '/products/' . $product['image']
                : 'https://placehold.co/300';
            ?>

            <div>
              <label class="block font-semibold mb-2">Unggah Gambar Baru (opsional)</label>
              <div class="border-2 border-dashed border-gray-300 rounded-3xl p-8 hover:border-emerald-500 transition">
              <div class="flex flex-col lg:flex-row items-center gap-6">
                
                <div id="currentImagePreview" class="w-28 h-28 rounded-2xl bg-cover bg-center" style="background-image: url('<?= $currentImage; ?>');"></div>

                <div class="flex-1">

                  <h3 class="text-xl font-bold mb-3">

                    Upload Gambar Baru

                  </h3>

                  <p class="text-gray-500 mb-6">

                    PNG, JPG, JPEG maksimal 5MB

                  </p>

                  <input
                    id="productImageInput"
                    name="image"
                    type="file"
                    accept="image/png,image/jpg,image/jpeg"
                    class="hidden"
                  >

                  <button
                    type="button"
                    id="selectImageButton"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition"
                  >

                    Pilih File

                  </button>

                  <p id="imageFileName" class="text-sm text-gray-500 mt-4"></p>

                </div>

              </div>

            </div>
            </div>

          </div>

        </div>
                <!-- Product Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- Product Name -->
          <div>

            <label class="block font-semibold mb-3">

              Nama Produk

            </label>

            <input
              type="text"
              name="name"
              required
              value="<?= htmlspecialchars($product['name']); ?>"
              placeholder="Masukkan nama produk"
              class="w-full border border-gray-300 rounded-2xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >

          </div>

          <!-- Category -->
          <div>

            <label class="block font-semibold mb-3">

              Kategori

            </label>

            <select
              name="category_id" required class="w-full border border-gray-300 rounded-2xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >

              <option value="">Pilih Kategori</option>
              <?php while($category = mysqli_fetch_assoc($categories)): ?>

                <option value="<?= $category['id']; ?>" <?= $category['id']==$product['category_id']? 'selected':''; ?>>

                  <?= htmlspecialchars($category['name']); ?>

                </option>

              <?php endwhile; ?>

            </select>

          </div>

        </div>
                <!-- Price & Stock -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- Price -->
          <div>

            <label class="block font-semibold mb-3">

              Harga Produk

            </label>

            <input
              name="price"
              type="number"
              required
              step="0.01"
              value="<?= html_entity_decode($product['price']) ?>"
              placeholder="Masukkan harga"
              class="w-full border border-gray-300 rounded-2xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >

          </div>

          <!-- Stock -->
          <div>

            <label class="block font-semibold mb-3">

              Stok Produk

            </label>

            <input
              name="stock"
              type="number"
              required
              min="0"
              value="<?= htmlspecialchars($product['stock']); ?>"
              placeholder="Masukkan stok"
              class="w-full border border-gray-300 rounded-2xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >

          </div>

        </div>
                <!-- Description -->
        <div>

          <label class="block font-semibold mb-3">

            Deskripsi Produk

          </label>

          <textarea
            name="description"
            required
            rows="8"
            placeholder="Masukkan deskripsi produk..."
            class="w-full border border-gray-300 rounded-2xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          ><?= htmlspecialchars($product['description']); ?></textarea>

        </div>
                <!-- Status -->
        <div>

          <label class="block font-semibold mb-4">

            Status Produk

          </label>

          <div class="flex flex-wrap gap-6">

            <label class="flex items-center gap-3">

              <input type="radio" name="status" value="active" <?= $product['status']=='active'? 'checked':''; ?>>

              <span>Aktif</span>

            </label>

            <label class="flex items-center gap-3">

              <input name="status" type="radio" value="draft" <?= $product['status']=='draft'? 'checked':''; ?>>

              <span>Draft</span>

            </label>

          </div>

        </div>
                <!-- Action -->
        <div class="flex flex-col sm:flex-row gap-4 pt-4">

          <button
            type="submit"
            class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-2xl transition"
          >

            Perbarui Produk

          </button>

          <a href="products.php" class="border border-gray-300 hover:bg-gray-100 px-8 py-4 rounded-2xl transition inline-block">Batal</a>

        </div>

      </form>

    </div>
      </main>

</div>

<script>
  const productImageInput = document.getElementById('productImageInput');
  const currentImagePreview = document.getElementById('currentImagePreview');
  const imageFileName = document.getElementById('imageFileName');
  const selectImageButton = document.getElementById('selectImageButton');
  selectImageButton.addEventListener('click', () => {
    productImageInput.click();
  });

  if (productImageInput) {
    productImageInput.addEventListener('change', () => {
      const file = productImageInput.files[0];
      if (!file) {
        imageFileName.textContent = '';
        return;
      }
      imageFileName.textContent = file.name;
      const reader = new FileReader();
      reader.onload = (event) => {
        currentImagePreview.style.backgroundImage = `url('${event.target.result}')`;
      };
      reader.readAsDataURL(file);
    });
  }
</script>

</body>
</html>