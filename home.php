<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php

// Set page title
$pageTitle = 'Home';

// Include header
include 'includes/header.php';

// Get featured products and categories
$featuredProducts = app()->getProductCatalog()->getFeaturedProducts(8);
$categories = app()->getProductCatalog()->getAllCategories();

// Get some random products for different sections
$newArrivals = app()->getProductCatalog()->listProducts(1, 4)['products'];
$bestSellers = app()->getProductCatalog()->listProducts(2, 4)['products'];
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-primary-600 to-purple-700 text-white">
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <div class="relative container mx-auto px-4 py-24 lg:py-32">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="text-center lg:text-left">
                <h1 class="text-4xl lg:text-6xl font-bold mb-6 animate-fade-in">
                    Welcome to <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-pink-400">AWE Electronics</span>
                </h1>
                <p class="text-xl mb-8 opacity-90">
                    Discover the latest in technology with unbeatable prices and exceptional service.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="<?php echo url('products'); ?>" class="bg-white text-primary-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transform hover:scale-105 transition duration-300 shadow-lg">
                        Shop Now
                    </a>
                    <a href="<?php echo url('deals'); ?>" class="border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-primary-600 transform hover:scale-105 transition duration-300">
                        View Deals
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-primary-400 to-purple-400 rounded-full filter blur-3xl opacity-30 animate-pulse"></div>
                <img src="<?php echo asset('images/hero-electronics.png'); ?>" 
                     alt="Electronics" 
                     class="relative z-10 w-full max-w-lg mx-auto animate-float"
                     onerror="this.src='https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=600&h=400&fit=crop'">
            </div>
        </div>
    </div>
    
    <!-- Wave SVG -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V120Z" fill="white"/>
        </svg>
    </div>
</section>

