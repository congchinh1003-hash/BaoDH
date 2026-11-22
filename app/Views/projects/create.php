<?php
/**
 * @var array $users
 */
use App\Core\Auth;
?>
<div class="container py-3" style="max-width: 800px;">
    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2 small">
            <li class="breadcrumb-item"><a href="/projects" class="text-decoration-none">Dự án</a></li>
            <li class="breadcrumb-item active">Tạo mới</li>
        </ol>
    </nav>

    <div class="card border shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: #0052CC; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px;">
                    <i class="bi bi-kanban"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-1">Tạo Dự Án Mới</h4>
                    <p class="text-muted small mb-0">Thiết lập không gian làm việc cho nhóm phát triển phần mềm hoặc quản trị dịch vụ</p>
                </div>
            </div>
        </div>

        <form action="/projects/create" method="POST" class="card-body p-4">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">

            <div class="row g-4">
                <!-- Tên dự án & Key -->
                <div class="col-md-8">
                    <label class="form-label fw-bold text-dark">TÊN DỰ ÁN <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="projectNameInput" class="form-control form-control-lg" placeholder="Ví dụ: Cổng Thanh Toán Trực Tuyến, Mobile App..." required oninput="autoGenerateKey(this.value)">
                    <div class="form-text small">Tên đầy đủ, dễ nhớ của dự án.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold text-dark">MÃ DỰ ÁN (KEY) <span class="text-danger">*</span></label>
                    <input type="text" name="project_key" id="projectKeyInput" class="form-control form-control-lg text-uppercase fw-bold text-primary font-monospace" placeholder="VD: BG, PAY" maxlength="8" required>
                    <div class="form-text small">Tiền tố định danh mã issue (VD: BG-101).</div>
                </div>

                <!-- Loại dự án & Trưởng dự án -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">LOẠI DỰ ÁN (PROJECT TYPE)</label>
                    <select name="project_type" class="form-select">
                        <option value="SOFTWARE" selected>🚀 Software Development (Scrum / Kanban)</option>
                        <option value="SERVICE_DESK">🎧 IT Service Desk & Support</option>
                        <option value="BUSINESS">📊 Business & Operations Project</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">TRƯỞNG DỰ ÁN (PROJECT LEAD) <span class="text-danger">*</span></label>
                    <select name="lead_id" class="form-select" required>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($u['id'] == Auth::id()) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?>) - <?= htmlspecialchars($u['role_names'] ?? $u['role'] ?? 'Thành viên') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Mô tả -->
                <div class="col-12">
                    <label class="form-label fw-bold text-dark">MÔ TẢ DỰ ÁN</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Mục tiêu, phạm vi và công nghệ của dự án..."></textarea>
                </div>

                <!-- Chọn Icon & Màu Nhận Diện -->
                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">ICON ĐẠI DIỆN</label>
                    <select name="avatar_icon" class="form-select">
                        <option value="bi-rocket-takeoff-fill" selected>🚀 Rocket (Phát triển / Khởi động)</option>
                        <option value="bi-code-slash">💻 Code Slash (Lập trình phần mềm)</option>
                        <option value="bi-laptop">💻 Laptop (Hạ tầng công nghệ)</option>
                        <option value="bi-shield-check">🛡️ Shield (Bảo mật & Tuân thủ)</option>
                        <option value="bi-cart-check">🛒 E-Commerce (Thương mại điện tử)</option>
                        <option value="bi-currency-exchange">💳 Fintech / Thanh toán</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-dark">MÀU NỀN NHẬN DIỆN</label>
                    <select name="avatar_bg" class="form-select">
                        <option value="#6554C0" selected>Tím Jira (#6554C0)</option>
                        <option value="#0052CC">Xanh Dương Atlassian (#0052CC)</option>
                        <option value="#00875A">Xanh Lá Cây Agile (#00875A)</option>
                        <option value="#FF5630">Cam Đỏ Nhiệt Huyết (#FF5630)</option>
                        <option value="#FFAB00">Vàng Cam Sáng Tạo (#FFAB00)</option>
                    </select>
                </div>

                <!-- Chọn Thành Viên Ban Đầu -->
                <div class="col-12">
                    <label class="form-label fw-bold text-dark">THÀNH VIÊN THAM GIA DỰ ÁN</label>
                    <div class="p-3 bg-light rounded border" style="max-height: 200px; overflow-y: auto;">
                        <div class="row g-2">
                            <?php foreach ($users as $u): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="member_ids[]" value="<?= $u['id'] ?>" id="mem_<?= $u['id'] ?>" <?= ($u['id'] == Auth::id()) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="mem_<?= $u['id'] ?>">
                                            <strong><?= htmlspecialchars($u['full_name']) ?></strong> (<?= htmlspecialchars($u['username']) ?>)
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-text small">Các thành viên sẽ được phân quyền xem bảng và tham gia làm task/bug.</div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between pt-4 border-top mt-4">
                <a href="/projects" class="btn btn-outline-secondary px-3">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-primary fw-bold px-4" style="background:#0052cc; border-color:#0052cc;">
                    <i class="bi bi-check-lg me-1"></i> Tạo Dự Án & Vào Bảng
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function autoGenerateKey(val) {
    if (!val) return;
    const clean = val.trim().replace(/[^a-zA-Z0-9\s]/g, '');
    const words = clean.split(/\s+/);
    let key = '';
    if (words.length >= 2) {
        key = (words[0][0] || '') + (words[1][0] || '');
        if (words.length >= 3) key += (words[2][0] || '');
    } else {
        key = clean.substring(0, 3);
    }
    document.getElementById('projectKeyInput').value = key.toUpperCase();
}
</script>

