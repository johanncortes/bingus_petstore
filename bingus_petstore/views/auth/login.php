<?php $page_title = 'Login - Bingus Petstore'; $body_class = 'bg-gradient'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="login-container">
    <div style="text-align:center; margin-bottom:20px;">
        <span style="font-size:50px;">🐾</span>
        <h2>Bingus Petstore</h2>
        <p style="color:#888; font-size:14px;">Intranet de Administración — Inicia sesión para continuar</p>
    </div>

    <form id="loginForm" onsubmit="handleLogin(event)">
        <div class="form-group">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" class="form-control" placeholder="Tu usuario de administrador" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" class="form-control" placeholder="Tu contraseña" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnLogin" style="margin-top:20px;">
            Iniciar Sesión
        </button>
    </form>

    <div style="text-align:center; margin-top:20px;">
        <a href="/bingus_petstore/views/tienda/tienda.php" style="color:#888; font-size:13px; text-decoration:none;">
            ← Ir a la Tienda Online
        </a>
    </div>
</div>

<script>
async function handleLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.textContent = 'Ingresando...';

    const res = await Api.post('/auth/login', {
        usuario: document.getElementById('usuario').value,
        password: document.getElementById('password').value
    });

    if (res.success) {
        window.location.href = '/bingus_petstore/' + res.data.redirect;
    } else {
        Api.alert('error', 'Acceso Denegado', res.message);
        btn.disabled = false;
        btn.textContent = 'Iniciar Sesión';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
