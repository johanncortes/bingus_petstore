# 🐾 Guía de Despliegue — Bingus Petstore v3.0
## Arquitectura Distribuida en 4 Equipos (2 Hosts + 2 VMs) — Todos Laptops

> 💡 **Todos los equipos físicos son laptops** (2 Windows 11 + 1 macOS) conectados por WiFi. La configuración de red recomendada es **NAT + Port Forwarding** (ver Paso 0).

---

## Diagrama de Arquitectura — NAT + Port Forwarding (⭐ Recomendada para laptops WiFi)

```
╔════════════════════════════════════════════════════════════════════════╗
║                    RED WiFi (misma red en todos)                      ║
║                    Subred: 192.168.1.0/24                            ║
╠════════════════════════════════════════════════════════════════════════╣
║                                                                       ║
║  ┌────────────────────────────────┐  ┌──────────────────────────────┐ ║
║  │  LAPTOP 1: Windows 11 Host    │  │  LAPTOP 2: Windows 11 Host   │ ║
║  │  IP WiFi: 192.168.1.100       │  │  IP WiFi: 192.168.1.200      │ ║
║  │                                │  │                               │ ║
║  │  🖥️ Navegador (Intranet Admin)│  │  Expone puerto 8080 →        │ ║
║  │  → http://192.168.1.200:8080/ │  │    redirige a VM W7 :80      │ ║
║  │    bingus_petstore/views/     │  │                               │ ║
║  │    auth/login.php             │  │  ┌───────────────────────────┐│ ║
║  │                                │  │  │ VM: Windows 7 (NAT)      ││ ║
║  │  Expone puerto 3306 →         │  │  │ IP interna: 10.0.2.15    ││ ║
║  │    redirige a VM Mint :3306   │  │  │                           ││ ║
║  │                                │  │  │ ⚙️ Apache + PHP 8.x     ││ ║
║  │  ┌────────────────────────┐   │  │  │    (XAMPP) puerto 80      ││ ║
║  │  │ VM: Linux Mint (NAT)   │   │  │  │                           ││ ║
║  │  │ IP interna: 10.0.2.15  │   │  │  │ 📦 LÓGICA DE NEGOCIO     ││ ║
║  │  │                        │   │  │  │ • API REST + Controllers  ││ ║
║  │  │ 🛢️ MariaDB puerto 3306│   │  │  │ • Models + Vistas         ││ ║
║  │  │                        │   │  │  │ • Auth + CORS + IVA 19%   ││ ║
║  │  └────────────────────────┘   │  │  └───────────────────────────┘│ ║
║  └───────────────┬────────────────┘  └──────────────┬───────────────┘ ║
║                  │ :3306 (via Host)                 │ :8080 (via Host)║
║                  └─────────────┬────────────────────┘                 ║
║                                │                                      ║
║                 ┌──────────────┴──────────────┐                       ║
║                 │  LAPTOP 3: macOS            │                       ║
║                 │  IP WiFi: 192.168.1.150     │                       ║
║                 │                              │                       ║
║                 │  🍎 Safari/Chrome            │                       ║
║                 │  → Tienda Online:            │                       ║
║                 │    http://192.168.1.200:8080/│                       ║
║                 │    bingus_petstore/views/    │                       ║
║                 │    tienda/tienda.php         │                       ║
║                 │                              │                       ║
║                 │  Comprar productos ✅         │                       ║
║                 └──────────────────────────────┘                       ║
╚════════════════════════════════════════════════════════════════════════╝
```

> 📝 **Clave**: Con NAT, las VMs NO tienen IP visible en la red WiFi. Se accede a sus servicios a través de los **puertos del Host** (8080 para Apache, 3306 para MariaDB).

---

## Diagrama de Arquitectura — Adaptador Puente (alternativa, solo si WiFi lo soporta)

```
╔════════════════════════════════════════════════════════════════════╗
║                    RED LOCAL (misma WiFi/LAN)                     ║
║                    Subred: 192.168.1.0/24                        ║
╠════════════════════════════════════════════════════════════════════╣
║                                                                   ║
║  ┌──────────────────────────────┐   ┌───────────────────────────┐ ║
║  │  EQUIPO 1: Windows 11 Host  │   │  EQUIPO 2: Windows 11 Host│ ║
║  │  IP Host: 192.168.1.100     │   │  IP Host: 192.168.1.200   │ ║
║  │                              │   │                           │ ║
║  │  🖥️  Navegador (Intranet    │   │  Corre VirtualBox con:    │ ║
║  │      Admin) accede a →      │   │                           │ ║
║  │      http://<IP_VM_W7>/     │   │  ┌───────────────────────┐│ ║
║  │      bingus_petstore/       │   │  │ VM: Windows 7         ││ ║
║  │      views/auth/login.php   │   │  │ IP: 192.168.1.10      ││ ║
║  │                              │   │  │                       ││ ║
║  │  Corre VirtualBox con:      │   │  │ ⚙️ Apache + PHP 8.x   ││ ║
║  │                              │   │  │    (XAMPP)             ││ ║
║  │  ┌──────────────────────┐   │   │  │                       ││ ║
║  │  │ VM: Linux Mint       │   │   │  │ 📦 LÓGICA DE NEGOCIO: ││ ║
║  │  │ IP: 192.168.1.20     │   │   │  │ • API REST (router)   ││ ║
║  │  │                      │   │   │  │ • Controllers PHP     ││ ║
║  │  │ 🛢️ MariaDB          │   │   │  │ • Models PHP          ││ ║
║  │  │    Puerto 3306       │   │   │  │ • Vistas PHP/HTML/JS  ││ ║
║  │  │                      │   │   │  │ • Sesiones y Auth     ││ ║
║  │  │ 🛡️ Firewall (ufw)   │   │   │  │ • CORS headers       ││ ║
║  │  │    Solo acepta 3306  │   │   │  │ • Validaciones        ││ ║
║  │  │    desde IP_VM_W7    │   │   │  │ • Cálculo IVA 19%    ││ ║
║  │  └──────────┬───────────┘   │   │  │                       ││ ║
║  │             │                │   │  │ Puerto 80 (HTTP)     ││ ║
║  │             │                │   │  └───────────┬───────────┘│ ║
║  └─────────────┼────────────────┘   └─────────────┼─────────────┘ ║
║                │ MySQL 3306                       │ HTTP 80       ║
║                └──────────────┬───────────────────┘               ║
║                               │                                   ║
║                ┌──────────────┴──────────────┐                    ║
║                │  EQUIPO 3: macOS            │                    ║
║                │  IP: 192.168.1.150          │                    ║
║                │                              │                    ║
║                │  🍎 Safari/Chrome            │                    ║
║                │  → Tienda Online:            │                    ║
║                │    http://<IP_VM_W7>/        │                    ║
║                │    bingus_petstore/          │                    ║
║                │    views/tienda/tienda.php   │                    ║
║                │                              │                    ║
║                │  Comprar productos ✅         │                    ║
║                └──────────────────────────────┘                    ║
╚════════════════════════════════════════════════════════════════════╝
```

