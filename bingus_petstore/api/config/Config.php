<?php
/**
 * ============================================
 * Configuración Global de la Aplicación
 * ============================================
 */

// Información de la App
define('APP_NAME', 'Bingus Petstore');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'development');

// URLs base — Ajusta si tu carpeta tiene otro nombre en htdocs
define('BASE_URL', 'http://localhost/bingus_petstore/');
define('API_URL', BASE_URL . 'api/');

// Rutas de archivos
define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('UPLOADS_PRODUCTOS', UPLOADS_PATH . 'productos/');

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora
?>
