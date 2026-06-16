<?php $page_title = 'Editar Repartidor - Bingus Petstore'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-sm">
    <div class="page-header">
        <h1>✏️ Editar Repartidor</h1>
        <a href="/bingus_petstore/views/admin/repartidores/listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="formEditar" onsubmit="editarRepartidor(event)">
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
const idRepartidor = params.get('id');

document.addEventListener('DOMContentLoaded', async () => {
    if (!idRepartidor) { window.location.href = '/bingus_petstore/views/admin/repartidores/listar.php'; return; }

    const res = await Api.get('/repartidores/' + idRepartidor);
    if (res.success) {
        const r = res.data;
        document.getElementById('nombre').value = r.nombre;
        document.getElementById('rut').value = r.rut;
        document.getElementById('email').value = r.email;
        document.getElementById('telefono').value = r.telefono || '';
        document.getElementById('fecha').value = r.fecha_contratacion || '';
    } else {
        Api.alert('error', 'Error', res.message);
    }
});

async function editarRepartidor(e) {
    e.preventDefault();

    const res = await Api.put('/repartidores/' + idRepartidor, {
        nombre: document.getElementById('nombre').value,
        rut: document.getElementById('rut').value,
        email: document.getElementById('email').value,
        telefono: document.getElementById('telefono').value,
        fecha_contratacion: document.getElementById('fecha').value
    });

    if (res.success) {
        Api.alert('success', 'Actualizado', res.message);
        setTimeout(() => window.location.href = '/bingus_petstore/views/admin/repartidores/listar.php', 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
