<?php
ob_start();
session_start();
require_once 'config.php';

// --- 1. SEGURIDAD: Verificar rol de Vendedor ---
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'VENDEDOR') {
    header("Location: auth_controlador.php");
    exit();
}

$pdo = conectarBD();
$id_vendedor = $_SESSION['usuario_id'];
$nombre_vendedor = $_SESSION['usuario_nombre'];

// Inicializar variables de sesión del carrito
if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
if (!isset($_SESSION['pos_id_cliente'])) $_SESSION['pos_id_cliente'] = ''; 

$vista_actual = $_GET['vista'] ?? 'pos';

// ==========================================
//           LÓGICA: GESTIÓN DE CLIENTES
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_cliente') {
    $nombre = trim($_POST['nombre']);
    $rut = trim($_POST['rut']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    if (empty($nombre) || empty($rut)) {
        $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Nombre y RUT obligatorios.'];
    } else {
        try {
            // Verificar RUT duplicado
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE rut = ?");
            $stmt->execute([$rut]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'RUT Duplicado', 'texto' => "El RUT $rut ya existe."];
            } else {
                // Insertar Cliente
                $stmt = $pdo->prepare("INSERT INTO clientes (nombre, rut, email, telefono, direccion) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $rut, $email, $telefono, $direccion]);
                
                // Auto-seleccionar al nuevo cliente
                $_SESSION['pos_id_cliente'] = $pdo->lastInsertId();
                
                $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Registrado', 'texto' => "Cliente creado y seleccionado."];
                header("Location: index_vendedor.php?vista=pos");
                exit();
            }
        } catch (Exception $e) { 
            $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error BD', 'texto' => $e->getMessage()]; 
        }
    }
}

// ==========================================
//           LÓGICA: PUNTO DE VENTA (POS)
// ==========================================

// 1. FIJAR CLIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'fijar_cliente') {
    $_SESSION['pos_id_cliente'] = $_POST['id_cliente'];
    $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Cliente Fijado', 'texto' => 'El cliente ha sido seleccionado para la venta.'];
    header("Location: index_vendedor.php?vista=pos");
    exit();
}

// 2. BUSCAR PRODUCTO
$producto_encontrado = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'buscar') {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ? AND activo = 1");
    $stmt->execute([$_POST['id_producto_buscar']]);
    $producto_encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$producto_encontrado) $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'texto' => 'Producto no encontrado o inactivo.'];
}

// 3. AGREGAR AL CARRITO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_carrito') {
    $id_prod = $_POST['id_producto'];
    $cant = (int) $_POST['cantidad'];
    
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$id_prod]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prod) {
        // Calcular cantidad total (carrito + nueva solicitud)
        $cant_actual = 0;
        foreach ($_SESSION['carrito'] as $i) if ($i['id'] == $id_prod) $cant_actual += $i['cantidad'];

        if (($cant + $cant_actual) <= $prod['stock'] && $cant > 0) {
            $_SESSION['carrito'][] = [
                'id' => $prod['id_producto'],
                'nombre' => $prod['nombre'],
                'precio' => $prod['precio'],
                'cantidad' => $cant,
                'subtotal' => $prod['precio'] * $cant
            ];
            header("Location: index_vendedor.php?vista=pos");
            exit();
        } else {
            $_SESSION['alerta'] = ['tipo' => 'warning', 'titulo' => 'Stock Insuficiente', 'texto' => "Solo quedan {$prod['stock']} unidades."];
        }
    }
}

// 4. ELIMINAR ITEM / VACIAR
if (isset($_GET['eliminar_item'])) {
    unset($_SESSION['carrito'][$_GET['eliminar_item']]);
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    header("Location: index_vendedor.php?vista=pos");
    exit();
}
if (isset($_GET['vaciar_carrito'])) {
    $_SESSION['carrito'] = [];
    header("Location: index_vendedor.php?vista=pos");
    exit();
}

