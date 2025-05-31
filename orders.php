<?php
// Check if user is logged in BEFORE any output
if (!app()->getAuthSession()->isLoggedIn()) {
    header("Location: " . url('login'));
    exit;
}

// Set page title
$pageTitle = 'My Orders';

// Include header after all redirects
include 'includes/header.php';

// Get current user
$user = app()->getAuthSession()->getCurrentUser();
$userId = app()->getAuthSession()->getCurrentUserId();

// Get customer's orders
$customer = new Customer(app()->getDB());
$customer->loadById($userId);
$orders = $customer->viewOrderHistory();

// Group orders by status
$ordersByStatus = [
    'all' => $orders,
    'pending' => array_filter($orders, fn($o) => $o['status'] === 'Pending'),
    'processing' => array_filter($orders, fn($o) => $o['status'] === 'Processing'),
    'shipped' => array_filter($orders, fn($o) => $o['status'] === 'Shipped'),
    'delivered' => array_filter($orders, fn($o) => $o['status'] === 'Delivered'),
    'cancelled' => array_filter($orders, fn($o) => in_array($o['status'], ['Cancelled', 'Refunded']))
];

// Get active tab from query parameter
$activeTab = $_GET['status'] ?? 'all';
if (!isset($ordersByStatus[$activeTab])) {
    $activeTab = 'all';
}

$currentOrders = $ordersByStatus[$activeTab];
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">My Orders</h1>
                <nav class="flex items-center text-sm">
                    <a href="<?php echo url(); ?>" class="hover:text-primary-200">Home</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <a href="<?php echo url('account'); ?>" class="hover:text-primary-200">My Account</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <span>Orders</span>
                </nav>
            </div>
            <div class="hidden md:block">
                <p class="text-lg opacity-90">Total Orders: <?php echo count($orders); ?></p>
            </div>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-8">
    <!-- Order Status Tabs -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b">
            <nav class="flex overflow-x-auto">
                <?php foreach(['all' => 'All Orders', 'pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled/Refunded'] as $status => $label): ?>
                <a href="<?php echo url('orders?status=' . $status); ?>" 
                   class="px-6 py-4 text-sm font-medium whitespace-nowrap border-b-2 transition
                          <?php echo $activeTab === $status ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-600 hover:text-gray-800'; ?>">
                    <?php echo $label; ?>
                    <span class="ml-2 px-2 py-1 text-xs rounded-full <?php echo $activeTab === $status ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-600'; ?>">
                        <?php echo count($ordersByStatus[$status]); ?>
                    </span>
                </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <!-- Orders List -->
    <?php if (empty($currentOrders)): ?>
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">No orders found</h2>
            <p class="text-gray-500 mb-6">
                <?php echo $activeTab === 'all' ? "You haven't placed any orders yet." : "You don't have any " . str_replace('_', ' ', $activeTab) . " orders."; ?>
            </p>
            <a href="<?php echo url('products'); ?>" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition inline-flex items-center">
                <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($currentOrders as $order): ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Order Header -->
                <div class="bg-gray-50 px-6 py-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Order Number</p>
                                <p class="font-semibold">#<?php echo $order['order_id']; ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Date Placed</p>
                                <p class="font-semibold"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Amount</p>
                                <p class="font-semibold">$<?php echo number_format($order['total_price'], 2); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Status</p>
                                <?php
                                $statusClass = '';
                                $statusIcon = '';
                                switch($order['status']) {
                                    case 'Delivered':
                                        $statusClass = 'text-green-600';
                                        $statusIcon = 'fa-check-circle';
                                        break;
                                    case 'Shipped':
                                        $statusClass = 'text-blue-600';
                                        $statusIcon = 'fa-truck';
                                        break;
                                    case 'Processing':
                                        $statusClass = 'text-yellow-600';
                                        $statusIcon = 'fa-cog';
                                        break;
                                    case 'Cancelled':
                                    case 'Refunded':
                                        $statusClass = 'text-red-600';
                                        $statusIcon = 'fa-times-circle';
                                        break;
                                    default:
                                        $statusClass = 'text-gray-600';
                                        $statusIcon = 'fa-clock';
                                }
                                ?>
                                <p class="font-semibold <?php echo $statusClass; ?>">
                                    <i class="fas <?php echo $statusIcon; ?> mr-1"></i>
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo url('orders/' . $order['order_id']); ?>" 
                               class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition text-sm">
                                View Details
                            </a>
                            <?php if ($order['status'] === 'Delivered'): ?>
                            <button class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition text-sm">
                                <i class="fas fa-redo mr-1"></i> Reorder
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Items Preview -->
                <div class="p-6">
                    <?php
                    // Get order items
                    $orderDAO = new OrderDAO(app()->getDB());
                    $items = $orderDAO->getOrderItems($order['order_id']);
                    $itemCount = count($items);
                    $displayItems = array_slice($items, 0, 2); // Show first 2 items
                    ?>
                    
                    <div class="space-y-4">
                        <?php foreach ($displayItems as $item): ?>
                        <div class="flex items-center space-x-4">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 class="w-16 h-16 object-cover rounded-lg"
                                 onerror="this.src='https://via.placeholder.com/64x64?text=No+Image'">
                            <div class="flex-1">
                                <h4 class="font-medium"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="text-sm text-gray-600">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['unit_price'], 2); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if ($itemCount > 2): ?>
                        <p class="text-sm text-gray-500">
                            and <?php echo $itemCount - 2; ?> more item<?php echo $itemCount - 2 > 1 ? 's' : ''; ?>...
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Order Actions -->
                    <div class="mt-6 pt-6 border-t flex flex-wrap gap-3">
                        <?php if (in_array($order['status'], ['Delivered', 'Shipped'])): ?>
                        <button class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            <i class="fas fa-download mr-1"></i> Download Invoice
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'Shipped'): ?>
                        <button class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            <i class="fas fa-map-marker-alt mr-1"></i> Track Shipment
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($order['status'] === 'Pending'): ?>
                        <button class="text-red-600 hover:text-red-700 text-sm font-medium">
                            <i class="fas fa-times mr-1"></i> Cancel Order
                        </button>
                        <?php endif; ?>
                        
                        <button class="text-gray-600 hover:text-gray-700 text-sm font-medium">
                            <i class="fas fa-question-circle mr-1"></i> Get Help
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination (if needed) -->
        <?php if (count($currentOrders) > 10): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center space-x-2">
                <button class="px-3 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-4 py-2 bg-primary-600 text-white border border-primary-600 rounded-lg">1</button>
                <button class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">2</button>
                <button class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">3</button>
                <button class="px-3 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </nav>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Order Timeline Modal (Hidden by default) -->
<div id="orderTimeline" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Order Timeline</h3>
                <button onclick="closeTimeline()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-6">
                <!-- Timeline items will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function showTimeline(orderId) {
    document.getElementById('orderTimeline').classList.remove('hidden');
    // In a real app, fetch timeline data via AJAX
}

function closeTimeline() {
    document.getElementById('orderTimeline').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('orderTimeline').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTimeline();
    }
});
</script>

<?php include 'includes/footer.php'; ?>