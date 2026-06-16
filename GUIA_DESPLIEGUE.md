# 🐾 Guía de Despliegue — Bingus Petstore v3.0
## Arquitectura Distribuida en 3 Máquinas (VirtualBox)

---

## Diagrama de Arquitectura

```
┌─────────────────────────────────────────┐
│     Máquina 3: Windows/Mac (Host)       │
│     🖥️  Navegador Web                   │
│     → Tienda Online + Intranet Admin    │
│     → Accede vía http://<IP_VM1>/...    │
└──────────────┬──────────────────────────┘
               │ HTTP (Puerto 80)
               ▼
┌─────────────────────────────────────────┐
│     Máquina 1: Windows 7 VM            │
│     ⚙️  Apache + PHP 8.x (XAMPP)       │
│     → Lógica de negocio completa       │
│     → Código en C:\xampp\htdocs\       │
│     → Red: Adaptador puente            │
└──────────────┬──────────────────────────┘
               │ MySQL (Puerto 3306)
               ▼
┌─────────────────────────────────────────┐
│     Máquina 2: Linux Mint VM           │
│     🛡️  MariaDB + Firewall (ufw)       │
│     → Base de datos bingus_petstore2   │
│     → Solo acepta conexiones de VM1    │
│     → Red: Adaptador puente            │
└─────────────────────────────────────────┘
```

---

## Paso 0: Configuración de Red en VirtualBox

Ambas VMs deben estar en **modo Adaptador Puente** para que se vean entre sí y desde el host.

### Configurar cada VM:
1. Seleccionar la VM → **Configuración** → **Red**
2. Adaptador 1 → **Conectado a: Adaptador puente**
3. Nombre: Seleccionar la interfaz de red real del host (WiFi o Ethernet)
4. Aceptar y arrancar la VM

### Obtener IPs:
- **Windows 7 VM:** Abrir CMD → `ipconfig` → Anotar IPv4 (ej: `192.168.1.10`)
- **Linux Mint VM:** Abrir terminal → `ip addr show` → Anotar IPv4 (ej: `192.168.1.20`)
- **Host:** Abrir CMD/Terminal → `ipconfig` / `ifconfig` → Anotar IPv4

### Verificar conectividad:
```bash
# Desde cualquier máquina, hacer ping a las otras:
ping 192.168.1.10   # Windows 7 VM
ping 192.168.1.20   # Linux Mint VM
```

---

## Paso 1: Configurar Máquina 2 (Linux Mint VM — Base de Datos)

### 1.1 Copiar archivos SQL a la VM
Copiar estos archivos a la VM Linux Mint (por USB, carpeta compartida, o SCP):
- `bingus_petstore2.sql` (esquema original)
- `migracion_v3.sql` (migración v3)

Colocarlos en `/tmp/`

### 1.2 Ejecutar script de setup
```bash
# Copiar setup_linux_db.sh a la VM
# IMPORTANTE: Editar las variables al inicio del script:
#   IP_WINDOWS7="192.168.1.10"   ← IP real de tu VM Windows 7
#   DB_PASSWORD="tu_contraseña"   ← Contraseña segura

chmod +x setup_linux_db.sh
sudo ./setup_linux_db.sh
```

### 1.3 Verificar
```bash
# Verificar que MariaDB escucha en todas las interfaces
sudo ss -tlnp | grep 3306

# Verificar firewall
sudo ufw status
```

---

## Paso 2: Configurar Máquina 1 (Windows 7 VM — Lógica de Negocio)

### 2.1 Instalar XAMPP
1. Descargar XAMPP para Windows (versión 8.0.x compatible con Windows 7)
   - URL: https://sourceforge.net/projects/xampp/files/
2. Instalar en `C:\xampp\`
3. Abrir XAMPP Control Panel → Iniciar **Apache**

### 2.2 Copiar el proyecto
1. Copiar toda la carpeta `bingus_petstore/` a `C:\xampp\htdocs\`
2. Estructura final: `C:\xampp\htdocs\bingus_petstore\`

### 2.3 Configurar conexión remota a BD
Editar `C:\xampp\htdocs\bingus_petstore\api\config\Config.php`:

```php
// Cambiar estas líneas:
define('APP_HOST', '192.168.1.10');          // ← IP de ESTA máquina (Windows 7)
define('DB_HOST', '192.168.1.20');           // ← IP de la VM Linux Mint
define('DB_NAME', 'bingus_petstore2');
define('DB_USER', 'bingus_app');             // ← Usuario creado en Linux
define('DB_PASS', 'B1ngu5_S3cur3_2026');     // ← Contraseña del setup
```

### 2.4 Configurar Apache para escuchar en red
Editar `C:\xampp\apache\conf\httpd.conf`:
```apache
# Buscar la línea:
Listen 80
# Asegurarse de que NO diga: Listen 127.0.0.1:80
# Debe decir simplemente: Listen 80
```

### 2.5 Permitir Apache en Firewall de Windows 7
1. Panel de Control → Firewall de Windows → Permitir un programa
2. Agregar: `C:\xampp\apache\bin\httpd.exe`
3. Marcar ambas casillas (Dominio y Público)

### 2.6 Verificar
```
# Desde la VM Windows 7, abrir navegador:
http://localhost/bingus_petstore/api/
# Debe mostrar: "API Bingus Petstore v3.0 funcionando"
```

---

## Paso 3: Acceder desde Máquina 3 (Host — Windows/Mac)

### 3.1 Accesos
No necesita instalación. Solo abrir el navegador:

| Recurso | URL |
|---|---|
| **Tienda Online** | `http://192.168.1.10/bingus_petstore/views/tienda/tienda.php` |
| **Intranet Admin** | `http://192.168.1.10/bingus_petstore/views/auth/login.php` |
| **API (prueba)** | `http://192.168.1.10/bingus_petstore/api/` |

> **Nota:** Reemplaza `192.168.1.10` con la IP real de tu VM Windows 7.

---

## Troubleshooting

### ❌ "Error de conexión a BD"
- Verificar que MariaDB esté corriendo en la VM Linux: `sudo systemctl status mariadb`
- Verificar que el firewall permite la conexión: `sudo ufw status`
- Probar conexión manual desde Windows 7: `mysql -h 192.168.1.20 -u bingus_app -p`

### ❌ "No se puede acceder desde el navegador del Host"
- Verificar que Apache esté corriendo en la VM Windows 7
- Verificar que el Firewall de Windows 7 permite el puerto 80
- Verificar que las VMs usan Adaptador Puente (no NAT)

### ❌ "Las VMs no se ven entre sí"
- Verificar que ambas usan Adaptador Puente en la MISMA interfaz de red
- Probar con `ping` entre las máquinas
- Desactivar temporalmente los firewalls para diagnosticar

### ❌ "CORS o API no funciona"
- La API ya incluye headers CORS (`Access-Control-Allow-Origin: *`)
- Si hay problemas, verificar que `.htaccess` funcione (mod_rewrite habilitado)

---

## Credenciales del Sistema

| Componente | Usuario | Contraseña |
|---|---|---|
| MariaDB (root, Linux) | `root` | *(sin contraseña por default)* |
| MariaDB (app, remoto) | `bingus_app` | `B1ngu5_S3cur3_2026` |
| Admin (Web) | `cmorales` | `admin123` |

> ⚠️ **IMPORTANTE:** Estas son credenciales de desarrollo. En producción, cambiar todas las contraseñas.
