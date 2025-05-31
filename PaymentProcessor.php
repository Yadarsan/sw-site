<?php
/**
 * PaymentProcessor Service Class
 * 
 * Handles payment processing (simulation only)
 */
class PaymentProcessor {
    // Database connection and dependencies
    private $conn;
    private $paymentDAO;
    private $orderDAO;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->paymentDAO = new PaymentDAO($db);
        $this->orderDAO = new OrderDAO($db);
    }
    
    /**
     * Simulate payment transaction
     *
     * @param int $orderId Order ID
     * @param float $amount Payment amount
     * @param string $method Payment method
     * @return array Payment result
     */
    public function simulatePayment($orderId, $amount, $method = 'Credit Card') {
        // Check if order exists
        $order = $this->orderDAO->findById($orderId);
        if(!$order) {
            return [
                'success' => false,
                'message' => 'Order not found'
            ];
        }
        
        // Check if payment already exists
        $existingPayment = $this->paymentDAO->findByOrder($orderId);
        if($existingPayment) {
            return [
                'success' => false,
                'message' => 'Payment already exists for this order',
                'payment_id' => $existingPayment['payment_id'],
                'status' => $existingPayment['status']
            ];
        }
        
        // For simulation purposes, randomly decide success/failure
        // In a real system, this would interact with payment gateway
        $randomOutcome = rand(1, 10);
        $success = ($randomOutcome <= 9); // 90% success rate
        
        // Create payment record
        $paymentData = [
            'order_id' => $orderId,
            'amount' => $amount,
            'method' => $method,
            'status' => $success ? 'Completed' : 'Failed'
        ];
        
        $paymentId = $this->paymentDAO->insert($paymentData);
        
        if(!$paymentId) {
            return [
                'success' => false,
                'message' => 'Failed to record payment'
            ];
        }
        
        // If payment successful, update order status
        if($success) {
            $this->orderDAO->updateStatus($orderId, 'Processing');
        }
        
        return [
            'success' => $success,
            'payment_id' => $paymentId,
            'status' => $success ? 'Completed' : 'Failed',
            'message' => $success ? 'Payment processed successfully' : 'Payment processing failed'
        ];
    }
    
    /**
     * Verify mock payment status
     *
     * @param int $paymentId Payment ID
     * @return array Payment status details
     */
    public function verifyMockStatus($paymentId) {
        $payment = $this->paymentDAO->findById($paymentId);
        
        if(!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found'
            ];
        }
        
        return [
            'success' => true,
            'payment_id' => $payment['payment_id'],
            'order_id' => $payment['order_id'],
            'amount' => $payment['amount'],
            'method' => $payment['method'],
            'status' => $payment['status']
        ];
    }
    
    /**
     * Handle refund
     *
     * @param int $paymentId Payment ID
     * @return array Refund result
     */
    public function handleRefund($paymentId) {
        $payment = $this->paymentDAO->findById($paymentId);
        
        if(!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found'
            ];
        }
        
        if($payment['status'] !== 'Completed') {
            return [
                'success' => false,
                'message' => 'Can only refund completed payments'
            ];
        }
        
        // For simulation purposes, randomly decide success/failure
        // In a real system, this would interact with payment gateway
        $randomOutcome = rand(1, 10);
        $success = ($randomOutcome <= 9); // 90% success rate
        
        if($success) {
            // Update payment status
            $this->paymentDAO->updateStatus($paymentId, 'Refunded');
            
            // Update order status
            $this->orderDAO->updateStatus($payment['order_id'], 'Refunded');
            
            return [
                'success' => true,
                'message' => 'Refund processed successfully',
                'new_status' => 'Refunded'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Refund processing failed',
            'current_status' => $payment['status']
        ];
    }
    
    /**
     * Get payment methods
     *
     * @return array Available payment methods
     */
    public function getPaymentMethods() {
        // In a real system, this might be retrieved from database or payment gateway
        return [
            'Credit Card',
            'PayPal',
            'Bank Transfer'
        ];
    }
}