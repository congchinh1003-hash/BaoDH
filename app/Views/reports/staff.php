<?php
use App\Core\Response;

$baseUrl = Response::baseUrl();
?>

<div class="page-hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3" style="width: 48px; height: 48px;">
                <i class="bi bi-people-fill fs-4"></i>
            </div>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/reports" class="text-decoration-none">Báo cáo & Phân tích</a></li>
                        <li class="breadcrumb-item active fw-semibold" aria-current="page">Hiệu suất Kỹ thuật viên</li>
                    </ol>
                </nav>
                <h4 class="fw-bold text-dark mb-0">Báo Cáo Hiệu Suất & Đánh Giá Kỹ Thuật Viên</h4>
                <p class="text-muted small mb-0">Đo lường năng suất, số lượng hoàn thành, tỉ lệ tuân thủ SLA và điểm hài lòng dịch vụ (CSAT Score).</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= $baseUrl ?>/reports/export?type=staff" class="btn btn-outline-success btn-sm fw-semibold">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Xuất Excel
            </a>
            <a href="<?= $baseUrl ?>/reports" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Về tổng quan báo cáo
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm bg-white">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark"><i class="bi bi-award-fill text-warning me-2"></i>Bảng Đo Lường KPI & Chỉ Số Hài Lòng (Staff Scorecard)</span>
        <span class="badge bg-light text-muted border">Toàn bộ Kỹ thuật viên & Quản lý IT</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small text-uppercase">
                        <th class="ps-4 py-3">Kỹ thuật viên</th>
                        <th class="py-3 text-center">Tổng việc</th>
                        <th class="py-3 text-center" title="Tổng số ticket đang mở thuộc về nhân viên">Active</th>
                        <th class="py-3 text-center text-primary" title="Ticket cần nhân viên tiếp tục xử lý (loại trừ Waiting User/Vendor)">Need Action</th>
                        <th class="py-3 text-center">Quá SLA</th>
                        <th class="py-3 text-center">Đã giải quyết</th>
                        <th class="py-3 text-center">Đã đóng</th>
                        <th class="py-3 text-center">Mở lại (Reopen)</th>
                        <th class="py-3 text-center">SLA Tuân thủ</th>
                        <th class="pe-4 py-3 text-end">Điểm CSAT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staffData)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                Chưa có dữ liệu nhân viên kỹ thuật.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($staffData as $s): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-circle bg-primary text-white" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            <?= strtoupper(substr($s['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($s['full_name']) ?></div>
                                            <div class="text-muted small" style="font-size: 0.72rem;"><?= htmlspecialchars($s['department'] ?: 'IT Support') ?> • <?= htmlspecialchars($s['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center font-monospace fw-bold fs-6"><?= (int)$s['total_assigned'] ?></td>
                                <td class="text-center font-monospace fw-bold text-secondary"><?= (int)$s['active_count'] ?></td>
                                <td class="text-center font-monospace text-primary fw-bold fs-6 bg-primary-subtle rounded-1"><?= (int)($s['need_action_count'] ?? 0) ?></td>
                                <td class="text-center font-monospace <?= (int)$s['overdue_count'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= (int)$s['overdue_count'] ?>
                                </td>
                                <td class="text-center font-monospace text-info fw-bold"><?= (int)$s['resolved_count'] ?></td>
                                <td class="text-center font-monospace text-success fw-bold"><?= (int)$s['closed_count'] ?></td>
                                <td class="text-center font-monospace <?= (int)$s['reopen_total'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= (int)$s['reopen_total'] ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill px-2 py-1 small <?= $s['sla_rate'] >= 80 ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger' ?>">
                                        <?= $s['sla_rate'] ?>%
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php if (!empty($s['avg_rating'])): ?>
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <span class="fw-bold text-dark fs-6"><?= $s['avg_rating'] ?></span>
                                            <span class="text-warning"><i class="bi bi-star-fill"></i></span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= $s['rating_count'] ?> lượt chấm</div>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Chưa có đánh giá</span>
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

