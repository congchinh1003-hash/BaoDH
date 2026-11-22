<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request, Response $response): void
    {
        if (!Auth::check()) {
            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            $response->redirect('/login');
        }
    }
}

