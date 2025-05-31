<?php
// Check if user is logged in BEFORE any output
if (!app()->getAuthSession()->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = url('checkout');
    header("Location: " . url('login'));
    exit;
}

// Get cart and check if empty BEFORE any output
$cart = app()->getShoppingCart();
if ($cart->isEmpty()) {
    header("Location: " . url('cart'));
    exit;
}

// Get user data
$user = app()->getAuthSession()->getCurrentUser();
$cartContents = $cart->getContents();
$subtotal = $cart->getSubtotal();

// Calculate totals
$taxRate = 0.08;
$tax = $subtotal * $taxRate;
$shipping = $subtotal > 50 ? 0 : 9.99;
$total = $subtotal + $tax + $shipping;

// Handle form submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process checkout
    $shippingInfo = json_encode([
        'name' => $_POST['shipping_name'] ?? $user['name'],
        'address' => $_POST['shipping_address'],
        'city' => $_POST['shipping_city'],
        'state' => $_POST['shipping_state'],
        'postal_code' => $_POST['shipping_zip'],
        'country' => $_POST['shipping_country'] ?? 'Sri Lanka',
        'phone' => $_POST['shipping_phone'] ?? $user['phone']
    ]);
    
    // Convert cart to order
    $orderData = app()->getCartManager()->convertToOrder($cart, $user['user_id'], $shippingInfo);
    
    if ($orderData) {
        // Store order ID in session for payment page
        $_SESSION['pending_order_id'] = $orderData['order_id'];
        header("Location: " . url('payment'));
        exit;
    } else {
        $error = "Failed to process order. Please try again.";
    }
}

// Set page title
$pageTitle = 'Checkout';

// NOW include header after all potential redirects
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-2">Checkout</h1>
        <nav class="flex items-center text-sm">
            <a href="<?php echo url(); ?>" class="hover:text-primary-200">Home</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="<?php echo url('cart'); ?>" class="hover:text-primary-200">Cart</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span>Checkout</span>
        </nav>
    </div>
</section>

