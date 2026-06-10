<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Clientes
 * ============================================
 * Acceso a datos para gestión de clientes.
 */

require_once __DIR__ . '/../config/Database.php';

class ClienteModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los clientes
     */
    public function getClientes() {
        $stmt = $this->conn->query(
            "SELECT c.*,
                    COUNT(p.id_pedido) as total_pedidos,
                    COALESCE(SUM(p.total), 0) as total_gastado,
                    MAX(p.fecha) as ultima_compra
             FROM clientes c
             LEFT JOIN pedidos p ON p.id_cliente = c.id_cliente
             GROUP BY c.id_cliente, c.nombre, c.rut, c.email, c.telefono, c.direccion
             ORDER BY c.nombre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Obtener un cliente por ID
     */
    public function getClienteById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Verificar si existe un RUT
     */
    public function existeRut($rut) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM clientes WHERE rut = ?");
        $stmt->execute([$rut]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Crear un nuevo cliente
     */
    public function insertCliente($nombre, $rut, $email, $telefono, $direccion) {
        $stmt = $this->conn->prepare(
            "INSERT INTO clientes (nombre, rut, email, telefono, direccion) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $rut, $email, $telefono, $direccion]);
        return $this->conn->lastInsertId();
    }
}
?>
