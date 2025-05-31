<?php
/**
 * Customer Model Class
 * 
 * Represents a customer in the system
 */
class Customer {
    // Properties
    private $userID;
    private $name;
    private $email;
    private $password;
    private $phone;
    private $address;
    
    // Database connection and DAO
    private $conn;
    private $customerDAO;
    private $orderDAO;
    
    /**
     * Constructor
     *
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
        $this->customerDAO = new CustomerDAO($db);
        $this->orderDAO = new OrderDAO($db);
    }
    
    /**
     * Load customer data by ID
     *
     * @param int $userId User ID
     * @return bool Success status
     */
    public function loadById($userId) {
        $customer = $this->customerDAO->findById($userId);
        
        if(!$customer) {
            return false;
        }
        
        $this->userID = $customer['user_id'];
        $this->name = $customer['name'];
        $this->email = $customer['email'];
        $this->password = $customer['password']; // Note: This is already hashed
        $this->phone = $customer['phone'];
        $this->address = $customer['address'];
        
        return true;
    }
    
    /**
     * Load customer data by email
     *
     * @param string $email Email address
     * @return bool Success status
     */
    public function loadByEmail($email) {
        $customer = $this->customerDAO->findByEmail($email);
        
        if(!$customer) {
            return false;
        }
        
        $this->userID = $customer['user_id'];
        $this->name = $customer['name'];
        $this->email = $customer['email'];
        $this->password = $customer['password']; // Note: This is already hashed
        $this->phone = $customer['phone'];
        $this->address = $customer['address'];
        
        return true;
    }
    
    /**
     * Save customer data (create or update)
     *
     * @return bool Success status
     */
    public function save() {
        $customerData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address
        ];
        
        if(isset($this->userID)) {
            // Update existing customer
            $customerData['user_id'] = $this->userID;
            return $this->customerDAO->update($customerData);
        } else {
            // Create new customer
            $customerData['password'] = $this->password;
            $result = $this->customerDAO->insert($customerData);
            
            if($result) {
                $this->loadByEmail($this->email);
                return true;
            }
            
            return false;
        }
    }
    
    /**
     * View order history for this customer
     *
     * @return array Orders belonging to this customer
     */
    public function viewOrderHistory() {
        if(!$this->userID) {
            return [];
        }
        
        return $this->orderDAO->findByUser($this->userID);
    }
    
    /**
     * Update customer profile
     *
     * @param array $profileData New profile data
     * @return bool Success status
     */
    public function updateProfile($profileData) {
        if(!$this->userID) {
            return false;
        }
        
        // Update local properties
        $this->name = isset($profileData['name']) ? $profileData['name'] : $this->name;
        $this->email = isset($profileData['email']) ? $profileData['email'] : $this->email;
        $this->phone = isset($profileData['phone']) ? $profileData['phone'] : $this->phone;
        $this->address = isset($profileData['address']) ? $profileData['address'] : $this->address;
        
        // Save to database
        return $this->save();
    }
    
    // Getters and Setters
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        $this->email = $email;
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
    
    public function getPhone() {
        return $this->phone;
    }
    
    public function setPhone($phone) {
        $this->phone = $phone;
    }
    
    public function getAddress() {
        return $this->address;
    }
    
    public function setAddress($address) {
        $this->address = $address;
    }
    
    /**
     * Apply member benefits (not implemented yet)
     *
     * @return string Message about feature not being implemented
     */
    public function applyMemberBenefits() {
        return "Member benefits not implemented yet.";
    }
}
?>