<?php
// Check admin access
if (!app()->getAuthSession()->isAdmin()) {
    header("Location: " . url('login'));
    exit;
}

// Set page title
$pageTitle = 'Customer Management';

// Include header
include 'includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $db = app()->getDB();
    
    switch ($_POST['action']) {
        case 'toggle_status':
            $userId = intval($_POST['user_id']);
            // You could add an 'active' field to customers table
            $_SESSION['flash_message'] = 'Customer status updated successfully!';
            $_SESSION['flash_type'] = 'success';
            break;
            
        case 'reset_password':
            $userId = intval($_POST['user_id']);
            // Generate temporary password
            $tempPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("UPDATE customers SET password = ? WHERE user_id = ?");
            if ($stmt->execute([$hashedPassword, $userId])) {
                $_SESSION['flash_message'] = "Password reset successfully. Temporary password: $tempPassword";
                $_SESSION['flash_type'] = 'success';
            }
            break;
    }
    
    header("Location: " . url('admin/customers'));
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$itemsPerPage = 20;

// Build query
$db = app()->getDB();
$query = "SELECT c.*, 
          COUNT(DISTINCT o.order_id) as total_orders,
          COALESCE(SUM(o.total_price), 0) as total_spent,
          MAX(o.order_date) as last_order_date
          FROM customers c
          LEFT JOIN orders o ON c.user_id = o.user_id";

$conditions = [];
$params = [];

if ($search) {
    $conditions[] = "(c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " GROUP BY c.user_id";

// Sort
switch ($sortBy) {
    case 'name':
        $query .= " ORDER BY c.name ASC";
        break;
    case 'spent':
        $query .= " ORDER BY total_spent DESC";
        break;
    case 'orders':
        $query .= " ORDER BY total_orders DESC";
        break;
    case 'newest':
    default:
        $query .= " ORDER BY c.user_id DESC";
        break;
}

// Get total count for pagination
$countQuery = "SELECT COUNT(DISTINCT c.user_id) as total FROM customers c";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}
$countStmt = $db->prepare($countQuery);
$countStmt->execute($params);
$totalCustomers = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Add pagination
$offset = ($page - 1) * $itemsPerPage;
$query .= " LIMIT ? OFFSET ?";
$params[] = $itemsPerPage;
$params[] = $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate pagination
$totalPages = ceil($totalCustomers / $itemsPerPage);

