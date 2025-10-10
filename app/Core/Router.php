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
        $method = strtoupper($method);
        $uri = strtok($uri, '?'); // Strip query string

        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];

            if (is_array($callback) && count($callback) === 2) {
                [$class, $func] = $callback;
                $controller = new $class();
                $controller->$func();
            } elseif (is_callable($callback)) {
                $callback();
            }
            return true;
        }

        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        return false;
    }
}