// 5. FINALIZAR VENTA (LÓGICA MODIFICADA DE STOCK)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'finalizar_venta') {
    if (empty($_SESSION['carrito'])) {
        $_SESSION['alerta'] = ['tipo' => 'warning', 'titulo' => 'Carrito Vacío', 'texto' => 'Agrega productos antes de cobrar.'];
    } elseif (empty($_POST['id_cliente'])) {
        $_SESSION['alerta'] = ['tipo' => 'warning', 'titulo' => 'Falta Cliente', 'texto' => 'Selecciona un cliente.'];
    } else {
        $estado_pago = $_POST['estado_pago'];

        try {
            $pdo->beginTransaction();
            $total = 0; foreach ($_SESSION['carrito'] as $i) $total += $i['subtotal'];
            
            // Insertar Cabecera del Pedido
            $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, id_vendedor, fecha, estado, total) VALUES (?, ?, NOW(), ?, ?)");
            $stmt->execute([$_POST['id_cliente'], $id_vendedor, $estado_pago, $total]);
            $id_pedido = $pdo->lastInsertId();

            // Insertar Detalles
            foreach ($_SESSION['carrito'] as $item) {
                // Verificar stock actual en BD
                $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id_producto = ?");
                $stmt->execute([$item['id']]);
                $stock_db = $stmt->fetchColumn();
                
                // Si es PAGADO y no hay stock -> ERROR
                if ($estado_pago === 'PAGADO' && $stock_db < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para: " . $item['nombre']);
                }

                // Guardar detalle
                $pdo->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$id_pedido, $item['id'], $item['cantidad'], $item['precio'], $item['subtotal']]);
                
                // --- SOLO DESCONTAR STOCK SI ES PAGADO ---
                if ($estado_pago === 'PAGADO') {
                    $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?")
                        ->execute([$item['cantidad'], $item['id']]);
                }
            }
            
            $pdo->commit();
            
            // Mensaje final según el caso
            $msg_extra = ($estado_pago === 'PAGADO') ? "Stock descontado." : "Guardado como pendiente (Stock intacto).";
            
            $_SESSION['carrito'] = [];
            unset($_SESSION['pos_id_cliente']);
            $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Venta Registrada', 'texto' => "Pedido #$id_pedido guardado. $msg_extra"];
            header("Location: index_vendedor.php?vista=pos");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error en Venta', 'texto' => $e->getMessage()];
        }
    }
}

