<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Notification;
use App\Models\RemoteSession;
use App\Models\Ticket;
use App\Models\User;

class RemoteSupportController extends Controller
{
    private RemoteSession $remoteModel;

    public function __construct()
    {
        $this->remoteModel = new RemoteSession();
    }

    /**
     * Remote Support Hub (/remote-support)
     */
    public function index(Request $request, Response $response): string
    {
        $user = Auth::user();
        $isStaff = Auth::isStaff() || Auth::isManager() || Auth::isAdmin();

        $activeSessions = $isStaff ? $this->remoteModel->getAllRecent(15) : [];
        $pendingForMe = $this->remoteModel->findActiveForClient((int)$user['id']);

        $userModel = new User();
        $allUsers = $isStaff ? $userModel->getAllUsers() : [];

        return $this->render('remote/index', [
            'pageTitle'      => 'Hỗ Trợ Từ Xa (Remote Desktop Assistance)',
            'isStaff'        => $isStaff,
            'activeSessions' => $activeSessions,
            'pendingForMe'   => $pendingForMe,
            'allUsers'       => $allUsers
        ]);
    }

    /**
     * Join session by PIN code
     */
    public function joinByPin(Request $request, Response $response): void
    {
        $pin = trim($request->input('pin') ?? '');
        if (empty($pin)) {
            $this->setFlash('danger', 'Vui lòng nhập mã PIN kết nối.');
            $response->redirect('/remote-support');
            return;
        }

        $session = $this->remoteModel->findByCode($pin);
        if (!$session) {
            $this->setFlash('danger', "Không tìm thấy phiên hỗ trợ có mã: {$pin}");
            $response->redirect('/remote-support');
            return;
        }

        $response->redirect("/remote-support/session/{$session['session_code']}");
    }

    /**
     * IT creates remote assistance request directly from a Ticket
     */
    public function requestFromTicket(Request $request, Response $response, string $ticketId): void
    {
        if (!Auth::isStaff() && !Auth::isManager() && !Auth::isAdmin()) {
            $this->setFlash('danger', 'Chỉ kỹ thuật viên IT mới có quyền tạo yêu cầu Remote hỗ trợ.');
            $response->redirect("/tickets/{$ticketId}");
            return;
        }

        $ticketModel = new Ticket();
        $ticket = $ticketModel->findById((int)$ticketId);
        if (!$ticket) {
            $this->setFlash('danger', 'Không tìm thấy ticket.');
            $response->redirect('/tickets');
            return;
        }

        $staff = Auth::user();
        $clientId = (int)$ticket['requester_id'];

        // Check if an active session already exists for this ticket
        $existing = $this->remoteModel->findActiveForTicket((int)$ticketId);
        if ($existing) {
            $response->redirect("/remote-support/session/{$existing['session_code']}");
            return;
        }

        // Create new session
        $note = "Remote hỗ trợ cho sự cố: " . $ticket['title'];
        $session = $this->remoteModel->createSession((int)$staff['id'], $clientId, (int)$ticketId, $note);

        // Notify client in real-time
        $notifModel = new Notification();
        $notifModel->create([
            'user_id'   => $clientId,
            'ticket_id' => (int)$ticketId,
            'title'     => "🔔 Yêu cầu kết nối Remote máy tính từ IT {$staff['full_name']}",
            'message'   => "Kỹ thuật viên đang yêu cầu kết nối remote để kiểm tra máy tính cho sự cố [{$ticket['ticket_code']}]. Bấm vào để cấp quyền chia sẻ màn hình.",
            'type'      => 'remote',
            'link'      => "/remote-support/session/{$session['session_code']}"
        ]);

        // Add internal comment into ticket history
        $ticketModel->addComment(
            (int)$ticketId, 
            (int)$staff['id'], 
            "🖥️ **[HỆ THỐNG REMOTE DESKTOP]** Kỹ thuật viên **{$staff['full_name']}** đã khởi tạo phiên hỗ trợ trực tuyến từ xa mã `{$session['session_code']}`. [Bấm vào đây để tham gia phiên kết nối](/remote-support/session/{$session['session_code']})", 
            true
        );

        $this->setFlash('success', "Đã tạo phiên Remote {$session['session_code']}. Vui lòng chờ người dùng chấp nhận chia sẻ.");
        $response->redirect("/remote-support/session/{$session['session_code']}");
    }

    /**
     * Create quick manual session between staff and user
     */
    public function createQuick(Request $request, Response $response): void
    {
        if (!Auth::isStaff() && !Auth::isManager() && !Auth::isAdmin()) {
            $this->setFlash('danger', 'Chỉ kỹ thuật viên IT mới có quyền tạo phiên hỗ trợ.');
            $response->redirect('/remote-support');
            return;
        }

        $clientId = (int)$request->input('client_id');
        $notes = trim($request->input('notes') ?? 'Hỗ trợ kỹ thuật nhanh');

        if (!$clientId) {
            $this->setFlash('danger', 'Vui lòng chọn nhân viên cần hỗ trợ.');
            $response->redirect('/remote-support');
            return;
        }

        $staff = Auth::user();
        $session = $this->remoteModel->createSession((int)$staff['id'], $clientId, null, $notes);

        $notifModel = new Notification();
        $notifModel->create([
            'user_id'   => $clientId,
            'ticket_id' => null,
            'title'     => "🔔 Yêu cầu kết nối Remote máy tính từ IT {$staff['full_name']}",
            'message'   => "Kỹ thuật viên đang yêu cầu kết nối remote để hỗ trợ máy tính cho bạn. Bấm vào để cấp quyền chia sẻ màn hình.",
            'type'      => 'remote',
            'link'      => "/remote-support/session/{$session['session_code']}"
        ]);

        $response->redirect("/remote-support/session/{$session['session_code']}");
    }

