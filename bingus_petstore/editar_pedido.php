<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Pedido - Bingus Petstore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px;
        }
        .container {
            background: white; border-radius: 10px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px; max-width: 700px; width: 100%;
        }
        h1 { color: #333; margin-bottom: 5px; font-size: 28px; }
        .subtitle { color: #666; margin-bottom: 25px; font-size: 14px; }
        
        .section-title { font-size: 14px; color: #667eea; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #f0f0f0; padding-bottom: 5px; margin-bottom: 15px; margin-top: 20px; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 10px; }
        .info-item label { display: block; font-size: 12px; color: #888; margin-bottom: 3px; font-weight: bold; }
        .info-item div { font-size: 15px; color: #333; padding: 8px 12px; background: #f9f9f9; border-radius: 5px; border: 1px solid #eee; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        .details-table th { text-align: left; background: #f4f6f9; padding: 8px; color: #555; border-bottom: 2px solid #ddd; }
        .details-table td { padding: 10px 8px; border-bottom: 1px solid #eee; color: #333; }
        .text-right { text-align: right; }

        .estado-box { background: #eef2f7; padding: 20px; border-radius: 8px; border-left: 5px solid #3498db; margin-top: 20px; }
        .estado-box.locked { border-left-color: #95a5a6; background: #f8f9fa; opacity: 0.8; }
        
        select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; margin-top: 5px; }
        /* Estilo para input deshabilitado */
        input:disabled { background: #e9ecef; color: #6c757d; cursor: not-allowed; border: 1px solid #ced4da; }
        
        .total-row { font-size: 18px; font-weight: bold; text-align: right; margin-top: 15px; color: #2c3e50; }

        .button-group { display: flex; gap: 10px; margin-top: 25px; }
        button, a.btn { flex: 1; padding: 12px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; transition: 0.3s; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; transform: translateY(-2px); }
        .btn-secondary { background: #ecf0f1; color: #333; }
        .btn-secondary:hover { background: #bdc3c7; }
        
        .msg-bloqueo { color: #e74c3c; font-weight: bold; font-size: 13px; margin-top: 10px; display: block; }
    </style>
</head>
<body>
    <div class="container">
        <?php 
            // DEFINIR SI ES EDITABLE
            $es_editable = ($pedido['estado'] === 'PENDIENTE'); 
        ?>

        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>📦 Pedido #<?php echo $pedido['id_pedido']; ?></h1>
                <p class="subtitle">Fecha: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></p>
            </div>
            <div style="padding:5px 15px; background:#333; color:white; border-radius:20px; font-weight:bold; font-size:14px;">
                <?php echo $pedido['estado']; ?>
            </div>
        </div>

        <div class="section-title">Información General</div>
        <div class="info-grid">
            <div class="info-item">
                <label>👤 Cliente</label>
                <div><?php echo htmlspecialchars($pedido['cliente_nombre']); ?> <small>(<?php echo htmlspecialchars($pedido['cliente_rut']); ?>)</small></div>
            </div>
            <div class="info-item">
                <label>💼 Vendedor</label>
                <div><?php echo htmlspecialchars($pedido['vendedor_nombre']); ?></div>
            </div>
        </div>

        <div class="section-title">Contenido del Pedido</div>
        <?php if (!empty($detalles)): ?>
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Cant.</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['producto_nombre']); ?></td>
                            <td class="text-right">$<?php echo number_format($d['precio_unitario'], 0, ',', '.'); ?></td>
                            <td class="text-right">x<?php echo $d['cantidad']; ?></td>
                            <td class="text-right" style="font-weight:bold;">$<?php echo number_format($d['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color:#999; font-style:italic;">No hay detalles disponibles.</p>
        <?php endif; ?>

        <div class="total-row">
            Total a Pagar: $ <?php echo number_format($pedido['total'], 0, ',', '.'); ?>
        </div>

        <form action="?action=actualizar" method="POST">
            <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">

            <div class="estado-box <?php echo !$es_editable ? 'locked' : ''; ?>">
                <label style="font-weight:bold; color: #3498db;">📝 Actualizar Estado del Pedido</label>
                
                <?php if ($es_editable): ?>
                    <select name="estado">
                        <option value="PENDIENTE" selected>⏳ PENDIENTE</option>
                        <option value="PAGADO">✅ PAGADO</option>
                        <option value="CANCELADO">🚫 CANCELADO</option>
                    </select>
                <?php else: ?>
                    <select disabled style="background:#ddd; cursor:not-allowed;">
                        <option><?php echo $pedido['estado']; ?></option>
                    </select>
                    <span class="msg-bloqueo">🔒 Este pedido ya está finalizado. No se permiten cambios.</span>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <?php if ($es_editable): ?>
                    <button type="submit" class="btn-primary">💾 Guardar Nuevo Estado</button>
                <?php endif; ?>
                <a href="?action=listar" class="btn btn-secondary">← Volver</a>
            </div>
        </form>
    </div>
</body>
</html>