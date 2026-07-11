<?php require_once '../../middleware/seller.php'; ?>

<?php require_once '../../config/database.php'; ?>

<?php

$categories = mysqli_query(
    $conn,
    "SELECT * FROM categories"
);

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

        Tambah Produk

      </h1>

      <p class="text-gray-600">

        Tambahkan produk baru ke marketplace.

      </p>

    </div>
    
    <!-- Form -->
    <div class="bg-white rounded-3xl shadow-sm p-5 lg:p-8">

      <form action="<?= BASE_URL ?>/src/seller/store-product.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                <!-- Upload -->
        <div>

          <label class="block font-semibold mb-4">

            Upload Gambar Produk

          </label>

          <div class="border-2 border-dashed border-gray-300 rounded-3xl p-6 lg:p-10 text-center hover:border-emerald-500 transition cursor-pointer">

            <div id="imagePreview" class="w-20 h-20 bg-emerald-100 rounded-3xl mx-auto mb-6"></div>

            <h3 class="text-xl font-bold mb-3">

              Upload Gambar

            </h3>

            <p class="text-gray-500 mb-6">

              PNG, JPG, JPEG maksimal 5MB

            </p>

            <input id="productImageInput" name="image" type="file" accept="image/png,image/jpg,image/jpeg" class="hidden">

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

              <option>Pilih Kategori</option>
              <?php while($category = mysqli_fetch_assoc($categories)): ?>

                <option value="<?= $category['id']; ?>">

                  <?= $category['name']; ?>

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
          ></textarea>

        </div>
                <!-- Status -->
        <div>

          <label class="block font-semibold mb-4">

            Status Produk

          </label>

          <div class="flex flex-wrap gap-6">

            <label class="flex items-center gap-3">

              <input type="radio" name="status" value="active" checked>

              <span>Aktif</span>

            </label>

            <label class="flex items-center gap-3">

              <input name="status" type="radio" value="draft">

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

            Simpan Produk

          </button>

          <button
            type="button"
            class="border border-gray-300 hover:bg-gray-100 px-8 py-4 rounded-2xl transition"
          >

            Batal

          </button>

        </div>

      </form>

    </div>
      </main>

</div>

<script>
  const productImageInput = document.getElementById('productImageInput');
  const selectImageButton = document.getElementById('selectImageButton');
  const imagePreview = document.getElementById('imagePreview');
  const imageFileName = document.getElementById('imageFileName');

  selectImageButton.addEventListener('click', () => {
    productImageInput.click();
  });

  productImageInput.addEventListener('change', () => {
    const file = productImageInput.files[0];
    if (!file) {
      imagePreview.style.backgroundImage = '';
      imageFileName.textContent = '';
      return;
    }

    imageFileName.textContent = file.name;
    const reader = new FileReader();
    reader.onload = (event) => {
      imagePreview.style.backgroundImage = `url('${event.target.result}')`;
      imagePreview.style.backgroundSize = 'cover';
      imagePreview.style.backgroundPosition = 'center';
    };
    reader.readAsDataURL(file);
  });
</script>

</body>
</html>