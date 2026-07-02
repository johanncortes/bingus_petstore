<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Tienda Pública
 * ============================================
 * Endpoints públicos para la tienda virtual (e-commerce).
 * Incluye autenticación de clientes y checkout con IVA.
 */

require_once __DIR__ . '/../models/TiendaModel.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validaciones.php';

class TiendaController {
    private $model;

    public function __construct() {
        $this->model = new TiendaModel();
    }

    /**
     * GET /api/tienda/catalogo
     * Obtener todos los productos disponibles con IVA desglosado
     */
    public function catalogo() {
        $productos = $this->model->getCatalogo();
        Response::success($productos, 'Catálogo obtenido.');
    }

    /**
     * GET /api/tienda/categorias
     * Obtener categorías con productos disponibles
     */
    public function categorias() {
        $categorias = $this->model->getCategorias();
        Response::success($categorias, 'Categorías obtenidas.');
    }

    /**
     * GET /api/tienda/config
     * Obtener configuración pública (tasa IVA, etc.)
     */
    public function config() {
        $tasa_iva = $this->model->getTasaIVA();
        Response::success([
            'iva' => [
                'nombre' => 'IVA',
                'porcentaje' => $tasa_iva,
                'descripcion' => "Impuesto al Valor Agregado ($tasa_iva%)"
            ]
        ], 'Configuración obtenida.');
    }

    // ========== AUTH DE CLIENTES ==========

    /**
     * POST /api/tienda/registro
     * Registrar un nuevo cliente
     * Body: { "nombre", "rut", "email", "password", "telefono", "direccion" }
     */
    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $nombre = trim($data['nombre'] ?? '');
        $rut = trim($data['rut'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $telefono = trim($data['telefono'] ?? '');
        $direccion = trim($data['direccion'] ?? '');

        // Validaciones básicas de campos obligatorios
        if (empty($nombre) || empty($rut) || empty($email) || empty($password)) {
            Response::error('Nombre, RUT, email y contraseña son obligatorios.');
        }

        // Validaciones REGEX
        $resultadoRut = Validaciones::validarRut($rut);
        if (!$resultadoRut['valido']) {
            Response::error($resultadoRut['mensaje']);
        }

        $resultadoEmail = Validaciones::validarEmail($email, true);
        if (!$resultadoEmail['valido']) {
            Response::error($resultadoEmail['mensaje']);
        }

        if (!empty($telefono)) {
            $resultadoTel = Validaciones::validarTelefono($telefono);
            if (!$resultadoTel['valido']) {
                Response::error($resultadoTel['mensaje']);
            }
        }

        if (strlen($password) < 6) {
            Response::error('La contraseña debe tener al menos 6 caracteres.');
        }

        // Verificar duplicados
        if ($this->model->existeEmail($email)) {
            Response::error('Este email ya está registrado. Intenta iniciar sesión.');
        }

        if ($this->model->buscarClientePorRut($rut)) {
            Response::error('Este RUT ya está registrado.');
        }

        try {
            $id_cliente = $this->model->registrarCliente($nombre, $rut, $email, $password, $telefono, $direccion);

            // Iniciar sesión automáticamente
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['cliente_id'] = $id_cliente;
            $_SESSION['cliente_nombre'] = $nombre;
            $_SESSION['cliente_email'] = $email;
            $_SESSION['usuario_rol'] = 'CLIENTE';

            Response::success([
                'id_cliente' => $id_cliente,
                'nombre' => $nombre,
                'email' => $email
            ], 'Cuenta creada exitosamente. ¡Bienvenido!', 201);

        } catch (Exception $e) {
            Response::error('Error al registrar: ' . $e->getMessage());
        }
    }

    /**
     * POST /api/tienda/login
     * Login de cliente
     * Body: { "email", "password" }
     */
    public function loginCliente() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            Response::error('Email y contraseña son obligatorios.');
        }

        $cliente = $this->model->loginCliente($email, $password);

        if ($cliente) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['cliente_id'] = $cliente['id_cliente'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];
            $_SESSION['cliente_email'] = $cliente['email'];
            $_SESSION['usuario_rol'] = 'CLIENTE';

