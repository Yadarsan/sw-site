<?php
/**
 * ProductCatalog Service Class
 * 
 * Manages product browsing, filtering, and searching
 */
class ProductCatalog {
    // Database connection and dependencies
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
     * List all products with pagination
     *
     * @param int $page Page number (starting from 1)
     * @param int $itemsPerPage Number of items per page
     * @return array Products and pagination metadata
     */
    public function listProducts($page = 1, $itemsPerPage = 12) {
        // Calculate offset
        $offset = ($page - 1) * $itemsPerPage;
        
        // Get products for current page
        $products = $this->productDAO->findAll($itemsPerPage, $offset);
        
        // Get total product count
        $totalProducts = $this->productDAO->getTotal();
        
        // Calculate pagination metadata
        $totalPages = ceil($totalProducts / $itemsPerPage);
        
        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'items_per_page' => $itemsPerPage,
                'total_items' => $totalProducts,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    /**
     * Filter products by category
     *
     * @param string $category Category to filter by
     * @param int $page Page number
     * @param int $itemsPerPage Number of items per page
     * @return array Filtered products and pagination metadata
     */
    public function filterByCategory($category, $page = 1, $itemsPerPage = 12) {
        // Calculate offset
        $offset = ($page - 1) * $itemsPerPage;
        
        // Get products for current page
        $products = $this->productDAO->filterByCategory($category, $itemsPerPage, $offset);
        
        // Get total count for this category
        $totalProducts = $this->productDAO->countByCategory($category);
        
        // Calculate pagination metadata
        $totalPages = ceil($totalProducts / $itemsPerPage);
        
        return [
            'products' => $products,
            'category' => $category,
            'pagination' => [
                'current_page' => $page,
                'items_per_page' => $itemsPerPage,
                'total_items' => $totalProducts,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    /**
     * Search products by keyword
     *
     * @param string $keyword Search term
     * @param int $page Page number
     * @param int $itemsPerPage Number of items per page
     * @return array Search results and pagination metadata
     */
    public function search($keyword, $page = 1, $itemsPerPage = 12) {
        // Calculate offset
        $offset = ($page - 1) * $itemsPerPage;
        
        // Get products for current page
        $products = $this->productDAO->search($keyword, $itemsPerPage, $offset);
        
        // Get total count for this search
        $totalProducts = $this->productDAO->countSearch($keyword);
        
        // Calculate pagination metadata
        $totalPages = ceil($totalProducts / $itemsPerPage);
        
        return [
            'products' => $products,
            'keyword' => $keyword,
            'pagination' => [
                'current_page' => $page,
                'items_per_page' => $itemsPerPage,
                'total_items' => $totalProducts,
                'total_pages' => $totalPages
            ]
        ];
    }
    
    /**
     * Get product details
     *
     * @param int $productId Product ID
     * @return array|null Product details or null if not found
     */
    public function getProductDetails($productId) {
        return $this->productDAO->findById($productId);
    }
    
    /**
     * Get all product categories
     *
     * @return array List of categories
     */
    public function getAllCategories() {
        return $this->productDAO->getAllCategories();
    }
    
    /**
     * Get featured products (simple implementation - returns most recent products)
     *
     * @param int $limit Number of products to return
     * @return array Featured products
     */
    public function getFeaturedProducts($limit = 4) {
        // In a real system, this might use a different criteria
        // For now, we'll just return the first few products
        return $this->productDAO->findAll($limit, 0);
    }
}