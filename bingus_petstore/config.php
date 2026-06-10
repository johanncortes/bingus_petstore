<?php
/**
 * ======================================
 * CONFIGURACIÓN GLOBAL - Bingus Petstore
 * ======================================
 */

// ========== CONFIGURACIÓN DE BASE DE DATOS ==========
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bingus_petstore2');

// ========== CONFIGURACIÓN DE APLICACIÓN ==========
define('APP_NAME', 'Bingus Petstore');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development');

// ========== CONFIGURACIÓN DE DIRECTORIO ==========
// Ajusta esto si tu carpeta se llama distinto
define('BASE_URL', 'http://localhost/bingus_petstore/');
define('ROOT_PATH', dirname(__FILE__) . '/');

// ========== FUNCIONES DE UTILIDAD ==========

function conectarBD() {
    try {
        $conexion = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        return $conexion;
    } catch (PDOException $e) {
        die("Error de conexión BD: " . $e->getMessage());
    }
}

function sanitizar($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Función para mostrar alertas SweetAlert2
 * Esta es la función que te faltaba y causaba el Fatal Error.
 */
function mostrarAlerta() {
    if (isset($_SESSION['alerta'])) {
        $tipo = $_SESSION['alerta']['tipo'];
        $titulo = $_SESSION['alerta']['titulo'];
        $texto = $_SESSION['alerta']['texto'];
        
        // Incluimos la librería SweetAlert2 por si no está en el HTML
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        
        // Imprimimos el script de la alerta
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '$tipo',
                    title: '$titulo',
                    text: '$texto',
                    confirmButtonColor: '#667eea'
                });
            });
        </script>";
        
        // Borramos la alerta para que no salga de nuevo al recargar
        unset($_SESSION['alerta']);
    }
}
?>