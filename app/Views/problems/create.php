<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $categories
 * @var array $priorities
 * @var array $staffUsers
 * @var ?array $prefillTicket
 */
$baseUrl = Response::baseUrl();
$prefillTicket = $prefillTicket ?? null;
$staffUsers = $staffUsers ?? [];
$categories = $categories ?? [];
$priorities = $priorities ?? [];
$csrfToken = Session::csrfToken();
?>

<style>
.problem-hero { background:#fff; border-left:4px solid #DE350B; border-radius:10px; padding:20px 24px; box-shadow:0 1px 4px rgba(9,30,66,0.12); border:1px solid #DFE1E6; }
.step-badge { display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:#DE350B; color:#fff; font-size:11px; font-weight:700; flex-shrink:0; }
.itil-card { background:#fff; border:1px solid #DFE1E6; border-radius:10px; box-shadow:0 1px 4px rgba(9,30,66,0.12); }
.itil-tip-card { background:#FFF8F6; border:1px solid #FFBDAD; border-radius:8px; padding:14px 16px; }
.itil-tip-card .tip-icon { width:32px; height:32px; border-radius:8px; background:#DE350B; display:flex; align-items:center; justify-content:center; color:#fff; font-size:14px; flex-shrink:0; margin-bottom:10px; }
.btn-submit-problem { background:#DE350B; border:none; color:#fff; padding:12px 20px; font-weight:600; font-size:0.95rem; border-radius:8px; width:100%; display:flex; align-items:center; justify-content:center; gap:8px; transition:background 0.15s; }
.btn-submit-problem:hover { background:#BF2600; color:#fff; }
.form-control:focus, .form-select:focus { border-color:#0052CC; box-shadow:0 0 0 2px rgba(0,82,204,0.15); }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Back -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/problems" class="text-decoration-none text-muted">Problem Management</a></li>
                <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Khai Báo Sự Cố Gốc (RCA)</li>
            </ol>
        </nav>
        <a href="<?= $baseUrl ?>/problems" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i>Danh Sách Problem
        </a>
    </div>

    <!-- Page Hero -->
    <div class="problem-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:10px;background:#DE350B;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-exclamation-octagon-fill text-white fs-4"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge fw-semibold px-2 py-1" style="background:#FFEBE6;color:#DE350B;font-size:11px;">ITIL V4 · Problem Management</span>
                        <span class="badge fw-semibold px-2 py-1" style="background:#F4F5F7;color:#5E6C84;font-size:11px;">RCA &amp; Workaround</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 mb-0">Khai Báo &amp; Điều Tra Sự Cố Gốc (Problem / RCA)</h5>
                    <p class="text-muted small mb-0">Gom nhóm các lỗi lặp lại, điều tra nguyên nhân cốt lõi và tìm Workaround để ngăn ngừa tái diễn.</p>
                </div>
            </div>
            <div class="d-none d-md-flex align-items-center gap-1 small text-muted">
                <span class="badge" style="background:#FFEBE6;color:#DE350B;">OPEN</span>
                <i class="bi bi-arrow-right" style="color:#DFE1E6;"></i>
                <span class="badge" style="background:#FFF0B3;color:#172B4D;">RCA</span>
                <i class="bi bi-arrow-right" style="color:#DFE1E6;"></i>
                <span class="badge" style="background:#E3FCEF;color:#006644;">KNOWN ERROR</span>
                <i class="bi bi-arrow-right" style="color:#DFE1E6;"></i>
                <span class="badge" style="background:#F4F5F7;color:#5E6C84;">CLOSED</span>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <form action="<?= $baseUrl ?>/problems/store" method="POST" id="problemCreateForm">
        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <?php if (!empty($prefillTicket)): ?>
            <input type="hidden" name="linked_ticket_id" value="<?= (int)$prefillTicket['id'] ?>">
            <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background:#FFEBE6;border-left:4px solid #DE350B;">
                <i class="bi bi-link-45deg fs-3 text-danger flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark mb-0 small">Liên Kết Tự Động Từ Sự Cố Nguồn:</div>
                    <div class="text-secondary small">
                        Đang tạo Problem từ ticket
                        <a href="<?= $baseUrl ?>/tickets/<?= $prefillTicket['id'] ?>" target="_blank" class="fw-bold text-danger text-decoration-underline">
                            #<?= htmlspecialchars($prefillTicket['ticket_code']) ?>
                        </a>: "<?= htmlspecialchars($prefillTicket['title']) ?>".
                        Ticket này sẽ tự động liên kết vào danh mục các sự cố liên quan.
                    </div>
                </div>
                <span class="badge text-white px-3 py-2 rounded-pill flex-shrink-0" style="background:#DE350B;">Linked #<?= $prefillTicket['id'] ?></span>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- KHỐI 1: NHẬN DIỆN -->
                <div class="itil-card mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2" style="border-radius:10px 10px 0 0;">
                        <span class="step-badge">1</span>
                        <h6 class="fw-bold text-dark mb-0">Nhận Diện Sự Cố Gốc</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                Tiêu đề Problem <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control form-control-lg fs-6" required
                                   placeholder="Ví dụ: Lỗi treo máy chủ ứng dụng do tràn bộ nhớ (Memory Leak) vào khung giờ cao điểm..."
                                   value="<?= htmlspecialchars($prefillTicket ? 'Problem: ' . $prefillTicket['title'] : '') ?>">
                            <div class="form-text small">Tóm tắt ngắn gọn hiện tượng lỗi tái diễn hoặc rủi ro hệ thống.</div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-dark">
                                Mô tả triệu chứng, tần suất &amp; phạm vi ảnh hưởng <span class="text-danger">*</span>
                            </label>
                            <textarea name="description" class="form-control" rows="6" required
                                      placeholder="Mô tả cụ thể:&#10;- Triệu chứng ghi nhận từ người dùng hoặc hệ thống giám sát&#10;- Tần suất xảy ra (hàng ngày, khi tải cao, ngẫu nhiên...)&#10;- Dịch vụ/Server/Database bị ảnh hưởng&#10;- Thông điệp log lỗi chính..."><?= htmlspecialchars($prefillTicket['description'] ?? '') ?></textarea>
                            <div class="form-text small">Cung cấp càng nhiều dữ liệu log/thông số hệ thống càng giúp điều tra RCA nhanh hơn.</div>
                        </div>
                    </div>
                </div>

                <!-- KHỐI 2: ITIL TIPS -->
                <div class="itil-card mb-4" style="background:#F4F5F7;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                            <h6 class="fw-bold text-dark mb-0">Nguyên Tắc ITIL Problem Management</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="itil-tip-card h-100">
                                    <div class="tip-icon"><i class="bi bi-bandaid-fill"></i></div>
                                    <div class="fw-bold text-dark small mb-1">Workaround</div>
                                    <div class="text-muted" style="font-size:12px;">Giải pháp tạm giúp người dùng tiếp tục công việc trong khi chờ bản vá chính thức.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="itil-tip-card h-100">
                                    <div class="tip-icon" style="background:#0052CC;"><i class="bi bi-search"></i></div>
                                    <div class="fw-bold text-dark small mb-1">Root Cause (RCA)</div>
                                    <div class="text-muted" style="font-size:12px;">Phân tích 5 Whys, dump log để tìm căn nguyên gốc rễ gây ra sự cố lặp lại.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="itil-tip-card h-100">
                                    <div class="tip-icon" style="background:#00875A;"><i class="bi bi-arrow-repeat"></i></div>
                                    <div class="fw-bold text-dark small mb-1">RFC / Permanent Fix</div>
                                    <div class="text-muted" style="font-size:12px;">Đề xuất thay đổi (RFC) và triển khai giải pháp vĩnh viễn để ngăn tái diễn.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- KHỐI 3: PHÂN LOẠI -->
                <div class="itil-card mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2" style="border-radius:10px 10px 0 0;">
                        <span class="step-badge">2</span>
                        <h6 class="fw-bold text-dark mb-0">Phân Loại &amp; Mức Độ</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">
                                Danh mục hệ thống <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục liên quan --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (!empty($prefillTicket) && $prefillTicket['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark">
                                Mức độ ưu tiên <span class="text-danger">*</span>
                            </label>
                            <select name="priority_id" class="form-select" required>
                                <?php foreach ($priorities as $pri): ?>
                                    <option value="<?= $pri['id'] ?>" <?= (!empty($prefillTicket) && $prefillTicket['priority_id'] == $pri['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pri['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Ưu tiên cao hơn với sự cố gián đoạn dịch vụ quan trọng.</div>
                        </div>
                    </div>
                </div>

                <!-- KHỐI 4: PHÂN CÔNG -->
                <div class="itil-card mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2" style="border-radius:10px 10px 0 0;">
                        <span class="step-badge">3</span>
                        <h6 class="fw-bold text-dark mb-0">Phân Công Điều Tra</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Chuyên viên phụ trách RCA</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Chưa chỉ định (Để ngỏ) --</option>
                                <?php foreach ($staffUsers as $st): ?>
                                    <option value="<?= $st['id'] ?>" <?= Auth::id() == $st['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['full_name']) ?> (<?= htmlspecialchars($st['role_names'] ?? $st['role'] ?? 'Kỹ thuật viên') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Mặc định chọn tài khoản hiện tại nếu là chuyên viên kỹ thuật.</div>
                        </div>
                        <div class="p-3 rounded-3" style="background:#F4F5F7;border:1px solid #DFE1E6;">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-person-check-fill text-success"></i>
                                <span class="text-dark fw-bold small">Người tạo yêu cầu:</span>
                            </div>
                            <div class="text-secondary small">
                                <?= htmlspecialchars(Auth::user()['full_name'] ?? 'Admin / Staff') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="itil-card">
                    <div class="card-body p-4">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn-submit-problem">
                                <i class="bi bi-check2-circle fs-5"></i>
                                <span>Khởi Tạo Problem &amp; Bắt Đầu RCA</span>
                            </button>
                            <a href="<?= $baseUrl ?>/problems" class="btn btn-light border py-2 text-secondary text-center">
                                Hủy bỏ &amp; Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
