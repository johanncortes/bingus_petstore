# Walkthrough — Reestructuración Bingus Petstore v3.0

## Resumen General

Se completó la transformación integral del sistema **Bingus Petstore** desde una plataforma POS + Tienda Online a un sistema **100% e-commerce** con intranet administrativa y soporte para arquitectura distribuida en 3 máquinas.

### Cambios principales:
1. **POS eliminado** — vendedores se convirtieron en repartidores (2 por admin)
2. **Tienda online** como foco principal del proyecto
3. **IVA 19%** integrado en catálogo, carrito y checkout
4. **3 máquinas** — lógica (Win7 VM), BD (Linux Mint VM), frontend (Host)
5. **Borrón y cuenta nueva** — pedidos, clientes y auditoría limpiados

---

## FASE 1: Base de Datos — Migración y Limpieza

### Archivo creado
- [migracion_v3.sql](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/migracion_v3.sql)

### Cambios en BD

| Operación | Detalle |
|---|---|
| **Limpieza** | DELETE de `detalle_pedido`, `pedidos`, `clientes`, `auditoria_cambios` + reset AUTO_INCREMENT |
| **Renombrar tabla** | `vendedores` → `repartidores`, `id_vendedor` → `id_repartidor` |
| **Columnas eliminadas** | `contrasena`, `usuario` de repartidores |
| **Columnas agregadas** | `estado_disponibilidad` ENUM en repartidores |
| **Tabla pedidos** | `id_vendedor` → `id_repartidor` (nullable), + `direccion_entrega`, `subtotal_neto`, `total_iva` |
| **Tabla detalle_pedido** | + `precio_neto`, `iva` |
| **Nueva tabla** | `configuracion_impuestos` con IVA 19% |
| **10 repartidores** | 2 por cada uno de los 5 admins |
| **Vistas recreadas** | `v_pedidos_detalle` (con repartidores), `v_catalogo_tienda` (nueva) |
| **SP actualizado** | `sp_dashboard_stats` con métricas de repartidores y reparto |
| **Triggers** | `trg_max_repartidores_insert/update` — limita 2 activos por admin |

> [!IMPORTANT]
> Este script debe ejecutarse sobre la BD existente `bingus_petstore2`. Primero importar el esquema original, luego aplicar la migración.

---

## FASE 2: Eliminación del POS y Refactorización Auth

### Archivos eliminados
- `views/vendedor/pos.php` (directorio completo)
- `assets/js/pos.js`

### Archivos modificados

| Archivo | Cambio |
|---|---|
| [AuthModel.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/models/AuthModel.php) | Eliminado bloque `elseif VENDEDOR`, solo queda login de admin |
| [AuthController.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/controllers/AuthController.php) | Eliminado redirect a POS, simplificado a solo admin |
| [AuthMiddleware.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/helpers/AuthMiddleware.php) | Eliminado `verificarVendedor()` |
| [login.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/views/auth/login.php) | Eliminado selector de rol, solo login admin + link a tienda |
| [index.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/index.php) | Eliminado redirect VENDEDOR, default → tienda pública |

---

## FASE 3: API — Vendedores → Repartidores

### Archivos nuevos (reemplazan los de vendedores)

| Archivo | Descripción |
|---|---|
| [RepartidorModel.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/models/RepartidorModel.php) | CRUD repartidores, sin passwords, con `contarRepartidoresPorAdmin()`, `cambiarDisponibilidad()`, `getRepartidoresDisponibles()` |
| [RepartidorController.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/controllers/RepartidorController.php) | Endpoints: listar, crear (valida límite 2), actualizar, eliminar, cambiar disponibilidad, listar disponibles |

### Archivos modificados

| Archivo | Cambio |
|---|---|
| [PedidoModel.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/models/PedidoModel.php) | Referencias `vendedor` → `repartidor`, nuevo `asignarRepartidor()`, `crearPedido()` con IVA |
| [PedidoController.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/controllers/PedidoController.php) | Máquina de estados: PENDIENTE→PAGADO→EN_REPARTO→ENTREGADO \| CANCELADO, endpoint `asignarRepartidor()` |
| [DashboardController.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/controllers/DashboardController.php) | Usa SP actualizado con métricas de repartidores |
| [API Router](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/index.php) | `vendedores` → `repartidores` con sub-rutas `disponibles`, `disponibilidad`, `pedidos/{id}/repartidor`, `tienda/config`, API v3.0 |

### Nuevas rutas API

```
GET    /api/repartidores              → Listar mis repartidores
GET    /api/repartidores/disponibles  → Todos los disponibles
GET    /api/repartidores/{id}         → Obtener uno
POST   /api/repartidores             → Crear (máx 2 por admin)
PUT    /api/repartidores/{id}         → Actualizar
PUT    /api/repartidores/{id}/disponibilidad → Cambiar estado
DELETE /api/repartidores/{id}         → Soft delete

PUT    /api/pedidos/{id}/repartidor   → Asignar repartidor
PUT    /api/pedidos/{id}/estado       → Cambiar estado (state machine)

GET    /api/tienda/config             → Tasa IVA vigente
```

