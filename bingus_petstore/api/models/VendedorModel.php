<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Vendedores
 * ============================================
 * Acceso a datos para gestión de vendedores.
 * Contraseñas en texto plano (sin hash).
 */

require_once __DIR__ . '/../config/Database.php';

class VendedorModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Verificar si existe un RUT registrado
     */
    public function existeRut($rut, $excluir_id = null) {
        if ($excluir_id) {
            $stmt = $this->conn->prepare("SELECT id_vendedor FROM vendedores WHERE rut = :rut AND id_vendedor != :id LIMIT 1");
            $stmt->execute([':rut' => $rut, ':id' => $excluir_id]);
        } else {
            $stmt = $this->conn->prepare("SELECT id_vendedor FROM vendedores WHERE rut = :rut LIMIT 1");
            $stmt->execute([':rut' => $rut]);
        }
        return $stmt->fetch();
    }

    /**
     * Verificar si existe un Email registrado
     */
    public function existeEmail($email, $excluir_id = null) {
        if ($excluir_id) {
            $stmt = $this->conn->prepare("SELECT id_vendedor FROM vendedores WHERE email = :email AND id_vendedor != :id LIMIT 1");
            $stmt->execute([':email' => $email, ':id' => $excluir_id]);
        } else {
            $stmt = $this->conn->prepare("SELECT id_vendedor FROM vendedores WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
        }
        return $stmt->fetch();
    }

    /**
     * Insertar nuevo vendedor (contraseña en texto plano)
     */
    public function insertVendedor($nombre, $rut, $email, $telefono, $fecha, $id_admin, $contrasena) {
        $query = "INSERT INTO vendedores (nombre, rut, email, telefono, fecha_contratacion, id_administrador, contrasena, activo) 
                  VALUES (:nombre, :rut, :email, :telefono, :fecha, :id_admin, :contrasena, 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nombre' => $nombre,
            ':rut' => $rut,
            ':email' => $email,
            ':telefono' => $telefono,
            ':fecha' => $fecha,
            ':id_admin' => $id_admin,
            ':contrasena' => $contrasena  // Texto plano, sin hash
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Actualizar datos de un vendedor
     */
    public function updateVendedor($id_vendedor, $nombre, $rut, $email, $telefono, $fecha, $id_admin) {
        $query = "UPDATE vendedores SET nombre = :nombre, rut = :rut, email = :email, telefono = :telefono, fecha_contratacion = :fecha 
                  WHERE id_vendedor = :id_vendedor AND id_administrador = :id_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nombre' => $nombre, ':rut' => $rut, ':email' => $email,
            ':telefono' => $telefono, ':fecha' => $fecha,
            ':id_vendedor' => $id_vendedor, ':id_admin' => $id_admin
        ]);
        return $stmt->rowCount() >= 0; // >= 0 porque puede no haber cambios
    }

    /**
     * Obtener vendedores de un administrador específico
     */
    public function getVendedoresPorAdmin($id_administrador) {
        $query = "SELECT * FROM vendedores WHERE id_administrador = :id_admin AND activo = 1 ORDER BY fecha_contratacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_admin' => $id_administrador]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener un vendedor por ID (verificando que pertenezca al admin)
     */
    public function getVendedorById($id_vendedor, $id_administrador) {
        $query = "SELECT * FROM vendedores WHERE id_vendedor = :id_vendedor AND id_administrador = :id_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_vendedor' => $id_vendedor, ':id_admin' => $id_administrador]);
        return $stmt->fetch();
    }

    /**
     * Soft delete de vendedor
     */
    public function deleteVendedor($id_vendedor, $id_admin) {
        $query = "UPDATE vendedores SET activo = 0 WHERE id_vendedor = :id_vendedor AND id_administrador = :id_admin";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_vendedor' => $id_vendedor, ':id_admin' => $id_admin]);
        return $stmt->rowCount() > 0;
    }
}
?>
