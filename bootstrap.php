<?php
/**
 * Bootstrap File
 * 
 * Initializes the system and prepares all core components
 */

// Start session if not already started
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define class autoloader
spl_autoload_register(function($class_name) {
    // Convert class name to file path
    // Check model directory
    if (file_exists('model/' . $class_name . '.php')) {
        require_once 'model/' . $class_name . '.php';
        return;
    }
    
    // Check dao directory
    if (file_exists('dao/' . $class_name . '.php')) {
        require_once 'dao/' . $class_name . '.php';
        return;
    }
    
    // Check service directory
    if (file_exists('service/' . $class_name . '.php')) {
        require_once 'service/' . $class_name . '.php';
        return;
    }
});

// Load configuration
require_once 'config/database.php';

/**
 * Global Application Class
 * 
 * Manages system-wide services and components
 */
class App {
    // Singleton instance
    private static $instance = null;
    
    // Core services
    private $db;
    private $authSession;
    private $productCatalog;
    private $cartManager;
    private $paymentProcessor;
    private $inventoryManager;
    private $reportGenerator;
    
    // Shopping cart for current session
    private $shoppingCart;
    
    /**
     * Private constructor (singleton pattern)
     */
    private function __construct() {
        try {
            // Initialize database connection
            $database = new Database();
            $this->db = $database->getConnection();
            
            // Initialize core services
            $this->initializeServices();
            
            // Initialize shopping cart
            $this->initializeCart();
            
        } catch (Exception $e) {
            // Handle initialization errors
            die("Error initializing system: " . $e->getMessage());
        }
    }
    
    /**
     * Get singleton instance
     *
     * @return App The singleton instance
     */
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Initialize core services
     */
    private function initializeServices() {
        // Authentication and session management
        $this->authSession = new AuthSession($this->db);
        
        // Product catalog and browsing
        $this->productCatalog = new ProductCatalog($this->db);
        
        // Shopping cart management
        $this->cartManager = new CartManager($this->db);
        
        // Payment processing
        $this->paymentProcessor = new PaymentProcessor($this->db);
        
        // Inventory management
        $this->inventoryManager = new InventoryManager($this->db);
        
        // Report generation
        $this->reportGenerator = new ReportGenerator($this->db);
    }
    
    /**
     * Initialize shopping cart
     */
    private function initializeCart() {
        // Check if user is logged in
        $userId = $this->authSession->getCurrentUserId();
        
        // Create cart for current session
        $this->shoppingCart = $this->cartManager->createCart($userId);
        
        // Store cart in session for persistence across page loads
        if(!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Restore cart contents from session if available
        if(isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
            foreach($_SESSION['cart']['items'] as $item) {
                if(isset($item['product_id'], $item['quantity'])) {
                    $this->shoppingCart->addProduct($item['product_id'], $item['quantity']);
                }
            }
        }
    }
    
    /**
     * Get database connection
     *
     * @return PDO Database connection
     */
    public function getDB() {
        return $this->db;
    }
    
    /**
     * Get authentication service
     *
     * @return AuthSession Authentication service
     */
    public function getAuthSession() {
        return $this->authSession;
    }
    
    /**
     * Get product catalog service
     *
     * @return ProductCatalog Product catalog service
     */
    public function getProductCatalog() {
        return $this->productCatalog;
    }
    
    /**
     * Get cart manager service
     *
     * @return CartManager Cart manager service
     */
    public function getCartManager() {
        return $this->cartManager;
    }
    
    /**
     * Get payment processor service
     *
     * @return PaymentProcessor Payment processor service
     */
    public function getPaymentProcessor() {
        return $this->paymentProcessor;
    }
    
    /**
     * Get inventory manager service
     *
     * @return InventoryManager Inventory manager service
     */
    public function getInventoryManager() {
        return $this->inventoryManager;
    }
    
    /**
     * Get report generator service
     *
     * @return ReportGenerator Report generator service
     */
    public function getReportGenerator() {
        return $this->reportGenerator;
    }
    
    /**
     * Get shopping cart for current session
     *
     * @return ShoppingCart Shopping cart
     */
    public function getShoppingCart() {
        return $this->shoppingCart;
    }
    
    /**
     * Save cart contents to session
     */
    public function saveCartToSession() {
        if($this->shoppingCart) {
            $_SESSION['cart']['items'] = [];
            
            $contents = $this->shoppingCart->getContents();
            foreach($contents as $item) {
                $_SESSION['cart']['items'][] = [
                    'product_id' => $item['product']['product_id'],
                    'quantity' => $item['quantity']
                ];
            }
        }
    }
}

// Initialize application
$app = App::getInstance();

// Global function to access app instance
function app() {
    return App::getInstance();
}

// Save cart to session on script completion
register_shutdown_function(function() {
    app()->saveCartToSession();
});
?>