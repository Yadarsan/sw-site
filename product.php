<?php
// Get product ID
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productId) {
    header("Location: " . url('products'));
    exit;
}

// Load product details
$product = app()->getProductCatalog()->getProductDetails($productId);

if (!$product) {
    header("HTTP/1.0 404 Not Found");
    $pageTitle = 'Product Not Found';
    include 'includes/header.php';
    echo '<div class="container mx-auto px-4 py-16 text-center">
            <h1 class="text-3xl font-bold mb-4">Product Not Found</h1>
            <p class="text-gray-600 mb-8">The product you are looking for does not exist.</p>
            <a href="' . url('products') . '" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition">
                Browse Products
            </a>
          </div>';
    include 'includes/footer.php';
    exit;
}

// Set page title
$pageTitle = $product['name'];

// Include header
include 'includes/header.php';

// Get related products (same category)
$relatedProducts = app()->getProductCatalog()->filterByCategory($product['category'], 1, 4)['products'];
// Remove current product from related
$relatedProducts = array_filter($relatedProducts, function($p) use ($productId) {
    return $p['product_id'] != $productId;
});
?>

<!-- Breadcrumb -->
<section class="bg-gray-100 py-4">
    <div class="container mx-auto px-4">
        <nav class="flex items-center text-sm text-gray-600">
            <a href="<?php echo url(); ?>" class="hover:text-primary-600">Home</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="<?php echo url('products'); ?>" class="hover:text-primary-600">Products</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="<?php echo url('category/' . urlencode($product['category'])); ?>" class="hover:text-primary-600">
                <?php echo htmlspecialchars($product['category']); ?>
            </a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-800"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12">
            <!-- Product Images -->
            <div>
                <div class="bg-white rounded-lg shadow-sm p-4" x-data="{ activeImage: 0 }">
                    <!-- Main Image -->
                    <div class="relative mb-4 overflow-hidden rounded-lg">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="w-full h-96 object-cover"
                             onerror="this.src='https://via.placeholder.com/600x600?text=No+Image'">
                        
                        <?php if($product['stock'] <= 5 && $product['stock'] > 0): ?>
                        <span class="absolute top-4 left-4 bg-red-500 text-white text-sm px-3 py-1 rounded-full">
                            Only <?php echo $product['stock']; ?> left in stock!
                        </span>
                        <?php elseif($product['stock'] == 0): ?>
                        <span class="absolute top-4 left-4 bg-gray-500 text-white text-sm px-3 py-1 rounded-full">
                            Out of Stock
                        </span>
                        <?php endif; ?>
                        
                        <!-- Zoom hint -->
                        <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                            <i class="fas fa-search-plus mr-1"></i> Click to zoom
                        </div>
                    </div>
                    
                    <!-- Thumbnail Images -->
                    <div class="grid grid-cols-4 gap-2">
                        <?php 
                        // Simulate multiple images
                        for($i = 0; $i < 4; $i++): 
                        ?>
                        <button @click="activeImage = <?php echo $i; ?>" 
                                class="border-2 rounded-lg overflow-hidden transition"
                                :class="activeImage === <?php echo $i; ?> ? 'border-primary-600' : 'border-gray-200'">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                                 alt="Product thumbnail"
                                 class="w-full h-20 object-cover"
                                 onerror="this.src='https://via.placeholder.com/150x150?text=No+Image'">
                        </button>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Product Info -->
            <div>
                <div class="mb-6">
                    <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                    <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <!-- Rating -->
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <?php 
                            $rating = 4.5; // Simulated rating
                            for($i = 0; $i < 5; $i++): 
                                if($i < floor($rating)):
                            ?>
                                <i class="fas fa-star"></i>
                            <?php elseif($i < $rating): ?>
                                <i class="fas fa-star-half-alt"></i>
                            <?php else: ?>
                                <i class="far fa-star"></i>
                            <?php endif; endfor; ?>
                        </div>
                        <span class="ml-2 text-gray-600">(<?php echo rand(50, 200); ?> reviews)</span>
                        <span class="mx-2 text-gray-400">|</span>
                        <span class="text-gray-600"><?php echo rand(100, 500); ?> sold</span>
                    </div>
                    
                    <!-- Price -->
                    <div class="flex items-baseline mb-6">
                        <span class="text-4xl font-bold text-primary-600">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php if(rand(0, 1)): // Random "was" price ?>
                        <span class="ml-3 text-xl text-gray-400 line-through">$<?php echo number_format($product['price'] * 1.3, 2); ?></span>
                        <span class="ml-2 bg-red-500 text-white text-sm px-2 py-1 rounded">-23% OFF</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <div class="prose prose-gray mb-6">
                        <h3 class="text-lg font-semibold mb-2">Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <!-- Key Features -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Key Features</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>High-quality materials and construction</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>1-year manufacturer warranty included</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>Free shipping on orders over $50</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>30-day money-back guarantee</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Add to Cart Section -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <?php if($product['stock'] > 0): ?>
                        <form onsubmit="addProductToCart(event, <?php echo $product['product_id']; ?>)" class="space-y-4">
                            <div class="flex items-center space-x-4">
                                <label class="font-semibold">Quantity:</label>
                                <div class="flex items-center border rounded-lg">
                                    <button type="button" onclick="decreaseQuantity()" class="px-4 py-2 hover:bg-gray-100 transition">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           id="quantity" 
                                           name="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="<?php echo $product['stock']; ?>"
                                           class="w-16 text-center border-x py-2 focus:outline-none">
                                    <button type="button" onclick="increaseQuantity()" class="px-4 py-2 hover:bg-gray-100 transition">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <span class="text-gray-600"><?php echo $product['stock']; ?> available</span>
                            </div>
                            
                            <div class="flex space-x-4">
                                <button type="submit" class="flex-1 bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition duration-300 font-semibold">
                                    <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                                </button>
                                <button type="button" onclick="addToWishlist(<?php echo $product['product_id']; ?>)" 
                                        class="bg-white border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:border-red-500 hover:text-red-500 transition duration-300">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            
                            <button type="button" onclick="buyNow(<?php echo $product['product_id']; ?>)" 
                                    class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-300 font-semibold">
                                Buy Now
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-circle text-5xl text-gray-400 mb-4"></i>
                            <p class="text-xl font-semibold text-gray-700 mb-2">Out of Stock</p>
                            <p class="text-gray-600 mb-4">This product is currently unavailable.</p>
                            <button onclick="notifyWhenAvailable(<?php echo $product['product_id']; ?>)" 
                                    class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition">
                                Notify When Available
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="border rounded-lg p-3">
                            <i class="fas fa-shield-alt text-2xl text-green-600 mb-1"></i>
                            <p class="text-sm font-semibold">Secure Payment</p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <i class="fas fa-truck text-2xl text-blue-600 mb-1"></i>
                            <p class="text-sm font-semibold">Fast Delivery</p>
                        </div>
                        <div class="border rounded-lg p-3">
                            <i class="fas fa-undo text-2xl text-yellow-600 mb-1"></i>
                            <p class="text-sm font-semibold">Easy Returns</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Tabs -->
        <div class="mt-16" x-data="{ activeTab: 'specifications' }">
            <div class="border-b">
                <nav class="flex space-x-8">
                    <button @click="activeTab = 'specifications'" 
                            :class="activeTab === 'specifications' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-800'"
                            class="py-4 font-semibold transition">
                        Specifications
                    </button>
                    <button @click="activeTab = 'reviews'" 
                            :class="activeTab === 'reviews' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-800'"
                            class="py-4 font-semibold transition">
                        Reviews (<?php echo rand(50, 200); ?>)
                    </button>
                    <button @click="activeTab = 'shipping'" 
                            :class="activeTab === 'shipping' ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-gray-800'"
                            class="py-4 font-semibold transition">
                        Shipping & Returns
                    </button>
                </nav>
            </div>
            
            <div class="py-8">
                <!-- Specifications Tab -->
                <div x-show="activeTab === 'specifications'" x-transition>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-xl font-semibold mb-4">Technical Specifications</h3>
                        <table class="w-full">
                            <tbody class="divide-y">
                                <tr>
                                    <td class="py-3 text-gray-600 w-1/3">Product ID</td>
                                    <td class="py-3 font-medium">#<?php echo $product['product_id']; ?></td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-gray-600">Category</td>
                                    <td class="py-3 font-medium"><?php echo htmlspecialchars($product['category']); ?></td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-gray-600">Availability</td>
                                    <td class="py-3 font-medium">
                                        <?php if($product['stock'] > 0): ?>
                                            <span class="text-green-600">In Stock (<?php echo $product['stock']; ?> units)</span>
                                        <?php else: ?>
                                            <span class="text-red-600">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-gray-600">Weight</td>
                                    <td class="py-3 font-medium"><?php echo rand(1, 10); ?> lbs</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-gray-600">Dimensions</td>
                                    <td class="py-3 font-medium"><?php echo rand(10, 30); ?>" x <?php echo rand(10, 20); ?>" x <?php echo rand(5, 15); ?>"</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-gray-600">Warranty</td>
                                    <td class="py-3 font-medium">1 Year Manufacturer Warranty</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div x-show="activeTab === 'reviews'" x-transition x-cloak>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold">Customer Reviews</h3>
                            <button class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                                Write a Review
                            </button>
                        </div>
                        
                        <!-- Review Summary -->
                        <div class="grid md:grid-cols-3 gap-6 mb-8">
                            <div class="text-center">
                                <p class="text-5xl font-bold text-primary-600">4.5</p>
                                <div class="flex justify-center text-yellow-400 my-2">
                                    <?php for($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-gray-600">Based on <?php echo rand(50, 200); ?> reviews</p>
                            </div>
                            
                            <div class="md:col-span-2">
                                <?php 
                                $ratings = [5 => 65, 4 => 20, 3 => 10, 2 => 3, 1 => 2];
                                foreach($ratings as $stars => $percentage): 
                                ?>
                                <div class="flex items-center mb-2">
                                    <span class="w-12"><?php echo $stars; ?> star</span>
                                    <div class="flex-1 mx-4 bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-400 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="w-12 text-right"><?php echo $percentage; ?>%</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Sample Reviews -->
                        <div class="space-y-6">
                            <?php 
                            $reviewers = ['John D.', 'Sarah M.', 'Mike R.'];
                            $reviews = [
                                'Excellent product! Exactly as described and works perfectly.',
                                'Great value for money. Fast shipping and well packaged.',
                                'Good quality, but took a while to arrive. Overall satisfied.'
                            ];
                            
                            for($i = 0; $i < 3; $i++): 
                            ?>
                            <div class="border-t pt-6">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold"><?php echo $reviewers[$i]; ?></p>
                                        <div class="flex text-yellow-400 text-sm my-1">
                                            <?php for($j = 0; $j < 5; $j++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-sm text-gray-600">Verified Purchase • <?php echo rand(1, 30); ?> days ago</p>
                                    </div>
                                    <button class="text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-flag"></i>
                                    </button>
                                </div>
                                <p class="mt-3 text-gray-700"><?php echo $reviews[$i]; ?></p>
                                <div class="mt-3 flex items-center space-x-4 text-sm">
                                    <button class="text-gray-600 hover:text-primary-600">
                                        <i class="fas fa-thumbs-up mr-1"></i> Helpful (<?php echo rand(5, 20); ?>)
                                    </button>
                                    <button class="text-gray-600 hover:text-primary-600">
                                        <i class="fas fa-thumbs-down mr-1"></i> Not Helpful (<?php echo rand(0, 3); ?>)
                                    </button>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="mt-8 text-center">
                            <button class="text-primary-600 hover:text-primary-700 font-medium">
                                Load More Reviews <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping Tab -->
                <div x-show="activeTab === 'shipping'" x-transition x-cloak>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-xl font-semibold mb-4">Shipping & Returns</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <h4 class="font-semibold mb-2"><i class="fas fa-truck mr-2 text-primary-600"></i>Shipping Information</h4>
                                <ul class="space-y-2 text-gray-700 ml-7">
                                    <li>• Free standard shipping on orders over $50</li>
                                    <li>• Standard shipping (5-7 business days): $9.99</li>
                                    <li>• Express shipping (2-3 business days): $19.99</li>
                                    <li>• Overnight shipping (1 business day): $39.99</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold mb-2"><i class="fas fa-undo mr-2 text-primary-600"></i>Return Policy</h4>
                                <ul class="space-y-2 text-gray-700 ml-7">
                                    <li>• 30-day return window from delivery date</li>
                                    <li>• Items must be unused and in original packaging</li>
                                    <li>• Free return shipping on defective items</li>
                                    <li>• Refund processed within 5-7 business days</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold mb-2"><i class="fas fa-shield-alt mr-2 text-primary-600"></i>Warranty</h4>
                                <p class="text-gray-700 ml-7">
                                    This product comes with a 1-year manufacturer warranty covering defects in materials and workmanship. 
                                    Extended warranty options are available at checkout.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if(!empty($relatedProducts)): ?>
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-8">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach(array_slice($relatedProducts, 0, 4) as $related): ?>
                <div class="bg-white rounded-lg shadow-sm hover:shadow-xl transition duration-300 overflow-hidden group">
                    <div class="relative">
                        <a href="<?php echo url('product/' . $related['product_id']); ?>">
                            <img src="<?php echo htmlspecialchars($related['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 class="w-full h-48 object-cover group-hover:scale-105 transition duration-300"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                        </a>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold mb-2 line-clamp-2">
                            <a href="<?php echo url('product/' . $related['product_id']); ?>" class="hover:text-primary-600 transition">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h3>
                        <p class="text-xl font-bold text-primary-600">$<?php echo number_format($related['price'], 2); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
[x-cloak] { display: none !important; }
</style>

<script>
// Quantity controls
function increaseQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const current = parseInt(input.value);
    if (current > 1) {
        input.value = current - 1;
    }
}

// Add to cart
function addProductToCart(event, productId) {
    event.preventDefault();
    const quantity = document.getElementById('quantity').value;
    
    showNotification('Adding to cart...', 'info');
    
    fetch('<?php echo url('ajax/cart'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            // Update cart count in header
            if (typeof Alpine !== 'undefined') {
                Alpine.store('cart').updateCount(data.cart_count);
            }
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Buy now
function buyNow(productId) {
    const quantity = document.getElementById('quantity').value;
    
    // Add to cart first
    fetch('<?php echo url('ajax/cart'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to checkout
            window.location.href = '<?php echo url('checkout'); ?>';
        } else {
            showNotification(data.message || 'Error processing request', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// Notify when available
function notifyWhenAvailable(productId) {
    showNotification('We\'ll notify you when this product is back in stock!', 'success');
}
</script>

<?php include 'includes/footer.php'; ?>