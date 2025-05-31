<?php
// Check admin access
if (!app()->getAuthSession()->isAdmin()) {
    header("Location: " . url('login'));
    exit;
}

// Determine if we're editing or adding
$isEdit = isset($_GET['id']);
$productId = $isEdit ? intval($_GET['id']) : null;
$product = null;

if ($isEdit) {
    // Load product data
    $product = app()->getProductCatalog()->getProductDetails($productId);
    if (!$product) {
        $_SESSION['flash_message'] = 'Product not found.';
        $_SESSION['flash_type'] = 'error';
        header("Location: " . url('admin/products'));
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productData = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => floatval($_POST['price'] ?? 0),
        'stock' => intval($_POST['stock'] ?? 0),
        'category' => $_POST['category'] ?? '',
        'image_url' => $_POST['image_url'] ?? ''
    ];
    
    // Validate
    $errors = [];
    if (empty($productData['name'])) {
        $errors[] = 'Product name is required.';
    }
    if ($productData['price'] <= 0) {
        $errors[] = 'Price must be greater than 0.';
    }
    if ($productData['stock'] < 0) {
        $errors[] = 'Stock cannot be negative.';
    }
    
    // Handle new category
    if ($_POST['category'] === '__new__' && !empty($_POST['new_category'])) {
        $productData['category'] = trim($_POST['new_category']);
    }
    
    if (empty($productData['category'])) {
        $errors[] = 'Category is required.';
    }
    
    if (empty($errors)) {
        $admin = new Administrator(app()->getDB());
        
        if ($isEdit) {
            $productData['product_id'] = $productId;
            $result = $admin->manageProduct('update', $productData);
        } else {
            $result = $admin->manageProduct('add', $productData);
        }
        
        if ($result) {
            $_SESSION['flash_message'] = $isEdit ? 'Product updated successfully!' : 'Product added successfully!';
            $_SESSION['flash_type'] = 'success';
            header("Location: " . url('admin/products'));
            exit;
        } else {
            $errors[] = 'Failed to save product.';
        }
    }
}

// Set page title
$pageTitle = $isEdit ? 'Edit Product' : 'Add New Product';

// Include header
include 'includes/header.php';

// Get categories
$categories = app()->getProductCatalog()->getAllCategories();
?>

<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center text-gray-600 text-sm mb-4">
                <a href="<?php echo url('admin/dashboard'); ?>" class="hover:text-primary-600">Dashboard</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="<?php echo url('admin/products'); ?>" class="hover:text-primary-600">Products</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-800"><?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?></span>
            </div>
            <h1 class="text-3xl font-bold text-gray-800"><?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?></h1>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <div>
                    <p class="font-semibold text-red-800">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-700 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Product Form -->
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm">
            <div class="p-6 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Product Name <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? $product['name'] ?? ''); ?>"
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="Enter product name">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Category <span class="text-red-500">*</span></label>
                            <div class="flex space-x-2">
                                <select name="category" 
                                        id="categorySelect"
                                        required
                                        class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>" 
                                                <?php echo (($_POST['category'] ?? $product['category'] ?? '') === $category) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="__new__">+ Add New Category</option>
                                </select>
                                <input type="text" 
                                       id="newCategoryInput"
                                       name="new_category"
                                       placeholder="New category name"
                                       class="hidden px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Description</label>
                    <textarea name="description" 
                              rows="4"
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Enter product description"><?php echo htmlspecialchars($_POST['description'] ?? $product['description'] ?? ''); ?></textarea>
                </div>

                <!-- Pricing & Inventory -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Pricing & Inventory</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                                <input type="number" 
                                       name="price" 
                                       value="<?php echo $_POST['price'] ?? $product['price'] ?? ''; ?>"
                                       required
                                       min="0.01"
                                       step="0.01"
                                       class="w-full pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                       placeholder="0.00">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Stock Quantity <span class="text-red-500">*</span></label>
                            <input type="number" 
                                   name="stock" 
                                   value="<?php echo $_POST['stock'] ?? $product['stock'] ?? '0'; ?>"
                                   required
                                   min="0"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="0">
                        </div>
                    </div>
                </div>

                <!-- Product Image -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Product Image</h3>
                    <div class="grid md:grid-cols-2 gap-6 items-start">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Image URL</label>
                            <input type="url" 
                                   name="image_url" 
                                   value="<?php echo htmlspecialchars($_POST['image_url'] ?? $product['image_url'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="https://example.com/image.jpg"
                                   onchange="updateImagePreview(this.value)">
                            <p class="text-sm text-gray-500 mt-2">Enter a URL to an existing image or upload a new one below.</p>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Preview</label>
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <img id="imagePreview" 
                                     src="<?php echo htmlspecialchars($_POST['image_url'] ?? $product['image_url'] ?? ''); ?>" 
                                     alt="Product preview"
                                     class="w-full h-48 object-cover rounded"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-gray-700 font-medium mb-2">Or Upload New Image</label>
                        <input type="file" 
                               name="product_image" 
                               accept="image/*"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <p class="text-sm text-gray-500 mt-2">Accepted formats: JPG, PNG, GIF. Max size: 5MB</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between">
                <a href="<?php echo url('admin/products'); ?>" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </a>
                <div class="space-x-3">
                    <?php if ($isEdit): ?>
                    <a href="<?php echo url('product/' . $productId); ?>" 
                       target="_blank"
                       class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-eye mr-2"></i> View Product
                    </a>
                    <?php endif; ?>
                    <button type="submit" 
                            class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition">
                        <i class="fas fa-save mr-2"></i> <?php echo $isEdit ? 'Update Product' : 'Add Product'; ?>
                    </button>
                </div>
            </div>
        </form>

        <!-- Additional Options -->
        <?php if (!$isEdit): ?>
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                <div>
                    <p class="text-blue-800">
                        <strong>Tip:</strong> After adding this product, you can manage its inventory from the 
                        <a href="<?php echo url('admin/inventory'); ?>" class="underline">Inventory Management</a> page.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateImagePreview(url) {
    const preview = document.getElementById('imagePreview');
    if (url) {
        preview.src = url;
    } else {
        preview.src = 'https://via.placeholder.com/300x200?text=No+Image';
    }
}

// Handle file upload preview
document.querySelector('input[type="file"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Handle category selection
document.getElementById('categorySelect').addEventListener('change', function() {
    const newCategoryInput = document.getElementById('newCategoryInput');
    if (this.value === '__new__') {
        newCategoryInput.classList.remove('hidden');
        newCategoryInput.required = true;
        this.required = false;
    } else {
        newCategoryInput.classList.add('hidden');
        newCategoryInput.required = false;
        this.required = true;
    }
});
</script>

<?php include 'includes/footer.php'; ?>