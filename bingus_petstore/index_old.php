<?php
session_start();

// 1. SEGURIDAD: Verificar si es Administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: auth_controlador.php");
    exit();
}

require_once 'config.php'; // Necesario para la conexión BD

// 2. DATOS REALES: Llamar al Procedimiento Almacenado con el ID del Admin
try {
    $pdo = conectarBD();
    
    // CAMBIO AQUÍ: Pasamos el ID del admin como parámetro (?)
    $stmt = $pdo->prepare("CALL sp_dashboard_stats(?)");
    $stmt->execute([$_SESSION['admin_id']]); 
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $compradores_total = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    
    if (!$stats) {
        $stats = ['total_productos' => 0, 'total_vendedores' => 0, 'total_pedidos' => 0];
    }
    $stats['total_compradores'] = ($compradores_total !== false) ? $compradores_total : 0;
} catch (Exception $e) {
    $stats = ['total_productos' => '-', 'total_vendedores' => '-', 'total_pedidos' => '-', 'total_compradores' => '-'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bingus Petstore - Sistema de Gestión</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px;
        }
    
        .container { 
            max-width: 1000px; 
            width: 100%; 
            margin-top: 40px; 
        }
    
        .banner-container {
            width: 100%;
            height: 180px; 
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            background: white;
        }
        .banner-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .header { text-align: center; color: white; margin-bottom: 40px; }
        .header h1 { font-size: 40px; margin-bottom: 5px; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); }
        .header p { font-size: 18px; opacity: 0.9; }
        
        .user-info { 
            position: absolute; 
            top: 15px; 
            right: 25px; 
            color: white; 
            font-weight: 600; 
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
            font-size: 15px;
        }
        .user-info a { color: #ffffffff; text-decoration: none; margin-left: 10px; border-bottom: 1px dotted #ffffffff; transition: 0.3s; }
        .user-info a:hover { color: #fff; border-bottom: 1px solid #fff; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px; }
        
        .card {
            background: white; border-radius: 12px; padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); transition: all 0.3s;
            text-decoration: none; color: inherit; text-align: center; display: block;
        }
        .card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); }
        
        .card-icon { font-size: 60px; margin-bottom: 20px; }
        .card h3 { color: #333; margin-bottom: 15px; font-size: 24px; }
        
        .card p { color: #666; font-size: 15px; line-height: 1.6; margin-bottom: 25px; }
        .stat-number { font-weight: bold; color: #667eea; font-size: 18px; }
        
        .card-action {
            display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 12px 30px; border-radius: 5px; font-weight: 600; font-size: 16px;
        }
        
        .footer { text-align: center; color: white; font-size: 14px; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="user-info">
        Hola, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?> 
        <a href="auth_controlador.php?action=logout">Cerrar Sesión</a>
    </div>

    <div class="container">
        <div class="banner-container">
            <img src="img/banner.png" alt="Bingus Banner" class="banner-img">
        </div>

        <div class="header">
            <h1>🐾 Bingus Petstore</h1>
            <p>Panel de Administración</p>
        </div>

        <div class="grid">
            <a href="producto_controlador.php?action=listar" class="card">
                <div class="card-icon">📦</div>
                <h3>Productos</h3>
                <p>Gestiona <span class="stat-number"><?php echo $stats['total_productos']; ?></span> productos en inventario.</p>
                <span class="card-action">Ir a Productos →</span>
            </a>

            <a href="vendedor_controlador.php?action=listar" class="card">
                <div class="card-icon">👥</div>
                <h3>Vendedores</h3>
                <p>Tu equipo: <span class="stat-number"><?php echo $stats['total_vendedores']; ?></span> vendedores asignados.</p>
                <span class="card-action">Ir a Vendedores →</span>
            </a>

            <a href="comprador_controlador.php?action=listar" class="card">
                <div class="card-icon">🧾</div>
                <h3>Compradores</h3>
                <p><span class="stat-number"><?php echo $stats['total_compradores']; ?></span> clientes registrados.</p>
                <span class="card-action">Ver Compradores →</span>
            </a>

            <a href="pedido_controlador.php?action=listar" class="card">
                <div class="card-icon">📋</div>
                <h3>Pedidos</h3>
                <p><span class="stat-number"><?php echo $stats['total_pedidos']; ?></span> ventas registradas en el historial.</p>
                <span class="card-action">Ver Pedidos →</span>
            </a>
        </div>

        <div class="footer">
            <p>🐾 Bingus Petstore - Sistema de Gestión © 2025</p>
        </div>
    </div>
</body>
</html>
