/**
 * ============================================
 * JS — Auth de Clientes — Bingus Petstore
 * ============================================
 * Maneja login y registro de clientes en la tienda virtual.
 * Incluye validación REGEX de RUT, email y teléfono.
 */

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar selects de región/comuna del registro
    poblarSelectRegion('regRegion', 'regComuna');

    // Configurar auto-formato y validación inline
    configurarAutoFormatoRut('regRut');
    configurarValidacionEmail('regEmail', true); // Email obligatorio en registro
    configurarAutoFormatoTelefono('regTelefono');
});

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

    // ========== VALIDACIÓN FRONTEND ==========
    const camposError = ['regRut', 'regEmail', 'regTelefono'];
    limpiarErrores(camposError);
    let hayErrores = false;

    const nombre = document.getElementById('regNombre').value.trim();
    const rut = document.getElementById('regRut').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value;
    const telefono = document.getElementById('regTelefono').value.trim();

    // Construir dirección desde los 3 campos
    const direccion = construirDireccion('regRegion', 'regComuna', 'regCalle');

    // Validar nombre
    if (!nombre) {
        Api.alert('error', 'Error', 'El nombre es obligatorio.');
        return;
    }

    // Validar RUT
    const resRut = validarRut(rut);
    if (!resRut.valido) {
        mostrarErrorCampo('regRut', resRut.mensaje);
        hayErrores = true;
    }

    // Validar Email (obligatorio en registro)
    const resEmail = validarEmailObligatorio(email);
    if (!resEmail.valido) {
        mostrarErrorCampo('regEmail', resEmail.mensaje);
        hayErrores = true;
    }

    // Validar Teléfono (opcional pero si se ingresa, debe ser válido)
    const resTel = validarTelefono(telefono);
    if (!resTel.valido) {
        mostrarErrorCampo('regTelefono', resTel.mensaje);
        hayErrores = true;
    }

    // Validar contraseña
    if (password.length < 6) {
        Api.alert('error', 'Error', 'La contraseña debe tener al menos 6 caracteres.');
        return;
    }

    if (hayErrores) return;

    btn.disabled = true;
    btn.textContent = 'Creando cuenta...';

    const res = await Api.post('/tienda/registro', {
        nombre,
        rut: limpiarRut(rut),
        email,
        password,
        telefono: telefono || null,
        direccion: direccion || null
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
