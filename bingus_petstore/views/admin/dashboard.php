<?php $page_title = 'Dashboard - Bingus Petstore'; $body_class = 'bg-dashboard'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="user-info">
    Hola, <span id="adminNombre">...</span>
    <a onclick="Api.logout()">Cerrar Sesión</a>
</div>

<div class="container" style="max-width:1000px; margin-top:40px;">
    <div class="banner">
        <img src="/bingus_petstore/assets/img/banner.png" alt="Bingus Banner">
    </div>

    <div style="text-align:center; color:white; margin-bottom:40px;">
        <h1 style="font-size:40px; text-shadow:0 2px 10px rgba(0,0,0,0.2);">🐾 Bingus Petstore</h1>
        <p style="font-size:18px; opacity:0.9;">Panel de Administración — Intranet</p>
    </div>

    <div class="grid-3" id="dashboardGrid">
        <a href="/bingus_petstore/views/admin/productos/listar.php" class="card card-dashboard">
            <div class="card-icon">📦</div>
            <h3>Productos</h3>
            <p>Gestiona <span class="stat-number" id="statProductos">-</span> productos en inventario.</p>
            <span class="card-action">Ir a Productos →</span>
        </a>

        <a href="/bingus_petstore/views/admin/repartidores/listar.php" class="card card-dashboard">
            <div class="card-icon">🚚</div>
            <h3>Repartidores</h3>
            <p>Tu equipo: <span class="stat-number" id="statRepartidores">-</span> repartidores asignados.</p>
            <span class="card-action">Ir a Repartidores →</span>
        </a>

        <a href="/bingus_petstore/views/admin/pedidos/listar.php" class="card card-dashboard">
            <div class="card-icon">📋</div>
            <h3>Pedidos</h3>
            <p><span class="stat-number" id="statPedidos">-</span> pedidos registrados en el sistema.</p>
            <span class="card-action">Ver Pedidos →</span>
        </a>
    </div>

    <!-- Indicadores de reparto -->
    <div class="grid-3" style="margin-top:20px;">
        <div class="card card-dashboard" style="cursor:default; text-align:center;">
            <div class="card-icon">⏳</div>
            <h3 style="font-size:28px;" id="statSinRepartidor">-</h3>
            <p>Pedidos sin repartidor</p>
        </div>
        <div class="card card-dashboard" style="cursor:default; text-align:center;">
            <div class="card-icon">🏍️</div>
            <h3 style="font-size:28px;" id="statEnReparto">-</h3>
            <p>En reparto</p>
        </div>
        <div class="card card-dashboard" style="cursor:default; text-align:center;">
            <div class="card-icon">🏪</div>
            <a href="/bingus_petstore/views/tienda/tienda.php" target="_blank" style="text-decoration:none; color:inherit;">
                <h3 style="font-size:16px;">Tienda Online</h3>
                <p>Abrir tienda →</p>
            </a>
        </div>
    </div>

    <div class="footer">
        <p>🐾 Bingus Petstore - Sistema de Gestión © 2026</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Verificar sesión
    const session = await Api.get('/auth/session');
    if (!session.success) {
        window.location.href = '/bingus_petstore/views/auth/login.php';
        return;
    }
    document.getElementById('adminNombre').textContent = session.data.nombre;

    // Cargar estadísticas
    const stats = await Api.get('/dashboard/stats');
    if (stats.success) {
        document.getElementById('statProductos').textContent = stats.data.total_productos;
        document.getElementById('statRepartidores').textContent = stats.data.total_repartidores;
        document.getElementById('statPedidos').textContent = stats.data.total_pedidos;
        document.getElementById('statSinRepartidor').textContent = stats.data.pedidos_sin_repartidor || 0;
        document.getElementById('statEnReparto').textContent = stats.data.pedidos_en_reparto || 0;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
