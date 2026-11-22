<?php
$categories = $categories ?? [];
?>
<div class="page-hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-success text-white p-3" style="width: 48px; height: 48px;">
                <i class="bi bi-pencil-square fs-4"></i>
            </div>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 13px;">
                        <li class="breadcrumb-item"><a href="/kb" class="text-decoration-none">Knowledge Base</a></li>
                        <li class="breadcrumb-item"><a href="/kb/manage" class="text-decoration-none">Quản trị</a></li>
                        <li class="breadcrumb-item active fw-semibold" aria-current="page">Soạn thảo bài viết</li>
                    </ol>
                </nav>
                <h4 class="fw-bold text-dark mb-0">Soạn Thảo Bài Hướng Dẫn Kỹ Thuật (SOP / KB)</h4>
                <p class="text-muted small mb-0">Xuất bản quy trình chuẩn hoặc hướng dẫn tự khắc phục sự cố cho người dùng cuối.</p>
            </div>
        </div>
        <div>
            <a href="/kb/manage" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-x-circle"></i> Hủy Bỏ
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <span class="step-badge bg-primary">1</span>
                <h6 class="m-0 fw-bold text-dark">Nội Dung Bài Viết Hướng Dẫn</h6>
            </div>
            <div class="card-body p-4">
                <form action="/kb/store" method="POST" id="kbCreateForm">
                    <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">

                    <div class="mb-3">
                        <label class="form-label font-weight-bold text-gray-700">Tiêu Đề Bài Viết <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Ví dụ: Hướng Dẫn Khắc Phục Lỗi Mất Kết Nối VPN Công Ty" required>
                        <small class="form-text text-muted">Tiêu đề nên ngắn gọn, chứa từ khóa người dùng thường tìm kiếm.</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold text-gray-700">Chuyên Mục Hướng Dẫn <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn chuyên mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold text-gray-700">Trạng Thái Xuất Bản <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="PUBLISHED" selected>Xuất bản ngay (Public)</option>
                                <option value="DRAFT">Bản nháp (Chỉ IT thấy)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-weight-bold text-gray-700">Nội Dung Chi Tiết (Hỗ trợ Markdown & HTML cơ bản) <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control font-monospace" rows="14" placeholder="Nhập các bước thực hiện chi tiết:
1. Mô tả triệu chứng sự cố...
2. Nguyên nhân thường gặp...
3. Các bước xử lý chi tiết từng bước:
   - Bước 1: Mở Run (Win + R) và gõ...
   - Bước 2: Khởi động lại dịch vụ...
4. Lưu ý hoặc liên hệ IT khi không tự khắc phục được..." required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="/kb/manage" class="btn btn-secondary">Đóng</a>
                        <button type="submit" class="btn btn-primary font-weight-semibold px-4">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Lưu & Xuất Bản Bài Viết
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card jira-card shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="m-0 font-weight-bold text-secondary">
                    <i class="bi bi-lightbulb me-1 text-warning"></i> Tiêu Chuẩn Soạn Thảo (ITIL / Jira)
                </h6>
            </div>
            <div class="card-body small text-secondary">
                <ul class="ps-3 mb-3" style="line-height: 1.7;">
                    <li><strong>Ngôn từ dễ hiểu:</strong> Tránh các thuật ngữ quá sâu không cần thiết đối với End-User.</li>
                    <li><strong>Các bước đánh số:</strong> Sử dụng định dạng 1, 2, 3 rõ ràng để người dùng thao tác tuần tự.</li>
                    <li><strong>Hình ảnh minh họa:</strong> Có thể chèn đường dẫn hình ảnh hoặc ảnh chụp màn hình phím tắt.</li>
                    <li><strong>Từ khóa gợi ý:</strong> Đưa vào các lỗi phổ biến (ví dụ: "Error 800", "Lỗi font tiếng Việt", "Kẹt giấy") để hệ thống gợi ý tìm kiếm chính xác.</li>
                </ul>
                <div class="p-3 bg-light rounded border">
                    <span class="text-muted d-block mb-1 font-weight-bold">Tự Động Liên Kết AI:</span>
                    <p class="mb-0 text-muted" style="font-size: 12px;">
                        Bài viết đã xuất bản sẽ được thuật toán Smart Triage của hệ thống tự động quét và đề xuất cho nhân viên khi họ mở phiếu yêu cầu có nội dung tương đồng!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

