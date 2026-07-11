<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$q = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE 1=1";

if ($q !== '') {
    $qEsc = mysqli_real_escape_string($conn, $q);

    $where .= "
        AND (
            name LIKE '%$qEsc%'
            OR email LIKE '%$qEsc%'
        )
    ";
}

$allowedRoles = ['buyer', 'seller', 'admin'];

if (in_array($role, $allowedRoles)) {
    $where .= " AND role='$role'";
}

$allowedStatus = ['active', 'suspended'];

if (in_array($status, $allowedStatus)) {
    $where .= " AND status='$status'";
}

$perPage = 10;

$page = max(1, intval($_GET['page'] ?? 1));

$offset = ($page - 1) * $perPage;

$countQuery = "
SELECT COUNT(*) AS total
FROM users
$where
";

$totalUsers = mysqli_fetch_assoc(
    mysqli_query($conn, $countQuery)
)['total'];

$totalPages = ceil($totalUsers / $perPage);

$query = "
SELECT *
FROM users
$where
ORDER BY created_at DESC
LIMIT $perPage OFFSET $offset
";

$users = mysqli_query($conn, $query);

function getRoleClasses($role) {

    $styles = [
        'buyer' => ['bg-blue-100', 'text-blue-700'],
        'seller' => ['bg-emerald-100', 'text-emerald-700'],
        'admin' => ['bg-purple-100', 'text-purple-700'],
    ];

    return $styles[$role] ?? ['bg-gray-100', 'text-gray-700'];
}

function getStatusClasses($status) {

    $styles = [
        'active' => ['bg-green-100', 'text-green-700'],
        'suspended' => ['bg-red-100', 'text-red-700'],
    ];

    return $styles[$status] ?? ['bg-gray-100', 'text-gray-700'];
}

?>

