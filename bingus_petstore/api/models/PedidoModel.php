<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Pedidos
 * ============================================
 * Acceso a datos para pedidos y detalle de pedidos.
 */

require_once __DIR__ . '/../config/Database.php';

class PedidoModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los pedidos con datos de cliente y vendedor
     */
    public function getPedidos() {
        $query = "SELECT * FROM v_pedidos_detalle ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener un pedido por ID con nombres de cliente y vendedor
     */
    public function getPedidoById($id) {
        $query = "SELECT p.*, 
                         COALESCE(c.nombre, 'Cliente Eliminado') as cliente_nombre,
                         COALESCE(c.rut, 'S/RUT') as cliente_rut,
                         COALESCE(v.nombre, 'Vendedor Eliminado') as vendedor_nombre 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                  LEFT JOIN vendedores v ON p.id_vendedor = v.id_vendedor 
                  WHERE p.id_pedido = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Obtener los productos de un pedido
     */
    public function getDetallesPedido($id_pedido) {
        $query = "SELECT dp.*, 
                         COALESCE(p.nombre, '🚫 Producto Eliminado (Histórico)') as producto_nombre 
                  FROM detalle_pedido dp 
                  LEFT JOIN productos p ON dp.id_producto = p.id_producto 
                  WHERE dp.id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_pedido' => $id_pedido]);
        return $stmt->fetchAll();
    }

    /**
     * Actualizar el estado de un pedido
     */
    public function updateEstadoPedido($id_pedido, $estado) {
        $query = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':estado' => $estado, ':id_pedido' => $id_pedido]);
        return $stmt->rowCount();
    }

    /**
     * Descontar stock cuando pedido pasa a PAGADO
     */
    public function descontarStockDePedido($id_pedido) {
        $sql = "UPDATE productos p 
                JOIN detalle_pedido dp ON p.id_producto = dp.id_producto 
                SET p.stock = p.stock - dp.cantidad 
                WHERE dp.id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_pedido' => $id_pedido]);
        return $stmt->rowCount();
    }

    /**
     * Crear un pedido completo (cabecera + detalle) — usado por POS
     * @param int $id_cliente
     * @param int $id_vendedor
     * @param string $estado - 'PAGADO' o 'PENDIENTE'
     * @param array $items - [['id_producto'=>..., 'cantidad'=>..., 'precio'=>..., 'subtotal'=>...], ...]
     * @return int - ID del pedido creado
     */
    public function crearPedido($id_cliente, $id_vendedor, $estado, $items) {
        $this->conn->beginTransaction();

        try {
            // Calcular total
            $total = 0;
            foreach ($items as $item) {
                $total += $item['subtotal'];
            }

            // Insertar cabecera
            $stmt = $this->conn->prepare(
                "INSERT INTO pedidos (id_cliente, id_vendedor, fecha, estado, total) VALUES (?, ?, NOW(), ?, ?)"
            );
            $stmt->execute([$id_cliente, $id_vendedor, $estado, $total]);
            $id_pedido = $this->conn->lastInsertId();

            // Insertar detalles y verificar stock
            foreach ($items as $item) {
                // Verificar stock actual
                $stmt = $this->conn->prepare("SELECT stock FROM productos WHERE id_producto = ?");
                $stmt->execute([$item['id_producto']]);
                $stock_db = $stmt->fetchColumn();

                if ($estado === 'PAGADO' && $stock_db < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para producto ID: " . $item['id_producto']);
                }

                // Guardar detalle
                $this->conn->prepare(
                    "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)"
                )->execute([$id_pedido, $item['id_producto'], $item['cantidad'], $item['precio'], $item['subtotal']]);

                // Descontar stock solo si es PAGADO
                if ($estado === 'PAGADO') {
                    $this->conn->prepare(
                        "UPDATE productos SET stock = stock - ? WHERE id_producto = ?"
                    )->execute([$item['cantidad'], $item['id_producto']]);
                }
            }

            $this->conn->commit();
            return $id_pedido;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
?>
