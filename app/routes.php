<?php

use App\Controllers\AdminController;
use App\Controllers\ApiController;
use App\Controllers\ApprovalController;
use App\Controllers\AssetController;
use App\Controllers\AuthController;
use App\Controllers\CatalogController;
use App\Controllers\ChangeController;
use App\Controllers\DashboardController;
use App\Controllers\KnowledgeBaseController;
use App\Controllers\ProblemController;
use App\Controllers\ReportController;
use App\Controllers\TicketController;
use App\Controllers\JumpServerController;
use App\Controllers\ProjectController;
use App\Controllers\ProfileController;
use App\Controllers\BackupController;
use App\Controllers\AttachmentController;
use App\Controllers\RemoteSupportController;
use App\Core\Auth;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\ManagerMiddleware;
use App\Middleware\StaffMiddleware;

/**
 * @var \App\Core\Router $router
 */
global $router;
if (!isset($router)) {
    return;
}

// ROOT
$router->get('/', function ($request, $response) {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (str_contains($ua, 'TaskFlowMobileApp')) {
        $response->redirect('/mobile');
        return;
    }

    if (!Auth::check()) {
        $response->redirect('/login');
    } elseif (Auth::isManager()) {
        $response->redirect('/manager/dashboard');
    } elseif (Auth::isStaff()) {
        $response->redirect('/staff/work-queue');
    } else {
        $response->redirect('/dashboard');
    }
});

// AUTHENTICATION
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);

