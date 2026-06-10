<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Clientes
 * ============================================
 * Endpoints para listar y crear clientes.
 */

require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class ClienteController {
    private $model;

    public function __construct() {
        $this->model = new ClienteModel();
    }

    /**
     * GET /api/clientes
     * Listar todos los clientes
     */
    public function listar() {
        AuthMiddleware::verificarAutenticado();
        $clientes = $this->model->getClientes();
        Response::success($clientes, 'Clientes obtenidos.');
    }

    /**
     * GET /api/clientes/{id}
     * Obtener un cliente por ID
     */
    public function obtener($id) {
        AuthMiddleware::verificarAutenticado();
        $cliente = $this->model->getClienteById($id);
        if (!$cliente) Response::notFound('Cliente no encontrado.');
        Response::success($cliente);
    }

    /**
     * POST /api/clientes
     * Crear un nuevo cliente
     * Body: { "nombre", "rut", "email", "telefono", "direccion" }
     */
    public function crear() {
        AuthMiddleware::verificarAutenticado();

        $data = json_decode(file_get_contents('php://input'), true);

        $nombre = trim($data['nombre'] ?? '');
        $rut = trim($data['rut'] ?? '');
        $email = trim($data['email'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $direccion = trim($data['direccion'] ?? '');

        if (empty($nombre) || empty($rut)) {
            Response::error('Nombre y RUT son obligatorios.');
        }

        if ($this->model->existeRut($rut)) {
            Response::error("El RUT $rut ya existe.");
        }

        $id = $this->model->insertCliente($nombre, $rut, $email, $telefono, $direccion);
        Response::success(['id_cliente' => $id], 'Cliente registrado exitosamente.', 201);
    }
}
?>
