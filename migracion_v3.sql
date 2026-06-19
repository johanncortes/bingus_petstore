-- ============================================
-- MIGRACIÓN v3.0 — Bingus Petstore
-- ============================================
-- Limpieza total + Vendedores → Repartidores + IVA
-- Ejecutar sobre la BD ORIGINAL: bingus_petstore2
-- (Importar bingus_petstore2.sql primero, luego ejecutar esto)
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- PASO 1: LIMPIEZA TOTAL (Borrón y cuenta nueva)
-- ============================================

-- Eliminar todos los detalles de pedidos
DELETE FROM detalle_pedido;
ALTER TABLE detalle_pedido AUTO_INCREMENT = 1;

-- Eliminar todos los pedidos
DELETE FROM pedidos;
ALTER TABLE pedidos AUTO_INCREMENT = 1;

-- Eliminar todos los clientes
DELETE FROM clientes;
ALTER TABLE clientes AUTO_INCREMENT = 1;

-- Limpiar auditoría
DELETE FROM auditoria_cambios;
ALTER TABLE auditoria_cambios AUTO_INCREMENT = 1;

-- ============================================
-- PASO 2: RENOMBRAR VENDEDORES → REPARTIDORES
-- ============================================

-- 2.1 Eliminar FK de pedidos → vendedores
ALTER TABLE pedidos DROP FOREIGN KEY `pedidos_ibfk_2`;

-- 2.2 Eliminar la vista que depende de vendedores
DROP VIEW IF EXISTS v_pedidos_detalle;
DROP VIEW IF EXISTS v_catalogo_pos;

-- 2.3 Eliminar los datos actuales de vendedores
DELETE FROM vendedores;

-- 2.4 Eliminar FK de vendedores → administradores (ANTES de renombrar)
ALTER TABLE vendedores DROP FOREIGN KEY `vendedores_ibfk_1`;

-- 2.5 Renombrar la tabla
RENAME TABLE vendedores TO repartidores;

-- 2.6 Renombrar columna id_vendedor → id_repartidor
ALTER TABLE repartidores CHANGE `id_vendedor` `id_repartidor` INT(11) NOT NULL AUTO_INCREMENT;

-- 2.7 Eliminar columnas de login (ya no necesitan acceso al sistema)
ALTER TABLE repartidores DROP COLUMN `contrasena`;
ALTER TABLE repartidores DROP COLUMN `usuario`;

-- 2.8 Agregar columna de disponibilidad
ALTER TABLE repartidores ADD COLUMN `estado_disponibilidad` ENUM('DISPONIBLE','EN_REPARTO','INACTIVO') NOT NULL DEFAULT 'DISPONIBLE' AFTER `activo`;

-- 2.9 Renombrar índices
ALTER TABLE repartidores DROP INDEX `email`;
ALTER TABLE repartidores DROP INDEX `idx_rut_vendedor`;
ALTER TABLE repartidores ADD UNIQUE KEY `email` (`email`);
ALTER TABLE repartidores ADD UNIQUE KEY `idx_rut_repartidor` (`rut`);

-- 2.10 Recrear FK con nombre estandarizado
ALTER TABLE repartidores ADD CONSTRAINT `repartidores_ibfk_1` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`) ON UPDATE CASCADE;

-- 2.11 Reset AUTO_INCREMENT
ALTER TABLE repartidores AUTO_INCREMENT = 1;

-- ============================================
-- PASO 3: ACTUALIZAR TABLA PEDIDOS
-- ============================================

-- 3.1 Renombrar columna id_vendedor → id_repartidor
ALTER TABLE pedidos CHANGE `id_vendedor` `id_repartidor` INT(11) NOT NULL;

-- 3.2 Agregar nuevas columnas
ALTER TABLE pedidos ADD COLUMN `direccion_entrega` VARCHAR(200) DEFAULT NULL AFTER `estado`;
ALTER TABLE pedidos ADD COLUMN `subtotal_neto` DECIMAL(10,2) DEFAULT 0.00 AFTER `total`;
ALTER TABLE pedidos ADD COLUMN `total_iva` DECIMAL(10,2) DEFAULT 0.00 AFTER `subtotal_neto`;

-- 3.3 Modificar campo estado para soportar nuevos estados
ALTER TABLE pedidos MODIFY COLUMN `estado` VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE';

-- 3.4 Recrear FK a repartidores (permitir NULL para pedidos online sin repartidor asignado)
ALTER TABLE pedidos MODIFY COLUMN `id_repartidor` INT(11) DEFAULT NULL;

-- 3.5 Renombrar índice
ALTER TABLE pedidos DROP INDEX `id_vendedor`;
ALTER TABLE pedidos ADD KEY `id_repartidor` (`id_repartidor`);

-- 3.6 Recrear FK a repartidores
ALTER TABLE pedidos ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id_repartidor`);