<!-- Features Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-primary-600 text-2xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Free Shipping</h3>
                <p class="text-gray-600">On orders over $50</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Secure Payment</h3>
                <p class="text-gray-600">100% secure transactions</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-undo text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Easy Returns</h3>
                <p class="text-gray-600">30-day return policy</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 text-center hover:shadow-lg transition duration-300">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-purple-600 text-2xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">24/7 Support</h3>
                <p class="text-gray-600">Dedicated customer service</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold mb-4">Shop by Category</h2>
            <p class="text-gray-600 text-lg">Find exactly what you're looking for</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <?php 
            $categoryIcons = [
                'Smartphones' => 'fa-mobile-alt',
                'Laptops' => 'fa-laptop',
                'TVs & Audio' => 'fa-tv',
                'Cameras' => 'fa-camera',
                'Smart Home' => 'fa-home',
                'Gaming' => 'fa-gamepad'
            ];
            
            foreach($categories as $index => $category): 
                $icon = $categoryIcons[$category] ?? 'fa-shopping-bag';
                $colors = ['primary', 'purple', 'green', 'yellow', 'pink', 'indigo'];
                $color = $colors[$index % count($colors)];
            ?>
            <a href="<?php echo url('category/' . urlencode($category)); ?>" 
               class="group bg-white rounded-xl p-6 text-center hover:shadow-xl transition duration-300 transform hover:-translate-y-1">
                <div class="w-20 h-20 bg-<?php echo $color; ?>-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition duration-300">
                    <i class="fas <?php echo $icon; ?> text-<?php echo $color; ?>-600 text-3xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-<?php echo $color; ?>-600 transition"><?php echo htmlspecialchars($category); ?></h3>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl lg:text-4xl font-bold mb-2">Featured Products</h2>
                <p class="text-gray-600 text-lg">Handpicked just for you</p>
            </div>
            <a href="<?php echo url('products'); ?>" class="text-primary-600 hover:text-primary-700 font-semibold flex items-center">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($featuredProducts as $product): ?>
            <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition duration-300 overflow-hidden group">
                <div class="relative aspect-w-1 aspect-h-1 bg-gray-100">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="w-full h-64 object-cover group-hover:scale-105 transition duration-300"
                         onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
                    
                    <?php if($product['stock'] <= 5 && $product['stock'] > 0): ?>
                    <span class="absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                        Only <?php echo $product['stock']; ?> left!
                    </span>
                    <?php elseif($product['stock'] == 0): ?>
                    <span class="absolute top-2 left-2 bg-gray-500 text-white text-xs px-2 py-1 rounded">
                        Out of Stock
                    </span>
                    <?php endif; ?>
                    
                    <!-- Quick Actions -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <div class="flex space-x-2">
                            <button onclick="quickView(<?php echo $product['product_id']; ?>)" 
                                    class="bg-white text-gray-800 p-3 rounded-full hover:bg-primary-600 hover:text-white transition duration-300">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="addToWishlist(<?php echo $product['product_id']; ?>)" 
                                    class="bg-white text-gray-800 p-3 rounded-full hover:bg-primary-600 hover:text-white transition duration-300">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-4">
                    <p class="text-sm text-gray-500 mb-1"><?php echo htmlspecialchars($product['category']); ?></p>
                    <h3 class="font-semibold text-lg mb-2 line-clamp-2">
                        <a href="<?php echo url('product/' . $product['product_id']); ?>" class="hover:text-primary-600 transition">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    <div class="flex items-center mb-3">
                        <div class="flex text-yellow-400 text-sm">
                            <?php for($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-gray-500 text-sm ml-2">(<?php echo rand(10, 200); ?>)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-bold text-primary-600">$<?php echo number_format($product['price'], 2); ?></span>
                        <?php if($product['stock'] > 0): ?>
                        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                                class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition duration-300 flex items-center">
                            <i class="fas fa-shopping-cart mr-2"></i> Add
                        </button>
                        <?php else: ?>
                        <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed">
                            Out of Stock
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Promotional Banner -->
<section class="py-16 bg-gradient-to-r from-purple-600 to-pink-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl lg:text-5xl font-bold mb-4">Special Offer: Up to 50% Off!</h2>
        <p class="text-xl mb-8 opacity-90">Limited time offer on selected electronics</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo url('deals'); ?>" class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transform hover:scale-105 transition duration-300 shadow-lg">
                Shop Deals Now
            </a>
            <div class="flex items-center justify-center space-x-2 text-lg">
                <i class="fas fa-clock"></i>
                <span>Ends in: <span id="countdown" class="font-bold">23:59:59</span></span>
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-4xl font-bold mb-4">New Arrivals</h2>
            <p class="text-gray-600 text-lg">Check out our latest products</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($newArrivals as $product): ?>
            <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition duration-300 overflow-hidden">
                <div class="relative">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="w-full h-48 object-cover"
                         onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                    <span class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded">New</span>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold mb-2 line-clamp-2">
                        <a href="<?php echo url('product/' . $product['product_id']); ?>" class="hover:text-primary-600 transition">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </h3>
                    <p class="text-xl font-bold text-primary-600">$<?php echo number_format($product['price'], 2); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-xl p-8 lg:p-12 text-center">
            <i class="fas fa-envelope text-6xl text-primary-600 mb-6"></i>
            <h2 class="text-3xl font-bold mb-4">Stay in the Loop</h2>
            <p class="text-gray-600 text-lg mb-8 max-w-2xl mx-auto">
                Subscribe to our newsletter and be the first to know about new products, exclusive deals, and tech tips.
            </p>
            <form action="<?php echo url('newsletter/subscribe'); ?>" method="POST" class="max-w-md mx-auto flex flex-col sm:flex-row gap-4">
                <input type="email" 
                       name="email" 
                       placeholder="Enter your email" 
                       required
                       class="flex-1 px-6 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <button type="submit" class="bg-primary-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-primary-700 transform hover:scale-105 transition duration-300 shadow-lg">
                    Subscribe
                </button>
            </form>
            <p class="text-sm text-gray-500 mt-4">We respect your privacy. Unsubscribe at any time.</p>
        </div>
    </div>
</section>

<!-- Custom CSS for animations -->
<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<!-- Custom JavaScript -->
<script>
// Countdown timer
function updateCountdown() {
    const now = new Date();
    const midnight = new Date();
    midnight.setHours(24, 0, 0, 0);
    
    const diff = midnight - now;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
    document.getElementById('countdown').textContent = 
        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

setInterval(updateCountdown, 1000);
updateCountdown();

// Quick view function
function quickView(productId) {
    // Implement quick view modal
    console.log('Quick view for product:', productId);
}

// Add to wishlist function
function addToWishlist(productId) {
    // Implement wishlist functionality
    console.log('Add to wishlist:', productId);
}
</script>

<?php include 'includes/footer.php'; ?>