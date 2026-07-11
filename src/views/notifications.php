<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {

    header('Location: public/login.php');

    exit;

}

$userId = intval($_SESSION['user']['id']);

mysqli_query(
    $conn,
    "
    UPDATE notifications

    SET is_read=1

    WHERE user_id='$userId'
    "
);

$notifications = mysqli_query(
    $conn,
    "
    SELECT *

    FROM notifications

    WHERE user_id='$userId'

    ORDER BY created_at DESC
    "
);

?>

<?php include __DIR__ . '/layouts/app.php'; ?>

<section class="max-w-4xl mx-auto px-4 py-10">

    <div class="flex items-center justify-between mb-10">

        <div>

            <h1 class="text-4xl font-bold mb-3">

                Notifikasi

            </h1>

            <p class="text-gray-600">

                Semua aktivitas dan update pesanan Anda.

            </p>

        </div>

    </div>

    <div class="space-y-4">

        <?php if (mysqli_num_rows($notifications) > 0): ?>

            <?php while($notif = mysqli_fetch_assoc($notifications)): ?>

                <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">

                    <div class="flex items-start justify-between gap-4">

                        <div>

                            <p class="text-gray-800 leading-relaxed">

                                <?= htmlspecialchars($notif['message']); ?>

                            </p>

                            <p class="text-sm text-gray-500 mt-3">

                                <?= date('d M Y H:i', strtotime($notif['created_at'])); ?>

                            </p>

                        </div>

                        <?php if (!$notif['is_read']): ?>

                            <span class="w-3 h-3 rounded-full bg-emerald-500 mt-2"></span>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="bg-white rounded-3xl shadow-sm p-16 text-center">

                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-emerald-100 flex items-center justify-center text-5xl">

                    🔔

                </div>

                <h2 class="text-2xl font-bold mb-3">

                    Belum ada notifikasi

                </h2>

                <p class="text-gray-500 max-w-md mx-auto leading-relaxed">

                    Semua update aktivitas akan muncul di sini.

                </p>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php include __DIR__ . '/layouts/footer.php'; ?>

</main>
</body>
</html>