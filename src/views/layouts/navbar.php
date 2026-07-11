<nav class="bg-white shadow-sm sticky top-0 z-50">

  <div class="max-w-7xl mx-auto px-4">

    <div class="flex items-center justify-between h-16">

      <!-- Logo -->
      <div class="flex items-center gap-2">

        <img
          src="<?= BASE_URL ?>/src/assets/images/logo.png"
          class="w-10 h-10 rounded-lg object-cover">

        <h1 class="text-xl font-bold text-emerald-500">
          UMKM Marketplace
        </h1>

      </div>

      <!-- Menu -->
      <div class="hidden lg:flex items-center gap-4 bg-white">

        <a href="<?= BASE_URL ?>/src/views/public/home.php" class="text-gray-700 hover:text-emerald-500 transition">Home</a>

        <a href="<?= BASE_URL ?>/src/views/public/shop.php" class="text-gray-700 hover:text-emerald-500 transition">Produk</a>

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
          <a href="<?= BASE_URL ?>/src/views/admin/dashboard.php" class="text-gray-700 hover:text-emerald-500 transition">Admin Panel</a>
        <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'seller'): ?>
          <a href="<?= BASE_URL ?>/src/views/seller/dashboard.php" class="text-gray-700 hover:text-emerald-500 transition">Seller</a>
        <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'buyer'): ?>
          <a href="<?= BASE_URL ?>/src/views/buyer/cart.php" class="relative text-gray-700 hover:text-emerald-500 transition">
            Cart
          </a>
          <a href="<?= BASE_URL ?>/src/views/buyer/orders.php" class="text-gray-700 hover:text-emerald-500 transition">
            Pesanan
          </a>
          <a href="<?= BASE_URL ?>/src/views/profile.php" class="text-gray-700 hover:text-emerald-500 transition">
            Profil
          </a>
        <?php else: ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['user'])): ?>

          <?php
          $userAvatar = (!empty($_SESSION['user']['profile_image']) &&
              file_exists(__DIR__ . '/../../uploads/sellers/' . $_SESSION['user']['profile_image']))
              ? UPLOAD_URL . '/sellers/' . $_SESSION['user']['profile_image']
              : 'https://placehold.co/100';
          $notifCount = 0;

          if (isset($_SESSION['user'])) {

              $uid = intval($_SESSION['user']['id']);

              $notifRes = mysqli_query(
                  $conn,
                  "
                  SELECT COUNT(*) AS total

                  FROM notifications

                  WHERE user_id='$uid'
                  AND is_read=0
                  "
              );

              if ($notifRes) {

                  $notifCount = mysqli_fetch_assoc($notifRes)['total'];

              }

          }
          ?>

          <a
            href="<?= BASE_URL ?>/src/views/profile.php"
            class="flex items-center gap-3 hover:bg-gray-100 px-3 py-2 rounded-2xl transition"
          >

            <img
              src="<?= $userAvatar; ?>"
              class="w-10 h-10 rounded-xl object-cover border"
            >

            <div class="hidden lg:block">

              <p class="font-semibold text-gray-700 leading-tight">

                <?= htmlspecialchars($_SESSION['user']['name']); ?>

              </p>

              <p class="text-xs text-gray-500">

                <?= ucfirst($_SESSION['user']['role']); ?>

              </p>

            </div>

          </a>
          <a
            href="<?= BASE_URL ?>/src/views/notifications.php"
            class="relative flex items-center justify-center w-11 h-11 rounded-xl hover:bg-gray-100 transition"
          >

            🔔

            <?php if ($notifCount > 0): ?>

              <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center">

                <?= $notifCount; ?>

              </span>

            <?php endif; ?>

          </a>

          <a
            href="<?= BASE_URL ?>/src/auth/logout.php"
            class="text-red-500 hover:text-red-600 transition"
          >
            Logout
          </a>

        <?php else: ?>

          <a
            href="<?= BASE_URL ?>/src/views/public/login.php"
            class="text-gray-700 hover:text-emerald-500 transition"
          >
            Login
          </a>

          <a
            href="<?= BASE_URL ?>/src/views/public/register.php"
            class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl transition"
          >
            Register
          </a>

        <?php endif; ?>
      </div>
      
        <!-- Mobile Button -->
        <div class="flex items-center gap-2 lg:hidden">

  <?php if (isset($_SESSION['user'])): ?>

    <a
      href="<?= BASE_URL ?>/src/views/notifications.php"
      class="relative flex items-center justify-center w-11 h-11 rounded-xl hover:bg-gray-100 transition"
    >

      🔔

      <?php if ($notifCount > 0): ?>

        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs min-w-[20px] h-5 px-1 rounded-full flex items-center justify-center">

          <?= $notifCount; ?>

        </span>

      <?php endif; ?>

    </a>

  <?php endif; ?>

  <!-- Mobile Button -->
  <button
    id="mobileMenuButton"
    class="flex items-center justify-center w-11 h-11 rounded-xl hover:bg-gray-100 transition"
  >

    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">

      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>

    </svg>

  </button>

