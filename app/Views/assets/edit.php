<?php
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
?>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-pencil-square fs-4 text-white"></i>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:.78rem;">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/assets" class="text-decoration-none" style="color:#0052CC;">Tài sản CNTT</a></li>
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>" class="text-decoration-none" style="color:#0052CC;"><?= htmlspecialchars($asset['asset_code']) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="color:#5E6C84;">Chỉnh sửa</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Chỉnh Sửa Thông Tin Tài Sản: <span style="color:#0052CC;"><?= htmlspecialchars($asset['asset_code']) ?></span></h4>
        </div>
    </div>
    <a href="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>" class="btn"
       style="border:1.5px solid #DFE1E6;border-radius:6px;color:#5E6C84;background:#fff;font-size:.875rem;">
        <i class="bi bi-arrow-left me-1"></i>Quay lại chi tiết
    </a>
</div>

<div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;max-width:900px;margin:0 auto;">
    <div class="card-body p-0">
        <form action="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/update" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

            <!-- Section: Thông tin cơ bản -->
            <div class="px-4 pt-4 pb-3" style="border-bottom:1px solid #DFE1E6;">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#0052CC;">
                    <i class="bi bi-laptop"></i> Thông Tin Cơ Bản
                </h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Tên thiết bị <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Loại thiết bị <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" style="border:1.5px solid #DFE1E6;border-radius:6px;" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((int)$asset['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Hãng sản xuất</label>
                        <input type="text" name="manufacturer" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['manufacturer'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Model</label>
                        <input type="text" name="model" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['model'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Số Serial Number</label>
                        <input type="text" name="serial_number" class="form-control font-monospace" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['serial_number'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Section: Thời hạn & Vị trí -->
            <div class="px-4 py-3" style="border-bottom:1px solid #DFE1E6;">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#0052CC;">
                    <i class="bi bi-calendar3"></i> Thời Hạn &amp; Vị Trí
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Ngày mua</label>
                        <input type="date" name="purchase_date" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['purchase_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Hạn bảo hành</label>
                        <input type="date" name="warranty_expire" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['warranty_expire'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Phòng ban quản lý</label>
                        <input type="text" name="department" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['department'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Vị trí lắp đặt / Lưu kho</label>
                        <input type="text" name="location" class="form-control" style="border:1.5px solid #DFE1E6;border-radius:6px;"
                               value="<?= htmlspecialchars($asset['location'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Section: Thông số & Ghi chú -->
            <div class="px-4 py-3" style="border-bottom:1px solid #DFE1E6;">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#0052CC;">
                    <i class="bi bi-cpu"></i> Cấu Hình &amp; Ghi Chú
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Cấu hình chi tiết / Thông số kỹ thuật</label>
                        <textarea name="specifications" class="form-control font-monospace small" rows="3"
                                  style="border:1.5px solid #DFE1E6;border-radius:6px;"><?= htmlspecialchars($asset['specifications'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small" style="color:#172B4D;">Ghi chú quản lý</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  style="border:1.5px solid #DFE1E6;border-radius:6px;"><?= htmlspecialchars($asset['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-4 py-3 d-flex justify-content-end gap-2">
                <a href="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>" class="btn px-4"
                   style="border:1.5px solid #DFE1E6;border-radius:6px;color:#5E6C84;background:#fff;">Hủy</a>
                <button type="submit" class="btn fw-bold px-4"
                        style="background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);color:#fff;border:none;border-radius:6px;">
                    <i class="bi bi-save me-1"></i>Cập Nhật Thông Tin
                </button>
            </div>
        </form>
    </div>
</div>
