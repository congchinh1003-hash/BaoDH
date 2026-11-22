<?php
use App\Core\Response;

/**
 * @var array $kpi
 * @var array $groupWorkload
 * @var array $staffWorkload
 * @var array $unassignedTickets
 */
$baseUrl = Response::baseUrl();
?>

<!-- PAGE HERO -->
<div class="page-hero mb-4" style="border-left-color: var(--primary);">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:40px;height:40px;background:var(--primary-subtle);">
                    <i class="bi bi-graph-up-arrow text-primary fs-5"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0" style="color:var(--n800);">Quản Lý Vận Hành & Hiệu Suất</h4>
                    <p class="small mb-0" style="color:var(--n400);">Giám sát năng suất kỹ thuật viên, phân bổ tài nguyên nhóm và mức độ tuân thủ SLA</p>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/tickets/unassigned" class="btn fw-bold shadow-sm"
               style="background:var(--warning-light);color:#974F0C;border:1.5px solid #ffe0a0;padding:8px 18px;">
                <i class="bi bi-inbox-fill me-1"></i>Hàng đợi Chờ Phân Công
                <span class="badge ms-1" style="background:#974F0C;color:#fff;border-radius:var(--radius-pill);">
                    <?= (int)($kpi['unassigned_tickets'] ?? 0) ?>
                </span>
            </a>
            <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i>Tạo Ticket Mới
            </a>
        </div>
    </div>
</div>

<!-- TOP METRIC CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--primary);">
            <div class="kpi-icon-wrap" style="background:var(--primary-subtle);color:var(--primary);">
                <i class="bi bi-layers-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--primary);"><?= (int)($kpi['total_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Tổng ticket tiếp nhận</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--warning);">
            <div class="kpi-icon-wrap" style="background:var(--warning-light);color:var(--warning);">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--warning);"><?= (int)($kpi['unassigned_tickets'] ?? 0) ?></div>
            <div class="kpi-label fw-bold" style="color:var(--warning);">Chờ phân công</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid #0065FF;">
            <div class="kpi-icon-wrap" style="background:#E6F0FF;color:#0065FF;">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <div class="kpi-value" style="color:#0065FF;"><?= (int)($kpi['active_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Đang xử lý (Active)</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--danger);">
            <div class="kpi-icon-wrap" style="background:var(--danger-light);color:var(--danger);">
                <i class="bi bi-alarm-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--danger);"><?= (int)($kpi['overdue_tickets'] ?? 0) ?></div>
            <div class="kpi-label fw-bold" style="color:var(--danger);">Quá hạn SLA</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--success);">
            <div class="kpi-icon-wrap" style="background:var(--success-light);color:var(--success);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--success);"><?= (int)($kpi['closed_tickets'] ?? 0) ?></div>
            <div class="kpi-label">Đã đóng (Closed)</div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <div class="kpi-card" style="border-top:4px solid var(--success);background:var(--success-light);">
            <div class="kpi-icon-wrap" style="background:#fff;color:var(--success);box-shadow:var(--shadow-xs);">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div class="kpi-value" style="color:var(--success);"><?= $kpi['sla_rate'] ?? 100 ?>%</div>
            <div class="kpi-label fw-bold" style="color:var(--success);">Tỷ lệ đạt SLA</div>
        </div>
    </div>
</div>

<!-- WORKLOAD TABLES ROW -->
<div class="row g-4 mb-4">

    <!-- WORKLOAD THEO NHÓM IT -->
    <div class="col-12 col-xl-6">
        <div class="card h-100" style="border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:none;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-radius:var(--radius-lg) var(--radius-lg) 0 0;border-bottom:1px solid var(--n60);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center"
                         style="width:32px;height:32px;background:var(--primary-subtle);">
                        <i class="bi bi-diagram-3-fill text-primary" style="font-size:1rem;"></i>
                    </div>
                    <span class="fw-bold" style="color:var(--n800);">Khối Lượng Theo Nhóm IT</span>
                </div>
                <span class="badge rounded-pill" style="background:var(--n50);color:var(--n400);border:1px solid var(--n60);">
                    <?= count($groupWorkload) ?> Nhóm
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:var(--n40);">
                            <th style="font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;padding:10px 16px;">Nhóm IT</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Tổng</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--primary);font-weight:600;">Active</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--warning);font-weight:600;">Waiting</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--danger);font-weight:600;">Overdue</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Chưa gán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupWorkload as $gw): ?>
                            <tr>
                                <td style="padding-left:16px;">
                                    <div class="fw-bold" style="color:var(--n800);"><?= htmlspecialchars($gw['group_name']) ?></div>
                                    <div class="small" style="color:var(--n300);font-size:0.75rem;"><?= htmlspecialchars($gw['group_code']) ?></div>
                                </td>
                                <td class="text-center fw-bold" style="color:var(--n700);"><?= (int)$gw['total'] ?></td>
                                <td class="text-center fw-semibold" style="color:var(--primary);"><?= (int)$gw['active'] ?></td>
                                <td class="text-center fw-semibold" style="color:var(--warning);"><?= (int)$gw['waiting'] ?></td>
                                <td class="text-center fw-bold" style="color:var(--danger);"><?= (int)$gw['overdue'] ?></td>
                                <td class="text-center">
                                    <?php if ((int)$gw['unassigned'] > 0): ?>
                                        <span class="badge rounded-pill" style="background:var(--warning);color:#fff;padding:3px 8px;">
                                            <?= (int)$gw['unassigned'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--n300);">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- WORKLOAD THEO NHÂN VIÊN -->
    <div class="col-12 col-xl-6">
        <div class="card h-100" style="border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:none;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 style="border-radius:var(--radius-lg) var(--radius-lg) 0 0;border-bottom:1px solid var(--n60);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-2 d-flex align-items-center justify-content-center"
                         style="width:32px;height:32px;background:var(--primary-subtle);">
                        <i class="bi bi-people-fill text-primary" style="font-size:1rem;"></i>
                    </div>
                    <span class="fw-bold" style="color:var(--n800);">Năng Suất Kỹ Thuật Viên</span>
                </div>
                <span class="badge rounded-pill" style="background:var(--n50);color:var(--n400);border:1px solid var(--n60);">
                    <?= count($staffWorkload) ?> Kỹ thuật
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:var(--n40);">
                            <th style="font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;padding:10px 16px;">Nhân viên IT</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--primary);font-weight:600;">Active</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:#0065FF;font-weight:600;">Need Action</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--danger);font-weight:600;">Overdue</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--success);font-weight:600;">Resolved</th>
                            <th class="text-center" style="font-size:11px;text-transform:uppercase;color:var(--danger);font-weight:600;">Reopen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffWorkload as $sw): ?>
                            <tr>
                                <td style="padding-left:16px;">
                                    <div class="fw-bold" style="color:var(--n800);"><?= htmlspecialchars($sw['full_name']) ?></div>
                                    <div class="small" style="color:var(--n300);font-size:0.75rem;"><?= htmlspecialchars($sw['email']) ?></div>
                                </td>
                                <td class="text-center fw-bold" style="color:var(--primary);"><?= (int)$sw['active'] ?></td>
                                <td class="text-center fw-semibold" style="color:#0065FF;"><?= (int)$sw['need_action'] ?></td>
                                <td class="text-center fw-bold" style="color:var(--danger);"><?= (int)$sw['overdue'] ?></td>
                                <td class="text-center fw-semibold" style="color:var(--success);"><?= (int)$sw['resolved'] ?></td>
                                <td class="text-center">
                                    <?php if ((int)$sw['total_reopens'] > 0): ?>
                                        <span class="badge rounded-pill" style="background:var(--danger);color:#fff;padding:3px 8px;">
                                            <?= (int)$sw['total_reopens'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--n300);">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- UNASSIGNED QUEUE ALERT DRAWER -->
