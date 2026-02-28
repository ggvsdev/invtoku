<?php
declare(strict_types=1);

// MODO DEBUG - Cambiar a false en producción
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Definir constante solo si no existe
if (!defined('SIG_START')) {
    define('SIG_START', true);
}

// Verificar que los archivos existan antes de incluirlos
$requiredFiles = [
    __DIR__ . '/config/constants.php',
    __DIR__ . '/core/Router.php',
    __DIR__ . '/core/Response.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("Error: Archivo requerido no encontrado: " . basename($file));
    }
    require_once $file;
}

// Configurar headers de seguridad
if (class_exists('Response')) {
    Response::securityHeaders();
}

// Configurar sesión
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerar ID de sesión periódicamente
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

try {
    // Verificar que Router existe
    if (!class_exists('Router')) {
        throw new Exception("Clase Router no encontrada");
    }

    $router = new Router();

    // RUTAS
    $router->add('', ['controller' => 'AuthController', 'action' => 'login']);
    $router->add('login', ['controller' => 'AuthController', 'action' => 'login']);
    $router->add('dashboard', ['controller' => 'DashboardController', 'action' => 'index']);
    $router->add('inventario/panel', ['controller' => 'InventarioController', 'action' => 'panel']);
    $router->add('inventario/conteo', ['controller' => 'InventarioController', 'action' => 'conteo']);
    $router->add('inventario/historial', ['controller' => 'InventarioController', 'action' => 'historial']);
    $router->add('productos', ['controller' => 'ProductoController', 'action' => 'index']);
    $router->add('productos/form', ['controller' => 'ProductoController', 'action' => 'form']);

    // Procesar URL
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $url = trim($requestUri, '/');

    // Remover 'sig/' si existe
    if (strpos($url, 'sig/') === 0) {
        $url = substr($url, 4);
    }
    if ($url === 'sig') {
        $url = '';
    }

    $router->dispatch($url);

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    if (DEBUG_MODE) {
        echo "<h1>Error del Sistema (Debug Mode)</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Error del sistema</h1>";
        echo "<p>Ha ocurrido un error. Por favor, contacte al administrador.</p>";
    }
}