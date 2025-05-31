<?php
// Check if user is logged in BEFORE any output
if (!app()->getAuthSession()->isLoggedIn()) {
    header("Location: " . url('login'));
    exit;
}

// Set page title
$pageTitle = 'My Account';

// Include header after all redirects
include 'includes/header.php';

// Get current user
$user = app()->getAuthSession()->getCurrentUser();
$userId = app()->getAuthSession()->getCurrentUserId();

// Get user's recent orders
$recentOrders = app()->getDB()->prepare("
    SELECT o.*, COUNT(oi.product_id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.order_id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.order_id 
    ORDER BY o.order_date DESC 
    LIMIT 5
");
$recentOrders->execute([$userId]);
$orders = $recentOrders->fetchAll(PDO::FETCH_ASSOC);

// Get order statistics
$orderStats = app()->getDB()->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_price) as total_spent,
        AVG(total_price) as avg_order_value
    FROM orders 
    WHERE user_id = ?
");
$orderStats->execute([$userId]);
$stats = $orderStats->fetch(PDO::FETCH_ASSOC);

// Handle profile update
$updateMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $customer = new Customer(app()->getDB());
        $customer->loadById($userId);
        
        $profileData = [
            'name' => $_POST['name'] ?? $user['name'],
            'email' => $_POST['email'] ?? $user['email'],
            'phone' => $_POST['phone'] ?? $user['phone'],
            'address' => $_POST['address'] ?? $user['address']
        ];
        
        if ($customer->updateProfile($profileData)) {
            $updateMessage = ['type' => 'success', 'text' => 'Profile updated successfully!'];
            // Reload user data
            $user = app()->getAuthSession()->getCurrentUser();
        } else {
            $updateMessage = ['type' => 'error', 'text' => 'Failed to update profile.'];
        }
    }
}
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">My Account</h1>
                <p class="opacity-90">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
            </div>
            <div class="hidden md:block">
                <i class="fas fa-user-circle text-6xl opacity-50"></i>
            </div>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-8">
    <?php if ($updateMessage): ?>
    <div class="mb-6 p-4 rounded-lg flex items-center <?php echo $updateMessage['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
        <i class="fas <?php echo $updateMessage['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
        <?php echo htmlspecialchars($updateMessage['text']); ?>
    </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <nav class="space-y-2" x-data="{ activeTab: 'dashboard' }">
                    <button @click="activeTab = 'dashboard'" 
                            :class="activeTab === 'dashboard' ? 'bg-primary-50 text-primary-600 border-l-4 border-primary-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-r-lg transition flex items-center">
                        <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                    </button>
                    <button @click="activeTab = 'profile'" 
                            :class="activeTab === 'profile' ? 'bg-primary-50 text-primary-600 border-l-4 border-primary-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-r-lg transition flex items-center">
                        <i class="fas fa-user mr-3"></i> Profile Settings
                    </button>
                    <a href="<?php echo url('orders'); ?>" 
                       class="block text-gray-700 hover:bg-gray-50 px-4 py-3 rounded-r-lg transition flex items-center">
                        <i class="fas fa-box mr-3"></i> Order History
                    </a>
                    <button @click="activeTab = 'addresses'" 
                            :class="activeTab === 'addresses' ? 'bg-primary-50 text-primary-600 border-l-4 border-primary-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-r-lg transition flex items-center">
                        <i class="fas fa-map-marker-alt mr-3"></i> Addresses
                    </button>
                    <button @click="activeTab = 'security'" 
                            :class="activeTab === 'security' ? 'bg-primary-50 text-primary-600 border-l-4 border-primary-600' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full text-left px-4 py-3 rounded-r-lg transition flex items-center">
                        <i class="fas fa-lock mr-3"></i> Security
                    </button>
                    <a href="<?php echo url('wishlist'); ?>" 
                       class="block text-gray-700 hover:bg-gray-50 px-4 py-3 rounded-r-lg transition flex items-center">
                        <i class="fas fa-heart mr-3"></i> Wishlist
                    </a>
                    <div class="border-t pt-2 mt-2">
                        <a href="<?php echo url('logout'); ?>" 
                           class="block text-red-600 hover:bg-red-50 px-4 py-3 rounded-r-lg transition flex items-center">
                            <i class="fas fa-sign-out-alt mr-3"></i> Logout
                        </a>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3" x-data="{ activeTab: 'dashboard' }">
            <!-- Dashboard Tab -->
            <div x-show="activeTab === 'dashboard'" x-transition>
                <!-- Statistics Cards -->
                <div class="grid md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Orders</p>
                                <p class="text-3xl font-bold text-gray-800"><?php echo intval($stats['total_orders']); ?></p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Spent</p>
                                <p class="text-3xl font-bold text-gray-800">$<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Avg. Order Value</p>
                                <p class="text-3xl font-bold text-gray-800">$<?php echo number_format($stats['avg_order_value'] ?? 0, 2); ?></p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold">Recent Orders</h2>
                            <a href="<?php echo url('orders'); ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p>
                        <a href="<?php echo url('products'); ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                            Start Shopping <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">#<?php echo $order['order_id']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $order['item_count']; ?> items
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        $<?php echo number_format($order['total_price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $statusClass = '';
                                        switch($order['status']) {
                                            case 'Delivered':
                                                $statusClass = 'bg-green-100 text-green-800';
                                                break;
                                            case 'Shipped':
                                                $statusClass = 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'Processing':
                                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                                break;
                                            default:
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?php echo url('orders/' . $order['order_id']); ?>" class="text-primary-600 hover:text-primary-700">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile Settings Tab -->
            <div x-show="activeTab === 'profile'" x-transition x-cloak>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-6">Profile Settings</h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <input type="text" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>"
                                       required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Member Since</label>
                                <input type="text" 
                                       value="<?php echo date('F Y', strtotime('-' . rand(1, 24) . ' months')); ?>"
                                       disabled
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea name="address" 
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Addresses Tab -->
            <div x-show="activeTab === 'addresses'" x-transition x-cloak>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold">Saved Addresses</h2>
                        <button class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition text-sm">
                            <i class="fas fa-plus mr-2"></i> Add New Address
                        </button>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Default Address -->
                        <div class="border rounded-lg p-6 relative">
                            <span class="absolute top-2 right-2 bg-primary-100 text-primary-600 text-xs px-2 py-1 rounded">Default</span>
                            <h3 class="font-semibold mb-2">Home</h3>
                            <p class="text-gray-600 text-sm">
                                <?php echo htmlspecialchars($user['name']); ?><br>
                                <?php echo nl2br(htmlspecialchars($user['address'] ?? 'No address saved')); ?><br>
                                <?php echo htmlspecialchars($user['phone'] ?? 'No phone number'); ?>
                            </p>
                            <div class="mt-4 flex space-x-4 text-sm">
                                <button class="text-primary-600 hover:text-primary-700">Edit</button>
                                <button class="text-red-600 hover:text-red-700">Delete</button>
                            </div>
                        </div>
                        
                        <!-- Add New Address Card -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 flex items-center justify-center">
                            <button class="text-gray-400 hover:text-gray-600 text-center">
                                <i class="fas fa-plus-circle text-4xl mb-2"></i>
                                <p>Add New Address</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div x-show="activeTab === 'security'" x-transition x-cloak>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-6">Security Settings</h2>
                    
                    <div class="space-y-6">
                        <!-- Change Password -->
                        <div>
                            <h3 class="font-medium mb-4">Change Password</h3>
                            <form class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <input type="password" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                        <input type="password" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                        <input type="password" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition">
                                    Update Password
                                </button>
                            </form>
                        </div>
                        
                        <div class="border-t pt-6">
                            <h3 class="font-medium mb-4">Two-Factor Authentication</h3>
                            <p class="text-gray-600 mb-4">Add an extra layer of security to your account.</p>
                            <button class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                                Enable 2FA
                            </button>
                        </div>
                        
                        <div class="border-t pt-6">
                            <h3 class="font-medium mb-4">Active Sessions</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium">Current Session</p>
                                        <p class="text-sm text-gray-600">Chrome on Windows • <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
                                    </div>
                                    <span class="text-green-600 text-sm">Active now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

<?php include 'includes/footer.php'; ?>