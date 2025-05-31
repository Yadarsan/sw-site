<?php
/**
 * ShoppingCart Model Class
 * 
 * Represents a shopping cart in the system
 */
class ShoppingCart {
    // Properties
    private $cartID;
    private $userID;
    private $productList = [];
    private $quantities = [];
    private $subtotal = 0;
    
    // Database connection and DAO
    private $conn;
    private $productDAO;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     * @param int $userId User ID (optional, null for guest cart)
     */
    public function __construct($db, $userId = null) {
        $this->conn = $db;
        $this->productDAO = new ProductDAO($db);
        $this->userID = $userId;
        $this->cartID = uniqid('cart_');
        
        // Initialize empty cart
        $this->productList = [];
        $this->quantities = [];
        $this->subtotal = 0;
    }
    
    /**
     * Add product to cart
     *
     * @param int $productId Product ID
     * @param int $quantity Quantity to add
     * @return bool Success status
     */
    public function addProduct($productId, $quantity = 1) {
        // Get product data
        $product = $this->productDAO->findById($productId);
        
        if(!$product) {
            return false;
        }
        
        // Check stock
        if($product['stock'] < $quantity) {
            return false;
        }
        
        // Check if product already in cart
        $index = array_search($productId, array_column($this->productList, 'product_id'));
        
        if($index !== false) {
            // Update quantity
            $this->quantities[$index] += $quantity;
        } else {
            // Add new product
            $this->productList[] = $product;
            $this->quantities[] = $quantity;
        }
        
        // Update subtotal
        $this->calculateTotal();
        
        return true;
    }
    
    /**
     * Remove product from cart
     *
     * @param int $productId Product ID
     * @return bool Success status
     */
    public function removeProduct($productId) {
        // Find product index
        $index = array_search($productId, array_column($this->productList, 'product_id'));
        
        if($index === false) {
            return false;
        }
        
        // Remove product
        array_splice($this->productList, $index, 1);
        array_splice($this->quantities, $index, 1);
        
        // Update subtotal
        $this->calculateTotal();
        
        return true;
    }
    
    /**
     * Update product quantity
     *
     * @param int $productId Product ID
     * @param int $newQuantity New quantity
     * @return bool Success status
     */
    public function updateQuantity($productId, $newQuantity) {
        // Validate quantity
        if($newQuantity <= 0) {
            return $this->removeProduct($productId);
        }
        
        // Find product index
        $index = array_search($productId, array_column($this->productList, 'product_id'));
        
        if($index === false) {
            return false;
        }
        
        // Check stock
        $product = $this->productList[$index];
        if($product['stock'] < $newQuantity) {
            return false;
        }
        
        // Update quantity
        $this->quantities[$index] = $newQuantity;
        
        // Update subtotal
        $this->calculateTotal();
        
        return true;
    }
    
    /**
     * Calculate cart total
     *
     * @return float Cart subtotal
     */
    public function calculateTotal() {
        $this->subtotal = 0;
        
        for($i = 0; $i < count($this->productList); $i++) {
            $this->subtotal += $this->productList[$i]['price'] * $this->quantities[$i];
        }
        
        return $this->subtotal;
    }
    
    /**
     * Get cart contents
     *
     * @return array Cart contents with products and quantities
     */
    public function getContents() {
        $contents = [];
        
        for($i = 0; $i < count($this->productList); $i++) {
            $contents[] = [
                'product' => $this->productList[$i],
                'quantity' => $this->quantities[$i],
                'subtotal' => $this->productList[$i]['price'] * $this->quantities[$i]
            ];
        }
        
        return $contents;
    }
    
    /**
     * Clear cart
     */
    public function clear() {
        $this->productList = [];
        $this->quantities = [];
        $this->subtotal = 0;
    }
    
    /**
     * Check if cart is empty
     *
     * @return bool True if cart is empty
     */
    public function isEmpty() {
        return count($this->productList) === 0;
    }
    
    /**
     * Get cart item count
     *
     * @return int Number of items in cart
     */
    public function getItemCount() {
        return array_sum($this->quantities);
    }
    
    /**
     * Get cart unique product count
     *
     * @return int Number of unique products in cart
     */
    public function getProductCount() {
        return count($this->productList);
    }
    
    // Getters and Setters
    
    public function getCartID() {
        return $this->cartID;
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
    }
    
    public function getSubtotal() {
        return $this->subtotal;
    }
}
?>