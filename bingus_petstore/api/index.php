<?php
/**
 * ============================================
 * ROUTER CENTRAL — API REST Bingus Petstore
 * ============================================
 * Punto de entrada único para todas las peticiones API.
 * Parsea la URL y despacha al controlador correcto.
 * 
 * Formato URL: /api/{recurso}/{id?}/{accion?}
 * 
 * Ejemplos:
 *   GET  /api/productos          → ProductoController::listar()
 *   GET  /api/productos/5        → ProductoController::obtener(5)
 *   POST /api/productos          → ProductoController::crear()
 *   POST /api/productos/5        → ProductoController::actualizar(5)
 *   DELETE /api/productos/5      → ProductoController::eliminar(5)
 *   GET  /api/productos/categorias  → ProductoController::categorias()
 *   PUT  /api/pedidos/3/estado   → PedidoController::cambiarEstado(3)
 */

// Headers CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Sesión global
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar helpers
require_once __DIR__ . '/helpers/Response.php';

// ========== PARSEAR LA URL ==========
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/bingus_petstore/api';

// Extraer la parte después de /api/
$path = parse_url($request_uri, PHP_URL_PATH);
$path = substr($path, strlen($base_path));
$path = trim($path, '/');

// Separar en segmentos: [recurso, id/accion, accion]
$segments = $path ? explode('/', $path) : [];
$recurso = $segments[0] ?? '';
$param1 = $segments[1] ?? null;
$param2 = $segments[2] ?? null;

$method = $_SERVER['REQUEST_METHOD'];

// ========== ROUTING ==========
try {
    switch ($recurso) {

        // ---- AUTH ----
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new AuthController();
            
            if ($param1 === 'login') $controller->login();
            elseif ($param1 === 'logout') $controller->logout();
            elseif ($param1 === 'session') $controller->session();
            else Response::notFound('Ruta de auth no encontrada.');
            break;

        // ---- PRODUCTOS ----
        case 'productos':
            require_once __DIR__ . '/controllers/ProductoController.php';
            $controller = new ProductoController();

            // Rutas especiales (no son IDs)
            if ($param1 === 'categorias') { $controller->categorias(); break; }
            if ($param1 === 'proveedores') { $controller->proveedores(); break; }

            if ($method === 'GET' && $param1 === null) $controller->listar();
            elseif ($method === 'GET' && is_numeric($param1)) $controller->obtener($param1);
            elseif ($method === 'POST' && $param1 === null) $controller->crear();
            elseif ($method === 'POST' && is_numeric($param1)) $controller->actualizar($param1);
            elseif ($method === 'DELETE' && is_numeric($param1)) $controller->eliminar($param1);
            else Response::notFound('Ruta de productos no encontrada.');
            break;

        // ---- VENDEDORES ----
        case 'vendedores':
            require_once __DIR__ . '/controllers/VendedorController.php';
            $controller = new VendedorController();

            if ($method === 'GET' && $param1 === null) $controller->listar();
            elseif ($method === 'GET' && is_numeric($param1)) $controller->obtener($param1);
            elseif ($method === 'POST' && $param1 === null) $controller->crear();
            elseif ($method === 'PUT' && is_numeric($param1)) $controller->actualizar($param1);
            elseif ($method === 'DELETE' && is_numeric($param1)) $controller->eliminar($param1);
            else Response::notFound('Ruta de vendedores no encontrada.');
            break;

        // ---- PEDIDOS ----
        case 'pedidos':
            require_once __DIR__ . '/controllers/PedidoController.php';
            $controller = new PedidoController();

            if ($method === 'GET' && $param1 === null) $controller->listar();
            elseif ($method === 'GET' && is_numeric($param1)) $controller->obtener($param1);
            elseif ($method === 'POST' && $param1 === null) $controller->crear();
            elseif ($method === 'PUT' && is_numeric($param1) && $param2 === 'estado') $controller->cambiarEstado($param1);
            else Response::notFound('Ruta de pedidos no encontrada.');
            break;

        // ---- CLIENTES ----
        case 'clientes':
            require_once __DIR__ . '/controllers/ClienteController.php';
            $controller = new ClienteController();

            if ($method === 'GET' && $param1 === null) $controller->listar();
            elseif ($method === 'GET' && is_numeric($param1)) $controller->obtener($param1);
            elseif ($method === 'POST' && $param1 === null) $controller->crear();
            else Response::notFound('Ruta de clientes no encontrada.');
            break;

        // ---- TIENDA PÚBLICA (sin autenticación) ----
        case 'tienda':
            require_once __DIR__ . '/controllers/TiendaController.php';
            $controller = new TiendaController();

            if ($method === 'GET' && $param1 === 'catalogo') $controller->catalogo();
            elseif ($method === 'GET' && $param1 === 'categorias') $controller->categorias();
            elseif ($method === 'POST' && $param1 === 'checkout') $controller->checkout();
            elseif ($method === 'POST' && $param1 === 'registro') $controller->registro();
            elseif ($method === 'POST' && $param1 === 'login') $controller->loginCliente();
            elseif ($method === 'POST' && $param1 === 'logout') $controller->logoutCliente();
            elseif ($method === 'GET' && $param1 === 'session') $controller->sessionCliente();
            else Response::notFound('Ruta de tienda no encontrada.');
            break;

        // ---- DASHBOARD ----
        case 'dashboard':
            require_once __DIR__ . '/controllers/DashboardController.php';
            $controller = new DashboardController();

            if ($param1 === 'stats') $controller->stats();
            else Response::notFound('Ruta de dashboard no encontrada.');
            break;

        // ---- RUTA BASE /api/ ----
        case '':
            Response::success([
                'app' => 'Bingus Petstore API',
                'version' => '2.0.0',
                'endpoints' => [
                    'auth' => '/api/auth/{login|logout|session}',
                    'productos' => '/api/productos',
                    'vendedores' => '/api/vendedores',
                    'pedidos' => '/api/pedidos',
                    'clientes' => '/api/clientes',
                    'dashboard' => '/api/dashboard/stats',
                    'tienda' => '/api/tienda/{catalogo|categorias|checkout|registro|login|logout|session}'
                ]
            ], 'API Bingus Petstore funcionando.');
            break;

        default:
            Response::notFound("Recurso '$recurso' no encontrado.");
    }
} catch (Exception $e) {
    Response::error('Error interno del servidor: ' . $e->getMessage(), 500);
}
?>
