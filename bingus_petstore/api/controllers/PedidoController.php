<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Pedidos
 * ============================================
 * Endpoints para listar, ver detalle, crear y cambiar estado de pedidos.
 */

require_once __DIR__ . '/../models/PedidoModel.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class PedidoController {
    private $model;

    public function __construct() {
        $this->model = new PedidoModel();
    }

    /**
     * GET /api/pedidos
     * Listar todos los pedidos con sus detalles
     */
    public function listar() {
        AuthMiddleware::verificarAutenticado();
        $pedidos = $this->model->getPedidos();

        // Agregar detalles a cada pedido
        foreach ($pedidos as &$p) {
            $p['detalles'] = $this->model->getDetallesPedido($p['id_pedido']);
        }
        unset($p);

        Response::success($pedidos, 'Pedidos obtenidos.');
    }

    /**
     * GET /api/pedidos/{id}
     * Obtener un pedido con sus detalles
     */
    public function obtener($id) {
        AuthMiddleware::verificarAutenticado();
        $pedido = $this->model->getPedidoById($id);
        
        if (!$pedido) {
            Response::notFound('Pedido no encontrado.');
        }

        $pedido['detalles'] = $this->model->getDetallesPedido($id);
        Response::success($pedido);
    }

    /**
     * POST /api/pedidos
     * Crear pedido desde POS
     * Body: { "id_cliente": 1, "estado": "PAGADO", "items": [...] }
     */
    public function crear() {
        AuthMiddleware::verificarVendedor();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $usuario = AuthMiddleware::getUsuarioActual();

        $id_cliente = $data['id_cliente'] ?? null;
        $estado = $data['estado'] ?? 'PENDIENTE';
        $items = $data['items'] ?? [];

        if (empty($id_cliente)) Response::error('Debe seleccionar un cliente.');
        if (empty($items)) Response::error('El carrito está vacío.');

        try {
            $id_pedido = $this->model->crearPedido($id_cliente, $usuario['id'], $estado, $items);
            
            $msg_extra = ($estado === 'PAGADO') ? 'Stock descontado.' : 'Guardado como pendiente (Stock intacto).';
            Response::success(
                ['id_pedido' => $id_pedido],
                "Pedido #$id_pedido registrado. $msg_extra",
                201
            );
        } catch (Exception $e) {
            Response::error('Error al crear pedido: ' . $e->getMessage());
        }
    }

    /**
     * PUT /api/pedidos/{id}/estado
     * Cambiar estado de un pedido
     * Body: { "estado": "PAGADO|CANCELADO" }
     */
    public function cambiarEstado($id) {
        AuthMiddleware::verificarAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        $nuevo_estado = $data['estado'] ?? '';

        if (empty($nuevo_estado)) Response::error('Debe indicar el nuevo estado.');

        // Verificar pedido actual
        $pedido = $this->model->getPedidoById($id);
        if (!$pedido) Response::notFound('Pedido no encontrado.');

        if ($pedido['estado'] !== 'PENDIENTE') {
            Response::error('Solo pedidos PENDIENTES pueden ser modificados.');
        }

        // Si pasa a PAGADO, descontar stock
        if ($nuevo_estado === 'PAGADO') {
            try {
                $this->model->descontarStockDePedido($id);
            } catch (Exception $e) {
                Response::error('Stock insuficiente: ' . $e->getMessage());
            }
        }

        $this->model->updateEstadoPedido($id, $nuevo_estado);
        Response::success(null, "Pedido #$id actualizado a: $nuevo_estado");
    }
}
?>
