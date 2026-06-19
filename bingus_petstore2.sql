-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-12-2025 a las 23:45:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bingus_petstore2`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_dashboard_stats` (IN `_id_admin` INT)   BEGIN
    SELECT 
        -- Productos (Globales)
        (SELECT COUNT(*) FROM productos WHERE activo = 1) as total_productos,
        
        -- Vendedores (FILTRADO POR TU ID DE ADMIN)
        (SELECT COUNT(*) FROM vendedores WHERE activo = 1 AND id_administrador = _id_admin) as total_vendedores,
        
        -- Pedidos (Globales - Histórico total)
        (SELECT COUNT(*) FROM pedidos) as total_pedidos;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_historial_cliente` (IN `_rut` VARCHAR(12))   BEGIN
    SELECT p.id_pedido, p.fecha, p.total, p.estado
    FROM pedidos p
    JOIN clientes c ON p.id_cliente = c.id_cliente
    WHERE c.rut = _rut
    ORDER BY p.fecha DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_stock_critico` (IN `_limite` INT)   BEGIN
    SELECT nombre, stock, precio 
    FROM productos 
    WHERE stock <= _limite AND activo = 1;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_administrador` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rut` varchar(12) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_administrador`, `nombre`, `rut`, `email`, `telefono`, `usuario`, `contrasena`, `fecha_creacion`, `activo`) VALUES
(1, 'Carlos Morales', '8706234-2', 'carlos.morales@tiendamascotas.cl', '+56 9 1111 1111', 'cmorales', 'bingus2025', '2025-11-19 13:51:36', 1),
(2, 'Lucía Herrera', '19560347-1', 'lucia.herrera@tiendamascotas.cl', '+56 9 2222 2222', 'lherrera', 'bingus2025', '2025-11-19 13:51:36', 1),
(3, 'Javier Soto', '14902634-1', 'javier.soto@tiendamascotas.cl', '+56 9 3333 3333', 'jsoto', 'bingus2025', '2025-11-19 13:51:36', 1),
(4, 'Fernanda Rivas', '18457823-0', 'fernanda.rivas@tiendamascotas.cl', '+56 9 4444 4444', 'frivas', 'bingus2025', '2025-11-19 13:51:36', 1),
(5, 'Andrés Pino', '20003637-k', 'andres.pino@tiendamascotas.cl', '+56 9 5555 5555', 'apino', 'bingus2025', '2025-11-19 13:51:36', 1);

--
-- Disparadores `administradores`
--
DELIMITER $$
CREATE TRIGGER `proteger_superadmin_delete` BEFORE DELETE ON `administradores` FOR EACH ROW BEGIN
    -- Si intentan borrar al admin 1
    IF OLD.id_administrador = 1 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Acción Denegada: No se puede eliminar al Administrador Principal del sistema.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_cambios`
--

