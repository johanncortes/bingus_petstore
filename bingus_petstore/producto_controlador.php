<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: auth_controlador.php"); exit(); }
require_once 'producto_modelo.php';
require_once 'config.php';

class Producto_controller {
    private $model;

    public function __construct() { $this->model = new Producto_model(); }

    public function cargarProductos() {
        $productos = $this->model->getProductos();
        include 'producto_view.php';
    }

    public function mostrarFormulario() {
        $categorias = $this->model->getCategorias();
        $proveedores = $this->model->getProveedores();
        include 'crear_producto.php';
    }

    public function agregarProducto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $precio = $_POST['precio'];
            $stock = $_POST['stock'];
            
            if ($this->model->existeProducto($nombre)) { 
                $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Duplicado', 'texto' => "El producto '$nombre' ya existe."];
                header("Location: ?action=agregar"); 
                return; 
            }
            if ($precio < 0 || $stock < 0) { 
                $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Inválido', 'texto' => "Valores negativos no permitidos."];
                header("Location: ?action=agregar");
                return; 
            }

            $nombre_imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $dir = 'uploads/productos/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombre_imagen = md5(time() . $nombre) . '.' . $ext;
                move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre_imagen);
            }

            $this->model->insertProducto($nombre, $_POST['descripcion'], $_POST['id_categoria'], $_POST['id_proveedor'], $precio, $stock, $nombre_imagen);
            
            $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Creado', 'texto' => 'Producto registrado exitosamente.'];
            header("Location: ?action=listar");
        }
    }

    public function mostrarEditarFormulario($id) {
        $producto = $this->model->getProductoById($id);
        $categorias = $this->model->getCategorias();
        $proveedores = $this->model->getProveedores();
        include 'editar_producto.php';
    }

    public function editarProducto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_producto'];
            $imagen_actual = $_POST['imagen_actual'] ?? '';
            $nombre_nueva_imagen = null;
            $mensaje_extra = "";

            // 1. LÓGICA DE ELIMINAR FOTO
            if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1') {
                // Borrar archivo físico si existe
                if (!empty($imagen_actual) && file_exists('uploads/productos/' . $imagen_actual)) {
                    unlink('uploads/productos/' . $imagen_actual);
                }
                // Limpiar en BD
                $this->model->eliminarFotoProducto($id);
                $mensaje_extra = " (Foto eliminada)";
            }
            
            // 2. LÓGICA DE SUBIR NUEVA FOTO (Sobrescribe si hay una)
            elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $dir = 'uploads/productos/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                
                // Borrar foto anterior si existía para no acumular basura
                if (!empty($imagen_actual) && file_exists($dir . $imagen_actual)) {
                    unlink($dir . $imagen_actual);
                }

                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombre_nueva_imagen = md5(time() . $_POST['nombre']) . '.' . $ext;
                move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre_nueva_imagen);
            }

            // Actualizar resto de datos
            $this->model->updateProducto($id, $_POST['nombre'], $_POST['descripcion'], $_POST['id_categoria'], $_POST['id_proveedor'], $_POST['precio'], $_POST['stock'], $nombre_nueva_imagen);
            
            $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Actualizado', 'texto' => 'Datos del producto guardados.' . $mensaje_extra];
            header("Location: ?action=listar");
        }
    }

    public function eliminarProducto($id) {
        $this->model->deleteProducto($id);
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'texto' => 'Producto eliminado correctamente.'];
        header("Location: ?action=listar");
    }
}

$c = new Producto_controller();
$a = $_GET['action'] ?? 'listar';
if ($a == 'agregar') $c->mostrarFormulario();
elseif ($a == 'insertar') $c->agregarProducto();
elseif ($a == 'editar') $c->mostrarEditarFormulario($_GET['id_producto']);
elseif ($a == 'actualizar') $c->editarProducto();
elseif ($a == 'eliminar') $c->eliminarProducto($_GET['id_producto']);
else $c->cargarProductos();
?>