<div class="flex bg-gray-100 min-h-screen overflow-hidden">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main -->
  <main class="flex-1 min-w-0 p-4 lg:p-10 overflow-x-hidden">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10">

      <div>

        <h1 class="text-3xl lg:text-4xl font-bold mb-3">

          Kelola Users

        </h1>

        <p class="text-gray-600">

          Kelola akun buyer dan seller marketplace.

        </p>
        
        <?php if (isset($_GET['updated'])): ?>

          <div class="mt-6 rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-700">

            Status user berhasil diperbarui.

          </div>

        <?php elseif (isset($_GET['self_error'])): ?>

          <div class="mt-6 rounded-3xl bg-red-50 border border-red-200 p-6 text-red-700">

            Anda tidak bisa mengubah status akun sendiri.

          </div>

        <?php endif; ?>

      </div>

    </div>
        <!-- Filter -->
    <div class="bg-white rounded-3xl shadow-sm p-6 mb-10">
      <form method="GET" action="users.php">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

          <!-- Search -->
          <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($q); ?>"
            placeholder="Cari user..."
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

          <!-- Role -->
          <select
            name="role"
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

            <option value="">Semua Role</option>
            <option value="buyer" <?= $role === 'buyer' ? 'selected' : '' ?>>Buyer</option>
            <option value="seller" <?= $role === 'seller' ? 'selected' : '' ?>>Seller</option>
            <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>

          </select>

          <!-- Status -->
          <select
            name="status"
            class="border border-gray-300 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >

            <option value="">Semua Status</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>

          </select>

          <button
            type="submit"
            class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl px-4 py-3 transition"
          >
            Terapkan
          </button>

        </div>
      </form>
    </div>
        <!-- User Table -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <div class="overflow-x-auto">

        <table class="w-full min-w-[900px]">

          <!-- Head -->
          <thead class="bg-gray-50">

            <tr>

              <th class="text-left px-6 py-5">
                User
              </th>

              <th class="text-left px-6 py-5">
                Role
              </th>

              <th class="text-left px-6 py-5">
                Bergabung
              </th>

              <th class="text-left px-6 py-5">
                Status
              </th>

              <th class="text-left px-6 py-5">
                Aksi
              </th>

            </tr>

          </thead>
        <tbody>
        
        <?php if (mysqli_num_rows($users) > 0): ?>

        <?php while($user = mysqli_fetch_assoc($users)): ?>

        <?php [$roleBg, $roleText] = getRoleClasses($user['role']); ?>
        <?php [$statusBg, $statusText] = getStatusClasses($user['status']); ?>

        <tr class="border-t hover:bg-gray-50 transition">

          <!-- User -->
          <td class="px-6 py-5">

            <div class="flex items-center gap-4">

              <?php
              $userAvatar = (!empty($user['profile_image']) &&
                  file_exists(__DIR__ . '/../../uploads/sellers/' . $user['profile_image']))
                  ? UPLOAD_URL . '/sellers/' . $user['profile_image']
                  : 'https://placehold.co/100';
              ?>

              <img
                src="<?= $userAvatar; ?>"
                class="w-14 h-14 rounded-2xl object-cover border"
              >

              <div>

                <h3 class="font-bold">

                  <?= htmlspecialchars($user['name']); ?>

                </h3>

                <p class="text-sm text-gray-500">

                  <?= htmlspecialchars($user['email']); ?>

                </p>

              </div>

            </div>

          </td>

          <!-- Role -->
          <td class="px-6 py-5">

            <span class="<?= $roleBg ?> <?= $roleText ?> px-4 py-2 rounded-full text-sm">

              <?= ucfirst($user['role']); ?>

            </span>

          </td>

          <!-- Join -->
          <td class="px-6 py-5">

            <?= date('d M Y', strtotime($user['created_at'])); ?>

          </td>

          <!-- Status -->
          <td class="px-6 py-5">

            <span class="<?= $statusBg ?> <?= $statusText ?> px-4 py-2 rounded-full text-sm">

              <?= ucfirst($user['status']); ?>

            </span>

          </td>

          <!-- Action -->
          <td class="px-6 py-5">

            <?php if ($user['role'] === 'admin'): ?>

              <span class="text-sm text-gray-400">
                Tidak ada aksi
              </span>

            <?php else: ?>

              <div class="flex flex-wrap gap-3">

                <?php if ($user['status'] === 'active'): ?>

                  <a
                    href="<?= BASE_URL ?>/src/admin/toggle-user-status.php?id=<?= $user['id']; ?>&status=suspended"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl transition"
                  >
                    Suspend
                  </a>

                <?php else: ?>

                  <a
                    href="<?= BASE_URL ?>/src/admin/toggle-user-status.php?id=<?= $user['id']; ?>&status=active"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl transition"
                  >
                    Activate
                  </a>

                <?php endif; ?>

              </div>

            <?php endif; ?>

          </td>

        </tr>

        <?php endwhile; ?>

        <?php else: ?>

        <tr>

          <td colspan="5" class="px-6 py-16 text-center text-gray-500">

            User tidak ditemukan.

          </td>

        </tr>

        <?php endif; ?>

        </tbody>

        </table>

      </div>

    </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>

          <div class="flex items-center justify-center gap-3 mt-10 flex-wrap">

            <!-- Prev -->
            <?php if ($page > 1): ?>

              <a
                href="?page=<?= $page - 1; ?>&q=<?= urlencode($q); ?>&role=<?= urlencode($role); ?>&status=<?= urlencode($status); ?>"
                class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
              >

                ←

              </a>

            <?php endif; ?>

            <!-- Numbers -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>

              <a
                href="?page=<?= $i; ?>&q=<?= urlencode($q); ?>&role=<?= urlencode($role); ?>&status=<?= urlencode($status); ?>"
                class="w-12 h-12 rounded-2xl transition flex items-center justify-center
                <?= $i == $page
                    ? 'bg-emerald-500 text-white'
                    : 'border hover:bg-gray-100'
                ?>"
              >

                <?= $i; ?>

              </a>

            <?php endfor; ?>

            <!-- Next -->
            <?php if ($page < $totalPages): ?>

              <a
                href="?page=<?= $page + 1; ?>&q=<?= urlencode($q); ?>&role=<?= urlencode($role); ?>&status=<?= urlencode($status); ?>"
                class="w-12 h-12 border rounded-2xl hover:bg-gray-100 transition flex items-center justify-center"
              >

                →

              </a>

            <?php endif; ?>

          </div>

          <?php endif; ?>
      </main>

</div>

</body>
</html>