CREATE TABLE `auditoria_cambios` (
  `id_auditoria` int(11) NOT NULL,
  `tabla_afectada` varchar(50) NOT NULL,
  `tipo_operacion` varchar(10) NOT NULL,
  `id_registro` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `usuario_sistema` varchar(100) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria_cambios`
--

INSERT INTO `auditoria_cambios` (`id_auditoria`, `tabla_afectada`, `tipo_operacion`, `id_registro`, `descripcion`, `usuario_sistema`, `fecha_cambio`) VALUES
(4, 'productos', 'DELETE', 11, 'Producto eliminado: ID=11, Nombre=\"Churu pescado\", Precio=3500.00, Stock=25, Categoría ID=3, Proveedor ID=5', 'root@localhost', '2025-12-05 16:54:29'),
(8, 'productos', 'DELETE', 12, 'Producto eliminado: ID=12, Nombre=\"Juguete Rana para Perro\", Precio=8000.00, Stock=20, Categoría ID=5, Proveedor ID=2', 'root@localhost', '2025-12-05 16:56:14'),
(17, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-05 18:34:47'),
(18, 'productos', 'UPDATE', 8, 'Producto actualizado. ', 'root@localhost', '2025-12-05 19:39:28'),
(19, 'productos', 'UPDATE', 4, 'Producto actualizado. ', 'root@localhost', '2025-12-05 19:39:55'),
(20, 'productos', 'UPDATE', 4, 'Producto actualizado. ', 'root@localhost', '2025-12-05 20:06:05'),
(21, 'productos', 'UPDATE', 8, 'Producto actualizado. ', 'root@localhost', '2025-12-05 20:06:14'),
(22, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-05 20:06:24'),
(23, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-05 20:06:53'),
(24, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-05 20:07:07'),
(25, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-05 20:07:17'),
(26, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-05 20:07:27'),
(27, 'productos', 'UPDATE', 4, 'Producto actualizado. Stock: 60 → 59. ', 'root@localhost', '2025-12-07 03:55:36'),
(28, 'productos', 'UPDATE', 1, 'Producto actualizado. Stock: 50 → 49. ', 'root@localhost', '2025-12-07 03:55:36'),
(29, 'productos', 'UPDATE', 6, 'Producto actualizado. Stock: 70 → 69. ', 'root@localhost', '2025-12-07 03:55:36'),
(30, 'productos', 'UPDATE', 1, 'Producto actualizado. ', 'root@localhost', '2025-12-07 03:57:21'),
(31, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-07 04:23:18'),
(32, 'productos', 'UPDATE', 10, 'Producto actualizado. ', 'root@localhost', '2025-12-07 04:25:19'),
(33, 'productos', 'UPDATE', 10, 'Producto actualizado. Stock: 10 → 9. ', 'root@localhost', '2025-12-08 19:50:50'),
(34, 'productos', 'ALERTA', 10, 'STOCK BAJO: Producto \"Jaula Roedor Mediana\" tiene solo 9 unidades, por favor añadir mas Stock.', 'root@localhost', '2025-12-08 19:50:50'),
(35, 'productos', 'UPDATE', 10, 'Producto actualizado. Stock: 9 → 8. ', 'root@localhost', '2025-12-08 19:57:39'),
(36, 'productos', 'ALERTA', 10, 'STOCK BAJO: Producto \"Jaula Roedor Mediana\" tiene solo 8 unidades, por favor añadir mas Stock.', 'root@localhost', '2025-12-08 19:57:39'),
(37, 'productos', 'UPDATE', 3, 'Producto actualizado. Stock: 40 → 39. ', 'root@localhost', '2025-12-08 20:03:06'),
(38, 'productos', 'UPDATE', 8, 'Producto actualizado. Stock: 100 → 99. ', 'root@localhost', '2025-12-08 20:03:06'),
(39, 'productos', 'UPDATE', 6, 'Producto actualizado. Stock: 69 → 68. ', 'root@localhost', '2025-12-08 20:03:06'),
(40, 'productos', 'UPDATE', 8, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:03:54'),
(41, 'productos', 'UPDATE', 2, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:06:11'),
(42, 'productos', 'UPDATE', 2, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:06:19'),
(43, 'productos', 'UPDATE', 2, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:06:19'),
(44, 'productos', 'UPDATE', 2, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:08:19'),
(45, 'productos', 'UPDATE', 5, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:09:13'),
(46, 'productos', 'UPDATE', 3, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:09:49'),
(47, 'productos', 'UPDATE', 6, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:10:20'),
(48, 'productos', 'UPDATE', 4, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:11:05'),
(49, 'productos', 'UPDATE', 9, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:12:10'),
(50, 'productos', 'UPDATE', 7, 'Producto actualizado. ', 'root@localhost', '2025-12-08 20:12:36'),
(51, 'productos', 'UPDATE', 7, 'Producto actualizado. Stock: 25 → 20. ', 'root@localhost', '2025-12-08 20:32:52'),
(52, 'clientes', 'DELETE', 6, 'Cliente eliminado: ID=6, RUT=\"12356895-0\", Nombre=\"Fart Simpson\", Email=\"correopi@gmail.com\", Teléfono=\"+56 9 7834 1023\", Total pedidos realizados=0', 'root@localhost', '2025-12-08 20:42:57'),
(53, 'productos', 'UPDATE', 3, 'Producto actualizado. Stock: 39 → 40. ', 'root@localhost', '2025-12-08 20:43:27'),
(54, 'productos', 'UPDATE', 6, 'Producto actualizado. Stock: 68 → 80. ', 'root@localhost', '2025-12-08 20:43:33'),
(55, 'productos', 'UPDATE', 4, 'Producto actualizado. Stock: 59 → 60. ', 'root@localhost', '2025-12-08 20:43:39'),
(56, 'productos', 'UPDATE', 9, 'Producto actualizado. Stock: 35 → 50. ', 'root@localhost', '2025-12-08 20:43:44'),
(57, 'productos', 'UPDATE', 10, 'Producto actualizado. Stock: 8 → 20. ', 'root@localhost', '2025-12-08 20:43:52'),
(58, 'productos', 'UPDATE', 6, 'Producto actualizado. Stock: 80 → 75. ', 'root@localhost', '2025-12-08 20:47:53'),
(59, 'productos', 'UPDATE', 4, 'Producto actualizado. Stock: 60 → 59. ', 'root@localhost', '2025-12-08 20:48:09'),
(60, 'productos', 'UPDATE', 9, 'Producto actualizado. Stock: 50 → 20. ', 'root@localhost', '2025-12-08 20:49:08'),
(61, 'productos', 'UPDATE', 10, 'Producto actualizado. Stock: 20 → 19. ', 'root@localhost', '2025-12-08 20:50:39'),
(62, 'productos', 'UPDATE', 5, 'Producto actualizado. Stock: 80 → 75. ', 'root@localhost', '2025-12-08 20:50:39'),
(63, 'productos', 'UPDATE', 6, 'Producto actualizado. Stock: 75 → 65. ', 'root@localhost', '2025-12-08 20:59:38'),
(64, 'productos', 'UPDATE', 5, 'Producto actualizado. Stock: 75 → 65. ', 'root@localhost', '2025-12-08 20:59:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_productos`
--

CREATE TABLE `categorias_productos` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_productos`
--

INSERT INTO `categorias_productos` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Alimento Perro', 'Alimento seco o húmedo para perros'),
(2, 'Alimento Gato', 'Alimento seco o húmedo para gatos'),
(3, 'Snacks', 'Premios y golosinas para mascotas'),
(4, 'Arena/Absorbentes', 'Arena sanitaria y productos absorbentes'),
(5, 'Accesorios', 'Correas, juguetes, platos, etc.'),
(6, 'Roedores y Otros', 'Alimento y accesorios para roedores y otras mascotas pequeñas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rut` varchar(12) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `rut`, `email`, `telefono`, `direccion`) VALUES
(1, 'Juan Pérez', '11111111-1', 'juan.perez@example.com', '+56 9 7000 0001', 'Av. Los Perros 123, Coquimbo'),
(2, 'María López', '22222222-2', 'maria.lopez@example.com', '+56 9 7000 0002', 'Calle Los Gatos 456, La Serena'),
(3, 'Carlos Sánchez', '33333333-3', 'carlos.sanchez@example.com', '+56 9 7000 0003', 'Pasaje Mascotas 789, Coquimbo'),
(4, 'Ana Torres', '44444444-4', 'ana.torres@example.com', '+56 9 7000 0004', 'Av. Central 100, La Serena'),
(5, 'Pedro Ramírez', '55555555-5', 'pedro.ramirez@example.com', '+56 9 7000 0005', 'Ruta 5 Norte km 10, Coquimbo'),
(7, 'Martina Cuello', '15374982-2', 'martina_cuello@hotmail.com', '+56 9 7234 8940', 'Las Cardas 2678');

