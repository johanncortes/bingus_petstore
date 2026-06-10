<?php $page_title = 'Editar Pedido - Bingus Petstore'; ?>
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container-sm">
    <div class="page-header">
        <h1>📋 Detalle del Pedido #<span id="pedidoId">...</span></h1>
        <a href="/bingus_petstore/views/admin/pedidos/listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <!-- Info del pedido -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <div class="form-grid">
                <div><strong>Cliente:</strong> <span id="clienteNombre">-</span></div>
                <div><strong>RUT:</strong> <span id="clienteRut">-</span></div>
                <div><strong>Vendedor:</strong> <span id="vendedorNombre">-</span></div>
                <div><strong>Fecha:</strong> <span id="pedidoFecha">-</span></div>
                <div><strong>Total:</strong> <span id="pedidoTotal" style="color:var(--primary); font-weight:bold;">-</span></div>
                <div><strong>Estado:</strong> <span id="pedidoEstado" class="badge">-</span></div>
            </div>
        </div>
    </div>

    <!-- Productos del pedido -->
    <div class="table-container" style="margin-bottom:20px;">
        <table>
            <thead><tr><th>Producto</th><th>Cant</th><th>P/U</th><th>Subtotal</th></tr></thead>
            <tbody id="detalleBody"></tbody>
        </table>
    </div>

    <!-- Cambiar estado (solo si es PENDIENTE) -->
    <div class="card" id="panelEstado" style="display:none;">
        <div class="card-body" style="text-align:center;">
            <h3 style="margin-bottom:15px;">Cambiar Estado</h3>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button class="btn btn-success btn-lg" onclick="cambiarEstado('PAGADO')">✅ Aprobar (Pagado)</button>
                <button class="btn btn-danger btn-lg" onclick="cambiarEstado('CANCELADO')">❌ Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script>
const params = new URLSearchParams(window.location.search);
const idPedido = params.get('id');

document.addEventListener('DOMContentLoaded', async () => {
    if (!idPedido) { window.location.href = '/bingus_petstore/views/admin/pedidos/listar.php'; return; }

    const res = await Api.get('/pedidos/' + idPedido);
    if (!res.success) { Api.alert('error', 'Error', res.message); return; }

    const p = res.data;
    document.getElementById('pedidoId').textContent = p.id_pedido;
    document.getElementById('clienteNombre').textContent = p.cliente_nombre;
    document.getElementById('clienteRut').textContent = p.cliente_rut;
    document.getElementById('vendedorNombre').textContent = p.vendedor_nombre;
    document.getElementById('pedidoFecha').textContent = p.fecha;
    document.getElementById('pedidoTotal').textContent = '$' + Number(p.total).toLocaleString('es-CL');

    // Badge estado
    const badge = document.getElementById('pedidoEstado');
    badge.textContent = p.estado;
    if (p.estado === 'PAGADO') badge.className = 'badge badge-success';
    else if (p.estado === 'PENDIENTE') badge.className = 'badge badge-warning';
    else badge.className = 'badge badge-danger';

    // Detalles
    document.getElementById('detalleBody').innerHTML = (p.detalles || []).map(d => `
        <tr>
            <td>${d.producto_nombre}</td>
            <td>x${d.cantidad}</td>
            <td>$${Number(d.precio_unitario).toLocaleString('es-CL')}</td>
            <td><strong>$${Number(d.subtotal).toLocaleString('es-CL')}</strong></td>
        </tr>
    `).join('');

    // Mostrar panel de cambio solo si PENDIENTE
    if (p.estado === 'PENDIENTE') {
        document.getElementById('panelEstado').style.display = 'block';
    }
});

async function cambiarEstado(estado) {
    const txt = estado === 'PAGADO' ? 'Se descontará stock.' : 'El pedido quedará cancelado.';
    const confirmed = await Api.confirm(`¿Cambiar a ${estado}?`, txt);
    if (!confirmed) return;

    const res = await Api.put('/pedidos/' + idPedido + '/estado', { estado });
    if (res.success) {
        Api.alert('success', 'Actualizado', res.message);
        setTimeout(() => location.reload(), 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