// DASHBOARDS & PROFILE
$router->get('/dashboard', [DashboardController::class, 'userDashboard'], [AuthMiddleware::class]);
$router->get('/profile', [ProfileController::class, 'show'], [AuthMiddleware::class]);
$router->post('/profile', [ProfileController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/staff/work-queue', [DashboardController::class, 'staffWorkQueue'], [StaffMiddleware::class]);
$router->get('/manager/dashboard', [DashboardController::class, 'managerDashboard'], [ManagerMiddleware::class]);

// TICKETS & JIRA BOARD
$router->get('/tickets', [TicketController::class, 'index'], [AuthMiddleware::class]);
$router->get('/tickets/board', [TicketController::class, 'kanbanBoard'], [StaffMiddleware::class]);
$router->get('/tickets/unassigned', [TicketController::class, 'unassigned'], [ManagerMiddleware::class]);
$router->get('/tickets/create', [TicketController::class, 'create'], [AuthMiddleware::class]);
$router->post('/tickets/create', [TicketController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/tickets/{id}', [TicketController::class, 'show'], [AuthMiddleware::class]);
$router->post('/tickets/{id}/assign', [TicketController::class, 'assign'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/transfer-group', [TicketController::class, 'transferGroup'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/status', [TicketController::class, 'updateStatus'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/transition-ajax', [TicketController::class, 'ajaxTransitionStatus'], [StaffMiddleware::class]);
$router->post('/tickets/{id}/resolve', [TicketController::class, 'resolve'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/confirm-close', [TicketController::class, 'confirmClose'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/reopen', [TicketController::class, 'reopen'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/comment', [TicketController::class, 'addComment'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/rate', [TicketController::class, 'rate'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/priority', [TicketController::class, 'adjustPriority'], [ManagerMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/duplicate', [TicketController::class, 'markDuplicate'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/cancel', [TicketController::class, 'cancel'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/watch', [TicketController::class, 'toggleWatch'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/link-asset', [TicketController::class, 'linkAsset'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/take', [TicketController::class, 'takeTicket'], [StaffMiddleware::class, CsrfMiddleware::class]);

// ATTACHMENT VIEW & DOWNLOAD
$router->get('/attachments/{id}/view', [AttachmentController::class, 'view'], [AuthMiddleware::class]);
$router->get('/attachments/{id}/download', [AttachmentController::class, 'download'], [AuthMiddleware::class]);

// JIRA WORKLOG, CHECKLIST & ISSUE LINKING ROUTES
$router->post('/tickets/{id}/worklog', [TicketController::class, 'addWorklog'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/checklist', [TicketController::class, 'addChecklistItem'], [StaffMiddleware::class]);
$router->post('/tickets/{id}/checklist/{itemId}/toggle', [TicketController::class, 'toggleChecklistItem'], [StaffMiddleware::class]);
$router->post('/tickets/{id}/checklist/{itemId}/delete', [TicketController::class, 'deleteChecklistItem'], [StaffMiddleware::class]);
$router->post('/tickets/{id}/link-issue', [TicketController::class, 'linkIssue'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/unlink-issue/{linkId}', [TicketController::class, 'unlinkIssue'], [StaffMiddleware::class, CsrfMiddleware::class]);

// ITIL ADVANCED WORKFLOW & CAPABILITY ROUTES
$router->post('/tickets/{id}/escalate-tier', [TicketController::class, 'escalateTier'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/link-problem', [TicketController::class, 'linkProblem'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/link-change', [TicketController::class, 'linkChange'], [StaffMiddleware::class, CsrfMiddleware::class]);

// SERVICE CATALOG (PORTAL)
$router->get('/catalog', [CatalogController::class, 'index'], [AuthMiddleware::class]);

// APPROVAL WORKFLOW CENTER
$router->get('/approvals', [ApprovalController::class, 'index'], [AuthMiddleware::class]);
$router->post('/approvals/decide', [ApprovalController::class, 'decide'], [AuthMiddleware::class, CsrfMiddleware::class]);

// PROBLEM MANAGEMENT & RCA (KEDB)
$router->get('/problems', [ProblemController::class, 'index'], [StaffMiddleware::class]);
$router->get('/problems/create', [ProblemController::class, 'create'], [StaffMiddleware::class]);
$router->post('/problems/store', [ProblemController::class, 'store'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->get('/problems/{id}', [ProblemController::class, 'show'], [StaffMiddleware::class]);
$router->post('/problems/{id}/rca', [ProblemController::class, 'updateRca'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/problems/{id}/link-incident', [ProblemController::class, 'linkIncident'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/problems/{id}/resolve', [ProblemController::class, 'resolve'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/problems/{id}/publish-kb', [ProblemController::class, 'publishKb'], [StaffMiddleware::class, CsrfMiddleware::class]);

// CHANGE & RELEASE MANAGEMENT (RFC)
$router->get('/changes', [ChangeController::class, 'index'], [StaffMiddleware::class]);
$router->get('/changes/create', [ChangeController::class, 'create'], [StaffMiddleware::class]);
$router->post('/changes/store', [ChangeController::class, 'store'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->get('/changes/{id}', [ChangeController::class, 'show'], [StaffMiddleware::class]);
$router->post('/changes/{id}/stage', [ChangeController::class, 'advanceStage'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/changes/{id}/cab-approve', [ChangeController::class, 'cabApprove'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/changes/{id}/complete', [ChangeController::class, 'complete'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/changes/{id}/link-ticket', [ChangeController::class, 'linkTicket'], [StaffMiddleware::class, CsrfMiddleware::class]);

// REPORTS & ANALYTICS (PHASE 3)
$router->get('/reports', [ReportController::class, 'index'], [StaffMiddleware::class]);
$router->get('/reports/staff', [ReportController::class, 'staff'], [StaffMiddleware::class]);
$router->get('/reports/export', [ReportController::class, 'exportCsv'], [StaffMiddleware::class]);

// ASSETS (ITAM - PHASE 2)
$router->get('/assets', [AssetController::class, 'index'], [StaffMiddleware::class]);
$router->get('/assets/create', [AssetController::class, 'create'], [StaffMiddleware::class]);
$router->post('/assets/store', [AssetController::class, 'store'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->get('/assets/{id}', [AssetController::class, 'show'], [AuthMiddleware::class]);
$router->get('/assets/{id}/edit', [AssetController::class, 'edit'], [StaffMiddleware::class]);
$router->post('/assets/{id}/update', [AssetController::class, 'update'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/assets/{id}/handover', [AssetController::class, 'handover'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/assets/{id}/recall', [AssetController::class, 'recall'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/assets/{id}/transfer', [AssetController::class, 'transfer'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/assets/{id}/repair', [AssetController::class, 'addRepair'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/assets/{id}/complete-repair', [AssetController::class, 'completeRepair'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->post('/assets/{id}/status', [AssetController::class, 'changeStatus'], [StaffMiddleware::class, CsrfMiddleware::class]);

// USER PERSONAL ASSETS
$router->get('/my-assets', [AssetController::class, 'myAssets'], [AuthMiddleware::class]);

// KNOWLEDGE BASE (PHASE 4)
$router->get('/kb', [KnowledgeBaseController::class, 'index'], [AuthMiddleware::class]);
$router->get('/kb/manage', [KnowledgeBaseController::class, 'manage'], [StaffMiddleware::class]);
$router->get('/kb/create', [KnowledgeBaseController::class, 'create'], [StaffMiddleware::class]);
$router->post('/kb/store', [KnowledgeBaseController::class, 'store'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->get('/kb/{id}', [KnowledgeBaseController::class, 'show'], [AuthMiddleware::class]);
$router->post('/kb/{id}/vote', [KnowledgeBaseController::class, 'voteHelpful'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/kb/{id}/delete', [KnowledgeBaseController::class, 'delete'], [StaffMiddleware::class, CsrfMiddleware::class]);
$router->get('/api/kb/search', [KnowledgeBaseController::class, 'searchApi'], [AuthMiddleware::class]);

// ASSET QR & LOOKUP (PHASE 4)
$router->get('/assets/{id}/qr', [AssetController::class, 'qr'], [StaffMiddleware::class]);
$router->get('/asset/{code}', [AssetController::class, 'lookup'], [AuthMiddleware::class]);

// API & AI SMART COPILOT (PHASE 5)
$router->get('/api/services', [ApiController::class, 'getServices'], [AuthMiddleware::class]);
$router->get('/api/users/search', [ApiController::class, 'searchUsers'], [AuthMiddleware::class]);
$router->post('/api/ai/triage', [ApiController::class, 'aiTriage'], [AuthMiddleware::class]);
$router->post('/api/ai/suggest', [ApiController::class, 'aiSuggest'], [StaffMiddleware::class]);
$router->post('/api/ai/kb-chat', [ApiController::class, 'kbChat'], [AuthMiddleware::class]);
$router->get('/api/notifications/unread', [\App\Controllers\NotificationController::class, 'getUnread'], [AuthMiddleware::class]);

// NOTIFICATIONS
$router->get('/notifications/read/{id}', [\App\Controllers\NotificationController::class, 'markAsRead'], [AuthMiddleware::class]);
$router->get('/notifications/read-all', [\App\Controllers\NotificationController::class, 'markAllAsRead'], [AuthMiddleware::class]);

// LANGUAGE
$router->get('/lang/{locale}', function($request, $response, $locale) {
    if (in_array($locale, ['vi', 'en'])) {
        \App\Core\Session::set('locale', $locale);
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    $response->redirect($referer);
});

// ADMIN CONFIGURATION
$router->get('/admin/groups', [AdminController::class, 'groups'], [AdminMiddleware::class]);
$router->post('/admin/groups/store', [AdminController::class, 'createGroup'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/groups/{id}/update', [AdminController::class, 'updateGroup'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/groups/{id}/delete', [AdminController::class, 'deleteGroup'], [AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/admin/categories', [AdminController::class, 'categories'], [AdminMiddleware::class]);
$router->post('/admin/categories/store', [AdminController::class, 'createCategory'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/categories/{id}/update', [AdminController::class, 'updateCategory'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'], [AdminMiddleware::class, CsrfMiddleware::class]);

$router->post('/admin/services/store', [AdminController::class, 'createService'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/services/{id}/update', [AdminController::class, 'updateService'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/services/{id}/delete', [AdminController::class, 'deleteService'], [AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/admin/sla', [AdminController::class, 'sla'], [AdminMiddleware::class]);
$router->post('/admin/sla/store', [AdminController::class, 'createSla'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/sla/{id}/update', [AdminController::class, 'updateSla'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/sla/{id}/delete', [AdminController::class, 'deleteSla'], [AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/admin/users', [AdminController::class, 'users'], [AdminMiddleware::class]);
$router->post('/admin/users/store', [AdminController::class, 'createUser'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/update', [AdminController::class, 'updateUser'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser'], [AdminMiddleware::class, CsrfMiddleware::class]);

$router->get('/admin/audit-logs', [AdminController::class, 'auditLogs'], [AdminMiddleware::class]);
$router->get('/admin/audit-logs/export', [AdminController::class, 'exportAuditLogs'], [AdminMiddleware::class]);

// ROLE & PERMISSIONS MATRIX (RBAC)
$router->get('/admin/permissions', [AdminController::class, 'permissionsMatrix'], [AdminMiddleware::class]);
$router->post('/admin/permissions/update', [AdminController::class, 'updatePermissionsMatrix'], [AdminMiddleware::class, CsrfMiddleware::class]);

// DATABASE BACKUP & RESTORE (DISASTER RECOVERY)
$router->get('/admin/backups', [BackupController::class, 'index'], [AdminMiddleware::class]);
$router->post('/admin/backups/create', [BackupController::class, 'create'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/backups/download/{filename}', [BackupController::class, 'download'], [AdminMiddleware::class]);
$router->post('/admin/backups/restore', [BackupController::class, 'restore'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/backups/delete', [BackupController::class, 'delete'], [AdminMiddleware::class, CsrfMiddleware::class]);

// DATA GRIDS EXPORT EXCEL
$router->get('/tickets/export', [TicketController::class, 'exportExcel'], [AuthMiddleware::class]);
$router->get('/assets/export', [AssetController::class, 'exportExcel'], [AuthMiddleware::class]);
$router->get('/projects/{id}/issues/export', [ProjectController::class, 'exportIssues'], [AuthMiddleware::class]);

// JUMPSERVER / BASTION PAM
$router->get('/jump', [JumpServerController::class, 'portal'], [AuthMiddleware::class]);
$router->get('/jump/terminal/{id}', [JumpServerController::class, 'terminal'], [AuthMiddleware::class]);
$router->post('/jump/api/heartbeat', [JumpServerController::class, 'apiHeartbeat'], [AuthMiddleware::class]);
$router->post('/jump/api/command', [JumpServerController::class, 'apiExecuteCommand'], [AuthMiddleware::class]);
$router->post('/jump/api/disconnect', [JumpServerController::class, 'apiDisconnect'], [AuthMiddleware::class]);

// JUMPSERVER ADMIN CONFIGURATION & MONITORING
$router->get('/admin/jump-servers', [JumpServerController::class, 'adminIndex'], [AdminMiddleware::class]);
$router->post('/admin/jump-servers/store', [JumpServerController::class, 'storeServer'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/jump-servers/{id}/update', [JumpServerController::class, 'updateServer'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/jump-servers/{id}/delete', [JumpServerController::class, 'deleteServer'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/jump-servers/permissions/grant', [JumpServerController::class, 'grantPermission'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/jump-servers/permissions/{id}/revoke', [JumpServerController::class, 'revokePermission'], [AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/jump-servers/sessions/{id}/kick', [JumpServerController::class, 'forceKickSession'], [AdminMiddleware::class, CsrfMiddleware::class]);

// ====================================================================
// JIRA PROJECTS & WORKSPACE (BOARD, BACKLOG, TESTCASES, BUGS)
// ====================================================================
$router->get('/projects', [ProjectController::class, 'index'], [AuthMiddleware::class]);
$router->get('/projects/create', [ProjectController::class, 'create'], [AuthMiddleware::class]);
$router->post('/projects/create', [ProjectController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/projects/{id}', [ProjectController::class, 'board'], [AuthMiddleware::class]);
$router->get('/projects/{id}/board', [ProjectController::class, 'board'], [AuthMiddleware::class]);
$router->get('/projects/{id}/backlog', [ProjectController::class, 'backlog'], [AuthMiddleware::class]);
$router->get('/projects/{id}/testcases', [ProjectController::class, 'testcases'], [AuthMiddleware::class]);
$router->get('/projects/{id}/bugs', [ProjectController::class, 'bugs'], [AuthMiddleware::class]);
$router->get('/projects/{id}/issues', [ProjectController::class, 'issues'], [AuthMiddleware::class]);
$router->get('/projects/{id}/issues/create', [ProjectController::class, 'createIssue'], [AuthMiddleware::class]);
$router->get('/projects/{id}/issues/{issueId}', [ProjectController::class, 'showIssue'], [AuthMiddleware::class]);
$router->post('/projects/{id}/issues/{issueId}', [ProjectController::class, 'updateIssue'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/projects/{id}/settings', [ProjectController::class, 'settings'], [AuthMiddleware::class]);
$router->post('/projects/{id}/settings', [ProjectController::class, 'updateSettings'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/projects/{id}/settings/remove-member/{userId}', [ProjectController::class, 'removeMember'], [AuthMiddleware::class]);

// TASKFLOW AJAX APIS (DRAG & DROP, TEST EXECUTION, SPRINT MOVE, QUICK CREATE, COMMENTS, ELIGIBLE PARENTS)
$router->post('/projects/api/update-status', [ProjectController::class, 'ajaxUpdateStatus'], [AuthMiddleware::class]);
$router->post('/projects/api/update-test-status', [ProjectController::class, 'ajaxUpdateTestStatus'], [AuthMiddleware::class]);
$router->post('/projects/api/move-sprint', [ProjectController::class, 'ajaxMoveSprint'], [AuthMiddleware::class]);
$router->post('/projects/api/quick-create', [ProjectController::class, 'ajaxQuickCreate'], [AuthMiddleware::class]);
$router->post('/projects/api/add-comment', [ProjectController::class, 'ajaxAddComment'], [AuthMiddleware::class]);
$router->get('/projects/api/eligible-parents', [ProjectController::class, 'ajaxGetEligibleParents'], [AuthMiddleware::class]);

// REMOTE SUPPORT ASSISTANCE (WEBRTC LIVE DESKTOP & INTERNAL REMOTE CONTROL)
$router->get('/remote-support', [RemoteSupportController::class, 'index'], [AuthMiddleware::class]);
$router->post('/remote-support/join', [RemoteSupportController::class, 'joinByPin'], [AuthMiddleware::class]);
$router->get('/remote-support/request/{ticketId}', [RemoteSupportController::class, 'requestFromTicket'], [AuthMiddleware::class]);
$router->post('/remote-support/create-quick', [RemoteSupportController::class, 'createQuick'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/remote-support/session/{code}', [RemoteSupportController::class, 'session'], [AuthMiddleware::class]);
$router->post('/remote-support/api/signal', [RemoteSupportController::class, 'apiSignalPost'], [AuthMiddleware::class]);
$router->get('/remote-support/api/signal/{code}', [RemoteSupportController::class, 'apiSignalGet'], [AuthMiddleware::class]);
$router->post('/remote-support/api/status/{code}', [RemoteSupportController::class, 'apiUpdateStatus'], [AuthMiddleware::class]);
$router->post('/remote-support/api/ultraviewer/{code}', [RemoteSupportController::class, 'apiSaveUltraviewer'], [AuthMiddleware::class]);
$router->get('/remote-support/api/check-pending', [RemoteSupportController::class, 'apiCheckPending'], [AuthMiddleware::class]);

// NATIVE REMOTE CONTROL AGENT APIS
$router->post('/remote-support/api/control/event', [RemoteSupportController::class, 'apiControlEvent'], [AuthMiddleware::class]);
$router->get('/remote-support/api/control/poll/{code}', [RemoteSupportController::class, 'apiControlPoll']);
$router->post('/remote-support/api/control/heartbeat/{code}', [RemoteSupportController::class, 'apiControlHeartbeat']);
$router->get('/remote-support/api/control/status/{code}', [RemoteSupportController::class, 'apiControlStatus'], [AuthMiddleware::class]);
$router->get('/remote-support/download-agent/{code}', [RemoteSupportController::class, 'downloadAgent']);

// NOVNC + WEBSOCKIFY + TIGHTVNC INTEGRATION (REQUEST -> ACCEPT -> CONTROL)
$router->post('/remote-support/vnc/request', [RemoteSupportController::class, 'vncRequest'], [AuthMiddleware::class]);
$router->get('/remote-support/vnc/check-request', [RemoteSupportController::class, 'vncCheckRequest'], [AuthMiddleware::class]);
$router->post('/remote-support/vnc/respond', [RemoteSupportController::class, 'vncRespond'], [AuthMiddleware::class]);
$router->get('/remote-support/vnc/session-status/{id}', [RemoteSupportController::class, 'vncSessionStatus'], [AuthMiddleware::class]);
$router->post('/remote-support/vnc/end', [RemoteSupportController::class, 'vncEnd'], [AuthMiddleware::class]);

// MOBILE APP & WEBVIEW EMBEDDED INTERFACE
use App\Controllers\MobileController;
$router->get('/mobile', [MobileController::class, 'index'], [AuthMiddleware::class]);
$router->get('/mobile/api/tickets', [MobileController::class, 'apiTickets'], [AuthMiddleware::class]);
$router->get('/mobile/api/tickets/{id}', [MobileController::class, 'apiTicketDetail'], [AuthMiddleware::class]);
$router->post('/mobile/api/tickets/create', [MobileController::class, 'apiCreateTicket'], [AuthMiddleware::class]);
$router->get('/mobile/api/approvals', [MobileController::class, 'apiApprovals'], [AuthMiddleware::class]);
$router->post('/mobile/api/approvals/decide', [MobileController::class, 'apiDecideApproval'], [AuthMiddleware::class]);
$router->get('/mobile/api/asset-scan/{code}', [MobileController::class, 'apiScanAsset'], [AuthMiddleware::class]);
$router->get('/TaskFlow-v1.3.apk', [MobileController::class, 'downloadApk']);
$router->get('/TaskFlow-v1.2.apk', [MobileController::class, 'downloadApk']);
$router->get('/TaskFlow-v1.1.apk', [MobileController::class, 'downloadApk']);
$router->get('/TaskFlow-v1.0.apk', [MobileController::class, 'downloadApk']);
$router->get('/mobile/download-apk', [MobileController::class, 'downloadApk']);



