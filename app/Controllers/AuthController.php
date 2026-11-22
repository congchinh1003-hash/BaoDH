<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(Request $request, Response $response): string
    {
        if (Auth::check()) {
            $returnUrl = Session::get('return_url');
            if ($returnUrl && is_string($returnUrl) && str_starts_with($returnUrl, '/')) {
                Session::remove('return_url');
                $response->redirect($returnUrl);
                return '';
            }
            $this->redirectBasedOnRole($response);
        }

        $db = \App\Core\Database::getConnection();
        $allUsers = $db->query('
            SELECT u.id, u.username, u.email, u.full_name, u.department, 
                   COALESCE(GROUP_CONCAT(r.code SEPARATOR ", "), "USER") as roles, 
                   COALESCE(GROUP_CONCAT(r.name SEPARATOR ", "), "End User") as role_names 
            FROM users u 
            LEFT JOIN user_roles ur ON u.id = ur.user_id 
            LEFT JOIN roles r ON ur.role_id = r.id 
            WHERE u.is_active = 1
            GROUP BY u.id 
            ORDER BY u.id ASC
        ')->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('auth/login', [
            'pageTitle' => 'Đăng nhập - TaskFlow IT Service Management',
            'allUsers'  => $allUsers
        ], 'auth');
    }

    public function login(Request $request, Response $response): void
    {
        $body = $request->getBody();
        $account = trim($body['account'] ?? '');
        $password = trim($body['password'] ?? '');

        if (empty($account) || empty($password)) {
            $this->setFlash('danger', 'Vui lòng nhập tên đăng nhập/email và mật khẩu.');
            $response->redirect('/login');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByUsernameOrEmail($account);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->setFlash('danger', 'Tên đăng nhập hoặc mật khẩu không chính xác.');
            $response->redirect('/login');
            return;
        }

        // Get roles
        $roles = $userModel->getRoles((int)$user['id']);
        if (empty($roles)) {
            $roles = ['USER'];
        }

        Auth::login($user, $roles);
        $this->setFlash('success', "Chào mừng " . htmlspecialchars($user['full_name']) . " đã đăng nhập thành công.");

        // Check if there is a pending return URL (e.g. from QR code scan on mobile)
        $returnUrl = Session::get('return_url');
        if ($returnUrl && is_string($returnUrl) && str_starts_with($returnUrl, '/')) {
            Session::remove('return_url');
            $response->redirect($returnUrl);
            return;
        }

        $this->redirectBasedOnRole($response);
    }

    public function logout(Request $request, Response $response): void
    {
        Auth::logout();
        $this->setFlash('info', 'Bạn đã đăng xuất khỏi hệ thống.');
        $response->redirect('/login');
    }

    private function redirectBasedOnRole(Response $response): void
    {
        if (Auth::isAdmin() || Auth::isManager()) {
            $response->redirect('/manager/dashboard');
        } elseif (Auth::isStaff()) {
            $response->redirect('/staff/work-queue');
        } else {
            $response->redirect('/dashboard');
        }
    }
}

