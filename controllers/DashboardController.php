<?php
declare(strict_types=1);

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Producto.php';

class DashboardController {
    
    public function __construct() {
        AuthController::checkAuth();
    }
    
    // Dashboard principal
    public function index(): void {
        $inventarioModel = new Inventario();
        $productoModel = new Producto();
        
        $stats = [
            'sesion_activa' => $inventarioModel->getSesionActiva(),
            'total_productos' => 0,
            'productos_bajo_stock' => 0
        ];
        
        // Obtener conteos totales si hay sesión activa
        if ($stats['sesion_activa']) {
            $conteoModel = new ConteoTiempoReal();
            $stats['conteos'] = $conteoModel->getStats($stats['sesion_activa']['id']);
        }
        
        require_once ROOT_PATH . 'views/dashboard/index.php';
    }
}