<?php
/**
 * ============================================
 * PUNTO DE ENTRADA — Bingus Petstore v3.0
 * ============================================
 * Redirige al login de admin, a la tienda (clientes),
 * o al panel correspondiente si ya hay sesión activa.
 */

session_start();

// Si ya hay sesión activa, redirigir al panel correspondiente
if (isset($_SESSION['usuario_rol'])) {
    if ($_SESSION['usuario_rol'] === 'ADMIN') {
        header("Location: views/admin/dashboard.php");
        exit();
    } elseif ($_SESSION['usuario_rol'] === 'CLIENTE') {
        header("Location: views/tienda/tienda.php");
        exit();
    }
}

// Sin sesión → redirigir a la tienda online (página principal pública)
header("Location: views/tienda/tienda.php");
exit();
?>
