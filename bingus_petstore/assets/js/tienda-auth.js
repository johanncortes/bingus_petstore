/**
 * ============================================
 * JS — Auth de Clientes — Bingus Petstore
 * ============================================
 * Maneja login y registro de clientes en la tienda virtual.
 */

// ========== TABS ==========
function switchTab(tab, el) {
    // Toggle visibility
    document.getElementById('tabLogin').style.display = tab === 'login' ? 'block' : 'none';
    document.getElementById('tabRegistro').style.display = tab === 'registro' ? 'block' : 'none';

    // Toggle active state
    document.querySelectorAll('.tienda-auth-tab').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');
}

// ========== LOGIN ==========
async function handleLoginCliente() {
    const btn = document.getElementById('btnLoginCliente');
    btn.disabled = true;
    btn.textContent = 'Ingresando...';

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;

    const res = await Api.post('/tienda/login', { email, password });

    if (res.success) {
        // Redirect to tienda
        window.location.href = '/bingus_petstore/views/tienda/tienda.php';
    } else {
        Api.alert('error', 'Error', res.message);
        btn.disabled = false;
        btn.textContent = 'Iniciar Sesión';
    }
}

// ========== REGISTRO ==========
async function handleRegistroCliente() {
    const btn = document.getElementById('btnRegistroCliente');
    btn.disabled = true;
    btn.textContent = 'Creando cuenta...';

    const nombre = document.getElementById('regNombre').value.trim();
    const rut = document.getElementById('regRut').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value;
    const telefono = document.getElementById('regTelefono').value.trim();
    const direccion = document.getElementById('regDireccion').value.trim();

    const res = await Api.post('/tienda/registro', {
        nombre, rut, email, password, telefono, direccion
    });

    if (res.success) {
        if (typeof Swal !== 'undefined') {
            await Swal.fire({
                icon: 'success',
                title: '¡Cuenta Creada!',
                text: 'Bienvenido a Bingus Petstore. Serás redirigido a la tienda.',
                confirmButtonColor: '#667eea',
                timer: 2000,
                timerProgressBar: true
            });
        }
        window.location.href = '/bingus_petstore/views/tienda/tienda.php';
    } else {
        Api.alert('error', 'Error', res.message);
        btn.disabled = false;
        btn.textContent = 'Crear Cuenta';
    }
}
