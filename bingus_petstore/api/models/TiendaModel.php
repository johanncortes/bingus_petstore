<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Tienda Pública
 * ============================================
 * Acceso a datos para la tienda virtual (e-commerce).
 * Consultas públicas sin autenticación requerida.
 */

require_once __DIR__ . '/../config/Database.php';

class TiendaModel {
    private $conn;

    /** ID del vendedor por defecto para pedidos online */
    const VENDEDOR_ONLINE_ID = 1;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Obtener catálogo de productos disponibles (activos + stock > 0)
     * Incluye categoría, descripción e imagen
     */
    public function getCatalogo() {
        $query = "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen,
                         c.nombre as categoria_nombre, c.id_categoria
                  FROM productos p
                  JOIN categorias_productos c ON p.id_categoria = c.id_categoria
                  WHERE p.activo = 1 AND p.stock > 0
                  ORDER BY c.nombre, p.nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
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
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "INSERT INTO clientes (nombre, rut, email, telefono, direccion, password) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nombre, $rut, $email, $telefono, $direccion, $hash]);
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

        if ($cliente && $cliente['password'] && password_verify($password, $cliente['password'])) {
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
     * Crear pedido completo desde la tienda virtual
     * - Estado por defecto: PENDIENTE (no descuenta stock hasta que admin apruebe)
     * - Vendedor: VENDEDOR_ONLINE_ID (asignado automáticamente)
     *
     * @param int $id_cliente
     * @param array $items [['id_producto'=>..., 'cantidad'=>..., 'precio'=>..., 'subtotal'=>...], ...]
     * @return int ID del pedido creado
     */
    public function crearPedidoTienda($id_cliente, $items) {
        $this->conn->beginTransaction();

        try {
            // Calcular total
            $total = 0;
            foreach ($items as $item) {
                $total += $item['subtotal'];
            }

            // Insertar cabecera del pedido
            $stmt = $this->conn->prepare(
                "INSERT INTO pedidos (id_cliente, id_vendedor, fecha, estado, total) VALUES (?, ?, NOW(), 'PENDIENTE', ?)"
            );
            $stmt->execute([$id_cliente, self::VENDEDOR_ONLINE_ID, $total]);
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

                // Guardar detalle
                $this->conn->prepare(
                    "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)"
                )->execute([$id_pedido, $item['id_producto'], $item['cantidad'], $item['precio'], $item['subtotal']]);
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