--
-- Disparadores `clientes`
--
DELIMITER $$
CREATE TRIGGER `trigger_after_actualizar_clientes` AFTER UPDATE ON `clientes` FOR EACH ROW BEGIN
    DECLARE v_cambios VARCHAR(500);
    DECLARE v_tipo_alerta VARCHAR(20);
    
    -- Construir descripción de cambios
    SET v_cambios = CONCAT(
        'Cliente actualizado. ',
        IF(OLD.nombre != NEW.nombre, CONCAT('Nombre: "', OLD.nombre, '" → "', NEW.nombre, '". '), ''),
        IF(OLD.email != NEW.email, CONCAT('Email: "', COALESCE(OLD.email, 'NULL'), '" → "', COALESCE(NEW.email, 'NULL'), '". '), ''),
        IF(OLD.telefono != NEW.telefono, CONCAT('Teléfono: "', COALESCE(OLD.telefono, 'NULL'), '" → "', COALESCE(NEW.telefono, 'NULL'), '". '), ''),
        IF(OLD.direccion != NEW.direccion, CONCAT('Dirección: "', COALESCE(OLD.direccion, 'NULL'), '" → "', COALESCE(NEW.direccion, 'NULL'), '". '), '')
    );
    
    -- Registrar en auditoría
    INSERT INTO auditoria_cambios (tabla_afectada, tipo_operacion, id_registro, descripcion, usuario_sistema)
    VALUES ('clientes', 'UPDATE', NEW.id_cliente, v_cambios, USER());
    
    -- Si cambió el email, generar alerta
    IF OLD.email != NEW.email THEN
        SET v_tipo_alerta = CONCAT('Cambio de email: ', COALESCE(OLD.email, 'SIN EMAIL'), ' → ', COALESCE(NEW.email, 'SIN EMAIL'));
        INSERT INTO auditoria_cambios (tabla_afectada, tipo_operacion, id_registro, descripcion, usuario_sistema)
        VALUES ('clientes', 'ALERTA', NEW.id_cliente, v_tipo_alerta, USER());
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_before_eliminar_clientes` BEFORE DELETE ON `clientes` FOR EACH ROW BEGIN
    DECLARE v_descripcion TEXT;
    DECLARE v_total_pedidos INT;
    
    -- Contar pedidos del cliente
    SELECT COUNT(*) INTO v_total_pedidos FROM pedidos WHERE id_cliente = OLD.id_cliente;
    
    -- Construir descripción detallada
    SET v_descripcion = CONCAT(
        'Cliente eliminado: ID=', OLD.id_cliente, 
        ', RUT="', OLD.rut, 
        '", Nombre="', OLD.nombre, 
        '", Email="', COALESCE(OLD.email, 'SIN EMAIL'),
        '", Teléfono="', COALESCE(OLD.telefono, 'SIN TELÉFONO'),
        '", Total pedidos realizados=', v_total_pedidos
    );
    
    -- Registrar eliminación en auditoría
    INSERT INTO auditoria_cambios (tabla_afectada, tipo_operacion, id_registro, descripcion, usuario_sistema)
    VALUES ('clientes', 'DELETE', OLD.id_cliente, v_descripcion, USER());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 6, 5, 3500.00, 17500.00),
(2, 2, 4, 1, 9000.00, 9000.00),
(3, 3, 9, 30, 7000.00, 210000.00),
(4, 4, 10, 1, 26000.00, 26000.00),
(5, 4, 5, 5, 5500.00, 27500.00),
(6, 5, 6, 10, 3500.00, 35000.00),
(7, 5, 5, 10, 5500.00, 55000.00),
(8, 6, 10, 19, 26000.00, 494000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(20) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `id_vendedor`, `fecha`, `estado`, `total`) VALUES
(1, 3, 2, '2025-12-08 17:47:53', 'PAGADO', 17500.00),
(2, 2, 2, '2025-12-08 17:48:09', 'PAGADO', 9000.00),
(3, 5, 1, '2025-12-08 17:49:08', 'CANCELADO', 210000.00),
(4, 7, 3, '2025-12-08 17:50:39', 'PAGADO', 53500.00),
(5, 2, 3, '2025-12-08 17:59:38', 'PENDIENTE', 90000.00),
(6, 1, 3, '2025-12-08 19:17:04', 'CANCELADO', 494000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `imagen`, `id_categoria`, `id_proveedor`, `precio`, `stock`, `activo`) VALUES
(1, 'Croquetas Perro Cachorro 2kg', 'Alimento seco para perro cachorro', NULL, 1, 1, 12000.00, 49, 0),
(2, 'Croquetas Perro Adulto 10kg', 'Alimento seco perro adulto', '89d8057a3c5b883f121f82085b1914ed.jpg', 1, 2, 28000.00, 30, 1),
(3, 'Alimento Gato Indoor 3kg', 'Control bolas de pelo', 'e4cbe022ac7b3ea223b3e214a4de5353.webp', 2, 3, 15000.00, 40, 1),
(4, 'Arena Sanitaria Aglomerante 10kg', 'Arena para gatos', '03375b2da9edad3805651aba80f671d3.webp', 4, 4, 9000.00, 59, 1),
(5, 'Snack Perro Huesitos 500g', 'Snacks sabor pollo', '5a261db32755c5484023dc4d88f3396f.webp', 3, 2, 5500.00, 65, 1),
(6, 'Snack Gato Pescado 100g', 'Snacks sabor pescado', 'f7fb09890baeb7f15a32a5dead06e616.webp', 3, 3, 3500.00, 65, 1),
(7, 'Correa Nylon Perro Mediano', 'Correa 1.5m', '21a5acc057b3955743760e85b3161133.webp', 5, 5, 8000.00, 20, 1),
(8, 'Juguete Pelota con Sonido', 'Pelota con sonido', NULL, 5, 1, 4500.00, 99, 0),
(9, 'Heno Premium Roedores 1kg', 'Heno natural', '1e0bc1df7a19e675b52f7e40f4f9b4f3.png', 6, 4, 7000.00, 20, 1),
(10, 'Jaula Roedor Mediana', 'Jaula metálica', '7b36d2038dd0d8dec16e0e6b0c01e5c3.jpg', 6, 5, 26000.00, 19, 1);

--
-- Disparadores `productos`
--
DELIMITER $$
CREATE TRIGGER `check_stock_negativo_update` BEFORE UPDATE ON `productos` FOR EACH ROW BEGIN
    -- Si intentan actualizar y el nuevo stock es menor a 0
    IF NEW.stock < 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Error Crítico: La operación dejaría el producto con stock negativo.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_after_actualizar_productos` AFTER UPDATE ON `productos` FOR EACH ROW BEGIN
    DECLARE v_cambios VARCHAR(500);
    
    -- Validar que el nuevo stock no sea menor a 0
    IF NEW.stock < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede establecer stock negativo';
    END IF;
    
    -- Construir descripción de cambios
    SET v_cambios = CONCAT(
        'Producto actualizado. ',
        IF(OLD.nombre != NEW.nombre, CONCAT('Nombre: "', OLD.nombre, '" → "', NEW.nombre, '". '), ''),
        IF(OLD.precio != NEW.precio, CONCAT('Precio: ', OLD.precio, ' → ', NEW.precio, '. '), ''),
        IF(OLD.stock != NEW.stock, CONCAT('Stock: ', OLD.stock, ' → ', NEW.stock, '. '), ''),
        IF(OLD.id_proveedor != NEW.id_proveedor, CONCAT('Proveedor: ', OLD.id_proveedor, ' → ', NEW.id_proveedor, '. '), '')
    );
    
    -- Registrar en auditoría
    INSERT INTO auditoria_cambios (tabla_afectada, tipo_operacion, id_registro, descripcion, usuario_sistema)
    VALUES ('productos', 'UPDATE', NEW.id_producto, v_cambios, USER());
    
    -- Si stock es bajo (menor a 10), registrar alerta
    IF NEW.stock < 10 AND NEW.stock > 0 THEN
        INSERT INTO auditoria_cambios (tabla_afectada, tipo_operacion, id_registro, descripcion, usuario_sistema)
        VALUES ('productos', 'ALERTA', NEW.id_producto, 
                CONCAT('STOCK BAJO: Producto "', NEW.nombre, '" tiene solo ', NEW.stock, ' unidades, por favor añadir mas Stock.'), 
                USER());
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_before_eliminar_productos` BEFORE DELETE ON `productos` FOR EACH ROW BEGIN
    DECLARE v_descripcion TEXT;
    
    -- Construir descripción detallada
    SET v_descripcion = CONCAT(
        'Producto eliminado: ID=', OLD.id_producto, 
        ', Nombre="', OLD.nombre, 
        '", Precio=', OLD.precio,
        ', Stock=', OLD.stock,
        ', Categoría ID=', OLD.id_categoria,
        ', Proveedor ID=', OLD.id_proveedor
    );
    
    -- Registrar eliminación en auditoría
    INSERT INTO auditoria_cambios (tabla_afectada, tipo_operacion, id_registro, descripcion, usuario_sistema)
    VALUES ('productos', 'DELETE', OLD.id_producto, v_descripcion, USER());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `nombre`, `contacto`, `telefono`, `email`, `direccion`) VALUES
(1, 'PetFoods Chile', 'María Rivera', '+56 2 2345 0001', 'ventas@petfoodschile.cl', 'Av. Industrial 123, Santiago'),
(2, 'Mundo Animal Distribuciones', 'José Gutiérrez', '+56 2 2345 0002', 'contacto@mundoanimal.cl', 'Camino Logístico 321, Santiago'),
(3, 'NutriPet Import', 'Carolina Díaz', '+56 2 2345 0003', 'info@nutripetimport.cl', 'Ruta 68 km 20, Valparaíso'),
(4, 'MascotaFeliz Ltda.', 'Rodrigo Soto', '+56 2 2345 0004', 'ventas@mascotafeliz.cl', 'Av. Principal 456, La Serena'),
(5, 'SuperPets Mayorista', 'Patricia Lagos', '+56 2 2345 0005', 'contacto@superpets.cl', 'Parque Industrial 789, Coquimbo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedores`
--

