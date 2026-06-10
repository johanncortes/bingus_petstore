<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Vendedor - Bingus Petstore</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px;
        }
        .container {
            background: white; border-radius: 10px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px; max-width: 600px; width: 100%;
        }
        h1 { color: #333; margin-bottom: 10px; font-size: 28px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        input:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .button-group { display: flex; gap: 10px; margin-top: 10px; }
        button { flex: 1; padding: 12px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        .btn-secondary { background: #ecf0f1; color: #333; text-decoration:none; text-align:center; display:flex; align-items:center; justify-content:center; }
        .btn-secondary:hover { background: #bdc3c7; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Contratar Vendedor</h1>
        <p class="subtitle">Crear credenciales de acceso para el nuevo personal</p>

        <form action="?action=insertar" method="POST">
            <label>Nombre Completo</label>
            <input type="text" name="nombre" required placeholder="Ej: Laura Gómez">
            <label>RUT (Usuario)</label>
            <input type="text" name="rut" required placeholder="Ej: 11111111-K">
            <label>Email (Usuario de Acceso)</label>
            <input type="email" name="email" required placeholder="laura@bingus.cl">
            <small style="display:block; margin-top:-15px; margin-bottom:15px; color:#888;">Este email será usado para iniciar sesión.</small>
            <label>Contraseña</label>
            <input type="password" name="password" required placeholder="Crear contraseña segura">
            <label>Teléfono</label>
            <input type="text" name="telefono" placeholder="+56 9 ...">
            <label>Fecha Contratación</label>
            <input type="date" name="fecha_contratacion" value="<?php echo date('Y-m-d'); ?>" required>
            
            <div class="button-group">
                <button type="submit" class="btn-primary">💾 Guardar y Crear Acceso</button>
                <a href="?action=listar" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    <?php if(function_exists('mostrarAlerta')) mostrarAlerta(); ?>
</body>
</html>