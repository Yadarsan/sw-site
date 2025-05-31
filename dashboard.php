<?php
// Check admin access
if (!app()->getAuthSession()->isAdmin()) {
    header("Location: " . url('login'));
    exit;
}

// Set page title
$pageTitle = 'Admin Dashboard';

// Include header
include 'includes/header.php';

// Get statistics
$db = app()->getDB();

// Total sales
$salesStmt = $db->query("SELECT SUM(total_price) as total FROM orders WHERE status IN ('Completed', 'Shipped', 'Processing')");
$totalSales = $salesStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Today's sales
$todaySalesStmt = $db->query("SELECT SUM(total_price) as total FROM orders WHERE DATE(order_date) = CURDATE() AND status != 'Cancelled'");
$todaySales = $todaySalesStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Order counts
$orderStats = $db->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'Processing' THEN 1 ELSE 0 END) as processing_orders,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_orders
    FROM orders
")->fetch(PDO::FETCH_ASSOC);

// Customer count
$customerCount = $db->query("SELECT COUNT(*) as total FROM customers")->fetch(PDO::FETCH_ASSOC)['total'];

// Product count
$productCount = $db->query("SELECT COUNT(*) as total FROM products")->fetch(PDO::FETCH_ASSOC)['total'];

// Low stock products
$lowStockCount = $db->query("SELECT COUNT(*) as total FROM products WHERE stock <= 5 AND stock > 0")->fetch(PDO::FETCH_ASSOC)['total'];
$outOfStockCount = $db->query("SELECT COUNT(*) as total FROM products WHERE stock = 0")->fetch(PDO::FETCH_ASSOC)['total'];

// Recent orders
$recentOrders = $db->query("
    SELECT o.*, c.name as customer_name 
    FROM orders o 
    JOIN customers c ON o.user_id = c.user_id 
    ORDER BY o.order_date DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Sales chart data (last 7 days)
$salesChartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $db->prepare("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE DATE(order_date) = ? AND status != 'Cancelled'");
    $stmt->execute([$date]);
    $salesChartData[] = [
        'date' => date('M d', strtotime($date)),
        'sales' => $stmt->fetch(PDO::FETCH_ASSOC)['total']
    ];
}

// Top selling products
$topProducts = $db->query("
    SELECT p.name, p.price, SUM(oi.quantity) as total_sold
    FROM products p
    JOIN order_items oi ON p.product_id = oi.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Admin Dashboard -->
<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars(app()->getAuthSession()->getCurrentUser()['name']); ?>!</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Sales -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($totalSales, 2); ?></p>
                        <p class="text-green-600 text-sm mt-1">
                            <i class="fas fa-arrow-up"></i> +12% from last month
                        </p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Today's Sales -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($todaySales, 2); ?></p>
                        <p class="text-blue-600 text-sm mt-1">
                            <?php echo date('F j, Y'); ?>
                        </p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($orderStats['total_orders']); ?></p>
                        <p class="text-orange-600 text-sm mt-1">
                            <?php echo $orderStats['pending_orders']; ?> pending
                        </p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <i class="fas fa-shopping-cart text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Customers -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($customerCount); ?></p>
                        <p class="text-green-600 text-sm mt-1">
                            <i class="fas fa-arrow-up"></i> +5 this week
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <i class="fas fa-users text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Sales Chart -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">Sales Overview</h2>
                        <select class="px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>Last 3 months</option>
                            <option>Last year</option>
                        </select>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-xl shadow-sm p-6 mt-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">Recent Orders</h2>
                        <a href="<?php echo url('admin/orders'); ?>" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-gray-500 text-sm border-b">
                                    <th class="pb-3">Order ID</th>
                                    <th class="pb-3">Customer</th>
                                    <th class="pb-3">Amount</th>
                                    <th class="pb-3">Status</th>
                                    <th class="pb-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <?php foreach($recentOrders as $order): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3">
                                        <a href="<?php echo url('admin/orders/' . $order['order_id']); ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                                            #<?php echo str_pad($order['order_id'], 5, '0', STR_PAD_LEFT); ?>
                                        </a>
                                    </td>
                                    <td class="py-3"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td class="py-3 font-semibold">$<?php echo number_format($order['total_price'], 2); ?></td>
                                    <td class="py-3">
                                        <?php 
                                        $statusColors = [
                                            'Pending' => 'bg-yellow-100 text-yellow-800',
                                            'Processing' => 'bg-blue-100 text-blue-800',
                                            'Shipped' => 'bg-purple-100 text-purple-800',
                                            'Delivered' => 'bg-green-100 text-green-800',
                                            'Cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusClass = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($order['order_date'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="<?php echo url('admin/products/add'); ?>" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <span class="flex items-center">
                                <i class="fas fa-plus-circle text-primary-600 mr-3"></i>
                                Add New Product
                            </span>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        <a href="<?php echo url('admin/orders?status=pending'); ?>" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <span class="flex items-center">
                                <i class="fas fa-clock text-yellow-600 mr-3"></i>
                                Pending Orders
                                <?php if($orderStats['pending_orders'] > 0): ?>
                                <span class="ml-2 bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                                    <?php echo $orderStats['pending_orders']; ?>
                                </span>
                                <?php endif; ?>
                            </span>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        <a href="<?php echo url('admin/inventory?filter=low'); ?>" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <span class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                                Low Stock Items
                                <?php if($lowStockCount > 0): ?>
                                <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                    <?php echo $lowStockCount; ?>
                                </span>
                                <?php endif; ?>
                            </span>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                        <a href="<?php echo url('admin/reports'); ?>" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <span class="flex items-center">
                                <i class="fas fa-chart-bar text-green-600 mr-3"></i>
                                Generate Reports
                            </span>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                    </div>
                </div>

                <!-- Inventory Status -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Inventory Status</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Total Products</span>
                                <span class="font-semibold"><?php echo $productCount; ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-600 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Low Stock</span>
                                <span class="font-semibold text-yellow-600"><?php echo $lowStockCount; ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: <?php echo ($productCount > 0) ? ($lowStockCount / $productCount * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Out of Stock</span>
                                <span class="font-semibold text-red-600"><?php echo $outOfStockCount; ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: <?php echo ($productCount > 0) ? ($outOfStockCount / $productCount * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Top Selling Products</h2>
                    <div class="space-y-3">
                        <?php foreach($topProducts as $index => $product): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-semibold mr-3">
                                    <?php echo $index + 1; ?>
                                </span>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $product['total_sold']; ?> sold</p>
                                </div>
                            </div>
                            <span class="font-semibold text-gray-800">$<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($salesChartData, 'date')); ?>,
        datasets: [{
            label: 'Sales',
            data: <?php echo json_encode(array_column($salesChartData, 'sales')); ?>,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
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
                        return '$' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});

// Real-time updates simulation
setInterval(() => {
    // Update time-based elements
    document.querySelectorAll('[data-live-time]').forEach(el => {
        el.textContent = new Date().toLocaleTimeString();
    });
}, 1000);
</script>

<?php include 'includes/footer.php'; ?>