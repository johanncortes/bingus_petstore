<?php
require_once 'config.php';

class Pedido_model {
    private $conexion;

    public function __construct() {
        $this->conexion = conectarBD();
    }

    public function getPedidos() {
        $query = "SELECT * FROM v_pedidos_detalle ORDER BY fecha DESC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // MEJORADO: Trae nombres de Cliente y Vendedor (incluso si se borraron)
    public function getPedidoById($id) {
        $query = "SELECT p.*, 
                         COALESCE(c.nombre, 'Cliente Eliminado') as cliente_nombre,
                         COALESCE(c.rut, 'S/RUT') as cliente_rut,
                         COALESCE(v.nombre, 'Vendedor Eliminado') as vendedor_nombre 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                  LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                  WHERE p.id_pedido = :id";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Detalle de productos (Mantiene la lógica de ver productos borrados)
    public function getDetallesPedido($id_pedido) {
        $query = "SELECT dp.*, 
                         COALESCE(p.nombre, '🚫 Producto Eliminado (Histórico)') as producto_nombre 
                  FROM detalle_pedido dp 
                  LEFT JOIN productos p ON dp.id_producto = p.id_producto 
                  WHERE dp.id_pedido = :id_pedido";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_pedido', $id_pedido);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEstadoPedido($id_pedido, $estado) {
        $query = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_pedido', $id_pedido);
        return $stmt->execute();
    }

    public function descontarStockDePedido($id_pedido) {
        $sql = "UPDATE productos p 
                JOIN detalle_pedido dp ON p.id_producto = dp.id_producto 
                SET p.stock = p.stock - dp.cantidad 
                WHERE dp.id_pedido = :id_pedido";
                
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id_pedido' => $id_pedido]);
        return $stmt->rowCount();
    }
}
?>