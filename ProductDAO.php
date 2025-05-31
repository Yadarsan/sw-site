<?php
/**
 * Product DAO Class
 * 
 * Handles database operations related to Product entities
 */
class ProductDAO {
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
     * Find product by ID
     *
     * @param int $productId Product's unique identifier
     * @return array Product data or null if not found
     */
    public function findById($productId) {
        $query = "SELECT * FROM products WHERE product_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $productId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Get all products
     *
     * @param int $limit Number of products to return (for pagination)
     * @param int $offset Starting position (for pagination)
     * @return array Array of products
     */
    public function findAll($limit = 10, $offset = 0) {
        $query = "SELECT * FROM products LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get count of all products
     *
     * @return int Total product count
     */
    public function getTotal() {
        $query = "SELECT COUNT(*) as total FROM products";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    /**
     * Filter products by category
     *
     * @param string $category Category name
     * @param int $limit Number of products to return
     * @param int $offset Starting position
     * @return array Filtered products
     */
    public function filterByCategory($category, $limit = 10, $offset = 0) {
        $query = "SELECT * FROM products WHERE category = :category LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count products in a category
     *
     * @param string $category Category name
     * @return int Count of products in the category
     */
    public function countByCategory($category) {
        $query = "SELECT COUNT(*) as total FROM products WHERE category = :category";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    /**
     * Search products by keyword in name or description
     *
     * @param string $keyword Search term
     * @param int $limit Number of products to return
     * @param int $offset Starting position
     * @return array Search results
     */
    public function search($keyword, $limit = 10, $offset = 0) {
        $search = "%{$keyword}%";
        $query = "SELECT * FROM products 
                 WHERE name LIKE :keyword OR description LIKE :keyword 
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword', $search);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count products matching a search term
     *
     * @param string $keyword Search term
     * @return int Count of matching products
     */
    public function countSearch($keyword) {
        $search = "%{$keyword}%";
        $query = "SELECT COUNT(*) as total FROM products 
                 WHERE name LIKE :keyword OR description LIKE :keyword";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword', $search);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    /**
     * Update product stock quantity
     *
     * @param int $productId Product ID
     * @param int $newStock New stock quantity
     * @return bool Success status
     */
    public function updateStock($productId, $newStock) {
        $query = "UPDATE products SET stock = :stock WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':stock', $newStock);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Insert a new product
     *
     * @param array $product Product data
     * @return bool Success status
     */
    public function insert($product) {
        $query = "INSERT INTO products (name, description, price, stock, category, image_url) 
                  VALUES (:name, :description, :price, :stock, :category, :image_url)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $name = htmlspecialchars(strip_tags($product['name']));
        $description = htmlspecialchars(strip_tags($product['description']));
        $price = htmlspecialchars(strip_tags($product['price']));
        $stock = htmlspecialchars(strip_tags($product['stock']));
        $category = htmlspecialchars(strip_tags($product['category']));
        $imageUrl = htmlspecialchars(strip_tags($product['image_url']));
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':image_url', $imageUrl);
        
        // Execute query
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update product information
     *
     * @param array $product Product data to update
     * @return bool Success status
     */
    public function update($product) {
        $query = "UPDATE products 
                  SET name = :name, 
                      description = :description, 
                      price = :price, 
                      stock = :stock, 
                      category = :category, 
                      image_url = :image_url 
                  WHERE product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $productId = htmlspecialchars(strip_tags($product['product_id']));
        $name = htmlspecialchars(strip_tags($product['name']));
        $description = htmlspecialchars(strip_tags($product['description']));
        $price = htmlspecialchars(strip_tags($product['price']));
        $stock = htmlspecialchars(strip_tags($product['stock']));
        $category = htmlspecialchars(strip_tags($product['category']));
        $imageUrl = htmlspecialchars(strip_tags($product['image_url']));
        
        // Bind parameters
        $stmt->bindParam(':product_id', $productId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':image_url', $imageUrl);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Delete a product
     *
     * @param int $productId Product ID to delete
     * @return bool Success status
     */
    public function delete($productId) {
        $query = "DELETE FROM products WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':product_id', $productId);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

  /**
 * Get all distinct product categories
 *
 * @return array List of categories
 */
public function getAllCategories() {
    $query = "SELECT category_id, name FROM category ORDER BY name";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();

    $categories = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row; // returns array with category_id and name
    }

    return $categories;
}


    /**
     * Get products with low stock
     *
     * @param int $threshold Stock level below which products are considered low
     * @return array Products with low stock
     */
    public function getLowStockProducts($threshold = 5) {
        $query = "SELECT * FROM products WHERE stock <= :threshold";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>