### Flujo de datos — Cliente (macOS → Tienda Online):
```
macOS (Cliente)                  Windows 7 VM                  Linux Mint VM
    │                           (Lógica de Negocio)             (Base de Datos)
    │                                  │                              │
    ├── HTTP GET /tienda ────────────►│                              │
    │                                  ├── SQL SELECT catálogo ─────►│
    │                                  │◄── Resultados ──────────────┤
    │◄── HTML + JSON productos ───────┤                              │
    │                                  │                              │
    ├── HTTP POST /checkout ─────────►│                              │
    │                                  ├── Validar datos              │
    │                                  ├── Calcular IVA 19%          │
    │                                  ├── SQL INSERT pedido ────────►│
    │                                  │◄── OK ─────────────────────-┤
    │◄── JSON {success: true} ────────┤                              │
```

### Flujo de datos — Administrador (Host Win11 #1 → Intranet Admin):
```
Host Win11 #1 (Admin)            Windows 7 VM                  Linux Mint VM
    │                           (Lógica de Negocio)             (Base de Datos)
    │                                  │                              │
    │  ── LOGIN ──────────────────────────────────────────────────────│
    ├── HTTP POST /auth/login ───────►│                              │
    │                                  ├── SQL SELECT admin ────────►│
    │                                  │◄── Credenciales válidas ────┤
    │                                  ├── Crear sesión PHP           │
    │◄── Redirect → dashboard ────────┤                              │
    │                                  │                              │
    │  ── DASHBOARD (estadísticas) ───────────────────────────────────│
    ├── HTTP GET /dashboard ─────────►│                              │
    │                                  ├── CALL sp_dashboard_stats ──►│
    │                                  │◄── {ventas, productos,      │
    │                                  │     pedidos, repartidores} ─┤
    │◄── HTML con estadísticas ───────┤                              │
    │                                  │                              │
    │  ── GESTIÓN DE PRODUCTOS ───────────────────────────────────────│
    ├── HTTP GET /productos ─────────►│                              │
    │                                  ├── SQL SELECT * productos ──►│
    │                                  │◄── Lista de productos ──────┤
    │◄── HTML tabla de productos ─────┤                              │
    │                                  │                              │
    ├── HTTP POST /productos/crear ──►│                              │
    │                                  ├── Validar datos + imagen     │
    │                                  ├── SQL INSERT producto ─────►│
    │                                  │◄── OK ─────────────────────-┤
    │◄── JSON {success} ──────────────┤                              │
    │                                  │                              │
    │  ── GESTIÓN DE PEDIDOS ─────────────────────────────────────────│
    ├── HTTP GET /pedidos ───────────►│                              │
    │                                  ├── SELECT v_pedidos_detalle ─►│
    │                                  │◄── Pedidos con detalle ─────┤
    │◄── HTML lista de pedidos ───────┤                              │
    │                                  │                              │
    ├── HTTP PUT /pedidos/estado ────►│                              │
    │   {id: 5, estado: "PAGADO"}     │                              │
    │                                  ├── Validar transición:        │
    │                                  │   PENDIENTE→PAGADO ✅        │
    │                                  │   PAGADO→PENDIENTE ❌        │
    │                                  ├── SQL UPDATE estado ────────►│
    │                                  │◄── OK ─────────────────────-┤
    │◄── JSON {success} ──────────────┤                              │
    │                                  │                              │
    │  ── ASIGNAR REPARTIDOR ─────────────────────────────────────────│
    ├── HTTP PUT /pedidos/repartidor ►│                              │
    │   {id_pedido: 5, id_rep: 2}     │                              │
    │                                  ├── SQL UPDATE asignar ───────►│
    │                                  │◄── OK ─────────────────────-┤
    │◄── JSON {success} ──────────────┤                              │
    │                                  │                              │
    │  ── GESTIÓN DE REPARTIDORES ────────────────────────────────────│
    ├── HTTP POST /repartidores ─────►│                              │
    │   {nombre, telefono, ...}       │                              │
    │                                  ├── Validar máximo 2 por admin │
    │                                  ├── SQL INSERT repartidor ────►│
    │                                  │   (Trigger valida máx 2) ────┤
    │                                  │◄── OK ─────────────────────-┤
    │◄── JSON {success} ──────────────┤                              │
```

