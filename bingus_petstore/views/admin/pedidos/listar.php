<?php $page_title = 'Pedidos - Bingus Petstore'; $page_script = 'pedidos.js'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>📋 Historial de Pedidos</h1>
        <a href="/bingus_petstore/views/admin/dashboard.php" class="btn btn-secondary">🏠 Volver</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="pedidosBody">
                <tr><td colspan="8" class="loading"></td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
