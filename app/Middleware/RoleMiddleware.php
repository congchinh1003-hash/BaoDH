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
            if ($request->isAjax()) {
                $response->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
                return;
            }

            Session::remove('return_url');
            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            $response->redirect('/login');
            return;
        }

        if (!Auth::hasAnyRole($this->allowedRoles())) {
            if ($request->isAjax()) {
                $response->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
                return;
            }

            Session::setFlash('danger', 'Bạn không có quyền truy cập chức năng này.');
            $response->redirect('/dashboard');
        }
    }
}
