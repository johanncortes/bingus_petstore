<?php $page_title = 'Punto de Venta - Bingus Petstore'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container" style="max-width:1100px;">
    <!-- Navbar vendedor -->
    <div class="navbar">
        <div style="display:flex; align-items:center; gap:20px;">
            <h2 style="margin:0;">🛒 <span id="vendedorNombre">...</span></h2>
            <div>
                <a href="#" class="nav-link active" id="navPos" onclick="cambiarVista('pos')">Punto de Venta</a>
                <a href="#" class="nav-link" id="navClientes" onclick="cambiarVista('clientes')">Nuevo Cliente</a>
            </div>
        </div>
        <a onclick="Api.logout()" class="btn btn-danger" style="cursor:pointer;">Cerrar Turno</a>
    </div>

    <div class="banner">
        <img src="/bingus_petstore/assets/img/banner.png" alt="Bingus Petstore Banner">
    </div>

    <!-- ====== VISTA: CLIENTES ====== -->
    <div id="vistaClientes" style="display:none;">
        <div class="panel" style="max-width:600px; margin:0 auto;">
            <h3 style="margin-top:0;">👤 Registrar Cliente</h3>
            <form onsubmit="crearCliente(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" id="cliNombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>RUT</label>
                        <input type="text" id="cliRut" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="cliEmail" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" id="cliTelefono" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" id="cliDireccion" class="form-control">
                </div>
                <button type="submit" class="btn btn-success btn-block" style="margin-top:15px;">Guardar Cliente</button>
            </form>
        </div>
    </div>

    <!-- ====== VISTA: POS ====== -->
    <div id="vistaPos">
        <div class="pos-grid">
            <!-- Columna izquierda: buscar + carrito -->
            <div style="display:flex; flex-direction:column; gap:20px;">
                <!-- Buscar producto -->
                <div class="panel" style="padding:20px;">
                    <div style="display:flex; gap:10px;">
                        <select id="selectProducto" class="form-control">
                            <option value="">🔍 Buscar producto...</option>
                        </select>
                        <button class="btn btn-primary" style="width:auto;" onclick="verProducto()">Ver</button>
                    </div>

                    <!-- Producto seleccionado -->
                    <div id="productoInfo" style="display:none; background:#f9f9f9; padding:15px; border-radius:10px; margin-top:15px; display:none;">
                        <div style="display:flex; gap:15px; align-items:center;">
                            <div id="prodImg" style="font-size:30px;">📦</div>
                            <div style="flex:1;">
                                <div id="prodNombre" style="font-weight:bold;"></div>
                                <div id="prodPrecio" style="color:#27ae60; font-weight:bold;"></div>
                                <div id="prodStock" style="font-size:12px; color:#888;"></div>
                            </div>
                            <div style="display:flex; gap:5px; align-items:center;">
                                <input type="number" id="prodCantidad" value="1" min="1" class="form-control" style="width:60px;">
                                <button class="btn btn-success" style="width:auto;" onclick="agregarAlCarrito()">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carrito -->
                <div class="panel" style="flex:1;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 style="margin:0;">🛒 Carrito</h3>
                        <a href="#" onclick="vaciarCarrito()" style="color:#e74c3c; font-size:12px;" id="btnVaciar" class="hidden">Vaciar</a>
                    </div>
                    
                    <div id="carritoVacio" style="color:#999; text-align:center; margin-top:40px;">Carrito vacío</div>
                    
                    <table id="carritoTabla" style="display:none;">
                        <thead><tr><th>Prod</th><th>Cant</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody id="carritoBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Columna derecha: total + cobrar -->
            <div class="panel" style="height:fit-content;">
                <div style="font-size:24px; font-weight:800; text-align:right; margin-bottom:20px;">
                    Total: $<span id="totalCarrito">0</span>
                </div>
                
                <div class="form-group">
                    <label>Cliente</label>
                    <select id="selectCliente" class="form-control" required>
                        <option value="">Seleccionar...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Estado Pago</label>
                    <select id="estadoPago" class="form-control">
                        <option value="PAGADO">✅ Pagado</option>
                        <option value="PENDIENTE">⏳ Pendiente</option>
                    </select>
                </div>

                <button class="btn btn-dark btn-block btn-lg" id="btnCobrar" onclick="confirmarCobro()" disabled>
                    COBRAR
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/bingus_petstore/assets/js/api.js"></script>
<script src="/bingus_petstore/assets/js/pos.js"></script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
