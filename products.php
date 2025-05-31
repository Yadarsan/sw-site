<?php
// Set page title
$pageTitle = 'Products';

// Include header
include 'includes/header.php';

// Get page number
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$itemsPerPage = 12;

// Get category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;

// Get sort order
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// Get products
if ($categoryFilter) {
    $result = app()->getProductCatalog()->filterByCategory($categoryFilter, $page, $itemsPerPage);
} else {
    $result = app()->getProductCatalog()->listProducts($page, $itemsPerPage);
}

$products = $result['products'];
$pagination = $result['pagination'];

// Get all categories for filter
$categories = app()->getProductCatalog()->getAllCategories();
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold mb-2">
                    <?php echo $categoryFilter ? htmlspecialchars($categoryFilter) : 'All Products'; ?>
                </h1>
                <nav class="flex items-center text-sm">
                    <a href="<?php echo url(); ?>" class="hover:text-primary-200">Home</a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <span>Products</span>
                    <?php if($categoryFilter): ?>
                        <i class="fas fa-chevron-right mx-2 text-xs"></i>
                        <span><?php echo htmlspecialchars($categoryFilter); ?></span>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="mt-4 md:mt-0">
                <p class="text-lg">
                    Showing <?php echo ($page - 1) * $itemsPerPage + 1; ?>-<?php echo min($page * $itemsPerPage, $pagination['total_items']); ?> 
                    of <?php echo $pagination['total_items']; ?> products
                </p>
            </div>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:w-1/4" x-data="{ filtersOpen: false }">
            <!-- Mobile Filter Toggle -->
            <button @click="filtersOpen = !filtersOpen" 
                    class="lg:hidden w-full bg-primary-600 text-white py-3 rounded-lg mb-4 flex items-center justify-center">
                <i class="fas fa-filter mr-2"></i> Filters
            </button>
            
            <div class="lg:block" :class="{ 'hidden': !filtersOpen }">
                <!-- Categories Filter -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="font-semibold text-lg mb-4 flex items-center">
                        <i class="fas fa-list-ul mr-2 text-primary-600"></i> Categories
                    </h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="<?php echo url('products'); ?>" 
                               class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-100 transition <?php echo !$categoryFilter ? 'bg-primary-100 text-primary-600' : ''; ?>">
                                <span>All Products</span>
                                <span class="text-sm text-gray-500"><?php echo $pagination['total_items']; ?></span>
                            </a>
                        </li>
                        <?php foreach($categories as $category): ?>
                        <li>
                            <a href="<?php echo url('products?category=' . urlencode($category)); ?>" 
                               class="flex items-center justify-between py-2 px-3 rounded hover:bg-gray-100 transition <?php echo $categoryFilter === $category ? 'bg-primary-100 text-primary-600' : ''; ?>">
                                <span><?php echo htmlspecialchars($category); ?></span>
                                <span class="text-sm text-gray-500">
                                    <?php echo app()->getProductCatalog()->filterByCategory($category, 1, 1)['pagination']['total_items']; ?>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Price Filter -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6" x-data="{ 
                    minPrice: 0, 
                    maxPrice: 5000,
                    selectedMin: <?php echo isset($_GET['min_price']) ? intval($_GET['min_price']) : 0; ?>,
                    selectedMax: <?php echo isset($_GET['max_price']) ? intval($_GET['max_price']) : 5000; ?>
                }">
                    <h3 class="font-semibold text-lg mb-4 flex items-center">
                        <i class="fas fa-dollar-sign mr-2 text-primary-600"></i> Price Range
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   x-model="selectedMin" 
                                   min="0" 
                                   max="5000"
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <span>-</span>
                            <input type="number" 
                                   x-model="selectedMax" 
                                   min="0" 
                                   max="5000"
                                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="relative pt-1">
                            <input type="range" 
                                   x-model="selectedMin" 
                                   min="0" 
                                   max="5000" 
                                   step="50"
                                   class="w-full">
                            <input type="range" 
                                   x-model="selectedMax" 
                                   min="0" 
                                   max="5000" 
                                   step="50"
                                   class="w-full mt-2">
                        </div>
                        <button @click="window.location.href = `<?php echo url('products'); ?>?<?php echo $categoryFilter ? 'category=' . urlencode($categoryFilter) . '&' : ''; ?>min_price=${selectedMin}&max_price=${selectedMax}`"
                                class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700 transition">
                            Apply Filter
                        </button>
                    </div>
                </div>
                
                <!-- Availability Filter -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-lg mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-2 text-primary-600"></i> Availability
                    </h3>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               class="mr-3 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                               <?php echo isset($_GET['in_stock']) ? 'checked' : ''; ?>
                               onchange="toggleInStock(this)">
                        <span>In Stock Only</span>
                    </label>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="lg:w-3/4">
            <!-- Sort and View Options -->
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Sort by:</span>
                        <select onchange="sortProducts(this.value)" 
                                class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="default" <?php echo $sort === 'default' ? 'selected' : ''; ?>>Default</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="setView('grid')" class="p-2 rounded hover:bg-gray-100 transition">
                            <i class="fas fa-th-large text-gray-600"></i>
                        </button>
                        <button onclick="setView('list')" class="p-2 rounded hover:bg-gray-100 transition">
                            <i class="fas fa-list text-gray-600"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(empty($products)): ?>
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                        <p class="text-xl text-gray-500">No products found</p>
                        <a href="<?php echo url('products'); ?>" class="text-primary-600 hover:underline mt-2 inline-block">
                            Clear filters
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <div class="product-card bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <div class="relative">
                            <a href="<?php echo url('product/' . $product['product_id']); ?>">
                                <img src="<?php echo htmlspecialchars($product['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-64 object-cover group-hover:scale-105 transition duration-300"
                                     onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'">
                            </a>
                            
                            <?php if($product['stock'] <= 5 && $product['stock'] > 0): ?>
                            <span class="absolute top-3 left-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full">
                                Only <?php echo $product['stock']; ?> left!
                            </span>
                            <?php elseif($product['stock'] == 0): ?>
                            <span class="absolute top-3 left-3 bg-gray-500 text-white text-xs px-3 py-1 rounded-full">
                                Out of Stock
                            </span>
                            <?php endif; ?>
                            
                            <!-- Wishlist Button -->
                            <button onclick="addToWishlist(<?php echo $product['product_id']; ?>)" 
                                    class="absolute top-3 right-3 bg-white text-gray-600 p-2 rounded-full opacity-0 group-hover:opacity-100 hover:bg-red-500 hover:text-white transition-all duration-300 shadow-md">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="p-5">
                            <p class="text-sm text-gray-500 mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                            <h3 class="font-semibold text-lg mb-2 line-clamp-2">
                                <a href="<?php echo url('product/' . $product['product_id']); ?>" class="hover:text-primary-600 transition">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            
                            <!-- Rating -->
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400 text-sm">
                                    <?php 
                                    $rating = rand(35, 50) / 10; // Random rating between 3.5 and 5.0
                                    $fullStars = floor($rating);
                                    $hasHalfStar = $rating - $fullStars >= 0.5;
                                    
                                    for($i = 0; $i < $fullStars; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    
                                    <?php if($hasHalfStar): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php endif; ?>
                                    
                                    <?php for($i = ceil($rating); $i < 5; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-gray-500 text-sm ml-2">(<?php echo rand(10, 200); ?>)</span>
                            </div>
                            
                            <!-- Price and Add to Cart -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-2xl font-bold text-primary-600">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if(rand(0, 1)): // Random "was" price ?>
                                    <span class="text-sm text-gray-400 line-through ml-2">$<?php echo number_format($product['price'] * 1.2, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if($product['stock'] > 0): ?>
                                <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                                        class="bg-primary-600 text-white p-3 rounded-lg hover:bg-primary-700 transition duration-300">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <?php else: ?>
                                <button disabled class="bg-gray-300 text-gray-500 p-3 rounded-lg cursor-not-allowed">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($pagination['total_pages'] > 1): ?>
            <div class="mt-12 flex justify-center">
                <nav class="flex items-center space-x-2">
                    <!-- Previous Page -->
                    <?php if($page > 1): ?>
                    <a href="<?php echo url('products?page=' . ($page - 1) . ($categoryFilter ? '&category=' . urlencode($categoryFilter) : '') . ($sort !== 'default' ? '&sort=' . $sort : '')); ?>" 
                       class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php else: ?>
                    <span class="px-4 py-2 bg-gray-100 border rounded-lg text-gray-400 cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($pagination['total_pages'], $page + 2);
                    
                    if($startPage > 1): ?>
                        <a href="<?php echo url('products?page=1' . ($categoryFilter ? '&category=' . urlencode($categoryFilter) : '') . ($sort !== 'default' ? '&sort=' . $sort : '')); ?>" 
                           class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">1</a>
                        <?php if($startPage > 2): ?>
                            <span class="px-2">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if($i == $page): ?>
                            <span class="px-4 py-2 bg-primary-600 text-white border border-primary-600 rounded-lg">
                                <?php echo $i; ?>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo url('products?page=' . $i . ($categoryFilter ? '&category=' . urlencode($categoryFilter) : '') . ($sort !== 'default' ? '&sort=' . $sort : '')); ?>" 
                               class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if($endPage < $pagination['total_pages']): ?>
                        <?php if($endPage < $pagination['total_pages'] - 1): ?>
                            <span class="px-2">...</span>
                        <?php endif; ?>
                        <a href="<?php echo url('products?page=' . $pagination['total_pages'] . ($categoryFilter ? '&category=' . urlencode($categoryFilter) : '') . ($sort !== 'default' ? '&sort=' . $sort : '')); ?>" 
                           class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">
                            <?php echo $pagination['total_pages']; ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Next Page -->
                    <?php if($page < $pagination['total_pages']): ?>
                    <a href="<?php echo url('products?page=' . ($page + 1) . ($categoryFilter ? '&category=' . urlencode($categoryFilter) : '') . ($sort !== 'default' ? '&sort=' . $sort : '')); ?>" 
                       class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php else: ?>
                    <span class="px-4 py-2 bg-gray-100 border rounded-lg text-gray-400 cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <span>Loading products...</span>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* List view styles */
.list-view .product-card {
    display: grid;
    grid-template-columns: 200px 1fr;
}

.list-view .product-card img {
    height: 200px;
}
</style>

<script>
// Sort products
function sortProducts(sortType) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('sort', sortType);
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    window.location.href = currentUrl.toString();
}

// Toggle in stock filter
function toggleInStock(checkbox) {
    const currentUrl = new URL(window.location);
    if (checkbox.checked) {
        currentUrl.searchParams.set('in_stock', '1');
    } else {
        currentUrl.searchParams.delete('in_stock');
    }
    currentUrl.searchParams.set('page', '1'); // Reset to first page
    window.location.href = currentUrl.toString();
}

// Set view type (grid/list)
function setView(viewType) {
    const productsGrid = document.getElementById('products-grid');
    if (viewType === 'list') {
        productsGrid.classList.add('list-view');
        productsGrid.classList.remove('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3');
        productsGrid.classList.add('space-y-4');
    } else {
        productsGrid.classList.remove('list-view', 'space-y-4');
        productsGrid.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3');
    }
    
    // Save preference
    localStorage.setItem('productsView', viewType);
}

// Load saved view preference
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('productsView');
    if (savedView === 'list') {
        setView('list');
    }
});

// Add to wishlist
function addToWishlist(productId) {
    // Show loading
    showNotification('Adding to wishlist...', 'info');
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Product added to wishlist!', 'success');
    }, 500);
}
</script>

<?php include 'includes/footer.php'; ?>