<?php
use App\Core\Response;

/**
 * @var array $kpi
 * @var array $queueUrgent
 * @var array $queueInProgress
 * @var array $queueWaiting
 * @var array $queueCompleted
 */
$baseUrl = Response::baseUrl();
$overdueCount = (int)($kpi['overdue_tickets'] ?? 0);
?>

<!-- PAGE HERO -->
<div class="page-hero mb-4" style="border-left-color: var(--danger);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:40px;height:40px;background:var(--danger-light);">
                    <i class="bi bi-fire" style="color:var(--danger);font-size:1.2rem;"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color:var(--n800);">Hàng Đợi Xử Lý Công Việc IT</h4>
                    <p class="small mb-0" style="color:var(--n400);">
                        Tập trung theo thứ tự ưu tiên: Overdue &rarr; Khẩn cấp &rarr; Đang xử lý &rarr; Chờ phản hồi
                    </p>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/tickets/unassigned" class="btn fw-semibold shadow-sm"
               style="background:var(--warning-light);color:#974F0C;border:1.5px solid #ffe0a0;padding:8px 18px;">
                <i class="bi bi-inbox-fill me-1" style="color:var(--warning);"></i>Hàng đợi chưa phân công
            </a>
            <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i>Tạo Ticket
            </a>
        </div>
    </div>
</div>

