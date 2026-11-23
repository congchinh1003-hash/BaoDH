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
        $path = $request->getPath();
        $isMobileApp = str_contains($_SERVER['HTTP_USER_AGENT'] ?? '', 'TaskFlowMobileApp');

        if (!Auth::check()) {
            if ($request->isAjax()) {
                $response->json(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.'], 401);
                return;
            }

            // Always store /mobile for mobile app sessions or /mobile paths
            if ($isMobileApp || str_starts_with($path, '/mobile')) {
                Session::set('return_url', '/mobile');
            } elseif (str_starts_with($path, '/asset/')) {
                Session::set('return_url', $path);
            } else {
                Session::remove('return_url');
            }

            Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            $response->redirect('/login');
            return;
        }

        // If authenticated AND inside Mobile App:
        // Any navigation to desktop pages (like /dashboard, /manager/dashboard, /staff/work-queue, /tickets)
        // must automatically be routed to /mobile!
        if ($isMobileApp && !str_starts_with($path, '/mobile') && !str_starts_with($path, '/api/') && !str_starts_with($path, '/logout') && !str_starts_with($path, '/TaskFlow')) {
            $response->redirect('/mobile');
            return;
        }
    }
}
