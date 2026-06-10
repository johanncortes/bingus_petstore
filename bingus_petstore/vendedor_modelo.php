<?php
require_once 'config.php';

class Vendedor_model {
    private $conexion;

    public function __construct() {
        $this->conexion = conectarBD();
    }

    // Verificar si existe RUT
    public function existeRut($rut) {
        $query = "SELECT id_vendedor FROM vendedores WHERE rut = :rut LIMIT 1";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':rut', $rut);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verificar si existe Email (Para evitar usuarios duplicados)
    public function existeEmail($email) {
        $query = "SELECT id_vendedor FROM vendedores WHERE email = :email LIMIT 1";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // INSERTAR AHORA CON CONTRASEÑA
    public function insertVendedor($nombre, $rut, $email, $telefono, $fecha, $id_admin, $contrasena) {
        $query = "INSERT INTO vendedores (nombre, rut, email, telefono, fecha_contratacion, id_administrador, contrasena, activo) 
                  VALUES (:nombre, :rut, :email, :telefono, :fecha, :id_admin, :contrasena, 1)";
        $stmt = $this->conexion->prepare($query);
        
        $stmt->execute([
            ':nombre' => $nombre,
            ':rut' => $rut,
            ':email' => $email,
            ':telefono' => $telefono,
            ':fecha' => $fecha,
            ':id_admin' => $id_admin,
            ':contrasena' => $contrasena // Aquí guardamos el hash
        ]);
        
        return $stmt->rowCount() > 0;
    }

    // Modificar vendedor (Opcional: aquí podrías agregar lógica para cambiar pass si quisieras)
    public function updateVendedor($id_vendedor, $nombre, $rut, $email, $telefono, $fecha, $id_admin) {
        $query = "UPDATE vendedores SET nombre = :nombre, rut = :rut, email = :email, telefono = :telefono, fecha_contratacion = :fecha 
                  WHERE id_vendedor = :id_vendedor AND id_administrador = :id_admin";
        $stmt = $this->conexion->prepare($query);
        
        $stmt->execute([
            ':nombre' => $nombre, ':rut' => $rut, ':email' => $email,
            ':telefono' => $telefono, ':fecha' => $fecha,
            ':id_vendedor' => $id_vendedor, ':id_admin' => $id_admin
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getVendedoresPorAdmin($id_administrador) {
        $query = "SELECT * FROM vendedores WHERE id_administrador = :id_admin AND activo = 1 ORDER BY fecha_contratacion DESC";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_admin', $id_administrador);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getVendedorById($id_vendedor, $id_administrador) {
        $query = "SELECT * FROM vendedores WHERE id_vendedor = :id_vendedor AND id_administrador = :id_admin";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        $stmt->bindParam(':id_admin', $id_administrador);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deleteVendedor($id_vendedor, $id_admin) {
        $query = "UPDATE vendedores SET activo = 0 WHERE id_vendedor = :id_vendedor AND id_administrador = :id_admin";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        $stmt->bindParam(':id_admin', $id_admin);
        return $stmt->execute();
    }
}
?>