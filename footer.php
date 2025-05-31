</main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <!-- Main Footer Content -->
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt text-white text-xl"></i>
                        </div>
                        <span class="text-xl font-bold text-white">AWE Electronics</span>
                    </div>
                    <p class="mb-4">Your trusted partner for quality electronics and exceptional service since 2020.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary-600 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary-600 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary-600 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary-600 transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo url('about'); ?>" class="hover:text-primary-400 transition">About Us</a></li>
                        <li><a href="<?php echo url('products'); ?>" class="hover:text-primary-400 transition">All Products</a></li>
                        <li><a href="<?php echo url('deals'); ?>" class="hover:text-primary-400 transition">Special Offers</a></li>
                        <li><a href="<?php echo url('blog'); ?>" class="hover:text-primary-400 transition">Blog</a></li>
                        <li><a href="<?php echo url('careers'); ?>" class="hover:text-primary-400 transition">Careers</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo url('contact'); ?>" class="hover:text-primary-400 transition">Contact Us</a></li>
                        <li><a href="<?php echo url('shipping'); ?>" class="hover:text-primary-400 transition">Shipping Info</a></li>
                        <li><a href="<?php echo url('returns'); ?>" class="hover:text-primary-400 transition">Returns & Exchanges</a></li>
                        <li><a href="<?php echo url('warranty'); ?>" class="hover:text-primary-400 transition">Warranty</a></li>
                        <li><a href="<?php echo url('faq'); ?>" class="hover:text-primary-400 transition">FAQ</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-white font-semibold text-lg mb-4">Stay Updated</h3>
                    <p class="mb-4">Subscribe to get special offers, free giveaways, and new arrivals!</p>
                    <form action="<?php echo url('newsletter/subscribe'); ?>" method="POST" class="space-y-2">
                        <input type="email" 
                               name="email" 
                               placeholder="Your email address" 
                               required
                               class="w-full px-4 py-2 bg-gray-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-white placeholder-gray-400">
                        <button type="submit" class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 transition font-medium">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Methods & Security -->
        <div class="border-t border-gray-800">
            <div class="container mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div>
                        <p class="text-sm mb-2">Secure Payment Methods</p>
                        <div class="flex space-x-4">
                            <i class="fab fa-cc-visa text-2xl"></i>
                            <i class="fab fa-cc-mastercard text-2xl"></i>
                            <i class="fab fa-cc-paypal text-2xl"></i>
                            <i class="fab fa-cc-stripe text-2xl"></i>
                            <i class="fab fa-cc-apple-pay text-2xl"></i>
                        </div>
                    </div>
                    <div class="flex items-center space-x-6 text-sm">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-lock text-green-400"></i>
                            <span>Secure Checkout</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-truck text-blue-400"></i>
                            <span>Fast Shipping</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-undo text-yellow-400"></i>
                            <span>Easy Returns</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="bg-gray-950 py-4">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                    <p>&copy; <?php echo date('Y'); ?> AWE Electronics. All rights reserved.</p>
                    <div class="flex space-x-6 mt-2 md:mt-0">
                        <a href="<?php echo url('privacy'); ?>" class="hover:text-primary-400 transition">Privacy Policy</a>
                        <a href="<?php echo url('terms'); ?>" class="hover:text-primary-400 transition">Terms of Service</a>
                        <a href="<?php echo url('sitemap'); ?>" class="hover:text-primary-400 transition">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button x-data="{ show: false }"
            x-show="show"
            x-on:scroll.window="show = window.pageYOffset > 300"
            x-on:click="window.scrollTo({top: 0, behavior: 'smooth'})"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-8 right-8 bg-primary-600 text-white w-12 h-12 rounded-full shadow-lg hover:bg-primary-700 transition flex items-center justify-center z-40"
            x-cloak>
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Mobile Bottom Navigation (for mobile users) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-40">
        <div class="grid grid-cols-5 gap-1">
            <a href="<?php echo url(); ?>" class="flex flex-col items-center py-2 text-gray-600 hover:text-primary-600 transition">
                <i class="fas fa-home text-xl"></i>
                <span class="text-xs mt-1">Home</span>
            </a>
            <a href="<?php echo url('categories'); ?>" class="flex flex-col items-center py-2 text-gray-600 hover:text-primary-600 transition">
                <i class="fas fa-th-large text-xl"></i>
                <span class="text-xs mt-1">Categories</span>
            </a>
            <a href="<?php echo url('cart'); ?>" class="flex flex-col items-center py-2 text-gray-600 hover:text-primary-600 transition relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                <span class="text-xs mt-1">Cart</span>
                <?php if(app()->getShoppingCart()->getItemCount() > 0): ?>
                    <span class="absolute top-1 right-1/4 bg-primary-600 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                        <?php echo app()->getShoppingCart()->getItemCount(); ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?php echo url('wishlist'); ?>" class="flex flex-col items-center py-2 text-gray-600 hover:text-primary-600 transition">
                <i class="fas fa-heart text-xl"></i>
                <span class="text-xs mt-1">Wishlist</span>
            </a>
            <a href="<?php echo url(app()->getAuthSession()->isLoggedIn() ? 'account' : 'login'); ?>" class="flex flex-col items-center py-2 text-gray-600 hover:text-primary-600 transition">
                <i class="fas fa-user text-xl"></i>
                <span class="text-xs mt-1">Account</span>
            </a>
        </div>
    </div>

    <!-- Add extra padding for mobile bottom navigation -->
    <div class="h-16 lg:hidden"></div>

    <!-- Custom JavaScript -->
    <script>
        // Initialize Alpine.js stores if needed
        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', {
                count: <?php echo app()->getShoppingCart()->getItemCount(); ?>,
                updateCount(newCount) {
                    this.count = newCount;
                }
            });
        });

        // AJAX Cart functionality
        function addToCart(productId, quantity = 1) {
            // Validate inputs
            if (!productId || productId <= 0) {
                showNotification('Invalid product', 'error');
                return;
            }
            
            quantity = parseInt(quantity) || 1;
            if (quantity < 1) {
                showNotification('Invalid quantity', 'error');
                return;
            }
            
            fetch('<?php echo url('ajax/cart'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => {
                // First check if response is ok
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Try to parse as JSON
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    const cartCountElements = document.querySelectorAll('[data-cart-count]');
                    cartCountElements.forEach(el => {
                        el.textContent = data.cart_count;
                    });
                    
                    // Update Alpine store if available
                    if (typeof Alpine !== 'undefined' && Alpine.store('cart')) {
                        Alpine.store('cart').updateCount(data.cart_count);
                    }
                    
                    // Show success message
                    showNotification('Product added to cart!', 'success');
                } else {
                    showNotification(data.message || 'Error adding to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Cart Error:', error);
                showNotification('Failed to add to cart. Please try again.', 'error');
            });
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 z-50 bg-white rounded-lg shadow-lg p-4 transform transition-all duration-300`;
            
            const iconClass = {
                'success': 'fas fa-check-circle text-green-500',
                'error': 'fas fa-times-circle text-red-500',
                'warning': 'fas fa-exclamation-triangle text-yellow-500',
                'info': 'fas fa-info-circle text-blue-500'
            }[type] || 'fas fa-info-circle text-blue-500';
            
            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="${iconClass} text-xl"></i>
                    <p class="text-gray-700">${message}</p>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.add('translate-x-0');
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>