// Get statistics
$stats = $db->query("
    SELECT 
        COUNT(DISTINCT c.user_id) as total_customers,
        COUNT(DISTINCT CASE WHEN o.order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN c.user_id END) as active_customers,
        COUNT(DISTINCT CASE WHEN o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN c.user_id END) as new_customers,
        AVG(order_totals.total_spent) as avg_customer_value
    FROM customers c
    LEFT JOIN orders o ON c.user_id = o.user_id
    LEFT JOIN (
        SELECT user_id, SUM(total_price) as total_spent
        FROM orders
        WHERE status != 'Cancelled'
        GROUP BY user_id
    ) order_totals ON c.user_id = order_totals.user_id
")->fetch(PDO::FETCH_ASSOC);
?>

<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Customer Management</h1>
                <p class="text-gray-600 mt-2">Manage your customer database</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="exportCustomers()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center">
                    <i class="fas fa-download mr-2"></i> Export
                </button>
                <button onclick="openEmailModal()" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition flex items-center">
                    <i class="fas fa-envelope mr-2"></i> Email Campaign
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_customers']); ?></p>
                    </div>
                    <i class="fas fa-users text-3xl text-primary-600"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active (30 days)</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['active_customers']); ?></p>
                    </div>
                    <i class="fas fa-user-check text-3xl text-green-600"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">New (7 days)</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['new_customers']); ?></p>
                    </div>
                    <i class="fas fa-user-plus text-3xl text-blue-600"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Avg. Customer Value</p>
                        <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($stats['avg_customer_value'] ?? 0, 2); ?></p>
                    </div>
                    <i class="fas fa-chart-line text-3xl text-purple-600"></i>
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
                           placeholder="Search by name, email, or phone..." 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <select name="sort" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="spent" <?php echo $sortBy === 'spent' ? 'selected' : ''; ?>>Highest Spent</option>
                    <option value="orders" <?php echo $sortBy === 'orders' ? 'selected' : ''; ?>>Most Orders</option>
                </select>
                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <?php if($search || $sortBy !== 'newest'): ?>
                <a href="<?php echo url('admin/customers'); ?>" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition text-center">
                    Clear
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-600 text-sm border-b bg-gray-50">
                            <th class="p-4">Customer</th>
                            <th class="p-4">Contact</th>
                            <th class="p-4">Orders</th>
                            <th class="p-4">Total Spent</th>
                            <th class="p-4">Last Order</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($customers)): ?>
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-4 block"></i>
                                No customers found
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($customers as $customer): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-primary-600 font-semibold">
                                                <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($customer['name']); ?></p>
                                            <p class="text-sm text-gray-500">ID: #<?php echo str_pad($customer['user_id'], 5, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <p class="text-sm"><?php echo htmlspecialchars($customer['email']); ?></p>
                                    <?php if($customer['phone']): ?>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['phone']); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <a href="<?php echo url('admin/orders?search=' . urlencode($customer['email'])); ?>" 
                                       class="text-primary-600 hover:text-primary-700 font-medium">
                                        <?php echo $customer['total_orders']; ?> orders
                                    </a>
                                </td>
                                <td class="p-4 font-semibold">
                                    $<?php echo number_format($customer['total_spent'], 2); ?>
                                </td>
                                <td class="p-4 text-sm">
                                    <?php if($customer['last_order_date']): ?>
                                        <?php echo date('M j, Y', strtotime($customer['last_order_date'])); ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                        Active
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center space-x-2">
                                        <button onclick="viewCustomer(<?php echo $customer['user_id']; ?>)" 
                                                class="text-primary-600 hover:text-primary-800 transition" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editCustomer(<?php echo $customer['user_id']; ?>)" 
                                                class="text-gray-600 hover:text-gray-800 transition" 
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="sendCustomerEmail(<?php echo $customer['user_id']; ?>, '<?php echo htmlspecialchars($customer['email']); ?>')" 
                                                class="text-blue-600 hover:text-blue-800 transition" 
                                                title="Send Email">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        <button onclick="resetPassword(<?php echo $customer['user_id']; ?>, '<?php echo htmlspecialchars($customer['name']); ?>')" 
                                                class="text-yellow-600 hover:text-yellow-800 transition" 
                                                title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="p-4 border-t">
                <nav class="flex justify-between items-center">
                    <p class="text-sm text-gray-600">
                        Showing <?php echo ($page - 1) * $itemsPerPage + 1; ?>-<?php echo min($page * $itemsPerPage, $totalCustomers); ?> 
                        of <?php echo $totalCustomers; ?> customers
                    </p>
                    <ul class="flex space-x-2">
                        <?php if($page > 1): ?>
                        <li>
                            <a href="?page=<?php echo ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($sortBy !== 'newest' ? '&sort=' . $sortBy : ''); ?>" 
                               class="px-3 py-2 bg-white border rounded hover:bg-gray-100 transition">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                        <li>
                            <a href="?page=<?php echo $i . ($search ? '&search=' . urlencode($search) : '') . ($sortBy !== 'newest' ? '&sort=' . $sortBy : ''); ?>" 
                               class="px-3 py-2 <?php echo $i === $page ? 'bg-primary-600 text-white' : 'bg-white border hover:bg-gray-100'; ?> rounded transition">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if($page < $totalPages): ?>
                        <li>
                            <a href="?page=<?php echo ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($sortBy !== 'newest' ? '&sort=' . $sortBy : ''); ?>" 
                               class="px-3 py-2 bg-white border rounded hover:bg-gray-100 transition">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div id="customerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Customer Details</h3>
                <button onclick="closeCustomerModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="customerModalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form id="resetPasswordForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="reset_password">
    <input type="hidden" name="user_id" id="reset_user_id">
</form>

<script>
function viewCustomer(customerId) {
    // TODO: Load customer details via AJAX
    document.getElementById('customerModal').classList.remove('hidden');
    document.getElementById('customerModalContent').innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
            <p class="mt-2 text-gray-600">Loading customer details...</p>
        </div>
    `;
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.add('hidden');
}

function editCustomer(customerId) {
    // TODO: Implement customer editing
    showNotification('Customer editing functionality to be implemented', 'info');
}

function sendCustomerEmail(customerId, email) {
    if (confirm(`Send email to ${email}?`)) {
        // TODO: Implement email sending
        showNotification('Email functionality to be implemented', 'info');
    }
}

function resetPassword(userId, customerName) {
    if (confirm(`Reset password for ${customerName}? A temporary password will be generated.`)) {
        document.getElementById('reset_user_id').value = userId;
        document.getElementById('resetPasswordForm').submit();
    }
}

function exportCustomers() {
    // TODO: Implement customer export
    showNotification('Export functionality to be implemented', 'info');
}

function openEmailModal() {
    // TODO: Implement email campaign modal
    showNotification('Email campaign functionality to be implemented', 'info');
}

// Close modal on outside click
document.getElementById('customerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCustomerModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>