<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

abstract class RoleMiddleware
{
    abstract protected function allowedRoles(): array;

    public function handle(Request $request, Response $response): void
    {
        if (!Auth::check()) {
            $currentUri = $_SERVER['REQUEST_URI'] ?? '';
            if (!empty($currentUri) && $currentUri !== '/login' && $currentUri !== '/logout') {
                Session::set('return_url', $currentUri);
            }
            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            $response->redirect('/login');
        }

        if (!Auth::hasAnyRole($this->allowedRoles())) {
            Session::setFlash('danger', 'Bạn không có quyền truy cập chức năng này.');
            $response->redirect('/dashboard');
        }
    }
}