### Máquina de estados de pedidos (gestionada por el Admin):
```
   ┌──────────┐     Admin paga     ┌──────────┐    Asigna       ┌────────────┐    Confirma     ┌────────────┐
   │PENDIENTE │ ──────────────────►│ PAGADO   │──repartidor──►│ EN_REPARTO │──entrega─────►│ ENTREGADO  │
   └──────────┘                    └──────────┘                └────────────┘                └────────────┘
        │                                │
        │         Admin cancela          │
        └──────────────┬─────────────────┘
                       ▼
                ┌────────────┐
                │ CANCELADO  │
                └────────────┘
```

---

## ¿Qué es la "Lógica de Negocio"?

La **lógica de negocio** es todo el código PHP que procesa las reglas y operaciones del sistema. En Bingus Petstore, esto incluye:

### Lo que corre en la VM Windows 7 (Apache + PHP):

| Componente | Archivos | Qué hace |
|---|---|---|
| **API Router** | `api/index.php` | Recibe todas las peticiones HTTP, las parsea y las envía al controlador correcto |
| **Controllers** | `api/controllers/*.php` | Procesan la lógica: validaciones, cálculos, transformaciones |
| **Models** | `api/models/*.php` | Ejecutan las consultas SQL contra la BD remota (Linux Mint) |
| **Configuración** | `api/config/Config.php` | Define la IP de la BD, credenciales, URLs base |
| **Conexión BD** | `api/config/Database.php` | Se conecta a MariaDB en la VM Linux Mint |
| **Helpers** | `api/helpers/*.php` | Funciones auxiliares (respuestas JSON, auth middleware) |
| **Vistas Admin** | `views/admin/*.php` | Dashboard, gestión de productos, pedidos, repartidores |
| **Vistas Tienda** | `views/tienda/*.php` | Tienda pública + checkout |
| **Vistas Auth** | `views/auth/*.php` | Login de admin |
| **Assets** | `assets/css/*.css`, `assets/js/*.js` | Estilos y JavaScript del frontend |

### Reglas de negocio específicas que procesa la VM Windows 7:

1. **Catálogo con IVA**: Calcula `precio_neto × 1.19 = precio_total` para cada producto
2. **Checkout**: Valida datos del cliente, crea el pedido con desglose de IVA
3. **Máquina de estados de pedidos**: `PENDIENTE → PAGADO → EN_REPARTO → ENTREGADO | CANCELADO`
4. **Gestión de repartidores**: Máximo 2 activos por administrador (validado por trigger + controller)
5. **Autenticación admin**: Login/logout con sesiones PHP
6. **Autenticación cliente**: Registro + login para compras
7. **Dashboard**: Estadísticas de ventas, productos, repartidores

### Lo que corre en la VM Linux Mint (solo BD):

| Componente | Qué hace |
|---|---|
| **MariaDB Server** | Almacena todos los datos: productos, pedidos, clientes, repartidores, admins |
| **Triggers SQL** | `trg_max_repartidores_insert/update` — valida máximo 2 repartidores por admin |
| **Stored Procedures** | `sp_dashboard_stats` — genera estadísticas para el dashboard |
| **Vistas SQL** | `v_catalogo_tienda`, `v_pedidos_detalle` — consultas preconstruidas |
| **Firewall (ufw)** | Solo acepta conexiones MySQL desde la IP de la VM Windows 7 |

---

## Requisito Previo: Todos los Equipos en la Misma Red

> ⚠️ **CRÍTICO**: Los 3 equipos físicos (2 Windows 11 + macOS) deben estar conectados a la **misma red WiFi** para que se vean entre sí.

---

## ⚠️ NOTA IMPORTANTE: Todos los Equipos son Laptops con WiFi

Todos los equipos físicos de este despliegue son **laptops** (2 Windows 11 + 1 macOS), lo que tiene implicaciones directas para la configuración de red de las VMs:

### El problema con Adaptador Puente + WiFi en laptops

El **Adaptador Puente (Bridge)** de VirtualBox necesita que el adaptador de red del host opere en **modo promiscuo** (aceptar paquetes con cualquier dirección MAC). Esto genera problemas en laptops porque:

1. **Los drivers WiFi de laptops NO soportan modo promiscuo de forma confiable** — a diferencia de Ethernet, el estándar WiFi (802.11) no permite que un adaptador maneje múltiples direcciones MAC simultáneamente.
2. **Windows 11 bloquea el modo promiscuo** en muchos drivers WiFi modernos por razones de seguridad.
3. **Síntomas comunes**: La VM no obtiene IP del router, la VM obtiene IP pero no es pingeable desde otros equipos, o la conexión se cae intermitentemente.

### Solución: Dos opciones de red

| Opción | Ventaja | Cuándo usarla |
|---|---|---|
| **A) NAT + Port Forwarding** (⭐ Recomendada para WiFi) | Siempre funciona, no depende del driver WiFi | Cuando usan WiFi en laptops |
| **B) Adaptador Puente** | La VM tiene su propia IP en la red | Solo si funciona (probar primero), o si conectan Ethernet por cable |

> 💡 **Recomendación**: Si ya intentaron Bridge y tuvieron problemas de conexión, **usen la Opción A (NAT + Port Forwarding)** directamente. Es 100% confiable.

---

## Paso 0: Configuración de Red en VirtualBox

### Opción A: NAT + Port Forwarding (⭐ Recomendada para laptops WiFi)

En esta opción, las VMs usan **NAT** (la red por defecto de VirtualBox que siempre funciona) y se accede a los servicios de las VMs a través del **Host**, que redirige los puertos.

#### 0.A.1 VM Windows 7 (en Equipo 2 - Windows 11) — Servidor Web

1. Abrir VirtualBox → Seleccionar la VM Windows 7 → **Configuración** → **Red**
2. Adaptador 1 → **Conectado a: NAT** (es la opción por defecto)
3. Click en **Avanzadas** → **Reenvío de puertos**
4. Agregar las siguientes reglas:

| Nombre | Protocolo | IP Host | Puerto Host | IP Invitado | Puerto Invitado |
|---|---|---|---|---|---|
| Apache HTTP | TCP | 0.0.0.0 | 8080 | 10.0.2.15 | 80 |

