/**
 * ============================================
 * JS — Tienda Virtual Bingus Petstore
 * ============================================
 * Catálogo, carrito (localStorage) y checkout.
 * Incluye controles de cantidad en tarjetas y
 * soporte para sesión de cliente.
 */

// ========== ESTADO ==========
let catalogoDB = [];
let categoriasDB = [];
let filtroCategoria = null;
let filtroBusqueda = '';
let clienteSesion = null; // datos del cliente si hay sesión

// ========== CARRITO EN LOCALSTORAGE ==========
function getCarrito() {
    try {
        return JSON.parse(localStorage.getItem('bingus_carrito')) || [];
    } catch {
        return [];
    }
}

function setCarrito(carrito) {
    localStorage.setItem('bingus_carrito', JSON.stringify(carrito));
}

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', async () => {
    await verificarSesionCliente();
    await cargarCategorias();
    await cargarCatalogo();
    renderBadgeCarrito();

    // Event listener para búsqueda
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filtroBusqueda = e.target.value.toLowerCase().trim();
            renderCatalogo();
        });
    }
});

// ========== SESIÓN DE CLIENTE ==========
async function verificarSesionCliente() {
    try {
        const res = await Api.get('/tienda/session');
        if (res.success) {
            clienteSesion = res.data;
        }
    } catch {
        clienteSesion = null;
    }
}

async function logoutCliente() {
    await Api.post('/tienda/logout', {});
    clienteSesion = null;
    window.location.reload();
}

// ========== CARGAR DATOS ==========
async function cargarCatalogo() {
    mostrarSkeletons();
    const res = await Api.get('/tienda/catalogo');
    if (res.success) {
        catalogoDB = res.data;
        renderCatalogo();
    } else {
        document.getElementById('tiendaGrid').innerHTML = `
            <div class="tienda-empty">
                <div class="emoji">❌</div>
                <h3>Error al cargar productos</h3>
                <p>${res.message}</p>
            </div>`;
    }
}

async function cargarCategorias() {
    const res = await Api.get('/tienda/categorias');
    if (res.success) {
        categoriasDB = res.data;
        renderFiltros();
    }
}

// ========== RENDER CATÁLOGO ==========
function renderCatalogo() {
    const grid = document.getElementById('tiendaGrid');
    const carrito = getCarrito();
    
    // Filtrar productos
    let productos = catalogoDB.filter(p => {
        const matchCat = !filtroCategoria || p.id_categoria == filtroCategoria;
        const matchBus = !filtroBusqueda || 
            p.nombre.toLowerCase().includes(filtroBusqueda) || 
            p.descripcion?.toLowerCase().includes(filtroBusqueda) ||
            p.categoria_nombre.toLowerCase().includes(filtroBusqueda);
        return matchCat && matchBus;
    });

    if (productos.length === 0) {
        grid.innerHTML = `
            <div class="tienda-empty">
                <div class="emoji">🔍</div>
                <h3>No se encontraron productos</h3>
                <p>Intenta con otra categoría o término de búsqueda.</p>
            </div>`;
        return;
    }

    grid.innerHTML = productos.map(p => {
        const precioFmt = Number(p.precio).toLocaleString('es-CL');
        const stockClass = p.stock <= 10 ? 'stock-bajo' : 'stock-ok';
        const stockLabel = p.stock <= 10 ? `¡Últimas ${p.stock}!` : `${p.stock} disponibles`;
        
        const imgHtml = p.imagen 
            ? `<img src="/bingus_petstore/uploads/productos/${p.imagen}" alt="${p.nombre}" loading="lazy">`
            : `<span class="no-img">🐾</span>`;

        // Check if product is in cart
        const enCarrito = carrito.find(i => i.id_producto == p.id_producto);
        const cantidadEnCarrito = enCarrito ? enCarrito.cantidad : 0;

        // Build action button/control
        let accionHtml;
        if (cantidadEnCarrito > 0) {
            accionHtml = `
                <div class="qty-control" id="qty-${p.id_producto}">
                    <button class="qty-btn qty-minus" onclick="cambiarCantidadTarjeta(${p.id_producto}, -1)" title="Quitar uno">−</button>
                    <span class="qty-value">${cantidadEnCarrito}</span>
                    <button class="qty-btn qty-plus" onclick="cambiarCantidadTarjeta(${p.id_producto}, 1)" title="Agregar uno" ${cantidadEnCarrito >= p.stock ? 'disabled' : ''}>+</button>
                </div>`;
        } else {
            accionHtml = `
                <button class="btn-agregar" onclick="agregarAlCarrito(${p.id_producto})" title="Agregar al carrito">
                    <span class="btn-agregar-icon">🛒</span> Agregar
                </button>`;
        }

        return `
        <div class="tienda-card ${cantidadEnCarrito > 0 ? 'in-cart' : ''}" id="card-${p.id_producto}">
            <div class="tienda-card-img">
                ${imgHtml}
                <span class="tienda-card-cat">${p.categoria_nombre}</span>
                <span class="tienda-card-stock ${stockClass}">${stockLabel}</span>
            </div>
            <div class="tienda-card-body">
                <div class="tienda-card-name">${p.nombre}</div>
                <div class="tienda-card-desc">${p.descripcion || 'Producto para tu mascota.'}</div>
                <div class="tienda-card-footer">
                    <div class="tienda-card-price">$${precioFmt} <small>CLP</small></div>
                    ${accionHtml}
                </div>
            </div>
        </div>`;
    }).join('');
}

