<?php

namespace App\Core;

class Request
{
    public function getMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        return $method === 'HEAD' ? 'GET' : $method;
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
    }

    public function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // Remove query string
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }

        // Support subfolder installation (like /Bao_DATN/public/... or Windows paths)
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        $uri = str_replace('\\', '/', $uri);

        if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }

        $uri = '/' . trim($uri, '/');

        // Strip index.php prefix if present (e.g. /index.php or /index.php/tickets)
        if (str_starts_with($uri, '/index.php/')) {
            $uri = substr($uri, 10);
        } elseif ($uri === '/index.php') {
            $uri = '/';
        }

        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    public function getBody(): array
    {
        $body = [];
        if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                $body[$key] = is_string($value) ? trim($value) : $value;
            }
        }
        return $body;
    }

    public function getQueryParams(): array
    {
        $query = [];
        foreach ($_GET as $key => $value) {
            $query[$key] = is_string($value) ? trim($value) : $value;
        }
        return $query;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public function getFiles(): array
    {
        return $_FILES;
    }
}