5. Aceptar y arrancar la VM

> 📝 Esto significa: cuando alguien acceda al **puerto 8080 del Host Win11 #2**, VirtualBox redirige al **puerto 80 de la VM Windows 7**.

#### 0.A.2 VM Linux Mint (en Equipo 1 - Windows 11) — Base de Datos

1. Abrir VirtualBox → Seleccionar la VM Linux Mint → **Configuración** → **Red**
2. Adaptador 1 → **Conectado a: NAT**
3. Click en **Avanzadas** → **Reenvío de puertos**
4. Agregar las siguientes reglas:

| Nombre | Protocolo | IP Host | Puerto Host | IP Invitado | Puerto Invitado |
|---|---|---|---|---|---|
| MariaDB | TCP | 0.0.0.0 | 3306 | 10.0.2.15 | 3306 |
| SSH | TCP | 0.0.0.0 | 2222 | 10.0.2.15 | 22 |

5. Aceptar y arrancar la VM

> 📝 Esto significa: cuando la VM Windows 7 necesite conectarse a MariaDB, se conecta a la **IP del Host Win11 #1, puerto 3306**.

#### 0.A.3 Cómo cambian las IPs con NAT + Port Forwarding

**Este es el cambio clave**: con NAT, las VMs NO tienen su propia IP visible en la red. Los otros equipos acceden a los servicios **a través del Host**.

| Máquina | Comando para obtener IP | IP Ejemplo | Rol |
|---|---|---|---|
| **Host Win11 #1** (tiene VM Linux Mint) | CMD → `ipconfig` → IPv4 del WiFi | `192.168.1.100` | Expone MariaDB en su puerto 3306 |
| **Host Win11 #2** (tiene VM Windows 7) | CMD → `ipconfig` → IPv4 del WiFi | `192.168.1.200` | Expone Apache en su puerto 8080 |
| **macOS** | Terminal → `ifconfig en0` | `192.168.1.150` | Cliente (navegador) |

> 📝 **ANOTA LAS IPs DE LOS HOSTS** — Son las que usarás en toda la configuración.

#### 0.A.4 Permitir puertos en Firewall del Host Windows 11

⚠️ **MUY IMPORTANTE**: El Firewall de Windows 11 en los **Hosts** debe permitir los puertos redirigidos. Si no, los otros equipos no podrán conectarse.

**En Host Win11 #2** (tiene la VM Windows 7 → Apache):
```powershell
# Ejecutar PowerShell como Administrador:
New-NetFirewallRule -DisplayName "VBox Apache Forward" -Direction Inbound -Protocol TCP -LocalPort 8080 -Action Allow
```

**En Host Win11 #1** (tiene la VM Linux Mint → MariaDB):
```powershell
# Ejecutar PowerShell como Administrador:
New-NetFirewallRule -DisplayName "VBox MariaDB Forward" -Direction Inbound -Protocol TCP -LocalPort 3306 -Action Allow
```

#### 0.A.5 Configuración especial: Conexión BD desde VM W7 hacia VM Linux Mint

Con NAT, la VM Windows 7 **no puede acceder directamente** a la VM Linux Mint (están en hosts distintos). La conexión a la BD se hace a través de la red real:

```
VM Windows 7 → NAT Gateway (10.0.2.2) → Host Win11 #2 → WiFi → Host Win11 #1 → Port Forward → VM Linux Mint (MariaDB)
```

En `Config.php`, la IP de la BD será **la IP del Host Win11 #1** (no la de la VM Linux Mint):
```php
define('DB_HOST', '192.168.1.100');  // ← IP del HOST Win11 #1 (NO de la VM Linux Mint)
```

Y en la configuración de firewall de Linux Mint (`setup_linux_db.sh`), se debe permitir acceso desde **la IP NAT interna** (10.0.2.2, que es como VirtualBox rutea desde la VM hacia afuera) Y también aceptar conexiones reenviadas por el Host:
```bash
# En el setup de Linux Mint, cambiar la IP permitida:
IP_WINDOWS7="%" # Permitir desde cualquier IP (en desarrollo está OK)
# O ser más específico:
# IP_WINDOWS7="192.168.1.%" # Permitir desde toda la subred local
```

#### 0.A.6 Verificar conectividad con NAT

```bash
# Desde la VM Windows 7 (CMD), verificar que ve al Host Win11 #1 (donde está MariaDB):
ping 192.168.1.100

# Probar conexión a MariaDB desde la VM W7 a través del Host:
C:\xampp\mysql\bin\mysql.exe -h 192.168.1.100 -P 3306 -u bingus_app -p

# Desde macOS, verificar que ve al Host Win11 #2 (donde está Apache):
ping 192.168.1.200

# Desde macOS, probar acceso HTTP:
curl http://192.168.1.200:8080/bingus_petstore/api/

# Desde Host Win11 #1, probar acceso HTTP:
# En navegador: http://192.168.1.200:8080/bingus_petstore/views/auth/login.php
```

#### 0.A.7 Resumen de URLs con NAT + Port Forwarding

| Recurso | URL (reemplazar IPs) |
|---|---|
| **Tienda Online** | `http://<IP_HOST_W11_2>:8080/bingus_petstore/views/tienda/tienda.php` |
| **Intranet Admin** | `http://<IP_HOST_W11_2>:8080/bingus_petstore/views/auth/login.php` |
| **API Test** | `http://<IP_HOST_W11_2>:8080/bingus_petstore/api/` |

> ⚠️ Nótese que el puerto cambia de **80** a **8080** en las URLs cuando se usa NAT + Port Forwarding.

---

### Opción B: Adaptador Puente (solo si funciona o con Ethernet por cable)

> ⚠️ **Intentar esta opción SOLO si**: tienen un adaptador Ethernet USB, están conectados por cable, o el Bridge les funcionó exitosamente en pruebas previas.

