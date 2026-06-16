<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Pedidos
 * ============================================
 * Endpoints para listar, ver detalle, asignar repartidor
 * y cambiar estado de pedidos.
 * Flujo: PENDIENTE → PAGADO → EN_REPARTO → ENTREGADO
 *                            → CANCELADO
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
     * PUT /api/pedidos/{id}/estado
     * Cambiar estado de un pedido
     * Body: { "estado": "PAGADO|EN_REPARTO|ENTREGADO|CANCELADO" }
     */
    public function cambiarEstado($id) {
        AuthMiddleware::verificarAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        $nuevo_estado = $data['estado'] ?? '';

        if (empty($nuevo_estado)) Response::error('Debe indicar el nuevo estado.');

        $estados_validos = ['PENDIENTE', 'PAGADO', 'EN_REPARTO', 'ENTREGADO', 'CANCELADO'];
        if (!in_array($nuevo_estado, $estados_validos)) {
            Response::error('Estado no válido. Opciones: ' . implode(', ', $estados_validos));
        }

        // Verificar pedido actual
        $pedido = $this->model->getPedidoById($id);
        if (!$pedido) Response::notFound('Pedido no encontrado.');

        // Validar transiciones de estado
        $transiciones_validas = [
            'PENDIENTE' => ['PAGADO', 'CANCELADO'],
            'PAGADO' => ['EN_REPARTO', 'CANCELADO'],
            'EN_REPARTO' => ['ENTREGADO'],
            'ENTREGADO' => [],
            'CANCELADO' => []
        ];

        $estado_actual = $pedido['estado'];
        if (!in_array($nuevo_estado, $transiciones_validas[$estado_actual] ?? [])) {
            Response::error("No se puede cambiar de '$estado_actual' a '$nuevo_estado'.");
        }

        // Si pasa a PAGADO, descontar stock
        if ($nuevo_estado === 'PAGADO') {
            try {
                $this->model->descontarStockDePedido($id);
            } catch (Exception $e) {
                Response::error('Stock insuficiente: ' . $e->getMessage());
            }
        }

        // Si pasa a EN_REPARTO, verificar que tenga repartidor asignado
        if ($nuevo_estado === 'EN_REPARTO' && empty($pedido['id_repartidor'])) {
            Response::error('Debe asignar un repartidor antes de enviar a reparto.');
        }

        $this->model->updateEstadoPedido($id, $nuevo_estado);
        Response::success(null, "Pedido #$id actualizado a: $nuevo_estado");
    }

    /**
     * PUT /api/pedidos/{id}/repartidor
     * Asignar repartidor a un pedido
     * Body: { "id_repartidor": 1 }
     */
    public function asignarRepartidor($id) {
        AuthMiddleware::verificarAdmin();

        $data = json_decode(file_get_contents('php://input'), true);
        $id_repartidor = $data['id_repartidor'] ?? null;

        if (empty($id_repartidor)) {
            Response::error('Debe indicar el ID del repartidor.');
        }

        // Verificar que el pedido exista
        $pedido = $this->model->getPedidoById($id);
        if (!$pedido) Response::notFound('Pedido no encontrado.');

        $this->model->asignarRepartidor($id, $id_repartidor);
        Response::success(null, "Repartidor asignado al pedido #$id.");
    }
}
?>
