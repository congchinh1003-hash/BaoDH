<?php
use App\Core\Response;

/**
 * @var array $tickets
 */
$baseUrl = Response::baseUrl();
?>

<!-- BREADCRUMB -->
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb breadcrumb-sm mb-0" style="font-size:0.8rem;">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none text-muted"><i class="bi bi-house me-1"></i>Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/manager/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Hàng đợi chờ phân công</li>
    </ol>
</nav>

<!-- PAGE HERO -->
<div class="page-hero mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3"
     style="background:#fff; border-left:4px solid #FF991F; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); padding:20px 24px;">
    <div class="d-flex align-items-center gap-3">
        <div style="background:#FFF4E0; border-radius:10px; width:48px; height:48px; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-inbox-fill fs-4" style="color:#FF991F;"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-1" style="color:#172B4D;">Hàng Đợi Chờ Phân Công</h4>
            <p class="text-muted small mb-0" style="color:#5E6C84;">Các yêu cầu mới gửi đến cần Leader/Manager xem xét chỉ định người phụ trách</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <?php if (!empty($tickets)): ?>
            <div class="text-center px-4 py-2 rounded-3" style="background:#FFF4E0; border:1.5px solid #FF991F;">
                <div class="fw-bold fs-4" style="color:#FF991F; line-height:1;"><?= count($tickets) ?></div>
                <div class="small text-muted" style="font-size:0.72rem;">TICKET CHỜ</div>
            </div>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>/manager/dashboard" class="btn btn-outline-secondary fw-semibold px-3">
            <i class="bi bi-arrow-left me-1"></i>Về Dashboard
        </a>
    </div>
</div>

<!-- MAIN TABLE CARD -->
<div class="card border-0" style="border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); overflow:hidden;">
    <!-- Card Header -->
    <div class="card-header border-0 py-3 px-4 d-flex justify-content-between align-items-center"
         style="background: linear-gradient(135deg, #FFF4E0 0%, #FAFBFC 100%); border-bottom:1.5px solid #FFE0A0 !important;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-octagon-fill" style="color:#FF991F;"></i>
            <span class="fw-bold text-dark">Ticket đang chờ phân công</span>
            <span class="badge rounded-pill fw-bold" style="background:#FF991F; color:#fff;"><?= count($tickets) ?></span>
        </div>
        <span class="text-muted small"><i class="bi bi-clock me-1"></i>Cập nhật theo thời gian thực</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#F4F5F7;">
                <tr>
                    <th style="width:130px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Mã Ticket</th>
                    <th style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Tiêu đề &amp; Nội dung</th>
                    <th style="width:160px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Người gửi</th>
                    <th style="width:120px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Ưu tiên</th>
                    <th style="width:160px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Nhóm phụ trách</th>
                    <th style="width:175px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;">Thời hạn SLA</th>
                    <th style="width:145px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#5E6C84; padding:12px 16px; font-weight:700;" class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div style="padding: 40px 0;">
                                <div style="width:72px; height:72px; background:#EBFBF0; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                                    <i class="bi bi-check-circle-fill fs-2" style="color:#00875A;"></i>
                                </div>
                                <div class="fw-bold mb-1" style="color:#172B4D; font-size:1rem;">Tuyệt vời! Hàng đợi trống</div>
                                <div class="text-muted small">Không có ticket nào đang chờ phân công. Tất cả đã được xử lý.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <?php
                        $priorityCode = strtolower($t['priority_code'] ?? 'normal');
                        $isOverdue = (!empty($t['sla_due_at']) && strtotime($t['sla_due_at']) < time());
                        ?>
                        <tr style="border-bottom:1px solid #F4F5F7;">
                            <td style="padding:14px 16px;">
                                <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>" class="ticket-code-pill">
                                    <i class="bi bi-hash"></i><?= htmlspecialchars($t['ticket_code']) ?>
                                </a>
                            </td>
                            <td style="padding:14px 16px;">
                                <div class="fw-bold" style="color:#172B4D;"><?= htmlspecialchars($t['title']) ?></div>
                                <div class="text-muted small text-truncate mt-1" style="max-width:380px; color:#5E6C84;">
                                    <?= htmlspecialchars($t['description']) ?>
                                </div>
                            </td>
                            <td style="padding:14px 16px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-circle bg-secondary text-white"
                                         style="width:28px;height:28px;font-size:0.75rem;line-height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <?= strtoupper(mb_substr($t['requester_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small" style="color:#172B4D;"><?= htmlspecialchars($t['requester_name']) ?></div>
                                        <?php if (!empty($t['requester_department'])): ?>
                                            <div class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars($t['requester_department']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 16px;">
                                <span class="badge-priority priority-<?= $priorityCode ?>">
                                    <?= htmlspecialchars($t['priority_name']) ?>
                                </span>
                            </td>
                            <td style="padding:14px 16px;">
                                <span class="badge bg-light text-dark border" style="border-radius:6px;">
                                    <i class="bi bi-people me-1 text-primary"></i><?= htmlspecialchars($t['group_name'] ?? 'Chưa chỉ định') ?>
                                </span>
                            </td>
                            <td style="padding:14px 16px;">
                                <div class="sla-live-timer small fw-semibold <?= $isOverdue ? 'text-danger' : 'text-dark' ?>" data-due="<?= htmlspecialchars($t['sla_due_at'] ?? '') ?>" data-status="<?= htmlspecialchars($t['status'] ?? 'NEW') ?>">
                                    <?= !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'N/A' ?>
                                </div>
                                <?php if (($t['calculated_sla_status'] ?? '') === 'OVERDUE' || $isOverdue): ?>
                                    <span class="badge text-white mt-1" style="background:#DE350B; border-radius:999px; font-size:0.68rem;">
                                        <i class="bi bi-alarm me-1"></i><?= \App\Services\SlaService::formatOverdueTime($t['sla_due_at']) ?: 'QUÁ HẠN' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end" style="padding:14px 16px;">
                                <div class="d-flex justify-content-end gap-2">
                                    <form action="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>/take" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn muốn tự tiếp nhận xử lý yêu cầu này?');">
                                        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-3" title="Tôi sẽ xử lý ticket này"
                                                style="border-radius:6px;">
                                            <i class="bi bi-hand-index-thumb me-1"></i>Nhận
                                        </button>
                                    </form>
                                    <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>"
                                       class="btn btn-sm fw-bold px-2 text-dark"
                                       style="background:#FFF4E0; border:1.5px solid #FF991F; border-radius:6px;"
                                       title="Xem &amp; Phân công cho người khác">
                                        <i class="bi bi-person-gear"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
