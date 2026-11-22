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
            $currentUri = $_SERVER['REQUEST_URI'] ?? '';
            if (!empty($currentUri) && $currentUri !== '/login' && $currentUri !== '/logout') {
                Session::set('return_url', $currentUri);
            }
            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục xem thông tin.');
            $response->redirect('/login');
        }
    }
}
