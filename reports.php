<?php
// Check admin access
if (!app()->getAuthSession()->isAdmin()) {
    header("Location: " . url('login'));
    exit;
}

// Set page title
$pageTitle = 'Reports';

// Include header
include 'includes/header.php';

// Get report generator
$reportGenerator = app()->getReportGenerator();

// Handle report generation
$reportType = $_GET['type'] ?? 'sales';
$period = $_GET['period'] ?? 'monthly';
$format = $_GET['format'] ?? 'html';

// Generate report based on type
$reportData = null;
if ($reportType === 'sales') {
    $reportData = $reportGenerator->generateSalesReport($period);
} elseif ($reportType === 'inventory') {
    $reportData = $reportGenerator->generateInventoryReport();
}

// Handle export
if (isset($_GET['export']) && $reportData) {
    $exportFormat = $_GET['export'];
    $content = $reportGenerator->exportReport($reportData, $exportFormat);
    
    if ($exportFormat === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_' . date('Y-m-d') . '.csv"');
        echo $content;
        exit;
    }
}

// Get additional statistics for dashboard
$db = app()->getDB();

// Revenue by category
$categoryRevenue = $db->query("
    SELECT p.category, SUM(oi.quantity * oi.unit_price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status != 'Cancelled'
    GROUP BY p.category
    ORDER BY revenue DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Monthly revenue (last 12 months)
$monthlyRevenue = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(total_price), 0) as revenue
        FROM orders
        WHERE DATE_FORMAT(order_date, '%Y-%m') = ?
        AND status != 'Cancelled'
    ");
    $stmt->execute([$month]);
    $monthlyRevenue[] = [
        'month' => date('M Y', strtotime($month . '-01')),
        'revenue' => $stmt->fetch(PDO::FETCH_ASSOC)['revenue']
    ];
}
?>

<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
                <p class="text-gray-600 mt-2">Generate and export business reports</p>
            </div>
            <div class="flex space-x-3">
                <?php if($reportData): ?>
                <a href="?type=<?php echo $reportType; ?>&period=<?php echo $period; ?>&export=csv" 
                   class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>
                <button onclick="window.print()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition flex items-center">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Report Type Selector -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Report Type</label>
                    <div class="flex space-x-2">
                        <a href="?type=sales&period=<?php echo $period; ?>" 
                           class="flex-1 text-center px-4 py-3 rounded-lg <?php echo $reportType === 'sales' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition">
                            <i class="fas fa-chart-line mb-2 block text-xl"></i>
                            Sales Report
                        </a>
                        <a href="?type=inventory&period=<?php echo $period; ?>" 
                           class="flex-1 text-center px-4 py-3 rounded-lg <?php echo $reportType === 'inventory' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition">
                            <i class="fas fa-boxes mb-2 block text-xl"></i>
                            Inventory Report
                        </a>
                    </div>
                </div>
                
                <?php if($reportType === 'sales'): ?>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Time Period</label>
                    <select onchange="window.location.href='?type=<?php echo $reportType; ?>&period=' + this.value" 
                            class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="daily" <?php echo $period === 'daily' ? 'selected' : ''; ?>>Today</option>
                        <option value="weekly" <?php echo $period === 'weekly' ? 'selected' : ''; ?>>This Week</option>
                        <option value="monthly" <?php echo $period === 'monthly' ? 'selected' : ''; ?>>This Month</option>
                        <option value="yearly" <?php echo $period === 'yearly' ? 'selected' : ''; ?>>This Year</option>
                        <option value="all" <?php echo $period === 'all' ? 'selected' : ''; ?>>All Time</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <!-- Monthly Revenue Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-xl font-semibold mb-4">Revenue Trend (Last 12 Months)</h3>
                <canvas id="revenueChart" height="150"></canvas>
            </div>

            <!-- Category Revenue Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-xl font-semibold mb-4">Revenue by Category</h3>
                <canvas id="categoryChart" height="150"></canvas>
            </div>
        </div>

        <!-- Report Content -->
        <?php if($reportData): ?>
        <div class="bg-white rounded-lg shadow-sm p-6" id="reportContent">
            <?php echo $reportGenerator->exportReport($reportData, 'html'); ?>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="grid md:grid-cols-4 gap-6 mt-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Average Order Value</p>
                        <p class="text-2xl font-bold text-gray-800">
                            $<?php echo number_format($reportData['metrics']['average_order_value'] ?? 0, 2); ?>
                        </p>
                    </div>
                    <i class="fas fa-calculator text-3xl text-primary-600"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Conversion Rate</p>
                        <p class="text-2xl font-bold text-gray-800">3.2%</p>
                    </div>
                    <i class="fas fa-percentage text-3xl text-green-600"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Return Rate</p>
                        <p class="text-2xl font-bold text-gray-800">1.8%</p>
                    </div>
                    <i class="fas fa-undo text-3xl text-yellow-600"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Customer Lifetime Value</p>
                        <p class="text-2xl font-bold text-gray-800">$287</p>
                    </div>
                    <i class="fas fa-user-clock text-3xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Custom Report Builder -->
        <div class="bg-white rounded-lg shadow-sm p-6 mt-6">
            <h3 class="text-xl font-semibold mb-4">Custom Report Builder</h3>
            <form class="grid md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm mb-2">Date Range</label>
                    <input type="date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-2">To</label>
                    <input type="date" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-2">Report Type</label>
                    <select class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option>Sales by Product</option>
                        <option>Customer Analysis</option>
                        <option>Payment Methods</option>
                        <option>Shipping Analysis</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-2">&nbsp;</label>
                    <button type="submit" class="w-full bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                        Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Trend Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthlyRevenue, 'month')); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode(array_column($monthlyRevenue, 'revenue')); ?>,
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
                        return ' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Category Revenue Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($categoryRevenue, 'category')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($categoryRevenue, 'revenue')); ?>,
            backgroundColor: [
                '#3B82F6',
                '#10B981',
                '#F59E0B',
                '#EF4444',
                '#8B5CF6',
                '#EC4899'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = ' + context.parsed.toLocaleString();
                        const percentage = ((context.parsed / context.dataset.data.reduce((a, b) => a + b, 0)) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
</script>

<!-- Print Styles -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #reportContent, #reportContent * {
        visibility: visible;
    }
    #reportContent {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>

<?php include 'includes/footer.php'; ?>