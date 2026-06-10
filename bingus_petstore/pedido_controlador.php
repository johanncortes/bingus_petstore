<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: auth_controlador.php"); exit(); }
require_once 'pedido_modelo.php';
require_once 'config.php';

class Pedido_controller {
    private $model;

    public function __construct() { $this->model = new Pedido_model(); }

    public function listar() {
        $pedidos = $this->model->getPedidos();
        foreach ($pedidos as &$p) {
            $p['detalles'] = $this->model->getDetallesPedido($p['id_pedido']);
        }
        unset($p);
        include 'pedido_view.php';
    }

    public function editar($id) {
        $pedido = $this->model->getPedidoById($id);
        $detalles = $this->model->getDetallesPedido($id);
        
        if ($pedido) {
            include 'editar_pedido.php';
        } else {
            header("Location: ?action=listar");
        }
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_pedido = $_POST['id_pedido'];
            $nuevo_estado = $_POST['estado'];

            $pedido_actual = $this->model->getPedidoById($id_pedido);
            $estado_db = $pedido_actual['estado'];

            if ($estado_db !== 'PENDIENTE') {
                $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Pedido cerrado no editable.'];
                header("Location: ?action=listar");
                return;
            }

            if ($nuevo_estado === 'PAGADO') {
                try {
                    $this->model->descontarStockDePedido($id_pedido);
                } catch (Exception $e) {
                    $_SESSION['alerta'] = [
                        'tipo' => 'error',
                        'titulo' => 'Stock Insuficiente',
                        'texto' => 'No se puede aprobar el pedido: ' . $e->getMessage()
                    ];
                    header("Location: ?action=listar");
                    return;
                }
            }

            $this->model->updateEstadoPedido($id_pedido, $nuevo_estado);
            
            $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Actualizado', 'texto' => "Pedido #$id_pedido ahora es: $nuevo_estado"];
            header("Location: ?action=listar");
        }
    }
}

$controller = new Pedido_controller();
$action = $_GET['action'] ?? 'listar';

if ($action == 'editar') $controller->editar($_GET['id']);
elseif ($action == 'actualizar') $controller->actualizar();
else $controller->listar();
?>