<?php
/**
 * ============================================
 * CAPA 3 — Acceso a Datos: Conexión a BD
 * ============================================
 * Clase Singleton para manejar la conexión PDO.
 * Lee credenciales desde Config.php para soportar
 * la arquitectura distribuida de 3 máquinas.
 */

require_once __DIR__ . '/Config.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $db   = defined('DB_NAME') ? DB_NAME : 'bingus_petstore2';
            $user = defined('DB_USER') ? DB_USER : 'root';
            $pass = defined('DB_PASS') ? DB_PASS : '';

            $this->connection = new PDO(
                "mysql:host={$host};dbname={$db};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error de conexión a BD: ' . $e->getMessage(),
                'hint' => 'Verifica que DB_HOST, DB_USER y DB_PASS estén configurados en Config.php'
            ]);
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
