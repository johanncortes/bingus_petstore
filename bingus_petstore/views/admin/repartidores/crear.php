<?php $page_title = 'Crear Repartidor - Bingus Petstore'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-sm">
    <div class="page-header">
        <h1>➕ Nuevo Repartidor</h1>
        <a href="/bingus_petstore/views/admin/repartidores/listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div id="alertaLimite" style="display:none; background:#fff3cd; color:#856404; padding:15px; border-radius:10px; margin-bottom:20px; text-align:center;">
        ⚠️ Ya tienes 2 repartidores activos asignados. No puedes agregar más.
    </div>

    <div class="card">
        <div class="card-body">
            <form id="formCrear" onsubmit="crearRepartidor(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" id="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>RUT</label>
                        <input type="text" id="rut" class="form-control" placeholder="12345678-9" required>
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

                <button type="submit" class="btn btn-success btn-block" id="btnCrear" style="margin-top:20px;">💾 Registrar Repartidor</button>
            </form>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script>
// Fecha default: hoy
document.getElementById('fecha').value = new Date().toISOString().split('T')[0];

// Verificar si ya tiene 2 repartidores
document.addEventListener('DOMContentLoaded', async () => {
    const res = await Api.get('/repartidores');
    if (res.success && res.data.length >= 2) {
        document.getElementById('alertaLimite').style.display = 'block';
        document.getElementById('btnCrear').disabled = true;
        document.getElementById('btnCrear').textContent = 'Límite alcanzado (2/2)';
    }
});

async function crearRepartidor(e) {
    e.preventDefault();

    const res = await Api.post('/repartidores', {
        nombre: document.getElementById('nombre').value,
        rut: document.getElementById('rut').value,
        email: document.getElementById('email').value,
        telefono: document.getElementById('telefono').value,
        fecha_contratacion: document.getElementById('fecha').value
    });

    if (res.success) {
        Api.alert('success', 'Repartidor Registrado', res.message);
        setTimeout(() => window.location.href = '/bingus_petstore/views/admin/repartidores/listar.php', 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
