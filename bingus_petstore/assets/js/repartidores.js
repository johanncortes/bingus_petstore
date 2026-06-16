/**
 * ============================================
 * JS — Repartidores (Listado)
 * ============================================
 */

document.addEventListener('DOMContentLoaded', async () => {
    const tbody = document.getElementById('repartidoresBody');
    
    const res = await Api.get('/repartidores');
    
    if (!res.success) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:40px; color:#888;">Error: ${res.message}</td></tr>`;
        return;
    }

    const repartidores = res.data;

    if (repartidores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#888;">No hay repartidores registrados.</td></tr>';
        return;
    }

    tbody.innerHTML = repartidores.map(r => {
        // Badge de estado
        let estadoBadge = '';
        if (r.estado_disponibilidad === 'DISPONIBLE') {
            estadoBadge = '<span class="badge badge-success">🟢 Disponible</span>';
        } else if (r.estado_disponibilidad === 'EN_REPARTO') {
            estadoBadge = '<span class="badge badge-warning">🏍️ En Reparto</span>';
        } else {
            estadoBadge = '<span class="badge badge-danger">🔴 Inactivo</span>';
        }

        return `
        <tr>
            <td><strong>${r.nombre}</strong></td>
            <td>${r.rut || '-'}</td>
            <td>${r.email}</td>
            <td>${r.telefono || '-'}</td>
            <td>${estadoBadge}</td>
            <td>${r.fecha_contratacion || '-'}</td>
            <td>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="/bingus_petstore/views/admin/repartidores/editar.php?id=${r.id_repartidor}" class="btn btn-info btn-sm">✏️</a>
                    <button class="btn btn-danger btn-sm" onclick="eliminarRepartidor(${r.id_repartidor})">🗑️</button>
                </div>
            </td>
        </tr>`;
    }).join('');
});

async function eliminarRepartidor(id) {
    const confirmed = await Api.confirm('¿Eliminar repartidor?', 'El repartidor será desactivado del sistema.');
    if (!confirmed) return;

    const res = await Api.delete('/repartidores/' + id);
    if (res.success) {
        Api.alert('success', 'Eliminado', res.message);
        setTimeout(() => location.reload(), 1200);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
