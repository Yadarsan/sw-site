<?php
/**
 * Customer DAO Class
 * 
 * Handles database operations related to Customer entities
 */
class CustomerDAO {
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
     * Find customer by ID
     *
     * @param int $userId Customer's unique identifier
     * @return array Customer data or null if not found
     */
    public function findById($userId) {
        $query = "SELECT * FROM customers WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Find customer by email
     *
     * @param string $email Customer's email address
     * @return array Customer data or null if not found
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM customers WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Find customer by email and password for authentication
     *
     * @param string $email Customer's email
     * @param string $password Customer's password (will be hashed and compared)
     * @return array|null Customer data or null if authentication fails
     */
    public function findByEmailAndPassword($email, $password) {
        $query = "SELECT * FROM customers WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            // Verify password
            if(password_verify($password, $customer['password'])) {
                return $customer;
            }
        }
        
        return null;
    }

    /**
     * Insert a new customer into the database
     *
     * @param array $customer Customer data
     * @return bool Success status
     */
    public function insert($customer) {
        // Hash the password
        $hashedPassword = password_hash($customer['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO customers (name, email, password, phone, address) 
                  VALUES (:name, :email, :password, :phone, :address)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $name = htmlspecialchars(strip_tags($customer['name']));
        $email = htmlspecialchars(strip_tags($customer['email']));
        $phone = htmlspecialchars(strip_tags($customer['phone']));
        $address = htmlspecialchars(strip_tags($customer['address']));
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Update customer information
     *
     * @param array $customer Customer data to update
     * @return bool Success status
     */
    public function update($customer) {
        $query = "UPDATE customers 
                  SET name = :name, 
                      email = :email, 
                      phone = :phone, 
                      address = :address 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $userId = htmlspecialchars(strip_tags($customer['user_id']));
        $name = htmlspecialchars(strip_tags($customer['name']));
        $email = htmlspecialchars(strip_tags($customer['email']));
        $phone = htmlspecialchars(strip_tags($customer['phone']));
        $address = htmlspecialchars(strip_tags($customer['address']));
        
        // Bind parameters
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Update customer's password
     *
     * @param int $userId Customer ID
     * @param string $newPassword New password to set
     * @return bool Success status
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE customers SET password = :password WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':password', $hashedPassword);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>