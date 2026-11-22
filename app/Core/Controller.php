<?php

namespace App\Core;

class Controller
{
    protected function render(string $view, array $params = [], string $layout = 'main'): string
    {
        $viewFile = __DIR__ . "/../Views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }

        extract($params);

        // Render inner view
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If layout is empty or null, return view only
        if (!$layout) {
            return $content;
        }

        $layoutFile = __DIR__ . "/../Views/layouts/{$layout}.php";
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: {$layoutFile}");
        }

        ob_start();
        require $layoutFile;
        return ob_get_clean();
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        (new Response())->json($data, $statusCode);
    }

    protected function redirect(string $url): void
    {
        (new Response())->redirect($url);
    }

    protected function setFlash(string $type, string $message): void
    {
        Session::setFlash($type, $message);
    }
}

