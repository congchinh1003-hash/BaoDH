<?php
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
$categories = $categories ?? [];
$users = $users ?? [];
?>

<!-- PAGE HERO -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-plus-circle fs-4 text-white"></i>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:.78rem;">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/assets" class="text-decoration-none" style="color:#0052CC;">Tài sản CNTT</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="color:#5E6C84;">Thêm mới</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Khai Báo Thiết Bị / Tài Sản CNTT Mới</h4>
        </div>
    </div>
    <a href="<?= $baseUrl ?>/assets" class="btn" style="border:1.5px solid #DFE1E6;border-radius:6px;color:#5E6C84;background:#fff;font-size:.875rem;">
        <i class="bi bi-arrow-left me-1"></i>Hủy &amp; Quay lại
    </a>
</div>

<div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;max-width:900px;margin:0 auto;">
    <div class="card-body p-0">
        <form action="<?= $baseUrl ?>/assets/store" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

            <!-- Section: Thông tin cơ bản -->
            <div class="px-4 pt-4 pb-3" style="border-bottom:1px solid #DFE1E6;">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#0052CC;">
                    <i class="bi bi-laptop"></i> Thông Tin Cơ Bản
                </h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Tên thiết bị <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               placeholder="Ví dụ: Laptop Dell Latitude 5420, Màn hình LG 27 inch..." required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Loại thiết bị <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" style="border:1.5px solid #DFE1E6;border-radius:6px;" required>
                            <option value="">-- Chọn loại tài sản --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Mã tài sản (Asset Code)</label>
                        <input type="text" name="asset_code" class="form-control font-monospace" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               placeholder="Để trống để hệ thống tự cấp mã">
                        <div class="form-text" style="font-size:.72rem;color:#5E6C84;">Để trống sẽ tự động cấp mã: AST-LAP-001...</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Hãng sản xuất (Brand)</label>
                        <input type="text" name="manufacturer" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               placeholder="Dell, HP, Apple, Canon, Cisco...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Model chi tiết</label>
                        <input type="text" name="model" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               placeholder="Latitude 5420, MacBook Pro 14...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Số Serial Number</label>
                        <input type="text" name="serial_number" class="form-control font-monospace" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               placeholder="SN-XXXX-YYYY">
                    </div>
                </div>
            </div>

            <!-- Section: Thời hạn & Vị trí -->
            <div class="px-4 py-3" style="border-bottom:1px solid #DFE1E6;">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#0052CC;">
                    <i class="bi bi-calendar3"></i> Thời Hạn &amp; Vị Trí
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Ngày mua / Bắt đầu</label>
                        <input type="date" name="purchase_date" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Hạn bảo hành (Warranty)</label>
                        <input type="date" name="warranty_expire" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Phòng ban quản lý / sở hữu</label>
                        <input type="text" name="department" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               placeholder="Kho IT, Kinh doanh, Kế toán, Ban Giám đốc...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Vị trí lắp đặt / Lưu kho</label>
                        <input type="text" name="location" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               placeholder="Kho IT - Kệ A2, Tầng 3 - Bàn 12...">
                    </div>
                </div>
            </div>

            <!-- Section: Cấp phát & Thông số -->
            <div class="px-4 py-3" style="border-bottom:1px solid #DFE1E6;">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#0052CC;">
                    <i class="bi bi-person-check"></i> Cấp Phát &amp; Thông Số Kỹ Thuật
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Cấp phát ngay cho nhân viên (Tùy chọn)</label>
                        <select name="current_user_id" class="form-select" style="border:1.5px solid #DFE1E6;border-radius:6px;">
                            <option value="">-- Chưa cấp phát (Lưu trong kho IT) --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['department'] ?: 'N/A') ?> - <?= htmlspecialchars($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Thông số kỹ thuật / Cấu hình chi tiết</label>
                        <textarea name="specifications" class="form-control font-monospace small" rows="3"
                                  style="border:1.5px solid #DFE1E6;border-radius:6px;"
                                  placeholder="CPU: Core i5 1135G7&#10;RAM: 16GB DDR4&#10;SSD: 512GB NVMe&#10;Màn hình: 14 inch FHD"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Ghi chú thêm</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  style="border:1.5px solid #DFE1E6;border-radius:6px;"
                                  placeholder="Ghi chú về phụ kiện kèm theo như sạc, túi, chuột..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-4 py-3 d-flex justify-content-end gap-2">
                <a href="<?= $baseUrl ?>/assets" class="btn px-4" style="border:1.5px solid #DFE1E6;border-radius:6px;color:#5E6C84;background:#fff;">Hủy</a>
                <button type="submit" class="btn fw-bold px-4"
                        style="background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);color:#fff;border:none;border-radius:6px;">
                    <i class="bi bi-save-fill me-1"></i>Lưu Tài Sản
                </button>
            </div>
        </form>
    </div>
</div>
