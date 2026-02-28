<?php
declare(strict_types=1);

// Evitar acceso directo
if (!defined('SIG_START')) {
    die('Acceso denegado');
}

// Constantes del Sistema
define('SIG_VERSION', '1.0.0');
define('SIG_NAME', 'Sistema de Inventario General');

// Detectar protocolo (http o https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// CORREGIDO: URLs y Paths - Asegurar que terminan en /
define('BASE_URL', $protocol . '://' . $host . '/sig/');
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('API_URL', BASE_URL . 'api/');

// Seguridad
define('SESSION_LIFETIME', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);
define('CSRF_TOKEN_NAME', 'sig_csrf_token');

// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sig_inventario');
define('DB_USER', 'root');
define('DB_PASS', '');  // <-- PON TU CONTRASEÑA DE MYSQL AQUÍ SI TIENES UNA
define('DB_CHARSET', 'utf8mb4');

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Entorno (development o production)
define('ENVIRONMENT', 'development');