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
        <p style="font-size:18px; opacity:0.9;">Panel de Administración</p>
    </div>

    <div class="grid-3" id="dashboardGrid">
        <a href="/bingus_petstore/views/admin/productos/listar.php" class="card card-dashboard">
            <div class="card-icon">📦</div>
            <h3>Productos</h3>
            <p>Gestiona <span class="stat-number" id="statProductos">-</span> productos en inventario.</p>
            <span class="card-action">Ir a Productos →</span>
        </a>

        <a href="/bingus_petstore/views/admin/vendedores/listar.php" class="card card-dashboard">
            <div class="card-icon">👥</div>
            <h3>Vendedores</h3>
            <p>Tu equipo: <span class="stat-number" id="statVendedores">-</span> vendedores asignados.</p>
            <span class="card-action">Ir a Vendedores →</span>
        </a>

        <a href="/bingus_petstore/views/admin/pedidos/listar.php" class="card card-dashboard">
            <div class="card-icon">📋</div>
            <h3>Pedidos</h3>
            <p><span class="stat-number" id="statPedidos">-</span> ventas registradas en el historial.</p>
            <span class="card-action">Ver Pedidos →</span>
        </a>
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
        document.getElementById('statVendedores').textContent = stats.data.total_vendedores;
        document.getElementById('statPedidos').textContent = stats.data.total_pedidos;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
