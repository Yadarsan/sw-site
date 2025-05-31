<?php
// Check admin access
if (!app()->getAuthSession()->isAdmin()) {
    header("Location: " . url('login'));
    exit;
}

// Set page title
$pageTitle = 'Inventory Management';

// Include header
include 'includes/header.php';

// Get inventory manager
$inventoryManager = app()->getInventoryManager();

// Handle stock updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_stock') {
        $productId = intval($_POST['product_id']);
        $newStock = intval($_POST['new_stock']);
        
        if ($inventoryManager->updateStock($productId, $newStock)) {
            $_SESSION['flash_message'] = 'Stock updated successfully!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Failed to update stock.';
            $_SESSION['flash_type'] = 'error';
        }
        
        header("Location: " . url('admin/inventory'));
        exit;
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Get products based on filter
$db = app()->getDB();
$query = "SELECT * FROM products";
$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(name LIKE ? OR product_id = ?)";
    $params[] = "%$search%";
    $params[] = $search;
}

switch ($filter) {
    case 'low':
        $conditions[] = "stock > 0 AND stock <= ?";
        $params[] = $inventoryManager->getLowStockThreshold();
        break;
    case 'out':
        $conditions[] = "stock = 0";
        break;
    case 'in_stock':
        $conditions[] = "stock > ?";
        $params[] = $inventoryManager->getLowStockThreshold();
        break;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY stock ASC, name ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalProducts = count($products);
$totalStock = array_sum(array_column($products, 'stock'));
$totalValue = array_sum(array_map(function($p) { return $p['stock'] * $p['price']; }, $products));
$lowStockCount = count(array_filter($products, function($p) use ($inventoryManager) { 
    return $p['stock'] > 0 && $p['stock'] <= $inventoryManager->getLowStockThreshold(); 
}));
$outOfStockCount = count(array_filter($products, function($p) { return $p['stock'] == 0; }));
?>

<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Inventory Management</h1>
                <p class="text-gray-600 mt-2">Monitor and manage product stock levels</p>
            </div>
            <button onclick="openBulkUpdateModal()" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition flex items-center">
                <i class="fas fa-boxes mr-2"></i> Bulk Update
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Products</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalProducts); ?></p>
                    </div>
                    <i class="fas fa-box text-3xl text-gray-400"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Stock</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalStock); ?></p>
                    </div>
                    <i class="fas fa-cubes text-3xl text-gray-400"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Stock Value</p>
                        <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($totalValue, 2); ?></p>
                    </div>
                    <i class="fas fa-dollar-sign text-3xl text-gray-400"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Low Stock</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $lowStockCount; ?></p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-3xl text-yellow-500"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Out of Stock</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $outOfStockCount; ?></p>
                    </div>
                    <i class="fas fa-times-circle text-3xl text-red-500"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by product name or ID..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="flex space-x-2">
                    <a href="<?php echo url('admin/inventory'); ?>" 
                       class="px-4 py-2 rounded-lg <?php echo $filter === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition">
                        All
                    </a>
                    <a href="<?php echo url('admin/inventory?filter=in_stock'); ?>" 
                       class="px-4 py-2 rounded-lg <?php echo $filter === 'in_stock' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition">
                        In Stock
                    </a>
                    <a href="<?php echo url('admin/inventory?filter=low'); ?>" 
                       class="px-4 py-2 rounded-lg <?php echo $filter === 'low' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition">
                        Low Stock (<?php echo $lowStockCount; ?>)
                    </a>
                    <a href="<?php echo url('admin/inventory?filter=out'); ?>" 
                       class="px-4 py-2 rounded-lg <?php echo $filter === 'out' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?> transition">
                        Out of Stock (<?php echo $outOfStockCount; ?>)
                    </a>
                </div>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-600 text-sm border-b bg-gray-50">
                            <th class="p-4">Product</th>
                            <th class="p-4">SKU</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Current Stock</th>
                            <th class="p-4">Stock Value</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                <i class="fas fa-boxes text-4xl mb-4 block"></i>
                                No products found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($products as $product): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="flex items-center">
                                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? asset('images/placeholder.jpg')); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="w-12 h-12 object-cover rounded mr-3"
                                             onerror="this.src='https://via.placeholder.com/48x48?text=No+Image'">
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($product['name']); ?></p>
                                            <a href="<?php echo url('product/' . $product['product_id']); ?>" 
                                               target="_blank"
                                               class="text-sm text-primary-600 hover:text-primary-700">
                                                View Product <i class="fas fa-external-link-alt text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <code class="bg-gray-100 px-2 py-1 rounded text-sm">#<?php echo str_pad($product['product_id'], 5, '0', STR_PAD_LEFT); ?></code>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm">
                                        <?php echo htmlspecialchars($product['category']); ?>
                                    </span>
                                </td>
                                <td class="p-4 font-semibold">$<?php echo number_format($product['price'], 2); ?></td>
                                <td class="p-4">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-semibold <?php echo $product['stock'] == 0 ? 'text-red-600' : ($product['stock'] <= 5 ? 'text-yellow-600' : 'text-green-600'); ?>">
                                            <?php echo $product['stock']; ?> units
                                        </span>
                                        <?php if($product['stock'] <= $inventoryManager->getLowStockThreshold() && $product['stock'] > 0): ?>
                                        <span class="text-yellow-500" title="Low Stock">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-4 font-semibold">
                                    $<?php echo number_format($product['stock'] * $product['price'], 2); ?>
                                </td>
                                <td class="p-4">
                                    <?php if($product['stock'] == 0): ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            Out of Stock
                                        </span>
                                    <?php elseif($product['stock'] <= $inventoryManager->getLowStockThreshold()): ?>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                            Low Stock
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                            In Stock
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center space-x-2">
                                        <button onclick="updateStock(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['stock']; ?>)" 
                                                class="bg-primary-600 text-white px-3 py-1 rounded text-sm hover:bg-primary-700 transition">
                                            <i class="fas fa-edit mr-1"></i> Update
                                        </button>
                                        <button onclick="viewHistory(<?php echo $product['product_id']; ?>)" 
                                                class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700 transition">
                                            <i class="fas fa-history mr-1"></i> History
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Threshold Setting -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Inventory Settings</h3>
            <div class="flex items-center space-x-4">
                <label class="text-gray-700">Low Stock Threshold:</label>
                <input type="number" 
                       id="lowStockThreshold" 
                       value="<?php echo $inventoryManager->getLowStockThreshold(); ?>" 
                       min="1" 
                       class="w-20 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <button onclick="updateThreshold()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition">
                    Update Threshold
                </button>
                <p class="text-sm text-gray-500">Products with stock at or below this level will be marked as low stock</p>
            </div>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="updateStockModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4">Update Stock</h3>
            <form method="POST" onsubmit="return validateStockUpdate()">
                <input type="hidden" name="action" value="update_stock">
                <input type="hidden" name="product_id" id="update_product_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Product</label>
                    <p id="update_product_name" class="font-medium"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Current Stock</label>
                    <p id="current_stock" class="font-medium text-lg"></p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">New Stock Level</label>
                    <input type="number" 
                           name="new_stock" 
                           id="new_stock" 
                           min="0" 
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUpdateModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                        Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateStock(productId, productName, currentStock) {
    document.getElementById('update_product_id').value = productId;
    document.getElementById('update_product_name').textContent = productName;
    document.getElementById('current_stock').textContent = currentStock + ' units';
    document.getElementById('new_stock').value = currentStock;
    document.getElementById('updateStockModal').classList.remove('hidden');
}

function closeUpdateModal() {
    document.getElementById('updateStockModal').classList.add('hidden');
}

function validateStockUpdate() {
    const newStock = parseInt(document.getElementById('new_stock').value);
    if (newStock < 0) {
        alert('Stock cannot be negative');
        return false;
    }
    return true;
}

function updateThreshold() {
    const threshold = document.getElementById('lowStockThreshold').value;
    // TODO: Implement AJAX call to update threshold
    alert('Threshold update functionality to be implemented');
}

function viewHistory(productId) {
    // TODO: Implement stock history view
    alert('Stock history view to be implemented');
}

function openBulkUpdateModal() {
    // TODO: Implement bulk update functionality
    alert('Bulk update functionality to be implemented');
}

// Close modal on outside click
document.getElementById('updateStockModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUpdateModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>