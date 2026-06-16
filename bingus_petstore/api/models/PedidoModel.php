<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Pedidos
 * ============================================
 * Acceso a datos para pedidos y detalle de pedidos.
 * Incluye soporte para repartidores e IVA.
 */

require_once __DIR__ . '/../config/Database.php';

class PedidoModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los pedidos con datos de cliente y repartidor
     */
    public function getPedidos() {
        $query = "SELECT * FROM v_pedidos_detalle ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener un pedido por ID con nombres de cliente y repartidor
     */
    public function getPedidoById($id) {
        $query = "SELECT p.*, 
                         COALESCE(c.nombre, 'Cliente Eliminado') as cliente_nombre,
                         COALESCE(c.rut, 'S/RUT') as cliente_rut,
                         COALESCE(c.telefono, 'S/Tel') as cliente_telefono,
                         COALESCE(c.direccion, 'S/Dir') as cliente_direccion,
                         COALESCE(r.nombre, 'Sin asignar') as repartidor_nombre 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                  LEFT JOIN repartidores r ON p.id_repartidor = r.id_repartidor 
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
     * Asignar repartidor a un pedido
     */
    public function asignarRepartidor($id_pedido, $id_repartidor) {
        $query = "UPDATE pedidos SET id_repartidor = :id_repartidor WHERE id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_repartidor' => $id_repartidor, ':id_pedido' => $id_pedido]);
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
     * Crear un pedido completo (cabecera + detalle) con IVA
     * @param int $id_cliente
     * @param string $estado - 'PENDIENTE', 'PAGADO', etc.
     * @param array $items - [['id_producto'=>..., 'cantidad'=>..., 'precio'=>..., 'subtotal'=>...], ...]
     * @param string|null $direccion_entrega
     * @param float $tasa_iva - Tasa de IVA (ej: 19.00)
     * @return int - ID del pedido creado
     */
    public function crearPedido($id_cliente, $estado, $items, $direccion_entrega = null, $tasa_iva = 19.00) {
        $this->conn->beginTransaction();

        try {
            // Calcular totales con IVA
            $total_con_iva = 0;
            $total_neto = 0;
            $total_iva = 0;

            foreach ($items as &$item) {
                $subtotal = $item['subtotal']; // precio * cantidad (precio ya incluye IVA conceptualmente, pero aquí viene neto)
                $neto_unitario = round($item['precio'] / (1 + $tasa_iva / 100), 2);
                $iva_unitario = round($item['precio'] - $neto_unitario, 2);
                $neto_linea = round($neto_unitario * $item['cantidad'], 2);
                $iva_linea = round($iva_unitario * $item['cantidad'], 2);

                $item['precio_neto'] = $neto_unitario;
                $item['iva'] = $iva_linea;

                $total_con_iva += $subtotal;
                $total_neto += $neto_linea;
                $total_iva += $iva_linea;
            }
            unset($item);

            // Insertar cabecera
            $stmt = $this->conn->prepare(
                "INSERT INTO pedidos (id_cliente, id_repartidor, fecha, estado, direccion_entrega, total, subtotal_neto, total_iva) 
                 VALUES (?, NULL, NOW(), ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$id_cliente, $estado, $direccion_entrega, $total_con_iva, $total_neto, $total_iva]);
            $id_pedido = $this->conn->lastInsertId();

            // Insertar detalles con IVA
            foreach ($items as $item) {
                // Verificar stock actual
                $stmt = $this->conn->prepare("SELECT stock, nombre FROM productos WHERE id_producto = ? AND activo = 1");
                $stmt->execute([$item['id_producto']]);
                $producto = $stmt->fetch();

                if (!$producto) {
                    throw new Exception("Producto ID " . $item['id_producto'] . " no disponible.");
                }

                if ($producto['stock'] < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para \"{$producto['nombre']}\". Disponible: {$producto['stock']}, Solicitado: {$item['cantidad']}.");
                }

                // Guardar detalle con IVA
                $this->conn->prepare(
                    "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, precio_neto, iva, subtotal) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                )->execute([
                    $id_pedido, $item['id_producto'], $item['cantidad'], 
                    $item['precio'], $item['precio_neto'], $item['iva'], $item['subtotal']
                ]);

                // Descontar stock si es PAGADO
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
