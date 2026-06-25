<?php
/**
 * ============================================
 * Configuración Global de la Aplicación v3.0
 * ============================================
 * 
 * ARQUITECTURA DE 3 MÁQUINAS:
 * ┌─────────────────────────────────────────────┐
 * │ Máquina 1 (Windows 7 VM)                    │
 * │   → Apache + PHP (este código)              │
 * │   → IP: Configurar DB_HOST más abajo        │
 * ├─────────────────────────────────────────────┤
 * │ Máquina 2 (Linux Mint VM)                   │
 * │   → MariaDB + Firewall (ufw)               │
 * │   → IP: Configurar DB_HOST más abajo        │
 * ├─────────────────────────────────────────────┤
 * │ Máquina 3 (Windows/Mac)                     │
 * │   → Navegador web (Tienda + Intranet)       │
 * └─────────────────────────────────────────────┘
 */

// ============================================
// INFORMACIÓN DE LA APP
// ============================================
define('APP_NAME', 'Bingus Petstore');
define('APP_VERSION', '3.0.0');
define('APP_ENV', 'development');  // 'development' | 'production'

// ============================================
// CONFIGURACIÓN DE RED — AJUSTAR SEGÚN ENTORNO
// ============================================
// Para desarrollo local (1 sola máquina con XAMPP):
//   APP_HOST = 'localhost'
//   DB_HOST  = 'localhost'
//
// Para 3 máquinas (VirtualBox):
//   APP_HOST = IP de la VM Windows 7 (ej: '192.168.1.10')
//   DB_HOST  = IP de la VM Linux Mint (ej: '192.168.1.20')
// ============================================

define('APP_HOST', 'localhost');       // ← IP de esta máquina (Windows 7 VM)
define('DB_HOST', '192.168.1.72');        // ← IP de la máquina con BD (Linux Mint VM)
define('DB_NAME', 'bingus_petstore2');
define('DB_USER', 'root');            // ← En producción: 'bingus_app'
define('DB_PASS', '');                // ← En producción: contraseña segura

// ============================================
// URLs base
// ============================================
define('BASE_URL', 'http://' . APP_HOST . '/bingus_petstore/');
define('API_URL', BASE_URL . 'api/');

// Rutas de archivos
define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('UPLOADS_PRODUCTOS', UPLOADS_PATH . 'productos/');

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora
?>
