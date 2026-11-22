<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Trang chủ</a></li>
                    <li class="breadcrumb-item">Cấu hình Quản trị</li>
                    <li class="breadcrumb-item active" aria-current="page">Sao Lưu &amp; Khôi Phục Dữ Liệu</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-database-fill-gear text-primary"></i>
                Trung Tâm Sao Lưu &amp; Khôi Phục Dữ Liệu (Disaster Recovery)
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Tạo bản chụp CSDL MySQL tức thì (Toàn bộ Schema và Dữ liệu bảng), tải về lưu trữ an toàn hoặc khôi phục dữ liệu ngay khi hệ thống gặp sự cố.
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                <i class="bi bi-cloud-arrow-up-fill"></i> Tạo Bản Sao Lưu Ngay
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary">
                        <i class="bi bi-hdd-stack-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Tổng số bản sao lưu</div>
                        <h4 class="fw-bold text-dark mb-0"><?= $count ?> <small class="text-muted fs-6 fw-normal">snapshot</small></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success">
                        <i class="bi bi-pie-chart-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Dung lượng lưu trữ</div>
                        <h4 class="fw-bold text-dark mb-0"><?= $totalSize ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info-subtle text-info">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Trạng thái phòng ngừa</div>
                        <h5 class="fw-bold text-success mb-0">Sẵn sàng khôi phục</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backups List -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-secondary"></i>
                Danh Sách Bản Sao Lưu Hệ Thống (Snapshots)
            </h6>
            <span class="badge bg-light text-muted border">Tự động dọn dẹp giữ 20 bản gần nhất</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#F4F5F7; border-bottom: 2px solid #DFE1E6;">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase fw-bold" style="font-size:0.75rem;">Tên Tệp Sao Lưu (.sql)</th>
                        <th class="py-3 text-secondary text-uppercase fw-bold" style="font-size:0.75rem;">Dung Lượng</th>
                        <th class="py-3 text-secondary text-uppercase fw-bold" style="font-size:0.75rem;">Thời Điểm Sao Lưu</th>
                        <th class="py-3 text-secondary text-uppercase fw-bold text-center" style="font-size:0.75rem;">Trạng Thái</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase fw-bold text-end" style="font-size:0.75rem;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($backups)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="bi bi-database-slash fs-1 opacity-50"></i></div>
                                <div class="fw-bold">Chưa có bản sao lưu nào trên máy chủ.</div>
                                <div class="small">Nhấn nút "Tạo Bản Sao Lưu Ngay" ở trên để tạo bản sao lưu đầu tiên.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($backups as $index => $b): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-code-fill text-primary fs-5"></i>
                                        <div>
                                            <span class="fw-bold text-dark font-monospace" style="font-size:0.875rem;">
                                                <?= htmlspecialchars($b['filename']) ?>
                                            </span>
                                            <?php if ($index === 0): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-2" style="font-size:0.68rem;">Mới nhất</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border font-monospace"><?= $b['size_human'] ?></span>
                                </td>
                                <td class="py-3 text-muted small">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y H:i:s', $b['timestamp']) ?>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>Nguyên vẹn
                                    </span>
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/backups/download/<?= urlencode($b['filename']) ?>" class="btn btn-outline-secondary" title="Tải file .sql về máy tính">
                                            <i class="bi bi-download"></i> Tải về
                                        </a>
                                        <button type="button" class="btn btn-outline-warning text-dark fw-bold" title="Khôi phục lại dữ liệu" onclick="confirmRestore('<?= htmlspecialchars($b['filename']) ?>')">
                                            <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" title="Xóa bản sao lưu" onclick="confirmDelete('<?= htmlspecialchars($b['filename']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Security & Disaster Recovery Guide -->
    <div class="card border-0 shadow-sm rounded-3 p-4 bg-light">
        <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-shield-exclamation text-warning"></i>
            Chính Sách &amp; Hướng Dẫn Phòng Chống Sự Cố (Disaster Recovery Policy)
        </h6>
        <div class="row g-3 text-muted small">
            <div class="col-md-6">
                <div class="p-3 bg-white rounded border">
                    <strong class="text-dark d-block mb-1"><i class="bi bi-arrow-repeat text-primary me-1"></i> Nguyên tắc Khôi phục (Restore):</strong>
                    Khi thực hiện lệnh khôi phục, hệ thống sẽ tạm thời tắt ràng buộc khóa ngoại (<code>FOREIGN_KEY_CHECKS=0</code>), drop và tái thiết cấu trúc tất cả bảng trong bản sao lưu, sau đó nạp lại toàn bộ dữ liệu. Đảm bảo toàn bộ người dùng đã hoàn thành các giao dịch trước khi restore.
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-white rounded border">
                    <strong class="text-dark d-block mb-1"><i class="bi bi-hdd-network text-success me-1"></i> Lưu trữ dự phòng ngoại vi:</strong>
                    Khuyến nghị IT Manager định kỳ tải về các bản snapshot quan trọng và lưu trên ổ đĩa mạng NAS hoặc dịch vụ lưu trữ đám mây tách biệt để dự phòng thảm họa phần cứng.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo Backup Mới -->
<div class="modal fade" id="createBackupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/admin/backups/create" method="POST" class="modal-content rounded-3 border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-up-fill text-primary"></i> Tạo Bản Sao Lưu CSDL Mới
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-dark">Ghi chú hoặc nhãn nhận diện (Tùy chọn):</label>
                    <input type="text" name="note" class="form-control form-control-sm" placeholder="Ví dụ: truoc_khi_nang_cap_he_thong, cuoi_thang...">
                    <small class="text-muted" style="font-size:0.75rem;">Nhãn sẽ được nối vào tên tệp sao lưu để dễ nhận biết.</small>
                </div>
                <div class="alert alert-info py-2 px-3 small mb-0 rounded-2">
                    <i class="bi bi-info-circle-fill me-1"></i> Hệ thống sẽ tự động xuất toàn bộ schema và dữ liệu của tất cả các bảng vào tệp SQL.
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
                    <i class="bi bi-check2"></i> Bắt đầu sao lưu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form ẩn Restore -->
<form id="restoreForm" action="/admin/backups/restore" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
    <input type="hidden" name="filename" id="restoreFilename">
</form>

<!-- Form ẩn Delete -->
<form id="deleteForm" action="/admin/backups/delete" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
    <input type="hidden" name="filename" id="deleteFilename">
</form>

<script>
function confirmRestore(filename) {
    if (confirm('CẢNH BÁO QUAN TRỌNG!\n\nBạn sắp khôi phục cơ sở dữ liệu từ file:\n' + filename + '\n\nToàn bộ dữ liệu hiện tại của hệ thống sẽ bị ghi đè hoàn toàn bằng dữ liệu của bản sao lưu này.\n\nBạn có chắc chắn 100% muốn thực hiện việc này không?')) {
        document.getElementById('restoreFilename').value = filename;
        document.getElementById('restoreForm').submit();
    }
}

function confirmDelete(filename) {
    if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bản sao lưu "' + filename + '"?')) {
        document.getElementById('deleteFilename').value = filename;
        document.getElementById('deleteForm').submit();
    }
}
</script>

