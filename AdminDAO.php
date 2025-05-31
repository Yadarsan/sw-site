<?php
/**
 * Administrator DAO Class
 * 
 * Handles database operations related to Administrator entities
 */
class AdminDAO {
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
     * Find administrator by ID
     *
     * @param int $adminId Administrator's unique identifier
     * @return array Admin data or null if not found
     */
    public function findById($adminId) {
        $query = "SELECT * FROM admins WHERE admin_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $adminId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Find administrator by name (username)
     *
     * @param string $name Administrator's name
     * @return array Admin data or null if not found
     */
    public function findByName($name) {
        $query = "SELECT * FROM admins WHERE name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }

    /**
     * Find admin by name and password for authentication
     *
     * @param string $name Admin's name
     * @param string $password Admin's password
     * @return array|null Admin data or null if authentication fails
     */
    public function findByNameAndPassword($name, $password) {
        $query = "SELECT * FROM admins WHERE name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            // Verify password
            if(password_verify($password, $admin['password'])) {
                return $admin;
            }
        }
        
        return null;
    }

    /**
     * Get all administrators
     *
     * @return array Array of all admins
     */
    public function getAll() {
        $query = "SELECT * FROM admins";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update administrator role
     *
     * @param int $adminId Admin ID
     * @param string $role New role to set
     * @return bool Success status
     */
    public function updateRole($adminId, $role) {
        $query = "UPDATE admins SET role = :role WHERE admin_id = :admin_id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':admin_id', $adminId);
        $stmt->bindParam(':role', $role);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Insert a new administrator
     *
     * @param array $admin Admin data
     * @return bool Success status
     */
    public function insert($admin) {
        // Hash the password
        $hashedPassword = password_hash($admin['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO admins (name, password, role) 
                  VALUES (:name, :password, :role)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $name = htmlspecialchars(strip_tags($admin['name']));
        $role = htmlspecialchars(strip_tags($admin['role']));
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Update administrator information
     *
     * @param array $admin Admin data to update
     * @return bool Success status
     */
    public function update($admin) {
        $query = "UPDATE admins 
                  SET name = :name, 
                      role = :role 
                  WHERE admin_id = :admin_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $adminId = htmlspecialchars(strip_tags($admin['admin_id']));
        $name = htmlspecialchars(strip_tags($admin['name']));
        $role = htmlspecialchars(strip_tags($admin['role']));
        
        // Bind parameters
        $stmt->bindParam(':admin_id', $adminId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':role', $role);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>