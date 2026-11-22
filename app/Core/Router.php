<?php

namespace App\Core;

class Router
{
    private Request $request;
    private Response $response;
    private array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, mixed $callback, array $middlewares = []): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }

    public function post(string $path, mixed $callback, array $middlewares = []): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = [
            'callback' => $callback,
            'middlewares' => $middlewares
        ];
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function resolve(): mixed
    {
        $method = $this->request->getMethod();
        
        // Check for manual route param fallback (e.g. index.php?route=/tickets)
        $routeParam = $_GET['route'] ?? null;
        if ($routeParam) {
            $path = $this->normalizePath($routeParam);
        } else {
            $path = $this->normalizePath($this->request->getPath());
        }

        $routesForMethod = $this->routes[$method] ?? [];

        // 1. Exact match
        if (isset($routesForMethod[$path])) {
            $routeInfo = $routesForMethod[$path];
            return $this->executeRoute($routeInfo['callback'], $routeInfo['middlewares'], []);
        }

        // 2. Pattern match with params (e.g., /tickets/{id})
        foreach ($routesForMethod as $routePattern => $routeInfo) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePattern);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->executeRoute($routeInfo['callback'], $routeInfo['middlewares'], $params);
            }
        }

        // 404 Not Found
        $this->response->setStatusCode(404);
        return $this->renderNotFound();
    }

    private function executeRoute(mixed $callback, array $middlewares, array $params): mixed
    {
        // Execute Middlewares
        foreach ($middlewares as $middlewareClass) {
            $middleware = new $middlewareClass();
            $middleware->handle($this->request, $this->response);
        }

        // Execute Callback
        if (is_array($callback)) {
            [$controllerClass, $action] = $callback;
            $controller = new $controllerClass();
            return call_user_func_array([$controller, $action], array_merge([$this->request, $this->response], $params));
        }

        if (is_callable($callback)) {
            return call_user_func_array($callback, array_merge([$this->request, $this->response], $params));
        }

        throw new \Exception("Invalid route callback");
    }

    private function renderNotFound(): string
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Không tìm thấy trang</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>body { font-family: 'Inter', sans-serif; background: #f8fafc; }</style>
        </head>
        <body class="d-flex align-items-center justify-content-center min-vh-100">
            <div class="text-center p-5 bg-white rounded-4 shadow-sm border" style="max-width: 500px;">
                <h1 class="display-1 fw-bold text-primary mb-3">404</h1>
                <h4 class="fw-semibold text-dark mb-2">Trang không tồn tại</h4>
                <p class="text-muted mb-4">Đường dẫn bạn yêu cầu không khả dụng hoặc đã được di chuyển.</p>
                <a href="<?= Response::baseUrl() ?>/" class="btn btn-primary px-4 py-2 rounded-3">Về Trang Chủ</a>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}

