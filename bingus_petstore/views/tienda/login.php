<?php 
$page_title = 'Mi Cuenta - Bingus Petstore'; 
$page_description = 'Inicia sesión o crea tu cuenta para comprar en Bingus Petstore.';
$body_class = 'tienda-body';

// Si ya hay sesión de cliente, redirigir a la tienda
session_start();
if (isset($_SESSION['cliente_id'])) {
    header("Location: /bingus_petstore/views/tienda/tienda.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <link rel="icon" href="/bingus_petstore/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/bingus_petstore/assets/css/styles.css?v=3.2">
    <link rel="stylesheet" href="/bingus_petstore/assets/css/tienda.css?v=3.2">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="<?php echo $body_class; ?>">

    <!-- ====== NAVBAR ====== -->
    <nav class="tienda-nav">
        <div class="tienda-nav-inner">
            <a href="/bingus_petstore/views/tienda/tienda.php" class="tienda-logo">
                🐾 <span>Bingus Petstore</span>
            </a>
            <div class="tienda-nav-actions">
                <a href="/bingus_petstore/views/tienda/tienda.php" class="btn-login-link">← Volver a la Tienda</a>
            </div>
        </div>
    </nav>

    <!-- ====== AUTH CONTAINER ====== -->
    <div class="tienda-auth-container">
        <div class="tienda-auth-card">
            <div class="tienda-auth-header">
                <span style="font-size:40px;">🐾</span>
                <h1>Mi Cuenta</h1>
                <p>Inicia sesión o crea tu cuenta para una mejor experiencia de compra.</p>
            </div>

            <!-- TABS -->
            <div class="tienda-auth-tabs">
                <button class="tienda-auth-tab active" data-tab="login" onclick="switchTab('login', this)">Iniciar Sesión</button>
                <button class="tienda-auth-tab" data-tab="registro" onclick="switchTab('registro', this)">Crear Cuenta</button>
            </div>

            <!-- LOGIN FORM -->
            <div class="tienda-auth-form" id="tabLogin">
                <form id="loginClienteForm" onsubmit="event.preventDefault(); handleLoginCliente();">
                    <div class="form-group">
                        <label for="loginEmail">Email</label>
                        <input type="email" id="loginEmail" class="form-control" placeholder="tu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Contraseña</label>
                        <input type="password" id="loginPassword" class="form-control" placeholder="Tu contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnLoginCliente" style="margin-top:10px;">
                        Iniciar Sesión
                    </button>
                </form>
            </div>

            <!-- REGISTRO FORM -->
            <div class="tienda-auth-form" id="tabRegistro" style="display:none;">
                <form id="registroClienteForm" onsubmit="event.preventDefault(); handleRegistroCliente();">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="regNombre">Nombre Completo *</label>
                            <input type="text" id="regNombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                        </div>
                        <div class="form-group">
                            <label for="regRut">RUT *</label>
                            <input type="text" id="regRut" class="form-control" placeholder="Ej: 12.345.678-9" required maxlength="12">
                            <span class="field-error" id="regRutError"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="regEmail">Email *</label>
                        <input type="email" id="regEmail" class="form-control" placeholder="tu@email.com" required>
                        <span class="field-error" id="regEmailError"></span>
                    </div>
                    <div class="form-group">
                        <label for="regPassword">Contraseña *</label>
                        <input type="password" id="regPassword" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="regTelefono">Teléfono</label>
                            <input type="text" id="regTelefono" class="form-control" placeholder="+56 9 XXXX XXXX" maxlength="17">
                            <span class="field-error" id="regTelefonoError"></span>
                        </div>
                        <div class="form-group">
                            <label for="regRegion">Región de Entrega *</label>
                            <select id="regRegion" class="form-control" required>
                                <option value="">-- Selecciona una región --</option>
                            </select>
                            <span class="field-error" id="regRegionError"></span>
                        </div>
                        <div class="form-group">
                            <label for="regComuna">Comuna *</label>
                            <select id="regComuna" class="form-control" required>
                                <option value="">-- Primero selecciona región --</option>
                            </select>
                            <span class="field-error" id="regComunaError"></span>
                        </div>
                        <div class="form-group">
                            <label for="regCalle">Calle y Número *</label>
                            <input type="text" id="regCalle" class="form-control" placeholder="Ej: Av. Los Perros 123" required>
                            <span class="field-error" id="regCalleError"></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnRegistroCliente" style="margin-top:10px;">
                        Crear Cuenta
                    </button>
                </form>
            </div>

            <div class="tienda-auth-footer">
                <p>Al crear una cuenta, podrás hacer checkout más rápido y ver tu historial de pedidos.</p>
            </div>
        </div>
    </div>

    <!-- ====== SCRIPTS ====== -->
    <script src="/bingus_petstore/assets/js/api.js?v=3.2"></script>
    <script src="/bingus_petstore/assets/js/comunas-chile.js?v=3.2"></script>
    <script src="/bingus_petstore/assets/js/validaciones.js?v=3.2"></script>
    <script src="/bingus_petstore/assets/js/tienda-auth.js?v=3.2"></script>

</body>
</html>
