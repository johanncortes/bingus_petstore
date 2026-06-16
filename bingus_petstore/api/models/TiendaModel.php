<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Tienda Pública
 * ============================================
 * Acceso a datos para la tienda virtual (e-commerce).
 * Incluye cálculo de IVA en catálogo y checkout.
 */

require_once __DIR__ . '/../config/Database.php';

class TiendaModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener la tasa de IVA vigente
     * @return float Porcentaje de IVA (ej: 19.00)
     */
    public function getTasaIVA() {
        $stmt = $this->conn->prepare(
            "SELECT porcentaje FROM configuracion_impuestos WHERE nombre = 'IVA' AND activo = 1 ORDER BY fecha_vigencia DESC LIMIT 1"
        );
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result ? (float)$result : 19.00; // Default 19% si no se encuentra
    }

    /**
     * Obtener catálogo de productos disponibles (activos + stock > 0)
     * Incluye categoría, descripción, imagen y desglose de IVA
     */
    public function getCatalogo() {
        $tasa_iva = $this->getTasaIVA();

        $query = "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen,
                         c.nombre as categoria_nombre, c.id_categoria
                  FROM productos p
                  JOIN categorias_productos c ON p.id_categoria = c.id_categoria
                  WHERE p.activo = 1 AND p.stock > 0
                  ORDER BY c.nombre, p.nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $productos = $stmt->fetchAll();

        // Agregar desglose de IVA a cada producto
        // Los precios en BD son NETOS, el IVA se agrega encima
        foreach ($productos as &$p) {
            $precio_neto = (float)$p['precio'];
            $iva = round($precio_neto * ($tasa_iva / 100), 2);
            $precio_total = round($precio_neto + $iva, 2);

            $p['precio_neto'] = $precio_neto;
            $p['iva'] = $iva;
            $p['precio_total'] = $precio_total;
            $p['tasa_iva'] = $tasa_iva;
        }
        unset($p);

        return $productos;
    }

    /**
     * Obtener todas las categorías que tienen productos disponibles
     */
    public function getCategorias() {
        $query = "SELECT DISTINCT c.id_categoria, c.nombre, c.descripcion
                  FROM categorias_productos c
                  JOIN productos p ON c.id_categoria = p.id_categoria
                  WHERE p.activo = 1 AND p.stock > 0
                  ORDER BY c.nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Buscar cliente por RUT
     */
    public function buscarClientePorRut($rut) {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE rut = ? LIMIT 1");
        $stmt->execute([$rut]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo cliente desde la tienda
     * @return int ID del cliente creado
     */
    public function crearClienteTienda($nombre, $rut, $email, $telefono, $direccion) {
        $stmt = $this->conn->prepare(
            "INSERT INTO clientes (nombre, rut, email, telefono, direccion) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $rut, $email, $telefono, $direccion]);
        return $this->conn->lastInsertId();
    }

    // ========== AUTH DE CLIENTES ==========

    /**
     * Registrar un nuevo cliente con contraseña
     * @return int ID del cliente creado
     */
    public function registrarCliente($nombre, $rut, $email, $password, $telefono, $direccion) {
        $stmt = $this->conn->prepare(
            "INSERT INTO clientes (nombre, rut, email, telefono, direccion, password) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $rut, $email, $telefono, $direccion, $password]);
        return $this->conn->lastInsertId();
    }

    /**
     * Login de cliente por email + password
     * @return array|false Datos del cliente o false
     */
    public function loginCliente($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch();

        // Comparación en texto plano (sin hash)
        if ($cliente && $cliente['password'] && $password === $cliente['password']) {
            return $cliente;
        }
        return false;
    }

    /**
     * Obtener cliente por ID
     */
    public function getClienteById($id) {
        $stmt = $this->conn->prepare("SELECT id_cliente, nombre, rut, email, telefono, direccion FROM clientes WHERE id_cliente = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Verificar si un email ya está registrado
     */
    public function existeEmail($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Crear pedido completo desde la tienda virtual con IVA
     * - Estado por defecto: PENDIENTE
     * - Repartidor: NULL (se asigna después por el admin)
     * - IVA se calcula y almacena por cada línea
     *
     * @param int $id_cliente
     * @param array $items [['id_producto'=>..., 'cantidad'=>..., 'precio'=>..., 'subtotal'=>...], ...]
     *                     'precio' y 'subtotal' incluyen IVA (precio_total)
     * @param string|null $direccion_entrega
     * @return int ID del pedido creado
     */
    public function crearPedidoTienda($id_cliente, $items, $direccion_entrega = null) {
        $tasa_iva = $this->getTasaIVA();

        $this->conn->beginTransaction();

        try {
            // Calcular totales con desglose de IVA
            $total_con_iva = 0;
            $total_neto = 0;
            $total_iva = 0;

            foreach ($items as &$item) {
                // El precio que viene del frontend incluye IVA
                $precio_con_iva = (float)$item['precio'];
                $precio_neto = round($precio_con_iva / (1 + $tasa_iva / 100), 2);
                $iva_unitario = round($precio_con_iva - $precio_neto, 2);

                $subtotal = round($precio_con_iva * $item['cantidad'], 2);
                $neto_linea = round($precio_neto * $item['cantidad'], 2);
                $iva_linea = round($iva_unitario * $item['cantidad'], 2);

                $item['precio_neto'] = $precio_neto;
                $item['iva'] = $iva_linea;
                $item['subtotal'] = $subtotal;

                $total_con_iva += $subtotal;
                $total_neto += $neto_linea;
                $total_iva += $iva_linea;
            }
            unset($item);

            // Insertar cabecera del pedido (sin repartidor asignado)
            $stmt = $this->conn->prepare(
                "INSERT INTO pedidos (id_cliente, id_repartidor, fecha, estado, direccion_entrega, total, subtotal_neto, total_iva) 
                 VALUES (?, NULL, NOW(), 'PENDIENTE', ?, ?, ?, ?)"
            );
            $stmt->execute([$id_cliente, $direccion_entrega, $total_con_iva, $total_neto, $total_iva]);
            $id_pedido = $this->conn->lastInsertId();

            // Insertar detalles y verificar stock
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
