<?php $page_title = 'Productos - Bingus Petstore'; $page_script = 'productos.js'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>🐾 Productos de la Tienda</h1>
        <div class="header-buttons">
            <a href="/bingus_petstore/views/admin/dashboard.php" class="btn btn-secondary">🏠 Volver</a>
            <a href="/bingus_petstore/views/admin/productos/crear.php" class="btn btn-primary">➕ Nuevo Producto</a>
        </div>
    </div>

    <div class="grid-cards" id="productosGrid">
        <div class="loading"></div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
