<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class Inventario {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Crear nueva sesión de inventario
    public function crearSesion(string $nombre, string $descripcion, int $userId): int {
        $stmt = $this->db->prepare("
            INSERT INTO sesiones_inventario (nombre, descripcion, created_by) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$nombre, $descripcion, $userId]);
        return (int)$this->db->lastInsertId();
    }
    
    // Obtener sesión activa
    public function getSesionActiva(): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.nombre_completo as creador 
            FROM sesiones_inventario s
            JOIN usuarios u ON s.created_by = u.id
            WHERE s.estado = 'activa' 
            ORDER BY s.fecha_inicio DESC 
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }
    
    // Obtener sesión por ID
    public function getSesionById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.nombre_completo as creador 
            FROM sesiones_inventario s
            JOIN usuarios u ON s.created_by = u.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    // Cerrar sesión
    public function cerrarSesion(int $sesionId): void {
        $stmt = $this->db->prepare("
            UPDATE sesiones_inventario 
            SET estado = 'cerrada', fecha_cierre = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$sesionId]);
    }
    
    // Pausar sesión
    public function pausarSesion(int $sesionId): void {
        $stmt = $this->db->prepare("
            UPDATE sesiones_inventario 
            SET estado = 'pausada' 
            WHERE id = ?
        ");
        $stmt->execute([$sesionId]);
    }
    
    // Reanudar sesión
    public function reanudarSesion(int $sesionId): void {
        $stmt = $this->db->prepare("
            UPDATE sesiones_inventario 
            SET estado = 'activa' 
            WHERE id = ?
        ");
        $stmt->execute([$sesionId]);
    }
    
    // Listar todas las sesiones
    public function getAllSesiones(int $page = 1, int $perPage = 10): array {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT s.*, u.nombre_completo as creador,
                   (SELECT COUNT(DISTINCT producto_id) FROM conteos_tiempo_real WHERE sesion_id = s.id) as productos_contados,
                   (SELECT SUM(cantidad) FROM conteos_tiempo_real WHERE sesion_id = s.id) as total_unidades
            FROM sesiones_inventario s
            JOIN usuarios u ON s.created_by = u.id
            ORDER BY s.fecha_inicio DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        return $stmt->fetchAll();
    }
}