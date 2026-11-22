<?php
/**
 * @var array $changes
 * @var array $filters
 */
$baseUrl = \App\Core\Response::baseUrl();
?>
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-arrow-repeat text-info me-2"></i>Quản Lý Thay Đổi & Phát Hành (Change & Release)
            </h4>
            <p class="text-muted small mb-0">Kiểm soát quy trình thay đổi hệ thống IT (RFC), đánh giá rủi ro, phê duyệt hội đồng CAB và theo dõi tiến độ qua các môi trường DEV &rarr; TEST &rarr; MIG/UAT &rarr; PROD.</p>
        </div>
        <div>
            <a href="<?= $baseUrl ?>/changes/create" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tạo Yêu Cầu Thay Đổi (RFC)
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= $baseUrl ?>/changes" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Mã RFC, tiêu đề thay đổi..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="environment_stage" class="form-select">
                        <option value="">-- Tất cả môi trường --</option>
                        <option value="DEV" <?= ($filters['environment_stage'] ?? '') === 'DEV' ? 'selected' : '' ?>>Phát triển (DEV)</option>
                        <option value="TEST" <?= ($filters['environment_stage'] ?? '') === 'TEST' ? 'selected' : '' ?>>Kiểm thử (TEST)</option>
                        <option value="MIG_UAT" <?= ($filters['environment_stage'] ?? '') === 'MIG_UAT' ? 'selected' : '' ?>>Chuyển dịch & UAT (MIG/UAT)</option>
                        <option value="RELEASE_PROD" <?= ($filters['environment_stage'] ?? '') === 'RELEASE_PROD' ? 'selected' : '' ?>>Phát hành (PROD)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="risk_level" class="form-select">
                        <option value="">-- Mức độ rủi ro --</option>
                        <option value="LOW" <?= ($filters['risk_level'] ?? '') === 'LOW' ? 'selected' : '' ?>>Thấp (LOW)</option>
                        <option value="MEDIUM" <?= ($filters['risk_level'] ?? '') === 'MEDIUM' ? 'selected' : '' ?>>Trung bình (MEDIUM)</option>
                        <option value="HIGH" <?= ($filters['risk_level'] ?? '') === 'HIGH' ? 'selected' : '' ?>>Cao (HIGH)</option>
                        <option value="CRITICAL" <?= ($filters['risk_level'] ?? '') === 'CRITICAL' ? 'selected' : '' ?>>Nghiêm trọng (CRITICAL)</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Lọc
                    </button>
                    <a href="<?= $baseUrl ?>/changes" class="btn btn-light btn-sm border">
                        Đặt lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Changes Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 140px;">Mã RFC</th>
                            <th>Tiêu Đề Thay Đổi</th>
                            <th>Môi Trường</th>
                            <th>Phân Loại</th>
                            <th>Rủi Ro</th>
                            <th>Trạng Thái</th>
                            <th>Người Đề Xuất</th>
                            <th class="text-end pe-4">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($changes)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success"></i>
                                    Không có yêu cầu thay đổi (RFC) nào theo tiêu chí lọc.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($changes as $c): ?>
                                <?php
                                $stageBadge = match($c['environment_stage']) {
                                    'DEV'          => '<span class="badge bg-secondary-subtle text-secondary border">1. DEV</span>',
                                    'TEST'         => '<span class="badge bg-primary-subtle text-primary border">2. TEST</span>',
                                    'MIG_UAT'      => '<span class="badge bg-warning-subtle text-warning-emphasis border">3. MIG/UAT</span>',
                                    'RELEASE_PROD' => '<span class="badge bg-success text-white">4. RELEASE_PROD</span>',
                                    default        => '<span class="badge bg-secondary">' . $c['environment_stage'] . '</span>'
                                };

                                $riskBadge = match($c['risk_level']) {
                                    'LOW'      => '<span class="badge bg-success-subtle text-success border">Rủi ro Thấp</span>',
                                    'MEDIUM'   => '<span class="badge bg-info-subtle text-info-emphasis border">Rủi ro TB</span>',
                                    'HIGH'     => '<span class="badge bg-warning-subtle text-warning-emphasis border">Rủi ro Cao</span>',
                                    'CRITICAL' => '<span class="badge bg-danger text-white">Rất Nghiêm Trọng</span>',
                                    default    => '<span class="badge bg-secondary">' . $c['risk_level'] . '</span>'
                                };

                                $statusBadge = match($c['status']) {
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
                                    default          => '<span class="badge bg-secondary">' . $c['status'] . '</span>'
                                };
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold">
                                        <a href="<?= $baseUrl ?>/changes/<?= $c['id'] ?>" class="text-decoration-none text-primary">
                                            <?= htmlspecialchars($c['change_code']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/changes/<?= $c['id'] ?>" class="text-dark fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($c['title']) ?>
                                        </a>
                                        <div class="text-muted small text-truncate mt-1" style="max-width: 380px;">
                                            Lý do: <?= htmlspecialchars($c['reason']) ?>
                                        </div>
                                    </td>
                                    <td><?= $stageBadge ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($c['change_type']) ?></span>
                                    </td>
                                    <td><?= $riskBadge ?></td>
                                    <td><?= $statusBadge ?></td>
                                    <td class="small text-dark">
                                        <?= htmlspecialchars($c['requester_name'] ?? 'Hệ thống') ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?= $baseUrl ?>/changes/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-3">
                                            <i class="bi bi-eye me-1"></i> Chi tiết
                                        </a>
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
