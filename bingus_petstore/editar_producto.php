<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - Bingus Petstore</title>
    <style>
        /* Mismos estilos que crear_producto.php */
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
        input[type="file"] { padding: 10px; background: #f8f9fa; border: 1px dashed #ccc; width: 100%; }
        textarea { resize: vertical; min-height: 100px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .required { color: #e74c3c; }
        .button-group { display: flex; gap: 10px; margin-top: 30px; }
        button, a.btn-cancelar {
            flex: 1; padding: 12px; border: none; border-radius: 5px;
            font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        .btn-cancelar { background: #ecf0f1; color: #333; }
        .btn-cancelar:hover { background: #bdc3c7; }
        
        .img-preview-box { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; background: #f9f9f9; padding: 10px; border-radius: 5px; border: 1px solid #eee; }
        .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; }
        .delete-check { display: flex; align-items: center; gap: 5px; color: #c0392b; font-size: 13px; font-weight: bold; cursor: pointer; }
        .delete-check input { width: auto; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ Editar Producto</h1>
        <p class="subtitle">Actualiza la información del producto</p>

        <?php if (isset($producto) && !empty($producto)): ?>
            <form method="POST" action="?action=actualizar" enctype="multipart/form-data">
                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                
                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($producto['imagen'] ?? ''); ?>">

                <div class="form-group">
                    <label>Nombre del Producto <span class="required">*</span></label>
                    <input type="text" name="nombre" required value="<?php echo htmlspecialchars($producto['nombre']); ?>">
                </div>

                <div class="form-group">
                    <label>Imagen del Producto</label>
                    
                    <?php if(!empty($producto['imagen']) && file_exists('uploads/productos/' . $producto['imagen'])): ?>
                        <div class="img-preview-box">
                            <img src="uploads/productos/<?php echo $producto['imagen']; ?>" class="img-thumb">
                            <div>
                                <div style="font-size:12px; color:#666;">Imagen actual</div>
                                <label class="delete-check">
                                    <input type="checkbox" name="eliminar_imagen" value="1"> 🗑️ Eliminar esta foto
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" name="imagen" accept="image/*">
                    <small style="color:#888;">Sube un archivo solo si deseas cambiar la imagen actual.</small>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Categoría <span class="required">*</span></label>
                        <select name="id_categoria" required>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat['id_categoria'] == $producto['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Proveedor <span class="required">*</span></label>
                        <select name="id_proveedor" required>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?php echo $prov['id_proveedor']; ?>" <?php echo ($prov['id_proveedor'] == $producto['id_proveedor']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prov['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Precio <span class="required">*</span></label>
                        <input type="number" name="precio" step="0.01" min="0" required value="<?php echo $producto['precio']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock <span class="required">*</span></label>
                        <input type="number" name="stock" min="0" required value="<?php echo $producto['stock']; ?>">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-primary">💾 Guardar Cambios</button>
                    <a href="?action=listar" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <p>Producto no encontrado.</p>
            <a href="?action=listar" class="btn-cancelar">Volver</a>
        <?php endif; ?>
    </div>
</body>
</html>