<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }

    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }

    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
    }

    public function patch($path, $callback) {
        $this->addRoute('PATCH', $path, $callback);
    }

    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
    }

    private function addRoute($method, $path, $callback) {
        $this->routes[strtoupper($method)][$path] = $callback;
    }

    public function dispatch($uri, $method) {
        // Handle method spoofing for DELETE, PUT, PATCH
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $method = strtoupper($method);
        $uri = strtok($uri, '?'); // Strip query string

        // First try exact match
        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];
            $this->executeCallback($callback, []);
            return true;
        }

        // Try pattern matching for dynamic routes
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                $this->executeCallback($callback, $matches);
                return true;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        return false;
    }

    private function convertRouteToRegex($route) {
        // Convert /portfolio/delete/{id} to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    private function executeCallback($callback, $params) {
        if (is_array($callback) && count($callback) === 2) {
            [$class, $func] = $callback;
            $controller = new $class();
            // Pass parameters to controller method
            call_user_func_array([$controller, $func], $params);
        } elseif (is_callable($callback)) {
            call_user_func_array($callback, $params);
        }
    }
}