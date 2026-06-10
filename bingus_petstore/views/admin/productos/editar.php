<?php $page_title = 'Editar Producto - Bingus Petstore'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-sm">
    <div class="page-header">
        <h1>✏️ Editar Producto</h1>
        <a href="/bingus_petstore/views/admin/productos/listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="formEditar" enctype="multipart/form-data" onsubmit="editarProducto(event)">
                <input type="hidden" name="imagen_actual" id="imagenActual">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Precio ($)</label>
                        <input type="number" name="precio" id="precio" class="form-control" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control"></textarea>
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
                        <input type="number" name="stock" id="stock" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Nueva Imagen (opcional)</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*">
                    </div>
                </div>

                <!-- Imagen actual -->
                <div id="imagenPreview" style="margin:15px 0; display:none;">
                    <label>Imagen actual:</label>
                    <div style="display:flex; align-items:center; gap:10px; margin-top:5px;">
                        <img id="imgPreview" style="width:80px; height:80px; object-fit:cover; border-radius:8px;">
                        <label style="font-weight:normal; cursor:pointer;">
                            <input type="checkbox" name="eliminar_imagen" value="1"> Eliminar imagen
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-block" style="margin-top:20px;">💾 Guardar Cambios</button>
            </form>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script>
const params = new URLSearchParams(window.location.search);
const idProducto = params.get('id');

document.addEventListener('DOMContentLoaded', async () => {
    if (!idProducto) { window.location.href = '/bingus_petstore/views/admin/productos/listar.php'; return; }

    // Cargar selects
    const [cats, provs, prod] = await Promise.all([
        Api.get('/productos/categorias'),
        Api.get('/productos/proveedores'),
        Api.get('/productos/' + idProducto)
    ]);

    if (cats.success) {
        document.getElementById('selectCategoria').innerHTML = 
            cats.data.map(c => `<option value="${c.id_categoria}">${c.nombre}</option>`).join('');
    }
    if (provs.success) {
        document.getElementById('selectProveedor').innerHTML = 
            provs.data.map(p => `<option value="${p.id_proveedor}">${p.nombre}</option>`).join('');
    }

    if (prod.success) {
        const p = prod.data;
        document.getElementById('nombre').value = p.nombre;
        document.getElementById('precio').value = p.precio;
        document.getElementById('descripcion').value = p.descripcion || '';
        document.getElementById('stock').value = p.stock;
        document.getElementById('selectCategoria').value = p.id_categoria;
        document.getElementById('selectProveedor').value = p.id_proveedor;
        document.getElementById('imagenActual').value = p.imagen || '';

        if (p.imagen) {
            document.getElementById('imagenPreview').style.display = 'block';
            document.getElementById('imgPreview').src = '/bingus_petstore/uploads/productos/' + p.imagen;
        }
    }
});

async function editarProducto(e) {
    e.preventDefault();
    const form = document.getElementById('formEditar');
    const formData = new FormData(form);

    const res = await Api.postForm('/productos/' + idProducto, formData);
    if (res.success) {
        Api.alert('success', 'Actualizado', res.message);
        setTimeout(() => window.location.href = '/bingus_petstore/views/admin/productos/listar.php', 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
