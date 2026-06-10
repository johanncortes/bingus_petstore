<?php
require_once 'config.php';

class Comprador_model {
    private $conexion;

    public function __construct() {
        $this->conexion = conectarBD();
    }

    public function getCompradores() {
        $query = "SELECT c.*,
                         COUNT(p.id_pedido) as total_pedidos,
                         COALESCE(SUM(p.total), 0) as total_gastado,
                         MAX(p.fecha) as ultima_compra
                  FROM clientes c
                  LEFT JOIN pedidos p ON p.id_cliente = c.id_cliente
                  GROUP BY c.id_cliente, c.nombre, c.rut, c.email, c.telefono, c.direccion
                  ORDER BY c.nombre ASC";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