<div class="card" style="border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1.5px solid #ffe0a0;">
    <div class="card-header py-3 d-flex justify-content-between align-items-center"
         style="background:var(--warning-light);border-radius:var(--radius-lg) var(--radius-lg) 0 0;border-bottom:1.5px solid #ffe0a0;">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-2 d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px;background:#fff;box-shadow:var(--shadow-xs);">
                <i class="bi bi-exclamation-octagon-fill" style="color:var(--warning);font-size:1rem;"></i>
            </div>
            <span class="fw-bold" style="color:var(--n800);">Hàng Đợi Cần Phân Công Ngay (Unassigned Tickets)</span>
        </div>
        <a href="<?= $baseUrl ?>/tickets/unassigned" class="btn btn-sm fw-bold shadow-sm"
           style="background:var(--warning);color:#fff;border:none;padding:6px 16px;">
            Xem toàn bộ (<?= count($unassignedTickets) ?>) <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr style="background:var(--n40);">
                    <th style="width:130px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;padding:10px 16px;">Mã Ticket</th>
                    <th style="font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Tiêu đề yêu cầu</th>
                    <th style="width:140px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Người gửi</th>
                    <th style="width:120px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Ưu tiên</th>
                    <th style="width:150px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Nhóm phụ trách</th>
                    <th style="width:180px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;">Thời hạn SLA</th>
                    <th style="width:130px;font-size:11px;text-transform:uppercase;color:var(--n400);font-weight:600;" class="text-end pe-3">Phân công</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($unassignedTickets)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-check-circle-fill d-block mb-3" style="font-size:3rem;color:var(--success);opacity:.6;"></i>
                            <div class="fw-semibold mb-1" style="color:var(--success);">Tất cả ticket đều đã được phân công!</div>
                            <div class="small" style="color:var(--n300);">Không có ticket nào đang chờ gán nhân sự phụ trách.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_slice($unassignedTickets, 0, 5) as $ut): ?>
                        <tr>
                            <td style="padding-left:16px;">
                                <a href="<?= $baseUrl ?>/tickets/<?= $ut['id'] ?>" class="ticket-code-pill">
                                    <i class="bi bi-hash"></i><?= htmlspecialchars($ut['ticket_code']) ?>
                                </a>
                            </td>
                            <td class="fw-bold" style="color:var(--n800);"><?= htmlspecialchars($ut['title']) ?></td>
                            <td style="color:var(--n600);"><?= htmlspecialchars($ut['requester_name']) ?></td>
                            <td>
                                <span class="badge-priority priority-<?= strtolower($ut['priority_code'] ?? 'normal') ?>">
                                    <?= htmlspecialchars($ut['priority_name']) ?>
                                </span>
                            </td>
                            <td style="color:var(--n500);font-size:.875rem;"><?= htmlspecialchars($ut['group_name'] ?? 'Chưa chỉ định') ?></td>
                            <td>
                                <div class="sla-live-timer small" data-due="<?= htmlspecialchars($ut['sla_due_at'] ?? '') ?>" data-status="<?= htmlspecialchars($ut['status'] ?? 'NEW') ?>">
                                    <?= !empty($ut['sla_due_at']) ? date('d/m/Y H:i', strtotime($ut['sla_due_at'])) : 'N/A' ?>
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <a href="<?= $baseUrl ?>/tickets/<?= $ut['id'] ?>" class="btn btn-sm fw-bold"
                                   style="background:var(--warning);color:#fff;border:none;padding:5px 14px;">
                                    <i class="bi bi-person-plus me-1"></i>Gán việc
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
