<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Autenticación
 * ============================================
 * Endpoints: login, logout, session
 */

require_once __DIR__ . '/../models/AuthModel.php';
require_once __DIR__ . '/../helpers/Response.php';

class AuthController {
    private $model;

    public function __construct() {
        $this->model = new AuthModel();
    }

    /**
     * POST /api/auth/login
     * Body: { "usuario": "...", "password": "...", "rol": "ADMIN|VENDEDOR" }
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        $usuario = trim($data['usuario'] ?? '');
        $password = $data['password'] ?? '';
        $rol = strtoupper(trim($data['rol'] ?? 'ADMIN'));

        if (empty($usuario) || empty($password)) {
            Response::error('Usuario y contraseña son obligatorios.');
        }

        $user = $this->model->validarLogin($usuario, $password, $rol);

        if ($user) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol'] = $user['rol'];

            if ($user['rol'] === 'ADMIN') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_nombre'] = $user['nombre'];
            }

            Response::success([
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol'],
                'redirect' => $user['rol'] === 'ADMIN' ? 'views/admin/dashboard.php' : 'views/vendedor/pos.php'
            ], 'Login exitoso.');
        } else {
            Response::error('Credenciales incorrectas para el perfil seleccionado.', 401);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        Response::success(null, 'Sesión cerrada correctamente.');
    }

    /**
     * GET /api/auth/session
     * Verificar si hay sesión activa
     */
    public function session() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (isset($_SESSION['usuario_id'])) {
            Response::success([
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['usuario_nombre'],
                'rol' => $_SESSION['usuario_rol']
            ], 'Sesión activa.');
        } else {
            Response::error('No hay sesión activa.', 401);
        }
    }
}
?>
