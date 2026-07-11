<?php require_once '../../middleware/guest.php'; ?>
<?php include '../layouts/auth.php'; ?>

<div class="w-full max-w-lg px-4">

  <div class="bg-white rounded-3xl shadow-sm p-6 lg:p-8 mt-5 mb-5">

    <!-- Header -->
    <div class="text-center mb-8">

      <img src="<?= BASE_URL ?>/src/assets/images/logo.png" class="w-16 h-16 lg:w-20 lg:h-20 rounded-2xl mx-auto mb-4"></img>
     
      <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">
        Buat Akun Baru
      </h1>

      <p class="text-gray-500 mt-2">
        Daftar sebagai buyer atau seller
      </p>

    </div>

    <!-- Form -->
    <form action="<?= BASE_URL ?>/src/auth/register.php" method="POST">

      <!-- Nama -->
      <?php
        $label = 'Nama Lengkap';
        $name = 'name';
        $type = 'text';
        $placeholder = 'Masukkan nama lengkap';

        include '../components/input.php';
      ?>

      <!-- Email -->
      <?php
        $label = 'Email';
        $name = 'email';
        $type = 'email';
        $placeholder = 'Masukkan email';

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

      <!-- Role -->
      <div class="mb-6">

        <label class="block mb-2 font-medium text-gray-700">

          Pilih Role

        </label>

        <select
          name="role"
          class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >

        <option value="buyer">Buyer</option>
        <option value="seller">Seller</option>

        </select>

      </div>

      <!-- Button -->
      <button
        class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl font-medium transition"
      >
        Register
      </button>

    </form>

    <!-- Login -->
    <p class="text-center text-gray-600 mt-8">

      Sudah punya akun?

      <a href="<?= BASE_URL ?>/src/views/public/login.php" class="text-emerald-500 font-medium">
        Login
      </a>

    </p>

  </div>

</div>

</div>
</body>
</html>