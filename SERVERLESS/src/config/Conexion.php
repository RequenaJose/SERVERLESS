<?php
class Conexion {
    // Parámetros por defecto de XAMPP
    private $host = 'localhost';
    private $db_name = 'db_emergencias';
    private $username = 'root'; 
    private $password = ''; 
    private $conn;

    public function conectar() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", 
                $this->username, 
                $this->password
            );
            // Configurar PDO para que lance excepciones ante errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die(json_encode([
                "success" => false, 
                "message" => "Error de conexión a BD: " . $e->getMessage()
            ]));
        }

        return $this->conn;
    }
}
?>