            Response::success([
                'id_cliente' => $cliente['id_cliente'],
                'nombre' => $cliente['nombre'],
                'email' => $cliente['email']
            ], 'Inicio de sesión exitoso.');
        } else {
            Response::error('Email o contraseña incorrectos.', 401);
        }
    }

    /**
     * POST /api/tienda/logout
     * Cerrar sesión de cliente
     */
    public function logoutCliente() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        Response::success(null, 'Sesión cerrada correctamente.');
    }

    /**
     * GET /api/tienda/session
     * Verificar sesión activa de cliente
     */
    public function sessionCliente() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (isset($_SESSION['cliente_id'])) {
            $cliente = $this->model->getClienteById($_SESSION['cliente_id']);
            if ($cliente) {
                Response::success($cliente, 'Sesión activa.');
            }
        }
        Response::error('No hay sesión activa.', 401);
    }

    // ========== CHECKOUT ==========

    /**
     * POST /api/tienda/checkout
     * Procesar compra desde la tienda virtual con IVA
     * 
     * Body: {
     *   "cliente": { "nombre", "rut", "email", "telefono", "direccion" },
     *   "items": [{ "id_producto": 1, "cantidad": 2, "precio": 14280, "subtotal": 28560 }, ...],
     *   "direccion_entrega": "Calle 123..."
     * }
     * 
     * Nota: 'precio' y 'subtotal' en items incluyen IVA (precio_total)
     */
    public function checkout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::methodNotAllowed();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $items = $data['items'] ?? [];
        $direccion_entrega = $data['direccion_entrega'] ?? null;

        if (empty($items)) {
            Response::error('El carrito está vacío.');
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            // Determinar el cliente
            if (isset($_SESSION['cliente_id'])) {
                // Cliente autenticado — usar su ID directamente
                $id_cliente = $_SESSION['cliente_id'];
                
                // Si no viene dirección de entrega, usar la del perfil
                if (empty($direccion_entrega)) {
                    $clienteData = $this->model->getClienteById($id_cliente);
                    $direccion_entrega = $clienteData['direccion'] ?? null;
                }
            } else {
                // Cliente anónimo — necesita datos del formulario
                $cliente = $data['cliente'] ?? null;

                if (!$cliente) {
                    Response::error('Datos del cliente son obligatorios.');
                }
                if (empty($cliente['nombre']) || empty($cliente['rut'])) {
                    Response::error('Nombre y RUT del cliente son obligatorios.');
                }

                // Validaciones REGEX del cliente anónimo
                $resultadoRut = Validaciones::validarRut($cliente['rut']);
                if (!$resultadoRut['valido']) {
                    Response::error($resultadoRut['mensaje']);
                }

                if (!empty($cliente['email'])) {
                    $resultadoEmail = Validaciones::validarEmail($cliente['email']);
                    if (!$resultadoEmail['valido']) {
                        Response::error($resultadoEmail['mensaje']);
                    }
                }

                if (!empty($cliente['telefono'])) {
                    $resultadoTel = Validaciones::validarTelefono($cliente['telefono']);
                    if (!$resultadoTel['valido']) {
                        Response::error($resultadoTel['mensaje']);
                    }
                }

                // Buscar si el cliente ya existe por RUT
                $clienteExistente = $this->model->buscarClientePorRut($cliente['rut']);

                if ($clienteExistente) {
                    $id_cliente = $clienteExistente['id_cliente'];
                } else {
                    // Crear nuevo cliente
                    $id_cliente = $this->model->crearClienteTienda(
                        $cliente['nombre'],
                        $cliente['rut'],
                        $cliente['email'] ?? null,
                        $cliente['telefono'] ?? null,
                        $cliente['direccion'] ?? null
                    );
                }

                // Usar la dirección del formulario si no viene dirección de entrega
                if (empty($direccion_entrega)) {
                    $direccion_entrega = $cliente['direccion'] ?? null;
                }
            }

            // Crear el pedido con IVA
            $id_pedido = $this->model->crearPedidoTienda($id_cliente, $items, $direccion_entrega);

            Response::success(
                [
                    'id_pedido' => $id_pedido,
                    'id_cliente' => $id_cliente
                ],
                "¡Pedido #$id_pedido registrado exitosamente! Tu pedido está pendiente de confirmación.",
                201
            );

        } catch (Exception $e) {
            Response::error('Error al procesar el pedido: ' . $e->getMessage());
        }
    }
}
?>
