<?php
$baseUrl = \App\Core\Response::baseUrl();

$stageSteps = ['DEV', 'TEST', 'MIG_UAT', 'RELEASE_PROD'];
$currentStageIndex = array_search($change['environment_stage'], $stageSteps);
if ($currentStageIndex === false) $currentStageIndex = 0;

$riskBadge = match($change['risk_level']) {
    'LOW'      => '<span class="badge bg-success-subtle text-success border">Rủi ro Thấp</span>',
    'MEDIUM'   => '<span class="badge bg-info-subtle text-info-emphasis border">Rủi ro TB</span>',
    'HIGH'     => '<span class="badge bg-warning-subtle text-warning-emphasis border">Rủi ro Cao</span>',
    'CRITICAL' => '<span class="badge bg-danger text-white">Rất Nghiêm Trọng</span>',
    default    => '<span class="badge bg-secondary">' . $change['risk_level'] . '</span>'
};

$statusBadge = match($change['status']) {
    'DRAFT'          => '<span class="badge bg-secondary">Nháp</span>',
    'SUBMITTED'      => '<span class="badge bg-info text-dark">Đã gửi</span>',
    'CAB_APPROVAL'   => '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Hội đồng CAB duyệt</span>',
    'SCHEDULED'      => '<span class="badge bg-primary">Đã lên lịch</span>',
    'IMPLEMENTING'   => '<span class="badge bg-warning text-dark">Đang triển khai</span>',
    'TESTING_UAT'    => '<span class="badge bg-info text-dark">Đang UAT</span>',
    'RELEASE_PROD'   => '<span class="badge bg-primary">Deploy PROD</span>',
    'SUCCESSFUL'     => '<span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Thành công</span>',
    'FAILED'         => '<span class="badge bg-danger">Thất bại</span>',
    'ROLLED_BACK'    => '<span class="badge bg-dark"><i class="bi bi-arrow-counterclockwise me-1"></i>Đã Rollback</span>',
    'CANCELLED'      => '<span class="badge bg-light text-secondary border">Hủy bỏ</span>',
    default          => '<span class="badge bg-secondary">' . $change['status'] . '</span>'
};
?>
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?= $baseUrl ?>/changes" class="text-secondary text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i>Change & Release
                </a>
                <span class="text-muted">/</span>
                <span class="badge bg-primary-subtle text-primary fw-bold border border-primary-subtle"><?= htmlspecialchars($change['change_code']) ?></span>
                <?= $statusBadge ?>
                <?= $riskBadge ?>
            </div>
            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($change['title']) ?></h4>
        </div>
        <div class="d-flex gap-2">
            <?php if (!in_array($change['status'], ['SUCCESSFUL', 'FAILED', 'ROLLED_BACK', 'CANCELLED'])): ?>
                <button type="button" class="btn btn-success btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#completeModal">
                    <i class="bi bi-check-circle me-1"></i> Đóng Thay Đổi
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- ITIL Environment Stage Pipeline Progress -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="fw-bold text-dark">
                    <i class="bi bi-diagram-3-fill text-primary me-2"></i>Quy Trình Triển Khai Môi Trường (Environment Pipeline)
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Môi trường hiện tại:</span>
                    <span class="badge bg-primary fs-6"><?= htmlspecialchars($change['environment_stage']) ?></span>
                </div>
            </div>

            <!-- Pipeline Steps Indicator -->
            <div class="row g-2 text-center my-3">
                <?php
                $stagesInfo = [
                    ['key' => 'DEV', 'name' => '1. Phát Triển (DEV)', 'desc' => 'Code & Local test'],
                    ['key' => 'TEST', 'name' => '2. Kiểm Thử (TEST)', 'desc' => 'QA / Staging test'],
                    ['key' => 'MIG_UAT', 'name' => '3. Di Chuyển & UAT', 'desc' => 'Người dùng nghiệm thu'],
                    ['key' => 'RELEASE_PROD', 'name' => '4. Trực Tiếp (PROD)', 'desc' => 'Vận hành Live'],
                ];
                ?>
                <?php foreach ($stagesInfo as $idx => $stg): ?>
                    <?php
                    $isPassed = $idx < $currentStageIndex;
                    $isCurrent = $idx === $currentStageIndex;
                    $cardClass = $isCurrent ? 'border-primary bg-primary-subtle shadow-sm' : ($isPassed ? 'border-success bg-light' : 'border-light bg-light opacity-50');
                    $textClass = $isCurrent ? 'text-primary fw-bold' : ($isPassed ? 'text-success fw-semibold' : 'text-muted');
                    $iconClass = $isCurrent ? 'bi-arrow-repeat text-primary' : ($isPassed ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted');
                    ?>
                    <div class="col-md-3">
                        <div class="card p-3 h-100 <?= $cardClass ?> border-2 rounded-3 text-start position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge <?= $isCurrent ? 'bg-primary' : ($isPassed ? 'bg-success' : 'bg-secondary') ?> rounded-pill">Bước <?= $idx + 1 ?></span>
                                <i class="bi <?= $iconClass ?> fs-5"></i>
                            </div>
                            <div class="<?= $textClass ?> mt-2"><?= $stg['name'] ?></div>
                            <div class="small text-muted" style="font-size: 0.75rem;"><?= $stg['desc'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Advance Stage Form -->
            <?php if (!in_array($change['status'], ['SUCCESSFUL', 'FAILED', 'ROLLED_BACK', 'CANCELLED'])): ?>
                <div class="bg-light p-3 rounded-2 border mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i> Chuyển môi trường sau khi hoàn tất kiểm tra nghiệm thu.
                    </div>
                    <form action="<?= $baseUrl ?>/changes/<?= $change['id'] ?>/stage" method="POST" class="d-flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                        <select name="target_stage" class="form-select form-select-sm" style="width: 220px;">
                            <option value="DEV" <?= $change['environment_stage'] === 'DEV' ? 'selected' : '' ?>>1. DEV (Development)</option>
                            <option value="TEST" <?= $change['environment_stage'] === 'TEST' ? 'selected' : '' ?>>2. TEST (Staging)</option>
                            <option value="MIG_UAT" <?= $change['environment_stage'] === 'MIG_UAT' ? 'selected' : '' ?>>3. MIG_UAT (User Acceptance)</option>
                            <option value="RELEASE_PROD" <?= $change['environment_stage'] === 'RELEASE_PROD' ? 'selected' : '' ?>>4. RELEASE_PROD (Production)</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary px-3 text-nowrap">
                            <i class="bi bi-arrow-right-circle me-1"></i> Chuyển Môi Trường
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Reason & Technical Description -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-card-checklist text-primary me-2"></i>Lý Do & Phạm Vi Kỹ Thuật
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Lý do thay đổi</span>
                        <div class="p-3 bg-light rounded-2 border small" style="white-space: pre-line;"><?= htmlspecialchars($change['reason']) ?></div>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Phạm vi kỹ thuật chi tiết</span>
                        <div class="p-3 bg-light rounded-2 border small" style="white-space: pre-line;"><?= htmlspecialchars($change['description']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Implementation & Rollback Plans -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-journal-code text-success me-2"></i>Kế Hoạch Triển Khai & Rollback An Toàn
                    </h6>
                    <span class="badge bg-light text-muted border">ITIL Standard</span>
                </div>
                <div class="card-body">
                    <!-- Implementation Plan -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark small text-uppercase">
                            <i class="bi bi-play-circle text-primary me-1"></i> 1. Kế hoạch triển khai (Implementation Plan)
                        </h6>
                        <div class="p-3 bg-light rounded-2 border small" style="white-space: pre-line;">
                            <?= !empty($change['implementation_plan']) ? htmlspecialchars($change['implementation_plan']) : '<span class="text-muted fst-italic">Chưa có kế hoạch chi tiết.</span>' ?>
                        </div>
                    </div>

                    <!-- Rollback Plan -->
                    <div class="mb-3">
                        <h6 class="fw-bold text-danger small text-uppercase">
                            <i class="bi bi-arrow-counterclockwise text-danger me-1"></i> 2. Kế hoạch hoàn nguyên (Rollback Plan - Bắt buộc)
                        </h6>
                        <div class="p-3 bg-danger-subtle text-danger-emphasis rounded-2 border border-danger-subtle small" style="white-space: pre-line;">
                            <?= !empty($change['rollback_plan']) ? htmlspecialchars($change['rollback_plan']) : '<span class="fst-italic">Cảnh báo: Chưa cung cấp kịch bản Rollback!</span>' ?>
                        </div>
                    </div>

                    <!-- Test Plan -->
                    <div>
                        <h6 class="fw-bold text-dark small text-uppercase">
                            <i class="bi bi-check2-square text-info me-1"></i> 3. Kế hoạch kiểm thử & Nghiệm thu (Test Plan)
                        </h6>
                        <div class="p-3 bg-light rounded-2 border small" style="white-space: pre-line;">
                            <?= !empty($change['test_plan']) ? htmlspecialchars($change['test_plan']) : '<span class="text-muted fst-italic">Chưa có kịch bản test.</span>' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Linked Tickets -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-link-45deg text-primary me-2"></i>Các Ticket Liên Quan (Linked Tickets: <?= count($linkedTickets) ?>)
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#linkTicketForm">
                        <i class="bi bi-plus-lg me-1"></i> Liên Kết Ticket
                    </button>
                </div>

                <div class="collapse border-bottom p-3 bg-light" id="linkTicketForm">
                    <form action="<?= $baseUrl ?>/changes/<?= $change['id'] ?>/link-ticket" method="POST" class="d-flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                        <input type="text" name="ticket_identifier" class="form-control form-control-sm" placeholder="Nhập mã Ticket (ví dụ IT-000001)..." required>
                        <button type="submit" class="btn btn-sm btn-primary px-3 text-nowrap">
                            <i class="bi bi-link me-1"></i> Liên kết
                        </button>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">Mã Ticket</th>
                                    <th>Tiêu Đề</th>
                                    <th>Người Gửi</th>
                                    <th>Trạng Thái</th>
                                    <th>Thời Gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($linkedTickets)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            Chưa có Ticket nào liên kết với Change Request này.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($linkedTickets as $tk): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <a href="<?= $baseUrl ?>/tickets/<?= $tk['ticket_id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($tk['ticket_code']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?= $baseUrl ?>/tickets/<?= $tk['ticket_id'] ?>" class="text-dark fw-semibold text-decoration-none">
                                                    <?= htmlspecialchars($tk['title']) ?>
                                                </a>
                                            </td>
                                            <td class="small text-muted"><?= htmlspecialchars($tk['requester_name']) ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?= $tk['status_color'] ?? '#6c757d' ?>;">
                                                    <?= htmlspecialchars($tk['status_name'] ?? $tk['status']) ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($tk['linked_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Details & CAB Approval -->
        <div class="col-lg-4">
            <!-- CAB Approval Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-people-fill text-warning me-2"></i>Hội Đồng Phê Duyệt (CAB Approval)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($change['cab_approved_at'])): ?>
                        <div class="alert alert-success d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill fs-4 me-2"></i>
                            <div>
                                <strong>Đã được CAB phê duyệt</strong><br>
                                <span class="small">Bởi: <?= htmlspecialchars($change['cab_approver_name'] ?? 'Hội đồng CAB') ?></span><br>
                                <span class="small text-muted"><?= date('d/m/Y H:i', strtotime($change['cab_approved_at'])) ?></span>
                            </div>
                        </div>
                        <?php if (!empty($change['cab_note'])): ?>
                            <div class="small text-muted"><strong>Ghi chú CAB:</strong> <?= htmlspecialchars($change['cab_note']) ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning mb-3 small">
                            <i class="bi bi-hourglass-split me-1"></i> Yêu cầu này đang chờ ý kiến phê duyệt của Hội đồng CAB trước khi tiến hành lên lịch triển khai.
                        </div>

                        <?php if (\App\Core\Auth::isManager() || \App\Core\Auth::isAdmin()): ?>
                            <form action="<?= $baseUrl ?>/changes/<?= $change['id'] ?>/cab-approve" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Quyết định CAB</label>
                                    <select name="decision" class="form-select form-select-sm">
                                        <option value="APPROVED">Phê Duyệt Triển Khai (Approve)</option>
                                        <option value="REJECTED">Bác Bỏ / Từ Chối (Reject)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Ghi chú hội đồng</label>
                                    <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Ghi chú đánh giá rủi ro hoặc điều kiện chấp thuận..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                                    <i class="bi bi-check-lg me-1"></i> Ghi Nhận Quyết Định CAB
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="text-muted small fst-italic">Bạn cần quyền IT Manager hoặc Administrator để ghi nhận quyết định CAB.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Change Attributes -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>Thông Tin RFC
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Mã thay đổi:</span>
                            <span class="fw-bold text-primary"><?= htmlspecialchars($change['change_code']) ?></span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Phân loại:</span>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($change['change_type']) ?></span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Mức rủi ro:</span>
                            <?= $riskBadge ?>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Người đề xuất:</span>
                            <span class="small fw-semibold text-dark"><?= htmlspecialchars($change['requester_name'] ?? 'Hệ thống') ?></span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Người triển khai:</span>
                            <span class="small text-dark"><?= htmlspecialchars($change['assignee_name'] ?? 'Chưa chỉ định') ?></span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Bắt đầu dự kiến:</span>
                            <span class="small text-muted"><?= !empty($change['scheduled_start']) ? date('d/m/Y H:i', strtotime($change['scheduled_start'])) : 'Chưa đặt' ?></span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Kết thúc dự kiến:</span>
                            <span class="small text-muted"><?= !empty($change['scheduled_end']) ? date('d/m/Y H:i', strtotime($change['scheduled_end'])) : 'Chưa đặt' ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Complete Change -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/changes/<?= $change['id'] ?>/complete" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-flag-fill me-2"></i>Đóng Yêu Cầu Thay Đổi (RFC Complete)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Kết quả triển khai <span class="text-danger">*</span></label>
                        <select name="outcome" class="form-select" required>
                            <option value="SUCCESSFUL">Thành công (SUCCESSFUL - Đã release an toàn)</option>
                            <option value="ROLLED_BACK">Thất bại & Đã Rollback (ROLLED_BACK - Đã quay về trạng thái cũ)</option>
                            <option value="FAILED">Thất bại (FAILED - Cần can thiệp khẩn)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú kết quả & Đánh giá PIR (Post Implementation Review)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Ghi nhận thời gian hoàn tất thực tế, tình trạng dịch vụ sau phát hành..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                        <i class="bi bi-check2 me-1"></i> Xác Nhận Đóng Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
