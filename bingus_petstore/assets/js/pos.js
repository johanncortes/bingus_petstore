/**
 * ============================================
 * JS — Punto de Venta (POS)
 * ============================================
 * Carrito en memoria (JavaScript), ventas via API.
 */

// ========== ESTADO LOCAL ==========
let carrito = [];
let productosDB = [];
let productoSeleccionado = null;

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', async () => {
    // Verificar sesión
    const session = await Api.get('/auth/session');
    if (!session.success || session.data.rol !== 'VENDEDOR') {
        window.location.href = '/bingus_petstore/views/auth/login.php';
        return;
    }
    document.getElementById('vendedorNombre').textContent = session.data.nombre;

    // Cargar productos y clientes
    await cargarProductos();
    await cargarClientes();
});

async function cargarProductos() {
    const res = await Api.get('/productos');
    if (res.success) {
        productosDB = res.data;
        const select = document.getElementById('selectProducto');
        select.innerHTML = '<option value="">🔍 Buscar producto...</option>' +
            productosDB.map(p => 
                `<option value="${p.id_producto}">${p.nombre} - Stock: ${p.stock}</option>`
            ).join('');
    }
}

async function cargarClientes() {
    const res = await Api.get('/clientes');
    if (res.success) {
        const select = document.getElementById('selectCliente');
        select.innerHTML = '<option value="">Seleccionar...</option>' +
            res.data.map(c => 
                `<option value="${c.id_cliente}">${c.nombre} (${c.rut})</option>`
            ).join('');
    }
}

// ========== VISTAS ==========
function cambiarVista(vista) {
    document.getElementById('vistaPos').style.display = vista === 'pos' ? 'block' : 'none';
    document.getElementById('vistaClientes').style.display = vista === 'clientes' ? 'block' : 'none';
    document.getElementById('navPos').className = 'nav-link' + (vista === 'pos' ? ' active' : '');
    document.getElementById('navClientes').className = 'nav-link' + (vista === 'clientes' ? ' active' : '');
}

// ========== CREAR CLIENTE ==========
async function crearCliente(e) {
    e.preventDefault();

    const res = await Api.post('/clientes', {
        nombre: document.getElementById('cliNombre').value,
        rut: document.getElementById('cliRut').value,
        email: document.getElementById('cliEmail').value,
        telefono: document.getElementById('cliTelefono').value,
        direccion: document.getElementById('cliDireccion').value
    });

    if (res.success) {
        Api.alert('success', 'Registrado', 'Cliente creado y seleccionado.');
        await cargarClientes();
        // Auto-seleccionar al nuevo cliente
        document.getElementById('selectCliente').value = res.data.id_cliente;
        cambiarVista('pos');
    } else {
        Api.alert('error', 'Error', res.message);
    }
}

// ========== VER PRODUCTO ==========
function verProducto() {
    const id = document.getElementById('selectProducto').value;
    if (!id) return;

    productoSeleccionado = productosDB.find(p => p.id_producto == id);
    if (!productoSeleccionado) return;

    const info = document.getElementById('productoInfo');
    info.style.display = 'block';
    
    document.getElementById('prodNombre').textContent = productoSeleccionado.nombre;
    document.getElementById('prodPrecio').textContent = '$' + Number(productoSeleccionado.precio).toLocaleString('es-CL');
    document.getElementById('prodStock').textContent = 'Stock disponible: ' + productoSeleccionado.stock;
    document.getElementById('prodCantidad').max = productoSeleccionado.stock;
    document.getElementById('prodCantidad').value = 1;

    // Imagen
    if (productoSeleccionado.imagen) {
        document.getElementById('prodImg').innerHTML = 
            `<img src="/bingus_petstore/uploads/productos/${productoSeleccionado.imagen}" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">`;
    } else {
        document.getElementById('prodImg').innerHTML = '📦';
    }
}

// ========== CARRITO ==========
function agregarAlCarrito() {
    if (!productoSeleccionado) return;

    const cantidad = parseInt(document.getElementById('prodCantidad').value);
    if (cantidad <= 0) return;

    // Verificar stock total (ya en carrito + nueva)
    const cantEnCarrito = carrito
        .filter(i => i.id_producto == productoSeleccionado.id_producto)
        .reduce((sum, i) => sum + i.cantidad, 0);

    if (cantEnCarrito + cantidad > productoSeleccionado.stock) {
        Api.alert('warning', 'Stock Insuficiente', 
            `Solo quedan ${productoSeleccionado.stock} unidades (${cantEnCarrito} ya en carrito).`);
        return;
    }

    carrito.push({
        id_producto: productoSeleccionado.id_producto,
        nombre: productoSeleccionado.nombre,
        precio: parseFloat(productoSeleccionado.precio),
        cantidad: cantidad,
        subtotal: parseFloat(productoSeleccionado.precio) * cantidad
    });

    renderCarrito();
    document.getElementById('productoInfo').style.display = 'none';
}

function eliminarItem(index) {
    carrito.splice(index, 1);
    renderCarrito();
}

function vaciarCarrito() {
    carrito = [];
    renderCarrito();
}

function renderCarrito() {
    const tbody = document.getElementById('carritoBody');
    const vacio = document.getElementById('carritoVacio');
    const tabla = document.getElementById('carritoTabla');
    const btnVaciar = document.getElementById('btnVaciar');
    const btnCobrar = document.getElementById('btnCobrar');

    if (carrito.length === 0) {
        vacio.style.display = 'block';
        tabla.style.display = 'none';
        btnVaciar.style.display = 'none';
        btnCobrar.disabled = true;
    } else {
        vacio.style.display = 'none';
        tabla.style.display = 'table';
        btnVaciar.style.display = 'inline';
        btnCobrar.disabled = false;

        tbody.innerHTML = carrito.map((item, i) => `
            <tr>
                <td>${item.nombre}</td>
                <td>x${item.cantidad}</td>
                <td>$${item.subtotal.toLocaleString('es-CL')}</td>
                <td style="text-align:right;">
                    <a href="#" onclick="eliminarItem(${i})" style="color:#e74c3c; font-weight:bold;">×</a>
                </td>
            </tr>
        `).join('');
    }

    // Total
    const total = carrito.reduce((sum, i) => sum + i.subtotal, 0);
    document.getElementById('totalCarrito').textContent = total.toLocaleString('es-CL');
}

// ========== COBRAR ==========
async function confirmarCobro() {
    if (carrito.length === 0) {
        Api.alert('warning', 'Carrito Vacío', 'Agrega productos antes de cobrar.');
        return;
    }

    const id_cliente = document.getElementById('selectCliente').value;
    if (!id_cliente) {
        Api.alert('warning', 'Falta Cliente', 'Selecciona un cliente.');
        return;
    }

    const confirmed = await Api.confirm('¿Confirmar Venta?', 'Se registrará el pedido en el sistema.');
    if (!confirmed) return;

    const estado = document.getElementById('estadoPago').value;

    const res = await Api.post('/pedidos', {
        id_cliente: parseInt(id_cliente),
        estado: estado,
        items: carrito.map(i => ({
            id_producto: i.id_producto,
            cantidad: i.cantidad,
            precio: i.precio,
            subtotal: i.subtotal
        }))
    });

    if (res.success) {
        Api.alert('success', 'Venta Registrada', res.message);
        carrito = [];
        renderCarrito();
        document.getElementById('selectCliente').value = '';
        // Recargar productos (stock actualizado)
        await cargarProductos();
    } else {
        Api.alert('error', 'Error en Venta', res.message);
    }
}