<!-- Checkout Steps -->
<div class="bg-gray-100 py-4">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-center space-x-4">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    <i class="fas fa-check"></i>
                </div>
                <span class="ml-2 text-sm font-medium">Shopping Cart</span>
            </div>
            <div class="w-16 h-1 bg-gray-300"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    2
                </div>
                <span class="ml-2 text-sm font-medium">Shipping Info</span>
            </div>
            <div class="w-16 h-1 bg-gray-300"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                    3
                </div>
                <span class="ml-2 text-sm text-gray-600">Payment</span>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <?php if (isset($error)): ?>
    <div class="mb-6 bg-red-100 text-red-800 p-4 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="grid lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Shipping Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Shipping Information</h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" 
                               name="shipping_name" 
                               value="<?php echo htmlspecialchars($user['name']); ?>"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Street Address *</label>
                        <input type="text" 
                               name="shipping_address" 
                               placeholder="11 20 pakington St "
                               value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Suburb *</label>
                        <input type="text" 
                               name="shipping_city" 
                               placeholder="KEW"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">State *</label>
                        <select name="shipping_state" 
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Select State</option>
                            <option value="Western">VIC</option>
                            <option value="Central">NSW</option>
                            <option value="Southern">WA</option>
                            <option value="Northern">Tasmania</option>
                            <option value="Eastern">SA</option>
                           
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code *</label>
                        <input type="text" 
                               name="shipping_zip" 
                               placeholder="3101"
                               
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" 
                               name="shipping_phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               placeholder="+61 420 310 334"
                               required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <input type="hidden" name="shipping_country" value="Australia">
                </div>
                
                <div class="mt-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               id="save_address" 
                               name="save_address" 
                               checked
                               class="mr-2 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Save this address for future orders</span>
                    </label>
                </div>
            </div>
            
            <!-- Delivery Options -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Delivery Options</h2>
                
                <div class="space-y-4">
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                        <input type="radio" 
                               name="delivery_option" 
                               value="standard" 
                               checked
                               class="mt-1 mr-3 text-primary-600">
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium">Standard Delivery</p>
                                    <p class="text-sm text-gray-600 mt-1">Delivered within 3-5 business days</p>
                                    <p class="text-sm text-gray-500 mt-1">Available across all provinces</p>
                                </div>
                                <p class="font-semibold">
                                    <?php if($shipping > 0): ?>
                                        Rs. <?php echo number_format($shipping * 330, 2); ?>
                                    <?php else: ?>
                                        <span class="text-green-600">FREE</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </label>
                    
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                        <input type="radio" 
                               name="delivery_option" 
                               value="express" 
                               class="mt-1 mr-3 text-primary-600">
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium">Express Delivery</p>
                                    <p class="text-sm text-gray-600 mt-1">Delivered within 1-2 business days</p>
                                    <p class="text-sm text-gray-500 mt-1">Available in Colombo & suburbs only</p>
                                </div>
                                <p class="font-semibold">Rs. <?php echo number_format(19.99 * 330, 2); ?></p>
                            </div>
                        </div>
                    </label>
                    
                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                        <input type="radio" 
                               name="delivery_option" 
                               value="pickup" 
                               class="mt-1 mr-3 text-primary-600">
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium">Store Pickup</p>
                                    <p class="text-sm text-gray-600 mt-1">Ready for pickup in 2-4 hours</p>
                                    <p class="text-sm text-gray-500 mt-1">AWE Electronics -  Main Branch</p>
                                </div>
                                <p class="font-semibold text-green-600">FREE</p>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6">Additional Information</h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Notes (Optional)</label>
                    <textarea name="order_notes" 
                              rows="3"
                              placeholder="Special delivery instructions, gift message, etc."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
                
                <div class="mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="gift_wrap" 
                               class="mr-2 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Gift wrap this order (5Aud)</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Order Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                <h2 class="text-xl font-semibold mb-6">Order Summary</h2>
                
                <!-- Cart Items -->
                <div class="space-y-4 mb-6 max-h-64 overflow-y-auto">
                    <?php foreach ($cartContents as $item): ?>
                    <div class="flex items-center space-x-3">
                        <img src="<?php echo htmlspecialchars($item['product']['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                             alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                             class="w-16 h-16 object-cover rounded"
                             onerror="this.src='https://via.placeholder.com/64x64?text=No+Image'">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium line-clamp-1"><?php echo htmlspecialchars($item['product']['name']); ?></h4>
                            <p class="text-sm text-gray-600">Qty: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['product']['price'], 2); ?></p>
                        </div>
                        <p class="text-sm font-medium">$<?php echo number_format($item['subtotal'], 2); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totals -->
                <div class="border-t pt-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax (<?php echo $taxRate * 100; ?>%)</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Shipping</span>
                        <span id="shipping-cost">
                            <?php if($shipping > 0): ?>
                                $<?php echo number_format($shipping, 2); ?>
                            <?php else: ?>
                                <span class="text-green-600">FREE</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="border-t pt-3">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold">Total</span>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-primary-600" id="total-amount">
                                    $<?php echo number_format($total, 2); ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    ≈ Rs. <?php echo number_format($total * 330, 2); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition duration-300 font-semibold mt-6">
                    Continue to Payment
                </button>
                
                <!-- Security Note -->
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-lock mr-1"></i>
                        Your information is secure and encrypted
                    </p>
                </div>
                
                <!-- Return Policy -->
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600 text-center">
                        <i class="fas fa-undo mr-1"></i>
                        30-day return policy applies
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
// Update shipping cost based on delivery option
document.querySelectorAll('input[name="delivery_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        updateTotals();
    });
});

// Update totals when gift wrap is selected
document.querySelector('input[name="gift_wrap"]').addEventListener('change', function() {
    updateTotals();
});

function updateTotals() {
    const subtotal = <?php echo $subtotal; ?>;
    const tax = <?php echo $tax; ?>;
    let shipping = <?php echo $shipping; ?>;
    
    // Get selected delivery option
    const deliveryOption = document.querySelector('input[name="delivery_option"]:checked').value;
    
    if (deliveryOption === 'express') {
        shipping = 19.99;
    } else if (deliveryOption === 'pickup') {
        shipping = 0;
    }
    
    // Check if gift wrap is selected
    const giftWrap = document.querySelector('input[name="gift_wrap"]').checked;
    const giftWrapCost = giftWrap ? (250 / 330) : 0; // Convert Rs. 250 to USD
    
    // Update shipping display
    const shippingElement = document.getElementById('shipping-cost');
    if (shipping > 0) {
        shippingElement.innerHTML = '$' + shipping.toFixed(2);
    } else {
        shippingElement.innerHTML = '<span class="text-green-600">FREE</span>';
    }
    
    // Calculate total
    const total = subtotal + tax + shipping + giftWrapCost;
    
    // Update total display
    document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
    
    // Update LKR equivalent
    const lkrAmount = total * 330;
    document.querySelector('.text-gray-500').textContent = '≈ Rs. ' + lkrAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const city = document.querySelector('input[name="shipping_city"]').value;
    const deliveryOption = document.querySelector('input[name="delivery_option"]:checked').value;
    
    // Check if express delivery is selected for non-Colombo addresses
    if (deliveryOption === 'express' && !city.toLowerCase().includes('colombo')) {
        e.preventDefault();
        alert('Express delivery is only available in Colombo and suburbs. Please select a different delivery option.');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>