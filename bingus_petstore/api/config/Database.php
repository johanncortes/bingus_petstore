<?php
/**
 * ============================================
 * CAPA 3 — Acceso a Datos: Conexión a BD
 * ============================================
 * Clase Singleton para manejar la conexión PDO.
 * Reemplaza la función global conectarBD().
 */

class Database {
    private static $instance = null;
    private $connection;

    // Credenciales BD (XAMPP default)
    private $host = 'localhost';
    private $db_name = 'bingus_petstore2';
    private $username = 'root';
    private $password = '';

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error de conexión a BD: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * Obtener la instancia única de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener el objeto PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    // Evitar clonación y deserialización
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("No se puede deserializar un Singleton.");
    }
}
?>
