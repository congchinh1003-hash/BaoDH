<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request, Response $response): void
    {
        if ($request->isPost()) {
            $token = $request->input('_csrf_token') 
                  ?? $request->input('csrf_token') 
                  ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

            if (!Session::validateCsrf($token)) {
                if ($request->isAjax()) {
                    $response->json(['success' => false, 'message' => 'CSRF token không hợp lệ hoặc đã hết hạn.'], 403);
                } else {
                    Session::setFlash('danger', 'Phiên làm việc hết hạn hoặc token bảo mật không hợp lệ.');
                    $response->redirect('/');
                }
            }
        }
    }
}

