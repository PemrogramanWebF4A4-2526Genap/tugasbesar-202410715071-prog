<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Floating Toggle -->
<button
  id="sidebarToggle"
  class="
    lg:hidden
    fixed
    top-1/2
    left-4
    -translate-y-1/2
    z-40
    w-12
    h-12
    rounded-full
    bg-white
    shadow-xl
    border
    flex
    items-center
    justify-center
    text-gray-700
  "
>

  <svg
    xmlns="http://www.w3.org/2000/svg"
    class="w-6 h-6"
    fill="none"
    viewBox="0 0 24 24"
    stroke="currentColor"
  >

    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
      d="M9 5l7 7-7 7"
    />

  </svg>

</button>

<!-- Overlay -->
<div
  id="sidebarOverlay"
  class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"
></div>

<!-- Sidebar -->
<aside
  id="sidebar"
  class="
    fixed
    lg:static
    top-0
    left-0
    z-40
    w-72
    bg-white
    border-r
    min-h-screen
    p-6
    overflow-y-auto
    transition-transform
    duration-300
    -translate-x-full
    lg:translate-x-0
  "
>

  <!-- Logo -->
  <div class="mb-10">

    <h1 class="text-2xl font-bold text-emerald-500">

      Admin Panel

    </h1>

  </div>

  <!-- Menu -->
  <nav class="space-y-3">

    <a
      href="<?= BASE_URL ?>/src/views/admin/dashboard.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'dashboard.php'
      ? 'bg-emerald-500 text-white'
      : 'text-gray-700 hover:bg-gray-100'
    ?>"
    >
      Dashboard
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/admin/users.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'users.php'
        ? 'bg-emerald-500 text-white'
        : 'text-gray-700 hover:bg-gray-100'
      ?>"
    >
      Users
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/admin/products.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'products.php'
        ? 'bg-emerald-500 text-white'
        : 'text-gray-700 hover:bg-gray-100'
      ?>"
    >
      Products
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/admin/categories.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'categories.php'
        || $currentPage === 'edit-category.php'
        ? 'bg-emerald-500 text-white'
        : 'text-gray-700 hover:bg-gray-100'
      ?>"
    >
      Categories
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/admin/orders.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'orders.php'
        || $currentPage === 'order-detail.php'
        ? 'bg-emerald-500 text-white'
        : 'text-gray-700 hover:bg-gray-100'
      ?>"
    >
      Pesanan
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/admin/reports.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'reports.php'
        ? 'bg-emerald-500 text-white'
        : 'text-gray-700 hover:bg-gray-100'
      ?>"
    >
      Reports
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/admin/payments.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'payments.php'
        ? 'bg-emerald-500 text-white'
        : 'text-gray-700 hover:bg-gray-100'
      ?>"
    >
      Payments
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/profile.php"
      class="flex items-center gap-3 px-5 py-4 rounded-2xl transition
      <?= $currentPage === 'profile.php'
        ? 'bg-emerald-500 text-white'
        : 'text-gray-700 hover:bg-gray-100'
      ?>"
    >
      Profil
    </a>

    <a
      href="<?= BASE_URL ?>/src/views/public/home.php"
      class="flex items-center gap-3 text-emerald-500 hover:bg-gray-100 px-5 py-4 rounded-2xl transition"
    >
      Kembali ke Situs
    </a>

  </nav>

</aside>

<script>

const sidebar =
  document.getElementById('sidebar');

const sidebarToggle =
  document.getElementById('sidebarToggle');

const overlay =
  document.getElementById('sidebarOverlay');

sidebarToggle.addEventListener('click', () => {

  sidebar.classList.toggle('-translate-x-full');

  overlay.classList.toggle('hidden');

});

overlay.addEventListener('click', () => {

  sidebar.classList.add('-translate-x-full');

  overlay.classList.add('hidden');

});

</script>