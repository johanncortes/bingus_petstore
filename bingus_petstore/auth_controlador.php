<?php
ob_start();
session_start();

require_once 'auth_modelo.php';
require_once 'config.php'; 

class Auth_controller {
    private $model;

    public function __construct() {
        $this->model = new Auth_model();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = $_POST['usuario'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 'ADMIN'; 

            $user = $this->model->validarLogin($usuario, $password, $rol);

            if ($user) {
                // ... (Lógica de éxito igual que antes) ...
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_rol'] = $user['rol'];

                if ($user['rol'] === 'ADMIN') {
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_nombre'] = $user['nombre'];
                    header("Location: index.php");
                } 
                elseif ($user['rol'] === 'VENDEDOR') {
                    header("Location: index_vendedor.php?vista=pos");
                }
                exit();
                
            } else {
                // --- CAMBIO AQUÍ ---
                // Guardamos la alerta
                $_SESSION['alerta'] = [
                    'tipo' => 'error',
                    'titulo' => 'Acceso Denegado',
                    'texto' => 'Credenciales incorrectas para el perfil seleccionado.'
                ];
                
                // Redirigimos PERO agregando el rol en la URL para recordarlo
                header("Location: auth_controlador.php?action=login&rol=" . $rol); 
                exit();
            }
        } else {
            include 'login.php';
        }
    }

    public function logout() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

$auth = new Auth_controller();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->logout();
} elseif (isset($_GET['action']) && $_GET['action'] === 'login') {
    $auth->login();
} else {
    if (isset($_SESSION['usuario_rol'])) {
        if ($_SESSION['usuario_rol'] === 'ADMIN') header("Location: index.php");
        elseif ($_SESSION['usuario_rol'] === 'VENDEDOR') header("Location: index_vendedor.php?vista=pos");
        exit();
    }
    $auth->login();
}
ob_end_flush();
?>