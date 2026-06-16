#!/bin/bash
# ============================================
# SETUP — Máquina 2: Linux Mint VM
# Base de Datos + Seguridad
# ============================================
# Ejecutar como root o con sudo:
#   chmod +x setup_linux_db.sh
#   sudo ./setup_linux_db.sh
# ============================================

set -e

echo "=========================================="
echo "🐾 Bingus Petstore — Setup Linux Mint VM"
echo "=========================================="

# ============================================
# 1. INSTALAR MariaDB
# ============================================
echo ""
echo "[1/6] Instalando MariaDB Server..."
apt update -y
apt install -y mariadb-server mariadb-client ufw

echo "✅ MariaDB instalado."

# ============================================
# 2. CONFIGURAR MariaDB PARA ESCUCHAR EN RED
# ============================================
echo ""
echo "[2/6] Configurando MariaDB para aceptar conexiones remotas..."

# Cambiar bind-address a 0.0.0.0 para escuchar en todas las interfaces
CONF_FILE="/etc/mysql/mariadb.conf.d/50-server.cnf"
if [ -f "$CONF_FILE" ]; then
    sed -i 's/^bind-address\s*=.*/bind-address = 0.0.0.0/' "$CONF_FILE"
    echo "✅ bind-address configurado a 0.0.0.0 en $CONF_FILE"
else
    echo "⚠️  Archivo $CONF_FILE no encontrado. Busca tu archivo de configuración de MariaDB."
    echo "   Debe contener: bind-address = 0.0.0.0"
fi

# Reiniciar MariaDB
systemctl restart mariadb
echo "✅ MariaDB reiniciado."

# ============================================
# 3. CREAR BASE DE DATOS Y USUARIO REMOTO
# ============================================
echo ""
echo "[3/6] Creando base de datos y usuario..."

# === MODIFICAR ESTAS VARIABLES ===
IP_WINDOWS7="192.168.1.10"  # ← Cambiar a la IP de la VM Windows 7
DB_PASSWORD="B1ngu5_S3cur3_2026"  # ← Cambiar a una contraseña segura
# ==================================

mysql -u root <<EOF
-- Crear base de datos (si no existe)
CREATE DATABASE IF NOT EXISTS bingus_petstore2 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Crear usuario con acceso solo desde la VM Windows 7
CREATE USER IF NOT EXISTS 'bingus_app'@'${IP_WINDOWS7}' IDENTIFIED BY '${DB_PASSWORD}';

-- Permisos mínimos (SELECT, INSERT, UPDATE, DELETE, EXECUTE)
-- No incluye: DROP, ALTER, GRANT, CREATE (seguridad)
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON bingus_petstore2.* TO 'bingus_app'@'${IP_WINDOWS7}';
FLUSH PRIVILEGES;

-- Verificar
SELECT User, Host FROM mysql.user WHERE User = 'bingus_app';
EOF

echo "✅ Usuario 'bingus_app' creado con acceso desde ${IP_WINDOWS7}."
echo "   Contraseña: ${DB_PASSWORD}"

# ============================================
# 4. IMPORTAR BASE DE DATOS
# ============================================
echo ""
echo "[4/6] Importando base de datos..."
echo "   ⚠️  Debes copiar los archivos SQL a esta máquina primero."
echo ""

# Si los archivos están en /tmp o en un directorio compartido:
if [ -f "/tmp/bingus_petstore2.sql" ]; then
    mysql -u root bingus_petstore2 < /tmp/bingus_petstore2.sql
    echo "✅ Esquema base importado."
else
    echo "⏭️  Archivo /tmp/bingus_petstore2.sql no encontrado. Impórtalo manualmente:"
    echo "   mysql -u root bingus_petstore2 < /ruta/a/bingus_petstore2.sql"
fi

if [ -f "/tmp/migracion_v3.sql" ]; then
    mysql -u root bingus_petstore2 < /tmp/migracion_v3.sql
    echo "✅ Migración v3 aplicada."
else
    echo "⏭️  Archivo /tmp/migracion_v3.sql no encontrado. Aplícalo manualmente:"
    echo "   mysql -u root bingus_petstore2 < /ruta/a/migracion_v3.sql"
fi

# ============================================
# 5. CONFIGURAR FIREWALL (UFW)
# ============================================
echo ""
echo "[5/6] Configurando firewall..."

ufw --force enable

# Permitir SSH (para administración remota)
ufw allow ssh

# Permitir MySQL SOLO desde la VM Windows 7
ufw allow from ${IP_WINDOWS7} to any port 3306 comment "MySQL desde VM Windows 7"

# Denegar MySQL desde cualquier otra IP
ufw deny 3306 comment "Bloquear MySQL externo"

echo "✅ Firewall configurado:"
ufw status verbose

# ============================================
# 6. VERIFICACIÓN FINAL
# ============================================
echo ""
echo "[6/6] Verificación..."
echo ""

# Verificar que MariaDB está corriendo
systemctl status mariadb --no-pager -l | head -5

echo ""
echo "=========================================="
echo "✅ SETUP COMPLETADO"
echo "=========================================="
echo ""
echo "📋 Resumen:"
echo "   BD: bingus_petstore2"
echo "   Usuario: bingus_app"
echo "   Acceso permitido desde: ${IP_WINDOWS7}"
echo "   Puerto: 3306"
echo ""
echo "📌 Siguiente paso:"
echo "   En Config.php de la VM Windows 7, configurar:"
echo "   define('DB_HOST', '$(hostname -I | awk '{print $1}')');  // IP de esta máquina"
echo "   define('DB_USER', 'bingus_app');"
echo "   define('DB_PASS', '${DB_PASSWORD}');"
echo ""
echo "🧪 Para probar la conexión desde Windows 7:"
echo "   mysql -h $(hostname -I | awk '{print $1}') -u bingus_app -p bingus_petstore2"
echo "=========================================="