// Carga de datos para la vista
$productos_db = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$clientes_db = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$total_carrito = 0; foreach ($_SESSION['carrito'] as $i) $total_carrito += $i['subtotal'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Vendedor - Bingus Petstore</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        .container { width: 100%; max-width: 1100px; }
        
        .navbar { background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .nav-link { padding: 10px 20px; text-decoration: none; color: #555; font-weight: bold; border-radius: 8px; transition: 0.3s; }
        .nav-link.active { background: #667eea; color: white; }
        .btn-logout { background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; }
        
        /* BANNER */
        .banner-box { width: 100%; height: 160px; border-radius: 12px; overflow: hidden; margin-bottom: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); background: white; }
        .banner-box img { width: 100%; height: 100%; object-fit: cover; object-position: center; }

        /* PANELES */
        .panel { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .pos-grid { display: grid; grid-template-columns: 1fr 350px; gap: 20px; }
        select, input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; width: 100%; box-sizing: border-box; }
        .btn { background: #27ae60; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; color: #888; border-bottom: 2px solid #eee; padding: 10px 0; font-size: 13px; }
        td { padding: 12px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <div style="display:flex; align-items:center; gap:20px;">
            <h2 style="margin:0; color:#333;">🛒 <?php echo htmlspecialchars($nombre_vendedor); ?></h2>
            <div>
                <a href="?vista=pos" class="nav-link <?php echo $vista_actual=='pos'?'active':''; ?>">Punto de Venta</a>
                <a href="?vista=clientes" class="nav-link <?php echo $vista_actual=='clientes'?'active':''; ?>">Nuevo Cliente</a>
            </div>
        </div>
        <a href="auth_controlador.php?action=logout" class="btn-logout">Cerrar Turno</a>
    </div>

    <div class="banner-box">
        <img src="img/banner.png" alt="Bingus Petstore Banner">
    </div>

    <?php if($vista_actual === 'clientes'): ?>
        <div class="panel" style="max-width:600px; margin:0 auto;">
            <h3 style="margin-top:0;">👤 Registrar Cliente</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="crear_cliente">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div><label>Nombre</label><input type="text" name="nombre" required></div>
                    <div><label>RUT</label><input type="text" name="rut" required></div>
                    <div><label>Email</label><input type="email" name="email"></div>
                    <div><label>Teléfono</label><input type="text" name="telefono"></div>
                </div>
                <div style="margin-top:15px;"><label>Dirección</label><input type="text" name="direccion"></div>
                <button type="submit" class="btn" style="margin-top:20px;">Guardar Cliente</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if($vista_actual === 'pos'): ?>
        <div class="pos-grid">
            <div style="display:flex; flex-direction:column; gap:20px;">
                <div class="panel" style="padding:20px;">
                    <form method="POST" style="display:flex; gap:10px;">
                        <input type="hidden" name="accion" value="buscar">
                        <select name="id_producto_buscar" required>
                            <option value="">🔍 Buscar producto...</option>
                            <?php foreach($productos_db as $p): ?>
                                <option value="<?php echo $p['id_producto']; ?>">
                                    <?php echo htmlspecialchars($p['nombre']); ?> - Stock: <?php echo $p['stock']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn" style="width:auto; background:#667eea;">Ver</button>
                    </form>

                    <?php if($producto_encontrado): ?>
                        <div style="background:#f9f9f9; padding:15px; border-radius:10px; margin-top:15px; display:flex; gap:15px; align-items:center;">
                            <?php 
                                $img = !empty($producto_encontrado['imagen']) ? 'uploads/productos/'.$producto_encontrado['imagen'] : ''; 
                                if(file_exists($img)) echo "<img src='$img' style='width:60px; height:60px; object-fit:cover; border-radius:8px;'>";
                                else echo "<div style='font-size:30px;'>📦</div>";
                            ?>
                            <div style="flex:1;">
                                <div style="font-weight:bold;"><?php echo htmlspecialchars($producto_encontrado['nombre']); ?></div>
                                <div style="color:#27ae60; font-weight:bold;">$<?php echo number_format($producto_encontrado['precio'],0,',','.'); ?></div>
                            </div>
                            <form method="POST" style="display:flex; gap:5px; align-items:center;">
                                <input type="hidden" name="accion" value="agregar_carrito">
                                <input type="hidden" name="id_producto" value="<?php echo $producto_encontrado['id_producto']; ?>">
                                <input type="number" name="cantidad" value="1" min="1" max="<?php echo $producto_encontrado['stock']; ?>" style="width:60px;">
                                <button type="submit" class="btn" style="width:auto;">+</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="panel" style="flex:1;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0;">🛒 Carrito</h3>
                        <?php if(!empty($_SESSION['carrito'])): ?>
                            <a href="?vista=pos&vaciar_carrito=1" style="color:#e74c3c; font-size:12px; text-decoration:none;">Vaciar</a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(empty($_SESSION['carrito'])): ?>
                        <p style="color:#999; text-align:center; margin-top:40px;">Carrito vacío</p>
                    <?php else: ?>
                        <table>
                            <thead><tr><th>Prod</th><th>Cant</th><th>Subtotal</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach($_SESSION['carrito'] as $k => $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                    <td>x<?php echo $item['cantidad']; ?></td>
                                    <td>$<?php echo number_format($item['subtotal'],0,',','.'); ?></td>
                                    <td style="text-align:right;"><a href="?vista=pos&eliminar_item=<?php echo $k; ?>" style="color:#e74c3c; text-decoration:none; font-weight:bold;">×</a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel" style="height:fit-content;">
                <div style="font-size:24px; font-weight:800; text-align:right; margin-bottom:20px;">
                    Total: $<?php echo number_format($total_carrito, 0, ',', '.'); ?>
                </div>
                
                <form method="POST" id="formVenta">
                    <input type="hidden" name="accion" value="finalizar_venta">
                    
                    <label>Cliente</label>
                    <div style="display:flex; gap:5px; margin-bottom:15px;">
                        <select name="id_cliente" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach($clientes_db as $c): ?>
                                <option value="<?php echo $c['id_cliente']; ?>" <?php echo (isset($_SESSION['pos_id_cliente']) && $_SESSION['pos_id_cliente'] == $c['id_cliente'])?'selected':''; ?>>
                                    <?php echo htmlspecialchars($c['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="accion" value="fijar_cliente" class="btn" style="width:auto; background:#f39c12;" title="Fijar">📌</button>
                    </div>

                    <label>Estado Pago</label>
                    <select name="estado_pago" style="margin-bottom:20px;">
                        <option value="PAGADO">✅ Pagado</option>
                        <option value="PENDIENTE">⏳ Pendiente</option>
                    </select>

                    <button type="submit" class="btn" style="background:#333; font-size:18px; padding:15px;" 
                        <?php echo empty($_SESSION['carrito'])?'disabled':''; ?> 
                        onclick="confirmarCobro(event)">
                        COBRAR
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function confirmarCobro(e) {
        // Evitamos el envío automático
        e.preventDefault();
        var form = e.target.form;

        Swal.fire({
            title: '¿Confirmar Venta?',
            text: "Se registrará el pedido en el sistema.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#333',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Sí, cobrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviamos el formulario manualmente si confirma
                form.submit();
            }
        });
    }
</script>

<?php mostrarAlerta(); ?>
</body>
</html>