    /**
     * Interactive Remote Screen Room (/remote-support/session/{code})
     */
    public function session(Request $request, Response $response, string $code): string
    {
        $session = $this->remoteModel->findByCode($code);
        if (!$session) {
            $this->setFlash('danger', "Phiên hỗ trợ {$code} không tồn tại hoặc đã kết thúc.");
            $response->redirect('/remote-support');
            return '';
        }

        $userId = Auth::id();
        $isStaff = ($userId === (int)$session['staff_id']) || Auth::isAdmin() || Auth::isManager();
        $isClient = ($userId === (int)$session['client_id']);

        if (!$isStaff && !$isClient) {
            $this->setFlash('danger', 'Bạn không có quyền tham gia vào phiên hỗ trợ kỹ thuật này.');
            $response->redirect('/dashboard');
            return '';
        }

        $roleInSession = $isStaff ? 'STAFF' : 'CLIENT';

        return $this->render('remote/session', [
            'pageTitle'     => "Phiên Remote #{$session['session_code']} - TaskFlow Live Assistance",
            'session'       => $session,
            'roleInSession' => $roleInSession,
            'isStaff'       => $isStaff,
            'isClient'      => $isClient
        ]);
    }

    /**
     * AJAX API: Send WebRTC Signal or Action (OFFER, ANSWER, CANDIDATE, POINTER, CHAT, COMMAND)
     */
    public function apiSignalPost(Request $request, Response $response): void
    {
        $body = $request->getBody();
        $code = trim($body['session_code'] ?? '');
        $signalType = trim($body['signal_type'] ?? '');
        $payload = is_string($body['payload'] ?? null) ? $body['payload'] : json_encode($body['payload'] ?? []);
        $senderType = trim($body['sender_type'] ?? '');

        if (!$code || !$signalType || !$senderType) {
            $response->json(['success' => false, 'message' => 'Missing parameters'], 400);
            return;
        }

        $session = $this->remoteModel->findByCode($code);
        if (!$session) {
            $response->json(['success' => false, 'message' => 'Session not found'], 404);
            return;
        }

        $insertId = $this->remoteModel->addSignal((int)$session['id'], $senderType, $signalType, $payload);

        // Auto update session status if needed
        if ($signalType === 'OFFER' || $signalType === 'ANSWER') {
            $this->remoteModel->updateStatus((int)$session['id'], 'CONNECTED');
        }

        $response->json(['success' => true, 'signal_id' => $insertId]);
    }

    /**
     * AJAX API: Poll new signals
     */
    public function apiSignalGet(Request $request, Response $response, string $code): void
    {
        $session = $this->remoteModel->findByCode($code);
        if (!$session) {
            $response->json(['success' => false, 'message' => 'Session not found'], 404);
            return;
        }

        $forType = trim($request->input('for_type') ?? 'STAFF');
        $afterId = (int)($request->input('after_id') ?? 0);

        $signals = $this->remoteModel->getSignals((int)$session['id'], $forType, $afterId);

        $response->json([
            'success'        => true,
            'signals'        => $signals,
            'session_status' => $session['status'],
            'ultraviewer_id' => $session['ultraviewer_id'],
            'ultraviewer_pass' => $session['ultraviewer_pass']
        ]);
    }

    /**
     * AJAX API: Update session status (CONNECTED, ENDED, REJECTED)
     */
    public function apiUpdateStatus(Request $request, Response $response, string $code): void
    {
        $body = $request->getBody();
        $status = trim($body['status'] ?? '');

        $session = $this->remoteModel->findByCode($code);
        if (!$session || !in_array($status, ['WAITING', 'CONNECTED', 'ENDED', 'REJECTED'])) {
            $response->json(['success' => false, 'message' => 'Invalid parameters'], 400);
            return;
        }

        $this->remoteModel->updateStatus((int)$session['id'], $status);

        // If ended, post signal to peer
        $senderType = (Auth::id() === (int)$session['staff_id']) ? 'STAFF' : 'CLIENT';
        $this->remoteModel->addSignal((int)$session['id'], $senderType, 'STATUS', json_encode(['status' => $status]));

        $response->json(['success' => true]);
    }

    /**
     * AJAX API: Save UltraViewer credentials from client
     */
    public function apiSaveUltraviewer(Request $request, Response $response, string $code): void
    {
        $body = $request->getBody();
        $uvId = trim($body['ultraviewer_id'] ?? '');
        $uvPass = trim($body['ultraviewer_pass'] ?? '');

        $session = $this->remoteModel->findByCode($code);
        if (!$session) {
            $response->json(['success' => false, 'message' => 'Session not found'], 404);
            return;
        }

        $this->remoteModel->updateUltraviewer((int)$session['id'], $uvId, $uvPass);

        // Notify staff via signal
        $this->remoteModel->addSignal(
            (int)$session['id'], 
            'CLIENT', 
            'ULTRAVIEWER', 
            json_encode(['id' => $uvId, 'pass' => $uvPass])
        );

        $response->json(['success' => true]);
    }

    /**
     * Polling API for global navbar popup
     */
    public function apiCheckPending(Request $request, Response $response): void
    {
        $user = Auth::user();
        if (!$user) {
            $response->json(['pending' => null]);
            return;
        }

        $pending = $this->remoteModel->findActiveForClient((int)$user['id']);
        $response->json(['pending' => $pending]);
    }
}
