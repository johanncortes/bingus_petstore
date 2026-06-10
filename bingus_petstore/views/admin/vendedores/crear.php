<?php $page_title = 'Crear Vendedor - Bingus Petstore'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-sm">
    <div class="page-header">
        <h1>➕ Nuevo Vendedor</h1>
        <a href="/bingus_petstore/views/admin/vendedores/listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="formCrear" onsubmit="crearVendedor(event)">
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
                        <label>Email (para login)</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" id="password" class="form-control" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Fecha de Contratación</label>
                        <input type="date" id="fecha" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-block" style="margin-top:20px;">💾 Registrar Vendedor</button>
            </form>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script>
// Fecha default: hoy
document.getElementById('fecha').value = new Date().toISOString().split('T')[0];

async function crearVendedor(e) {
    e.preventDefault();

    const res = await Api.post('/vendedores', {
        nombre: document.getElementById('nombre').value,
        rut: document.getElementById('rut').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        telefono: document.getElementById('telefono').value,
        fecha_contratacion: document.getElementById('fecha').value
    });

    if (res.success) {
        Api.alert('success', 'Vendedor Contratado', res.message);
        setTimeout(() => window.location.href = '/bingus_petstore/views/admin/vendedores/listar.php', 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
