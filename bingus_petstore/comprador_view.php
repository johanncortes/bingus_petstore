<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Compradores - Bingus Petstore</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }

        .header { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 30px; background: white; padding: 25px; 
            border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08); 
        }
        .header h1 { color: #333; font-size: 28px; margin: 0; }
        .header-buttons { display: flex; gap: 10px; align-items: center; }

        .btn { padding: 12px 25px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-volver { background: #95a5a6; color: white; }
        .btn-volver:hover { background: #7f8c8d; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); overflow: hidden; transition: transform 0.3s; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-5px); }

        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 18px 20px; display: flex; align-items: center; gap: 12px; }
        .avatar { font-size: 22px; background: rgba(255,255,255,0.2); width: 44px; height: 44px; border-radius: 50%; display: flex; justify-content: center; align-items: center; }
        .card-body { padding: 20px; }

        .info-row { margin-bottom: 8px; font-size: 14px; color: #555; }
        .stats { display: flex; gap: 10px; margin-top: 15px; }
        .stat-box { flex: 1; background: #f7f8fb; border-radius: 8px; padding: 10px; text-align: center; }
        .stat-title { font-size: 11px; text-transform: uppercase; color: #888; margin-bottom: 4px; }
        .stat-value { font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧾 Compradores</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn btn-volver">🏠 Volver al Menú</a>
            </div>
        </div>

        <?php if (empty($compradores)): ?>
            <div style="text-align:center; padding:50px; background:white; border-radius:10px;">
                <h2 style="color:#666;">No hay compradores registrados</h2>
                <p style="color:#999;">Los clientes creados por ventas aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($compradores as $c): ?>
                <?php
                    $ultima_compra = !empty($c['ultima_compra'])
                        ? date('d/m/Y', strtotime($c['ultima_compra']))
                        : 'Sin compras';
                ?>
                <div class="card">
                    <div class="card-header">
                        <div class="avatar">👤</div>
                        <div>
                            <div style="font-weight:bold; font-size:18px;"><?php echo htmlspecialchars($c['nombre']); ?></div>
                            <div style="font-size:12px; opacity:0.9;">ID: #<?php echo $c['id_cliente']; ?></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-row"><strong>🆔 RUT:</strong> <?php echo htmlspecialchars($c['rut'] ?? 'S/RUT'); ?></div>
                        <div class="info-row"><strong>📧 Email:</strong> <?php echo htmlspecialchars($c['email'] ?? 'S/E'); ?></div>
                        <div class="info-row"><strong>📞 Teléfono:</strong> <?php echo htmlspecialchars($c['telefono'] ?? 'S/T'); ?></div>
                        <div class="info-row"><strong>📍 Dirección:</strong> <?php echo htmlspecialchars($c['direccion'] ?? 'S/D'); ?></div>

                        <div class="stats">
                            <div class="stat-box">
                                <div class="stat-title">Pedidos</div>
                                <div class="stat-value"><?php echo (int) $c['total_pedidos']; ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-title">Total</div>
                                <div class="stat-value">$<?php echo number_format($c['total_gastado'], 0, ',', '.'); ?></div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-title">Última</div>
                                <div class="stat-value"><?php echo $ultima_compra; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