-- ============================================
-- PASO 4: ACTUALIZAR TABLA DETALLE_PEDIDO (IVA)
-- ============================================

ALTER TABLE detalle_pedido ADD COLUMN `precio_neto` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `precio_unitario`;
ALTER TABLE detalle_pedido ADD COLUMN `iva` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `precio_neto`;

-- ============================================
-- PASO 5: CREAR TABLA DE CONFIGURACIÓN DE IMPUESTOS
-- ============================================

CREATE TABLE IF NOT EXISTS `configuracion_impuestos` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(50) NOT NULL,
    `porcentaje` DECIMAL(5,2) NOT NULL,
    `activo` TINYINT(1) DEFAULT 1,
    `fecha_vigencia` DATE NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `configuracion_impuestos` (`nombre`, `porcentaje`, `activo`, `fecha_vigencia`) VALUES
('IVA', 19.00, 1, '2026-01-01');

-- ============================================
-- PASO 6: INSERTAR REPARTIDORES (2 por admin = 10 total)
-- ============================================

INSERT INTO `repartidores` (`id_repartidor`, `nombre`, `rut`, `email`, `telefono`, `fecha_contratacion`, `id_administrador`, `activo`, `estado_disponibilidad`) VALUES
-- Admin 1: Carlos Morales
(1, 'Sandra Quiroga', '20780562-2', 'sandra.q@bingus.cl', '+56 9 7890 2374', '2025-12-08', 1, 1, 'DISPONIBLE'),
(2, 'Laura Gómez', '17354823-K', 'laura.g@bingus.cl', '+56 9 7023 7723', '2025-12-08', 1, 1, 'DISPONIBLE'),
-- Admin 2: Lucía Herrera
(3, 'Nicolás Guerrero', '20890111-9', 'nicolas.g@bingus.cl', '+56 9 1789 3738', '2025-12-08', 2, 1, 'DISPONIBLE'),
(4, 'Camila Reyes', '19234567-3', 'camila.r@bingus.cl', '+56 9 8123 4567', '2026-06-11', 2, 1, 'DISPONIBLE'),
-- Admin 3: Javier Soto
(5, 'Diego Fernández', '18765432-1', 'diego.f@bingus.cl', '+56 9 6234 5678', '2026-06-11', 3, 1, 'DISPONIBLE'),
(6, 'Valentina Muñoz', '21345678-5', 'valentina.m@bingus.cl', '+56 9 5345 6789', '2026-06-11', 3, 1, 'DISPONIBLE'),
-- Admin 4: Fernanda Rivas
(7, 'Matías Araya', '19876543-2', 'matias.a@bingus.cl', '+56 9 4456 7890', '2026-06-11', 4, 1, 'DISPONIBLE'),
(8, 'Isidora Castro', '20567890-4', 'isidora.c@bingus.cl', '+56 9 3567 8901', '2026-06-11', 4, 1, 'DISPONIBLE'),
-- Admin 5: Andrés Pino
(9, 'Tomás Vargas', '18234567-8', 'tomas.v@bingus.cl', '+56 9 2678 9012', '2026-06-11', 5, 1, 'DISPONIBLE'),
(10, 'Francisca López', '21678901-6', 'francisca.l@bingus.cl', '+56 9 1789 0123', '2026-06-11', 5, 1, 'DISPONIBLE');

ALTER TABLE repartidores AUTO_INCREMENT = 11;

