/**
 * ============================================
 * JS — Vendedores (Listado)
 * ============================================
 */

document.addEventListener('DOMContentLoaded', async () => {
    const tbody = document.getElementById('vendedoresBody');
    
    const res = await Api.get('/vendedores');
    
    if (!res.success) {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:40px; color:#888;">Error: ${res.message}</td></tr>`;
        return;
    }

    const vendedores = res.data;

    if (vendedores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:40px; color:#888;">No hay vendedores registrados.</td></tr>';
        return;
    }

    tbody.innerHTML = vendedores.map(v => `
        <tr>
            <td><strong>${v.nombre}</strong></td>
            <td>${v.rut || '-'}</td>
            <td>${v.email}</td>
            <td>${v.telefono || '-'}</td>
            <td>${v.fecha_contratacion || '-'}</td>
            <td>
                <div style="display:flex; gap:8px;">
                    <a href="/bingus_petstore/views/admin/vendedores/editar.php?id=${v.id_vendedor}" class="btn btn-info btn-sm">✏️</a>
                    <button class="btn btn-danger btn-sm" onclick="eliminarVendedor(${v.id_vendedor})">🗑️</button>
                </div>
            </td>
        </tr>
    `).join('');
});

async function eliminarVendedor(id) {
    const confirmed = await Api.confirm('¿Eliminar vendedor?', 'El vendedor será desactivado del sistema.');
    if (!confirmed) return;

    const res = await Api.delete('/vendedores/' + id);
    if (res.success) {
        Api.alert('success', 'Eliminado', res.message);
        setTimeout(() => location.reload(), 1200);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
