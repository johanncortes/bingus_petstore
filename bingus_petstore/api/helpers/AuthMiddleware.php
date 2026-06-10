<?php
/**
 * ============================================
 * Middleware de Autenticación
 * ============================================
 * Verifica que el usuario tenga sesión activa
 * antes de acceder a endpoints protegidos.
 */

class AuthMiddleware {
    
    /**
     * Verificar que haya sesión activa (cualquier rol)
     */
    public static function verificarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['usuario_id'])) {
            Response::unauthorized('Sesión no activa. Por favor inicia sesión.');
        }
    }

    /**
     * Verificar que sea Administrador
     */
    public static function verificarAdmin() {
        self::verificarSesion();
        
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'ADMIN') {
            Response::error('Acceso denegado. Se requiere rol de Administrador.', 403);
        }
    }

    /**
     * Verificar que sea Vendedor
     */
    public static function verificarVendedor() {
        self::verificarSesion();
        
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'VENDEDOR') {
            Response::error('Acceso denegado. Se requiere rol de Vendedor.', 403);
        }
    }

    /**
     * Verificar que sea Admin O Vendedor
     */
    public static function verificarAutenticado() {
        self::verificarSesion();
    }

    /**
     * Obtener datos del usuario actual
     */
    public static function getUsuarioActual() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nombre' => $_SESSION['usuario_nombre'] ?? null,
            'rol' => $_SESSION['usuario_rol'] ?? null,
            'admin_id' => $_SESSION['admin_id'] ?? null
        ];
    }
}
?>
