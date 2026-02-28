<?php
declare(strict_types=1);

// Solo cargar si no ha sido cargado antes
if (!class_exists('Security')) {

class Security {
    
    public static function generateCSRFToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    public static function validateCSRFToken(string $token): bool {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    public static function sanitize(string $data): string {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    public static function generateConteoHash(int $sesionId, int $productoId, string $codigo, int $usuarioId): string {
        $data = $sesionId . '|' . $productoId . '|' . $codigo . '|' . $usuarioId . '|' . microtime(true);
        return hash('sha256', $data);
    }
    
    public static function checkRateLimit(string $identifier, int $maxAttempts = 60, int $window = 60): bool {
        $key = 'rate_limit_' . $identifier;
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'start' => $now];
            return true;
        }
        
        if ($now - $_SESSION[$key]['start'] > $window) {
            $_SESSION[$key] = ['count' => 1, 'start' => $now];
            return true;
        }
        
        if ($_SESSION[$key]['count'] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key]['count']++;
        return true;
    }
    
    public static function logSecurity(string $accion, ?int $usuarioId = null, string $detalle = ''): void {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO logs_seguridad (usuario_id, accion, detalle, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $usuarioId,
                $accion,
                $detalle,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Error en log de seguridad: " . $e->getMessage());
        }
    }
}

} // Fin del if (!class_exists)