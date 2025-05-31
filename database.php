<?php
/**
 * Database Configuration
 * 
 * This file handles the database connection setup for the AWE Electronics Online Sales System.
 */

class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "awe_electronics";
    private $username = "root";  // Change to your MySQL username
    private $password = "";      // Change to your MySQL password
    private $conn;

    /**
     * Get database connection
     *
     * @return PDO database connection object
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>