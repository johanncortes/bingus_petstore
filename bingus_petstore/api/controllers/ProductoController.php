<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Productos
 * ============================================
 * Endpoints CRUD para productos, categorías y proveedores.
 */

require_once __DIR__ . '/../models/ProductoModel.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';
require_once __DIR__ . '/../config/Config.php';

class ProductoController {
    private $model;

    public function __construct() {
        $this->model = new ProductoModel();
    }

    /**
     * GET /api/productos
     * Listar todos los productos activos
     */
    public function listar() {
        AuthMiddleware::verificarAutenticado();
        $productos = $this->model->getProductos();
        Response::success($productos, 'Productos obtenidos.');
    }

    /**
     * GET /api/productos/{id}
     * Obtener un producto por ID
     */
    public function obtener($id) {
        AuthMiddleware::verificarAutenticado();
        $producto = $this->model->getProductoById($id);
        if (!$producto) {
            Response::notFound('Producto no encontrado.');
        }
        Response::success($producto);
    }

    /**
     * POST /api/productos
     * Crear nuevo producto (soporta multipart/form-data para imagen)
     */
    public function crear() {
        AuthMiddleware::verificarAdmin();

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $id_categoria = $_POST['id_categoria'] ?? '';
        $id_proveedor = $_POST['id_proveedor'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;

        // Validaciones
        if (empty($nombre)) Response::error('El nombre es obligatorio.');
        if ($precio < 0 || $stock < 0) Response::error('Valores negativos no permitidos.');
        if ($this->model->existeProducto($nombre)) Response::error("El producto '$nombre' ya existe.");

        // Manejo de imagen
        $nombre_imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $dir = UPLOADS_PRODUCTOS;
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_imagen = md5(time() . $nombre) . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre_imagen);
        }

        $resultado = $this->model->insertProducto($nombre, $descripcion, $id_categoria, $id_proveedor, $precio, $stock, $nombre_imagen);
        
        if ($resultado) {
            Response::success(null, 'Producto registrado exitosamente.', 201);
        } else {
            Response::error('No se pudo crear el producto.');
        }
    }

    /**
     * POST /api/productos/{id} (con _method=PUT)
     * Actualizar producto existente
     */
    public function actualizar($id) {
        AuthMiddleware::verificarAdmin();

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $id_categoria = $_POST['id_categoria'] ?? '';
        $id_proveedor = $_POST['id_proveedor'] ?? '';
        $precio = $_POST['precio'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $imagen_actual = $_POST['imagen_actual'] ?? '';
        $eliminar_imagen = $_POST['eliminar_imagen'] ?? '0';

        if (empty($nombre)) Response::error('El nombre es obligatorio.');

        $nombre_nueva_imagen = null;

        // 1. Eliminar foto
        if ($eliminar_imagen === '1') {
            if (!empty($imagen_actual) && file_exists(UPLOADS_PRODUCTOS . $imagen_actual)) {
                unlink(UPLOADS_PRODUCTOS . $imagen_actual);
            }
            $this->model->eliminarFotoProducto($id);
        }
        // 2. Subir nueva foto
        elseif (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $dir = UPLOADS_PRODUCTOS;
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            
            // Borrar anterior
            if (!empty($imagen_actual) && file_exists($dir . $imagen_actual)) {
                unlink($dir . $imagen_actual);
            }

            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_nueva_imagen = md5(time() . $nombre) . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombre_nueva_imagen);
        }

        $this->model->updateProducto($id, $nombre, $descripcion, $id_categoria, $id_proveedor, $precio, $stock, $nombre_nueva_imagen);
        Response::success(null, 'Producto actualizado exitosamente.');
    }

    /**
     * DELETE /api/productos/{id}
     * Soft delete de producto
     */
    public function eliminar($id) {
        AuthMiddleware::verificarAdmin();
        $this->model->deleteProducto($id);
        Response::success(null, 'Producto eliminado correctamente.');
    }

    /**
     * GET /api/productos/categorias
     */
    public function categorias() {
        AuthMiddleware::verificarAutenticado();
        $categorias = $this->model->getCategorias();
        Response::success($categorias);
    }

    /**
     * GET /api/productos/proveedores
     */
    public function proveedores() {
        AuthMiddleware::verificarAutenticado();
        $proveedores = $this->model->getProveedores();
        Response::success($proveedores);
    }
}
?>
