<?php
// Get current user and cart info
$authSession = app()->getAuthSession();
$currentUser = $authSession->getCurrentUser();
$isAdmin = $authSession->isAdmin();
$cart = app()->getShoppingCart();
$cartCount = $cart->getItemCount();

// Get categories for navigation
$categories = app()->getProductCatalog()->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>AWE Electronics</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="h-full bg-gray-50">
    <!-- Top Bar -->
    <div class="bg-gray-900 text-white text-sm">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-2">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center">
                        <i class="fas fa-phone mr-2"></i>
                        <span class="hidden sm:inline">(123) 456-7890</span>
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-envelope mr-2"></i>
                        <span class="hidden sm:inline">info@aweelectronics.com</span>
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo url('track-order'); ?>" class="hover:text-primary-400 transition">
                        <i class="fas fa-shipping-fast mr-1"></i>
                        <span class="hidden sm:inline">Track Order</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileMenu: false, searchOpen: false, userMenu: false, categoryMenu: false }">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="<?php echo url(); ?>" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt text-white text-xl"></i>
                        </div>
                        <span class="text-xl font-bold gradient-text">AWE Electronics</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden lg:flex items-center space-x-8">
                    <a href="<?php echo url(); ?>" class="text-gray-700 hover:text-primary-600 font-medium transition">Home</a>
                    
                    <!-- Categories Dropdown -->
                    <div class="relative" @mouseenter="categoryMenu = true" @mouseleave="categoryMenu = false">
                        <button class="text-gray-700 hover:text-primary-600 font-medium transition flex items-center space-x-1">
                            <span>Categories</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div x-show="categoryMenu" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute left-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 z-50"
                             x-cloak>
                            <?php foreach($categories as $category): ?>
                                <a href="<?php echo url('category/' . urlencode($category)); ?>" 
                                   class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                                    <?php echo htmlspecialchars($category); ?>
                                </a>
                            <?php endforeach; ?>
                            <div class="border-t mt-2 pt-2">
                                <a href="<?php echo url('products'); ?>" 
                                   class="block px-4 py-2 text-primary-600 font-medium hover:bg-primary-50 transition">
                                    View All Products
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <a href="<?php echo url('products'); ?>" class="text-gray-700 hover:text-primary-600 font-medium transition">Products</a>
                    <a href="<?php echo url('deals'); ?>" class="text-gray-700 hover:text-primary-600 font-medium transition">Deals</a>
                    <a href="<?php echo url('about'); ?>" class="text-gray-700 hover:text-primary-600 font-medium transition">About</a>
                    <a href="<?php echo url('contact'); ?>" class="text-gray-700 hover:text-primary-600 font-medium transition">Contact</a>
                </nav>

                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Search Button -->
                    <button @click="searchOpen = !searchOpen" class="text-gray-600 hover:text-primary-600 transition">
                        <i class="fas fa-search text-xl"></i>
                    </button>

                    <!-- Cart -->
                    <a href="<?php echo url('cart'); ?>" class="relative text-gray-600 hover:text-primary-600 transition">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <?php if($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-primary-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" data-cart-count>
                                <?php echo $cartCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Menu -->
                    <?php if($currentUser): ?>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-primary-600 transition">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-primary-600"></i>
                                </div>
                                <span class="hidden md:block font-medium"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50"
                                 x-cloak>
                                <?php if($isAdmin): ?>
                                    <a href="<?php echo url('admin/dashboard'); ?>" class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                                        <i class="fas fa-tachometer-alt mr-2"></i> Admin Dashboard
                                    </a>
                                    <div class="border-t my-2"></div>
                                <?php endif; ?>
                                <a href="<?php echo url('account'); ?>" class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                                    <i class="fas fa-user-circle mr-2"></i> My Account
                                </a>
                                <a href="<?php echo url('orders'); ?>" class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                                    <i class="fas fa-box mr-2"></i> My Orders
                                </a>
                                <a href="<?php echo url('wishlist'); ?>" class="block px-4 py-2 text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                                    <i class="fas fa-heart mr-2"></i> Wishlist
                                </a>
                                <div class="border-t my-2"></div>
                                <a href="<?php echo url('logout'); ?>" class="block px-4 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo url('login'); ?>" class="hidden md:flex items-center space-x-2 bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>

                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenu = !mobileMenu" class="lg:hidden text-gray-600 hover:text-primary-600 transition">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div x-show="searchOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-full"
             class="absolute top-full left-0 w-full bg-white border-t shadow-lg z-40"
             x-cloak>
            <div class="container mx-auto px-4 py-4">
                <form action="<?php echo url('search'); ?>" method="GET" class="relative">
                    <input type="text" 
                           name="q" 
                           placeholder="Search for products..."
                           class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           autofocus>
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700 transition">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenu" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
             @click="mobileMenu = false"
             x-cloak>
        </div>
        
        <div x-show="mobileMenu" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="lg:hidden fixed left-0 top-0 h-full w-64 bg-white shadow-xl z-50"
             x-cloak>
            <div class="p-4">
                <div class="flex items-center justify-between mb-8">
                    <span class="text-xl font-bold gradient-text">AWE Electronics</span>
                    <button @click="mobileMenu = false" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <nav class="space-y-4">
                    <a href="<?php echo url(); ?>" class="block text-gray-700 hover:text-primary-600 font-medium">Home</a>
                    <a href="<?php echo url('products'); ?>" class="block text-gray-700 hover:text-primary-600 font-medium">Products</a>
                    <a href="<?php echo url('categories'); ?>" class="block text-gray-700 hover:text-primary-600 font-medium">Categories</a>
                    <a href="<?php echo url('deals'); ?>" class="block text-gray-700 hover:text-primary-600 font-medium">Deals</a>
                    <a href="<?php echo url('about'); ?>" class="block text-gray-700 hover:text-primary-600 font-medium">About</a>
                    <a href="<?php echo url('contact'); ?>" class="block text-gray-700 hover:text-primary-600 font-medium">Contact</a>
                    
                    <?php if(!$currentUser): ?>
                        <div class="pt-4 border-t">
                            <a href="<?php echo url('login'); ?>" class="block bg-primary-600 text-white text-center px-4 py-2 rounded-lg hover:bg-primary-700 transition">
                                Login / Register
                            </a>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-20 right-4 z-50">
            <div class="bg-white rounded-lg shadow-lg p-4 flex items-center space-x-3 
                        <?php echo $_SESSION['flash_type'] === 'success' ? 'border-l-4 border-green-500' : ''; ?>
                        <?php echo $_SESSION['flash_type'] === 'warning' ? 'border-l-4 border-yellow-500' : ''; ?>
                        <?php echo $_SESSION['flash_type'] === 'danger' ? 'border-l-4 border-red-500' : ''; ?>
                        <?php echo $_SESSION['flash_type'] === 'info' ? 'border-l-4 border-blue-500' : ''; ?>">
                <div>
                    <?php if($_SESSION['flash_type'] === 'success'): ?>
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    <?php elseif($_SESSION['flash_type'] === 'warning'): ?>
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                    <?php elseif($_SESSION['flash_type'] === 'danger'): ?>
                        <i class="fas fa-times-circle text-red-500 text-xl"></i>
                    <?php else: ?>
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <p class="text-gray-700"><?php echo htmlspecialchars($_SESSION['flash_message']); ?></p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="min-h-screen">