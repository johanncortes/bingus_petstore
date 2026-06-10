<?php
/**
 * ============================================
 * CAPA 3 — Modelo: Autenticación
 * ============================================
 * Acceso a datos para login de Admins y Vendedores.
 * Contraseñas en texto plano (sin hash).
 */

require_once __DIR__ . '/../config/Database.php';

class AuthModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Validar credenciales según rol
     * @param string $usuario_o_email - Usuario (admin) o Email (vendedor)
     * @param string $password - Contraseña en texto plano
     * @param string $rol - 'ADMIN' o 'VENDEDOR'
     * @return array|false - Datos del usuario o false
     */
    public function validarLogin($usuario_o_email, $password, $rol) {
        
        if ($rol === 'ADMIN') {
            $stmt = $this->conn->prepare("SELECT * FROM administradores WHERE usuario = :u AND activo = 1 LIMIT 1");
            $stmt->execute([':u' => $usuario_o_email]);
            $admin = $stmt->fetch();

            // Comparación en texto plano
            if ($admin && $password === $admin['contrasena']) {
                return [
                    'id' => $admin['id_administrador'],
                    'nombre' => $admin['nombre'],
                    'rol' => 'ADMIN'
                ];
            }
        } 
        elseif ($rol === 'VENDEDOR') {
            $stmt = $this->conn->prepare("SELECT * FROM vendedores WHERE email = :u AND activo = 1 LIMIT 1");
            $stmt->execute([':u' => $usuario_o_email]);
            $vendedor = $stmt->fetch();

            // Comparación en texto plano
            if ($vendedor && $password === $vendedor['contrasena']) {
                return [
                    'id' => $vendedor['id_vendedor'],
                    'nombre' => $vendedor['nombre'],
                    'rol' => 'VENDEDOR'
                ];
            }
        }

        return false;
    }
}
?>
