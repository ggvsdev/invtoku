<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../config/security.php';

class ConteoTiempoReal {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Registrar conteo (con hash único para evitar duplicados)
    public function registrar(array $data): array {
        try {
            $hash = Security::generateConteoHash(
                $data['sesion_id'], 
                $data['producto_id'], 
                $data['codigo_escaneado'], 
                $data['contador_id']
            );
            
            $stmt = $this->db->prepare("
                INSERT INTO conteos_tiempo_real 
                (sesion_id, producto_id, codigo_escaneado, cantidad, contador_id, dispositivo_info, hash_unico) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                cantidad = cantidad + VALUES(cantidad),
                created_at = NOW()
            ");
            
            $stmt->execute([
                $data['sesion_id'],
                $data['producto_id'],
                $data['codigo_escaneado'],
                $data['cantidad'],
                $data['contador_id'],
                $data['dispositivo_info'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                $hash
            ]);
            
            // Actualizar stock físico del producto
            $producto = new Producto();
            $producto->updateStockFisico($data['producto_id'], $data['cantidad']);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'hash' => $hash,
                'message' => 'Conteo registrado correctamente'
            ];
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['success' => true, 'message' => 'Conteo acumulado (duplicado detectado)'];
            }
            throw $e;
        }
    }
    
    // Obtener conteos en tiempo real por sesión
    public function getConteosBySesion(int $sesionId): array {
        $stmt = $this->db->prepare("
            SELECT 
                c.*,
                p.codigo_producto,
                p.codigo_barra,
                p.nombre as producto_nombre,
                p.stock_teorico,
                p.stock_fisico,
                p.diferencia,
                u.nombre_completo as contador_nombre
            FROM conteos_tiempo_real c
            JOIN productos p ON c.producto_id = p.id
            JOIN usuarios u ON c.contador_id = u.id
            WHERE c.sesion_id = ?
            ORDER BY c.created_at DESC
            LIMIT 100
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll();
    }
    
    // Obtener totales agrupados (vista)
    public function getTotalesAgrupados(int $sesionId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM vista_conteos_agrupados 
            WHERE sesion_id = ?
            ORDER BY total_contado DESC
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetchAll();
    }
    
    // Estadísticas en tiempo real
    public function getStats(int $sesionId): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT producto_id) as productos_distintos,
                SUM(cantidad) as total_unidades,
                COUNT(DISTINCT contador_id) as total_contadores,
                MAX(created_at) as ultimo_conteo
            FROM conteos_tiempo_real 
            WHERE sesion_id = ?
        ");
        $stmt->execute([$sesionId]);
        return $stmt->fetch();
    }
    
    // Últimos conteos (para actualización en tiempo real)
    public function getUltimosConteos(int $sesionId, int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT 
                c.id,
                c.cantidad,
                c.created_at,
                p.codigo_producto,
                p.codigo_barra,
                p.nombre as producto_nombre,
                u.nombre_completo as contador_nombre
            FROM conteos_tiempo_real c
            JOIN productos p ON c.producto_id = p.id
            JOIN usuarios u ON c.contador_id = u.id
            WHERE c.sesion_id = ?
            ORDER BY c.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$sesionId, $limit]);
        return $stmt->fetchAll();
    }
}