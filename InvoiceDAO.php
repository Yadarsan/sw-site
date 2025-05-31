<?php
/**
 * Invoice DAO Class
 * 
 * Handles database operations related to Invoice entities
 */
class InvoiceDAO {
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
     * Find invoice by ID
     *
     * @param int $invoiceId Invoice's unique identifier
     * @return array|null Invoice data or null if not found
     */
    public function findById($invoiceId) {
        $query = "SELECT * FROM invoices WHERE invoice_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $invoiceId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Find invoice by order ID
     *
     * @param int $orderId Order's unique identifier
     * @return array|null Invoice data or null if not found
     */
    public function findByOrder($orderId) {
        $query = "SELECT * FROM invoices WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Insert a new invoice
     *
     * @param array $invoice Invoice data
     * @return int|bool New invoice ID or false on failure
     */
    public function insert($invoice) {
        $query = "INSERT INTO invoices (order_id, date, order_details) 
                  VALUES (:order_id, NOW(), :order_details)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $orderId = htmlspecialchars(strip_tags($invoice['order_id']));
        $orderDetails = $invoice['order_details']; // JSON or TEXT serialized data
        
        // Bind parameters
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':order_details', $orderDetails);
        
        // Execute query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Get invoice details with order and customer information
     *
     * @param int $invoiceId Invoice ID
     * @return array|null Complete invoice information or null if not found
     */
    public function getInvoiceDetails($invoiceId) {
        $query = "SELECT i.*, o.order_date, o.status as order_status, o.total_price, o.shipping_info, 
                         c.name as customer_name, c.email as customer_email, c.address as customer_address 
                 FROM invoices i
                 JOIN orders o ON i.order_id = o.order_id
                 JOIN customers c ON o.user_id = c.user_id
                 WHERE i.invoice_id = :invoice_id";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Get recent invoices
     *
     * @param int $limit Number of recent invoices to retrieve
     * @return array Recent invoices
     */
    public function getRecentInvoices($limit = 10) {
        $query = "SELECT i.*, o.order_date, c.name as customer_name 
                 FROM invoices i
                 JOIN orders o ON i.order_id = o.order_id
                 JOIN customers c ON o.user_id = c.user_id
                 ORDER BY i.date DESC
                 LIMIT :limit";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate PDF content for invoice (simplified - returns HTML for demo)
     * 
     * In a real implementation, this would use a PDF library like FPDF or TCPDF
     *
     * @param int $invoiceId Invoice ID
     * @return string HTML content for PDF generation
     */
    public function getPDF($invoiceId) {
        $invoiceDetails = $this->getInvoiceDetails($invoiceId);
        
        if(!$invoiceDetails) {
            return false;
        }
        
        // For simplicity, we're returning HTML that could be converted to PDF
        // In a real implementation, use a PDF library here
        $html = '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;">';
        $html .= '<h1>AWE Electronics - Invoice #' . $invoiceId . '</h1>';
        $html .= '<div style="margin-bottom: 20px;">';
        $html .= '<p><strong>Date:</strong> ' . date('F j, Y', strtotime($invoiceDetails['date'])) . '</p>';
        $html .= '<p><strong>Order ID:</strong> ' . $invoiceDetails['order_id'] . '</p>';
        $html .= '<p><strong>Order Status:</strong> ' . $invoiceDetails['order_status'] . '</p>';
        $html .= '</div>';
        
        $html .= '<div style="margin-bottom: 20px;">';
        $html .= '<h3>Customer Information</h3>';
        $html .= '<p><strong>Name:</strong> ' . $invoiceDetails['customer_name'] . '</p>';
        $html .= '<p><strong>Email:</strong> ' . $invoiceDetails['customer_email'] . '</p>';
        $html .= '<p><strong>Shipping Address:</strong> ' . $invoiceDetails['customer_address'] . '</p>';
        $html .= '</div>';
        
        // Get order items
        $orderItems = json_decode($invoiceDetails['order_details'], true);
        
        $html .= '<div style="margin-bottom: 20px;">';
        $html .= '<h3>Order Items</h3>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr style="background-color: #f2f2f2;">';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Product</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Quantity</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Unit Price</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Subtotal</th>';
        $html .= '</tr>';
        
        foreach($orderItems as $item) {
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $item['name'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $item['quantity'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">$' . number_format($item['unit_price'], 2) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">$' . number_format($item['quantity'] * $item['unit_price'], 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $html .= '</div>';
        
        $html .= '<div style="text-align: right;">';
        $html .= '<p><strong>Total:</strong> $' . number_format($invoiceDetails['total_price'], 2) . '</p>';
        $html .= '</div>';
        
        $html .= '<div style="margin-top: 40px; text-align: center; font-size: 12px;">';
        $html .= '<p>Thank you for shopping with AWE Electronics!</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
}
?>