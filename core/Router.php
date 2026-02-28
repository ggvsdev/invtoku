<?php
declare(strict_types=1);

class Router {
    private array $routes = [];
    private array $params = [];
    
    public function add(string $route, array $params = []): void {
        // Convertir la ruta a regex
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-zA-Z0-9-]+)', $route);
        $route = '/^' . $route . '$/i';
        $this->routes[$route] = $params;
    }
    
    public function match(string $url): bool {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }
    
    public function dispatch(string $url): void {
        // URL vacía = login
        if (empty($url)) {
            $url = '';
        }
        
        if ($this->match($url)) {
            $controller = $this->params['controller'] ?? 'DashboardController';
            $action = $this->params['action'] ?? 'index';
            
            // Eliminar controller y action de los parámetros a pasar al método
            // porque no son parámetros válidos para el método del controlador
            $methodParams = $this->params;
            unset($methodParams['controller']);
            unset($methodParams['action']);
            
            // Cargar archivo del controlador
            $controllerFile = ROOT_PATH . 'controllers/' . $controller . '.php';
            
            if (!file_exists($controllerFile)) {
                throw new Exception("Archivo del controlador no encontrado: " . $controllerFile);
            }
            
            require_once $controllerFile;
            
            if (!class_exists($controller)) {
                throw new Exception("Clase del controlador no encontrada: " . $controller);
            }
            
            $controllerObject = new $controller();
            
            if (!method_exists($controllerObject, $action)) {
                throw new Exception("Método no encontrado: " . $controller . "::" . $action);
            }
            
            // CORREGIDO: Pasar solo los parámetros del método (sin controller/action)
            // y reindexar el array para que sea numérico (no asociativo)
            $methodParams = array_values($methodParams);
            
            call_user_func_array([$controllerObject, $action], $methodParams);
            
        } else {
            // Ruta no encontrada - mostrar login por defecto
            http_response_code(404);
            $loginFile = ROOT_PATH . 'views/auth/login.php';
            if (file_exists($loginFile)) {
                require_once $loginFile;
            } else {
                throw new Exception("Página no encontrada y login no disponible");
            }
        }
    }
    
    public function getParams(): array {
        return $this->params;
    }
}