<!-- OVERDUE CRITICAL ALERT BANNER -->
<?php if ($overdueCount > 0): ?>
    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3"
         style="background:var(--danger-light);border:1.5px solid #ffcdd4;" role="alert">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:42px;height:42px;background:var(--danger);">
            <i class="bi bi-exclamation-triangle-fill text-white fs-5"></i>
        </div>
        <div>
            <div class="fw-bold" style="color:var(--danger);">Cảnh báo Quá hạn SLA (Overdue)!</div>
            <div class="small" style="color:var(--n700);">
                Hiện đang có <strong><?= $overdueCount ?></strong> yêu cầu đã vượt quá thời hạn SLA cam kết. Cần ưu tiên xử lý ngay lập tức.
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- KPI METRIC CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--danger);">
            <div class="kpi-icon-wrap" style="background:var(--danger-light);color:var(--danger);">
                <i class="bi bi-alarm-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--danger);"><?= $overdueCount ?></div>
            <div class="kpi-label fw-bold" style="color:var(--danger);">Quá hạn SLA</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--warning);">
            <div class="kpi-icon-wrap" style="background:var(--warning-light);color:var(--warning);">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--warning);"><?= (int)($kpi['urgent_tickets'] ?? 0) ?></div>
            <div class="kpi-label fw-bold" style="color:var(--warning);">Khẩn cấp (Urgent)</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--primary);">
            <div class="kpi-icon-wrap" style="background:var(--primary-subtle);color:var(--primary);">
                <i class="bi bi-play-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--primary);"><?= (int)($kpi['active_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Đang xử lý</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:#fef3c7;color:#d97706;">
                <i class="bi bi-pause-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:#d97706;"><?= (int)($kpi['waiting_user_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Chờ User (Pause SLA)</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="kpi-icon-wrap" style="background:#f3e8ff;color:#7c3aed;">
                <i class="bi bi-building-gear"></i>
            </div>
            <div class="kpi-value" style="color:#7c3aed;"><?= (int)($kpi['waiting_vendor_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Chờ Vendor</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--success);">
            <div class="kpi-icon-wrap" style="background:var(--success-light);color:var(--success);">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="kpi-value" style="color:var(--success);"><?= (int)($kpi['resolved_today'] ?? 0) ?></div>
            <div class="kpi-label fw-bold" style="color:var(--success);">Xong hôm nay</div>
        </div>
    </div>
</div>

<!-- WORK QUEUE TABS -->
<div class="card" style="border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:none;">
    <div class="card-header bg-white border-bottom-0 pb-0 pt-3"
         style="border-radius:var(--radius-lg) var(--radius-lg) 0 0;">
        <ul class="nav nav-tabs card-header-tabs" id="queueTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold d-flex align-items-center gap-2"
                        id="urgent-tab" data-bs-toggle="tab" data-bs-target="#urgent-pane"
                        type="button" role="tab" style="color:var(--danger);">
                    <i class="bi bi-fire"></i> Cần Xử Lý Ngay
                    <span class="badge rounded-pill" style="background:var(--danger);color:#fff;">
                        <?= count($queueUrgent) ?>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold d-flex align-items-center gap-2"
                        id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress-pane"
                        type="button" role="tab" style="color:var(--primary);">
                    <i class="bi bi-gear-wide-connected"></i> Đang Xử Lý
                    <span class="badge rounded-pill" style="background:var(--primary);color:#fff;">
                        <?= count($queueInProgress) ?>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold d-flex align-items-center gap-2"
                        id="waiting-tab" data-bs-toggle="tab" data-bs-target="#waiting-pane"
                        type="button" role="tab" style="color:var(--warning);">
                    <i class="bi bi-hourglass-split"></i> Đang Chờ / Tạm Dừng
                    <span class="badge rounded-pill" style="background:var(--warning);color:#fff;">
                        <?= count($queueWaiting) ?>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold d-flex align-items-center gap-2"
                        id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-pane"
                        type="button" role="tab" style="color:var(--success);">
                    <i class="bi bi-check2-all"></i> Đã Xong Hôm Nay
                    <span class="badge rounded-pill" style="background:var(--success);color:#fff;">
                        <?= count($queueCompleted) ?>
                    </span>
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="queueTabsContent">

            <!-- TAB 1: CẦN XỬ LÝ NGAY -->
            <div class="tab-pane fade show active" id="urgent-pane" role="tabpanel">
                <?php renderStaffQueueTable($queueUrgent, $baseUrl, 'Không có ticket nào trong diện cần xử lý gấp. Tình trạng hoàn hảo!'); ?>
            </div>

            <!-- TAB 2: ĐANG XỬ LÝ -->
            <div class="tab-pane fade" id="inprogress-pane" role="tabpanel">
                <?php renderStaffQueueTable($queueInProgress, $baseUrl, 'Hiện không có ticket đang xử lý.'); ?>
            </div>

            <!-- TAB 3: ĐANG CHỜ -->
            <div class="tab-pane fade" id="waiting-pane" role="tabpanel">
                <?php renderStaffQueueTable($queueWaiting, $baseUrl, 'Không có ticket nào đang tạm dừng chờ thông tin.'); ?>
            </div>

            <!-- TAB 4: ĐÃ HOÀN THÀNH -->
            <div class="tab-pane fade" id="completed-pane" role="tabpanel">
                <?php renderStaffQueueTable($queueCompleted, $baseUrl, 'Chưa có ticket nào hoàn thành trong ngày hôm nay.'); ?>
            </div>

        </div>
    </div>
</div>

<?php
/**
 * Polished Table Renderer for Staff Work Queue
 */
function renderStaffQueueTable(array $list, string $baseUrl, string $emptyMessage): void {
?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr style="background:var(--n40);">
                    <th style="width:130px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;padding:10px 16px;">Mã Ticket</th>
                    <th style="font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Tiêu đề & Người yêu cầu</th>
                    <th style="width:120px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Độ ưu tiên</th>
                    <th style="width:140px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Nhóm IT</th>
                    <th style="width:140px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Người phụ trách</th>
                    <th style="width:150px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Trạng thái</th>
                    <th style="width:180px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Thời hạn SLA</th>
                    <th style="width:120px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;" class="text-end pe-3">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-check2-circle d-block mb-3" style="font-size:3rem;color:var(--success);opacity:.6;"></i>
                            <div class="fw-semibold" style="color:var(--n500);"><?= $emptyMessage ?></div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($list as $t): ?>
                        <tr>
                            <td style="padding-left:16px;">
                                <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>" class="ticket-code-pill">
                                    <i class="bi bi-hash"></i><?= htmlspecialchars($t['ticket_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-truncate" style="max-width:320px;color:var(--n800);">
                                    <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>"
                                       class="text-decoration-none" style="color:var(--n800);">
                                        <?= htmlspecialchars($t['title']) ?>
                                    </a>
                                </div>
                                <div class="small" style="color:var(--n300);">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($t['requester_name']) ?>
                                    <span class="mx-1">•</span>
                                    <i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($t['category_name']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge-priority priority-<?= strtolower($t['priority_code'] ?? 'normal') ?>">
                                    <?= htmlspecialchars($t['priority_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="small fw-semibold" style="color:var(--n500);">
                                    <?= htmlspecialchars($t['group_name'] ?? 'Chưa phân nhóm') ?>
                                </span>
                            </td>
                            <td>
                                <span class="small fw-semibold" style="color:var(--n800);">
                                    <?= htmlspecialchars($t['assignee_name'] ?? 'Chưa gán') ?>
                                </span>
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
                                <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>" class="btn btn-primary btn-sm py-1 px-3">
                                    Xử lý <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
}
?>
