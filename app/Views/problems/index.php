<?php
/**
 * @var array $problems
 * @var array $filters
 * @var array $kpi
 */
$baseUrl = \App\Core\Response::baseUrl();
?>
<style>
.problem-hero { background:#fff; border-left:4px solid #DE350B; border-radius:10px; padding:20px 24px; box-shadow:0 1px 4px rgba(9,30,66,0.12); border:1px solid #DFE1E6; }
.filter-card { background:#fff; border:1px solid #DFE1E6; border-radius:10px; box-shadow:0 1px 4px rgba(9,30,66,0.12); }
.table-card { background:#fff; border:1px solid #DFE1E6; border-radius:10px; box-shadow:0 1px 4px rgba(9,30,66,0.12); overflow:hidden; }
.table-card .table thead th { background:#F4F5F7; color:#5E6C84; font-size:11px; letter-spacing:0.5px; text-transform:uppercase; font-weight:700; border-bottom:1px solid #DFE1E6; padding:12px 16px; }
.table-card .table tbody tr { border-bottom:1px solid #F4F5F7; }
.table-card .table tbody tr:hover { background:#FAFBFC; }
.table-card .table tbody td { padding:12px 16px; vertical-align:middle; }
.kedb-badge { background:#172B4D; color:#FFC400; border:1px solid #FFC400; font-size:10px; font-weight:700; padding:2px 7px; border-radius:3px; letter-spacing:0.5px; }
.badge-logged { background:#DFE1E6; color:#172B4D; }
.badge-investigating { background:#FFF0B3; color:#172B4D; }
.badge-workaround { background:#DEEBFF; color:#0052CC; }
.badge-known-error { background:#172B4D; color:#FFC400; border:1px solid #FFC400; }
.badge-resolved { background:#E3FCEF; color:#006644; }
.badge-closed { background:#F4F5F7; color:#5E6C84; }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Page Hero -->
    <div class="problem-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:10px;background:#DE350B;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-exclamation-octagon-fill text-white fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1">Quản Lý Problem &amp; RCA (KEDB)</h5>
                    <p class="text-muted small mb-0">Điều tra nguyên nhân gốc rễ (Root Cause Analysis), xây dựng Workaround và quản lý cơ sở dữ liệu lỗi đã biết (Known Error Database).</p>
                </div>
            </div>
            <a href="<?= $baseUrl ?>/problems/create" class="btn fw-semibold shadow-sm flex-shrink-0" style="background:#DE350B;color:#fff;border:none;padding:8px 18px;border-radius:6px;">
                <i class="bi bi-plus-lg me-1"></i> Khai Báo Problem Mới
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-card mb-4 p-3">
        <form method="GET" action="<?= $baseUrl ?>/problems" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Mã problem, tiêu đề, nguyên nhân..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-flag"></i></span>
                    <select name="status" class="form-select border-start-0" style="padding-left:0;">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="LOGGED" <?= ($filters['status'] ?? '') === 'LOGGED' ? 'selected' : '' ?>>Mới ghi nhận (LOGGED)</option>
                        <option value="INVESTIGATING" <?= ($filters['status'] ?? '') === 'INVESTIGATING' ? 'selected' : '' ?>>Đang điều tra RCA</option>
                        <option value="WORKAROUND_FOUND" <?= ($filters['status'] ?? '') === 'WORKAROUND_FOUND' ? 'selected' : '' ?>>Đã có giải pháp tạm</option>
                        <option value="KNOWN_ERROR" <?= ($filters['status'] ?? '') === 'KNOWN_ERROR' ? 'selected' : '' ?>>Lỗi đã biết (Known Error)</option>
                        <option value="RESOLVED" <?= ($filters['status'] ?? '') === 'RESOLVED' ? 'selected' : '' ?>>Đã giải quyết triệt để</option>
                        <option value="CLOSED" <?= ($filters['status'] ?? '') === 'CLOSED' ? 'selected' : '' ?>>Đã đóng</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-bookmark-x"></i></span>
                    <select name="is_known_error" class="form-select border-start-0" style="padding-left:0;">
                        <option value="">-- KEDB --</option>
                        <option value="1" <?= ($filters['is_known_error'] ?? '') === '1' ? 'selected' : '' ?>>Chỉ lỗi đã biết (KEDB)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm fw-semibold flex-grow-1" style="background:#0052CC;color:#fff;border:none;">
                    <i class="bi bi-funnel me-1"></i> Lọc kết quả
                </button>
                <a href="<?= $baseUrl ?>/problems" class="btn btn-light btn-sm border">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Problems Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:140px;">Mã Problem</th>
                        <th>Tiêu Đề &amp; Known Error</th>
                        <th>Danh Mục</th>
                        <th>Mức Độ</th>
                        <th>Trạng Thái</th>
                        <th>Incidents</th>
                        <th>Phụ Trách</th>
                        <th class="text-end" style="padding-right:20px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($problems)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success opacity-50"></i>
                                <div class="fw-semibold">Không có Problem nào theo tiêu chí lọc.</div>
                                <div class="small mt-1">Hệ thống đang hoạt động ổn định!</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($problems as $p): ?>
                            <?php
                            $statusBadge = match($p['status']) {
                                'LOGGED'           => '<span class="badge badge-logged"><i class="bi bi-circle me-1"></i>Mới ghi nhận</span>',
                                'INVESTIGATING'    => '<span class="badge badge-investigating"><i class="bi bi-search me-1"></i>Đang điều tra RCA</span>',
                                'WORKAROUND_FOUND' => '<span class="badge badge-workaround"><i class="bi bi-bandaid me-1"></i>Có giải pháp tạm</span>',
                                'KNOWN_ERROR'      => '<span class="badge badge-known-error"><i class="bi bi-bookmark-x me-1"></i>Known Error</span>',
                                'RESOLVED'         => '<span class="badge badge-resolved"><i class="bi bi-check-all me-1"></i>Đã xử lý xong</span>',
                                'CLOSED'           => '<span class="badge badge-closed">Đã đóng</span>',
                                default            => '<span class="badge badge-logged">' . htmlspecialchars($p['status']) . '</span>'
                            };
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= $baseUrl ?>/problems/<?= $p['id'] ?>" class="fw-bold text-decoration-none" style="color:#DE350B;">
                                        <?= htmlspecialchars($p['problem_code']) ?>
                                    </a>
                                </td>
                                <td style="max-width:380px;">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <?php if (!empty($p['is_known_error'])): ?>
                                            <span class="kedb-badge" title="Known Error Database">KEDB</span>
                                        <?php endif; ?>
                                        <a href="<?= $baseUrl ?>/problems/<?= $p['id'] ?>" class="text-dark fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($p['workaround'])): ?>
                                        <div class="text-muted text-truncate" style="font-size:12px;max-width:360px;">
                                            <i class="bi bi-bandaid text-primary me-1"></i>Workaround: <?= htmlspecialchars($p['workaround']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background:#F4F5F7;color:#172B4D;border:1px solid #DFE1E6;"><?= htmlspecialchars($p['category_name'] ?? 'Chung') ?></span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: <?= $p['priority_color'] ?? '#6c757d' ?>;">
                                        <?= htmlspecialchars($p['priority_name'] ?? '') ?>
                                    </span>
                                </td>
                                <td><?= $statusBadge ?></td>
                                <td>
                                    <span class="badge" style="background:#DEEBFF;color:#0052CC;border:1px solid #B3D4FF;">
                                        <i class="bi bi-link-45deg me-1"></i><?= (int)($p['incident_count'] ?? 0) ?> incidents
                                    </span>
                                </td>
                                <td class="small text-dark">
                                    <?= htmlspecialchars($p['assignee_name'] ?? 'Chưa phân công') ?>
                                </td>
                                <td class="text-end" style="padding-right:20px;">
                                    <a href="<?= $baseUrl ?>/problems/<?= $p['id'] ?>" class="btn btn-sm fw-semibold" style="background:#DEEBFF;color:#0052CC;border:1px solid #B3D4FF;padding:5px 14px;">
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
