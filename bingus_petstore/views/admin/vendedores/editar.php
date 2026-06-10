<?php $page_title = 'Editar Vendedor - Bingus Petstore'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-sm">
    <div class="page-header">
        <h1>✏️ Editar Vendedor</h1>
        <a href="/bingus_petstore/views/admin/vendedores/listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="formEditar" onsubmit="editarVendedor(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" id="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>RUT</label>
                        <input type="text" id="rut" class="form-control" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Fecha de Contratación</label>
                    <input type="date" id="fecha" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success btn-block" style="margin-top:20px;">💾 Guardar Cambios</button>
            </form>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script>
const params = new URLSearchParams(window.location.search);
const idVendedor = params.get('id');

document.addEventListener('DOMContentLoaded', async () => {
    if (!idVendedor) { window.location.href = '/bingus_petstore/views/admin/vendedores/listar.php'; return; }

    const res = await Api.get('/vendedores/' + idVendedor);
    if (res.success) {
        const v = res.data;
        document.getElementById('nombre').value = v.nombre;
        document.getElementById('rut').value = v.rut;
        document.getElementById('email').value = v.email;
        document.getElementById('telefono').value = v.telefono || '';
        document.getElementById('fecha').value = v.fecha_contratacion || '';
    } else {
        Api.alert('error', 'Error', res.message);
    }
});

async function editarVendedor(e) {
    e.preventDefault();

    const res = await Api.put('/vendedores/' + idVendedor, {
        nombre: document.getElementById('nombre').value,
        rut: document.getElementById('rut').value,
        email: document.getElementById('email').value,
        telefono: document.getElementById('telefono').value,
        fecha_contratacion: document.getElementById('fecha').value
    });

    if (res.success) {
        Api.alert('success', 'Actualizado', res.message);
        setTimeout(() => window.location.href = '/bingus_petstore/views/admin/vendedores/listar.php', 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
