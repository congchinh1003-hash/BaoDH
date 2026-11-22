<?php
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $categories
 * @var array $services
 * @var array $groups
 */
$baseUrl = Response::baseUrl();
$groups = $groups ?? [];
$csrfToken = Session::csrfToken();
?>

<!-- PAGE HERO -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-tags-fill fs-4 text-white"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Cấu Hình Danh Mục &amp; Dịch Vụ Hỗ Trợ IT</h4>
            <p class="mb-0 small" style="color:#5E6C84;">Cấu hình linh hoạt các phân loại sự cố cấp 1 (Category) và dịch vụ chi tiết cấp 2 (Service Catalog)</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-folder-plus me-1"></i>Thêm Danh Mục Cấp 1
        </button>
        <button type="button" class="btn btn-success fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-circle me-1"></i>Thêm Dịch Vụ Cấp 2
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- DANH MỤC LỚN (CATEGORIES) -->
    <div class="col-12 col-xl-5">
        <div class="card h-100" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid #DFE1E6;border-radius:10px 10px 0 0;">
                <span class="fw-bold" style="color:#172B4D;">
                    <i class="bi bi-folder2-open me-2" style="color:#0052CC;"></i>Danh Mục Sự Cố (<?= count($categories) ?>)
                </span>
                <span class="badge px-2 py-1" style="background:#EAF0FB;color:#0052CC;border:1px solid #B3C7F5;font-size:.75rem;">
                    Cấp 1
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead>
                        <tr style="background:#F4F5F7;color:#5E6C84;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                            <th class="ps-4 py-3">Danh mục</th>
                            <th class="py-3">Nhóm IT mặc định</th>
                            <th class="py-3 text-center">Dịch vụ</th>
                            <th class="pe-4 py-3 text-end" style="width:100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">Chưa có danh mục nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold" style="color:#172B4D;"><?= htmlspecialchars($cat['name']) ?></div>
                                        <span class="font-monospace" style="font-size:.70rem;color:#5E6C84;background:#F4F5F7;padding:1px 6px;border-radius:4px;border:1px solid #DFE1E6;">
                                            <?= htmlspecialchars($cat['code']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge px-2 py-1" style="background:#F4F5F7;color:#5E6C84;border:1px solid #DFE1E6;font-size:.75rem;">
                                            <i class="bi bi-people me-1"></i><?= htmlspecialchars($cat['default_group_name'] ?? 'Tự động') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge px-3 py-1 fw-bold" style="background:#EAF0FB;color:#0052CC;border:1px solid #B3C7F5;font-size:.85rem;">
                                            <?= (int)$cat['service_count'] ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- Sửa Category -->
                                            <button type="button" class="btn btn-outline-secondary btn-sm p-1 px-2"
                                                    title="Chỉnh sửa danh mục"
                                                    onclick="openEditCategoryModal(<?= htmlspecialchars(json_encode([
                                                        'id'               => $cat['id'],
                                                        'name'             => $cat['name'],
                                                        'code'             => $cat['code'],
                                                        'description'      => $cat['description'] ?? '',
                                                        'default_group_id' => $cat['default_group_id'] ?? ''
                                                    ]), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <!-- Xóa Category -->
                                            <form action="<?= $baseUrl ?>/admin/categories/<?= $cat['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này? Nếu danh mục đã có ticket, hệ thống sẽ ẩn thay vì xóa vĩnh viễn.');">
                                                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Xóa danh mục">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DỊCH VỤ CON (SERVICES) -->
    <div class="col-12 col-xl-7">
        <div class="card h-100" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center"
                 style="border-bottom:1px solid #DFE1E6;border-radius:10px 10px 0 0;">
                <span class="fw-bold" style="color:#172B4D;">
                    <i class="bi bi-gear-wide-connected me-2" style="color:#00875A;"></i>Dịch Vụ Hỗ Trợ Chi Tiết (<?= count($services) ?>)
                </span>
                <span class="badge px-2 py-1" style="background:#E3FCEF;color:#00875A;border:1px solid #ABF5D1;font-size:.75rem;">
                    Cấp 2
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead>
                        <tr style="background:#F4F5F7;color:#5E6C84;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                            <th class="ps-4 py-3">Tên Dịch vụ</th>
                            <th class="py-3">Thuộc danh mục</th>
                            <th class="py-3">Mô tả chi tiết</th>
                            <th class="pe-4 py-3 text-end" style="width:100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">Chưa có dịch vụ con nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $srv): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold" style="color:#172B4D;"><?= htmlspecialchars($srv['name']) ?></div>
                                        <span class="font-monospace" style="font-size:.70rem;color:#5E6C84;background:#F4F5F7;padding:1px 6px;border-radius:4px;border:1px solid #DFE1E6;">
                                            <?= htmlspecialchars($srv['code']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge px-2 py-1" style="background:#EAF0FB;color:#0052CC;border:1px solid #B3C7F5;font-size:.75rem;">
                                            <i class="bi bi-folder2 me-1"></i><?= htmlspecialchars($srv['category_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted text-truncate d-inline-block" style="max-width:260px;">
                                            <?= htmlspecialchars($srv['description'] ?? 'Chưa có mô tả') ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- Sửa Service -->
                                            <button type="button" class="btn btn-outline-secondary btn-sm p-1 px-2"
                                                    title="Chỉnh sửa dịch vụ"
                                                    onclick="openEditServiceModal(<?= htmlspecialchars(json_encode([
                                                        'id'          => $srv['id'],
                                                        'category_id' => $srv['category_id'],
                                                        'name'        => $srv['name'],
                                                        'code'        => $srv['code'],
                                                        'description' => $srv['description'] ?? ''
                                                    ]), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <!-- Xóa Service -->
                                            <form action="<?= $baseUrl ?>/admin/services/<?= $srv['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ con này?');">
                                                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Xóa dịch vụ">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: THÊM DANH MỤC MỚI -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/admin/categories/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-folder-plus me-2"></i>Thêm Danh Mục Sự Cố Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Hạ tầng Mạng, Phần mềm ERP, Thiết bị máy trạm..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mã định danh (Code)</label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="Để trống hệ thống sẽ tự sinh: CAT_NETWORK...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nhóm IT mặc định tiếp nhận</label>
                        <select name="default_group_id" class="form-select">
                            <option value="">-- Tự động phân công / Chưa gán --</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Mô tả danh mục</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Mô tả phạm vi các sự cố thuộc danh mục này..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Lưu Danh Mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: SỬA DANH MỤC -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editCategoryForm" method="POST" action="">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Danh Mục
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editCatName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mã định danh (Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="editCatCode" class="form-control font-monospace" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nhóm IT mặc định tiếp nhận</label>
                        <select name="default_group_id" id="editCatGroupId" class="form-select">
                            <option value="">-- Tự động phân công / Chưa gán --</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Mô tả danh mục</label>
                        <textarea name="description" id="editCatDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-save me-1"></i>Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: THÊM DỊCH VỤ CON -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/admin/services/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Thêm Dịch Vụ Con Mới (Service)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Thuộc danh mục cấp 1 <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục trực thuộc --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= htmlspecialchars($cat['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên dịch vụ con <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Mất kết nối WiFi, Cài đặt Outlook, Cấp tài khoản VPN..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mã dịch vụ (Code)</label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="Tự sinh nếu để trống...">
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Mô tả hướng dẫn cho người dùng</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Mô tả triệu chứng thường gặp khi chọn dịch vụ này..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Lưu Dịch Vụ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: SỬA DỊCH VỤ CON -->
<div class="modal fade" id="editServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editServiceForm" method="POST" action="">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Dịch Vụ Con
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Thuộc danh mục cấp 1 <span class="text-danger">*</span></label>
                        <select name="category_id" id="editSrvCatId" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên dịch vụ con <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editSrvName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mã dịch vụ (Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="editSrvCode" class="form-control font-monospace" required>
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Mô tả</label>
                        <textarea name="description" id="editSrvDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-save me-1"></i>Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditCategoryModal(cat) {
    document.getElementById('editCategoryForm').action = '<?= $baseUrl ?>/admin/categories/' + cat.id + '/update';
    document.getElementById('editCatName').value = cat.name;
    document.getElementById('editCatCode').value = cat.code;
    document.getElementById('editCatDesc').value = cat.description || '';
    document.getElementById('editCatGroupId').value = cat.default_group_id || '';
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

function openEditServiceModal(srv) {
    document.getElementById('editServiceForm').action = '<?= $baseUrl ?>/admin/services/' + srv.id + '/update';
    document.getElementById('editSrvCatId').value = srv.category_id;
    document.getElementById('editSrvName').value = srv.name;
    document.getElementById('editSrvCode').value = srv.code;
    document.getElementById('editSrvDesc').value = srv.description || '';
    new bootstrap.Modal(document.getElementById('editServiceModal')).show();
}
</script>
