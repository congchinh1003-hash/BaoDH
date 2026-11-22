<?php
// Layout wrapper handled by controller render
?>
<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Trang chủ</a></li>
                    <li class="breadcrumb-item">Cấu hình Quản trị</li>
                    <li class="breadcrumb-item active" aria-current="page">Ma trận phân quyền (RBAC Matrix)</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary"></i>
                Ma Trận Phân Quyền Vai Trò &amp; Menu Hệ Thống
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Quản trị chi tiết quyền truy cập menu và tính năng hành động cho từng vai trò người dùng (Admin, IT Manager, IT Staff, User). Khi bỏ chọn quyền menu, người dùng sẽ không thấy menu và bị chặn truy cập.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/users" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-people-fill"></i> Quản lý Người dùng
            </a>
            <button type="submit" form="permissionMatrixForm" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm px-3 fw-bold">
                <i class="bi bi-check2-circle"></i> Lưu Thiết Lập Ma Trận
            </button>
        </div>
    </div>

    <!-- Alert / Tips -->
    <div class="alert alert-primary border-primary-subtle d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-xs">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill fs-4 text-primary"></i>
            <div>
                <strong class="d-block text-dark">Quy tắc bảo vệ Quản trị viên (ADMIN Super-User):</strong>
                <span class="text-muted small">Tài khoản thuộc vai trò <code>ADMIN</code> luôn có đặc quyền tối cao truy cập tất cả tài nguyên hệ thống kể cả khi quyền đó chưa được tích ở bảng ma trận bên dưới.</span>
            </div>
        </div>
        <div class="input-group input-group-sm w-auto">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" id="filterMatrix" class="form-control" placeholder="Tìm kiếm chức năng / mã quyền..." style="min-width: 260px;">
        </div>
    </div>

    <form id="permissionMatrixForm" action="/admin/permissions/update" method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="matrixTable">
                    <thead style="background:#F4F5F7; border-bottom: 2px solid #DFE1E6;">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fw-bold" style="font-size:0.75rem; width: 38%;">
                                Chức Năng / Quyền Hạn Hệ Thống
                            </th>
                            <?php foreach ($roles as $r): ?>
                                <th class="text-center py-3" style="min-width: 140px;">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge mb-1 <?= $r['name'] === 'ADMIN' ? 'bg-danger' : ($r['name'] === 'IT_MANAGER' ? 'bg-primary' : ($r['name'] === 'IT_STAFF' ? 'bg-info text-dark' : 'bg-secondary')) ?> px-2 py-1" style="font-size:0.75rem; letter-spacing: 0.5px;">
                                            <?= htmlspecialchars($r['name']) ?>
                                        </span>
                                        <small class="text-muted fw-normal" style="font-size:0.7rem;"><?= htmlspecialchars($r['description'] ?? '') ?></small>
                                        
                                        <!-- Column quick toggles -->
                                        <div class="d-flex gap-1 mt-1">
                                            <button type="button" class="btn btn-link p-0 text-decoration-none text-primary" style="font-size:0.68rem;" onclick="toggleColumn(<?= $r['id'] ?>, true)">Chọn hết</button>
                                            <span class="text-muted" style="font-size:0.68rem;">|</span>
                                            <button type="button" class="btn btn-link p-0 text-decoration-none text-secondary" style="font-size:0.68rem;" onclick="toggleColumn(<?= $r['id'] ?>, false)">Bỏ chọn</button>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $moduleLabels = [
                            'PROJECTS'   => ['name' => 'Dự án Jira & Công việc (Projects, Sprints, Issues, Board)', 'icon' => 'bi-kanban-fill text-primary'],
                            'TICKETS'    => ['name' => 'Phiếu Hỗ Trợ Kỹ Thuật (Tickets & Workflows)', 'icon' => 'bi-ticket-detailed-fill text-success'],
                            'CATALOG'    => ['name' => 'Cổng Dịch Vụ Người Dùng (Service Catalog)', 'icon' => 'bi-grid-fill text-warning'],
                            'APPROVALS'  => ['name' => 'Trung Tâm Phê Duyệt Đơn (Approvals Center)', 'icon' => 'bi-check-circle-fill text-info'],
                            'ASSETS'     => ['name' => 'Quản Lý Tài Sản Thiết Bị (IT Asset Management - ITAM)', 'icon' => 'bi-laptop-fill text-secondary'],
                            'KB'         => ['name' => 'Cơ Sở Tri Thức & KEDB (Knowledge Base)', 'icon' => 'bi-book-fill text-primary'],
                            'JUMPSERVER' => ['name' => 'Cổng Điều Khiển Bastion PAM (JumpServer)', 'icon' => 'bi-terminal-fill text-dark'],
                            'REPORTS'    => ['name' => 'Báo Cáo & Phân Tích Hiệu Suất (Reports & BI)', 'icon' => 'bi-graph-up-arrow text-success'],
                            'EXPORT'     => ['name' => 'Xuất Dữ Liệu Excel / CSV (Data Grids Export)', 'icon' => 'bi-file-earmark-excel-fill text-success'],
                            'ADMIN'      => ['name' => 'Quản Trị Hệ Thống Nâng Cao (System Administration)', 'icon' => 'bi-gear-wide-connected text-danger'],
                            'SYSTEM'     => ['name' => 'Sao Lưu & Khôi Phục Dữ Liệu CSDL (Backup & Disaster Recovery)', 'icon' => 'bi-database-fill-gear text-dark'],
                        ];
                        ?>

                        <?php foreach ($groupedPerms as $moduleKey => $perms): ?>
                            <?php 
                            $modMeta = $moduleLabels[$moduleKey] ?? ['name' => $moduleKey, 'icon' => 'bi-folder-fill text-secondary'];
                            ?>
                            <!-- Module Group Header Row -->
                            <tr class="table-light module-header-row" data-module="<?= htmlspecialchars($moduleKey) ?>" style="border-top: 1px solid #DFE1E6; border-bottom: 1px solid #DFE1E6;">
                                <td colspan="<?= count($roles) + 1 ?>" class="ps-4 py-2 bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size:0.85rem;">
                                            <i class="bi <?= $modMeta['icon'] ?>"></i>
                                            <?= htmlspecialchars($modMeta['name']) ?>
                                            <span class="badge bg-white text-secondary border ms-1" style="font-size:0.68rem;"><?= count($perms) ?> quyền</span>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:0.7rem;" onclick="toggleModule('<?= htmlspecialchars($moduleKey) ?>')">
                                            Chọn tất cả nhóm này
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Permission Rows -->
                            <?php foreach ($perms as $p): ?>
                                <tr class="perm-row" data-module="<?= htmlspecialchars($moduleKey) ?>" data-text="<?= htmlspecialchars(strtolower($p['name'] . ' ' . $p['code'] . ' ' . ($p['description'] ?? ''))) ?>">
                                    <td class="ps-4 py-2">
                                        <div class="d-flex align-items-start gap-2">
                                            <?php if (!empty($p['is_menu'])): ?>
                                                <span class="badge border bg-light text-primary mt-1" title="Quyền hiển thị Menu điều hướng">
                                                    <i class="bi bi-layout-sidebar-inset"></i> Menu
                                                </span>
                                            <?php else: ?>
                                                <span class="badge border bg-light text-muted mt-1" title="Quyền thực thi tác vụ">
                                                    <i class="bi bi-lightning-fill"></i> Tác vụ
                                                </span>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:0.875rem;">
                                                    <?= htmlspecialchars($p['name']) ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 mt-0">
                                                    <code class="text-primary small" style="font-size:0.73rem; background:rgba(37,99,235,0.06); padding:1px 4px; border-radius:3px;"><?= htmlspecialchars($p['code']) ?></code>
                                                    <?php if (!empty($p['description'])): ?>
                                                        <span class="text-muted" style="font-size:0.75rem;">- <?= htmlspecialchars($p['description']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <?php foreach ($roles as $r): ?>
                                        <?php 
                                        $isChecked = !empty($activeMatrix[$r['id']][$p['id']]);
                                        // Admin is always checked visually, but allow toggle if saved
                                        ?>
                                        <td class="text-center py-2">
                                            <div class="form-check d-flex justify-content-center m-0">
                                                <input class="form-check-input perm-checkbox col-role-<?= $r['id'] ?> mod-perm-<?= htmlspecialchars($moduleKey) ?>" 
                                                       type="checkbox" 
                                                       name="matrix[<?= $r['id'] ?>][<?= $p['id'] ?>]" 
                                                       value="1"
                                                       id="perm_<?= $r['id'] ?>_<?= $p['id'] ?>"
                                                       <?= $isChecked ? 'checked' : '' ?>
                                                       style="cursor: pointer; width: 1.15rem; height: 1.15rem;">
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sticky/Bottom Actions -->
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Sau khi lưu, phân quyền mới sẽ có hiệu lực ngay lập tức cho toàn bộ người dùng đang trực tuyến.
                </span>
                <div class="d-flex gap-2">
                    <button type="reset" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-arrow-counterclockwise"></i> Khôi phục ban đầu
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Lưu Ma Trận Phân Quyền
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Filter table rows
document.getElementById('filterMatrix').addEventListener('input', function() {
    var filter = this.value.toLowerCase().trim();
    var rows = document.querySelectorAll('.perm-row');
    var headerRows = document.querySelectorAll('.module-header-row');

    rows.forEach(function(row) {
        var text = row.getAttribute('data-text') || '';
        if (text.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    // Check module header visibility
    headerRows.forEach(function(header) {
        var mod = header.getAttribute('data-module');
        var visiblePerms = document.querySelectorAll('.perm-row[data-module="' + mod + '"]:not([style*="display: none"])');
        if (visiblePerms.length > 0) {
            header.style.display = '';
        } else {
            header.style.display = 'none';
        }
    });
});

// Toggle all checkboxes in a column (for a specific role)
function toggleColumn(roleId, state) {
    var cbs = document.querySelectorAll('.col-role-' + roleId);
    cbs.forEach(function(cb) {
        cb.checked = state;
    });
}

// Toggle all checkboxes in a specific module
function toggleModule(moduleKey) {
    var cbs = document.querySelectorAll('.mod-perm-' + moduleKey);
    if (cbs.length === 0) return;
    // Check if at least one is unchecked; if so, check all, else uncheck all
    var hasUnchecked = Array.from(cbs).some(cb => !cb.checked);
    cbs.forEach(function(cb) {
        cb.checked = hasUnchecked;
    });
}
</script>

