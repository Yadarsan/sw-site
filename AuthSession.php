<?php
/**
 * AuthSession Service Class
 * 
 * Handles authentication and session management
 */
class AuthSession {
    // Database connection and dependencies
    private $conn;
    private $customerDAO;
    private $adminDAO;
    
    // Session data
    private $sessionID;
    private $currentUser;
    private $userType;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->customerDAO = new CustomerDAO($db);
        $this->adminDAO = new AdminDAO($db);
        
        // Start PHP session if not already started
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate or retrieve session ID
        $this->sessionID = session_id();
        
        // Get current user from session if available
        $this->loadSessionData();
    }
    
    /**
     * Authenticate user (login)
     *
     * @param string $email Email address for customers or username for admins
     * @param string $password Password
     * @param string $userType Type of user ('customer' or 'admin')
     * @return bool|array Authentication result (user data or false)
     */
    public function authenticate($email, $password, $userType = 'customer') {
        if($userType === 'admin') {
            // Admin authentication
            $admin = $this->adminDAO->findByNameAndPassword($email, $password);
            
            if($admin) {
                // Set session data for admin
                $_SESSION['user_id'] = $admin['admin_id'];
                $_SESSION['user_name'] = $admin['name'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['user_role'] = $admin['role'];
                
                // Update local properties
                $this->currentUser = $admin;
                $this->userType = 'admin';
                
                return $admin;
            }
        } else {
            // Customer authentication
            $customer = $this->customerDAO->findByEmailAndPassword($email, $password);
            
            if($customer) {
                // Set session data for customer
                $_SESSION['user_id'] = $customer['user_id'];
                $_SESSION['user_name'] = $customer['name'];
                $_SESSION['user_type'] = 'customer';
                
                // Update local properties
                $this->currentUser = $customer;
                $this->userType = 'customer';
                
                return $customer;
            }
        }
        
        return false;
    }
    
    /**
     * Validate user permission
     *
     * @param string $permission Permission to check
     * @return bool True if user has permission
     */
    public function validatePermission($permission) {
        // Admin permissions
        if($this->userType === 'admin') {
            switch($permission) {
                case 'manage_products':
                case 'manage_inventory':
                case 'view_reports':
                    return true;
                
                case 'super_admin':
                    // Only admins with 'super_admin' role have this permission
                    return isset($this->currentUser['role']) && $this->currentUser['role'] === 'super_admin';
                
                default:
                    return false;
            }
        }
        
        // Customer permissions
        if($this->userType === 'customer') {
            switch($permission) {
                case 'view_orders':
                case 'place_order':
                case 'manage_account':
                    return true;
                
                default:
                    return false;
            }
        }
        
        // Guest permissions
        switch($permission) {
            case 'browse_products':
            case 'register':
                return true;
            
            default:
                return false;
        }
    }
    
    /**
     * Register a new customer
     *
     * @param array $userData User data
     * @return bool|array Registration result (user data or false)
     */
    public function registerUser($userData) {
        // Check if email already exists
        $existingUser = $this->customerDAO->findByEmail($userData['email']);
        
        if($existingUser) {
            return false;
        }
        
        // Insert new customer
        $result = $this->customerDAO->insert($userData);
        
        if($result) {
            // Get the new user data
            $newUser = $this->customerDAO->findByEmail($userData['email']);
            
            // Auto-login the user
            $_SESSION['user_id'] = $newUser['user_id'];
            $_SESSION['user_name'] = $newUser['name'];
            $_SESSION['user_type'] = 'customer';
            
            // Update local properties
            $this->currentUser = $newUser;
            $this->userType = 'customer';
            
            return $newUser;
        }
        
        return false;
    }
    
    /**
     * Log out current user
     *
     * @return bool Success status
     */
    public function logout() {
        // Clear session data
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Clear local properties
        $this->currentUser = null;
        $this->userType = null;
        
        return true;
    }
    
    /**
     * Check if user is logged in
     *
     * @return bool True if user is logged in
     */
    public function isLoggedIn() {
        return $this->currentUser !== null;
    }
    
    /**
     * Check if current user is admin
     *
     * @return bool True if user is admin
     */
    public function isAdmin() {
        return $this->userType === 'admin';
    }
    
    /**
     * Get current user data
     *
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        return $this->currentUser;
    }
    
    /**
     * Get current user ID
     *
     * @return int|null User ID or null if not logged in
     */
    public function getCurrentUserId() {
        return $this->currentUser ? ($this->userType === 'admin' ? $this->currentUser['admin_id'] : $this->currentUser['user_id']) : null;
    }
    
    /**
     * Get current user type
     *
     * @return string|null User type ('customer', 'admin') or null if not logged in
     */
    public function getUserType() {
        return $this->userType;
    }
    
    /**
     * Get session ID
     *
     * @return string Session ID
     */
    public function getSessionID() {
        return $this->sessionID;
    }
    
    /**
     * Load user data from session
     */
    private function loadSessionData() {
        if(isset($_SESSION['user_id'], $_SESSION['user_type'])) {
            $this->userType = $_SESSION['user_type'];
            
            if($this->userType === 'admin') {
                $this->currentUser = $this->adminDAO->findById($_SESSION['user_id']);
            } else {
                $this->currentUser = $this->customerDAO->findById($_SESSION['user_id']);
            }
        } else {
            $this->currentUser = null;
            $this->userType = null;
        }
    }
}