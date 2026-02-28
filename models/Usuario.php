<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/Database.php';

class Usuario {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = 1");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, username, email, nombre_completo, rol, ultimo_acceso FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public function updateLastAccess(int $id, string $token, string $ip): void {
        $stmt = $this->db->prepare("
            UPDATE usuarios 
            SET ultimo_acceso = NOW(), session_token = ?, ip_ultimo_acceso = ?, intentos_fallidos = 0 
            WHERE id = ?
        ");
        $stmt->execute([$token, $ip, $id]);
    }
    
    public function logout(int $id): void {
        $stmt = $this->db->prepare("UPDATE usuarios SET session_token = NULL WHERE id = ?");
        $stmt->execute([$id]);
    }
}