#### 0.B.1 VM Linux Mint (en Equipo 1 - Windows 11)
1. Abrir VirtualBox → Seleccionar la VM Linux Mint → **Configuración** → **Red**
2. Adaptador 1 → **Conectado a: Adaptador puente**
3. Nombre: Seleccionar la interfaz de red real del Host (**preferir Ethernet si hay**, si no, WiFi)
4. Modo promiscuo: **Permitir todo** (para asegurar conectividad)
5. Aceptar y arrancar la VM

#### 0.B.2 VM Windows 7 (en Equipo 2 - Windows 11)
1. Abrir VirtualBox → Seleccionar la VM Windows 7 → **Configuración** → **Red**
2. Adaptador 1 → **Conectado a: Adaptador puente**
3. Nombre: Seleccionar la interfaz de red real del Host (**preferir Ethernet si hay**, si no, WiFi)
4. Modo promiscuo: **Permitir todo**
5. Aceptar y arrancar la VM

#### 0.B.3 Diagnóstico rápido: ¿Funciona el Bridge con mi WiFi?

Después de arrancar la VM, verificar **dentro de la VM**:
```bash
# En VM Linux Mint:
ip addr show
# Debe mostrar una IP del tipo 192.168.1.x (misma subred que el Host)
# Si muestra SOLO 127.0.0.1 o no tiene IPv4 → Bridge NO funciona con tu WiFi

# En VM Windows 7:
ipconfig
# Debe mostrar IPv4 del tipo 192.168.1.x
# Si muestra "Media desconectada" o 169.254.x.x → Bridge NO funciona con tu WiFi
```

> ❌ **Si la VM no obtiene IP en la misma subred del host**, cambiar a **Opción A (NAT + Port Forwarding)**.

#### 0.B.4 Obtener las IPs (si Bridge funciona)

| Máquina | Comando | IP Ejemplo |
|---|---|---|
| **VM Windows 7** | Abrir CMD → `ipconfig` → Anotar IPv4 | `192.168.1.10` |
| **VM Linux Mint** | Abrir terminal → `ip addr show` o `hostname -I` | `192.168.1.20` |
| **Host Win11 #1** | Abrir CMD → `ipconfig` → IPv4 | `192.168.1.100` |
| **Host Win11 #2** | Abrir CMD → `ipconfig` → IPv4 | `192.168.1.200` |
| **macOS** | Terminal → `ifconfig en0` o Preferencias del Sistema → Red | `192.168.1.150` |

> 📝 **ANOTA ESTAS IPs** — Las vas a necesitar en los siguientes pasos.

#### 0.B.5 Verificar conectividad (desde cada máquina)
```bash
# Desde la VM Windows 7, verificar que ve a Linux Mint:
ping <IP_VM_LINUX>

# Desde Linux Mint, verificar que ve a Windows 7:
ping <IP_VM_W7>

# Desde macOS, verificar que ve a Windows 7:
ping <IP_VM_W7>

# Desde Host Win11 #1, verificar que ve a Windows 7:
ping <IP_VM_W7>
```

> Si algún ping falla y usas WiFi, probablemente el Bridge no funciona con tu adaptador WiFi → **cambiar a Opción A**.

---

## Paso 1: Configurar VM Linux Mint — Base de Datos (Equipo 1)

### 1.1 Copiar archivos SQL a la VM
Copiar estos archivos a la VM Linux Mint (por USB, carpeta compartida VirtualBox, o SCP):
- `bingus_petstore2.sql` (esquema original)
- `migracion_v3.sql` (migración v3)
- `setup_linux_db.sh` (script de setup)

Colocarlos en `/tmp/`

### 1.2 Editar las variables del script
Antes de ejecutar, editar `setup_linux_db.sh`:
```bash
nano /tmp/setup_linux_db.sh

# Cambiar estas líneas con la IP REAL de tu VM Windows 7:
IP_WINDOWS7="192.168.1.10"       # ← IP real de la VM Windows 7
DB_PASSWORD="B1ngu5_S3cur3_2026" # ← Contraseña (puedes cambiarla)
```

### 1.3 Ejecutar script de setup
```bash
chmod +x /tmp/setup_linux_db.sh
sudo /tmp/setup_linux_db.sh
```

El script automáticamente:
- ✅ Instala MariaDB Server
- ✅ Configura MariaDB para escuchar en red (bind-address 0.0.0.0)
- ✅ Crea la BD `bingus_petstore2`
- ✅ Crea el usuario `bingus_app` con acceso solo desde la VM Windows 7
- ✅ Importa el esquema y la migración v3
- ✅ Configura el firewall (ufw) — solo permite puerto 3306 desde la VM Windows 7

### 1.4 Verificar
```bash
# Verificar que MariaDB escucha en todas las interfaces
sudo ss -tlnp | grep 3306

# Verificar firewall
sudo ufw status

# Verificar la base de datos
sudo mysql -u root -e "USE bingus_petstore2; SHOW TABLES;"
```

---

## Paso 2: Configurar VM Windows 7 — Lógica de Negocio (Equipo 2)

### 2.1 Instalar XAMPP
1. Descargar **XAMPP 8.0.x** compatible con Windows 7:
   - URL: https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/
   - Buscar una versión 8.0.x (ej: `xampp-windows-x64-8.0.30-0-VS16-installer.exe`)
   
   > ⚠️ **IMPORTANTE**: Las versiones 8.2+ requieren Windows 10+. Para Windows 7, usar 8.0.x o 8.1.x.

