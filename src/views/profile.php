<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../views/public/login.php');
    exit;
}

$userId = intval($_SESSION['user']['id']);
$userQuery = mysqli_query($conn, "SELECT id, name, email, role, status, created_at, profile_image FROM users WHERE id='$userId' LIMIT 1");
$user = mysqli_fetch_assoc($userQuery);
if (!$user) {
    header('Location: ../views/public/login.php');
    exit;
}
?>
<?php include 'layouts/app.php'; ?>

<div class="flex min-h-screen bg-gray-100 overflow-hidden">
<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>

    <?php include 'admin/sidebar.php'; ?>

  <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'seller'): ?>

    <?php include 'seller/sidebar.php'; ?>

  <?php endif; ?>



<main class="flex-1 min-w-0 overflow-x-hidden p-4 lg:p-8">
<section class="max-w-4xl mx-auto px-4">
      
  <div class="bg-white rounded-3xl shadow-sm p-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl lg:text-4xl font-bold">Profil Saya</h1>
        <p class="text-gray-600">Kelola informasi akun dan ubah kata sandi Anda.</p>
      </div>
      <div class="text-right">
        <p class="text-sm text-gray-500">Role: <span class="font-semibold"><?= htmlspecialchars($user['role']); ?></span></p>
        <p class="text-sm text-gray-500">Status: <span class="font-semibold"><?= htmlspecialchars($user['status']); ?></span></p>
      </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
      <div class="mb-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">
        Profil berhasil diperbarui.
      </div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="mb-6 rounded-3xl bg-red-50 border border-red-200 p-6 text-red-700">
        Terjadi kesalahan saat memperbarui profil. Pastikan email belum dipakai pengguna lain.
      </div>
        <?php endif; ?>

        <form action="../user/update-profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
          <?php
          $profileImage = (!empty($user['profile_image']) &&
              file_exists(__DIR__ . '/../uploads/sellers/' . $user['profile_image']))
              ? UPLOAD_URL . '/sellers/' . $user['profile_image']
              : 'https://placehold.co/200';
          ?>

          <div>

            <label class="block mb-4 font-semibold">

              Foto Profil

            </label>

            <div class="border-2 border-dashed border-gray-300 rounded-3xl p-6 hover:border-emerald-500 transition">

              <div class="flex flex-col lg:flex-row items-center gap-6">

                <img
                  id="profilePreview"
                  src="<?= $profileImage; ?>"
                  class="w-28 h-28 rounded-3xl object-cover border"
                >

                <div class="flex-1">

                  <h3 class="text-xl font-bold mb-3">

                    Upload Foto Profil

                  </h3>

                  <p class="text-gray-500 mb-6">

                    PNG, JPG, JPEG maksimal 5MB

                  </p>

                  <input
                    id="profileImageInput"
                    type="file"
                    name="profile_image"
                    accept="image/png,image/jpg,image/jpeg"
                    class="hidden"
                  >

                  <button
                    type="button"
                    id="selectProfileImage"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition"
                  >

                    Pilih Foto

                  </button>

                  <p id="profileFileName" class="text-sm text-gray-500 mt-4"></p>

                </div>

              </div>

            </div>

          </div>
      <div>
        <label class="block mb-2 font-semibold">Nama</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required class="w-full border border-gray-300 rounded-2xl px-4 py-3" />
      </div>
      <div>
        <label class="block mb-2 font-semibold">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required class="w-full border border-gray-300 rounded-2xl px-4 py-3" />
      </div>
      <div>
        <label class="block mb-2 font-semibold">Kata Sandi Baru</label>
        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengganti" class="w-full border border-gray-300 rounded-2xl px-4 py-3" />
      </div>
      <div>
        <label class="block mb-2 font-semibold">Konfirmasi Kata Sandi Baru</label>
        <input type="password" name="password_confirm" placeholder="Konfirmasi kata sandi baru" class="w-full border border-gray-300 rounded-2xl px-4 py-3" />
      </div>
      <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-2xl transition">Simpan Perubahan</button>
    </form>
  </div>
</section>
</main>

</div>
</main>
<script>

const profileImageInput = document.getElementById('profileImageInput');
const selectProfileImage = document.getElementById('selectProfileImage');
const profilePreview = document.getElementById('profilePreview');
const profileFileName = document.getElementById('profileFileName');

if (profileImageInput) {

  selectProfileImage.addEventListener('click', () => {
    profileImageInput.click();
  });

  profileImageInput.addEventListener('change', function(e) {

    const file = e.target.files[0];

    if (!file) return;

    profileFileName.textContent = file.name;

    const reader = new FileReader();

    reader.onload = function(event) {
      profilePreview.src = event.target.result;
    };

    reader.readAsDataURL(file);

  });

}

</script>
</body>
</html>
