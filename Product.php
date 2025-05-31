<?php
/**
 * Product Model Class
 * 
 * Represents a product in the system
 */
class Product {
    // Properties
    private $productID;
    private $name;
    private $description;
    private $price;
    private $stock;
    private $category;
    private $imageUrl;
    
    // Database connection and DAO
    private $conn;
    private $productDAO;
    
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
     * Load product data by ID
     *
     * @param int $productId Product ID
     * @return bool Success status
     */
    public function loadById($productId) {
        $product = $this->productDAO->findById($productId);
        
        if(!$product) {
            return false;
        }
        
        $this->productID = $product['product_id'];
        $this->name = $product['name'];
        $this->description = $product['description'];
        $this->price = $product['price'];
        $this->stock = $product['stock'];
        $this->category = $product['category'];
        $this->imageUrl = $product['image_url'];
        
        return true;
    }
    
    /**
     * Save product data (create or update)
     *
     * @return bool|int Success status or new product ID
     */
    public function save() {
        $productData = [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'category' => $this->category,
            'image_url' => $this->imageUrl
        ];
        
        if(isset($this->productID)) {
            // Update existing product
            $productData['product_id'] = $this->productID;
            return $this->productDAO->update($productData);
        } else {
            // Create new product
            $result = $this->productDAO->insert($productData);
            
            if($result) {
                $this->productID = $result;
                return $result;
            }
            
            return false;
        }
    }
    
    /**
     * Update stock level
     *
     * @param int $newStock New stock level
     * @return bool Success status
     */
    public function updateStock($newStock) {
        if(!$this->productID) {
            return false;
        }
        
        $this->stock = $newStock;
        return $this->productDAO->updateStock($this->productID, $newStock);
    }
    
    /**
     * Apply discount
     * Not implemented yet - placeholder for future expansion
     *
     * @param float $discountPercent Discount percentage
     * @return string Message about feature not being implemented
     */
    public function applyDiscount($discountPercent) {
        return "Discount feature not implemented yet.";
    }
    
    // Getters and Setters
    
    public function getProductID() {
        return $this->productID;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getPrice() {
        return $this->price;
    }
    
    public function setPrice($price) {
        $this->price = $price;
    }
    
    public function getStock() {
        return $this->stock;
    }
    
    public function setStock($stock) {
        $this->stock = $stock;
    }
    
    public function getCategory() {
        return $this->category;
    }
    
    public function setCategory($category) {
        $this->category = $category;
    }
    
    public function getImageUrl() {
        return $this->imageUrl;
    }
    
    public function setImageUrl($imageUrl) {
        $this->imageUrl = $imageUrl;
    }
    
    /**
     * Check if product is in stock
     *
     * @param int $quantity Quantity to check against
     * @return bool True if enough stock available
     */
    public function isInStock($quantity = 1) {
        return $this->stock >= $quantity;
    }
}
?>