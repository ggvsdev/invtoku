<?php
declare(strict_types=1);

define('SIG_START', true);
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';

session_start();
Response::securityHeaders();

$router = new Router();

// Rutas de API
$router->add('api/auth/login', ['controller' => 'AuthController', 'action' => 'authenticate']);
$router->add('api/auth/logout', ['controller' => 'AuthController', 'action' => 'logout']);

$router->add('api/inventario/sesion', ['controller' => 'InventarioController', 'action' => 'apiCrearSesion']);
$router->add('api/inventario/conteo', ['controller' => 'InventarioController', 'action' => 'apiRegistrarConteo']);
$router->add('api/inventario/conteos', ['controller' => 'InventarioController', 'action' => 'apiGetConteos']);
$router->add('api/inventario/cerrar', ['controller' => 'InventarioController', 'action' => 'apiCerrarSesion']);

$router->add('api/productos', ['controller' => 'ProductoController', 'action' => 'apiCreate']);
$router->add('api/productos/buscar', ['controller' => 'ProductoController', 'action' => 'apiBuscar']);

$url = trim($_SERVER['REQUEST_URI'], '/');
$router->dispatch($url);