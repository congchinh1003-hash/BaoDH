<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Asset;
use App\Models\ProjectIssue;
use App\Models\Ticket;
use App\Models\User;

class ProfileController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Màn hình xem Hồ sơ Cá nhân (Profile)
     */
    public function show(Request $request, Response $response): string
    {
        $userId = Auth::id();
        $user = $this->userModel->findById($userId);

        if (!$user) {
            Session::setFlash('danger', 'Không tìm thấy tài khoản người dùng.');
            $response->redirect('/dashboard');
            return '';
        }

        $roles = $this->userModel->getRoles($userId);

        // Lấy danh sách thiết bị được cấp phát
        $assetModel = new Asset();
        $myAssets = $assetModel->getByUser($userId);

        // Lấy danh sách ticket gần đây
        $ticketModel = new Ticket();
        $myTickets = $ticketModel->getTickets([], $userId, 'USER');
        $myTickets = array_slice($myTickets, 0, 5);

        // Lấy danh sách công việc dự án được phân công qua các dự án
        $issueModel = new ProjectIssue();
        $myIssues = $issueModel->getByAssignee($userId, 5);

        return $this->render('user/profile', [
            'pageTitle' => "Hồ Sơ Cá Nhân - {$user['full_name']}",
            'profileUser' => $user,
            'roles'     => $roles,
            'myAssets'  => $myAssets,
            'myTickets' => $myTickets,
            'myIssues'  => $myIssues
        ]);
    }

    /**
     * Cập nhật thông tin Hồ sơ Cá nhân & Mật khẩu
     */
    public function update(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $user = $this->userModel->findById($userId);

        if (!$user) {
            Session::setFlash('danger', 'Tài khoản không hợp lệ.');
            $response->redirect('/profile');
            return;
        }

        $fullName     = trim($request->input('full_name', ''));
        $phone        = trim($request->input('phone', ''));
        $department   = trim($request->input('department', ''));
        $seatLocation = trim($request->input('seat_location', ''));
        $jobTitle     = trim($request->input('job_title', ''));

        $currPass     = trim($request->input('current_password', ''));
        $newPass      = trim($request->input('new_password', ''));
        $confirmPass  = trim($request->input('confirm_password', ''));

        if (empty($fullName)) {
            Session::setFlash('danger', 'Họ và tên không được để trống.');
            $response->redirect('/profile');
            return;
        }

        $db = \App\Core\Database::getConnection();

        // 1. Kiểm tra nếu có đổi mật khẩu
        $passwordUpdateSql = "";
        $params = [
            'fn'   => $fullName,
            'ph'   => $phone,
            'dep'  => $department,
            'seat' => $seatLocation,
            'job'  => $jobTitle,
            'id'   => $userId
        ];

        if (!empty($newPass)) {
            if (empty($currPass)) {
                Session::setFlash('danger', 'Vui lòng nhập mật khẩu hiện tại để xác nhận thay đổi mật khẩu.');
                $response->redirect('/profile');
                return;
            }

            if (!password_verify($currPass, $user['password_hash'])) {
                Session::setFlash('danger', 'Mật khẩu hiện tại không chính xác.');
                $response->redirect('/profile');
                return;
            }

            if (strlen($newPass) < 6) {
                Session::setFlash('danger', 'Mật khẩu mới phải có tối thiểu 6 ký tự.');
                $response->redirect('/profile');
                return;
            }

            if ($newPass !== $confirmPass) {
                Session::setFlash('danger', 'Mật khẩu mới và xác nhận mật khẩu không khớp.');
                $response->redirect('/profile');
                return;
            }

            $passwordUpdateSql = ", password_hash = :p";
            $params['p'] = password_hash($newPass, PASSWORD_BCRYPT);
        }

        try {
            $sql = "
                UPDATE users 
                SET full_name = :fn, phone = :ph, department = :dep, seat_location = :seat, job_title = :job, updated_at = NOW()
                {$passwordUpdateSql}
                WHERE id = :id
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // Cập nhật lại session Auth
            $updatedUser = $this->userModel->findById($userId);
            $roles = $this->userModel->getRoles($userId);
            Auth::login($updatedUser, $roles);

            Session::setFlash('success', 'Hồ sơ cá nhân đã được cập nhật thành công!');
        } catch (\Throwable $e) {
            Session::setFlash('danger', 'Lỗi khi cập nhật hồ sơ: ' . $e->getMessage());
        }

        $response->redirect('/profile');
    }
}

