<?php

$productName = $productName ?? 'Produk UMKM';
$price = $price ?? 'Rp 100.000';
$image = $image ?? 'https://placehold.co/400';
$seller = $seller ?? 'UMKM Indonesia';
$id = $id ?? null;
$category = $product['category_name'] ?? 'UMKM';

?>

<div class="bg-white rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 transition overflow-hidden">

  <!-- Product Image -->
  <div class="relative">

    <!-- Badge Category -->
    <span class="absolute top-4 left-4 bg-white/90 backdrop-blur text-emerald-600 text-xs font-semibold px-3 py-1 rounded-full shadow-sm z-10">

      <?= htmlspecialchars($category) ?>

    </span>

    <img
      src="<?= $image ?>"
      alt="<?= $productName ?>"
      class="w-full h-56 object-cover"
    >

  </div>

  <!-- Product Content -->
  <div class="p-5">

    <h3 class="font-semibold text-lg mb-2 line-clamp-2">

      <?= $productName ?>

    </h3>

    <p class="text-emerald-500 font-bold text-xl mb-3">

      <?= $price ?>

    </p>

    <p class="text-gray-500 text-sm mb-5">

      <?= $seller ?>

    </p>

    <a
      href="<?= BASE_URL ?>/src/views/public/product-detail.php?id=<?= $id ? intval($id) : ''; ?>"
      class="block text-center w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3 rounded-xl transition"
    >
      Lihat Detail
    </a>

  </div>

</div>