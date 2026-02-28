<?php
declare(strict_types=1);

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../core/Response.php';

class ProductoController {
    
    public function __construct() {
        AuthController::checkAuth();
    }
    
    // Vista listado
    public function index(): void {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        $productoModel = new Producto();
        $productos = $productoModel->getAll((int)$page, 20, $search);
        
        require_once ROOT_PATH . 'views/productos/index.php';
    }
    
    // Vista formulario (crear/editar)
    public function form(): void {
        $id = $_GET['id'] ?? 0;
        $producto = null;
        
        if ($id) {
            $productoModel = new Producto();
            $producto = $productoModel->findById((int)$id);
        }
        
        require_once ROOT_PATH . 'views/productos/form.php';
    }
    
    // API: Crear producto
    public function apiCreate(): void {
        try {
            // Solo admin y supervisor
            if (!in_array($_SESSION['user_role'], ['admin', 'supervisor'])) {
                Response::error('No autorizado', 403);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $validator = new Validator($input);
            $validator->required('codigo_producto')
                     ->required('codigo_barra')
                     ->required('nombre')->min('nombre', 3)
                     ->required('precio_costo')->numeric('precio_costo')
                     ->required('precio_venta')->numeric('precio_venta');
            
            if ($validator->fails()) {
                Response::error('Validación fallida', 422, $validator->errors());
            }
            
            $productoModel = new Producto();
            $id = $productoModel->create([
                'codigo_producto' => Security::sanitize($input['codigo_producto']),
                'codigo_barra' => Security::sanitize($input['codigo_barra']),
                'nombre' => Security::sanitize($input['nombre']),
                'descripcion' => Security::sanitize($input['descripcion'] ?? ''),
                'unidad_medida' => Security::sanitize($input['unidad_medida'] ?? 'UNIDAD'),
                'stock_teorico' => (float)($input['stock_teorico'] ?? 0),
                'precio_costo' => (float)$input['precio_costo'],
                'precio_venta' => (float)$input['precio_venta'],
                'ubicacion' => Security::sanitize($input['ubicacion'] ?? '')
            ]);
            
            Security::logSecurity('producto_creado', $_SESSION['user_id'], "Producto ID: $id");
            Response::success('Producto creado correctamente', ['id' => $id]);
            
        } catch (Exception $e) {
            Response::error('Error al crear producto: ' . $e->getMessage(), 500);
        }
    }
    
    // API: Buscar producto por código
    public function apiBuscar(): void {
        $codigo = $_GET['codigo'] ?? '';
        
        if (empty($codigo)) {
            Response::error('Código requerido', 422);
        }
        
        $productoModel = new Producto();
        $producto = $productoModel->findByCode(Security::sanitize($codigo));
        
        if (!$producto) {
            Response::error('Producto no encontrado', 404);
        }
        
        Response::success('Producto encontrado', ['producto' => $producto]);
    }
}