CREATE TABLE `vendedores` (
  `id_vendedor` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rut` varchar(12) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `id_administrador` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `contrasena` varchar(255) DEFAULT '123456',
  `usuario` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vendedores`
--

INSERT INTO `vendedores` (`id_vendedor`, `nombre`, `rut`, `email`, `telefono`, `fecha_contratacion`, `id_administrador`, `activo`, `contrasena`, `usuario`) VALUES
(1, 'Sandra Quiroga', '20780562-2', 'sandra.q@bingus.cl', '+56 9 7890 2374', '2025-12-08', 1, 1, '$2y$10$sRWi9wsZsVlo0EAAzPmiP.L8cBcEZnlwCGQcI.OzQvOrZOR2tMEEG', NULL),
(2, 'Laura Goméz', '17354823-K', 'laura.g@bingus.cl', '+56 9 7023 7723', '2025-12-08', 1, 1, '$2y$10$MVlWW0nNFTSK8DyDuLlaF.VlJGNV5aaWPmyZp0RAkfUOv.ihGLvty', NULL),
(3, 'Nicolás Guerrero', '20890111-9', 'nicolas.g@bingus.cl', '+56 9 1789 3738', '2025-12-08', 2, 1, '$2y$10$7vNs0873YBGPBYAGLuZEB.p3GYmLIyZxqzHKl35M/kgDFpQVuikZO', NULL);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_catalogo_pos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_catalogo_pos` (
`id_producto` int(11)
,`nombre` varchar(150)
,`precio` decimal(10,2)
,`stock` int(11)
,`imagen` varchar(255)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pedidos_detalle`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pedidos_detalle` (
`id_pedido` int(11)
,`fecha` datetime
,`estado` varchar(20)
,`total` decimal(10,2)
,`cliente_nombre` varchar(100)
,`cliente_rut` varchar(12)
,`vendedor_nombre` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_catalogo_pos`
--
DROP TABLE IF EXISTS `v_catalogo_pos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_catalogo_pos`  AS SELECT `productos`.`id_producto` AS `id_producto`, `productos`.`nombre` AS `nombre`, `productos`.`precio` AS `precio`, `productos`.`stock` AS `stock`, `productos`.`imagen` AS `imagen` FROM `productos` WHERE `productos`.`activo` = 1 AND `productos`.`stock` > 0 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pedidos_detalle`
--
DROP TABLE IF EXISTS `v_pedidos_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedidos_detalle`  AS SELECT `p`.`id_pedido` AS `id_pedido`, `p`.`fecha` AS `fecha`, `p`.`estado` AS `estado`, `p`.`total` AS `total`, `c`.`nombre` AS `cliente_nombre`, `c`.`rut` AS `cliente_rut`, `v`.`nombre` AS `vendedor_nombre` FROM ((`pedidos` `p` left join `clientes` `c` on(`p`.`id_cliente` = `c`.`id_cliente`)) left join `vendedores` `v` on(`p`.`id_vendedor` = `v`.`id_vendedor`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `idx_rut_admin` (`rut`);

--
-- Indices de la tabla `auditoria_cambios`
--
ALTER TABLE `auditoria_cambios`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `idx_tabla` (`tabla_afectada`),
  ADD KEY `idx_fecha` (`fecha_cambio`);

--
-- Indices de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `rut` (`rut`),
  ADD UNIQUE KEY `idx_rut_cliente` (`rut`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_vendedor` (`id_vendedor`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD PRIMARY KEY (`id_vendedor`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_rut_vendedor` (`rut`),
  ADD KEY `id_administrador` (`id_administrador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `auditoria_cambios`
--
ALTER TABLE `auditoria_cambios`
  MODIFY `id_auditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `vendedores`
--
ALTER TABLE `vendedores`
  MODIFY `id_vendedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedores` (`id_vendedor`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_productos` (`id_categoria`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`);

--
-- Filtros para la tabla `vendedores`
--
ALTER TABLE `vendedores`
  ADD CONSTRAINT `vendedores_ibfk_1` FOREIGN KEY (`id_administrador`) REFERENCES `administradores` (`id_administrador`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
