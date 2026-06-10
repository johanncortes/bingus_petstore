/**
 * ============================================
 * JS — Compradores (Listado)
 * ============================================
 */

document.addEventListener('DOMContentLoaded', async () => {
    const tbody = document.getElementById('compradoresBody');

    const res = await Api.get('/clientes');

    if (!res.success) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:40px; color:#888;">Error: ${res.message}</td></tr>`;
        return;
    }

    const compradores = res.data;

    if (compradores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:40px; color:#888;">No hay compradores registrados.</td></tr>';
        return;
    }

    tbody.innerHTML = compradores.map(c => {
        const totalGastado = Number(c.total_gastado || 0).toLocaleString('es-CL');
        const ultimaCompra = c.ultima_compra ? String(c.ultima_compra).substring(0, 10) : '-';

        return `
        <tr>
            <td><strong>${c.nombre || '-'}</strong></td>
            <td>${c.rut || '-'}</td>
            <td>${c.email || '-'}</td>
            <td>${c.telefono || '-'}</td>
            <td>${c.direccion || '-'}</td>
            <td>${c.total_pedidos || 0}</td>
            <td><strong>$${totalGastado}</strong></td>
            <td>${ultimaCompra}</td>
        </tr>`;
    }).join('');
});
