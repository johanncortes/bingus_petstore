<?php $page_title = 'Vendedores - Bingus Petstore'; $page_script = 'vendedores.js'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>👥 Gestión de Vendedores</h1>
        <div class="header-buttons">
            <a href="/bingus_petstore/views/admin/dashboard.php" class="btn btn-secondary">🏠 Volver</a>
            <a href="/bingus_petstore/views/admin/vendedores/crear.php" class="btn btn-primary">➕ Nuevo Vendedor</a>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Fecha Contratación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="vendedoresBody">
                <tr><td colspan="6" class="loading"></td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
