<?php
/**
 * Payment Model Class
 * 
 * Represents a payment in the system
 */
class Payment {
    // Properties
    private $paymentID;
    private $orderID;
    private $amount;
    private $method;
    private $status;
    
    // Database connection and DAO
    private $conn;
    private $paymentDAO;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->paymentDAO = new PaymentDAO($db);
    }
    
    /**
     * Load payment data by ID
     *
     * @param int $paymentId Payment ID
     * @return bool Success status
     */
    public function loadById($paymentId) {
        $payment = $this->paymentDAO->findById($paymentId);
        
        if(!$payment) {
            return false;
        }
        
        $this->paymentID = $payment['payment_id'];
        $this->orderID = $payment['order_id'];
        $this->amount = $payment['amount'];
        $this->method = $payment['method'];
        $this->status = $payment['status'];
        
        return true;
    }
    
    /**
     * Load payment data by order ID
     *
     * @param int $orderId Order ID
     * @return bool Success status
     */
    public function loadByOrder($orderId) {
        $payment = $this->paymentDAO->findByOrder($orderId);
        
        if(!$payment) {
            return false;
        }
        
        $this->paymentID = $payment['payment_id'];
        $this->orderID = $payment['order_id'];
        $this->amount = $payment['amount'];
        $this->method = $payment['method'];
        $this->status = $payment['status'];
        
        return true;
    }
    
    /**
     * Save payment data (create or update status)
     *
     * @return int|bool New payment ID or success status
     */
    public function save() {
        if(isset($this->paymentID)) {
            // Only status updates are allowed for existing payments
            return $this->paymentDAO->updateStatus($this->paymentID, $this->status);
        }
        
        // Prepare payment data
        $paymentData = [
            'order_id' => $this->orderID,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status
        ];
        
        // Create new payment
        $newPaymentId = $this->paymentDAO->insert($paymentData);
        
        if($newPaymentId) {
            $this->paymentID = $newPaymentId;
            return $newPaymentId;
        }
        
        return false;
    }
    
    /**
     * Process payment
     * This is a simplified version since actual payment processing is not implemented
     *
     * @return bool Success status
     */
    public function processPayment() {
        if(!$this->orderID || !$this->amount) {
            return false;
        }
        
        // In a real system, this would interact with payment gateway
        // For this implementation, we'll just simulate success
        $this->status = 'Completed';
        
        // Save payment
        return $this->save();
    }
    
    /**
     * Verify payment status
     *
     * @return string Current payment status
     */
    public function verifyStatus() {
        if(!$this->paymentID) {
            return 'Unknown';
        }
        
        // In a real system, this would check with payment gateway
        return $this->status;
    }
    
    /**
     * Issue refund
     * This is a simplified version since actual refund is not implemented
     *
     * @return bool Success status
     */
    public function issueRefund() {
        if(!$this->paymentID || $this->status !== 'Completed') {
            return false;
        }
        
        // In a real system, this would process refund with payment gateway
        $this->status = 'Refunded';
        
        // Save payment status
        return $this->paymentDAO->updateStatus($this->paymentID, $this->status);
    }
    
    // Getters and Setters
    
    public function getPaymentID() {
        return $this->paymentID;
    }
    
    public function getOrderID() {
        return $this->orderID;
    }
    
    public function setOrderID($orderID) {
        $this->orderID = $orderID;
    }
    
    public function getAmount() {
        return $this->amount;
    }
    
    public function setAmount($amount) {
        $this->amount = $amount;
    }
    
    public function getMethod() {
        return $this->method;
    }
    
    public function setMethod($method) {
        $this->method = $method;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
}
?>