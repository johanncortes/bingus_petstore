<?php
require_once 'config.php';

class Producto_model {
    private $conexion;

    public function __construct() {
        $this->conexion = conectarBD();
    }

    public function getProductos() {
        $query = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as proveedor_nombre 
                  FROM productos p 
                  JOIN categorias_productos c ON p.id_categoria = c.id_categoria 
                  JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
                  WHERE p.activo = 1"; 
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductoById($id_producto) {
        $query = "SELECT * FROM productos WHERE id_producto = :id_producto";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_producto', $id_producto);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertProducto($nombre, $descripcion, $id_categoria, $id_proveedor, $precio, $stock, $imagen) {
        $query = "INSERT INTO productos (nombre, descripcion, id_categoria, id_proveedor, precio, stock, imagen, activo) 
                  VALUES (:nombre, :descripcion, :id_categoria, :id_proveedor, :precio, :stock, :imagen, 1)";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute([
            ':nombre' => $nombre, ':descripcion' => $descripcion, 
            ':id_categoria' => $id_categoria, ':id_proveedor' => $id_proveedor, 
            ':precio' => $precio, ':stock' => $stock, ':imagen' => $imagen
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateProducto($id, $nombre, $descripcion, $cat, $prov, $precio, $stock, $imagen = null) {
        if ($imagen) {
            $sql = "UPDATE productos SET nombre=?, descripcion=?, id_categoria=?, id_proveedor=?, precio=?, stock=?, imagen=? WHERE id_producto=?";
            $params = [$nombre, $descripcion, $cat, $prov, $precio, $stock, $imagen, $id];
        } else {
            $sql = "UPDATE productos SET nombre=?, descripcion=?, id_categoria=?, id_proveedor=?, precio=?, stock=? WHERE id_producto=?";
            $params = [$nombre, $descripcion, $cat, $prov, $precio, $stock, $id];
        }
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($params);
    }

    // NUEVA FUNCIÓN: Eliminar solo la referencia de la imagen
    public function eliminarFotoProducto($id_producto) {
        $sql = "UPDATE productos SET imagen = NULL WHERE id_producto = ?";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$id_producto]);
    }

    public function deleteProducto($id) {
        $query = "UPDATE productos SET activo = 0 WHERE id_producto = ?";
        $stmt = $this->conexion->prepare($query);
        return $stmt->execute([$id]);
    }
    
    public function existeProducto($nombre) {
        $query = "SELECT id_producto FROM productos WHERE nombre = ? AND activo = 1";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute([$nombre]);
        return $stmt->fetch();
    }

    public function getCategorias() { return $this->conexion->query("SELECT * FROM categorias_productos")->fetchAll(); }
    public function getProveedores() { return $this->conexion->query("SELECT * FROM proveedores")->fetchAll(); }
}
?>