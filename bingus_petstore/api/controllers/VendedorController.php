<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Vendedores
 * ============================================
 * Endpoints CRUD para gestión de vendedores.
 */

require_once __DIR__ . '/../models/VendedorModel.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class VendedorController {
    private $model;

    public function __construct() {
        $this->model = new VendedorModel();
    }

    /**
     * GET /api/vendedores
     * Listar vendedores del admin actual
     */
    public function listar() {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();
        $vendedores = $this->model->getVendedoresPorAdmin($usuario['admin_id']);
        Response::success($vendedores, 'Vendedores obtenidos.');
    }

    /**
     * GET /api/vendedores/{id}
     * Obtener un vendedor
     */
    public function obtener($id) {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();
        $vendedor = $this->model->getVendedorById($id, $usuario['admin_id']);
        
        if (!$vendedor) {
            Response::notFound('Vendedor no encontrado.');
        }
        Response::success($vendedor);
    }

    /**
     * POST /api/vendedores
     * Crear nuevo vendedor
     * Body: { "nombre", "rut", "email", "telefono", "fecha_contratacion", "password" }
     */
    public function crear() {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();

        $data = json_decode(file_get_contents('php://input'), true);

        $nombre = trim($data['nombre'] ?? '');
        $rut = trim($data['rut'] ?? '');
        $email = trim($data['email'] ?? '');
        $telefono = trim($data['telefono'] ?? '');
        $fecha = $data['fecha_contratacion'] ?? date('Y-m-d');
        $password = $data['password'] ?? '';

        // Validaciones
        if (empty($nombre) || empty($rut) || empty($email)) {
            Response::error('Nombre, RUT y email son obligatorios.');
        }
        if (empty($password)) {
            Response::error('La contraseña es obligatoria.');
        }
        if ($this->model->existeRut($rut)) {
            Response::error("El RUT $rut ya está asociado a otro vendedor.");
        }
        if ($this->model->existeEmail($email)) {
            Response::error("El correo $email ya está registrado.");
        }

        // Guardar contraseña en texto plano (sin hash)
        $resultado = $this->model->insertVendedor(
            $nombre, $rut, $email, $telefono, $fecha,
            $usuario['admin_id'], $password
        );

        if ($resultado) {
            Response::success(null, 'Vendedor registrado exitosamente.', 201);
        } else {
            Response::error('No se pudo crear el vendedor.');
        }
    }

    /**
     * PUT /api/vendedores/{id}
     * Actualizar vendedor
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

        // Verificar duplicados excluyendo el vendedor actual
        if ($this->model->existeRut($rut, $id)) {
            Response::error("El RUT $rut ya está asociado a otro vendedor.");
        }
        if ($this->model->existeEmail($email, $id)) {
            Response::error("El correo $email ya está registrado.");
        }

        $this->model->updateVendedor($id, $nombre, $rut, $email, $telefono, $fecha, $usuario['admin_id']);
        Response::success(null, 'Vendedor actualizado exitosamente.');
    }

    /**
     * DELETE /api/vendedores/{id}
     * Soft delete de vendedor
     */
    public function eliminar($id) {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();
        $resultado = $this->model->deleteVendedor($id, $usuario['admin_id']);
        
        if ($resultado) {
            Response::success(null, 'Vendedor eliminado correctamente.');
        } else {
            Response::error('No se pudo eliminar el vendedor.');
        }
    }
}
?>
