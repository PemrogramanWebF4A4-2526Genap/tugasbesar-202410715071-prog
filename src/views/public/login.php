<?php require_once '../../middleware/guest.php'; ?>
<?php include '../layouts/auth.php'; ?>

<div class="w-full max-w-md px-4">

  <!-- Card -->
  <div class="bg-white rounded-3xl shadow-sm p-6 lg:p-8">

    <!-- Logo -->
    <div class="text-center mb-8">

      <img src="<?= BASE_URL ?>/src/assets/images/logo.png" class="w-16 h-16 lg:w-20 lg:h-20 rounded-2xl mx-auto mb-4"></img>

      <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">
        Selamat Datang
      </h1>

      <p class="text-gray-500 mt-2">
        Login ke akun UMKM Marketplace
      </p>

    </div>


    <?php if (isset($_SESSION['error'])): ?>

      <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700">

        <?= $_SESSION['error']; ?>

      </div>

      <?php unset($_SESSION['error']); ?>

    <?php endif; ?>

    <!-- Form -->
    <form action="<?= BASE_URL ?>/src/auth/login.php" method="POST">

      <!-- Email -->
      <?php
        $label = 'Email';
        $name = 'email';
        $type = 'email';
        $placeholder = 'Masukkan email';
        $value = $_COOKIE['remember_email'] ?? '';

        include '../components/input.php';
      ?>

      <!-- Password -->
      <?php
        $label = 'Password';
        $name = 'password';
        $type = 'password';
        $placeholder = 'Masukkan password';

        include '../components/input.php';
      ?>

      <!-- Remember -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">

        <label class="flex items-center gap-2 text-sm text-gray-600">

          <input
            type="checkbox"
            name="remember"
            class="w-4 h-4"
            <?= isset($_COOKIE['remember_email']) ? 'checked' : '' ?>
          >
          Remember me

        </label>

      </div>

      <!-- Button -->
      <button
        class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl font-medium transition"
      >
        Login
      </button>

    </form>

    <!-- Register -->
    <p class="text-center text-gray-600 mt-8">

      Belum punya akun?

      <a href="<?= BASE_URL ?>/src/views/public/register.php" class="text-emerald-500 font-medium">
        Register
      </a>

    </p>

</div>
</body>
</html>
    </div>

  

  </main>
  </body>
  </html>