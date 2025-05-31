<?php
/**
 * Administrator Model Class
 * 
 * Represents an administrator in the system
 */
class Administrator {
    // Properties
    private $adminID;
    private $name;
    private $password;
    private $role;
    
    // Database connection and DAO
    private $conn;
    private $adminDAO;
    private $productDAO;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->adminDAO = new AdminDAO($db);
        $this->productDAO = new ProductDAO($db);
    }
    
    /**
     * Load administrator data by ID
     *
     * @param int $adminId Admin ID
     * @return bool Success status
     */
    public function loadById($adminId) {
        $admin = $this->adminDAO->findById($adminId);
        
        if(!$admin) {
            return false;
        }
        
        $this->adminID = $admin['admin_id'];
        $this->name = $admin['name'];
        $this->password = $admin['password']; // Already hashed
        $this->role = $admin['role'];
        
        return true;
    }
    
    /**
     * Load administrator data by name
     *
     * @param string $name Admin username
     * @return bool Success status
     */
    public function loadByName($name) {
        $admin = $this->adminDAO->findByName($name);
        
        if(!$admin) {
            return false;
        }
        
        $this->adminID = $admin['admin_id'];
        $this->name = $admin['name'];
        $this->password = $admin['password']; // Already hashed
        $this->role = $admin['role'];
        
        return true;
    }
    
    /**
     * Save administrator data (create or update)
     *
     * @return bool Success status
     */
    public function save() {
        $adminData = [
            'name' => $this->name,
            'role' => $this->role
        ];
        
        if(isset($this->adminID)) {
            // Update existing admin
            $adminData['admin_id'] = $this->adminID;
            return $this->adminDAO->update($adminData);
        } else {
            // Create new admin
            $adminData['password'] = $this->password;
            $result = $this->adminDAO->insert($adminData);
            
            if($result) {
                $this->loadByName($this->name);
                return true;
            }
            
            return false;
        }
    }
    
    /**
     * Manage product - add, update, or delete
     *
     * @param string $action Action to perform (add, update, delete)
     * @param array $productData Product data
     * @return bool|int Success status or new product ID
     */
    public function manageProduct($action, $productData = []) {
        switch($action) {
            case 'add':
                return $this->productDAO->insert($productData);
                
            case 'update':
                return $this->productDAO->update($productData);
                
            case 'delete':
                return $this->productDAO->delete($productData['product_id']);
                
            default:
                return false;
        }
    }
    
    /**
     * View system reports
     * This is a placeholder that delegates to ReportGenerator
     *
     * @param string $reportType Type of report to generate
     * @return string Message indicating this function delegates to ReportGenerator
     */
    public function viewReports($reportType) {
        return "This function delegates to ReportGenerator service. Please use ReportGenerator class directly.";
    }
    
    /**
     * Manage inventory
     * This is a placeholder that delegates to InventoryManager
     *
     * @return string Message indicating this function delegates to InventoryManager
     */
    public function manageInventory() {
        return "This function delegates to InventoryManager service. Please use InventoryManager class directly.";
    }
    
    // Getters and Setters
    
    public function getAdminID() {
        return $this->adminID;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function setPassword($password) {
        // Hash password if it's a new/plain password
        if(strlen($password) < 60) { // Not already hashed
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $this->password = $password;
        }
    }
    
    public function getRole() {
        return $this->role;
    }
    
    public function setRole($role) {
        $this->role = $role;
    }
}
?>