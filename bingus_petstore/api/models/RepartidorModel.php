<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Repartidores
 * ============================================
 * Acceso a datos para gestión de repartidores.
 * Los repartidores son personal de entrega gestionado
 * por administradores (2 por admin máximo).
 * No tienen acceso al sistema (sin login).
 */

require_once __DIR__ . '/../config/Database.php';

class RepartidorModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Verificar si existe un RUT registrado
     */
    public function existeRut($rut, $excluir_id = null) {
        if ($excluir_id) {
            $stmt = $this->conn->prepare("SELECT id_repartidor FROM repartidores WHERE rut = :rut AND id_repartidor != :id LIMIT 1");
            $stmt->execute([':rut' => $rut, ':id' => $excluir_id]);
        } else {
            $stmt = $this->conn->prepare("SELECT id_repartidor FROM repartidores WHERE rut = :rut LIMIT 1");
            $stmt->execute([':rut' => $rut]);
        }
        return $stmt->fetch();
    }

    /**
     * Verificar si existe un Email registrado
     */
    public function existeEmail($email, $excluir_id = null) {
        if ($excluir_id) {
            $stmt = $this->conn->prepare("SELECT id_repartidor FROM repartidores WHERE email = :email AND id_repartidor != :id LIMIT 1");
            $stmt->execute([':email' => $email, ':id' => $excluir_id]);
        } else {
            $stmt = $this->conn->prepare("SELECT id_repartidor FROM repartidores WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
        }
        return $stmt->fetch();
    }

    /**
     * Contar repartidores activos de un administrador
     */
    public function contarRepartidoresPorAdmin($id_admin) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM repartidores WHERE id_administrador = :id_admin AND activo = 1");
        $stmt->execute([':id_admin' => $id_admin]);
        return $stmt->fetchColumn();
    }

    /**
     * Insertar nuevo repartidor
     */
    public function insertRepartidor($nombre, $rut, $email, $telefono, $fecha, $id_admin) {
        $query = "INSERT INTO repartidores (nombre, rut, email, telefono, fecha_contratacion, id_administrador, activo, estado_disponibilidad) 
                  VALUES (:nombre, :rut, :email, :telefono, :fecha, :id_admin, 1, 'DISPONIBLE')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nombre' => $nombre,
            ':rut' => $rut,
            ':email' => $email,
            ':telefono' => $telefono,
            ':fecha' => $fecha,
            ':id_admin' => $id_admin
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Actualizar datos de un repartidor
     */
    public function updateRepartidor($id_repartidor, $nombre, $rut, $email, $telefono, $fecha, $id_admin) {
        $query = "UPDATE repartidores SET nombre = :nombre, rut = :rut, email = :email, telefono = :telefono, fecha_contratacion = :fecha 
                  WHERE id_repartidor = :id_repartidor AND id_administrador = :id_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nombre' => $nombre, ':rut' => $rut, ':email' => $email,
            ':telefono' => $telefono, ':fecha' => $fecha,
            ':id_repartidor' => $id_repartidor, ':id_admin' => $id_admin
        ]);
        return $stmt->rowCount() >= 0;
    }

    /**
     * Cambiar estado de disponibilidad
     */
    public function cambiarDisponibilidad($id_repartidor, $estado, $id_admin) {
        $query = "UPDATE repartidores SET estado_disponibilidad = :estado 
                  WHERE id_repartidor = :id_repartidor AND id_administrador = :id_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':estado' => $estado, ':id_repartidor' => $id_repartidor, ':id_admin' => $id_admin]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Obtener repartidores de un administrador específico
     */
    public function getRepartidoresPorAdmin($id_administrador) {
        $query = "SELECT * FROM repartidores WHERE id_administrador = :id_admin AND activo = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_admin' => $id_administrador]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todos los repartidores disponibles (para asignación de pedidos)
     */
    public function getRepartidoresDisponibles() {
        $query = "SELECT r.*, a.nombre as admin_nombre 
                  FROM repartidores r 
                  JOIN administradores a ON r.id_administrador = a.id_administrador
                  WHERE r.activo = 1 AND r.estado_disponibilidad = 'DISPONIBLE' 
                  ORDER BY r.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener un repartidor por ID (verificando que pertenezca al admin)
     */
    public function getRepartidorById($id_repartidor, $id_administrador) {
        $query = "SELECT * FROM repartidores WHERE id_repartidor = :id_repartidor AND id_administrador = :id_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_repartidor' => $id_repartidor, ':id_admin' => $id_administrador]);
        return $stmt->fetch();
    }

    /**
     * Soft delete de repartidor
     */
    public function deleteRepartidor($id_repartidor, $id_admin) {
        $query = "UPDATE repartidores SET activo = 0 WHERE id_repartidor = :id_repartidor AND id_administrador = :id_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_repartidor' => $id_repartidor, ':id_admin' => $id_admin]);
        return $stmt->rowCount() > 0;
    }
}
?>