2. Instalar en `C:\xampp\`
3. Abrir XAMPP Control Panel → Iniciar **Apache** (NO es necesario iniciar MySQL, la BD está en Linux Mint)

### 2.2 Copiar el proyecto completo
1. Copiar toda la carpeta `bingus_petstore/` a `C:\xampp\htdocs\`
2. Estructura final debe ser:
```
C:\xampp\htdocs\bingus_petstore\
├── api\
│   ├── config\
│   │   ├── Config.php        ← Se edita en paso 2.3
│   │   └── Database.php
│   ├── controllers\
│   │   ├── AuthController.php
│   │   ├── ClienteController.php
│   │   ├── DashboardController.php
│   │   ├── PedidoController.php
│   │   ├── ProductoController.php
│   │   ├── RepartidorController.php
│   │   └── TiendaController.php
│   ├── helpers\
│   ├── models\
│   │   ├── AuthModel.php
│   │   ├── ClienteModel.php
│   │   ├── PedidoModel.php
│   │   ├── ProductoModel.php
│   │   ├── RepartidorModel.php
│   │   └── TiendaModel.php
│   └── index.php             ← Router central de la API
├── assets\
│   ├── css\
│   └── js\
├── views\
│   ├── admin\                ← Intranet de administración
│   ├── auth\                 ← Login de admin
│   ├── tienda\               ← Tienda pública
│   └── layouts\
├── uploads\
├── .htaccess
├── favicon.ico
└── index.php
```

### 2.3 Configurar conexión remota a la BD
Editar `C:\xampp\htdocs\bingus_petstore\api\config\Config.php`:

```php
// ============================================
// CONFIGURACIÓN DE RED — AJUSTAR CON TUS IPs
// ============================================
define('APP_HOST', '192.168.1.10');       // ← IP de ESTA máquina (VM Windows 7)
define('DB_HOST', '192.168.1.20');        // ← IP de la VM Linux Mint
define('DB_NAME', 'bingus_petstore2');
define('DB_USER', 'bingus_app');          // ← Usuario creado en el setup de Linux
define('DB_PASS', 'B1ngu5_S3cur3_2026'); // ← Contraseña del setup
```

> 📌 Reemplaza las IPs con las IPs **reales** que anotaste en el Paso 0.3

### 2.4 Configurar Apache para escuchar en red
Editar `C:\xampp\apache\conf\httpd.conf`:
```apache
# Buscar la línea Listen y asegurarse de que diga:
Listen 80
# NO debe decir: Listen 127.0.0.1:80  (eso bloquea acceso externo)
```

### 2.5 Habilitar mod_rewrite
En el mismo `httpd.conf`, verificar que esta línea NO esté comentada:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```
Y que el bloque `<Directory>` de htdocs tenga `AllowOverride All`:
```apache
<Directory "C:/xampp/htdocs">
    AllowOverride All
    Require all granted
</Directory>
```

### 2.6 Permitir Apache en Firewall de Windows 7
1. **Panel de Control** → **Firewall de Windows** → **Permitir un programa a través del Firewall**
2. Click en **Permitir otro programa...**
3. Navegar a: `C:\xampp\apache\bin\httpd.exe`
4. Marcar ambas casillas: **Doméstica/Trabajo** y **Pública**
5. Click **Aceptar**

Alternativa por CMD (como Administrador):
```cmd
netsh advfirewall firewall add rule name="Apache XAMPP" dir=in action=allow protocol=TCP localport=80
```

### 2.7 Reiniciar Apache
En XAMPP Control Panel → Stop → Start Apache

### 2.8 Verificar (desde la propia VM Windows 7)
```
Abrir navegador en la VM Windows 7:
http://localhost/bingus_petstore/api/

Debe mostrar: "API Bingus Petstore v3.0 funcionando"
```

Probar conexión a la BD:
```
http://localhost/bingus_petstore/api/tienda/catalogo

Debe mostrar el JSON del catálogo de productos
```

---

## Paso 3: Acceder desde el Equipo 1 — Intranet Admin (Host Windows 11 #1)

### 3.1 No requiere instalación
El Equipo 1 (Host Windows 11) solo necesita un navegador web. Abre Chrome, Edge o Firefox y accede a:

| Recurso | URL |
|---|---|
| **🔐 Intranet Admin (Login)** | `http://192.168.1.10/bingus_petstore/views/auth/login.php` |
| **📊 Dashboard** | `http://192.168.1.10/bingus_petstore/views/admin/dashboard.php` |
| **📦 Gestión Productos** | `http://192.168.1.10/bingus_petstore/views/admin/productos/listar.php` |
| **🚚 Gestión Repartidores** | `http://192.168.1.10/bingus_petstore/views/admin/repartidores/listar.php` |
| **📋 Gestión Pedidos** | `http://192.168.1.10/bingus_petstore/views/admin/pedidos/listar.php` |
| **🧪 API (prueba)** | `http://192.168.1.10/bingus_petstore/api/` |

> 📌 Reemplaza `192.168.1.10` con la IP real de tu VM Windows 7.

### 3.2 Credenciales de Admin
| Usuario | Contraseña |
|---|---|
| `cmorales` | `admin123` |

### 3.3 ¿Qué puede hacer el admin?
- Ver dashboard con estadísticas de ventas
- Gestionar productos (crear, editar, eliminar)
- Gestionar repartidores (crear hasta 2 por admin, cambiar disponibilidad)
- Ver y gestionar pedidos (cambiar estados, asignar repartidores)

---

## Paso 4: Acceder desde macOS — Tienda Online (Equipo 3)

### 4.1 No requiere instalación
El equipo macOS solo necesita un navegador (Safari, Chrome, Firefox). Abrir:

| Recurso | URL |
|---|---|
| **🛒 Tienda Online** | `http://192.168.1.10/bingus_petstore/views/tienda/tienda.php` |

> 📌 Reemplaza `192.168.1.10` con la IP real de tu VM Windows 7.

