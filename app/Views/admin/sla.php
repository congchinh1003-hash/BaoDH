<?php
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $slaConfigs
 * @var array $priorities
 */
$baseUrl = Response::baseUrl();
$priorities = $priorities ?? [];
$csrfToken = Session::csrfToken();
?>

<!-- PAGE HERO -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-clock-history fs-4 text-white"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Cấu Hình Mức Độ Ưu Tiên &amp; Cam Kết SLA</h4>
            <p class="mb-0 small" style="color:#5E6C84;">Thiết lập thời gian cam kết dịch vụ (SLA Snapshot) và các ngưỡng cảnh báo vi phạm</p>
        </div>
    </div>
    <button type="button" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSlaModal">
        <i class="bi bi-plus-circle me-1"></i>Thêm Cấu Hình SLA
    </button>
</div>

<!-- SLA TABLE -->
<div class="card mb-4" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center" style="border-bottom:1px solid #DFE1E6;border-radius:10px 10px 0 0;">
        <span class="fw-bold" style="color:#172B4D;">
            <i class="bi bi-clock-history me-2" style="color:#0052CC;"></i>Bảng Quy Định SLA Theo Từng Mức Độ Ưu Tiên
        </span>
        <span class="badge bg-light text-muted border">ITIL V4 Service Level</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
            <thead>
                <tr style="background:#F4F5F7;color:#5E6C84;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                    <th class="ps-4 py-3" style="width:160px;">Mức Độ Ưu Tiên</th>
                    <th class="py-3">Tên cấu hình quy chuẩn</th>
                    <th class="py-3 text-center" style="width:170px;">Thời gian cam kết (SLA)</th>
                    <th class="py-3 text-center" style="width:150px;">Cảnh báo (Warning)</th>
                    <th class="py-3 text-center" style="width:150px;">Nguy cấp (Critical)</th>
                    <th class="py-3 text-center" style="width:110px;">Trạng thái</th>
                    <th class="pe-4 py-3 text-end" style="width:100px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($slaConfigs)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted small">Chưa có cấu hình SLA nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($slaConfigs as $cfg): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded flex-shrink-0" style="width:4px;height:36px;background:<?= htmlspecialchars($cfg['priority_color'] ?? '#6c757d') ?>;"></div>
                                    <span class="badge py-2 px-3 fw-bold" style="background:<?= htmlspecialchars($cfg['priority_color'] ?? '#6c757d') ?>;color:#fff;font-size:.8rem;border-radius:6px;">
                                        <?= htmlspecialchars($cfg['priority_name']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="fw-semibold" style="color:#172B4D;"><?= htmlspecialchars($cfg['name']) ?></td>
                            <td class="text-center">
                                <span class="font-monospace fw-bold px-3 py-1 rounded-3" style="background:#EAF0FB;color:#0052CC;font-size:.9rem;border:1px solid #B3C7F5;">
                                    <?= (int)$cfg['resolution_time'] ?> <?= $cfg['resolution_unit'] === 'HOUR' ? 'Giờ' : 'Ngày' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold px-3 py-1 rounded-3 font-monospace" style="background:#FFFAE6;color:#FF991F;border:1px solid #FFE380;font-size:.82rem;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= (int)$cfg['warning_threshold_percent'] ?>% SLA
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold px-3 py-1 rounded-3 font-monospace" style="background:#FFEBE6;color:#DE350B;border:1px solid #FFBDAD;font-size:.82rem;">
                                    <i class="bi bi-fire me-1"></i><?= (int)$cfg['critical_threshold_percent'] ?>% SLA
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($cfg['is_active']): ?>
                                    <span class="badge px-2 py-1 fw-bold d-inline-flex align-items-center gap-1"
                                          style="background:#E3FCEF;color:#00875A;border:1px solid #ABF5D1;border-radius:20px;font-size:.75rem;">
                                        <i class="bi bi-check-circle-fill"></i>Bật
                                    </span>
                                <?php else: ?>
                                    <span class="badge px-2 py-1 fw-bold d-inline-flex align-items-center gap-1"
                                          style="background:#F4F5F7;color:#5E6C84;border:1px solid #DFE1E6;border-radius:20px;font-size:.75rem;">
                                        Tắt
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <!-- Nút Sửa SLA -->
                                    <button type="button" class="btn btn-outline-secondary btn-sm p-1 px-2"
                                            title="Chỉnh sửa SLA"
                                            onclick="openEditSlaModal(<?= htmlspecialchars(json_encode([
                                                'id'                         => $cfg['id'],
                                                'name'                       => $cfg['name'],
                                                'priority_name'              => $cfg['priority_name'],
                                                'resolution_time'            => $cfg['resolution_time'],
                                                'resolution_unit'            => $cfg['resolution_unit'],
                                                'warning_threshold_percent'  => $cfg['warning_threshold_percent'],
                                                'critical_threshold_percent' => $cfg['critical_threshold_percent'],
                                                'is_active'                  => $cfg['is_active']
                                            ]), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <!-- Nút Xóa SLA -->
                                    <form action="<?= $baseUrl ?>/admin/sla/<?= $cfg['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cấu hình SLA này?');">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Xóa SLA">
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

