<?php
use App\Core\Response;

/**
 * @var array $tickets
 * @var array $filters
 * @var array $priorities
 * @var array $itGroups
 */
$baseUrl = Response::baseUrl();
?>

<!-- BREADCRUMB -->
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb breadcrumb-sm mb-0" style="font-size:0.8rem;">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none text-muted"><i class="bi bi-house me-1"></i>Trang chủ</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Tickets</li>
    </ol>
</nav>

<!-- PAGE HERO -->
<div class="page-hero mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3"
     style="background:#fff; border-left:4px solid #0052CC; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); padding:20px 24px;">
    <div class="d-flex align-items-center gap-3">
        <div style="background:#EAF0FB; border-radius:10px; width:48px; height:48px; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-collection-fill text-primary fs-4"></i>
        </div>
        <div>
            <h4 class="fw-bold text-dark mb-1" style="color:#172B4D !important;">Toàn Bộ Yêu Cầu Hỗ Trợ</h4>
            <p class="text-muted small mb-0" style="color:#5E6C84 !important;">Quản trị và tra cứu danh sách yêu cầu kỹ thuật theo phân loại, trạng thái và thời hạn SLA</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if (\App\Core\Auth::can('export.tickets')): ?>
            <a href="<?= $baseUrl ?>/tickets/export" class="btn btn-outline-success fw-semibold px-3" title="Xuất danh sách ticket sang file Excel">
                <i class="bi bi-file-earmark-excel-fill me-1"></i>Xuất Excel
            </a>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>/tickets/board" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-kanban me-1"></i>Kanban Board
        </a>
        <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary fw-bold px-4 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i>Tạo Ticket Mới
        </a>
    </div>
</div>