// ========== RENDER FILTROS ==========
function renderFiltros() {
    const container = document.getElementById('filtrosContainer');
    if (!container) return;

    container.innerHTML = `
        <button class="filtro-btn active" onclick="filtrarPorCategoria(null, this)">Todos</button>
        ${categoriasDB.map(c => `
            <button class="filtro-btn" onclick="filtrarPorCategoria(${c.id_categoria}, this)">${c.nombre}</button>
        `).join('')}
    `;
}

function filtrarPorCategoria(catId, el) {
    filtroCategoria = catId;
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('active'));
    if (el) el.classList.add('active');
    renderCatalogo();
}

// ========== SKELETONS (Loading) ==========
function mostrarSkeletons() {
    const grid = document.getElementById('tiendaGrid');
    grid.innerHTML = Array(8).fill('').map(() => `
        <div class="skeleton-card">
            <div class="skeleton-img"></div>
            <div class="skeleton-body">
                <div class="skeleton-line short"></div>
                <div class="skeleton-line"></div>
                <div class="skeleton-line price"></div>
            </div>
        </div>
    `).join('');
}

// ========== CARRITO: AGREGAR ==========
function agregarAlCarrito(id_producto) {
    const producto = catalogoDB.find(p => p.id_producto == id_producto);
    if (!producto) return;

    let carrito = getCarrito();
    const existente = carrito.find(i => i.id_producto == id_producto);

    if (existente) {
        if (existente.cantidad + 1 > producto.stock) {
            mostrarToast(`Stock máximo alcanzado (${producto.stock})`, 'error');
            return;
        }
        existente.cantidad++;
        existente.subtotal = existente.cantidad * existente.precio;
    } else {
        carrito.push({
            id_producto: producto.id_producto,
            nombre: producto.nombre,
            precio: parseFloat(producto.precio),
            cantidad: 1,
            subtotal: parseFloat(producto.precio),
            imagen: producto.imagen,
            stock: producto.stock
        });
    }

    setCarrito(carrito);
    renderBadgeCarrito();
    renderCatalogo(); // Re-render to show qty controls

    mostrarToast(`${producto.nombre} agregado al carrito`, 'success');
}

// ========== CARRITO: CAMBIAR CANTIDAD DESDE TARJETA ==========
function cambiarCantidadTarjeta(id_producto, delta) {
    let carrito = getCarrito();
    const index = carrito.findIndex(i => i.id_producto == id_producto);
    if (index === -1) return;

    const item = carrito[index];
    const producto = catalogoDB.find(p => p.id_producto == id_producto);
    const nuevaCant = item.cantidad + delta;

    if (nuevaCant <= 0) {
        carrito.splice(index, 1);
        mostrarToast(`${item.nombre} eliminado del carrito`, 'success');
    } else if (producto && nuevaCant > producto.stock) {
        mostrarToast(`Stock máximo: ${producto.stock}`, 'error');
        return;
    } else {
        item.cantidad = nuevaCant;
        item.subtotal = item.cantidad * item.precio;
    }

    setCarrito(carrito);
    renderBadgeCarrito();
    renderCatalogo(); // Re-render to update qty display
}

