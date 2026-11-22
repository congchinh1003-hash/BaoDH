<?php
/**
 * @var array $projects
 */
?>
<style>
.project-card-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #DFE1E6;
    background: #FFFFFF;
}
.project-card-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(9, 30, 66, 0.12) !important;
}
.text-purple {
    color: #6554C0 !important;
}
.hover-primary:hover {
    color: #0C66E4 !important;
}
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.5;
}
</style>

<div class="container-fluid px-0">
    <!-- HERO HEADER -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="/projects" class="text-decoration-none">TaskFlow Agile</a></li>
                    <li class="breadcrumb-item active">Dự án</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0">Tất Cả Dự Án</h3>
            <p class="text-muted small mb-0">Quản lý và theo dõi tiến độ công việc, bug, testcase theo từng dự án phần mềm</p>
        </div>
        <?php if (\App\Core\Auth::can('projects.create')): ?>
        <div class="d-flex align-items-center gap-2">
            <a href="/projects/create" class="btn btn-primary fw-bold px-3" style="background:#0C66E4; border-color:#0C66E4;">
                <i class="bi bi-plus-lg me-1"></i> Tạo Dự Án Mới
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- PROJECT LIST CARDS -->
    <?php if (empty($projects)): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="py-4">
                <i class="bi bi-kanban text-secondary opacity-50" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold mt-3 text-dark">Chưa Có Dự Án Nào</h5>
                <p class="text-muted small">
                    <?= \App\Core\Auth::can('projects.create') 
                        ? 'Hãy tạo dự án đầu tiên để phân chia công việc, ca kiểm thử và theo dõi lỗi.' 
                        : 'Bạn hiện chưa được phân công tham gia dự án nào. Vui lòng liên hệ Quản trị viên để được gán vào dự án.' ?>
                </p>
                <?php if (\App\Core\Auth::can('projects.create')): ?>
                    <a href="/projects/create" class="btn btn-primary btn-sm px-3 mt-2">
                        <i class="bi bi-plus-circle me-1"></i> Bắt đầu tạo dự án
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($projects as $prj): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card shadow-sm h-100 rounded-3 project-card-item">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width: 44px; height: 44px; border-radius: 8px; background: <?= htmlspecialchars($prj['avatar_bg'] ?? '#6554C0') ?>; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                            <i class="bi <?= htmlspecialchars($prj['avatar_icon'] ?? 'bi-rocket-takeoff-fill') ?>"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-0">
                                                <a href="/projects/<?= $prj['id'] ?>/board" class="text-decoration-none text-dark hover-primary">
                                                    <?= htmlspecialchars($prj['name']) ?>
                                                </a>
                                            </h5>
                                            <span class="badge bg-light text-secondary border fw-semibold mt-1">
                                                KEY: <?= htmlspecialchars($prj['project_key']) ?>
                                            </span>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 ms-1">
                                                <?= $prj['project_type'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-1">
                                            <li><a class="dropdown-item small" href="/projects/<?= $prj['id'] ?>/board"><i class="bi bi-kanban me-2 text-primary"></i> Xem Bảng Board</a></li>
                                            <li><a class="dropdown-item small" href="/projects/<?= $prj['id'] ?>/backlog"><i class="bi bi-layers me-2 text-info"></i> Xem Backlog</a></li>
                                            <li><a class="dropdown-item small" href="/projects/<?= $prj['id'] ?>/testcases"><i class="bi bi-clipboard-check me-2 text-purple"></i> Test Cases</a></li>
                                            <li><a class="dropdown-item small" href="/projects/<?= $prj['id'] ?>/bugs"><i class="bi bi-bug me-2 text-danger"></i> Bug Defect</a></li>
                                            <?php if (\App\Core\Auth::isManager() || \App\Core\Auth::can('projects.manage') || (int)$prj['lead_id'] === (int)\App\Core\Auth::id()): ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item small" href="/projects/<?= $prj['id'] ?>/settings"><i class="bi bi-gear me-2"></i> Cài Đặt Dự Án</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>

                                <p class="text-muted small mb-3 text-truncate-2" style="min-height: 38px;">
                                    <?= htmlspecialchars($prj['description'] ?: 'Không có mô tả chi tiết cho dự án này.') ?>
                                </p>

                                <!-- METRICS MINI TILES -->
                                <div class="row g-2 mb-3 text-center">
                                    <div class="col-3">
                                        <div class="p-2 bg-light rounded border">
                                            <div class="fw-bold text-dark fs-6"><?= $prj['total_issues'] ?></div>
                                            <div class="text-muted" style="font-size: 10px;">TỔNG CỘNG</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 bg-light rounded border">
                                            <div class="fw-bold text-success fs-6"><?= $prj['done_issues'] ?></div>
                                            <div class="text-muted" style="font-size: 10px;">HOÀN THÀNH</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 bg-light rounded border">
                                            <div class="fw-bold text-danger fs-6"><?= $prj['bug_count'] ?></div>
                                            <div class="text-muted" style="font-size: 10px;">BUGS</div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2 bg-light rounded border">
                                            <div class="fw-bold text-purple fs-6"><?= $prj['testcase_count'] ?></div>
                                            <div class="text-muted" style="font-size: 10px;">TESTCASE</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PROGRESS BAR -->
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Tiến độ hoàn thành</span>
                                        <span class="fw-bold text-dark"><?= $prj['progress_percent'] ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $prj['progress_percent'] ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD FOOTER -->
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="jira-avatar-circle" style="width: 26px; height: 26px; font-size: 10px; background: #0052CC;">
                                        <?= strtoupper(substr($prj['lead_name'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <span class="text-secondary small"><?= htmlspecialchars($prj['lead_name'] ?? 'Admin') ?></span>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <a href="/projects/<?= $prj['id'] ?>/backlog" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11px;">
                                        Backlog
                                    </a>
                                    <a href="/projects/<?= $prj['id'] ?>/board" class="btn btn-sm btn-primary py-1 px-2 fw-semibold" style="font-size: 11px; background:#0052cc; border-color:#0052cc;">
                                        Vào Bảng <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- PAGINATION BAR (Tối đa 20 / trang) -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card border rounded-3 bg-white mt-4 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small text-muted">
                    Hiển thị <?= min(($pagination['page'] - 1) * $pagination['per_page'] + 1, $pagination['total']) ?> - 
                    <?= min($pagination['page'] * $pagination['per_page'], $pagination['total']) ?> 
                    trên tổng số <strong><?= $pagination['total'] ?></strong> dự án (tối đa 20 trên 1 trang)
                </div>
                <?php if ($pagination['total_pages'] > 1): ?>
                    <ul class="pagination pagination-sm tf-pagination mb-0">
                        <li class="page-item <?= ($pagination['page'] <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>">« Trước</a>
                        </li>
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                            <li class="page-item <?= ($p === $pagination['page']) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagination['page'] >= $pagination['total_pages']) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>">Sau »</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

