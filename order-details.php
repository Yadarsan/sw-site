<?php
// Get order ID
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$orderId) {
    header("Location: " . url('orders'));
    exit;
}

// Check if user is logged in BEFORE any output
if (!app()->getAuthSession()->isLoggedIn()) {
    header("Location: " . url('login'));
    exit;
}

// Get current user
$userId = app()->getAuthSession()->getCurrentUserId();

// Load order
$order = new Order(app()->getDB());
if (!$order->loadById($orderId)) {
    // Order not found - we need to handle this without output
    $orderNotFound = true;
} else {
    // Verify order belongs to current user
    if ($order->getUserID() != $userId) {
        $accessDenied = true;
    }
}

// Set page title
$pageTitle = 'Order Details';

// Include header after all redirects
include 'includes/header.php';

// Handle error cases after header is included
if (isset($orderNotFound)) {
    echo '<div class="container mx-auto px-4 py-16 text-center">
            <h1 class="text-3xl font-bold mb-4">Order Not Found</h1>
            <p class="text-gray-600 mb-8">The order you are looking for does not exist.</p>
            <a href="' . url('orders') . '" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition">
                Back to Orders
            </a>
          </div>';
    include 'includes/footer.php';
    exit;
}

if (isset($accessDenied)) {
    echo '<div class="container mx-auto px-4 py-16 text-center">
            <h1 class="text-3xl font-bold mb-4">Access Denied</h1>
            <p class="text-gray-600 mb-8">You do not have permission to view this order.</p>
            <a href="' . url('orders') . '" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition">
                Back to Orders
            </a>
          </div>';
    include 'includes/footer.php';
    exit;
}

// Get order details
$orderDetails = $order->getDetails();
$orderItems = $orderDetails['items'];

// Get payment info
$payment = new Payment(app()->getDB());
$paymentInfo = null;
if ($payment->loadByOrder($orderId)) {
    $paymentInfo = [
        'method' => $payment->getMethod(),
        'status' => $payment->getStatus(),
        'amount' => $payment->getAmount()
    ];
}

// Parse shipping info
$shippingInfo = json_decode($orderDetails['shipping_info'], true) ?? [];

