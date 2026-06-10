<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Tienda Pública
 * ============================================
 * Endpoints públicos para la tienda virtual (e-commerce).
 * NO requiere autenticación — accesible por cualquier visitante.
 */

require_once __DIR__ . '/../models/TiendaModel.php';
require_once __DIR__ . '/../helpers/Response.php';

class TiendaController {
    private $model;

    public function __construct() {
        $this->model = new TiendaModel();
    }

    /**
     * GET /api/tienda/catalogo
     * Obtener todos los productos disponibles para la venta
     */
    public function catalogo() {
        $productos = $this->model->getCatalogo();
        Response::success($productos, 'Catálogo obtenido.');
    }

    /**
     * GET /api/tienda/categorias
     * Obtener categorías con productos disponibles
     */
    public function categorias() {
        $categorias = $this->model->getCategorias();
        Response::success($categorias, 'Categorías obtenidas.');
    }

    /**
     * POST /api/tienda/checkout
     * Procesar compra desde la tienda virtual
     * 
     * Body: {
     *   "cliente": { "nombre": "...", "rut": "...", "email": "...", "telefono": "...", "direccion": "..." },
     *   "items": [{ "id_producto": 1, "cantidad": 2, "precio": 12000, "subtotal": 24000 }, ...]
     * }
     */
    public function checkout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos del cliente
        $cliente = $data['cliente'] ?? null;
        $items = $data['items'] ?? [];

        if (!$cliente) {
            Response::error('Datos del cliente son obligatorios.');
        }
        if (empty($cliente['nombre']) || empty($cliente['rut'])) {
            Response::error('Nombre y RUT del cliente son obligatorios.');
        }
        if (empty($items)) {
            Response::error('El carrito está vacío.');
        }

        try {
            // Buscar si el cliente ya existe por RUT
            $clienteExistente = $this->model->buscarClientePorRut($cliente['rut']);

            if ($clienteExistente) {
                $id_cliente = $clienteExistente['id_cliente'];
            } else {
                // Crear nuevo cliente
                $id_cliente = $this->model->crearClienteTienda(
                    $cliente['nombre'],
                    $cliente['rut'],
                    $cliente['email'] ?? null,
                    $cliente['telefono'] ?? null,
                    $cliente['direccion'] ?? null
                );
            }

            // Crear el pedido
            $id_pedido = $this->model->crearPedidoTienda($id_cliente, $items);

            Response::success(
                [
                    'id_pedido' => $id_pedido,
                    'cliente_nuevo' => !$clienteExistente,
                    'id_cliente' => $id_cliente
                ],
                "¡Pedido #$id_pedido registrado exitosamente! Tu pedido está pendiente de confirmación.",
                201
            );

        } catch (Exception $e) {
            Response::error('Error al procesar el pedido: ' . $e->getMessage());
        }
    }
}
?>