<!-- ADVANCED FILTER BAR -->
<div class="card mb-4 border-0 bg-white" style="border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08);">
    <div class="card-body p-3">
        <form action="<?= $baseUrl ?>/tickets" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border:1.5px solid #DFE1E6; border-radius:6px 0 0 6px; background:#FAFBFC !important;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0"
                           style="border:1.5px solid #DFE1E6; border-radius:0 6px 6px 0; background:#FAFBFC;"
                           name="search"
                           placeholder="Mã IT-000001, tiêu đề, người tạo..."
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"
                          style="border:1.5px solid #DFE1E6; border-radius:6px 0 0 6px; background:#FAFBFC !important;">
                        <i class="bi bi-ui-checks-grid text-secondary" style="font-size:0.85rem;"></i>
                    </span>
                    <select class="form-select border-start-0" name="status"
                            style="border:1.5px solid #DFE1E6; border-radius:0 6px 6px 0; background:#FAFBFC;">
                        <option value="">-- Trạng thái --</option>
                        <option value="NEW" <?= ($filters['status'] === 'NEW') ? 'selected' : '' ?>>Mới tạo (NEW)</option>
                        <option value="ASSIGNED" <?= ($filters['status'] === 'ASSIGNED') ? 'selected' : '' ?>>Đã phân công</option>
                        <option value="IN_PROGRESS" <?= ($filters['status'] === 'IN_PROGRESS') ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="WAITING_USER" <?= ($filters['status'] === 'WAITING_USER') ? 'selected' : '' ?>>Chờ User phản hồi</option>
                        <option value="RESOLVED" <?= ($filters['status'] === 'RESOLVED') ? 'selected' : '' ?>>Đã xử lý xong</option>
                        <option value="CLOSED" <?= ($filters['status'] === 'CLOSED') ? 'selected' : '' ?>>Đã đóng</option>
                        <option value="REOPENED" <?= ($filters['status'] === 'REOPENED') ? 'selected' : '' ?>>Mở lại</option>
                    </select>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"
                          style="border:1.5px solid #DFE1E6; border-radius:6px 0 0 6px; background:#FAFBFC !important;">
                        <i class="bi bi-flag text-secondary" style="font-size:0.85rem;"></i>
                    </span>
                    <select class="form-select border-start-0" name="priority_id"
                            style="border:1.5px solid #DFE1E6; border-radius:0 6px 6px 0; background:#FAFBFC;">
                        <option value="">-- Ưu tiên --</option>
                        <?php foreach ($priorities as $pri): ?>
                            <option value="<?= $pri['id'] ?>" <?= ((string)$filters['priority_id'] === (string)$pri['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pri['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"
                          style="border:1.5px solid #DFE1E6; border-radius:6px 0 0 6px; background:#FAFBFC !important;">
                        <i class="bi bi-people text-secondary" style="font-size:0.85rem;"></i>
                    </span>
                    <select class="form-select border-start-0" name="group_id"
                            style="border:1.5px solid #DFE1E6; border-radius:0 6px 6px 0; background:#FAFBFC;">
                        <option value="">-- Nhóm IT phụ trách --</option>
                        <?php foreach ($itGroups as $grp): ?>
                            <option value="<?= $grp['id'] ?>" <?= ((string)$filters['group_id'] === (string)$grp['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($grp['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-bold flex-fill px-3">
                    <i class="bi bi-funnel-fill me-1"></i>Lọc
                </button>
                <a href="<?= $baseUrl ?>/tickets" class="btn btn-outline-secondary px-3" title="Đặt lại bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- TICKETS TABLE -->
<div class="card border-0" style="border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#F4F5F7;">
                <tr>
                    <th style="width:130px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Mã Ticket</th>
                    <th style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Tiêu đề &amp; Người yêu cầu</th>
                    <th style="width:120px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Ưu tiên</th>
                    <th style="width:150px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Nhóm IT</th>
                    <th style="width:140px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Người xử lý</th>
                    <th style="width:150px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Trạng thái</th>
                    <th style="width:170px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Thời hạn SLA</th>
                    <th style="width:90px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;" class="text-end">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div style="padding: 40px 0;">
                                <div style="width:72px; height:72px; background:#F4F5F7; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                                    <i class="bi bi-inbox fs-2 text-secondary opacity-50"></i>
                                </div>
                                <div class="fw-bold text-dark mb-1" style="font-size:1rem;">Không tìm thấy ticket nào</div>
                                <div class="text-muted small mb-3">Không có yêu cầu nào khớp với điều kiện bộ lọc hiện tại.</div>
                                <a href="<?= $baseUrl ?>/tickets" class="btn btn-outline-secondary btn-sm me-2">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Xóa bộ lọc
                                </a>
                                <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Tạo ticket mới
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <?php
                        $priorityCode = strtolower($t['priority_code'] ?? 'normal');
                        $priorityIcon = match($priorityCode) {
                            'urgent' => 'bi-exclamation-circle-fill',
                            'high'   => 'bi-arrow-up-circle-fill',
                            'normal' => 'bi-dash-circle-fill',
                            'low'    => 'bi-arrow-down-circle-fill',
                            default  => 'bi-dash-circle-fill',
                        };
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
                        <tr style="border-bottom:1px solid #F4F5F7;">
                            <td style="padding:14px 16px;">
                                <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>" class="ticket-code-pill">
                                    <i class="bi bi-hash"></i><?= htmlspecialchars($t['ticket_code']) ?>
                                </a>
                            </td>
                            <td style="padding:14px 16px;">
                                <div class="fw-bold text-truncate" style="max-width:320px; color:#172B4D;">
                                    <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>" class="text-decoration-none hover-primary" style="color:#172B4D;">
                                        <?= htmlspecialchars($t['title']) ?>
                                    </a>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($t['requester_name']) ?>
                                    <span class="mx-1 text-secondary">•</span>
                                    <i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($t['category_name']) ?>
                                </div>
                            </td>
                            <td style="padding:14px 16px;">
                                <span class="badge-priority priority-<?= $priorityCode ?>">
                                    <i class="bi <?= $priorityIcon ?> me-1"></i><?= htmlspecialchars($t['priority_name']) ?>
                                </span>
                            </td>
                            <td style="padding:14px 16px;">
                                <span class="small fw-semibold" style="color:#5E6C84;"><?= htmlspecialchars($t['group_name'] ?? 'Chưa phân nhóm') ?></span>
                            </td>
                            <td style="padding:14px 16px;">
                                <?php if (!empty($t['assignee_name'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-circle bg-primary text-white"
                                             style="width:26px;height:26px;font-size:0.72rem;line-height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <?= mb_strtoupper(mb_substr($t['assignee_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?>
                                        </div>
                                        <span class="small fw-semibold" style="color:#172B4D;"><?= htmlspecialchars($t['assignee_name']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border" style="border-radius:999px; font-size:0.75rem;">Chưa gán</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 16px;">
                                <span class="badge-status status-<?= strtolower($t['status']) ?>">
                                    <?= htmlspecialchars($t['status_name'] ?? $t['status']) ?>
                                </span>
                            </td>
                            <td style="padding:14px 16px;">
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
                            <td class="text-end" style="padding:14px 16px;">
                                <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>" class="btn btn-outline-primary btn-sm fw-semibold px-3" style="border-radius:6px;">
                                    Xem <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!empty($tickets)): ?>
    <div class="d-flex justify-content-between align-items-center px-4 py-3 bg-white border-top" style="font-size:0.85rem; color:#5E6C84;">
        <span>Hiển thị <strong><?= count($tickets) ?></strong> yêu cầu</span>
        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Dữ liệu được cập nhật theo thời gian thực</span>
    </div>
    <?php endif; ?>
</div>
