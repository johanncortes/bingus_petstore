<?php
/**
 * ============================================
 * CAPA 2 — Controlador: Dashboard
 * ============================================
 * Estadísticas del panel de administración.
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthMiddleware.php';

class DashboardController {

    /**
     * GET /api/dashboard/stats
     * Obtener estadísticas del dashboard
     */
    public function stats() {
        AuthMiddleware::verificarAdmin();
        $usuario = AuthMiddleware::getUsuarioActual();
        
        try {
            $conn = Database::getInstance()->getConnection();
            $stmt = $conn->prepare("CALL sp_dashboard_stats(?)");
            $stmt->execute([$usuario['admin_id']]);
            $stats = $stmt->fetch();
            $stmt->closeCursor();

            $total_compradores = $conn->query("SELECT COUNT(*) FROM clientes")->fetchColumn();

            if (!$stats) {
                $stats = ['total_productos' => 0, 'total_vendedores' => 0, 'total_pedidos' => 0];
            }
            $stats['total_compradores'] = ($total_compradores !== false) ? $total_compradores : 0;

            Response::success($stats, 'Estadísticas obtenidas.');
        } catch (Exception $e) {
            Response::error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
}
?>
