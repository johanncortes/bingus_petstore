<?php $page_title = 'Compradores - Bingus Petstore'; $page_script = 'compradores.js'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>🧾 Compradores</h1>
        <a href="/bingus_petstore/views/admin/dashboard.php" class="btn btn-secondary">🏠 Volver</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Pedidos</th>
                    <th>Total Gastado</th>
                    <th>Última Compra</th>
                </tr>
            </thead>
            <tbody id="compradoresBody">
                <tr><td colspan="8" class="loading"></td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
