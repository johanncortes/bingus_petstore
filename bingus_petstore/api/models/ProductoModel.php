<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Productos
 * ============================================
 * Acceso a datos para productos, categorías y proveedores.
 */

require_once __DIR__ . '/../config/Database.php';

class ProductoModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los productos activos con categoría y proveedor
     */
    public function getProductos() {
        $query = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as proveedor_nombre 
                  FROM productos p 
                  JOIN categorias_productos c ON p.id_categoria = c.id_categoria 
                  JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
                  WHERE p.activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener un producto por ID
     */
    public function getProductoById($id_producto) {
        $query = "SELECT p.*, c.nombre as categoria_nombre, pr.nombre as proveedor_nombre 
                  FROM productos p 
                  JOIN categorias_productos c ON p.id_categoria = c.id_categoria 
                  JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
                  WHERE p.id_producto = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id_producto]);
        return $stmt->fetch();
    }

    /**
     * Insertar nuevo producto
     */
    public function insertProducto($nombre, $descripcion, $id_categoria, $id_proveedor, $precio, $stock, $imagen) {
        $query = "INSERT INTO productos (nombre, descripcion, id_categoria, id_proveedor, precio, stock, imagen, activo) 
                  VALUES (:nombre, :descripcion, :id_categoria, :id_proveedor, :precio, :stock, :imagen, 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nombre' => $nombre, ':descripcion' => $descripcion,
            ':id_categoria' => $id_categoria, ':id_proveedor' => $id_proveedor,
            ':precio' => $precio, ':stock' => $stock, ':imagen' => $imagen
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Actualizar producto existente
     */
    public function updateProducto($id, $nombre, $descripcion, $cat, $prov, $precio, $stock, $imagen = null) {
        if ($imagen) {
            $sql = "UPDATE productos SET nombre=?, descripcion=?, id_categoria=?, id_proveedor=?, precio=?, stock=?, imagen=? WHERE id_producto=?";
            $params = [$nombre, $descripcion, $cat, $prov, $precio, $stock, $imagen, $id];
        } else {
            $sql = "UPDATE productos SET nombre=?, descripcion=?, id_categoria=?, id_proveedor=?, precio=?, stock=? WHERE id_producto=?";
            $params = [$nombre, $descripcion, $cat, $prov, $precio, $stock, $id];
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminar solo la imagen del producto
     */
    public function eliminarFotoProducto($id_producto) {
        $sql = "UPDATE productos SET imagen = NULL WHERE id_producto = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_producto]);
    }

    /**
     * Soft delete de producto (activo = 0)
     */
    public function deleteProducto($id) {
        $query = "UPDATE productos SET activo = 0 WHERE id_producto = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Verificar si ya existe un producto con ese nombre
     */
    public function existeProducto($nombre, $excluir_id = null) {
        if ($excluir_id) {
            $query = "SELECT id_producto FROM productos WHERE nombre = ? AND activo = 1 AND id_producto != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$nombre, $excluir_id]);
        } else {
            $query = "SELECT id_producto FROM productos WHERE nombre = ? AND activo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$nombre]);
        }
        return $stmt->fetch();
    }

    /**
     * Obtener todas las categorías
     */
    public function getCategorias() {
        return $this->conn->query("SELECT * FROM categorias_productos")->fetchAll();
    }

    /**
     * Obtener todos los proveedores
     */
    public function getProveedores() {
        return $this->conn->query("SELECT * FROM proveedores")->fetchAll();
    }
}
?>