### 4.2 ¿Qué puede hacer el cliente desde macOS?
1. **Navegar el catálogo** — Ver productos con precios (IVA incluido)
2. **Buscar productos** — Barra de búsqueda en el navbar
3. **Filtrar por categoría** — Botones de filtro por categoría
4. **Agregar al carrito** — Click en "Agregar al Carrito" en cada producto
5. **Ver carrito** — Sidebar con desglose: Subtotal Neto + IVA 19% + Total
6. **Registrarse / Login** — Crear cuenta de cliente
7. **Hacer checkout** — Ingresar datos (nombre, RUT, dirección) y confirmar pedido
8. **Recibir confirmación** — El pedido queda como "PENDIENTE" para que el admin lo gestione

### 4.3 Flujo completo de compra
```
1. Cliente abre la tienda → http://<IP_VM_W7>/bingus_petstore/views/tienda/tienda.php
2. Navega productos → GET /api/tienda/catalogo (carga productos con IVA)
3. Agrega al carrito → JavaScript local (no toca servidor)
4. Click "Ir al Pago" → Se abre modal de checkout
5. Llena formulario → Nombre, RUT, Email, Teléfono, Dirección
6. Confirma pedido → POST /api/tienda/checkout
   → Backend valida datos
   → Backend calcula IVA 19%
   → Backend inserta en BD (Linux Mint): pedido + detalle_pedido
7. Respuesta exitosa → "¡Pedido creado! #12345"
8. Admin (Equipo 1) ve el pedido en el Dashboard → lo gestiona
```

---

## Paso 5: Verificación Integral (Prueba Completa)

### 5.1 Desde macOS — Crear un pedido
1. Abrir `http://<IP_VM_W7>/bingus_petstore/views/tienda/tienda.php`
2. Agregar 2 productos al carrito
3. Click "Ir al Pago"
4. Llenar formulario con datos de prueba
5. Click "Confirmar Pedido"
6. ✅ Debe aparecer mensaje de éxito con número de pedido

### 5.2 Desde Host Win11 #1 — Verificar en admin
1. Abrir `http://<IP_VM_W7>/bingus_petstore/views/auth/login.php`
2. Login: `cmorales` / `admin123`
3. Ir al Dashboard → Verificar que el pedido aparece
4. Ir a Pedidos → Ver el pedido creado desde macOS
5. Cambiar estado a "PAGADO"
6. Asignar un repartidor
7. Cambiar estado a "EN_REPARTO"
8. ✅ Todo debe funcionar sin errores

### 5.3 Desde Linux Mint — Verificar en BD
```bash
sudo mysql -u root -e "
USE bingus_petstore2;
SELECT id_pedido, nombre_cliente, total, estado
FROM pedidos
ORDER BY id_pedido DESC
LIMIT 5;
"
```

---

## Troubleshooting

### ❌ "No puedo ver la tienda desde macOS"
1. Verificar que Apache está corriendo en la VM Windows 7 (XAMPP → Apache → Running)
2. Verificar que el Firewall de Windows 7 permite el puerto 80
3. Verificar que la VM Windows 7 usa **Adaptador Puente** (no NAT)
4. Desde macOS, ejecutar: `ping <IP_VM_W7>` — debe responder
5. Si el ping funciona pero la web no, es firewall → desactivar temporalmente para probar

### ❌ "Error de conexión a BD" (desde la web)
1. En la VM Linux Mint: `sudo systemctl status mariadb` — debe estar active (running)
2. Verificar firewall: `sudo ufw status` — debe permitir 3306 desde la IP de W7
3. Probar conexión manual desde la VM Windows 7 (instalar MySQL client en XAMPP o usar el de XAMPP):
   ```cmd
   C:\xampp\mysql\bin\mysql.exe -h 192.168.1.20 -u bingus_app -p bingus_petstore2
   ```
4. Verificar que las IPs en `Config.php` son correctas

### ❌ "Las VMs no se ven entre sí (ping falla)" — Con Bridge
- **¿Estás usando WiFi en laptops?** → Este es probablemente el problema. **Cambiar a NAT + Port Forwarding** (Opción A del Paso 0)
- Verificar que **ambas VMs** usan **Adaptador Puente** en la misma interfaz de red del Host
- Verificar que el Host del Equipo 2 está en la misma red WiFi/LAN que el Equipo 1
- En la VM Windows 7, verificar que la red no está configurada como "Pública" → cambiar a "Red de trabajo"
- Desactivar temporalmente los firewalls de ambos para diagnosticar:
  ```cmd
  # Windows 7:
  netsh advfirewall set allprofiles state off
  ```
  ```bash
  # Linux Mint:
  sudo ufw disable
  ```

### ❌ "La VM no obtiene IP con Adaptador Puente" (problema típico en laptops WiFi)
1. **Causa raíz**: El driver WiFi del laptop no soporta modo promiscuo
2. **Diagnóstico rápido**: En la VM, ejecutar `ipconfig` (Windows) o `ip addr` (Linux). Si la IP es `169.254.x.x` o no hay IPv4, el Bridge no funciona
3. **Solución definitiva**: Cambiar a **NAT + Port Forwarding** (Opción A del Paso 0)
4. **Solución alternativa**: Comprar un adaptador USB-Ethernet (~$5-10 USD) y conectar por cable → Bridge funciona perfecto por Ethernet

### ❌ "Con NAT + Port Forwarding no puedo acceder desde macOS"
1. Verificar que el **Firewall de Windows 11 del Host** permite el puerto 8080 (ver paso 0.A.4)
2. Verificar que VirtualBox tiene las reglas de port forwarding correctas
3. Verificar que Apache está corriendo dentro de la VM (XAMPP → Apache → Running)
4. Probar desde el **propio Host** primero: `http://localhost:8080/bingus_petstore/api/`
   - Si esto funciona pero macOS no puede acceder → es firewall del Host
   - Si esto no funciona → la regla de port forwarding está mal