// ========== CARRITO: RENDER BADGE ==========
function renderBadgeCarrito() {
    const carrito = getCarrito();
    const badge = document.getElementById('carritoBadge');
    const total = carrito.reduce((sum, i) => sum + i.cantidad, 0);
    
    if (badge) {
        badge.textContent = total;
        badge.style.display = total > 0 ? 'flex' : 'none';
    }
}

// ========== CARRITO: SIDEBAR ==========
function abrirCarrito() {
    renderCarritoSidebar();
    document.getElementById('carritoOverlay').classList.add('visible');
    document.getElementById('carritoSidebar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function cerrarCarrito() {
    document.getElementById('carritoOverlay').classList.remove('visible');
    document.getElementById('carritoSidebar').classList.remove('open');
    document.body.style.overflow = '';
}

function renderCarritoSidebar() {
    const carrito = getCarrito();
    const itemsContainer = document.getElementById('carritoItemsContainer');
    const footerEl = document.getElementById('carritoFooter');

    if (carrito.length === 0) {
        itemsContainer.innerHTML = `
            <div class="carrito-vacio">
                <div class="emoji">🛒</div>
                <p>Tu carrito está vacío</p>
                <p style="font-size:13px; margin-top:5px;">¡Explora nuestros productos!</p>
            </div>`;
        footerEl.style.display = 'none';
        return;
    }

    footerEl.style.display = 'block';

    itemsContainer.innerHTML = carrito.map((item, index) => {
        const imgHtml = item.imagen
            ? `<img src="/bingus_petstore/uploads/productos/${item.imagen}" alt="${item.nombre}">`
            : `<span style="font-size:28px;">🐾</span>`;

        return `
        <div class="carrito-item">
            <div class="carrito-item-img">${imgHtml}</div>
            <div class="carrito-item-info">
                <div class="carrito-item-name">${item.nombre}</div>
                <div class="carrito-item-price">$${item.subtotal.toLocaleString('es-CL')}</div>
            </div>
            <div class="carrito-item-actions">
                <button class="carrito-item-remove" onclick="eliminarDelCarrito(${index})" title="Eliminar">×</button>
                <div class="carrito-qty">
                    <button onclick="cambiarCantidad(${index}, -1)">−</button>
                    <span>${item.cantidad}</span>
                    <button onclick="cambiarCantidad(${index}, 1)">+</button>
                </div>
            </div>
        </div>`;
    }).join('');

    // Total
    const total = carrito.reduce((sum, i) => sum + i.subtotal, 0);
    document.getElementById('carritoTotalValor').textContent = '$' + total.toLocaleString('es-CL');
}

// ========== CARRITO: ACCIONES ==========
function cambiarCantidad(index, delta) {
    let carrito = getCarrito();
    const item = carrito[index];
    if (!item) return;

    const nuevaCant = item.cantidad + delta;
    if (nuevaCant <= 0) {
        carrito.splice(index, 1);
    } else if (nuevaCant > item.stock) {
        mostrarToast(`Stock máximo: ${item.stock}`, 'error');
        return;
    } else {
        item.cantidad = nuevaCant;
        item.subtotal = item.cantidad * item.precio;
    }

    setCarrito(carrito);
    renderCarritoSidebar();
    renderBadgeCarrito();
    renderCatalogo(); // Sync card qty controls
}

function eliminarDelCarrito(index) {
    let carrito = getCarrito();
    carrito.splice(index, 1);
    setCarrito(carrito);
    renderCarritoSidebar();
    renderBadgeCarrito();
    renderCatalogo(); // Sync card qty controls
}

function vaciarCarrito() {
    if (!confirm('¿Vaciar todo el carrito?')) return;
    setCarrito([]);
    renderCarritoSidebar();
    renderBadgeCarrito();
    renderCatalogo(); // Sync card qty controls
}

// ========== CHECKOUT ==========
function abrirCheckout() {
    const carrito = getCarrito();
    if (carrito.length === 0) return;

    cerrarCarrito();

    // Pre-fill form if client is logged in
    if (clienteSesion) {
        document.getElementById('chkNombre').value = clienteSesion.nombre || '';
        document.getElementById('chkRut').value = clienteSesion.rut || '';
        document.getElementById('chkEmail').value = clienteSesion.email || '';
        document.getElementById('chkTelefono').value = clienteSesion.telefono || '';
        document.getElementById('chkDireccion').value = clienteSesion.direccion || '';
    }

    // Render resumen
    const resumenHtml = carrito.map(item => `
        <div class="checkout-resumen-item">
            <span>${item.nombre} <span class="qty">×${item.cantidad}</span></span>
            <span>$${item.subtotal.toLocaleString('es-CL')}</span>
        </div>
    `).join('');

    const total = carrito.reduce((sum, i) => sum + i.subtotal, 0);

    document.getElementById('checkoutResumen').innerHTML = resumenHtml + `
        <div class="checkout-resumen-total">
            <span>Total</span>
            <span>$${total.toLocaleString('es-CL')}</span>
        </div>`;

    document.getElementById('checkoutOverlay').classList.add('visible');
    document.body.style.overflow = 'hidden';
}

function cerrarCheckout() {
    document.getElementById('checkoutOverlay').classList.remove('visible');
    document.body.style.overflow = '';
}

async function confirmarPedido() {
    const carrito = getCarrito();
    if (carrito.length === 0) return;

    const btnConfirmar = document.getElementById('btnConfirmarPedido');
    btnConfirmar.disabled = true;
    btnConfirmar.textContent = 'Procesando...';

    // Build request body
    const body = {
        items: carrito.map(i => ({
            id_producto: i.id_producto,
            cantidad: i.cantidad,
            precio: i.precio,
            subtotal: i.subtotal
        }))
    };

    // If not logged in, include client data from form
    if (!clienteSesion) {
        const nombre = document.getElementById('chkNombre').value.trim();
        const rut = document.getElementById('chkRut').value.trim();
        const email = document.getElementById('chkEmail').value.trim();
        const telefono = document.getElementById('chkTelefono').value.trim();
        const direccion = document.getElementById('chkDireccion').value.trim();

        if (!nombre || !rut) {
            mostrarToast('Nombre y RUT son obligatorios', 'error');
            btnConfirmar.disabled = false;
            btnConfirmar.textContent = '✅ Confirmar Pedido';
            return;
        }

        body.cliente = { nombre, rut, email, telefono, direccion };
    }

    const res = await Api.post('/tienda/checkout', body);

    if (res.success) {
        // Limpiar carrito
        setCarrito([]);
        renderBadgeCarrito();
        cerrarCheckout();

        // Limpiar formulario
        document.getElementById('checkoutForm').reset();

        // Recargar catálogo (stock pudo haber cambiado)
        await cargarCatalogo();

        // Mostrar éxito con SweetAlert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Pedido Registrado!',
                html: `
                    <p style="font-size:15px; color:#666;">Tu pedido <strong>#${res.data.id_pedido}</strong> fue creado exitosamente.</p>
                    <p style="font-size:13px; color:#999; margin-top:10px;">Estado: <strong>Pendiente de confirmación</strong></p>
                `,
                confirmButtonColor: '#667eea',
                confirmButtonText: 'Entendido'
            });
        } else {
            alert(res.message);
        }
    } else {
        mostrarToast(res.message, 'error');
    }

    btnConfirmar.disabled = false;
    btnConfirmar.textContent = '✅ Confirmar Pedido';
}

// ========== TOASTS ==========
function mostrarToast(mensaje, tipo = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.innerHTML = `${tipo === 'success' ? '✅' : '⚠️'} ${mensaje}`;
    container.appendChild(toast);

    setTimeout(() => toast.remove(), 3200);
}
