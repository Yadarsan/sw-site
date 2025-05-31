<?php
/**
 * Invoice Model Class
 * 
 * Represents an invoice in the system
 */
class Invoice {
    // Properties
    private $invoiceID;
    private $orderID;
    private $date;
    private $orderDetails;
    
    // Database connection and DAOs
    private $conn;
    private $invoiceDAO;
    private $orderDAO;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->invoiceDAO = new InvoiceDAO($db);
        $this->orderDAO = new OrderDAO($db);
    }
    
    /**
     * Load invoice data by ID
     *
     * @param int $invoiceId Invoice ID
     * @return bool Success status
     */
    public function loadById($invoiceId) {
        $invoice = $this->invoiceDAO->findById($invoiceId);
        
        if(!$invoice) {
            return false;
        }
        
        $this->invoiceID = $invoice['invoice_id'];
        $this->orderID = $invoice['order_id'];
        $this->date = $invoice['date'];
        $this->orderDetails = $invoice['order_details'];
        
        return true;
    }
    
    /**
     * Load invoice data by order ID
     *
     * @param int $orderId Order ID
     * @return bool Success status
     */
    public function loadByOrder($orderId) {
        $invoice = $this->invoiceDAO->findByOrder($orderId);
        
        if(!$invoice) {
            return false;
        }
        
        $this->invoiceID = $invoice['invoice_id'];
        $this->orderID = $invoice['order_id'];
        $this->date = $invoice['date'];
        $this->orderDetails = $invoice['order_details'];
        
        return true;
    }
    
    /**
     * Generate PDF version of the invoice
     * Note: In this implementation, we return HTML that could be converted to PDF
     *
     * @return string HTML content for PDF
     */
    public function generatePDF() {
        if(!$this->invoiceID) {
            return false;
        }
        
        return $this->invoiceDAO->getPDF($this->invoiceID);
    }
    
    /**
     * Retrieve order information linked to the invoice
     *
     * @return array|null Order details or null if not found
     */
    public function retrieveOrder() {
        if(!$this->orderID) {
            return null;
        }
        
        $order = new Order($this->conn);
        if($order->loadById($this->orderID)) {
            return $order->getDetails();
        }
        
        return null;
    }
    
    /**
     * Get full invoice details including order information
     *
     * @return array|null Invoice details or null if not found
     */
    public function getDetails() {
        if(!$this->invoiceID) {
            return null;
        }
        
        return $this->invoiceDAO->getInvoiceDetails($this->invoiceID);
    }
    
    // Getters and Setters
    
    public function getInvoiceID() {
        return $this->invoiceID;
    }
    
    public function getOrderID() {
        return $this->orderID;
    }
    
    public function setOrderID($orderID) {
        $this->orderID = $orderID;
    }
    
    public function getDate() {
        return $this->date;
    }
    
    public function getOrderDetails() {
        return $this->orderDetails;
    }
    
    public function setOrderDetails($orderDetails) {
        $this->orderDetails = $orderDetails;
    }
}
?>