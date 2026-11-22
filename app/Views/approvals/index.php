<?php
/**
 * @var array $approvals
 * @var string $currentStatus
 * @var bool $isManagerOrAdmin
 */
$baseUrl = \App\Core\Response::baseUrl();
?>
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-shield-check text-success me-2"></i>Trung Tâm Phê Duyệt (Approval Center)
            </h4>
            <p class="text-muted small mb-0">Quản lý và xét duyệt các Yêu cầu Dịch vụ (Service Requests) và Yêu cầu Thay đổi Hệ thống (RFC Change Requests).</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/approvals?status=PENDING" class="btn btn-sm <?= ($currentStatus === 'PENDING' || empty($currentStatus)) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-hourglass-split me-1"></i> Chờ duyệt
            </a>
            <a href="<?= $baseUrl ?>/approvals?status=APPROVED" class="btn btn-sm <?= $currentStatus === 'APPROVED' ? 'btn-success' : 'btn-outline-secondary' ?>">
                <i class="bi bi-check-circle me-1"></i> Đã phê duyệt
            </a>
            <a href="<?= $baseUrl ?>/approvals?status=REJECTED" class="btn btn-sm <?= $currentStatus === 'REJECTED' ? 'btn-danger' : 'btn-outline-secondary' ?>">
                <i class="bi bi-x-circle me-1"></i> Đã từ chối
            </a>
            <a href="<?= $baseUrl ?>/approvals?status=" class="btn btn-sm <?= $currentStatus === '' ? 'btn-dark' : 'btn-outline-secondary' ?>">
                Tất cả
            </a>
        </div>
    </div>

    <!-- Approvals List Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 140px;">Mã Đối Tượng</th>
                            <th>Loại Yêu Cầu & Tiêu Đề</th>
                            <th>Người Gửi</th>
                            <th>Bước Phê Duyệt</th>
                            <th>Trạng Thái</th>
                            <th>Thời Gian</th>
                            <th class="text-end pe-4">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($approvals)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    Không có yêu cầu phê duyệt nào trong danh sách.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($approvals as $appr): ?>
                                <?php
                                $isTicket = !empty($appr['ticket_id']);
                                $isChange = !empty($appr['change_id']);
                                $code = $isTicket ? $appr['ticket_code'] : ($isChange ? $appr['change_code'] : '#');
                                $title = $isTicket ? $appr['ticket_title'] : ($isChange ? $appr['change_title'] : '');
                                $link = $isTicket ? "{$baseUrl}/tickets/{$appr['ticket_id']}" : ($isChange ? "{$baseUrl}/changes/{$appr['change_id']}" : '#');
                                
                                $statusBadge = match($appr['status']) {
                                    'PENDING'  => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Chờ duyệt</span>',
                                    'APPROVED' => '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Đã duyệt</span>',
                                    'REJECTED' => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Từ chối</span>',
                                    'RETURNED' => '<span class="badge bg-info text-dark"><i class="bi bi-arrow-return-left me-1"></i>Trả về</span>',
                                    default    => '<span class="badge bg-secondary">' . $appr['status'] . '</span>'
                                };
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        <a href="<?= $link ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($code) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($isTicket): ?>
                                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.7rem;">
                                                    <i class="bi bi-ticket-detailed me-1"></i>SERVICE_REQ
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border" style="font-size: 0.7rem;">
                                                    <i class="bi bi-arrow-repeat me-1"></i>CHANGE_RFC
                                                </span>
                                            <?php endif; ?>
                                            <a href="<?= $link ?>" class="text-dark fw-semibold text-decoration-none text-truncate" style="max-width: 400px;">
                                                <?= htmlspecialchars($title) ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-medium text-dark"><?= htmlspecialchars($appr['requester_name'] ?? 'Hệ thống') ?></div>
                                    </td>
                                    <td>
                                        <span class="small text-secondary"><?= htmlspecialchars($appr['step_name']) ?></span>
                                    </td>
                                    <td><?= $statusBadge ?></td>
                                    <td class="small text-muted">
                                        <?= date('d/m/Y H:i', strtotime($appr['created_at'])) ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($appr['status'] === 'PENDING'): ?>
                                            <button type="button" class="btn btn-sm btn-primary py-1 px-3" data-bs-toggle="modal" data-bs-target="#modalApprove<?= $appr['id'] ?>">
                                                <i class="bi bi-pencil-square me-1"></i> Xét duyệt
                                            </button>

                                            <!-- Approval Decision Modal -->
                                            <div class="modal fade text-start" id="modalApprove<?= $appr['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content border-0 shadow">
                                                        <form action="<?= $baseUrl ?>/approvals/decide" method="POST">
                                                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                                                            <input type="hidden" name="approval_id" value="<?= $appr['id'] ?>">
                                                            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/approvals') ?>">

                                                            <div class="modal-header bg-light">
                                                                <h5 class="modal-title fw-bold text-dark">
                                                                    Phê duyệt: <?= htmlspecialchars($code) ?>
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="mb-2"><strong>Nội dung yêu cầu:</strong> <?= htmlspecialchars($title) ?></p>
                                                                <p class="mb-3 text-muted small"><strong>Người gửi:</strong> <?= htmlspecialchars($appr['requester_name'] ?? '') ?></p>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Quyết định của bạn <span class="text-danger">*</span></label>
                                                                    <div class="d-flex gap-2">
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input" type="radio" name="decision" id="decApprove<?= $appr['id'] ?>" value="APPROVED" checked>
                                                                            <label class="form-check-label text-success fw-bold" for="decApprove<?= $appr['id'] ?>">
                                                                                <i class="bi bi-check-circle me-1"></i> Phê Duyệt (Approve)
                                                                            </label>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input" type="radio" name="decision" id="decReject<?= $appr['id'] ?>" value="REJECTED">
                                                                            <label class="form-check-label text-danger fw-bold" for="decReject<?= $appr['id'] ?>">
                                                                                <i class="bi bi-x-circle me-1"></i> Từ Chối (Reject)
                                                                            </label>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input" type="radio" name="decision" id="decReturn<?= $appr['id'] ?>" value="RETURNED">
                                                                            <label class="form-check-label text-info fw-bold" for="decReturn<?= $appr['id'] ?>">
                                                                                <i class="bi bi-arrow-return-left me-1"></i> Bổ Sung (Return)
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold">Ý kiến / Ghi chú phê duyệt</label>
                                                                    <textarea name="comment" class="form-control" rows="3" placeholder="Nhập lý do phê duyệt, lý do từ chối hoặc nội dung cần bổ sung..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                                                                    <i class="bi bi-check2 me-1"></i> Xác Nhận Quyết Định
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= $link ?>" class="btn btn-sm btn-outline-secondary py-1 px-3">
                                                <i class="bi bi-eye me-1"></i> Xem chi tiết
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
