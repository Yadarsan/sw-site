<?php
/**
 * AWE Electronics - Main Entry Point
 * Enhanced routing system with clean URLs
 */

// Load bootstrap
require_once 'bootstrap.php';

// Get the request URI and parse it
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// Remove base path from request URI
if ($base_path !== '/' && strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Remove query string
$request_uri = strtok($request_uri, '?');

// Clean up the URI
$request_uri = trim($request_uri, '/');

// Default route
if (empty($request_uri)) {
    $request_uri = 'home';
}

// Parse the route
$segments = explode('/', $request_uri);
$page = $segments[0];
$id = isset($segments[1]) ? $segments[1] : null;
$action = isset($segments[2]) ? $segments[2] : null;

// Store route info for use in views
$GLOBALS['route'] = [
    'page' => $page,
    'id' => $id,
    'action' => $action,
    'base_path' => $base_path
];

// Route handling
try {
    switch($page) {
        // Public routes
        case 'home':
        case '':
            require 'views/home.php';
            break;
            
        case 'products':
            require 'views/products.php';
            break;
            
        case 'product':
            if (!$id) {
                header("Location: {$base_path}/products");
                exit;
            }
            $_GET['id'] = $id; // For backward compatibility
            require 'views/product.php';
            break;
            
        case 'category':
            $_GET['category'] = $id;
            require 'views/category.php';
            break;
            
        case 'cart':
            require 'views/cart.php';
            break;
            
        case 'search':
            require 'views/search.php';
            break;
            
        // Authentication routes
        case 'auth':
        case 'login':
            if (app()->getAuthSession()->isLoggedIn()) {
                header("Location: {$base_path}/account");
                exit;
            }
            require 'views/auth.php';
            break;
            
        case 'register':
            if (app()->getAuthSession()->isLoggedIn()) {
                header("Location: {$base_path}/account");
                exit;
            }
            $_GET['mode'] = 'register';
            require 'views/auth.php';
            break;
            
        case 'logout':
            app()->getAuthSession()->logout();
            $_SESSION['flash_message'] = 'You have been logged out successfully.';
            $_SESSION['flash_type'] = 'success';
            header("Location: {$base_path}/");
            exit;
            
        // Protected customer routes
        case 'account':
            requireAuth();
            require 'views/account.php';
            break;
            
        case 'orders':
            requireAuth();
            if ($id) {
                $_GET['id'] = $id;
                require 'views/order-details.php';
            } else {
                require 'views/orders.php';
            }
            break;
            
        case 'checkout':
            requireAuth();
            if (app()->getShoppingCart()->isEmpty()) {
                $_SESSION['flash_message'] = 'Your cart is empty.';
                $_SESSION['flash_type'] = 'warning';
                header("Location: {$base_path}/cart");
                exit;
            }
            require 'views/checkout.php';
            break;
            
        case 'payment':
            requireAuth();
            require 'views/payment.php';
            break;
            
        // Admin routes
        case 'admin':
            requireAdmin();
            if (!$id) {
                header("Location: {$base_path}/admin/dashboard");
                exit;
            }
            
            switch($id) {
                case 'dashboard':
                    require 'views/admin/dashboard.php';
                    break;
                    
                case 'products':
                    if ($action === 'add') {
                        require 'views/admin/product-form.php';
                    } elseif ($action === 'edit') {
                        $_GET['id'] = $segments[3] ?? null;
                        require 'views/admin/product-form.php';
                    } else {
                        require 'views/admin/products.php';
                    }
                    break;
                    
                case 'inventory':
                    require 'views/admin/inventory.php';
                    break;
                    
                case 'orders':
                    if (isset($segments[2]) && is_numeric($segments[2])) {
                        $_GET['order_id'] = $segments[2];
                        require 'views/admin/order-details.php';
                    } else {
                        require 'views/admin/orders.php';
                    }
                    break;
                    
                case 'customers':
                    require 'views/admin/customers.php';
                    break;
                    
                case 'reports':
                    require 'views/admin/reports.php';
                    break;
                    
                default:
                    throw new Exception('Page not found');
            }
            break;
            
        // AJAX endpoints
        case 'ajax':
            header('Content-Type: application/json');
            
            switch($id) {
                case 'cart':
                    require 'ajax/cart.php';
                    break;
                    
                case 'search':
                    require 'ajax/search.php';
                    break;
                    
                case 'product':
                    require 'ajax/product.php';
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint not found']);
            }
            exit;
            
        // 404 - Page not found
        default:
            throw new Exception('Page not found');
    }
    
} catch (Exception $e) {
    http_response_code(404);
    require 'views/404.php';
}

/**
 * Helper function to require authentication
 */
function requireAuth() {
    global $base_path;
    if (!app()->getAuthSession()->isLoggedIn()) {
        $_SESSION['flash_message'] = 'Please login to continue.';
        $_SESSION['flash_type'] = 'warning';
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: {$base_path}/login");
        exit;
    }
}

/**
 * Helper function to require admin access
 */
function requireAdmin() {
    global $base_path;
    if (!app()->getAuthSession()->isLoggedIn() || !app()->getAuthSession()->isAdmin()) {
        $_SESSION['flash_message'] = 'Access denied. Admin privileges required.';
        $_SESSION['flash_type'] = 'danger';
        header("Location: {$base_path}/");
        exit;
    }
}

/**
 * Helper function to generate URLs
 */
function url($path = '') {
    global $base_path;
    return $base_path . '/' . ltrim($path, '/');
}

/**
 * Helper function to generate asset URLs
 */
function asset($path = '') {
    global $base_path;
    return $base_path . '/assets/' . ltrim($path, '/');
}
?>