/**
 * ============================================
 * JS — Productos (Listado)
 * ============================================
 * Carga y renderiza productos desde la API.
 */

document.addEventListener('DOMContentLoaded', async () => {
    const grid = document.getElementById('productosGrid');
    
    const res = await Api.get('/productos');
    
    if (!res.success) {
        grid.innerHTML = '<div class="empty-state"><h2>Error al cargar productos</h2><p>' + res.message + '</p></div>';
        return;
    }

    const productos = res.data;

    if (productos.length === 0) {
        grid.innerHTML = '<div class="empty-state"><h2>No hay productos registrados</h2><p>Comienza agregando inventario a tu tienda.</p></div>';
        return;
    }

    grid.innerHTML = productos.map(p => {
        // Stock badge
        let stockClass = 'stock-high';
        if (p.stock <= 5) stockClass = 'stock-low';
        else if (p.stock <= 20) stockClass = 'stock-medium';

        // Imagen
        let imgHtml = '';
        if (p.imagen) {
            imgHtml = `<img src="/bingus_petstore/uploads/productos/${p.imagen}" alt="${p.nombre}">`;
        } else {
            imgHtml = '<div class="no-image">📦</div>';
        }

        return `
        <div class="card product-card">
            <div class="product-image">${imgHtml}</div>
            <div class="product-header">
                <div class="product-name">${p.nombre}</div>
                <span class="badge badge-primary">${p.categoria_nombre}</span>
            </div>
            <div class="product-body">
                <div class="product-desc">${(p.descripcion || '').substring(0, 80)}...</div>
                <div class="product-price">$${Number(p.precio).toLocaleString('es-CL')}</div>
                <div class="product-meta">
                    <span>Prov: ${p.proveedor_nombre}</span>
                    <span class="badge ${stockClass}">Stock: ${p.stock}</span>
                </div>
                <div class="btn-actions">
                    <a href="/bingus_petstore/views/admin/productos/editar.php?id=${p.id_producto}" class="btn btn-info btn-sm">✏️ Editar</a>
                    <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${p.id_producto})">🗑️ Borrar</button>
                </div>
            </div>
        </div>`;
    }).join('');
});

async function eliminarProducto(id) {
    const confirmed = await Api.confirm('¿Estás seguro?', 'No podrás revertir esto.');
    if (!confirmed) return;

    const res = await Api.delete('/productos/' + id);
    if (res.success) {
        Api.alert('success', 'Eliminado', res.message);
        setTimeout(() => location.reload(), 1200);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
