<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: auth_controlador.php"); exit(); }
require_once 'vendedor_modelo.php';
require_once 'config.php'; 

class Vendedor_controller {
    private $model;
    private $admin_id;

    public function __construct() {
        $this->model = new Vendedor_model();
        $this->admin_id = $_SESSION['admin_id'];
    }

    public function listar() {
        $vendedores = $this->model->getVendedoresPorAdmin($this->admin_id);
        include 'vendedor_view.php';
    }

    public function formularioCrear() { include 'crear_vendedor.php'; }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $rut = $_POST['rut'];
            $email = $_POST['email'];
            $pass_raw = $_POST['password']; 
            
            // 1. Validar RUT Duplicado
            if ($this->model->existeRut($rut)) {
                $_SESSION['alerta'] = [
                    'tipo' => 'error',
                    'titulo' => 'RUT Duplicado',
                    'texto' => "El RUT $rut ya está asociado a otro vendedor."
                ];
                // Redirigir explícitamente al controlador de vendedores
                header("Location: vendedor_controlador.php?action=agregar");
                return;
            }
            
            // 2. Validar Email
            if ($this->model->existeEmail($email)) {
                 $_SESSION['alerta'] = [
                    'tipo' => 'error',
                    'titulo' => 'Email Ocupado',
                    'texto' => "El correo $email ya está registrado."
                ];
                header("Location: vendedor_controlador.php?action=agregar");
                return;
            }

            // 3. Insertar
            $password_hash = password_hash($pass_raw, PASSWORD_DEFAULT);
            $this->model->insertVendedor($nombre, $rut, $email, $_POST['telefono'], $_POST['fecha_contratacion'], $this->admin_id, $password_hash);
            
            $_SESSION['alerta'] = [
                'tipo' => 'success',
                'titulo' => 'Vendedor Contratado',
                'texto' => 'El nuevo vendedor ha sido registrado exitosamente.'
            ];
            // Redirigir explícitamente a la lista de VENDEDORES
            header("Location: vendedor_controlador.php?action=listar");
        }
    }

    public function eliminar($id) {
        $this->model->deleteVendedor($id, $this->admin_id);
        
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'titulo' => 'Vendedor Eliminado',
            'texto' => 'El vendedor ha sido borrado correctamente.'
        ];
        
        // --- AQUÍ ESTABA EL ERROR ---
        // Ahora forzamos la redirección al archivo correcto:
        header("Location: vendedor_controlador.php?action=listar");
        exit();
    }

    public function formularioEditar($id) {
        $vendedor = $this->model->getVendedorById($id, $this->admin_id);
        if ($vendedor) include 'editar_vendedor.php';
        else header("Location: vendedor_controlador.php?action=listar");
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_vendedor'];
            $this->model->updateVendedor($id, $_POST['nombre'], $_POST['rut'], $_POST['email'], $_POST['telefono'], $_POST['fecha_contratacion'], $this->admin_id);
            
            $_SESSION['alerta'] = [
                'tipo' => 'success',
                'titulo' => 'Datos Actualizados',
                'texto' => 'La información del vendedor se ha guardado.'
            ];
            header("Location: vendedor_controlador.php?action=listar");
        }
    }
}

$c = new Vendedor_controller();
$a = $_GET['action'] ?? 'listar';
if ($a == 'agregar') $c->formularioCrear();
elseif ($a == 'insertar') $c->guardar();
elseif ($a == 'eliminar') $c->eliminar($_GET['id']);
elseif ($a == 'editar') $c->formularioEditar($_GET['id']);
elseif ($a == 'actualizar') $c->actualizar();
else $c->listar();
?>