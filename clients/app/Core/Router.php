<?php
class Router {
    protected $routes = [];

    public function add($method, $uri, $controller, $action, $middleware = []) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    public function get($uri, $controller, $action, $middleware = []) {
        $this->add('GET', $uri, $controller, $action, $middleware);
    }

    public function post($uri, $controller, $action, $middleware = []) {
        $this->add('POST', $uri, $controller, $action, $middleware);
    }

    public function dispatch($uri) {
        // Remove query string from URI
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Clean up subfolder path if any (in case clients folder is used as base)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\') {
            $uri = str_replace($scriptName, '', $uri);
        }
        $uri = '/' . trim($uri, '/');

        $method = $_SERVER['REQUEST_METHOD'];
        $matched = false;

        foreach ($this->routes as $route) {
            $pattern = '@^' . str_replace('/', '\/', $route['uri']) . '$@';
            if (preg_match($pattern, $uri, $matches) && $route['method'] === $method) {
                $matched = true;
                array_shift($matches); // Remove the full match
                
                // Execute Middleware
                foreach ($route['middleware'] as $middleware) {
                    require_once BASE_PATH . "/app/Middleware/{$middleware}.php";
                    $middlewareInstance = new $middleware();
                    if (!$middlewareInstance->handle()) {
                        return; // Middleware stopped execution
                    }
                }

                $controller = $route['controller'];
                $action = $route['action'];

                if (strpos($controller, '\\') !== false) {
                    // Namespaced controller (loaded via autoloader)
                    if (class_exists($controller)) {
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $action)) {
                            $controllerInstance->$action(new \App\Core\Request(), $matches);
                        } else {
                            $this->send404("Method $action not found in $controller.");
                        }
                    } else {
                        $this->send404("Controller $controller not found.");
                    }
                } else {
                    // Legacy controller
                    $controllerFile = BASE_PATH . "/app/Controllers/{$controller}.php";
                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $action)) {
                            // Legacy controllers don't take Request/matches
                            $controllerInstance->$action();
                        } else {
                            $this->send404("Method $action not found in $controller.");
                        }
                    } else {
                        $this->send404("Controller $controller not found.");
                    }
                }
                return;
            }
        }

        if (!$matched) {
            $this->send404("Route not found: $uri");
        }
    }

    private function send404($message = "404 Not Found") {
        http_response_code(404);
        echo $message;
    }
}
