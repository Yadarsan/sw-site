<?php
/**
 * CartManager Service Class
 * 
 * Manages shopping cart operations
 */
class CartManager {
    // Database connection and dependencies
    private $conn;
    private $inventoryManager;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->inventoryManager = new InventoryManager($db);
    }
    
    /**
     * Create a new shopping cart
     *
     * @param int $userId User ID (optional, null for guest cart)
     * @return ShoppingCart New cart instance
     */
    public function createCart($userId = null) {
        return new ShoppingCart($this->conn, $userId);
    }
    
    /**
     * Merge two shopping carts
     * Useful when a guest user logs in and their guest cart needs to be merged with their user cart
     *
     * @param ShoppingCart $sourceCart Source cart (typically guest cart)
     * @param ShoppingCart $targetCart Target cart (typically user cart)
     * @return ShoppingCart Merged cart
     */
    public function mergeCarts($sourceCart, $targetCart) {
        if($sourceCart->isEmpty()) {
            return $targetCart;
        }
        
        // Get contents of source cart
        $sourceContents = $sourceCart->getContents();
        
        // Add each item to target cart
        foreach($sourceContents as $item) {
            $targetCart->addProduct($item['product']['product_id'], $item['quantity']);
        }
        
        return $targetCart;
    }
    
    /**
     * Convert a shopping cart to an order
     *
     * @param ShoppingCart $cart Shopping cart to convert
     * @param int $userId User ID
     * @param string $shippingInfo Shipping information
     * @return array|bool New order details or false on failure
     */
    public function convertToOrder($cart, $userId, $shippingInfo) {
        if($cart->isEmpty()) {
            return false;
        }
        
        // Validate stock for all items
        $contents = $cart->getContents();
        foreach($contents as $item) {
            if(!$this->inventoryManager->checkStock($item['product']['product_id'], $item['quantity'])) {
                return false;
            }
        }
        
        // Create order
        $order = new Order($this->conn);
        $order->setUserID($userId);
        $order->setStatus('Pending');
        $order->setTotalPrice($cart->getSubtotal());
        $order->setShippingInfo($shippingInfo);
        
        // Prepare order items
        $orderItems = [];
        foreach($contents as $item) {
            $orderItems[] = [
                'product_id' => $item['product']['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['product']['price']
            ];
            
            // Update inventory
            $this->inventoryManager->updateStock(
                $item['product']['product_id'], 
                $item['product']['stock'] - $item['quantity']
            );
        }
        
        // Save order
        $orderId = $order->save($orderItems);
        
        if(!$orderId) {
            return false;
        }
        
        // Clear cart
        $cart->clear();
        
        // Load full order details
        $order->loadById($orderId);
        return $order->getDetails();
    }
    
    /**
     * Validate cart contents
     *
     * @param ShoppingCart $cart Shopping cart to validate
     * @return bool|array True if valid or array of invalid items
     */
    public function validateCart($cart) {
        if($cart->isEmpty()) {
            return true;
        }
        
        $invalidItems = [];
        $contents = $cart->getContents();
        
        foreach($contents as $item) {
            if(!$this->inventoryManager->checkStock($item['product']['product_id'], $item['quantity'])) {
                $invalidItems[] = [
                    'product_id' => $item['product']['product_id'],
                    'name' => $item['product']['name'],
                    'requested' => $item['quantity'],
                    'available' => $item['product']['stock']
                ];
            }
        }
        
        return empty($invalidItems) ? true : $invalidItems;
    }
    
    /**
     * Calculate estimated shipping
     * This is a simple implementation that could be expanded
     *
     * @param ShoppingCart $cart Shopping cart
     * @param string $shippingMethod Shipping method
     * @return float Shipping cost
     */
    public function calculateShipping($cart, $shippingMethod = 'standard') {
        $baseRate = 5.00;
        $itemCount = $cart->getItemCount();
        
        switch($shippingMethod) {
            case 'express':
                return $baseRate + ($itemCount * 2);
            case 'overnight':
                return $baseRate + ($itemCount * 5);
            case 'standard':
            default:
                return $baseRate + ($itemCount * 0.5);
        }
    }
}