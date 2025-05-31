<?php
// Check if user is logged in BEFORE any output
if (!app()->getAuthSession()->isLoggedIn()) {
    header("Location: " . url('login'));
    exit;
}

// Check if we have a pending order BEFORE any output
if (!isset($_SESSION['pending_order_id'])) {
    header("Location: " . url('cart'));
    exit;
}

$orderId = $_SESSION['pending_order_id'];

// Load order details
$order = new Order(app()->getDB());
if (!$order->loadById($orderId)) {
    unset($_SESSION['pending_order_id']);
    header("Location: " . url('cart'));
    exit;
}

// Verify order belongs to current user
if ($order->getUserID() != app()->getAuthSession()->getCurrentUserId()) {
    unset($_SESSION['pending_order_id']);
    header("Location: " . url('cart'));
    exit;
}

$orderDetails = $order->getDetails();
$total = $orderDetails['total_price'];

// Handle payment submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    // Process payment based on method
    $paymentProcessor = app()->getPaymentProcessor();
    $result = $paymentProcessor->simulatePayment($orderId, $total, $paymentMethod);
    
    if ($result['success']) {
        // Clear the pending order from session
        unset($_SESSION['pending_order_id']);
        
        // Generate invoice
        $order->generateInvoice();
        
        // Clear the cart
        app()->getShoppingCart()->clear();
        app()->saveCartToSession();
        
        // Set success message
        $_SESSION['flash_message'] = 'Payment successful! Your order has been confirmed.';
        $_SESSION['flash_type'] = 'success';
        
        // Redirect to order confirmation
        header("Location: " . url('orders/' . $orderId));
        exit;
    } else {
        $error = $result['message'];
    }
}

// Set page title
$pageTitle = 'Payment';

// Include header after all redirects
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-2">Payment</h1>
        <nav class="flex items-center text-sm">
            <a href="<?php echo url(); ?>" class="hover:text-primary-200">Home</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span>Payment</span>
        </nav>
    </div>
</section>

