/**
 * ============================================
 * JS — Pedidos (Listado) v3.0
 * ============================================
 * Actualizado para repartidores y nuevos estados.
 */

document.addEventListener('DOMContentLoaded', async () => {
    const tbody = document.getElementById('pedidosBody');
    
    const res = await Api.get('/pedidos');
    
    if (!res.success) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:40px; color:#888;">Error: ${res.message}</td></tr>`;
        return;
    }

    const pedidos = res.data;

    if (pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:40px; color:#888;">No hay pedidos registrados.</td></tr>';
        return;
    }

    tbody.innerHTML = pedidos.map(p => {
        // Badge estado con colores para nuevos estados
        const badgeMap = {
            'PENDIENTE': 'badge-warning',
            'PAGADO': 'badge-success',
            'EN_REPARTO': 'badge-info',
            'ENTREGADO': 'badge-success',
            'CANCELADO': 'badge-danger'
        };
        const badgeClass = badgeMap[p.estado] || 'badge-warning';

        // Icono de estado
        const iconMap = {
            'PENDIENTE': '⏳',
            'PAGADO': '✅',
            'EN_REPARTO': '🚚',
            'ENTREGADO': '📦',
            'CANCELADO': '❌'
        };
        const icon = iconMap[p.estado] || '';

        // Productos resumen
        const prods = (p.detalles || []).map(d => 
            `${d.producto_nombre} (x${d.cantidad})`
        ).join(', ');

        return `
        <tr>
            <td><strong>#${p.id_pedido}</strong></td>
            <td>${p.fecha}</td>
            <td>${p.cliente_nombre || '-'}</td>
            <td>${p.repartidor_nombre || '<em style="color:#999;">Sin asignar</em>'}</td>
            <td><strong>$${Number(p.total).toLocaleString('es-CL')}</strong></td>
            <td><span class="badge ${badgeClass}">${icon} ${p.estado}</span></td>
            <td style="font-size:12px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${prods || '-'}</td>
            <td>
                <a href="/bingus_petstore/views/admin/pedidos/editar.php?id=${p.id_pedido}" class="btn btn-info btn-sm">👁️ Ver</a>
            </td>
        </tr>`;
    }).join('');
});