// Calculate order totals
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['unit_price'] * $item['quantity'];
}
$tax = $subtotal * 0.08; // 8% tax
$shipping = $subtotal > 50 ? 0 : 9.99;
$total = $orderDetails['total_price'];
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-8">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Order #<?php echo $orderId; ?></h1>
                <nav class="flex items-center text-sm">
                    <a href="<?php echo url(); ?>" class="hover:text-primary-200">Home</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <a href="<?php echo url('account'); ?>" class="hover:text-primary-200">My Account</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <a href="<?php echo url('orders'); ?>" class="hover:text-primary-200">Orders</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <span>#<?php echo $orderId; ?></span>
                </nav>
            </div>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-8">
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Status -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-4">Order Status</h2>
                
                <?php
                $statusSteps = ['Pending', 'Processing', 'Shipped', 'Delivered'];
                $currentStepIndex = array_search($orderDetails['status'], $statusSteps);
                if ($currentStepIndex === false) $currentStepIndex = 0;
                ?>
                
                <div class="relative">
                    <!-- Progress Bar -->
                    <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200">
                        <div class="h-full bg-primary-600 transition-all duration-500" 
                             style="width: <?php echo ($currentStepIndex / (count($statusSteps) - 1)) * 100; ?>%"></div>
                    </div>
                    
                    <!-- Steps -->
                    <div class="relative flex justify-between">
                        <?php foreach ($statusSteps as $index => $step): ?>
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-2
                                        <?php echo $index <= $currentStepIndex ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-400'; ?>">
                                <?php if ($index < $currentStepIndex): ?>
                                    <i class="fas fa-check text-sm"></i>
                                <?php else: ?>
                                    <?php echo $index + 1; ?>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm <?php echo $index <= $currentStepIndex ? 'text-gray-800 font-medium' : 'text-gray-400'; ?>">
                                <?php echo $step; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php if ($orderDetails['status'] === 'Shipped'): ?>
                <div class="mt-6 bg-blue-50 rounded-lg p-4">
                    <p class="text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        Your order has been shipped! Track your package with tracking number: 
                        <span class="font-semibold">1Z999AA10123456784</span>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">Order Items</h2>
                </div>
                <div class="divide-y">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="p-6">
                        <div class="flex items-start space-x-4">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 class="w-20 h-20 object-cover rounded-lg"
                                 onerror="this.src='https://via.placeholder.com/80x80?text=No+Image'">
                            <div class="flex-1">
                                <h3 class="font-semibold">
                                    <a href="<?php echo url('product/' . $item['product_id']); ?>" class="hover:text-primary-600 transition">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </a>
                                </h3>
                                <p class="text-gray-600 text-sm mt-1">
                                    Quantity: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['unit_price'], 2); ?>
                                </p>
                            </div>
                            <p class="font-semibold">
                                $<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Order Summary -->
                <div class="bg-gray-50 p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax</span>
                            <span>$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'FREE'; ?></span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between">
                                <span class="font-semibold text-lg">Total</span>
                                <span class="font-semibold text-lg">$<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold mb-4">Order Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-600">Order Date</dt>
                        <dd class="font-medium"><?php echo date('F d, Y g:i A', strtotime($orderDetails['order_date'])); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Order Number</dt>
                        <dd class="font-medium">#<?php echo $orderId; ?></dd>
                    </div>
                    <?php if ($paymentInfo): ?>
                    <div>
                        <dt class="text-sm text-gray-600">Payment Method</dt>
                        <dd class="font-medium"><?php echo htmlspecialchars($paymentInfo['method']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Payment Status</dt>
                        <dd class="font-medium">
                            <?php if ($paymentInfo['status'] === 'Completed'): ?>
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Paid</span>
                            <?php else: ?>
                                <span class="text-yellow-600"><i class="fas fa-clock mr-1"></i> <?php echo htmlspecialchars($paymentInfo['status']); ?></span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
            
            <!-- Shipping Address -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold mb-4">Shipping Address</h3>
                <address class="text-sm not-italic text-gray-600">
                    <?php if (!empty($shippingInfo)): ?>
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($shippingInfo['name'] ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($shippingInfo['address'] ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($shippingInfo['city'] ?? ''); ?>, <?php echo htmlspecialchars($shippingInfo['state'] ?? ''); ?> <?php echo htmlspecialchars($shippingInfo['postal_code'] ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($shippingInfo['country'] ?? ''); ?></p>
                        <?php if (!empty($shippingInfo['phone'])): ?>
                            <p class="mt-2"><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($shippingInfo['phone']); ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><?php echo nl2br(htmlspecialchars($orderDetails['shipping_info'])); ?></p>
                    <?php endif; ?>
                </address>
            </div>
            
            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold mb-4">Actions</h3>
                <div class="space-y-3">
                    <?php if (in_array($orderDetails['status'], ['Delivered', 'Shipped'])): ?>
                    <button class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-download mr-2"></i> Download Invoice
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($orderDetails['status'] === 'Shipped'): ?>
                    <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-map-marker-alt mr-2"></i> Track Package
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($orderDetails['status'] === 'Delivered'): ?>
                    <button class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-redo mr-2"></i> Reorder Items
                    </button>
                    <button class="w-full bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-star mr-2"></i> Leave Review
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($orderDetails['status'] === 'Pending'): ?>
                    <button class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-times mr-2"></i> Cancel Order
                    </button>
                    <?php endif; ?>
                    
                    <button class="w-full bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-question-circle mr-2"></i> Contact Support
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Reorder functionality
function reorderItems() {
    if (confirm('Add all items from this order to your cart?')) {
        // In a real app, this would make an AJAX call to add items to cart
        showNotification('Items added to cart!', 'success');
    }
}

// Cancel order
function cancelOrder() {
    if (confirm('Are you sure you want to cancel this order?')) {
        // In a real app, this would make an AJAX call to cancel the order
        showNotification('Order cancellation requested', 'info');
    }
}
</script>

<?php include 'includes/footer.php'; ?>