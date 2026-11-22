<?php
/**
 * @var array $project
 * @var array $issues
 * @var array $members
 * @var array $filters
 * @var array $pagination
 */

use App\Core\Response;
$baseUrl = Response::baseUrl();

$typeIcons = [
    'BACKLOG'  => ['icon' => 'bi-box-seam-fill',          'color' => '#00875A', 'label' => 'Backlog'],
    'STORY'    => ['icon' => 'bi-bookmark-star-fill',     'color' => '#00875A', 'label' => 'Story'],
    'TASK'     => ['icon' => 'bi-check-square-fill',      'color' => '#0052CC', 'label' => 'Task'],
    'BUG'      => ['icon' => 'bi-bug-fill',               'color' => '#DE350B', 'label' => 'Bug'],
    'TESTCASE' => ['icon' => 'bi-clipboard-check-fill',  'color' => '#6554C0', 'label' => 'TestCase'],
    'EPIC'     => ['icon' => 'bi-lightning-charge-fill',  'color' => '#FFAB00', 'label' => 'Epic']
];
?>

<div class="container-fluid px-0">
    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects" class="text-decoration-none text-secondary">Projects</a></li>
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="text-decoration-none text-secondary"><?= htmlspecialchars($project['name']) ?></a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold">Issues</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0">Tất Cả Công Việc (Issues & Tasks)</h3>
            <p class="text-muted small mb-0">Danh sách tổng hợp toàn bộ Backlog, Task, Bug, TestCase và Story có phân trang (Tối đa 20/trang)</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?php if (\App\Core\Auth::can('export.issues')): ?>
                <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/export" class="btn btn-sm btn-outline-success px-3" title="Xuất công việc ra Excel">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Xuất Excel
                </a>
            <?php endif; ?>
            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-outline-secondary px-3">
                <i class="bi bi-kanban me-1"></i> Xem Board
            </a>
            <button class="btn btn-sm btn-primary fw-semibold px-3" style="background:#0C66E4; border-color:#0C66E4;" data-bs-toggle="modal" data-bs-target="#globalQuickCreateModal">
                <i class="bi bi-plus-lg me-1"></i> Tạo Công Việc Mới
            </button>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="card border rounded-3 shadow-sm mb-3 p-3 bg-light">
        <form action="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control form-control-sm ps-4" placeholder="Tìm kiếm theo tiêu đề, mã key..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 7px; font-size: 12px;"></i>
                </div>
            </div>
            <div class="col-md-3">
                <select name="issue_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả loại công việc --</option>
                    <option value="BACKLOG" <?= ($filters['issue_type'] === 'BACKLOG') ? 'selected' : '' ?>>📦 Backlog</option>
                    <option value="TASK" <?= ($filters['issue_type'] === 'TASK') ? 'selected' : '' ?>>📌 Task</option>
                    <option value="BUG" <?= ($filters['issue_type'] === 'BUG') ? 'selected' : '' ?>>🐞 Bug</option>
                    <option value="TESTCASE" <?= ($filters['issue_type'] === 'TESTCASE') ? 'selected' : '' ?>>🧪 TestCase</option>
                    <option value="STORY" <?= ($filters['issue_type'] === 'STORY') ? 'selected' : '' ?>>📖 Story</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="TO_DO" <?= ($filters['status'] === 'TO_DO') ? 'selected' : '' ?>>To Do</option>
                    <option value="IN_PROGRESS" <?= ($filters['status'] === 'IN_PROGRESS') ? 'selected' : '' ?>>In Progress</option>
                    <option value="IN_REVIEW" <?= ($filters['status'] === 'IN_REVIEW') ? 'selected' : '' ?>>In Review</option>
                    <option value="DONE" <?= ($filters['status'] === 'DONE') ? 'selected' : '' ?>>Done</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold" style="background:#0C66E4;">Lọc</button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="card border rounded-3 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light text-secondary small" style="text-transform: uppercase; font-size: 11px;">
                    <tr>
                        <th style="width: 50px;">Type</th>
                        <th style="width: 100px;">Key</th>
                        <th>Tiêu Đề Tóm Tắt (Summary)</th>
                        <th style="width: 170px;">Mục Cha Liên Kết</th>
                        <th style="width: 110px;">Component</th>
                        <th style="width: 110px;">Trạng Thái</th>
                        <th style="width: 90px;">Ưu Tiên</th>
                        <th style="width: 140px;">Người Phụ Trách</th>
                        <th style="width: 60px;" class="text-end">Xem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($issues)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                Không tìm thấy công việc nào phù hợp.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($issues as $iss): ?>
                            <?php $typeMeta = $typeIcons[$iss['issue_type']] ?? $typeIcons['TASK']; ?>
                            <tr>
                                <td class="text-center">
                                    <i class="bi <?= $typeMeta['icon'] ?>" style="color: <?= $typeMeta['color'] ?>; font-size: 15px;" title="<?= $typeMeta['label'] ?>"></i>
                                </td>
                                <td>
                                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['id'] ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                        <?= htmlspecialchars($iss['issue_key']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['id'] ?>" class="text-decoration-none text-dark fw-semibold hover-primary">
                                        <?= htmlspecialchars($iss['summary']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($iss['parent_key'])): ?>
                                        <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['parent_id'] ?>" class="badge bg-light text-primary border text-decoration-none text-truncate d-inline-block" style="max-width: 160px;" title="<?= htmlspecialchars($iss['parent_summary'] ?? '') ?>">
                                            <i class="bi bi-diagram-3-fill me-1"></i> <?= htmlspecialchars($iss['parent_key']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($iss['component'])): ?>
                                        <span class="badge bg-light text-secondary border">
                                            <?= htmlspecialchars($iss['component']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?= $iss['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small fw-semibold">
                                        <?= $iss['priority'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($iss['assignee_id']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="jira-avatar-circle" style="width: 22px; height: 22px; font-size: 9px; background: #0052CC;">
                                                <?= strtoupper(substr($iss['assignee_name'] ?: 'U', 0, 1)) ?>
                                            </div>
                                            <span class="small text-truncate" style="max-width: 90px;"><?= htmlspecialchars($iss['assignee_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-- Unassigned --</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Chi tiết">
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
                    trên tổng số <strong><?= $pagination['total'] ?></strong> mục (tối đa 20 trên 1 trang)
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
