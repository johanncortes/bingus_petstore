-- ============================================
-- MIGRACIÓN: Auth de Clientes en Tienda Virtual
-- Bingus Petstore v2.0
-- ============================================
-- Agrega columna 'password' a la tabla clientes
-- para soportar registro/login desde la tienda.

ALTER TABLE `clientes` ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `direccion`;
