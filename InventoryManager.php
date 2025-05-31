<?php
/**
 * InventoryManager Service Class
 * 
 * Manages product inventory
 */
class InventoryManager {
    // Database connection and dependencies
    private $conn;
    private $productDAO;
    
    // Configuration
    private $lowStockThreshold = 5;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->productDAO = new ProductDAO($db);
    }
    
    /**
     * Update product stock
     *
     * @param int $productId Product ID
     * @param int $newStock New stock level
     * @return bool Success status
     */
    public function updateStock($productId, $newStock) {
        if($newStock < 0) {
            $newStock = 0;
        }
        
        $result = $this->productDAO->updateStock($productId, $newStock);
        
        // Check if item is now low in stock
        if($result && $newStock <= $this->lowStockThreshold) {
            $this->alertLowStock($productId, $newStock);
        }
        
        return $result;
    }
    
    /**
     * Alert about low stock
     * In a real system, this might send emails, notifications, etc.
     *
     * @param int $productId Product ID
     * @param int $currentStock Current stock level
     * @return bool Success status
     */
    public function alertLowStock($productId, $currentStock = null) {
        if($currentStock === null) {
            $product = $this->productDAO->findById($productId);
            if(!$product) {
                return false;
            }
            $currentStock = $product['stock'];
        }
        
        // In a real system, this would send notifications to admin
        // For this implementation, we'll just log it
        error_log("LOW STOCK ALERT: Product ID {$productId} has {$currentStock} items remaining.");
        
        return true;
    }
    
    /**
     * Get stock status for all products or specific products
     *
     * @param int|array $productIds Single product ID or array of product IDs (optional)
     * @return array Stock status information
     */
    public function getStockStatus($productIds = null) {
        if($productIds === null) {
            // Get all products
            $products = $this->productDAO->findAll(1000, 0); // Get up to 1000 products
        } elseif(is_array($productIds)) {
            // Get multiple specific products
            $products = [];
            foreach($productIds as $id) {
                $product = $this->productDAO->findById($id);
                if($product) {
                    $products[] = $product;
                }
            }
        } else {
            // Get single product
            $product = $this->productDAO->findById($productIds);
            $products = $product ? [$product] : [];
        }
        
        // Format stock status
        $stockStatus = [];
        foreach($products as $product) {
            $stockStatus[] = [
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'category' => $product['category'],
                'stock' => $product['stock'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'low_stock' => ($product['stock'] <= $this->lowStockThreshold),
                'in_stock' => ($product['stock'] > 0)
            ];
        }
        
        return $stockStatus;
    }
    
    /**
     * Check if product has sufficient stock
     *
     * @param int $productId Product ID
     * @param int $quantity Quantity to check
     * @return bool True if sufficient stock
     */
    public function checkStock($productId, $quantity) {
        $product = $this->productDAO->findById($productId);
        
        if(!$product) {
            return false;
        }
        
        return $product['stock'] >= $quantity;
    }
    
    /**
     * Get products with low stock
     *
     * @return array Low stock products
     */
    public function getLowStockProducts() {
        return $this->productDAO->getLowStockProducts($this->lowStockThreshold);
    }
    
    /**
     * Set low stock threshold
     *
     * @param int $threshold New threshold
     */
    public function setLowStockThreshold($threshold) {
        $this->lowStockThreshold = $threshold;
    }
    
    /**
     * Get low stock threshold
     *
     * @return int Current threshold
     */
    public function getLowStockThreshold() {
        return $this->lowStockThreshold;
    }
}