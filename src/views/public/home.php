<?php


require_once '../../config/database.php';

// Featured products
$query = "SELECT products.*, categories.name AS category_name, users.name AS seller_name FROM products JOIN categories ON products.category_id = categories.id JOIN users ON products.seller_id = users.id WHERE products.status='active' ORDER BY products.id DESC LIMIT 8";
$products = mysqli_query($conn, $query);

// categories for showcase
$categoriesRes = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC LIMIT 6");


?>

<?php include '../layouts/app.php'; ?>


<section class="bg-gradient-to-b from-emerald-50 to-white">

  <div class="max-w-7xl mx-auto px-4 py-20">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

      <!-- Left Content -->
      <div>

        <span class="bg-emerald-100 text-emerald-600 px-4 py-2 rounded-full text-sm font-medium">
          Marketplace UMKM Indonesia
        </span>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mt-6 mb-6">

          Dukung Produk
          <span class="text-emerald-500">
            UMKM Lokal
          </span>

        </h1>

        <p class="text-gray-600 text-lg leading-relaxed mb-8">

          Temukan produk berkualitas dari berbagai UMKM Indonesia
          dengan pengalaman belanja modern dan terpercaya.

        </p>

        <div class="flex flex-col sm:flex-row gap-4">

            <a href="<?= BASE_URL ?>/src/views/public/shop.php" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-2xl transition inline-block">
                Jelajahi Produk
            </a>
            <?php if (!isset($_SESSION['user'])): ?>
            <a href="<?= BASE_URL ?>/src/views/public/register.php" class="border border-gray-300 hover:bg-gray-100 px-8 py-4 rounded-2xl transition inline-block">
                Menjadi Seller
            </a>
            <?php else: ?>
            <?php endif; ?>
        </div>

      </div>

      <!-- Right Image -->
      <div>

        <img
          src="../../assets/images/hero.jpg"
          alt="Marketplace"
          class="rounded-3xl shadow-xl w-full h-[300px] lg:h-auto object-cover"
        >

      </div>

    </div>

  </div>

</section>

<section class="max-w-7xl mx-auto px-4 py-20">

  <!-- Header -->
  <div class="flex items-center justify-between mb-10">

    <h2 class="text-2xl lg:text-3xl font-bold">
      Kategori Populer
    </h2>

    <a
      href="shop.php"
      class="text-emerald-500 font-medium hover:underline"
    >
      Lihat Semua
    </a>

  </div>

  <!-- Grid -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6">

    <?php while($c = mysqli_fetch_assoc($categoriesRes)): ?>

      <?php
        $iconPath = __DIR__ . '/../../uploads/categories/' . ($c['icon'] ?? '');

        $iconSrc = (!empty($c['icon']) && file_exists($iconPath))
          ? UPLOAD_URL . '/categories/' . $c['icon']
          : 'https://placehold.co/100';
      ?>

      <a
        href="shop.php?category=<?= $c['id']; ?>"
        class="bg-white rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition p-4 lg:p-6 text-center border border-gray-100"
      >

        <img
          src="<?= $iconSrc; ?>"
          class="w-16 h-16 lg:w-20 lg:h-20 object-cover rounded-2xl mx-auto mb-5"
        >

        <h3 class="font-bold text-gray-800">

          <?= htmlspecialchars($c['name']); ?>

        </h3>

      </a>

    <?php endwhile; ?>

  </div>

</section>

<section class="max-w-7xl mx-auto px-4 pb-20">

  <div class="bg-emerald-500 rounded-3xl overflow-hidden">

    <div class="grid grid-cols-1 lg:grid-cols-2 items-center">

      <div class="p-10 lg:p-16 text-white">

        <h2 class="text-3xl lg:text-4xl font-bold mb-6">

          Promo Spesial UMKM Indonesia

        </h2>

        <p class="text-emerald-50 text-lg mb-8">

          Dapatkan berbagai produk terbaik dari UMKM lokal
          dengan penawaran spesial setiap hari.

        </p>

        <a href="<?= BASE_URL ?>/src/views/public/shop.php" class="bg-white text-emerald-500 px-8 py-4 rounded-2xl font-medium hover:bg-gray-100 transition">

          Belanja Sekarang

        </a>

      </div>

      <div>

        <img
          src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?q=80&w=1200&auto=format&fit=crop"
          alt="Promo"
          class="w-full h-full object-cover"
        >

      </div>

    </div>

  </div>

</section>

<section class="max-w-7xl mx-auto px-4 py-6 lg:py-10">

  <div class="flex items-center justify-between mb-10">

    <h2 class="text-3xl font-bold">
      Produk Unggulan
    </h2>

    <a href="<?= BASE_URL ?>/src/views/public/shop.php" class="text-emerald-500 font-medium">
      Lihat Semua
    </a>

  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

  <?php while($product = mysqli_fetch_assoc($products)): ?>

    <?php
      $imgPath = __DIR__ . '/../../uploads/products/' . ($product['image'] ?? '');
      $imgSrc = (isset($product['image']) && file_exists($imgPath))
        ? UPLOAD_URL . '/products/' . $product['image']
        : 'https://placehold.co/600x400';

      $productName = $product['name'];
      $price = 'Rp ' . number_format($product['price']);
      $image = $imgSrc;
      $seller = htmlspecialchars($product['seller_name'] ?? 'UMKM Indonesia');
      $id = $product['id'];
    ?>

    <?php include '../components/product-card.php'; ?>

  <?php endwhile; ?>

  </div>

</section>
<?php if (!isset($_SESSION['user'])): ?>
<section class="bg-white border-t">

  <div class="max-w-7xl mx-auto px-4 py-20 text-center">

    <h2 class="text-3xl lg:text-4xl font-bold mb-6">

      Bergabung Bersama UMKM Marketplace

    </h2>

    <p class="text-gray-600 text-lg mb-10 max-w-2xl mx-auto">

      Jadilah bagian dari marketplace modern yang mendukung
      pertumbuhan UMKM lokal Indonesia.

    </p>

    <a href="<?= BASE_URL ?>/src/views/public/register.php" class="bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-4 rounded-2xl transition inline-block">

      Daftar Sekarang

    </a>

  </div>

</section>
<?php else: ?>
<?php endif; ?>

<?php include '../layouts/footer.php'; ?>

</main>
</body>
</html>