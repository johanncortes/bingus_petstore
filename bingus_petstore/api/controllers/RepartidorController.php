<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Repartidores
 * ============================================
 * Endpoints CRUD para gestión de repartidores.
 * Cada admin puede tener máximo 2 repartidores activos.
 */

require_once __DIR__ . '/../models/RepartidorModel.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class RepartidorController {
    private $model;

    public function __construct() {
        $this->model = new RepartidorModel();
    }

    /**
     * GET /api/repartidores
     * Listar repartidores del admin actual
     */
    public function listar() {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();
        $repartidores = $this->model->getRepartidoresPorAdmin($usuario['admin_id']);
        Response::success($repartidores, 'Repartidores obtenidos.');
    }

    /**
     * GET /api/repartidores/disponibles
     * Listar todos los repartidores disponibles (para asignar a pedidos)
     */
    public function disponibles() {
        AuthMiddleware::verificarAdmin();
        $repartidores = $this->model->getRepartidoresDisponibles();
        Response::success($repartidores, 'Repartidores disponibles obtenidos.');
    }

    /**
     * GET /api/repartidores/{id}
     * Obtener un repartidor
     */
    public function obtener($id) {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();
        $repartidor = $this->model->getRepartidorById($id, $usuario['admin_id']);
        
        if (!$repartidor) {
            Response::notFound('Repartidor no encontrado.');
        }
        Response::success($repartidor);
    }

    /**
     * POST /api/repartidores
     * Crear nuevo repartidor
     * Body: { "nombre", "rut", "email", "telefono", "fecha_contratacion" }
     */
    public function crear() {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();

        // Verificar límite de 2 repartidores
        $count = $this->model->contarRepartidoresPorAdmin($usuario['admin_id']);
        if ($count >= 2) {
            Response::error('Límite alcanzado: cada administrador puede tener máximo 2 repartidores activos.');
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $nombre = trim($data['nombre'] ?? '');
        $rut = trim($data['rut'] ?? '');
        $email = trim($data['email'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $fecha = $data['fecha_contratacion'] ?? date('Y-m-d');

        // Validaciones
        if (empty($nombre) || empty($rut) || empty($email)) {
            Response::error('Nombre, RUT y email son obligatorios.');
        }
        if ($this->model->existeRut($rut)) {
            Response::error("El RUT $rut ya está asociado a otro repartidor.");
        }
        if ($this->model->existeEmail($email)) {
            Response::error("El correo $email ya está registrado.");
        }

        $resultado = $this->model->insertRepartidor(
            $nombre, $rut, $email, $telefono, $fecha,
            $usuario['admin_id']
        );

        if ($resultado) {
            Response::success(null, 'Repartidor registrado exitosamente.', 201);
        } else {
            Response::error('No se pudo crear el repartidor.');
        }
    }

    /**
     * PUT /api/repartidores/{id}
     * Actualizar repartidor
     */
    public function actualizar($id) {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();

        $data = json_decode(file_get_contents('php://input'), true);

        $nombre = trim($data['nombre'] ?? '');
        $rut = trim($data['rut'] ?? '');
        $email = trim($data['email'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $fecha = $data['fecha_contratacion'] ?? '';

        if (empty($nombre) || empty($rut) || empty($email)) {
            Response::error('Nombre, RUT y email son obligatorios.');
        }

        // Verificar duplicados excluyendo el repartidor actual
        if ($this->model->existeRut($rut, $id)) {
            Response::error("El RUT $rut ya está asociado a otro repartidor.");
        }
        if ($this->model->existeEmail($email, $id)) {
            Response::error("El correo $email ya está registrado.");
        }

        $this->model->updateRepartidor($id, $nombre, $rut, $email, $telefono, $fecha, $usuario['admin_id']);
        Response::success(null, 'Repartidor actualizado exitosamente.');
    }

    /**
     * PUT /api/repartidores/{id}/disponibilidad
     * Cambiar estado de disponibilidad
     * Body: { "estado": "DISPONIBLE|EN_REPARTO|INACTIVO" }
     */
    public function cambiarDisponibilidad($id) {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();

        $data = json_decode(file_get_contents('php://input'), true);
        $estado = $data['estado'] ?? '';

        $estados_validos = ['DISPONIBLE', 'EN_REPARTO', 'INACTIVO'];
        if (!in_array($estado, $estados_validos)) {
            Response::error('Estado no válido. Opciones: ' . implode(', ', $estados_validos));
        }

        $resultado = $this->model->cambiarDisponibilidad($id, $estado, $usuario['admin_id']);
        if ($resultado) {
            Response::success(null, "Estado del repartidor actualizado a: $estado");
        } else {
            Response::error('No se pudo cambiar el estado del repartidor.');
        }
    }

    /**
     * DELETE /api/repartidores/{id}
     * Soft delete de repartidor
     */
    public function eliminar($id) {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();
        $resultado = $this->model->deleteRepartidor($id, $usuario['admin_id']);
        
        if ($resultado) {
            Response::success(null, 'Repartidor eliminado correctamente.');
        } else {
            Response::error('No se pudo eliminar el repartidor.');
        }
    }
}
?>
