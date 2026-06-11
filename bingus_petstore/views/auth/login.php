<?php $page_title = 'Login - Bingus Petstore'; $body_class = 'bg-gradient'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="login-container">
    <div style="text-align:center; margin-bottom:20px;">
        <span style="font-size:50px;">🐾</span>
        <h2>Bingus Petstore</h2>
        <p style="color:#888; font-size:14px;">Panel de Gestión — Inicia sesión para continuar</p>
    </div>

    <!-- Selector de Rol -->
    <div class="role-selector">
        <div class="role-btn active" data-rol="ADMIN" onclick="selectRol(this)">🔑 Administrador</div>
        <div class="role-btn" data-rol="VENDEDOR" onclick="selectRol(this)">🛒 Vendedor</div>
    </div>

    <form id="loginForm" onsubmit="handleLogin(event)">
        <input type="hidden" id="rol" value="ADMIN">
        
        <div class="form-group">
            <label id="labelUsuario">Usuario</label>
            <input type="text" id="usuario" class="form-control" placeholder="Tu usuario" required>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" id="password" class="form-control" placeholder="Tu contraseña" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnLogin" style="margin-top:20px;">
            Iniciar Sesión
        </button>
    </form>
</div>

<script>
function selectRol(el) {
    document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('rol').value = el.dataset.rol;
    
    // Cambiar label según rol
    const label = document.getElementById('labelUsuario');
    const input = document.getElementById('usuario');
    if (el.dataset.rol === 'VENDEDOR') {
        label.textContent = 'Email';
        input.type = 'email';
        input.placeholder = 'tu.email@bingus.cl';
    } else {
        label.textContent = 'Usuario';
        input.type = 'text';
        input.placeholder = 'Tu usuario';
    }
}

async function handleLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.textContent = 'Ingresando...';

    const res = await Api.post('/auth/login', {
        usuario: document.getElementById('usuario').value,
        password: document.getElementById('password').value,
        rol: document.getElementById('rol').value
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
