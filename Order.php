<?php
/**
 * Order Model Class
 * 
 * Represents an order in the system
 */
class Order {
    // Properties
    private $orderID;
    private $userID;
    private $orderDate;
    private $status;
    private $totalPrice;
    private $shippingInfo;
    private $items = [];
    
    // Database connection and DAOs
    private $conn;
    private $orderDAO;
    private $invoiceDAO;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->orderDAO = new OrderDAO($db);
        $this->invoiceDAO = new InvoiceDAO($db);
    }
    
    /**
     * Load order data by ID
     *
     * @param int $orderId Order ID
     * @return bool Success status
     */
    public function loadById($orderId) {
        $order = $this->orderDAO->findById($orderId);
        
        if(!$order) {
            return false;
        }
        
        $this->orderID = $order['order_id'];
        $this->userID = $order['user_id'];
        $this->orderDate = $order['order_date'];
        $this->status = $order['status'];
        $this->totalPrice = $order['total_price'];
        $this->shippingInfo = $order['shipping_info'];
        
        // Load order items
        $this->items = $this->orderDAO->getOrderItems($orderId);
        
        return true;
    }
    
    /**
     * Save order data (create only - orders shouldn't be fully updated)
     *
     * @param array $orderItems Order items
     * @return int|bool New order ID or false on failure
     */
    public function save($orderItems) {
        if(isset($this->orderID)) {
            // Orders shouldn't be fully updated after creation
            // Only status updates are allowed
            return false;
        }
        
        // Prepare order data
        $orderData = [
            'user_id' => $this->userID,
            'status' => $this->status,
            'total_price' => $this->totalPrice,
            'shipping_info' => $this->shippingInfo
        ];
        
        // Create new order
        $newOrderId = $this->orderDAO->insert($orderData, $orderItems);
        
        if($newOrderId) {
            $this->orderID = $newOrderId;
            $this->items = $orderItems;
            return $newOrderId;
        }
        
        return false;
    }
    
    /**
     * Update order status
     *
     * @param string $newStatus New order status
     * @return bool Success status
     */
    public function updateStatus($newStatus) {
        if(!$this->orderID) {
            return false;
        }
        
        $result = $this->orderDAO->updateStatus($this->orderID, $newStatus);
        
        if($result) {
            $this->status = $newStatus;
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate invoice for this order
     *
     * @return int|bool New invoice ID or false on failure
     */
    public function generateInvoice() {
        if(!$this->orderID) {
            return false;
        }
        
        // Check if invoice already exists
        $existingInvoice = $this->invoiceDAO->findByOrder($this->orderID);
        if($existingInvoice) {
            return $existingInvoice['invoice_id'];
        }
        
        // Prepare invoice data
        $invoiceData = [
            'order_id' => $this->orderID,
            'order_details' => json_encode($this->items)
        ];
        
        // Create invoice
        return $this->invoiceDAO->insert($invoiceData);
    }
    
    /**
     * Get order details including items
     *
     * @return array Order details
     */
    public function getDetails() {
        if(!$this->orderID) {
            return null;
        }
        
        return [
            'order_id' => $this->orderID,
            'user_id' => $this->userID,
            'order_date' => $this->orderDate,
            'status' => $this->status,
            'total_price' => $this->totalPrice,
            'shipping_info' => $this->shippingInfo,
            'items' => $this->items
        ];
    }
    
    // Getters and Setters
    
    public function getOrderID() {
        return $this->orderID;
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
    }
    
    public function getOrderDate() {
        return $this->orderDate;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function getTotalPrice() {
        return $this->totalPrice;
    }
    
    public function setTotalPrice($totalPrice) {
        $this->totalPrice = $totalPrice;
    }
    
    public function getShippingInfo() {
        return $this->shippingInfo;
    }
    
    public function setShippingInfo($shippingInfo) {
        $this->shippingInfo = $shippingInfo;
    }
    
    public function getItems() {
        return $this->items;
    }
}
?>