### ❌ "Con NAT, la web carga pero no conecta a la BD"
1. Verificar que `Config.php` usa la **IP del Host Win11 #1** (no la IP de la VM Linux Mint):
   ```php
   // ❌ INCORRECTO con NAT:
   define('DB_HOST', '10.0.2.15');  // IP interna de la VM — no accesible desde otra VM

   // ✅ CORRECTO con NAT:
   define('DB_HOST', '192.168.1.100');  // IP del HOST que tiene la VM Linux Mint
   ```
2. Verificar que el Firewall del Host Win11 #1 permite puerto 3306
3. Verificar que MariaDB está corriendo: `sudo systemctl status mariadb`
4. Verificar que el usuario MySQL permite conexiones desde la IP que llega:
   ```bash
   sudo mysql -u root -e "SELECT user, host FROM mysql.user WHERE user='bingus_app';"
   # Debe mostrar '%' o la subred correcta en la columna host
   ```

### ❌ "CORS o API no funciona"
- La API ya incluye headers CORS (`Access-Control-Allow-Origin: *`)
- Si hay problemas, verificar que mod_rewrite está habilitado en Apache
- Verificar que `.htaccess` existe en `C:\xampp\htdocs\bingus_petstore\`
- **Con NAT + Port Forwarding**: Verificar que `APP_HOST` en `Config.php` refleje la IP/puerto correctos

### ❌ "macOS no puede conectarse a ninguna máquina"
- Verificar que el macOS está en la **misma red WiFi** que los otros equipos
- Desde macOS: `ifconfig en0` → verificar que la IP está en la misma subred (192.168.1.x)
- Si usan redes WiFi distintas (por ejemplo, 5GHz vs 2.4GHz), pueden tener subredes distintas
- Solución: Conectar todos los equipos a la misma banda WiFi
- **Con NAT**: Hacer ping a la IP del **Host** (no de la VM): `ping 192.168.1.200`

### ❌ "Las imágenes de productos no se ven"
- Las imágenes se guardan en `C:\xampp\htdocs\bingus_petstore\uploads\productos\`
- Verificar que la carpeta tiene permisos de lectura
- Si migraste desde otro XAMPP, copiar también la carpeta `uploads/`

---

## Credenciales del Sistema

| Componente | Usuario | Contraseña |
|---|---|---|
| MariaDB (root, Linux) | `root` | *(sin contraseña por default)* |
| MariaDB (app, remoto) | `bingus_app` | `B1ngu5_S3cur3_2026` |
| Admin (Web) | `cmorales` | `admin123` |

> ⚠️ **IMPORTANTE:** Estas son credenciales de desarrollo. En producción, cambiar todas las contraseñas.

---

## Resumen de Puertos y Servicios

### Con NAT + Port Forwarding (⭐ Recomendada para laptops)

| Máquina expuesta | Servicio | Puerto real (VM) | Puerto expuesto (Host) | Accesible desde |
|---|---|---|---|---|
| Host Win11 #2 (VM W7) | Apache (HTTP) | 80 | **8080** | Todos los equipos de la red WiFi |
| Host Win11 #1 (VM Mint) | MariaDB (MySQL) | 3306 | **3306** | Todos (controlado por usuario MySQL) |
| Host Win11 #1 (VM Mint) | SSH | 22 | **2222** | Todos (para administración) |

### Con Adaptador Puente (si funciona)

| Máquina | Servicio | Puerto | Accesible desde |
|---|---|---|---|
| VM Windows 7 | Apache (HTTP) | 80 | Todos los equipos de la red |
| VM Linux Mint | MariaDB (MySQL) | 3306 | Solo VM Windows 7 (firewall) |
| VM Linux Mint | SSH | 22 | Todos (para administración) |

---

## Checklist Final

### Si usas NAT + Port Forwarding (⭐ Recomendada):
- [ ] Todas las laptops están en la misma red WiFi
- [ ] VM Linux Mint tiene adaptador en NAT con port forwarding (3306→3306, 22→2222)
- [ ] VM Windows 7 tiene adaptador en NAT con port forwarding (80→8080)
- [ ] Firewall del Host Win11 #1 permite puerto 3306
- [ ] Firewall del Host Win11 #2 permite puerto 8080
- [ ] VM Linux Mint tiene MariaDB corriendo con usuario `bingus_app` usando host `%`
- [ ] VM Windows 7 tiene XAMPP con Apache corriendo
- [ ] `Config.php` tiene `DB_HOST` apuntando a la IP del **Host Win11 #1**
- [ ] `Config.php` tiene `APP_HOST` apuntando a la IP del **Host Win11 #2**
- [ ] Desde el Host Win11 #2: `http://localhost:8080/bingus_petstore/api/` funciona
- [ ] Desde macOS se puede acceder a `http://<IP_HOST_W11_2>:8080/bingus_petstore/views/tienda/tienda.php`
- [ ] Desde Host Win11 #1 se puede acceder al login de admin vía `http://<IP_HOST_W11_2>:8080/...`
- [ ] Un pedido creado desde macOS aparece en el dashboard del admin

### Si usas Adaptador Puente:
- [ ] Todas las máquinas están en la misma red WiFi/LAN
- [ ] Las VMs obtienen IP en la misma subred (192.168.1.x) — si no, cambiar a NAT
- [ ] VM Linux Mint tiene MariaDB corriendo y firewall configurado
- [ ] VM Windows 7 tiene XAMPP con Apache corriendo
- [ ] `Config.php` tiene las IPs correctas de la VM W7 y la VM Linux Mint
- [ ] Firewall de Windows 7 permite puerto 80
- [ ] Desde macOS se puede hacer ping a la VM Windows 7
- [ ] Desde el Host Win11 #1 se puede acceder al login de admin
- [ ] Desde macOS se puede acceder a la tienda online
- [ ] Un pedido creado desde macOS aparece en el dashboard del admin

