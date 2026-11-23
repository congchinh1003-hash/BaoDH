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
            if ($request->isAjax()) {
                $response->json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.'], 401);
                return;
            }

            // Store return_url if user is accessing an asset via QR code scan (/asset/{code}) or mobile view (/mobile)
            $path = $request->getPath();
            if (str_starts_with($path, '/asset/') || str_starts_with($path, '/mobile')) {
                Session::set('return_url', $path);
            } else {
                Session::remove('return_url');
            }

            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục xem thông tin.');
            $response->redirect('/login');
        }
    }
}
