<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class Producto {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Buscar por código de barra o código de producto
    public function findByCode(string $code): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM productos 
            WHERE (codigo_barra = ? OR codigo_producto = ?) 
            AND activo = 1 
            LIMIT 1
        ");
        $stmt->execute([$code, $code]);
        return $stmt->fetch() ?: null;
    }
    
    // Buscar por ID
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ? AND activo = 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    // Listar todos con paginación
    public function getAll(int $page = 1, int $perPage = 20, string $search = ''): array {
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        $where = "WHERE activo = 1";
        if ($search) {
            $where .= " AND (nombre LIKE ? OR codigo_producto LIKE ? OR codigo_barra LIKE ?)";
            $params = array_fill(0, 3, "%$search%");
        }
        
        $stmt = $this->db->prepare("
            SELECT SQL_CALC_FOUND_ROWS * FROM productos 
            $where 
            ORDER BY nombre 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([...$params, $perPage, $offset]);
        $products = $stmt->fetchAll();
        
        $total = $this->db->query("SELECT FOUND_ROWS()")->fetchColumn();
        
        return [
            'data' => $products,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current' => $page
        ];
    }
    
    // Crear producto
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO productos 
            (codigo_producto, codigo_barra, nombre, descripcion, categoria_id, unidad_medida, 
             stock_teorico, precio_costo, precio_venta, ubicacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['codigo_producto'],
            $data['codigo_barra'],
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['categoria_id'] ?? null,
            $data['unidad_medida'] ?? 'UNIDAD',
            $data['stock_teorico'] ?? 0,
            $data['precio_costo'] ?? 0,
            $data['precio_venta'] ?? 0,
            $data['ubicacion'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }
    
    // Actualizar stock físico (desde conteos)
    public function updateStockFisico(int $id, float $cantidad): void {
        $stmt = $this->db->prepare("
            UPDATE productos 
            SET stock_fisico = stock_fisico + ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$cantidad, $id]);
    }
    
    // Resetear stock físico (al cerrar sesión)
    public function resetStockFisico(int $id): void {
        $stmt = $this->db->prepare("UPDATE productos SET stock_fisico = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }
}