<!-- MODAL: THÊM CẤU HÌNH SLA -->
<div class="modal fade" id="addSlaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/admin/sla/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-clock-history me-2"></i>Thêm Cấu Hình SLA Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mức độ ưu tiên áp dụng <span class="text-danger">*</span></label>
                        <select name="priority_id" class="form-select" required>
                            <option value="">-- Chọn mức độ ưu tiên --</option>
                            <?php foreach ($priorities as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Level <?= $p['level'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên quy chuẩn cam kết <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: SLA Tiêu chuẩn 4 Giờ, SLA Khẩn cấp 2 Giờ..." required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-bold small">Thời gian xử lý <span class="text-danger">*</span></label>
                            <input type="number" name="resolution_time" class="form-control" min="1" value="4" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-bold small">Đơn vị</label>
                            <select name="resolution_unit" class="form-select">
                                <option value="HOUR" selected>Giờ</option>
                                <option value="DAY">Ngày</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Ngưỡng Cảnh báo (%)</label>
                            <input type="number" name="warning_threshold_percent" class="form-control" min="10" max="99" value="70">
                            <div class="form-text small">Ví dụ: 70% thời gian</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Ngưỡng Nguy cấp (%)</label>
                            <input type="number" name="critical_threshold_percent" class="form-control" min="20" max="100" value="90">
                            <div class="form-text small">Ví dụ: 90% thời gian</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Lưu Quy Định SLA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: SỬA CẤU HÌNH SLA -->
<div class="modal fade" id="editSlaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editSlaForm" method="POST" action="">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Quy Định SLA
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên quy chuẩn cam kết <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editSlaName" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-bold small">Thời gian xử lý <span class="text-danger">*</span></label>
                            <input type="number" name="resolution_time" id="editSlaTime" class="form-control" min="1" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-bold small">Đơn vị</label>
                            <select name="resolution_unit" id="editSlaUnit" class="form-select">
                                <option value="HOUR">Giờ</option>
                                <option value="DAY">Ngày</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Ngưỡng Cảnh báo (%)</label>
                            <input type="number" name="warning_threshold_percent" id="editSlaWarn" class="form-control" min="10" max="99">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Ngưỡng Nguy cấp (%)</label>
                            <input type="number" name="critical_threshold_percent" id="editSlaCrit" class="form-control" min="20" max="100">
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Trạng thái kích hoạt</label>
                        <select name="is_active" id="editSlaActive" class="form-select">
                            <option value="1">Kích hoạt áp dụng</option>
                            <option value="0">Tạm tắt</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-save me-1"></i>Cập Nhật SLA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditSlaModal(cfg) {
    document.getElementById('editSlaForm').action = '<?= $baseUrl ?>/admin/sla/' + cfg.id + '/update';
    document.getElementById('editSlaName').value = cfg.name;
    document.getElementById('editSlaTime').value = cfg.resolution_time;
    document.getElementById('editSlaUnit').value = cfg.resolution_unit;
    document.getElementById('editSlaWarn').value = cfg.warning_threshold_percent;
    document.getElementById('editSlaCrit').value = cfg.critical_threshold_percent;
    document.getElementById('editSlaActive').value = cfg.is_active;
    new bootstrap.Modal(document.getElementById('editSlaModal')).show();
}
</script>
