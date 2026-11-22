<?php
/**
 * @var array $project
 * @var array $bugs
 * @var array $members
 * @var array $metrics
 * @var array $filters
 * @var array $pagination
 */

use App\Core\Response;
$baseUrl = Response::baseUrl();
?>

<div class="container-fluid px-0">
    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects" class="text-decoration-none text-secondary">Projects</a></li>
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="text-decoration-none text-secondary"><?= htmlspecialchars($project['name']) ?></a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold">Bug Tracking</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-bug-fill text-danger"></i> Quản Lý Lỗi & Sự Cố (Bug Tracking)
            </h3>
            <p class="text-muted small mb-0">Theo dõi danh sách các defect, mức độ nghiêm trọng và liên kết với Backlog cha (Tối đa 20/trang)</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-danger fw-semibold px-3" onclick="openNewBugModal()">
                <i class="bi bi-plus-lg me-1"></i> Báo Lỗi Mới (New Bug)
            </button>
        </div>
    </div>

    <!-- METRICS TILES -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="p-3 bg-light rounded border text-center">
                <div class="text-muted small fw-bold">TỔNG SỐ BUG</div>
                <div class="fs-4 fw-bold text-dark"><?= $metrics['total'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-center">
                <div class="text-danger small fw-bold">🔴 BLOCKER</div>
                <div class="fs-4 fw-bold text-danger"><?= $metrics['blocker'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-center">
                <div class="text-danger small fw-bold">🟠 CRITICAL</div>
                <div class="fs-4 fw-bold text-danger"><?= $metrics['critical'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded text-center">
                <div class="text-warning small fw-bold">🟡 MAJOR</div>
                <div class="fs-4 fw-bold text-dark"><?= $metrics['major'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-info bg-opacity-10 border border-info border-opacity-25 rounded text-center">
                <div class="text-info small fw-bold">🔵 MINOR / TRIVIAL</div>
                <div class="fs-4 fw-bold text-dark"><?= $metrics['minor'] ?></div>
            </div>
        </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="card border rounded-3 shadow-sm mb-3 p-3 bg-light">
        <form action="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/bugs" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control form-control-sm ps-4" placeholder="Tìm theo tiêu đề bug, mã key..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 7px; font-size: 12px;"></i>
                </div>
            </div>
            <div class="col-md-4">
                <select name="severity" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả mức độ nghiêm trọng --</option>
                    <option value="BLOCKER" <?= ($filters['severity'] === 'BLOCKER') ? 'selected' : '' ?>>🔴 Blocker</option>
                    <option value="CRITICAL" <?= ($filters['severity'] === 'CRITICAL') ? 'selected' : '' ?>>🟠 Critical</option>
                    <option value="MAJOR" <?= ($filters['severity'] === 'MAJOR') ? 'selected' : '' ?>>🟡 Major</option>
                    <option value="MINOR" <?= ($filters['severity'] === 'MINOR') ? 'selected' : '' ?>>🔵 Minor</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-sm btn-danger w-100 fw-semibold">Lọc lỗi</button>
            </div>
        </form>
    </div>

    <!-- BUGS TABLE -->
    <div class="card border rounded-3 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light text-secondary small" style="text-transform: uppercase; font-size: 11px;">
                    <tr>
                        <th style="width: 100px;">Key</th>
                        <th style="width: 110px;">Nghiêm Trọng</th>
                        <th>Nội Dung Sự Cố (Summary & Context)</th>
                        <th style="width: 160px;">Mục Backlog Cha</th>
                        <th style="width: 120px;">Trạng Thái</th>
                        <th style="width: 140px;">Người Phụ Trách</th>
                        <th style="width: 80px;" class="text-end">Chi Tiết</th>
                    </tr>
                </thead>
                <tbody id="bugTableBody">
                    <?php if (empty($bugs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check text-success fs-2 opacity-50"></i>
                                <div class="mt-2">Tuyệt vời! Không có bug nào được tìm thấy.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bugs as $b): ?>
                            <?php 
                            $sevBadge = [
                                'BLOCKER'  => '<span class="badge bg-danger text-white">BLOCKER</span>',
                                'CRITICAL' => '<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50">CRITICAL</span>',
                                'MAJOR'    => '<span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50">MAJOR</span>',
                                'MINOR'    => '<span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50">MINOR</span>'
                            ][$b['bug_severity']] ?? $b['bug_severity'];
                            ?>
                            <tr class="bug-row">
                                <td>
                                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $b['id'] ?>" class="fw-bold font-monospace text-danger text-decoration-none">
                                        <?= htmlspecialchars($b['issue_key']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?= $sevBadge ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $b['id'] ?>" class="text-decoration-none text-dark hover-primary">
                                            <?= htmlspecialchars($b['summary']) ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($b['environment'])): ?>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-display me-1 text-secondary"></i> Môi trường: <?= htmlspecialchars($b['environment']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($b['parent_key'])): ?>
                                        <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $b['parent_id'] ?>" class="badge bg-light text-primary border text-decoration-none text-truncate d-inline-block" style="max-width: 150px;" title="<?= htmlspecialchars($b['parent_summary'] ?? '') ?>">
                                            <i class="bi bi-diagram-3-fill me-1"></i> <?= htmlspecialchars($b['parent_key']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?= $b['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['assignee_id']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="jira-avatar-circle" style="width: 22px; height: 22px; font-size: 9px; background: #DE350B;">
                                                <?= strtoupper(substr($b['assignee_name'] ?: 'U', 0, 1)) ?>
                                            </div>
                                            <span class="small text-truncate" style="max-width: 90px;"><?= htmlspecialchars($b['assignee_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-- Unassigned --</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Xem chi tiết">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION BAR (Tối đa 20 / trang) -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-footer bg-white border-top py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small text-muted">
                    Hiển thị <?= min(($pagination['page'] - 1) * $pagination['per_page'] + 1, $pagination['total']) ?> - 
                    <?= min($pagination['page'] * $pagination['per_page'], $pagination['total']) ?> 
                    trên tổng số <strong><?= $pagination['total'] ?></strong> lỗi (tối đa 20 trên 1 trang)
                </div>
                <?php if ($pagination['total_pages'] > 1): ?>
                    <ul class="pagination pagination-sm tf-pagination mb-0">
                        <li class="page-item <?= ($pagination['page'] <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])) ?>">« Trước</a>
                        </li>
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                            <li class="page-item <?= ($p === $pagination['page']) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagination['page'] >= $pagination['total_pages']) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])) ?>">Sau »</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function openNewBugModal() {
    const modalEl = document.getElementById('globalQuickCreateModal');
    if (modalEl) {
        const typeSelect = document.getElementById('qc_issue_type');
        if (typeSelect) {
            typeSelect.value = 'BUG';
            toggleQcTypeFields('BUG');
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}
</script>
