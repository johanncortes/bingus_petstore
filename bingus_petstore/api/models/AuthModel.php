<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Autenticación
 * ============================================
 * Acceso a datos para login de Administradores.
 * Solo admins tienen acceso al sistema interno (Intranet).
 * Contraseñas en texto plano (sin hash).
 */

require_once __DIR__ . '/../config/Database.php';

class AuthModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Validar credenciales de administrador
     * @param string $usuario - Nombre de usuario del admin
     * @param string $password - Contraseña en texto plano
     * @return array|false - Datos del usuario o false
     */
    public function validarLogin($usuario, $password) {
        
        $stmt = $this->conn->prepare("SELECT * FROM administradores WHERE usuario = :u AND activo = 1 LIMIT 1");
        $stmt->execute([':u' => $usuario]);
        $admin = $stmt->fetch();

        // Comparación en texto plano
        if ($admin && $password === $admin['contrasena']) {
            return [
                'id' => $admin['id_administrador'],
                'nombre' => $admin['nombre'],
                'rol' => 'ADMIN'
            ];
        }

        return false;
    }
}
?>
