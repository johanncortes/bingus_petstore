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
                <div><strong>Repartidor:</strong> <span id="repartidorNombre">-</span></div>
                <div><strong>Fecha:</strong> <span id="pedidoFecha">-</span></div>
                <div><strong>Dirección Entrega:</strong> <span id="pedidoDireccion">-</span></div>
                <div><strong>Estado:</strong> <span id="pedidoEstado" class="badge">-</span></div>
            </div>
            <!-- Desglose IVA -->
            <div style="margin-top:15px; padding-top:15px; border-top:1px solid #eee;">
                <div class="form-grid">
                    <div><strong>Subtotal Neto:</strong> <span id="pedidoNeto">-</span></div>
                    <div><strong>IVA (19%):</strong> <span id="pedidoIva">-</span></div>
                    <div><strong>Total c/IVA:</strong> <span id="pedidoTotal" style="color:var(--primary); font-weight:bold; font-size:18px;">-</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos del pedido -->
    <div class="table-container" style="margin-bottom:20px;">
        <table>
            <thead><tr><th>Producto</th><th>Cant</th><th>P/U Neto</th><th>IVA</th><th>Subtotal</th></tr></thead>
            <tbody id="detalleBody"></tbody>
        </table>
    </div>

    <!-- Asignar Repartidor -->
    <div class="card" id="panelRepartidor" style="display:none; margin-bottom:20px;">
        <div class="card-body">
            <h3 style="margin-bottom:15px;">🚚 Asignar Repartidor</h3>
            <div style="display:flex; gap:10px; align-items:end;">
                <div class="form-group" style="flex:1; margin-bottom:0;">
                    <label>Repartidor disponible</label>
                    <select id="selectRepartidor" class="form-control">
                        <option value="">Seleccionar...</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="asignarRepartidor()">Asignar</button>
            </div>
        </div>
    </div>

    <!-- Cambiar estado -->
    <div class="card" id="panelEstado" style="display:none;">
        <div class="card-body" style="text-align:center;">
            <h3 style="margin-bottom:15px;">Cambiar Estado</h3>
            <div id="botonesEstado" style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;"></div>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script>
const params = new URLSearchParams(window.location.search);
const idPedido = params.get('id');

// Transiciones válidas de estado
const transiciones = {
    'PENDIENTE': ['PAGADO', 'CANCELADO'],
    'PAGADO': ['EN_REPARTO', 'CANCELADO'],
    'EN_REPARTO': ['ENTREGADO'],
    'ENTREGADO': [],
    'CANCELADO': []
};

const botonesConfig = {
    'PAGADO': { class: 'btn-success', icon: '✅', label: 'Aprobar (Pagado)' },
    'EN_REPARTO': { class: 'btn-primary', icon: '🚚', label: 'Enviar a Reparto' },
    'ENTREGADO': { class: 'btn-success', icon: '📦', label: 'Marcar Entregado' },
    'CANCELADO': { class: 'btn-danger', icon: '❌', label: 'Cancelar' }
};

document.addEventListener('DOMContentLoaded', async () => {
    if (!idPedido) { window.location.href = '/bingus_petstore/views/admin/pedidos/listar.php'; return; }

    const res = await Api.get('/pedidos/' + idPedido);
    if (!res.success) { Api.alert('error', 'Error', res.message); return; }

    const p = res.data;
    document.getElementById('pedidoId').textContent = p.id_pedido;
    document.getElementById('clienteNombre').textContent = p.cliente_nombre;
    document.getElementById('clienteRut').textContent = p.cliente_rut;
    document.getElementById('repartidorNombre').textContent = p.repartidor_nombre || 'Sin asignar';
    document.getElementById('pedidoFecha').textContent = p.fecha;
    document.getElementById('pedidoDireccion').textContent = p.direccion_entrega || 'No especificada';

    // Desglose IVA
    document.getElementById('pedidoNeto').textContent = '$' + Number(p.subtotal_neto || 0).toLocaleString('es-CL');
    document.getElementById('pedidoIva').textContent = '$' + Number(p.total_iva || 0).toLocaleString('es-CL');
    document.getElementById('pedidoTotal').textContent = '$' + Number(p.total).toLocaleString('es-CL');

    // Badge estado
    const badge = document.getElementById('pedidoEstado');
    badge.textContent = p.estado;
    const badgeMap = {
        'PAGADO': 'badge-success', 'PENDIENTE': 'badge-warning',
        'EN_REPARTO': 'badge-info', 'ENTREGADO': 'badge-success',
        'CANCELADO': 'badge-danger'
    };
    badge.className = 'badge ' + (badgeMap[p.estado] || 'badge-warning');

    // Detalles con IVA
    document.getElementById('detalleBody').innerHTML = (p.detalles || []).map(d => `
        <tr>
            <td>${d.producto_nombre}</td>
            <td>x${d.cantidad}</td>
            <td>$${Number(d.precio_neto || 0).toLocaleString('es-CL')}</td>
            <td>$${Number(d.iva || 0).toLocaleString('es-CL')}</td>
            <td><strong>$${Number(d.subtotal).toLocaleString('es-CL')}</strong></td>
        </tr>
    `).join('');

    // Mostrar panel de asignación de repartidor si está PAGADO y sin repartidor
    if ((p.estado === 'PAGADO' || p.estado === 'PENDIENTE') && !p.id_repartidor) {
        document.getElementById('panelRepartidor').style.display = 'block';
        await cargarRepartidoresDisponibles();
    }

    // Mostrar botones de estado según transiciones válidas
    const posibles = transiciones[p.estado] || [];
    if (posibles.length > 0) {
        document.getElementById('panelEstado').style.display = 'block';
        const container = document.getElementById('botonesEstado');
        container.innerHTML = posibles.map(est => {
            const cfg = botonesConfig[est];
            // Si quiere ir a EN_REPARTO pero no tiene repartidor, deshabilitar
            const disabled = (est === 'EN_REPARTO' && !p.id_repartidor) ? 'disabled title="Asigne un repartidor primero"' : '';
            return `<button class="btn ${cfg.class} btn-lg" onclick="cambiarEstado('${est}')" ${disabled}>${cfg.icon} ${cfg.label}</button>`;
        }).join('');
    }
});

async function cargarRepartidoresDisponibles() {
    const res = await Api.get('/repartidores/disponibles');
    if (res.success) {
        const select = document.getElementById('selectRepartidor');
        res.data.forEach(r => {
            select.innerHTML += `<option value="${r.id_repartidor}">${r.nombre} (Admin: ${r.admin_nombre})</option>`;
        });
    }
}

async function asignarRepartidor() {
    const id_repartidor = document.getElementById('selectRepartidor').value;
    if (!id_repartidor) { Api.alert('error', 'Error', 'Seleccione un repartidor.'); return; }

    const res = await Api.put('/pedidos/' + idPedido + '/repartidor', { id_repartidor: parseInt(id_repartidor) });
    if (res.success) {
        Api.alert('success', 'Asignado', res.message);
        setTimeout(() => location.reload(), 1500);
    } else {
        Api.alert('error', 'Error', res.message);
    }
}

async function cambiarEstado(estado) {
    const mensajes = {
        'PAGADO': 'Se descontará stock de los productos.',
        'EN_REPARTO': 'El pedido pasará a estado de reparto.',
        'ENTREGADO': 'Se marcará como entregado al cliente.',
        'CANCELADO': 'El pedido quedará cancelado permanentemente.'
    };
    const confirmed = await Api.confirm(`¿Cambiar a ${estado}?`, mensajes[estado] || '');
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
