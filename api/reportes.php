<?php
declare(strict_types=1);

define('SIG_START', true);
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../controllers/AuthController.php';

session_start();
AuthController::checkAuth();

// Reportes de inventario
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'diferencias':
        generarReporteDiferencias();
        break;
    case 'conteos':
        generarReporteConteos();
        break;
    default:
        Response::error('Acción no válida', 400);
}

function generarReporteDiferencias(): void {
    $db = Database::getInstance();
    $stmt = $db->query("
        SELECT 
            p.codigo_producto,
            p.codigo_barra,
            p.nombre,
            p.stock_teorico,
            p.stock_fisico,
            p.diferencia,
            CASE 
                WHEN p.diferencia = 0 THEN 'OK'
                WHEN p.diferencia > 0 THEN 'SOBRANTE'
                ELSE 'FALTANTE'
            END as estado
        FROM productos p
        WHERE p.stock_fisico > 0 OR p.diferencia != 0
        ORDER BY ABS(p.diferencia) DESC
    ");
    
    Response::success('Reporte generado', ['data' => $stmt->fetchAll()]);
}

function generarReporteConteos(): void {
    $sesionId = $_GET['sesion_id'] ?? 0;
    $conteoModel = new ConteoTiempoReal();
    
    Response::success('Reporte generado', [
        'agrupados' => $conteoModel->getTotalesAgrupados((int)$sesionId),
        'detalle' => $conteoModel->getConteosBySesion((int)$sesionId)
    ]);
}