<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Nuevo Producto - Bingus Petstore</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px;
        }
        .container {
            background: white; border-radius: 10px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px; max-width: 600px; width: 100%;
        }
        h1 { color: #333; margin-bottom: 10px; font-size: 28px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px; }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; transition: border-color 0.3s;
        }
        input:focus, textarea:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        input[type="file"] { padding: 10px; background: #f8f9fa; border: 1px dashed #ccc; width: 100%; }
        
        textarea { resize: vertical; min-height: 100px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .required { color: #e74c3c; }

        .button-group { display: flex; gap: 10px; margin-top: 30px; }
        button, a.btn-cancelar {
            flex: 1; padding: 12px; border: none; border-radius: 5px;
            font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center;
            transition: all 0.3s;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        
        .btn-cancelar { background: #ecf0f1; color: #333; }
        .btn-cancelar:hover { background: #bdc3c7; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Nuevo Producto</h1>
        <p class="subtitle">Agrega mercancía al inventario de la tienda</p>

        <form method="POST" action="?action=insertar" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nombre del Producto <span class="required">*</span></label>
                <input type="text" name="nombre" required placeholder="Ej: Correa Retráctil">
            </div>

            <div class="form-group">
                <label>Imagen del Producto</label>
                <input type="file" name="imagen" accept="image/*">
                <small style="color:#888;">Formatos permitidos: JPG, PNG, GIF</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoría <span class="required">*</span></label>
                    <select name="id_categoria" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach($categorias as $c): ?>
                            <option value="<?php echo $c['id_categoria']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Proveedor <span class="required">*</span></label>
                    <select name="id_proveedor" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach($proveedores as $p): ?>
                            <option value="<?php echo $p['id_proveedor']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" placeholder="Detalles del producto..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio (CLP) <span class="required">*</span></label>
                    <input type="number" name="precio" placeholder="0" required min="0">
                </div>
                <div class="form-group">
                    <label>Stock Inicial <span class="required">*</span></label>
                    <input type="number" name="stock" placeholder="0" required min="0">
                </div>
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">💾 Guardar Producto</button>
                <a href="?action=listar" class="btn-cancelar">Cancelar</a>
            </div>
        </form>
    </div>
    <?php if(function_exists('mostrarAlerta')) mostrarAlerta(); ?>
</body>
</html>