-- ============================================
-- PASO 7: RECREAR VISTAS
-- ============================================

-- Vista de pedidos con detalle (actualizada para repartidores)
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedidos_detalle` AS
SELECT 
    p.id_pedido, p.fecha, p.estado, p.total, p.subtotal_neto, p.total_iva,
    p.direccion_entrega,
    c.nombre AS cliente_nombre, c.rut AS cliente_rut,
    COALESCE(r.nombre, 'Sin asignar') AS repartidor_nombre
FROM pedidos p
LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN repartidores r ON p.id_repartidor = r.id_repartidor;

-- Vista de catálogo para tienda online (reemplaza v_catalogo_pos)
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_catalogo_tienda` AS
SELECT 
    p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.imagen,
    c.nombre AS categoria_nombre, c.id_categoria
FROM productos p
JOIN categorias_productos c ON p.id_categoria = c.id_categoria
WHERE p.activo = 1 AND p.stock > 0;

-- ============================================
-- PASO 8: RECREAR STORED PROCEDURES
-- ============================================

DROP PROCEDURE IF EXISTS sp_dashboard_stats;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_dashboard_stats` (IN `_id_admin` INT)
BEGIN
    SELECT 
        -- Productos activos (Global)
        (SELECT COUNT(*) FROM productos WHERE activo = 1) as total_productos,
        
        -- Repartidores activos del admin
        (SELECT COUNT(*) FROM repartidores WHERE activo = 1 AND id_administrador = _id_admin) as total_repartidores,
        
        -- Pedidos totales (Global)
        (SELECT COUNT(*) FROM pedidos) as total_pedidos,
        
        -- Pedidos pendientes de reparto (Global)
        (SELECT COUNT(*) FROM pedidos WHERE estado = 'PAGADO' AND id_repartidor IS NULL) as pedidos_sin_repartidor,
        
        -- Pedidos en reparto (Global)
        (SELECT COUNT(*) FROM pedidos WHERE estado = 'EN_REPARTO') as pedidos_en_reparto;
END$$
DELIMITER ;

-- ============================================
-- PASO 9: TRIGGER — Máximo 2 repartidores por admin
-- ============================================

DELIMITER $$
CREATE TRIGGER `trg_max_repartidores_insert` BEFORE INSERT ON `repartidores` FOR EACH ROW
BEGIN
    DECLARE v_count INT;
    
    IF NEW.activo = 1 THEN
        SELECT COUNT(*) INTO v_count 
        FROM repartidores 
        WHERE id_administrador = NEW.id_administrador AND activo = 1;
        
        IF v_count >= 2 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: Cada administrador puede tener máximo 2 repartidores activos.';
        END IF;
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `trg_max_repartidores_update` BEFORE UPDATE ON `repartidores` FOR EACH ROW
BEGIN
    DECLARE v_count INT;
    
    -- Solo validar si se está activando un repartidor o cambiando de admin
    IF NEW.activo = 1 AND (OLD.activo = 0 OR OLD.id_administrador != NEW.id_administrador) THEN
        SELECT COUNT(*) INTO v_count 
        FROM repartidores 
        WHERE id_administrador = NEW.id_administrador AND activo = 1
        AND id_repartidor != NEW.id_repartidor;
        
        IF v_count >= 2 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: Cada administrador puede tener máximo 2 repartidores activos.';
        END IF;
    END IF;
END$$
DELIMITER ;

-- ============================================
-- PASO 10: RE-HABILITAR FOREIGN KEY CHECKS
-- ============================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- MIGRACIÓN COMPLETADA
-- ============================================
-- Resultado esperado:
-- ✅ Pedidos, clientes, auditoría → VACIOS
-- ✅ vendedores → repartidores (10 registros, 2 por admin)
-- ✅ Tabla configuracion_impuestos con IVA 19%
-- ✅ Pedidos: nuevos campos (id_repartidor, direccion_entrega, IVA)
-- ✅ Detalle pedido: campos precio_neto e iva
-- ✅ Vistas recreadas para repartidores
-- ✅ SP dashboard actualizado
-- ✅ Trigger límite 2 repartidores por admin
-- ============================================