</div>

    </div>

  </div>

  <!-- Mobile Menu -->
<div
  id="mobileMenu"
  class="hidden lg:hidden border-t bg-white"
>

    <div class="px-4 py-6 space-y-4">

      <a
        href="<?= BASE_URL ?>/src/views/public/home.php"
        class="block py-3 px-4 rounded-2xl hover:bg-gray-100 transition"
      >
        Home
      </a>

      <a
        href="<?= BASE_URL ?>/src/views/public/shop.php"
        class="block py-3 px-4 rounded-2xl hover:bg-gray-100 transition"
      >
        Produk
      </a>

      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>

        <a
          href="<?= BASE_URL ?>/src/views/admin/dashboard.php"
          class="block py-3 px-4 rounded-2xl hover:bg-gray-100 transition"
        >
          Admin Panel
        </a>

      <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'seller'): ?>

        <a
          href="<?= BASE_URL ?>/src/views/seller/dashboard.php"
          class="block py-3 px-4 rounded-2xl hover:bg-gray-100 transition"
        >
          Seller Dashboard
        </a>

      <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'buyer'): ?>

        <a
          href="<?= BASE_URL ?>/src/views/buyer/cart.php"
          class="block py-3 px-4 rounded-2xl hover:bg-gray-100 transition"
        >
          Cart
        </a>

        <a
          href="<?= BASE_URL ?>/src/views/buyer/orders.php"
          class="block py-3 px-4 rounded-2xl hover:bg-gray-100 transition"
        >
          Pesanan
        </a>

      <?php endif; ?>

      <?php if (isset($_SESSION['user'])): ?>

        <div class="border-t pt-4">

          <div class="flex items-center gap-4 mb-4">

            <img
              src="<?= $userAvatar; ?>"
              class="w-12 h-12 rounded-2xl object-cover border"
            >

            <div>

              <h3 class="font-bold">

                <?= htmlspecialchars($_SESSION['user']['name']); ?>

              </h3>

              <p class="text-sm text-gray-500">

                <?= ucfirst($_SESSION['user']['role']); ?>

              </p>

            </div>

          </div>

          <a
            href="<?= BASE_URL ?>/src/views/profile.php"
            class="block py-3 px-4 rounded-2xl hover:bg-gray-100 transition mb-2"
          >
            Profil
          </a>

          <a
            href="<?= BASE_URL ?>/src/auth/logout.php"
            class="block py-3 px-4 rounded-2xl bg-red-50 text-red-500 hover:bg-red-100 transition"
          >
            Logout
          </a>

        </div>

      <?php else: ?>

        <div class="border-t pt-4 space-y-3">

          <a
            href="<?= BASE_URL ?>/src/views/public/login.php"
            class="block py-3 px-4 rounded-2xl border hover:bg-gray-100 transition text-center"
          >
            Login
          </a>

          <a
            href="<?= BASE_URL ?>/src/views/public/register.php"
            class="block py-3 px-4 rounded-2xl bg-emerald-500 text-white hover:bg-emerald-600 transition text-center"
          >
            Register
          </a>

        </div>

      <?php endif; ?>

    </div>

  </div>
</nav>
<script>

const mobileMenuButton = document.getElementById('mobileMenuButton');
const mobileMenu = document.getElementById('mobileMenu');

if (mobileMenuButton && mobileMenu) {

  mobileMenuButton.addEventListener('click', () => {

    mobileMenu.classList.toggle('hidden');

  });

}

</script>