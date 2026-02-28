<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../config/security.php';

class AuthController {
    
    public function login(): void {
        // Si ya está logueado, ir al dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
        
        $loginFile = ROOT_PATH . 'views/auth/login.php';
        if (!file_exists($loginFile)) {
            throw new Exception("Vista login no encontrada: " . $loginFile);
        }
        
        require_once $loginFile;
    }
    
    public function authenticate(): void {
        try {
            header('Content-Type: application/json');
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                Response::error('Datos JSON inválidos', 400);
                return;
            }
            
            // Validar CSRF
            if (!isset($input['csrf_token']) || !Security::validateCSRFToken($input['csrf_token'])) {
                Response::error('Token de seguridad inválido', 403);
                return;
            }
            
            // Validar campos
            if (empty($input['username']) || empty($input['password'])) {
                Response::error('Usuario y contraseña requeridos', 422);
                return;
            }
            
            $username = Security::sanitize($input['username']);
            $password = $input['password'];
            
            $userModel = new Usuario();
            $user = $userModel->findByUsername($username);
            
            if (!$user) {
                Response::error('Credenciales inválidas', 401);
                return;
            }
            
            // Verificar contraseña
            if (!Security::verifyPassword($password, $user['password_hash'])) {
                Response::error('Credenciales inválidas', 401);
                return;
            }
            
            // Crear sesión
            $sessionToken = bin2hex(random_bytes(32));
            $userModel->updateLastAccess($user['id'], $sessionToken, $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre_completo'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['session_token'] = $sessionToken;
            
            Response::success('Login exitoso', [
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['nombre_completo'],
                    'role' => $user['rol']
                ],
                'redirect' => BASE_URL . 'dashboard'
            ]);
            
        } catch (Exception $e) {
            error_log("Error en authenticate: " . $e->getMessage());
            Response::error('Error interno del servidor', 500);
        }
    }
    
    public function logout(): void {
        if (isset($_SESSION['user_id'])) {
            try {
                $userModel = new Usuario();
                $userModel->logout($_SESSION['user_id']);
            } catch (Exception $e) {
                error_log("Error en logout: " . $e->getMessage());
            }
        }
        
        session_destroy();
        Response::redirect(BASE_URL);
    }
    
    public static function checkAuth(): void {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                Response::error('No autorizado', 401);
            } else {
                header('Location: ' . BASE_URL);
                exit;
            }
        }
    }
}