<?php require_once '../../middleware/admin.php'; ?>
<?php require_once '../../config/database.php'; ?>
<?php include '../layouts/header.php'; ?>

<?php

$totalRevenue = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT COALESCE(SUM(total_amount),0) AS total
        FROM orders
        WHERE status IN ('paid','processed','shipped','completed')
        "
    )
)['total'];

$totalTransactions = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM payments"
    )
)['total'];

$totalOrders = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM orders"
    )
)['total'];

$totalCompleted = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT COUNT(*) AS total
        FROM orders
        WHERE status='completed'
        "
    )
)['total'];

$conversionRate = $totalOrders > 0
    ? round(($totalCompleted / $totalOrders) * 100)
    : 0;

// top sellers
$topSellers = mysqli_query(
    $conn,
    "
    SELECT
        users.name,
        COUNT(order_items.id) AS total_sales,
        COALESCE(SUM(order_items.subtotal),0) AS revenue

    FROM order_items

    JOIN users
    ON order_items.seller_id = users.id

    GROUP BY users.id

    ORDER BY revenue DESC

    LIMIT 5
    "
);

// top products
$topProducts = mysqli_query(
    $conn,
    "
    SELECT
        products.name,
        SUM(order_items.quantity) AS sold,
        COALESCE(SUM(order_items.subtotal),0) AS revenue

    FROM order_items

    JOIN products
    ON order_items.product_id = products.id

    GROUP BY products.id

    ORDER BY sold DESC

    LIMIT 5
    "
);

// latest transactions
$latestTransactions = mysqli_query(
    $conn,
    "
    SELECT
        orders.invoice_number,
        orders.total_amount,
        orders.status,
        users.name AS buyer_name,
        GROUP_CONCAT(DISTINCT sellers.name SEPARATOR ', ') AS seller_name

    FROM orders

    JOIN users
    ON orders.user_id = users.id

    LEFT JOIN order_items
    ON orders.id = order_items.order_id

    LEFT JOIN users sellers
    ON order_items.seller_id = sellers.id

    GROUP BY
        orders.id,
        orders.invoice_number,
        orders.total_amount,
        orders.status,
        users.name

    ORDER BY orders.created_at DESC

    LIMIT 10
    "
);

$range = $_GET['range'] ?? '7';

$days = 7;

if ($range === '30') {

    $days = 30;

} elseif ($range === '365') {

    $days = 365;

}

$salesChart = mysqli_query(
    $conn,
    "
    SELECT
        DATE(created_at) AS order_date,
        COALESCE(SUM(total_amount),0) AS revenue

    FROM orders

    WHERE status IN ('paid','processed','shipped','completed')
    AND created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)

    GROUP BY DATE(created_at)

    ORDER BY DATE(created_at) ASC
    "
);

$chartLabels = [];
$chartRevenue = [];

while ($chart = mysqli_fetch_assoc($salesChart)) {

    $chartLabels[] = date('d M', strtotime($chart['order_date']));
    $chartRevenue[] = (float)$chart['revenue'];

}

