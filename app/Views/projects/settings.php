<?php
/**
 * @var array $project
 * @var array $members
 * @var array $allUsers
 */
?>

<div class="container py-3" style="max-width: 900px;">
    <!-- BREADCRUMBS -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2 small">
            <li class="breadcrumb-item"><a href="/projects" class="text-decoration-none">Dự án</a></li>
            <li class="breadcrumb-item"><a href="/projects/<?= $project['id'] ?>/board" class="text-decoration-none"><?= htmlspecialchars($project['name']) ?></a></li>
            <li class="breadcrumb-item active">Cài đặt dự án</li>
        </ol>
    </nav>

    <div class="card border rounded-3 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 8px; background: <?= htmlspecialchars($project['avatar_bg'] ?? '#6554C0') ?>; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px;">
                        <i class="bi <?= htmlspecialchars($project['avatar_icon'] ?? 'bi-rocket-takeoff-fill') ?>"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Cài Đặt Dự Án: <?= htmlspecialchars($project['name']) ?></h4>
                        <span class="badge bg-light text-secondary border font-monospace mt-1">KEY: <?= htmlspecialchars($project['project_key']) ?></span>
                    </div>
                </div>
                <a href="/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại Board
                </a>
            </div>
        </div>

        <form action="/projects/<?= $project['id'] ?>/settings" method="POST" class="card-body p-4">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::getCsrfToken() ?>">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::getCsrfToken() ?>">

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold small text-secondary">TÊN DỰ ÁN</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($project['name']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small text-secondary">TRƯỞNG DỰ ÁN (PROJECT LEAD)</label>
                    <select name="lead_id" class="form-select">
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($u['id'] == $project['lead_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold small text-secondary">MÔ TẢ DỰ ÁN</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small text-secondary">LOẠI DỰ ÁN</label>
                    <select name="project_type" class="form-select">
                        <option value="SOFTWARE" <?= ($project['project_type'] === 'SOFTWARE') ? 'selected' : '' ?>>Software (Scrum / Kanban)</option>
                        <option value="SERVICE_DESK" <?= ($project['project_type'] === 'SERVICE_DESK') ? 'selected' : '' ?>>IT Service Desk</option>
                        <option value="BUSINESS" <?= ($project['project_type'] === 'BUSINESS') ? 'selected' : '' ?>>Business Project</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold small text-secondary">ICON ĐẠI DIỆN</label>
                    <select name="avatar_icon" class="form-select">
                        <option value="bi-rocket-takeoff-fill" <?= ($project['avatar_icon'] === 'bi-rocket-takeoff-fill') ? 'selected' : '' ?>>🚀 Rocket</option>
                        <option value="bi-code-slash" <?= ($project['avatar_icon'] === 'bi-code-slash') ? 'selected' : '' ?>>💻 Code Slash</option>
                        <option value="bi-laptop" <?= ($project['avatar_icon'] === 'bi-laptop') ? 'selected' : '' ?>>💻 Laptop</option>
                        <option value="bi-shield-check" <?= ($project['avatar_icon'] === 'bi-shield-check') ? 'selected' : '' ?>>🛡️ Shield</option>
                    </select>
                </div>
            </div>

            <!-- THÀNH VIÊN DỰ ÁN -->
            <div class="border-top pt-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0">Thành Viên Nhóm Dự Án (Team Members)</h5>
                    <span class="badge bg-light text-secondary border"><?= count($members) ?> thành viên</span>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Thành viên</th>
                                <th>Email</th>
                                <th>Vai trò trong dự án</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="jira-avatar-circle" style="width: 26px; height: 26px; font-size: 10px; background: #0052CC;">
                                                <?= strtoupper(substr($m['full_name'] ?: 'U', 0, 1)) ?>
                                            </div>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($m['full_name']) ?></span>
                                            <span class="text-muted small">(<?= htmlspecialchars($m['username']) ?>)</span>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($m['email']) ?></td>
                                    <td>
                                        <span class="badge <?= ($m['role'] === 'ADMIN') ? 'bg-primary' : 'bg-light text-secondary border' ?>">
                                            <?= $m['role'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($m['user_id'] != $project['lead_id']): ?>
                                            <a href="/projects/<?= $project['id'] ?>/settings/remove-member/<?= $m['user_id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Bạn có chắc muốn xóa thành viên này khỏi dự án?')" style="font-size: 11px;">
                                                <i class="bi bi-person-dash"></i> Xóa
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Project Lead</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Thêm thành viên mới -->
                <div class="p-3 bg-light rounded border">
                    <label class="form-label fw-bold small text-secondary">THÊM THÀNH VIÊN VÀO DỰ ÁN</label>
                    <div class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <select name="new_member_id" class="form-select form-select-sm">
                                <option value="0">-- Chọn người dùng để thêm --</option>
                                <?php foreach ($allUsers as $u): ?>
                                    <option value="<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?>) - <?= htmlspecialchars($u['role_names'] ?? $u['role'] ?? 'Thành viên') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="new_member_role" class="form-select form-select-sm">
                                <option value="MEMBER" selected>Member (Thành viên)</option>
                                <option value="ADMIN">Admin (Quản trị dự án)</option>
                                <option value="VIEWER">Viewer (Chỉ xem)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end pt-4 border-top mt-4 gap-2">
                <button type="submit" class="btn btn-primary fw-bold px-4" style="background:#0052cc; border-color:#0052cc;">
                    <i class="bi bi-check-lg me-1"></i> Lưu Cài Đặt Dự Án
                </button>
            </div>
        </form>
    </div>
</div>