---

## FASE 4: Intranet Admin — Vistas

### Archivos nuevos

| Archivo | Descripción |
|---|---|
| [repartidores/listar.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/views/admin/repartidores/listar.php) | Lista con badges de disponibilidad (🟢/🏍️/🔴) |
| [repartidores/crear.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/views/admin/repartidores/crear.php) | Formulario sin password, alerta si ya tiene 2 |
| [repartidores/editar.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/views/admin/repartidores/editar.php) | Edición de datos del repartidor |
| [repartidores.js](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/assets/js/repartidores.js) | Lógica de listado y eliminación |

### Archivos modificados

| Archivo | Cambio |
|---|---|
| [dashboard.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/views/admin/dashboard.php) | Tarjeta "Repartidores" 🚚 + indicadores de reparto (⏳ sin repartidor, 🏍️ en reparto) + link a tienda |
| [pedidos/listar.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/views/admin/pedidos/listar.php) | Columna Repartidor (en vez de Vendedor), Total c/IVA |
| [pedidos/editar.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/views/admin/pedidos/editar.php) | Desglose IVA (neto/iva/total), panel asignar repartidor, state machine buttons |
| [pedidos.js](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/assets/js/pedidos.js) | Badges de nuevos estados, icono por estado, repartidor_nombre |

---

## FASE 5: Tienda Online — IVA

### Archivos modificados

| Archivo | Cambio |
|---|---|
| [TiendaModel.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/models/TiendaModel.php) | `getCatalogo()` agrega `precio_neto`, `iva`, `precio_total` a cada producto. `crearPedidoTienda()` almacena desglose IVA en cada detalle y cabecera |
| [TiendaController.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/controllers/TiendaController.php) | Nuevo endpoint `GET /tienda/config` que devuelve tasa IVA vigente |
| [tienda.js](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/assets/js/tienda.js) | Carga IVA desde API, cards muestran "IVA incl.", carrito sidebar con desglose (Neto/IVA/Total), checkout con desglose completo |
| [tienda.css](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/assets/css/tienda.css) | Nuevas clases: `.precio-iva-tag`, `.carrito-desglose*`, `.checkout-resumen-desglose` |

### Flujo del IVA
```
BD (precio neto) → API (+iva, +precio_total) → Frontend (muestra total c/IVA)
                                              → Checkout envía precio_total
                                              → Backend desglosa y almacena neto + iva
```

---

## FASE 6: Arquitectura 3 Máquinas

### Archivos modificados/creados

| Archivo | Cambio |
|---|---|
| [Config.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/config/Config.php) | Constantes `APP_HOST`, `DB_HOST`, `DB_USER`, `DB_PASS` configurables. Diagrama ASCII en comentarios |
| [Database.php](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/bingus_petstore/api/config/Database.php) | Lee credenciales de Config.php en vez de hardcoded. Mensaje de error con hint |
| [setup_linux_db.sh](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/setup_linux_db.sh) | **NUEVO** — Script automatizado para Linux Mint: instala MariaDB, configura red, crea usuario con permisos mínimos, configura ufw |
| [GUIA_DESPLIEGUE.md](file:///c:/Users/scozi/OneDrive/Escritorio/UCEN/7%C2%B0%20SEMESTRE/Arquitectura%20de%20Software/BINGUS%20ONLINE%20MARKET/bingus_petstore/GUIA_DESPLIEGUE.md) | **NUEVO** — Guía paso a paso para las 3 máquinas con VirtualBox |

---

## FASE 7: Limpieza

### Archivos eliminados

| Archivo | Razón |
|---|---|
| `api/controllers/VendedorController.php` | Reemplazado por RepartidorController |
| `api/models/VendedorModel.php` | Reemplazado por RepartidorModel |
| `assets/js/vendedores.js` | Reemplazado por repartidores.js |
| `assets/js/pos.js` | POS eliminado |
| `views/vendedor/` (directorio) | POS eliminado |
| `views/admin/vendedores/` (directorio) | Reemplazado por repartidores/ |

### Verificación
- ✅ Cero referencias a "vendedor" en todo el código PHP, JS y vistas
- ✅ Estructura de directorios limpia (sin archivos huérfanos)
- ✅ API Router actualizado a v3.0 con endpoints correctos

---

## Para ejecutar la migración

```bash
# 1. Importar esquema original (si es BD nueva)
mysql -u root -p bingus_petstore2 < bingus_petstore2.sql

# 2. Aplicar migración v3
mysql -u root -p bingus_petstore2 < migracion_v3.sql

# 3. Verificar resultado
mysql -u root -p -e "SELECT COUNT(*) as repartidores FROM repartidores; SELECT COUNT(*) as impuestos FROM configuracion_impuestos;" bingus_petstore2
```
