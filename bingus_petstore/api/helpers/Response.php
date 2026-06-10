<?php
/**
 * ============================================
 * Helper: Respuestas JSON estandarizadas
 * ============================================
 * Todas las respuestas de la API pasan por aquí
 * para mantener un formato consistente.
 */

class Response {
    
    /**
     * Respuesta exitosa
     */
    public static function success($data = null, $message = 'Operación exitosa', $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Respuesta de error
     */
    public static function error($message = 'Error interno', $code = 400, $data = null) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * No autorizado
     */
    public static function unauthorized($message = 'No autorizado. Inicia sesión.') {
        self::error($message, 401);
    }

    /**
     * No encontrado
     */
    public static function notFound($message = 'Recurso no encontrado.') {
        self::error($message, 404);
    }

    /**
     * Método no permitido
     */
    public static function methodNotAllowed() {
        self::error('Método HTTP no permitido.', 405);
    }
}
?>
