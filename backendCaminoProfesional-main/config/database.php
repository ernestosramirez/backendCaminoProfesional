<?php
class Database {
    private $host = "localhost";
    private $db_name = "usuarios"; // Pon aquí el nombre exacto que tienes en phpMyAdmin
    private $username = "root";          // ← CAMBIA ESTO
    private $password = "";              // ← CAMBIA ESTO
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}
?>