<!-- Checkout Steps -->
<div class="bg-gray-100 py-4">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-center space-x-4">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    <i class="fas fa-check"></i>
                </div>
                <span class="ml-2 text-sm font-medium">Shopping Cart</span>
            </div>
            <div class="w-16 h-1 bg-green-600"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    <i class="fas fa-check"></i>
                </div>
                <span class="ml-2 text-sm font-medium">Shipping Info</span>
            </div>
            <div class="w-16 h-1 bg-primary-600"></div>
            <div class="flex items-center">
                <div class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    3
                </div>
                <span class="ml-2 text-sm font-medium">Payment</span>
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

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Payment Methods -->
        <div class="lg:col-span-2">
            <form method="POST" id="payment-form">
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-6">Select Payment Method</h2>
                    
                    <div class="space-y-4">
                        <!-- Credit/Debit Card -->
                        <div class="border rounded-lg overflow-hidden">
                            <label class="flex items-start p-4 cursor-pointer hover:bg-gray-50 transition">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="Credit Card" 
                                       checked
                                       class="mt-1 mr-3 text-primary-600"
                                       onchange="showPaymentDetails('card')">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium">Credit/Debit Card</p>
                                            <p class="text-sm text-gray-600 mt-1">Visa, Mastercard, AMEX</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <i class="fab fa-cc-visa text-2xl text-gray-400"></i>
                                            <i class="fab fa-cc-mastercard text-2xl text-gray-400"></i>
                                            <i class="fab fa-cc-amex text-2xl text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <!-- Card Details -->
                            <div id="card-details" class="border-t p-4 bg-gray-50">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                                        <input type="text" 
                                               name="card_number" 
                                               placeholder="1234 5678 9012 3456"
                                               maxlength="19"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                                            <input type="text" 
                                                   name="card_expiry" 
                                                   placeholder="MM/YY"
                                                   maxlength="5"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">CVV</label>
                                            <input type="text" 
                                                   name="card_cvv" 
                                                   placeholder="123"
                                                   maxlength="4"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Cardholder Name</label>
                                        <input type="text" 
                                               name="card_name" 
                                               placeholder="John Doe"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PayPal -->
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="PayPal" 
                                   class="mt-1 mr-3 text-primary-600"
                                   onchange="showPaymentDetails('paypal')">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium">PayPal</p>
                                        <p class="text-sm text-gray-600 mt-1">Pay with your PayPal account</p>
                                    </div>
                                    <i class="fab fa-paypal text-3xl text-[#003087]"></i>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Bank Transfer -->
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" 
                                   name="payment_method" 
                                   value="Bank Transfer" 
                                   class="mt-1 mr-3 text-primary-600"
                                   onchange="showPaymentDetails('bank')">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium">Bank Transfer</p>
                                        <p class="text-sm text-gray-600 mt-1">Direct bank transfer (Sri Lankan banks)</p>
                                    </div>
                                    <i class="fas fa-university text-2xl text-gray-400"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- PayPal Details (Hidden) -->
                    <div id="paypal-details" class="mt-6 p-4 bg-blue-50 rounded-lg hidden">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            You will be redirected to PayPal to complete your payment securely.
                        </p>
                    </div>
                    
                    <!-- Bank Transfer Details (Hidden) -->
                    <div id="bank-details" class="mt-6 p-4 bg-yellow-50 rounded-lg hidden">
                        <h4 class="font-medium mb-3">Bank Account Details:</h4>
                        <dl class="space-y-2 text-sm">
                            <div class="flex">
                                <dt class="font-medium w-32">Bank:</dt>
                                <dd>Commercial Bank of Ceylon</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium w-32">Account Name:</dt>
                                <dd>AWE Electronics (Pvt) Ltd</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium w-32">Account No:</dt>
                                <dd>1234567890</dd>
                            </div>
                            <div class="flex">
                                <dt class="font-medium w-32">Branch:</dt>
                                <dd>Colombo Main Branch</dd>
                            </div>
                        </dl>
                        <p class="text-sm text-yellow-800 mt-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Please use Order #<?php echo $orderId; ?> as the reference. Orders are processed after payment confirmation.
                        </p>
                    </div>
                </div>
                
                <!-- Billing Address -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-6">Billing Address</h2>
                    
                    <label class="flex items-center mb-4">
                        <input type="checkbox" 
                               id="same_as_shipping" 
                               checked
                               class="mr-2 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Same as shipping address</span>
                    </label>
                    
                    <div id="billing-address" class="hidden">
                        <!-- Billing address fields would go here -->
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                <h2 class="text-xl font-semibold mb-6">Order Summary</h2>
                
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order #</span>
                        <span class="font-medium"><?php echo $orderId; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Items</span>
                        <span class="font-medium"><?php echo count($orderDetails['items']); ?></span>
                    </div>
                </div>
                
                <div class="border-t pt-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-semibold">Total Due</span>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-primary-600">
                                $<?php echo number_format($total, 2); ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                ≈ Rs. <?php echo number_format($total * 330, 2); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" 
                        form="payment-form"
                        class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition duration-300 font-semibold">
                    Complete Payment
                </button>
                
                <!-- Security badges -->
                <div class="mt-6 pt-6 border-t">
                    <div class="flex items-center justify-center space-x-4 text-gray-400">
                        <i class="fas fa-lock text-xl"></i>
                        <i class="fas fa-shield-alt text-xl"></i>
                        <span class="text-xs text-gray-600">256-bit SSL Encryption</span>
                    </div>
                </div>
                
                <!-- Test Mode Notice -->
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-700 text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Test Mode: Use card number 4242 4242 4242 4242
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide payment details based on selection
function showPaymentDetails(method) {
    // Hide all details
    document.getElementById('card-details').style.display = 'none';
    document.getElementById('paypal-details').classList.add('hidden');
    document.getElementById('bank-details').classList.add('hidden');
    
    // Show selected method details
    if (method === 'card') {
        document.getElementById('card-details').style.display = 'block';
    } else if (method === 'paypal') {
        document.getElementById('paypal-details').classList.remove('hidden');
    } else if (method === 'bank') {
        document.getElementById('bank-details').classList.remove('hidden');
    }
}

// Format card number input
document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Format expiry date
document.querySelector('input[name="card_expiry"]').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
    }
    e.target.value = value;
});

// Only allow numbers for CVV
document.querySelector('input[name="card_cvv"]').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

// Toggle billing address
document.getElementById('same_as_shipping').addEventListener('change', function() {
    document.getElementById('billing-address').classList.toggle('hidden', this.checked);
});

// Form validation
document.getElementById('payment-form').addEventListener('submit', function(e) {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'Credit Card') {
        const cardNumber = document.querySelector('input[name="card_number"]').value.replace(/\s/g, '');
        const cardExpiry = document.querySelector('input[name="card_expiry"]').value;
        const cardCvv = document.querySelector('input[name="card_cvv"]').value;
        const cardName = document.querySelector('input[name="card_name"]').value;
        
        if (!cardNumber || cardNumber.length < 16) {
            e.preventDefault();
            alert('Please enter a valid card number');
            return false;
        }
        
        if (!cardExpiry || !cardExpiry.match(/^\d{2}\/\d{2}$/)) {
            e.preventDefault();
            alert('Please enter a valid expiry date (MM/YY)');
            return false;
        }
        
        if (!cardCvv || cardCvv.length < 3) {
            e.preventDefault();
            alert('Please enter a valid CVV');
            return false;
        }
        
        if (!cardName) {
            e.preventDefault();
            alert('Please enter the cardholder name');
            return false;
        }
    }
    
    // Show loading state
    const submitButton = e.target.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
});
</script>

<?php include 'includes/footer.php'; ?>