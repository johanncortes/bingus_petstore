<?php 
session_start();
$page_title = 'Tienda - Bingus Petstore'; 
$page_description = 'Tienda virtual de productos para mascotas. Encuentra todo lo que tu mascota necesita.';
$body_class = 'tienda-body';
$cliente_logueado = isset($_SESSION['cliente_id']);
$cliente_nombre = $_SESSION['cliente_nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <link rel="icon" href="/bingus_petstore/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/bingus_petstore/assets/css/styles.css">
    <link rel="stylesheet" href="/bingus_petstore/assets/css/tienda.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="<?php echo $body_class; ?>">

    <!-- ====== NAVBAR ====== -->
    <nav class="tienda-nav" id="tiendaNav">
        <div class="tienda-nav-inner">
            <a href="/bingus_petstore/views/tienda/tienda.php" class="tienda-logo">
                🐾 <span>Bingus Petstore</span>
            </a>

            <div class="tienda-search">
                <input type="text" id="searchInput" placeholder="Buscar productos..." autocomplete="off">
            </div>

            <div class="tienda-nav-actions">
                <?php if ($cliente_logueado): ?>
                    <span class="tienda-user-name" id="navClienteNombre">👤 <?php echo htmlspecialchars($cliente_nombre); ?></span>
                    <button class="btn-login-link btn-logout-link" onclick="logoutCliente()">Cerrar Sesión</button>
                <?php else: ?>
                    <a href="/bingus_petstore/views/tienda/login.php" class="btn-login-link" id="navLoginLink">👤 Mi Cuenta</a>
                <?php endif; ?>
                <button class="btn-carrito" onclick="abrirCarrito()" id="btnCarritoNav">
                    🛒 Carrito
                    <span class="carrito-badge" id="carritoBadge" style="display:none;">0</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- ====== HERO BANNER ====== -->
    <section class="tienda-hero">
        <h1>🐕 Todo para tu Mascota 🐈</h1>
        <p>Alimentos, snacks, accesorios y más. Encuentra los mejores productos para consentir a tu compañero peludo.</p>
    </section>

    <!-- ====== FILTROS POR CATEGORÍA ====== -->
    <div class="tienda-filtros" id="filtrosContainer">
        <!-- Se llena dinámicamente con JS -->
    </div>

    <!-- ====== GRID DE PRODUCTOS ====== -->
    <div class="tienda-grid" id="tiendaGrid">
        <!-- Se llena dinámicamente con JS -->
    </div>

    <!-- ====== SIDEBAR CARRITO ====== -->
    <div class="carrito-overlay" id="carritoOverlay" onclick="cerrarCarrito()"></div>
    <aside class="carrito-sidebar" id="carritoSidebar">
        <div class="carrito-header">
            <h2>🛒 Tu Carrito</h2>
            <button class="carrito-close" onclick="cerrarCarrito()">&times;</button>
        </div>
        
        <div class="carrito-items" id="carritoItemsContainer">
            <div class="carrito-vacio">
                <div class="emoji">🛒</div>
                <p>Tu carrito está vacío</p>
                <p style="font-size:13px; margin-top:5px;">¡Explora nuestros productos!</p>
            </div>
        </div>

        <div class="carrito-footer" id="carritoFooter" style="display:none;">
            <div class="carrito-total">
                <span class="carrito-total-label">Total</span>
                <span class="carrito-total-valor" id="carritoTotalValor">$0</span>
            </div>
            <button class="btn-checkout" onclick="abrirCheckout()">
                Ir al Pago →
            </button>
            <button class="btn-vaciar" onclick="vaciarCarrito()">
                🗑️ Vaciar Carrito
            </button>
        </div>
    </aside>

    <!-- ====== MODAL CHECKOUT ====== -->
    <div class="checkout-overlay" id="checkoutOverlay">
        <div class="checkout-modal">
            <h2>📦 Finalizar Compra</h2>
            <p class="checkout-subtitle">Ingresa tus datos para procesar el pedido.</p>

            <form id="checkoutForm" onsubmit="event.preventDefault(); confirmarPedido();">
                <div class="checkout-section">
                    <h3>👤 Datos del Comprador</h3>
                    <div class="checkout-form-grid">
                        <div class="checkout-form-group">
                            <label for="chkNombre">Nombre Completo *</label>
                            <input type="text" id="chkNombre" required placeholder="Ej: Juan Pérez">
                        </div>
                        <div class="checkout-form-group">
                            <label for="chkRut">RUT *</label>
                            <input type="text" id="chkRut" required placeholder="Ej: 12345678-9">
                        </div>
                        <div class="checkout-form-group">
                            <label for="chkEmail">Email</label>
                            <input type="email" id="chkEmail" placeholder="tu@email.com">
                        </div>
                        <div class="checkout-form-group">
                            <label for="chkTelefono">Teléfono</label>
                            <input type="text" id="chkTelefono" placeholder="+56 9 XXXX XXXX">
                        </div>
                        <div class="checkout-form-group full">
                            <label for="chkDireccion">Dirección de Entrega</label>
                            <input type="text" id="chkDireccion" placeholder="Calle, número, comuna">
                        </div>
                    </div>
                </div>

                <div class="checkout-section">
                    <h3>🛍️ Resumen del Pedido</h3>
                    <div class="checkout-resumen" id="checkoutResumen">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>

                <div class="checkout-buttons">
                    <button type="submit" class="btn-confirmar" id="btnConfirmarPedido">
                        ✅ Confirmar Pedido
                    </button>
                    <button type="button" class="btn-cancelar-checkout" onclick="cerrarCheckout()">
                        Volver
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====== TOAST CONTAINER ====== -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ====== SCRIPTS ====== -->
    <script src="/bingus_petstore/assets/js/api.js"></script>
    <script src="/bingus_petstore/assets/js/tienda.js"></script>

</body>
</html>
