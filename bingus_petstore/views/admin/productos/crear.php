<?php $page_title = 'Crear Producto - Bingus Petstore'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-sm">
    <div class="page-header">
        <h1>➕ Nuevo Producto</h1>
        <a href="/bingus_petstore/views/admin/productos/listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="formCrear" enctype="multipart/form-data" onsubmit="crearProducto(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Precio ($)</label>
                        <input type="number" name="precio" class="form-control" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control"></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="id_categoria" class="form-control" id="selectCategoria" required></select>
                    </div>
                    <div class="form-group">
                        <label>Proveedor</label>
                        <select name="id_proveedor" class="form-control" id="selectProveedor" required></select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-block" style="margin-top:20px;">💾 Guardar Producto</button>
            </form>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Cargar categorías
    const cats = await Api.get('/productos/categorias');
    if (cats.success) {
        document.getElementById('selectCategoria').innerHTML = 
            cats.data.map(c => `<option value="${c.id_categoria}">${c.nombre}</option>`).join('');
    }
    // Cargar proveedores
    const provs = await Api.get('/productos/proveedores');
    if (provs.success) {
        document.getElementById('selectProveedor').innerHTML = 
            provs.data.map(p => `<option value="${p.id_proveedor}">${p.nombre}</option>`).join('');
    }
});

async function crearProducto(e) {
    e.preventDefault();
    const form = document.getElementById('formCrear');
    const formData = new FormData(form);

    const res = await Api.postForm('/productos', formData);
    if (res.success) {
        Api.alert('success', 'Creado', res.message);
        setTimeout(() => window.location.href = '/bingus_petstore/views/admin/productos/listar.php', 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
