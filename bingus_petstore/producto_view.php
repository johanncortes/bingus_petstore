<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Productos - Bingus Petstore</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* (Tus estilos CSS existentes... mantenlos igual) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); }
        .header h1 { color: #333; font-size: 28px; }
        .header-buttons { display: flex; gap: 10px; align-items: center; }
        .btn-crear { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-crear:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3); }
        .btn-volver { background: #95a5a6; color: white; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-volver:hover { background: #7f8c8d; transform: translateY(-2px); }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); overflow: hidden; transition: all 0.3s; display: flex; flex-direction: column; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15); }
        .product-image-container { width: 100%; height: 200px; background: #f0f0f0; display: flex; justify-content: center; align-items: center; overflow: hidden; position: relative; }
        .product-image-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .product-card:hover .product-image-container img { transform: scale(1.05); }
        .no-image { font-size: 40px; color: #ccc; }
        .product-header { padding: 15px 20px 0; }
        .product-name { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 5px; line-height: 1.2; }
        .product-category { font-size: 12px; background: #eef2f7; color: #667eea; padding: 4px 10px; border-radius: 20px; font-weight: 600; display: inline-block; }
        .product-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .product-desc { font-size: 13px; color: #666; margin-bottom: 15px; flex-grow: 1; }
        .product-price { font-size: 22px; font-weight: 700; color: #667eea; margin-bottom: 10px; }
        .product-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 12px; color: #888; }
        .stock-tag { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .stock-high { background: #d4edda; color: #155724; }
        .stock-medium { background: #fff3cd; color: #856404; }
        .stock-low { background: #f8d7da; color: #721c24; }
        .product-actions { display: flex; gap: 10px; border-top: 1px solid #eee; padding-top: 15px; margin-top: auto; }
        .btn-small { flex: 1; padding: 8px; border-radius: 5px; font-size: 13px; font-weight: 600; text-align: center; text-decoration: none; color: white; cursor: pointer; border: none; }
        .btn-edit { background: #3498db; }
        .btn-edit:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; }
        .btn-delete:hover { background: #c0392b; }
        .empty-state { grid-column: 1 / -1; text-align: center; padding: 50px; background: white; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐾 Productos de la Tienda</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn-volver">🏠 Volver</a>
                <a href="?action=agregar" class="btn-crear">➕ Nuevo Producto</a>
            </div>
        </div>

        <?php if (isset($productos) && !empty($productos)): ?>
            <div class="products-grid">
                <?php foreach ($productos as $producto): ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <?php 
                                $ruta_img = 'uploads/productos/' . ($producto['imagen'] ?? '');
                                if (!empty($producto['imagen']) && file_exists($ruta_img)): 
                            ?>
                                <img src="<?php echo $ruta_img; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <?php else: ?>
                                <div class="no-image">📦</div>
                            <?php endif; ?>
                        </div>

                        <div class="product-header">
                            <div class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                            <span class="product-category"><?php echo htmlspecialchars($producto['categoria_nombre']); ?></span>
                        </div>

                        <div class="product-body">
                            <div class="product-desc">
                                <?php echo htmlspecialchars(substr($producto['descripcion'] ?? '', 0, 80)) . '...'; ?>
                            </div>
                            <div class="product-price">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></div>
                            <div class="product-meta">
                                <span>Prov: <?php echo htmlspecialchars($producto['proveedor_nombre']); ?></span>
                                <?php 
                                    $s = $producto['stock'];
                                    if ($s > 20) $st_class = 'stock-high';
                                    elseif ($s > 5) $st_class = 'stock-medium';
                                    else $st_class = 'stock-low';
                                ?>
                                <span class="stock-tag <?php echo $st_class; ?>">Stock: <?php echo $s; ?></span>
                            </div>

                            <div class="product-actions">
                                <a href="?action=editar&id_producto=<?php echo $producto['id_producto']; ?>" class="btn-small btn-edit">✏️ Editar</a>
                                
                                <button type="button" class="btn-small btn-delete" 
                                    onclick="confirmarEliminar('?action=eliminar&id_producto=<?php echo $producto['id_producto']; ?>')">
                                    🗑️ Borrar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No hay productos registrados</h2>
                <p>Comienza agregando inventario a tu tienda.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmarEliminar(url) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        }
    </script>

    <?php mostrarAlerta(); ?>
</body>
</html>