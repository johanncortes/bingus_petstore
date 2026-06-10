<?php
$rol_inicial = isset($_GET['rol']) ? $_GET['rol'] : 'ADMIN';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Bingus Petstore</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center; margin: 0;
        }
        .login-card {
            background: white; padding: 0; border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2); width: 100%; max-width: 400px; overflow: hidden;
        }
        
        /* BANNER EN EL LOGIN */
        .login-banner {
            width: 100%;
            height: 120px;
            background: #fff;
        }
        .login-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .tabs { display: flex; cursor: pointer; background: #f0f2f5; }
        .tab {
            flex: 1; padding: 15px; text-align: center; font-weight: bold; color: #666;
            transition: all 0.3s; border-bottom: 3px solid transparent; font-size: 14px;
        }
        .tab.active { background: white; color: #667eea; border-bottom: 3px solid #667eea; }
        
        .login-body { padding: 30px 40px 40px; }
        h1 { color: #333; margin: 0 0 20px 0; font-size: 24px; text-align: center; }
        
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #666; font-size: 14px; font-weight: 600; }
        input {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 8px; box-sizing: border-box; font-size: 16px;
        }
        input:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        
        button {
            width: 100%; padding: 14px; border: none; border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; font-size: 16px; font-weight: bold; cursor: pointer;
            transition: transform 0.2s; margin-top: 10px;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3); }
        
        .footer-link { margin-top: 25px; font-size: 12px; color: #999; text-align: center; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-banner">
        <img src="img/banner.png" alt="Bingus Login">
    </div>

    <div class="tabs">
        <div class="tab" onclick="cambiarRol('ADMIN')" id="tab-admin">👨‍💼 Administrador</div>
        <div class="tab" onclick="cambiarRol('VENDEDOR')" id="tab-vendedor">🛒 Vendedor</div>
    </div>

    <div class="login-body">
        <h1 id="titulo-login">Acceso Administrativo</h1>

        <form method="POST" action="auth_controlador.php?action=login">
            <input type="hidden" name="rol" id="rol-input" value="ADMIN">

            <div class="input-group">
                <label id="label-usuario">Usuario</label>
                <input type="text" name="usuario" id="input-usuario" required placeholder="Ej: cmorales">
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" required placeholder="••••••">
            </div>

            <button type="submit">Ingresar</button>
        </form>

        <div class="footer-link">Bingus Petstore © 2025</div>
    </div>
</div>

<script>
    function cambiarRol(rol) {
        document.getElementById('rol-input').value = rol;
        
        if(rol === 'ADMIN') {
            document.getElementById('tab-admin').classList.add('active');
            document.getElementById('tab-vendedor').classList.remove('active');
            document.getElementById('titulo-login').innerText = 'Acceso Administrativo';
            document.getElementById('label-usuario').innerText = 'Usuario';
            document.getElementById('input-usuario').placeholder = 'Ej: cmorales';
        } else {
            document.getElementById('tab-vendedor').classList.add('active');
            document.getElementById('tab-admin').classList.remove('active');
            document.getElementById('titulo-login').innerText = 'Portal de Vendedores';
            document.getElementById('label-usuario').innerText = 'Correo Electrónico';
            document.getElementById('input-usuario').placeholder = 'Ej: vendedor@bingus.cl';
        }
    }

    window.onload = function() {
        cambiarRol('<?php echo $rol_inicial; ?>');
    };
</script>

<?php 
    if (function_exists('mostrarAlerta')) {
        mostrarAlerta(); 
    }
?>

</body>
</html>