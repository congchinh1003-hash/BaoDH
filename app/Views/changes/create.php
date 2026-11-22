<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $staffUsers
 * @var ?array $prefillTicket
 */
$baseUrl = Response::baseUrl();
$prefillTicket = $prefillTicket ?? null;
$staffUsers = $staffUsers ?? [];
$csrfToken = Session::csrfToken();
?>

<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Back -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/changes" class="text-decoration-none">Change & Release</a></li>
                <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Tạo Yêu Cầu Thay Đổi (RFC)</li>
            </ol>
        </nav>
        <a href="<?= $baseUrl ?>/changes" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i>Danh Sách RFC
        </a>
    </div>

    <!-- Header Hero Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-left: 5px solid #3b82f6 !important;">
        <div class="card-body p-4 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">
                            <i class="bi bi-shield-lock-fill me-1"></i> ITIL V4 Change Enablement
                        </span>
                        <span class="badge bg-dark border border-secondary text-secondary-emphasis px-2 py-1">
                            RFC Workflow
                        </span>
                    </div>
                    <h3 class="fw-bold text-white mb-1">Khởi Tạo Yêu Cầu Thay Đổi Hệ Thống (RFC)</h3>
                    <p class="text-white-50 small mb-0" style="max-width: 750px;">
                        Kiểm soát rủi ro và đảm bảo tính liên tục của hệ thống CNTT. Mọi thay đổi cấu hình, hạ tầng, cơ sở dữ liệu và mã nguồn ứng dụng đều cần lập kế hoạch triển khai, kiểm thử và phương án hoàn nguyên (Rollback).
                    </p>
                </div>
                <div class="d-none d-md-block text-end">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 text-center" style="min-width: 170px;">
                        <div class="text-info small fw-bold text-uppercase">Quy Trình Chuẩn</div>
                        <div class="fw-semibold text-white mt-1" style="font-size: 0.85rem;">DEV &rarr; TEST &rarr; UAT &rarr; PROD</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <form action="<?= $baseUrl ?>/changes/store" method="POST" id="rfcCreateForm">
        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <?php if (!empty($prefillTicket)): ?>
            <input type="hidden" name="linked_ticket_id" value="<?= (int)$prefillTicket['id'] ?>">
            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center gap-3 mb-4 rounded-3 p-3" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important;">
                <i class="bi bi-link-45deg fs-3 text-primary"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark mb-0">Liên Kết Với Sự Cố Nguồn:</div>
                    <div class="text-secondary small">
                        Yêu cầu thay đổi này được khởi tạo để khắc phục ticket
                        <a href="<?= $baseUrl ?>/tickets/<?= $prefillTicket['id'] ?>" target="_blank" class="fw-bold text-primary text-decoration-underline">
                            #<?= htmlspecialchars($prefillTicket['ticket_code']) ?>
                        </a>: "<?= htmlspecialchars($prefillTicket['title']) ?>"
                    </div>
                </div>
                <span class="badge bg-primary text-white px-3 py-2 rounded-pill">Linked Ticket #<?= $prefillTicket['id'] ?></span>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Form Details -->
            <div class="col-lg-8">
                <!-- KHỐI 1: THÔNG TIN TIÊU ĐỀ & MỤC ĐÍCH -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                        <div class="rounded-2 p-2 bg-primary-subtle text-primary me-2">
                            <i class="bi bi-card-text fs-6"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0">1. Thông Tin Chung Về Thay Đổi</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                Tiêu đề thay đổi (RFC Title) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control form-control-lg fs-6" required 
                                   placeholder="Ví dụ: Nâng cấp cụm Database MySQL 8.0, Triển khai cổng thanh toán VNPay v2..." 
                                   value="<?= htmlspecialchars($prefillTicket ? 'Change: ' . $prefillTicket['title'] : '') ?>">
                            <div class="form-text small">Tóm tắt ngắn gọn đối tượng hệ thống và hành động thay đổi chính.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                Lý do thay đổi & Lợi ích nghiệp vụ (Business Justification) <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason" class="form-control" rows="3" required 
                                      placeholder="Nêu rõ nguyên nhân phát sinh: Do vá lỗ hổng bảo mật, đáp ứng yêu cầu kinh doanh, hay mở rộng tải hệ thống..."></textarea>
                        </div>

                        <div>
                            <label class="form-label fw-semibold text-dark">
                                Phạm vi kỹ thuật chi tiết & Các thành phần CI ảnh hưởng <span class="text-danger">*</span>
                            </label>
                            <textarea name="description" class="form-control" rows="4" required 
                                      placeholder="Liệt kê chi tiết: Máy chủ nào, cơ sở dữ liệu nào, phiên bản package, port mạng, firewall rule và dịch vụ liên đới..."><?= htmlspecialchars($prefillTicket['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- KHỐI 2: KẾ HOẠCH TRIỂN KHAI, ROLLBACK & KIỂM THỬ -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 p-2 bg-success-subtle text-success me-2">
                                <i class="bi bi-shield-check fs-6"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0">2. Kế Hoạch Triển Khai & An Toàn Hệ Thống (Rollback)</h6>
                        </div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Bắt buộc có Rollback Plan
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <!-- Implementation Plan -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-play-circle text-primary me-1"></i>Kế hoạch triển khai tuần tự (Implementation Plan)</span>
                                <span class="text-muted small fw-normal">Quy trình từng bước</span>
                            </label>
                            <textarea name="implementation_plan" class="form-control font-monospace" rows="4" style="font-size: 0.85rem;"
                                      placeholder="Bước 1: Snapshot máy chủ VM lúc 23:00&#10;Bước 2: Chạy lệnh database migration&#10;Bước 3: Deploy package code mới qua CI/CD&#10;Bước 4: Restart application service"></textarea>
                        </div>

                        <!-- Rollback Plan -->
                        <div class="mb-4 p-3 rounded-3 bg-danger-subtle border border-danger-subtle">
                            <label class="form-label fw-bold text-danger d-flex align-items-center gap-1">
                                <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                <span>Kế hoạch hoàn nguyên khi gặp sự cố (Rollback / Contingency Plan) *</span>
                            </label>
                            <textarea name="rollback_plan" class="form-control font-monospace border-danger-subtle" rows="4" required style="font-size: 0.85rem;"
                                      placeholder="Ví dụ phương án khôi phục:&#10;- Điều kiện rollback: Quá trình release kéo dài quá 30 phút hoặc tỷ lệ lỗi 5xx vượt 1%&#10;- Thao tác rollback: Restore database snapshot đã sao lưu lúc 23:00 và trỏ DNS về cluster cũ..."></textarea>
                            <div class="text-danger small mt-2" style="font-size: 0.78rem;">
                                Theo tiêu chuẩn ITIL V4, hội đồng CAB sẽ từ chối phê duyệt bất kỳ RFC nào không có phương án quay lui an toàn.
                            </div>
                        </div>

                        <!-- Test Plan -->
                        <div>
                            <label class="form-label fw-semibold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-check2-square text-info me-1"></i>Kế hoạch kiểm thử & Tiêu chí nghiệm thu (Test Plan)</span>
                                <span class="text-muted small fw-normal">Acceptance Criteria</span>
                            </label>
                            <textarea name="test_plan" class="form-control" rows="3" 
                                      placeholder="Mô tả các kịch bản test sau khi release: Kiểm tra login SSO, tạo thử đơn hàng test, kiểm tra log hệ thống không phát sinh exception..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Classification, Risk, Stage & Schedule -->
            <div class="col-lg-4">
                <!-- PHÂN LOẠI THAY ĐỔI -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-sliders text-primary me-2"></i>Phân Loại & Mức Độ Rủi Ro
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <!-- Loại thay đổi -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Loại thay đổi (Change Type) <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                <label class="card p-2 border cursor-pointer bg-light change-type-card" id="cardType_NORMAL">
                                    <div class="d-flex align-items-center gap-2">
                                        <input class="form-check-input mt-0" type="radio" name="change_type" value="NORMAL" id="type_normal" checked>
                                        <div>
                                            <div class="fw-bold small text-dark">Thông Thường (NORMAL)</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Cần đánh giá rủi ro & hội đồng CAB duyệt</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="card p-2 border cursor-pointer bg-light change-type-card" id="cardType_STANDARD">
                                    <div class="d-flex align-items-center gap-2">
                                        <input class="form-check-input mt-0" type="radio" name="change_type" value="STANDARD" id="type_standard">
                                        <div>
                                            <div class="fw-bold small text-success">Tiêu Chuẩn (STANDARD)</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Đã chuẩn hóa, rủi ro thấp, phê duyệt trước</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="card p-2 border cursor-pointer bg-light change-type-card" id="cardType_EMERGENCY">
                                    <div class="d-flex align-items-center gap-2">
                                        <input class="form-check-input mt-0" type="radio" name="change_type" value="EMERGENCY" id="type_emergency">
                                        <div>
                                            <div class="fw-bold small text-danger">Khẩn Cấp (EMERGENCY)</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Xử lý sự cố Outage, ECAB phê duyệt nhanh</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Mức độ rủi ro -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Mức độ rủi ro (Risk Level) <span class="text-danger">*</span></label>
                            <select name="risk_level" id="riskLevelSelect" class="form-select" required>
                                <option value="LOW">🟢 Thấp (LOW) - Không gián đoạn dịch vụ</option>
                                <option value="MEDIUM" selected>🟡 Trung bình (MEDIUM) - Tác động cục bộ</option>
                                <option value="HIGH">🟠 Cao (HIGH) - Gián đoạn dịch vụ tạm thời</option>
                                <option value="CRITICAL">🔴 Rất Nghiêm Trọng (CRITICAL) - Ngừng toàn bộ hệ thống</option>
                            </select>
                        </div>

                        <!-- Môi trường triển khai ban đầu -->
                        <div>
                            <label class="form-label small fw-bold text-muted text-uppercase">Môi trường bắt đầu (Initial Stage) <span class="text-danger">*</span></label>
                            <select name="environment_stage" class="form-select" required>
                                <option value="DEV" selected>1. Phát triển (DEV - Development)</option>
                                <option value="TEST">2. Kiểm thử (TEST - Staging/QA)</option>
                                <option value="MIG_UAT">3. Chuyển dịch & UAT (MIG/UAT)</option>
                                <option value="RELEASE_PROD">4. Môi trường Trực Tiếp (PROD - Live)</option>
                            </select>
                            <div class="form-text small" style="font-size: 0.72rem;">RFC sẽ chuyển tuần tự qua các stage theo phê duyệt kiểm thử.</div>
                        </div>
                    </div>
                </div>

                <!-- LỊCH TRÌNH & PHÂN CÔNG -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-clock-history text-success me-2"></i>Lịch Trình & Người Chịu Trách Nhiệm
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Người chịu trách nhiệm triển khai (Owner)</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Chưa chỉ định --</option>
                                <?php foreach ($staffUsers as $st): ?>
                                    <option value="<?= (int)$st['id'] ?>" <?= (Auth::id() == $st['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['full_name']) ?> (<?= htmlspecialchars($st['role'] ?? 'IT Staff') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Thời gian bắt đầu (Maintenance Window)</label>
                            <input type="datetime-local" name="scheduled_start" class="form-control form-control-sm">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Thời gian kết thúc dự kiến</label>
                            <input type="datetime-local" name="scheduled_end" class="form-control form-control-sm">
                        </div>

                        <div class="p-3 bg-light rounded-2 border small text-muted">
                            <i class="bi bi-people-fill text-warning me-1"></i>
                            <strong>Hội đồng CAB:</strong> Sau khi tạo, RFC sẽ được gửi tới hội đồng đánh giá thay đổi để kiểm duyệt lịch trình và phương án rollback.
                        </div>
                    </div>
                </div>

                <!-- SUBMIT ACTIONS CARD -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">
                                <i class="bi bi-send-check-fill me-1"></i> Khởi Tạo Yêu Cầu Thay Đổi
                            </button>
                            <a href="<?= $baseUrl ?>/changes" class="btn btn-light border py-2 text-secondary">
                                Hủy bỏ thao tác
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Style active change type cards
    const typeRadios = document.querySelectorAll('input[name="change_type"]');
    function updateTypeCards() {
        typeRadios.forEach(radio => {
            const card = radio.closest('.change-type-card');
            if (card) {
                if (radio.checked) {
                    card.classList.add('border-primary', 'bg-primary-subtle');
                    card.classList.remove('bg-light');
                } else {
                    card.classList.remove('border-primary', 'bg-primary-subtle');
                    card.classList.add('bg-light');
                }
            }
        });
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', updateTypeCards);
    });
    updateTypeCards();

    // Form client-side validation
    const form = document.getElementById('rfcCreateForm');
    form.addEventListener('submit', function (e) {
        const title = form.querySelector('[name="title"]').value.trim();
        const reason = form.querySelector('[name="reason"]').value.trim();
        const desc = form.querySelector('[name="description"]').value.trim();
        const rollback = form.querySelector('[name="rollback_plan"]').value.trim();

        if (!title || !reason || !desc || !rollback) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ Tiêu đề, Lý do, Mô tả phạm vi và Kế hoạch hoàn nguyên (Rollback Plan)!');
            return false;
        }
    });
});
</script>
