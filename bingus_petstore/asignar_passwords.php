<?php
/**
 * ============================================
 * SCRIPT DE MIGRACIÓN: Asignar contraseñas a clientes existentes
 * ============================================
 * 
 * Ejecutar UNA SOLA VEZ desde el navegador.
 * URL: http://localhost/bingus_petstore/asignar_passwords.php
 * 
 * Asigna contraseñas en TEXTO PLANO (sin hash) a todos los 
 * clientes que actualmente tienen password = NULL.
 * 
 * IMPORTANTE: Eliminar este archivo después de usarlo.
 */

require_once __DIR__ . '/api/config/Database.php';

// ========================================
// CONFIGURACIÓN: Contraseña por cada cliente
// ========================================

$passwordsPorCliente = [
    // id_cliente => contraseña en texto plano
    1 => 'cliente123',    // Juan Pérez - juan.perez@example.com
    2 => 'cliente123',    // María López - maria.lopez@example.com
    3 => 'cliente123',    // Carlos Sánchez - carlos.sanchez@example.com
    4 => 'cliente123',    // Ana Torres - ana.torres@example.com
    5 => 'cliente123',    // Pedro Ramírez - pedro.ramirez@example.com
    7 => 'cliente123',    // Martina Cuello - martina_cuello@hotmail.com
    8 => 'cliente123',    // Maurice Tania - maurice@gmail.com
];

// ========================================
// EJECUCIÓN
// ========================================

try {
    $conn = Database::getInstance()->getConnection();
    
    echo "<h2>🔐 Asignación de Contraseñas a Clientes (Texto Plano)</h2>";
    echo "<hr>";
    
    $actualizados = 0;
    $stmtUpdate = $conn->prepare("UPDATE clientes SET password = ? WHERE id_cliente = ? AND password IS NULL");
    
    echo "<h3>Resultado:</h3><ul>";
    
    foreach ($passwordsPorCliente as $id => $password) {
        $stmtUpdate->execute([$password, $id]);
        
        if ($stmtUpdate->rowCount() > 0) {
            echo "<li style='color: green;'>✅ Cliente ID=$id → Contraseña: <b>$password</b></li>";
            $actualizados++;
        } else {
            $check = $conn->prepare("SELECT password FROM clientes WHERE id_cliente = ?");
            $check->execute([$id]);
            $row = $check->fetch();
            
            if ($row && $row['password']) {
                echo "<li style='color: orange;'>⚠️ Cliente ID=$id → Ya tenía contraseña, no se modificó.</li>";
            } else {
                echo "<li style='color: red;'>❌ Cliente ID=$id → No encontrado.</li>";
            }
        }
    }
    
    echo "</ul><hr>";
    echo "<p><b>Actualizados:</b> $actualizados</p>";
    
    if ($actualizados > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;'>";
        echo "<h4>✅ ¡Listo! Credenciales de acceso:</h4>";
        echo "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #333; color: white;'><th>Email</th><th>Contraseña</th></tr>";
        
        foreach ($passwordsPorCliente as $id => $pass) {
            $stmt = $conn->prepare("SELECT email FROM clientes WHERE id_cliente = ?");
            $stmt->execute([$id]);
            $c = $stmt->fetch();
            if ($c) {
                echo "<tr><td>{$c['email']}</td><td><code>$pass</code></td></tr>";
            }
        }
        echo "</table></div>";
    }
    
    echo "<br><p style='color: red; font-weight: bold;'>⚠️ Elimina este archivo después de usarlo.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
