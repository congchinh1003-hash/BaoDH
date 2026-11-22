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
            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            $response->redirect('/login');
        }

        if (!Auth::hasAnyRole($this->allowedRoles())) {
            Session::setFlash('danger', 'Bạn không có quyền truy cập chức năng này.');
            $response->redirect('/dashboard');
        }
    }
}