function getStatusClasses($status) {

    $styles = [
        'pending' => ['bg-yellow-100', 'text-yellow-700'],
        'paid' => ['bg-blue-100', 'text-blue-700'],
        'processed' => ['bg-indigo-100', 'text-indigo-700'],
        'shipped' => ['bg-purple-100', 'text-purple-700'],
        'completed' => ['bg-green-100', 'text-green-700'],
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

          Laporan & Analytics

        </h1>

        <p class="text-gray-600">

          Pantau performa marketplace dan statistik transaksi.

        </p>

      </div>

    </div>
        <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">

      <!-- Revenue -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Revenue

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-emerald-500">

          Rp <?= number_format($totalRevenue); ?>

        </h2>

      </div>

      <!-- Transactions -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Transaksi

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-blue-500">

          <?= number_format($totalTransactions); ?>

        </h2>

      </div>

      <!-- Orders -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Total Pesanan

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-yellow-500">

          <?= number_format($totalOrders); ?>

        </h2>

      </div>

      <!-- Conversion -->
      <div class="bg-white rounded-3xl shadow-sm p-6">

        <p class="text-gray-500 mb-3">

          Conversion Rate

        </p>

        <h2 class="text-3xl lg:text-4xl font-bold text-purple-500">

          <?= $conversionRate; ?>%

        </h2>

      </div>

    </div>
        <!-- Chart -->
    <div class="bg-white rounded-3xl shadow-sm p-8 mb-10">

      <div class="flex items-center justify-between mb-8">

        <h2 class="text-2xl font-bold">

          Statistik Penjualan

        </h2>

        <form method="GET">

          <select
            name="range"
            onchange="this.form.submit()"
            class="border border-gray-300 rounded-2xl px-4 py-2"
          >

            <option value="7" <?= $range === '7' ? 'selected' : ''; ?>>

              7 Hari

            </option>

            <option value="30" <?= $range === '30' ? 'selected' : ''; ?>>

              30 Hari

            </option>

            <option value="365" <?= $range === '365' ? 'selected' : ''; ?>>

              1 Tahun

            </option>

          </select>

        </form>

      </div>

      <!-- Chart -->
      <div class="h-72 lg:h-96">

        <canvas id="salesChart"></canvas>

      </div>

    </div>
        <!-- Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

      <!-- Top Seller -->
      <div class="bg-white rounded-3xl shadow-sm p-8">

        <h2 class="text-2xl font-bold mb-8">

          Top Seller

        </h2>

        <div class="space-y-6">

          <?php while($seller = mysqli_fetch_assoc($topSellers)): ?>

            <div class="flex items-center justify-between">

              <div>

                <h3 class="font-bold">

                  <?= htmlspecialchars($seller['name']); ?>

                </h3>

                <p class="text-gray-500 text-sm">

                  <?= number_format($seller['total_sales']); ?> Penjualan

                </p>

              </div>

              <span class="font-bold text-emerald-500">

                Rp <?= number_format($seller['revenue']); ?>

              </span>

            </div>

          <?php endwhile; ?>

        </div>

      </div>

      <!-- Top Product -->
      <div class="bg-white rounded-3xl shadow-sm p-8">

        <h2 class="text-2xl font-bold mb-8">

          Produk Terlaris

        </h2>

        <div class="space-y-6">

          <?php while($product = mysqli_fetch_assoc($topProducts)): ?>

            <div class="flex items-center justify-between">

              <div>

                <h3 class="font-bold">

                  <?= htmlspecialchars($product['name']); ?>

                </h3>

                <p class="text-gray-500 text-sm">

                  <?= number_format($product['sold']); ?> Terjual

                </p>

              </div>

              <span class="font-bold text-emerald-500">

                Rp <?= number_format($product['revenue']); ?>

              </span>

            </div>

          <?php endwhile; ?>

        </div>

      </div>

    </div>
        <!-- Transactions -->
    <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

      <div class="p-8 border-b">

        <h2 class="text-2xl font-bold">

          Transaksi Terbaru

        </h2>

      </div>

      <div class="overflow-x-auto">

        <table class="w-full min-w-[900px]">

          <thead class="bg-gray-50">

            <tr>

              <th class="text-left px-6 py-5">
                Invoice
              </th>

              <th class="text-left px-6 py-5">
                Customer
              </th>

              <th class="text-left px-6 py-5">
                Seller
              </th>

              <th class="text-left px-6 py-5">
                Total
              </th>

              <th class="text-left px-6 py-5">
                Status
              </th>

            </tr>

          </thead>

          <tbody>

            <?php while($trx = mysqli_fetch_assoc($latestTransactions)): ?>

              <?php [$bg, $text] = getStatusClasses($trx['status']); ?>

              <tr class="border-t hover:bg-gray-50 transition">

                <td class="px-6 py-5 font-bold">

                  <?= htmlspecialchars($trx['invoice_number']); ?>

                </td>

                <td class="px-6 py-5">

                  <?= htmlspecialchars($trx['buyer_name']); ?>

                </td>

                <td class="px-6 py-5">

                  <?= htmlspecialchars($trx['seller_name'] ?? '-'); ?>

                </td>

                <td class="px-6 py-5 font-bold text-emerald-500">

                  Rp <?= number_format($trx['total_amount']); ?>

                </td>

                <td class="px-6 py-5">

                  <span class="<?= $bg ?> <?= $text ?> px-4 py-2 rounded-full text-sm">

                    <?= ucfirst($trx['status']); ?>

                  </span>

                </td>

              </tr>

            <?php endwhile; ?>

            </tbody>
        </table>

      </div>

    </div>
      </main>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const salesCtx = document.getElementById('salesChart');

const chartLabels = <?= json_encode($chartLabels); ?>;
const chartRevenue = <?= json_encode($chartRevenue); ?>;

if (chartLabels.length === 0) {

    salesCtx.parentElement.innerHTML = `
        <div class="h-full flex items-center justify-center text-gray-500">
            Belum ada data penjualan.
        </div>
    `;

} else {
    const gradient = salesCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);

    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
    new Chart(salesCtx, {

        type: 'line',

        data: {

            labels: <?= json_encode($chartLabels); ?>,

            datasets: [{

                label: 'Revenue',

                data: <?= json_encode($chartRevenue); ?>,

                borderRadius: 16,

                borderSkipped: false,
                
                fill: true,

                backgroundColor: gradient,
               
                borderColor: '#10b981',
                
                tension: 0.4,

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {
                    display: false
                },

                tooltip: {

                    callbacks: {

                        label: function(context) {

                            return 'Rp ' + context.raw.toLocaleString('id-ID');

                        }

                    }

                }

            },

            scales: {

                y: {

                    beginAtZero: true

                }

            }

        }

    });
}
</script>
</body>
</html>