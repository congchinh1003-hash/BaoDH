<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, Response $response, string $id): void
    {
        $notificationModel = new Notification();
        $notif = $notificationModel->findById((int)$id);
        $notificationModel->markAsRead((int)$id, Auth::id());

        $redirect = $request->input('redirect');
        if (!empty($redirect)) {
            $response->redirect($redirect);
            return;
        }

        if ($notif) {
            if (!empty($notif['link'])) {
                $response->redirect($notif['link']);
                return;
            }
            if (!empty($notif['ticket_id'])) {
                $response->redirect('/tickets/' . $notif['ticket_id']);
                return;
            }
        }

        $response->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
    }

    public function markAllAsRead(Request $request, Response $response): void
    {
        $notificationModel = new Notification();
        $notificationModel->markAllAsRead(Auth::id());
        $response->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public function getUnread(Request $request, Response $response): void
    {
        $notificationModel = new Notification();
        $unread = $notificationModel->getUnreadForUser(Auth::id());
        $this->json(['success' => true, 'data' => $unread]);
    }
}

