<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Approval;

class ApprovalController extends Controller
{
    private Approval $approvalModel;

    public function __construct()
    {
        $this->approvalModel = new Approval();
    }

    /**
     * Approvals dashboard & pending list
     */
    public function index(Request $request, Response $response): string
    {
        if (!Auth::can('menu.approvals')) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập Trung tâm phê duyệt.');
            $response->redirect('/dashboard');
            return '';
        }

        $userId = Auth::id();
        $isManagerOrAdmin = Auth::isManager() || Auth::isAdmin();

        $status = $request->input('status', 'PENDING');
        
        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }
        if (!$isManagerOrAdmin) {
            $filters['approver_id'] = $userId;
        }

        $approvals = $this->approvalModel->getAll($filters);

        return $this->render('approvals/index', [
            'pageTitle'        => 'Trung Tâm Phê Duyệt (Approval Center)',
            'approvals'        => $approvals,
            'currentStatus'    => $status,
            'isManagerOrAdmin' => $isManagerOrAdmin
        ]);
    }

    /**
     * Decide approval (APPROVE, REJECT, RETURN)
     */
    public function decide(Request $request, Response $response): void
    {
        $approvalId = (int)$request->input('approval_id');
        $decision = strtoupper($request->input('decision'));
        $comment = trim($request->input('comment', ''));
        $userId = Auth::id();

        if (!in_array($decision, ['APPROVED', 'REJECTED', 'RETURNED'])) {
            Session::setFlash('error', 'Quyết định phê duyệt không hợp lệ.');
            $response->redirect('/approvals');
            return;
        }

        $success = $this->approvalModel->decide($approvalId, $userId, $decision, $comment);

        if ($success) {
            $statusLabels = [
                'APPROVED' => 'Đã phê duyệt thành công',
                'REJECTED' => 'Đã từ chối yêu cầu',
                'RETURNED' => 'Đã yêu cầu bổ sung thông tin'
            ];
            Session::setFlash('success', $statusLabels[$decision] ?? 'Đã xử lý phê duyệt.');

            // TRIGGER NOTIFICATION: Notify requester & assignee of approval decision
            try {
                $appr = $this->approvalModel->findById($approvalId);
                if (!empty($appr['ticket_id'])) {
                    $ticketModel = new \App\Models\Ticket();
                    $ticket = $ticketModel->findById((int)$appr['ticket_id']);
                    if ($ticket) {
                        $notifModel = new \App\Models\Notification();
                        $deciderName = Auth::user()['full_name'] ?? 'Cấp quản lý';
                        $decisionText = ($decision === 'APPROVED') ? 'được PHÊ DUYỆT' : (($decision === 'REJECTED') ? 'bị TỪ CHỐI' : 'yêu cầu BỔ SUNG THÔNG TIN');
                        $noteStr = $comment ? " (Lý do: {$comment})" : "";

                        // Notify Requester
                        if (!empty($ticket['requester_id']) && (int)$ticket['requester_id'] !== (int)$userId) {
                            $notifModel->notifyUser((int)$ticket['requester_id'], [
                                'ticket_id' => (int)$ticket['id'],
                                'title'     => "Kết quả duyệt yêu cầu #{$ticket['ticket_code']}",
                                'message'   => "Yêu cầu \"{$ticket['title']}\" của bạn đã {$decisionText} bởi {$deciderName}{$noteStr}.",
                                'type'      => ($decision === 'APPROVED') ? 'success' : 'warning',
                                'link'      => "/tickets/{$ticket['id']}"
                            ]);
                        }

                        // Notify Assignee or Group if approved
                        if ($decision === 'APPROVED') {
                            if (!empty($ticket['assignee_id']) && (int)$ticket['assignee_id'] !== (int)$userId) {
                                $notifModel->notifyUser((int)$ticket['assignee_id'], [
                                    'ticket_id' => (int)$ticket['id'],
                                    'title'     => "Phiếu đã được duyệt: #{$ticket['ticket_code']}",
                                    'message'   => "Yêu cầu \"{$ticket['title']}\" đã được phê duyệt, sẵn sàng triển khai hỗ trợ.",
                                    'type'      => 'info',
                                    'link'      => "/tickets/{$ticket['id']}"
                                ]);
                            } elseif (!empty($ticket['group_id'])) {
                                $notifModel->notifyGroup((int)$ticket['group_id'], [
                                    'ticket_id' => (int)$ticket['id'],
                                    'title'     => "Phiếu đã được duyệt: #{$ticket['ticket_code']}",
                                    'message'   => "Yêu cầu \"{$ticket['title']}\" đã được phê duyệt, sẵn sàng tiếp nhận xử lý.",
                                    'type'      => 'info',
                                    'link'      => "/tickets/{$ticket['id']}"
                                ], $userId);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {}
        } else {
            Session::setFlash('error', 'Xử lý phê duyệt thất bại hoặc bạn không có quyền.');
        }

        // Redirect back to referring page or approvals list
        $returnUrl = $request->input('return_url', '/approvals');
        $response->redirect($returnUrl);
    }
}

