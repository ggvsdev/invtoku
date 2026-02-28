<?php
declare(strict_types=1);

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/ConteoTiempoReal.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../core/Response.php';

class InventarioController {
    
    public function __construct() {
        AuthController::checkAuth();
    }
    
    // Vista de panel de inventario
    public function panel(): void {
        $inventarioModel = new Inventario();
        $sesion = $inventarioModel->getSesionActiva();
        
        $stats = [];
        if ($sesion) {
            $conteoModel = new ConteoTiempoReal();
            $stats = $conteoModel->getStats($sesion['id']);
        }
        
        require_once ROOT_PATH . 'views/inventario/panel.php';
    }
    
    // Vista de conteo (scanner)
    public function conteo(): void {
        $inventarioModel = new Inventario();
        $sesion = $inventarioModel->getSesionActiva();
        
        if (!$sesion) {
            header('Location: ' . BASE_URL . 'inventario/panel?error=no_session');
            exit;
        }
        
        require_once ROOT_PATH . 'views/inventario/conteo.php';
    }
    
    // Vista de historial
    public function historial(): void {
        $inventarioModel = new Inventario();
        $sesiones = $inventarioModel->getAllSesiones();
        require_once ROOT_PATH . 'views/inventario/historial.php';
    }
    
    // API: Crear nueva sesión
    public function apiCrearSesion(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $validator = new Validator($input);
            $validator->required('nombre', 'El nombre es requerido')->min('nombre', 3);
            
            if ($validator->fails()) {
                Response::error('Validación fallida', 422, $validator->errors());
            }
            
            // Verificar si ya hay sesión activa
            $inventarioModel = new Inventario();
            if ($inventarioModel->getSesionActiva()) {
                Response::error('Ya existe una sesión de inventario activa', 400);
            }
            
            $sesionId = $inventarioModel->crearSesion(
                Security::sanitize($input['nombre']),
                Security::sanitize($input['descripcion'] ?? ''),
                $_SESSION['user_id']
            );
            
            Security::logSecurity('sesion_creada', $_SESSION['user_id'], "Sesión ID: $sesionId");
            Response::success('Sesión creada correctamente', ['sesion_id' => $sesionId]);
            
        } catch (Exception $e) {
            Response::error('Error al crear sesión: ' . $e->getMessage(), 500);
        }
    }
    
    // API: Registrar conteo (AJAX desde scanner)
    public function apiRegistrarConteo(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            $validator = new Validator($input);
            $validator->required('codigo', 'Código requerido')
                     ->required('cantidad', 'Cantidad requerida')
                     ->numeric('cantidad')
                     ->positive('cantidad');
            
            if ($validator->fails()) {
                Response::error('Datos inválidos', 422, $validator->errors());
            }
            
            // Obtener sesión activa
            $inventarioModel = new Inventario();
            $sesion = $inventarioModel->getSesionActiva();
            
            if (!$sesion) {
                Response::error('No hay sesión de inventario activa', 400);
            }
            
            // Buscar producto
            $productoModel = new Producto();
            $codigo = Security::sanitize($input['codigo']);
            $producto = $productoModel->findByCode($codigo);
            
            if (!$producto) {
                Response::error('Producto no encontrado: ' . $codigo, 404);
            }
            
            // Registrar conteo
            $conteoModel = new ConteoTiempoReal();
            $resultado = $conteoModel->registrar([
                'sesion_id' => $sesion['id'],
                'producto_id' => $producto['id'],
                'codigo_escaneado' => $codigo,
                'cantidad' => (float)$input['cantidad'],
                'contador_id' => $_SESSION['user_id']
            ]);
            
            // Respuesta con datos del producto
            Response::success('Conteo registrado', [
                'producto' => [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'codigo' => $producto['codigo_producto'],
                    'stock_teorico' => $producto['stock_teorico'],
                    'stock_fisico' => $producto['stock_fisico'] + (float)$input['cantidad']
                ],
                'conteo' => $resultado
            ]);
            
        } catch (Exception $e) {
            error_log("Error en conteo: " . $e->getMessage());
            Response::error('Error al registrar conteo', 500);
        }
    }
    
    // API: Obtener conteos en tiempo  real (para AJAX polling)
    public function apiGetConteos(): void {
        $inventarioModel = new Inventario();
        $sesion = $inventarioModel->getSesionActiva();
        
        if (!$sesion) {
            Response::error('No hay sesión activa', 400);
        }
        
        $conteoModel = new ConteoTiempoReal();
        
        Response::success('Datos obtenidos', [
            'stats' => $conteoModel->getStats($sesion['id']),
            'ultimos' => $conteoModel->getUltimosConteos($sesion['id'], 20),
            'agrupados' => $conteoModel->getTotalesAgrupados($sesion['id'])
        ]);
    }
    
    // API: Cerrar sesión
    public function apiCerrarSesion(): void {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $sesionId = $input['sesion_id'] ?? 0;
            
            $inventarioModel = new Inventario();
            $inventarioModel->cerrarSesion((int)$sesionId);
            
            Security::logSecurity('sesion_cerrada', $_SESSION['user_id'], "Sesión ID: $sesionId");
            Response::success('Sesión cerrada correctamente');
            
        } catch (Exception $e) {
            Response::error('Error al cerrar sesión', 500);
        }
    }
}