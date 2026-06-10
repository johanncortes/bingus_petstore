<?php
require_once 'config.php';

class Auth_model {
    private $conexion;

    public function __construct() {
        $this->conexion = conectarBD();
    }

    public function validarLogin($usuario_o_email, $password, $rol) {
        
        if ($rol === 'ADMIN') {
            // Lógica para ADMINISTRADORES (Login por Usuario)
            $stmt = $this->conexion->prepare("SELECT * FROM administradores WHERE usuario = :u LIMIT 1");
            $stmt->execute([':u' => $usuario_o_email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificamos contraseña (en texto plano según tus datos anteriores '123456')
            // OJO: Si ya encriptaste a admins, usa password_verify. 
            // Según tu historial, admins usan texto plano y vendedores hash.
            if ($admin && $password === $admin['contrasena']) {
                return [
                    'id' => $admin['id_administrador'], 
                    'nombre' => $admin['nombre'], 
                    'rol' => 'ADMIN'
                ];
            }
        } 
        elseif ($rol === 'VENDEDOR') {
            // Lógica para VENDEDORES (Login por Email)
            $stmt = $this->conexion->prepare("SELECT * FROM vendedores WHERE email = :u AND activo = 1 LIMIT 1");
            $stmt->execute([':u' => $usuario_o_email]);
            $vendedor = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificamos contraseña encriptada (los vendedores sí usan hash en tu sistema nuevo)
            if ($vendedor && password_verify($password, $vendedor['contrasena'])) {
                return [
                    'id' => $vendedor['id_vendedor'], 
                    'nombre' => $vendedor['nombre'], 
                    'rol' => 'VENDEDOR'
                ];
            }
        }

        return false; // Credenciales incorrectas
    }
}
?>