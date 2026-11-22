<?php

namespace App\Core;

class Response
{
    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }

    public function redirect(string $url): void
    {
        // Support base sub-paths without duplicating baseUrl
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $baseUrl = self::baseUrl();
            if (!empty($baseUrl)) {
                if (str_starts_with($url, $baseUrl . '/')) {
                    // Already contains baseUrl prefix
                } elseif ($url === $baseUrl) {
                    // Exact match
                } else {
                    $url = $baseUrl . '/' . ltrim($url, '/');
                }
            } else {
                $url = '/' . ltrim($url, '/');
            }
        }
        header("Location: {$url}");
        exit;
    }

    public function json(array $data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function baseUrl(): string
    {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        return ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\' || $scriptDir === '') ? '' : rtrim($scriptDir, '/');
    }
}

