<?php
use App\Core\Auth;
use App\Core\Response;

/**
 * @var array $kpi
 * @var array $tickets
 */
$baseUrl = Response::baseUrl();
$user = Auth::user();
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Chào buổi sáng' : ($hour < 18 ? 'Chào buổi chiều' : 'Chào buổi tối');
?>

<!-- PAGE HERO -->
<div class="page-hero mb-4" style="border-left-color: var(--primary);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:40px;height:40px;background:var(--primary-subtle);">
                    <i class="bi bi-person-fill text-primary fs-5"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color:var(--n800);">
                        <?= $greeting ?>, <?= htmlspecialchars($user['full_name'] ?? 'Bạn') ?>! 👋
                    </h4>
                    <p class="small mb-0" style="color:var(--n400);">Cổng Yêu Cầu Hỗ Trợ Kỹ Thuật IT Doanh Nghiệp</p>
                </div>
            </div>
        </div>
        <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Gửi Yêu Cầu Hỗ Trợ Mới
        </a>
    </div>
</div>

<!-- KPI SUMMARY CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:var(--primary-subtle);color:var(--primary);">
                <i class="bi bi-ticket-detailed-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--primary);"><?= (int)($kpi['total_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Tổng yêu cầu</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-left:4px solid #0065FF;">
            <div class="kpi-icon-wrap" style="background:#E6F0FF;color:#0065FF;">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
            <div class="kpi-value" style="color:#0065FF;"><?= (int)($kpi['in_progress_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Đang xử lý</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-left:4px solid var(--warning);">
            <div class="kpi-icon-wrap" style="background:var(--warning-light);color:var(--warning);">
                <i class="bi bi-chat-left-dots-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--warning);"><?= (int)($kpi['waiting_user_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Chờ bạn phản hồi</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-left:4px solid var(--success);">
            <div class="kpi-icon-wrap" style="background:var(--success-light);color:var(--success);">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--success);"><?= (int)($kpi['resolved_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Chờ nghiệm thu</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:var(--n50);color:var(--n400);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--n500);"><?= (int)($kpi['closed_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Đã hoàn thành</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-left:4px solid var(--danger);">
            <div class="kpi-icon-wrap" style="background:var(--danger-light);color:var(--danger);">
                <i class="bi bi-alarm-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--danger);"><?= (int)($kpi['overdue_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Quá SLA</div>
        </div>
    </div>
</div>

<!-- TICKETS TABLE -->
<div class="card" style="border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:none;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"
         style="border-radius:var(--radius-lg) var(--radius-lg) 0 0;border-bottom:1px solid var(--n60);">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-2 d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px;background:var(--primary-subtle);">
                <i class="bi bi-list-columns-reverse text-primary" style="font-size:1rem;"></i>
            </div>
            <span class="fw-bold" style="color:var(--n800);">Yêu Cầu Hỗ Trợ Của Bạn</span>
            <span class="badge rounded-pill" style="background:var(--n50);color:var(--n400);border:1px solid var(--n60);">
                <?= count($tickets) ?>
            </span>
        </div>
        <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tạo mới
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr style="background:var(--n40);">
                    <th style="width:130px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;padding:10px 16px;">Mã Ticket</th>
                    <th style="font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Tiêu đề yêu cầu</th>
                    <th style="width:140px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Phân loại</th>
                    <th style="width:120px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Ưu tiên</th>
                    <th style="width:140px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Nhân viên xử lý</th>
                    <th style="width:150px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Trạng thái</th>
                    <th style="width:180px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Thời hạn SLA</th>
                    <th style="width:110px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;" class="text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox d-block mb-3" style="font-size:3rem;color:var(--n80);"></i>
                            <div class="fw-semibold mb-1" style="color:var(--n500);">Bạn chưa có yêu cầu hỗ trợ nào</div>
                            <div class="small mb-3" style="color:var(--n300);">Nhấn nút bên dưới để gửi yêu cầu đầu tiên</div>
                            <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary btn-sm px-4">
                                <i class="bi bi-plus-lg me-1"></i>Gửi Yêu Cầu Mới
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td style="padding-left:16px;">
                                <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>" class="ticket-code-pill">
                                    <i class="bi bi-hash"></i><?= htmlspecialchars($t['ticket_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-truncate" style="max-width:320px;color:var(--n800);">
                                    <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>"
                                       class="text-decoration-none" style="color:var(--n800);"
                                       onmouseover="this.style.color='var(--primary)'"
                                       onmouseout="this.style.color='var(--n800)'">
                                        <?= htmlspecialchars($t['title']) ?>
                                    </a>
                                </div>
                                <div class="small" style="color:var(--n300);font-size:0.775rem;">
                                    Gửi lúc: <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background:var(--n50);color:var(--n600);border:1px solid var(--n60);font-weight:500;">
                                    <?= htmlspecialchars($t['category_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-priority priority-<?= strtolower($t['priority_code'] ?? 'normal') ?>">
                                    <?= htmlspecialchars($t['priority_name']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($t['assignee_name'])): ?>
                                    <span class="small fw-semibold" style="color:var(--n800);">
                                        <i class="bi bi-person-check me-1" style="color:var(--success);"></i>
                                        <?= htmlspecialchars($t['assignee_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background:var(--warning-light);color:var(--warning);border:1px solid #ffe0a0;">Chờ tiếp nhận</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-status status-<?= strtolower($t['status']) ?>">
                                    <?= htmlspecialchars($t['status_name'] ?? $t['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $isCompleted = in_array($t['status'], ['RESOLVED', 'CLOSED']);
                                $isStopped   = in_array($t['status'], ['CANCELLED', 'DUPLICATE']);
                                $isPaused    = in_array($t['status'], ['WAITING_USER', 'WAITING_VENDOR']);
                                $isOverdue   = (!empty($t['sla_due_at']) && strtotime($t['sla_due_at']) < time() && !$isCompleted && !$isStopped);

                                $completedWithinSla = false;
                                if ($isCompleted) {
                                    if (!empty($t['sla_due_at']) && !empty($t['resolved_at'])) {
                                        $completedWithinSla = (strtotime($t['resolved_at']) <= strtotime($t['sla_due_at']));
                                    } elseif (!empty($t['sla_due_at']) && !empty($t['closed_at'])) {
                                        $completedWithinSla = (strtotime($t['closed_at']) <= strtotime($t['sla_due_at']));
                                    } else {
                                        $completedWithinSla = true;
                                    }
                                }
                                ?>
                                <?php if ($isCompleted): ?>
                                    <div class="small fw-semibold text-muted font-monospace">
                                        <?= !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'N/A' ?>
                                    </div>
                                    <?php if ($completedWithinSla): ?>
                                        <span class="badge mt-1" style="background:#EBFBF0; color:#006644; border:1px solid #006644; border-radius:999px; font-size:0.68rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i>Đạt SLA
                                        </span>
                                    <?php else: ?>
                                        <span class="badge mt-1" style="background:#FFEBE6; color:#DE350B; border:1px solid #DE350B; border-radius:999px; font-size:0.68rem;">
                                            <i class="bi bi-clock-history me-1"></i>Trễ SLA
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($isStopped): ?>
                                    <div class="small fw-semibold text-muted font-monospace">
                                        <?= !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'N/A' ?>
                                    </div>
                                    <span class="badge bg-light text-muted border mt-1" style="border-radius:999px; font-size:0.68rem;">
                                        <i class="bi bi-pause-circle me-1"></i>SLA Đã Dừng
                                    </span>
                                <?php elseif ($isPaused): ?>
                                    <div class="small fw-semibold text-dark font-monospace">
                                        <?= !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'N/A' ?>
                                    </div>
                                    <span class="badge mt-1" style="background:#FFF4E0; color:#974F0C; border:1px solid #FF991F; border-radius:999px; font-size:0.68rem;">
                                        <i class="bi bi-pause-fill me-1"></i>Tạm dừng (<?= $t['status'] === 'WAITING_USER' ? 'Chờ User' : 'Chờ Vendor' ?>)
                                    </span>
                                <?php else: ?>
                                    <div class="sla-live-timer small fw-semibold <?= $isOverdue ? 'text-danger' : 'text-dark' ?>"
                                         data-due="<?= htmlspecialchars($t['sla_due_at'] ?? '') ?>"
                                         data-status="<?= htmlspecialchars($t['status'] ?? '') ?>"
                                         data-paused="0"
                                         data-paused-seconds="<?= (int)($t['sla_paused_seconds'] ?? 0) ?>">
                                        <?= !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'Không áp dụng SLA' ?>
                                    </div>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge text-white mt-1" style="background:#DE350B; border-radius:999px; font-size:0.68rem;">
                                            <i class="bi bi-alarm me-1"></i><?= \App\Services\SlaService::formatOverdueTime($t['sla_due_at']) ?: 'Quá hạn' ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>"
                                   class="btn btn-sm fw-semibold"
                                   style="border:1.5px solid var(--primary);color:var(--primary);border-radius:var(--radius);padding:4px 14px;"
                                   onmouseover="this.style.background='var(--primary)';this.style.color='#fff';"
                                   onmouseout="this.style.background='transparent';this.style.color='var(--primary)';">
                                    Xem <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
