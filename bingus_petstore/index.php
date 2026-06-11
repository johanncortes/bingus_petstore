<?php
/**
 * ============================================
 * PUNTO DE ENTRADA — Bingus Petstore v2.0
 * ============================================
 * Redirige al login o al panel correspondiente
 * si ya hay sesión activa.
 */

session_start();

// Si ya hay sesión activa, redirigir al panel
if (isset($_SESSION['usuario_rol'])) {
    if ($_SESSION['usuario_rol'] === 'ADMIN') {
        header("Location: views/admin/dashboard.php");
    } elseif ($_SESSION['usuario_rol'] === 'VENDEDOR') {
        header("Location: views/vendedor/pos.php");
    } elseif ($_SESSION['usuario_rol'] === 'CLIENTE') {
        header("Location: views/tienda/tienda.php");
    }
    exit();
}

// Sin sesión → login
header("Location: views/auth/login.php");
exit();
?>
