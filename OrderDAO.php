<?php
/**
 * Order DAO Class
 * 
 * Handles database operations related to Order entities
 */
class OrderDAO {
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
     * Find order by ID
     *
     * @param int $orderId Order's unique identifier
     * @return array|null Order data or null if not found
     */
    public function findById($orderId) {
        $query = "SELECT * FROM orders WHERE order_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Find orders by user ID
     *
     * @param int $userId Customer's unique identifier
     * @return array Orders belonging to the user
     */
    public function findByUser($userId) {
        $query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert a new order
     *
     * @param array $order Order data
     * @param array $orderItems Array of items in the order
     * @return int|bool New order ID or false on failure
     */
    public function insert($order, $orderItems) {
        try {
            $this->conn->beginTransaction();
            
            // Insert order
            $query = "INSERT INTO orders (user_id, order_date, status, total_price, shipping_info) 
                      VALUES (:user_id, NOW(), :status, :total_price, :shipping_info)";
            
            $stmt = $this->conn->prepare($query);
            
            // Sanitize input
            $userId = htmlspecialchars(strip_tags($order['user_id']));
            $status = htmlspecialchars(strip_tags($order['status']));
            $totalPrice = htmlspecialchars(strip_tags($order['total_price']));
            $shippingInfo = htmlspecialchars(strip_tags($order['shipping_info']));
            
            // Bind parameters
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':total_price', $totalPrice);
            $stmt->bindParam(':shipping_info', $shippingInfo);
            
            $stmt->execute();
            $orderId = $this->conn->lastInsertId();
            
            // Insert order items
            $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
                          VALUES (:order_id, :product_id, :quantity, :unit_price)";
            
            $itemStmt = $this->conn->prepare($itemQuery);
            
            foreach($orderItems as $item) {
                $itemStmt->bindParam(':order_id', $orderId);
                $itemStmt->bindParam(':product_id', $item['product_id']);
                $itemStmt->bindParam(':quantity', $item['quantity']);
                $itemStmt->bindParam(':unit_price', $item['unit_price']);
                $itemStmt->execute();
            }
            
            $this->conn->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Update order status
     *
     * @param int $orderId Order ID
     * @param string $newStatus New order status
     * @return bool Success status
     */
    public function updateStatus($orderId, $newStatus) {
        $query = "UPDATE orders SET status = :status WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':status', $newStatus);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Get order items (products) for a specific order
     *
     * @param int $orderId Order ID
     * @return array Order items with product details
     */
    public function getOrderItems($orderId) {
        $query = "SELECT oi.*, p.name, p.image_url 
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.product_id
                 WHERE oi.order_id = :order_id";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent orders for admin reporting
     *
     * @param int $limit Number of recent orders to retrieve
     * @return array Recent orders
     */
    public function getRecentOrders($limit = 10) {
        $query = "SELECT o.*, c.name as customer_name 
                 FROM orders o
                 JOIN customers c ON o.user_id = c.user_id
                 ORDER BY o.order_date DESC
                 LIMIT :limit";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get orders by status
     *
     * @param string $status Order status to filter by
     * @return array Orders with the specified status
     */
    public function getOrdersByStatus($status) {
        $query = "SELECT o.*, c.name as customer_name 
                 FROM orders o
                 JOIN customers c ON o.user_id = c.user_id
                 WHERE o.status = :status
                 ORDER BY o.order_date DESC";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total sales amount
     *
     * @param string $period Time period (daily, weekly, monthly, yearly, all)
     * @return float Total sales amount
     */
    public function getTotalSales($period = 'all') {
        $whereClause = "";
        
        switch($period) {
            case 'daily':
                $whereClause = "WHERE DATE(order_date) = CURDATE()";
                break;
            case 'weekly':
                $whereClause = "WHERE YEARWEEK(order_date) = YEARWEEK(CURDATE())";
                break;
            case 'monthly':
                $whereClause = "WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())";
                break;
            case 'yearly':
                $whereClause = "WHERE YEAR(order_date) = YEAR(CURDATE())";
                break;
            default:
                $whereClause = "";
                break;
        }
        
        $query = "SELECT SUM(total_price) as total FROM orders " . $whereClause;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total']) ? $result['total'] : 0;
    }

    /**
     * Get count of orders
     *
     * @param string $period Time period (daily, weekly, monthly, yearly, all)
     * @return int Order count
     */
    public function getOrderCount($period = 'all') {
        $whereClause = "";
        
        switch($period) {
            case 'daily':
                $whereClause = "WHERE DATE(order_date) = CURDATE()";
                break;
            case 'weekly':
                $whereClause = "WHERE YEARWEEK(order_date) = YEARWEEK(CURDATE())";
                break;
            case 'monthly':
                $whereClause = "WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())";
                break;
            case 'yearly':
                $whereClause = "WHERE YEAR(order_date) = YEAR(CURDATE())";
                break;
            default:
                $whereClause = "";
                break;
        }
        
        $query = "SELECT COUNT(*) as count FROM orders " . $whereClause;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>