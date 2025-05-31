<?php
/**
 * Payment DAO Class
 * 
 * Handles database operations related to Payment entities
 */
class PaymentDAO {
    private $conn;

    /**
     * Constructor with database connection
     *
     * @param PDO $db Database connection object
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Find payment by ID
     *
     * @param int $paymentId Payment's unique identifier
     * @return array|null Payment data or null if not found
     */
    public function findById($paymentId) {
        $query = "SELECT * FROM payments WHERE payment_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $paymentId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Find payment by order ID
     *
     * @param int $orderId Order's unique identifier
     * @return array|null Payment data or null if not found
     */
    public function findByOrder($orderId) {
        $query = "SELECT * FROM payments WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Insert a new payment
     *
     * @param array $payment Payment data
     * @return int|bool New payment ID or false on failure
     */
    public function insert($payment) {
        $query = "INSERT INTO payments (order_id, amount, method, status) 
                  VALUES (:order_id, :amount, :method, :status)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $orderId = htmlspecialchars(strip_tags($payment['order_id']));
        $amount = htmlspecialchars(strip_tags($payment['amount']));
        $method = htmlspecialchars(strip_tags($payment['method']));
        $status = htmlspecialchars(strip_tags($payment['status']));
        
        // Bind parameters
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':method', $method);
        $stmt->bindParam(':status', $status);
        
        // Execute query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update payment status
     *
     * @param int $paymentId Payment ID
     * @param string $newStatus New payment status
     * @return bool Success status
     */
    public function updateStatus($paymentId, $newStatus) {
        $query = "UPDATE payments SET status = :status WHERE payment_id = :payment_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':payment_id', $paymentId);
        $stmt->bindParam(':status', $newStatus);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get payments by status
     *
     * @param string $status Payment status to filter by
     * @return array Payments with the specified status
     */
    public function getPaymentsByStatus($status) {
        $query = "SELECT p.*, o.order_date 
                 FROM payments p
                 JOIN orders o ON p.order_id = o.order_id
                 WHERE p.status = :status
                 ORDER BY o.order_date DESC";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent payments
     *
     * @param int $limit Number of recent payments to retrieve
     * @return array Recent payments
     */
    public function getRecentPayments($limit = 10) {
        $query = "SELECT p.*, o.order_date 
                 FROM payments p
                 JOIN orders o ON p.order_id = o.order_id
                 ORDER BY o.order_date DESC
                 LIMIT :limit";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update payment method
     *
     * @param int $paymentId Payment ID
     * @param string $newMethod New payment method
     * @return bool Success status
     */
    public function updateMethod($paymentId, $newMethod) {
        $query = "UPDATE payments SET method = :method WHERE payment_id = :payment_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':payment_id', $paymentId);
        $stmt->bindParam(':method', $newMethod);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>