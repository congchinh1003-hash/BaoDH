<?php
if (!class_exists(\App\Core\Response::class)) {
    require_once dirname(__DIR__, 2) . '/autoload.php';
    require_once dirname(__DIR__, 2) . '/Helpers.php';
}

use App\Core\Response;
use App\Core\Session;

// If accessed directly via browser instead of MVC router, redirect to the official route
if (php_sapi_name() !== 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    header('Location: ' . Response::baseUrl() . '/tickets/create');
    exit;
}

/**
 * @var array $categories
 * @var array $priorities
 */
$baseUrl = Response::baseUrl();
$categories = $categories ?? [];
$priorities = $priorities ?? [];
$userAssets = $userAssets ?? [];
$approverUsers = $approverUsers ?? [];
?>

<!-- BREADCRUMB -->
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb breadcrumb-sm mb-0" style="font-size:0.8rem;">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none text-muted"><i class="bi bi-house me-1"></i>Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/tickets" class="text-decoration-none text-muted">Tickets</a></li>
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Tạo mới</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">

        <!-- PAGE HERO -->
        <div class="page-hero mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3"
             style="background:#fff; border-left:4px solid #0052CC; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); padding:20px 24px;">
            <div class="d-flex align-items-center gap-3">
                <div style="background:#EAF0FB; border-radius:10px; width:48px; height:48px; display:flex; align-items:center; justify-content:center;">
                    <i class="bi bi-plus-circle-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1" style="color:#172B4D;">Tạo Yêu Cầu Hỗ Trợ Kỹ Thuật</h4>
                    <p class="text-muted small mb-0" style="color:#5E6C84;">Hệ thống tự động snapshot SLA và chỉ định nhóm IT phụ trách phù hợp</p>
                </div>
            </div>
            <a href="<?= $baseUrl ?>/tickets" class="btn btn-outline-secondary fw-semibold px-3">
                <i class="bi bi-arrow-left me-1"></i>Danh sách yêu cầu
            </a>
        </div>

        <!-- MAIN FORM CARD -->
        <div class="card border-0" style="border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.10);">
            <div class="card-body p-4 p-md-5">
                <form action="<?= $baseUrl ?>/tickets/create" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">

                    <!-- STEP 0: LOẠI TICKET ITIL -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle"
                                 style="width:28px;height:28px;background:#0052CC;font-size:0.85rem;flex-shrink:0;">0</div>
                            <h6 class="fw-bold mb-0 text-uppercase" style="color:#172B4D; letter-spacing:.05em;">
                                Phân Loại Yêu Cầu ITIL <span class="text-danger">*</span>
                            </h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="card p-3 h-100 border-2 cursor-pointer type-select-card"
                                       id="typeIncidentCard"
                                       style="border-radius:10px; transition:all .2s; border-color:#DFE1E6 !important;">
                                    <div class="d-flex align-items-start gap-3">
                                        <input class="form-check-input mt-1" type="radio" name="ticket_type"
                                               id="type_incident" value="INCIDENT"
                                               <?= ($prefillType ?? 'INCIDENT') === 'INCIDENT' ? 'checked' : '' ?>>
                                        <div>
                                            <div class="fw-bold text-danger d-flex align-items-center gap-2 mb-1">
                                                <div style="background:#FFEBE6;border-radius:8px;padding:6px 8px;display:inline-flex;">
                                                    <i class="bi bi-bug-fill" style="color:#DE350B;"></i>
                                                </div>
                                                Sự Cố Kỹ Thuật (Incident)
                                            </div>
                                            <div class="text-muted small">Báo cáo dịch vụ bị gián đoạn, lỗi ứng dụng, hỏng hóc thiết bị cần khắc phục.</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="card p-3 h-100 border-2 cursor-pointer type-select-card"
                                       id="typeRequestCard"
                                       style="border-radius:10px; transition:all .2s; border-color:#DFE1E6 !important;">
                                    <div class="d-flex align-items-start gap-3">
                                        <input class="form-check-input mt-1" type="radio" name="ticket_type"
                                               id="type_service_request" value="SERVICE_REQUEST"
                                               <?= ($prefillType ?? '') === 'SERVICE_REQUEST' ? 'checked' : '' ?>>
                                        <div>
                                            <div class="fw-bold text-primary d-flex align-items-center gap-2 mb-1">
                                                <div style="background:#EAF0FB;border-radius:8px;padding:6px 8px;display:inline-flex;">
                                                    <i class="bi bi-send-check-fill text-primary"></i>
                                                </div>
                                                Yêu Cầu Dịch Vụ (Service Request)
                                            </div>
                                            <div class="text-muted small">Yêu cầu cấp mới tài khoản, phần mềm, thiết bị hoặc quyền truy cập.</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Service Request Approver Field (Conditional) -->
                        <div id="approverSection" class="mt-3 pt-3 border-top <?= ($prefillType ?? '') === 'SERVICE_REQUEST' ? '' : 'd-none' ?>">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-dark small mb-0">
                                        <i class="bi bi-shield-check text-success me-1"></i>Người phê duyệt (Approver)
                                    </label>
                                    <div class="text-muted small" style="font-size:0.75rem;">Yêu cầu dịch vụ sẽ gửi tới quản lý để xét duyệt trước khi IT thực hiện.</div>
                                </div>
                                <div class="col-md-7">
                                    <select name="approver_id" class="form-select form-select-sm"
                                            style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;">
                                        <option value="">-- Tự động chuyển quản lý / Trưởng nhóm --</option>
                                        <?php if (!empty($approverUsers)): ?>
                                            <?php foreach ($approverUsers as $appr): ?>
                                                <option value="<?= $appr['id'] ?>"><?= htmlspecialchars($appr['full_name']) ?> (<?= htmlspecialchars($appr['role_names'] ?? $appr['role'] ?? 'Quản lý') ?>)</option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" style="border-color:#F4F5F7;">

                    <!-- STEP 1: TIÊU ĐỀ -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle"
                                 style="width:28px;height:28px;background:#0052CC;font-size:0.85rem;flex-shrink:0;">1</div>
                            <h6 class="fw-bold mb-0 text-uppercase" style="color:#172B4D; letter-spacing:.05em;">Tiêu đề sự cố</h6>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="title" class="form-label fw-bold text-dark mb-0 small">
                                Tiêu đề sự cố / yêu cầu <span class="text-danger">*</span>
                            </label>
                            <button type="button" id="btnAiTriage"
                                    class="btn btn-outline-primary btn-sm fw-semibold py-1 px-3"
                                    style="font-size:12px; border-radius:999px;"
                                    title="Tự động phân loại danh mục, mức độ ưu tiên và đề xuất bài viết tự sửa lỗi">
                                <i class="bi bi-stars me-1 text-warning"></i>✨ AI Phân Tích &amp; Gợi Ý
                            </button>
                        </div>
                        <input type="text" class="form-control form-control-lg"
                               style="border:1.5px solid #DFE1E6; border-radius:8px; background:#FAFBFC; font-size:1rem;"
                               id="title" name="title"
                               placeholder="Mô tả tóm tắt sự cố (ví dụ: Mất kết nối VPN không vào được hệ thống ERP)"
                               required autocomplete="off">

                        <!-- KB Live Suggestions -->
                        <div id="kbLiveSuggestions" class="card border mt-2 d-none"
                             style="background:#f8faff; border-color:#cce5ff !important; border-radius:8px;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold" style="font-size:13px;">
                                    <i class="bi bi-lightbulb-fill text-warning"></i>
                                    <span>Bài viết hướng dẫn có thể giúp bạn tự xử lý ngay:</span>
                                </div>
                                <div id="kbSuggestionList" class="list-group list-group-flush small"></div>
                            </div>
                        </div>

                        <!-- AI Triage Result Banner -->
                        <div id="aiTriageBanner" class="alert border-0 mt-3 d-none"
                             style="background:#E8F0FE; border-left:4px solid #0052CC !important; border-radius:8px;">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-robot fs-5 text-primary"></i>
                                <div class="w-100" style="font-size:13px;">
                                    <div class="fw-bold text-dark mb-1">Kết Quả Phân Tích Từ AI Copilot:</div>
                                    <div id="aiTriageReason" class="text-secondary mb-2"></div>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <span class="badge bg-primary rounded-pill" id="aiPredictedCategory">Danh mục</span>
                                        <span class="badge bg-dark rounded-pill" id="aiPredictedPriority">Mức độ</span>
                                        <span class="text-muted small ms-auto" id="aiConfidence"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: PHÂN LOẠI & DỊCH VỤ -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle"
                                 style="width:28px;height:28px;background:#0052CC;font-size:0.85rem;flex-shrink:0;">2</div>
                            <h6 class="fw-bold mb-0 text-uppercase" style="color:#172B4D; letter-spacing:.05em;">Phân loại &amp; Dịch vụ</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="ticket_category_select" class="form-label fw-bold text-dark small">
                                    Phân loại sự cố (Category) <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="ticket_category_select" name="category_id" required
                                        style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;">
                                    <option value="">-- Chọn danh mục sự cố --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= (!empty($prefillCategory) && (int)$prefillCategory === (int)$cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="ticket_service_select" class="form-label fw-bold text-dark small">Dịch vụ chi tiết (Service)</label>
                                <select class="form-select" id="ticket_service_select" name="service_id"
                                        style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;">
                                    <option value="">-- Vui lòng chọn danh mục trước --</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="asset_id" class="form-label fw-bold text-dark small">Thiết bị / Tài sản gặp sự cố (Tùy chọn)</label>
                                <select class="form-select" id="asset_id" name="asset_id"
                                        style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;">
                                    <option value="">-- Không liên quan đến thiết bị cụ thể --</option>
                                    <?php if (!empty($userAssets)): ?>
                                        <optgroup label="Thiết bị được cấp phát / Trong hệ thống">
                                            <?php
                                            $selectedAssetId = (int)($_GET['asset_id'] ?? 0);
                                            foreach ($userAssets as $ast):
                                            ?>
                                                <option value="<?= $ast['id'] ?>" <?= ($selectedAssetId === (int)$ast['id']) ? 'selected' : '' ?>>
                                                    [<?= htmlspecialchars($ast['asset_code']) ?>] <?= htmlspecialchars($ast['name']) ?> (SN: <?= htmlspecialchars($ast['serial_number'] ?: 'N/A') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text" style="font-size:0.75rem;">Chọn thiết bị gặp lỗi để KTV tra cứu nhanh lịch sử cấu hình và sửa chữa.</div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: ITIL IMPACT X URGENCY MATRIX -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle"
                                 style="width:28px;height:28px;background:#0052CC;font-size:0.85rem;flex-shrink:0;">3</div>
                            <h6 class="fw-bold mb-0 text-uppercase" style="color:#172B4D; letter-spacing:.05em;">Ma trận ITIL &amp; Mức ưu tiên SLA</h6>
                        </div>

                        <div class="p-4 rounded-3 border" style="background:#F4F5F7;">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <span class="small fw-semibold text-muted text-uppercase" style="letter-spacing:.05em;">
                                    <i class="bi bi-grid-3x3 me-1 text-primary"></i>Tác Động (Impact) &times; Khẩn Cấp (Urgency)
                                </span>
                                <div class="small fw-semibold" id="matrixCalculatedBadge">
                                    Mức ưu tiên tính toán: <span class="badge rounded-pill bg-primary">Bình thường (NORMAL)</span>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold" style="color:#5E6C84;">1. Mức độ tác động (Impact) <span class="text-danger">*</span></label>
                                    <select name="impact" id="impactSelect" class="form-select form-select-sm"
                                            style="border:1.5px solid #DFE1E6; border-radius:6px; background:#fff;">
                                        <option value="LOW">Thấp (LOW) - Ảnh hưởng 1 cá nhân</option>
                                        <option value="MEDIUM" selected>Trung bình (MEDIUM) - Ảnh hưởng 1 nhóm / phòng ban</option>
                                        <option value="HIGH">Cao (HIGH) - Ảnh hưởng toàn công ty / hệ thống cốt lõi</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold" style="color:#5E6C84;">2. Mức độ khẩn cấp (Urgency) <span class="text-danger">*</span></label>
                                    <select name="urgency" id="urgencySelect" class="form-select form-select-sm"
                                            style="border:1.5px solid #DFE1E6; border-radius:6px; background:#fff;">
                                        <option value="LOW">Thấp (LOW) - Có thể làm việc bình thường, chờ được</option>
                                        <option value="MEDIUM" selected>Trung bình (MEDIUM) - Ảnh hưởng tiến độ, cần trong ngày</option>
                                        <option value="HIGH">Cao (HIGH) - Ngưng trệ công việc ngay lập tức</option>
                                    </select>
                                </div>
                            </div>

                            <label class="form-label fw-bold text-dark small mb-3">Cam kết thời hạn xử lý SLA tương ứng:</label>
                            <div class="row g-3">
                                <?php foreach ($priorities as $pri): ?>
                                    <div class="col-md-4">
                                        <label class="card p-3 h-100 border-2 cursor-pointer priority-option-card"
                                               id="priorityCard_<?= $pri['id'] ?>"
                                               style="border-radius:10px; transition:all .2s; border-color:#DFE1E6 !important;">
                                            <div class="d-flex align-items-start gap-3">
                                                <input class="form-check-input mt-1" type="radio"
                                                       name="priority_id"
                                                       id="priRadio_<?= $pri['id'] ?>"
                                                       data-code="<?= $pri['code'] ?>"
                                                       value="<?= $pri['id'] ?>"
                                                       <?= ($pri['code'] === 'NORMAL') ? 'checked' : '' ?> required>
                                                <div class="flex-1">
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($pri['name']) ?></div>
                                                    <div class="mt-1">
                                                        <span class="badge rounded-pill text-white"
                                                              style="background-color:<?= htmlspecialchars($pri['color']) ?>; font-size:0.75rem;">
                                                            <i class="bi bi-stopwatch-fill me-1"></i>SLA: <?= (int)$pri['resolution_time'] ?> <?= $pri['resolution_unit'] === 'HOUR' ? 'Giờ' : 'Ngày' ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-muted mt-2" style="font-size:0.72rem; line-height:1.4;">
                                                        <?php if ($pri['code'] === 'URGENT'): ?>
                                                            Impact Cao &times; Urgency Cao
                                                        <?php elseif ($pri['code'] === 'HIGH'): ?>
                                                            Impact Cao hoặc Urgency Cao
                                                        <?php else: ?>
                                                            Yêu cầu tiêu chuẩn, xử lý thông thường
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: VỊ TRÍ & LIÊN HỆ (AUTO-FILLED FROM USER PROFILE) -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle"
                                 style="width:28px;height:28px;background:#0052CC;font-size:0.85rem;flex-shrink:0;">4</div>
                            <h6 class="fw-bold mb-0 text-uppercase" style="color:#172B4D; letter-spacing:.05em;">Vị trí &amp; Liên hệ</h6>
                        </div>

                        <!-- User Profile Information Card -->
                        <?php if (!empty($currentUser)): ?>
                            <div class="p-3 bg-light rounded-2 border mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="jira-avatar-circle" style="background:#0052CC; width:36px; height:36px; font-size:14px;">
                                        <?= strtoupper(mb_substr($currentUser['full_name'] ?: 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">
                                            <?= htmlspecialchars($currentUser['full_name'] ?? '') ?> 
                                            <span class="badge bg-white text-secondary border ms-1 font-monospace"><?= htmlspecialchars($currentUser['job_title'] ?? 'Nhân viên') ?></span>
                                            <span class="badge bg-white text-info border ms-1"><i class="bi bi-building me-1"></i><?= htmlspecialchars($currentUser['department'] ?? 'Phòng ban') ?></span>
                                        </div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>Chỗ ngồi profile: <strong><?= htmlspecialchars($currentUser['seat_location'] ?? 'Chưa cập nhật') ?></strong>
                                            <span class="mx-1">&bull;</span>
                                            <i class="bi bi-telephone text-primary me-1"></i>SĐT: <strong><?= htmlspecialchars($currentUser['phone'] ?? 'Chưa cập nhật') ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <a href="/profile" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:12px;">
                                    <i class="bi bi-person-gear me-1"></i>Chỉnh sửa hồ sơ
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-bold text-dark small">
                                    Vị trí / Khu vực làm việc <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal ms-1" style="font-size:10px;">Tự điền từ Profile</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"
                                          style="background:#FAFBFC; border:1.5px solid #DFE1E6; border-right:0; border-radius:6px 0 0 6px;">
                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                    </span>
                                    <input type="text" class="form-control"
                                           style="border:1.5px solid #DFE1E6; border-left:0; border-radius:0 6px 6px 0; background:#FAFBFC;"
                                           id="location" name="location"
                                           value="<?= htmlspecialchars($currentUser['seat_location'] ?? '') ?>"
                                           placeholder="Tòa nhà, Tầng, Số phòng hoặc Vị trí bàn">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="contact_info" class="form-label fw-bold text-dark small">
                                    Số điện thoại nội bộ / Liên hệ <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal ms-1" style="font-size:10px;">Tự điền từ Profile</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"
                                          style="background:#FAFBFC; border:1.5px solid #DFE1E6; border-right:0; border-radius:6px 0 0 6px;">
                                        <i class="bi bi-telephone text-primary"></i>
                                    </span>
                                    <input type="text" class="form-control"
                                           style="border:1.5px solid #DFE1E6; border-left:0; border-radius:0 6px 6px 0; background:#FAFBFC;"
                                           id="contact_info" name="contact_info"
                                           value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>"
                                           placeholder="Số máy nhánh (Ext) hoặc SĐT di động">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 5: MÔ TẢ CHI TIẾT -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle"
                                 style="width:28px;height:28px;background:#0052CC;font-size:0.85rem;flex-shrink:0;">5</div>
                            <h6 class="fw-bold mb-0 text-uppercase" style="color:#172B4D; letter-spacing:.05em;">Mô tả chi tiết</h6>
                        </div>
                        <label for="description" class="form-label fw-bold text-dark small">
                            Mô tả chi tiết sự cố <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control"
                                  style="border:1.5px solid #DFE1E6; border-radius:8px; background:#FAFBFC;"
                                  id="description" name="description" rows="5"
                                  placeholder="Vui lòng mô tả chi tiết: Thao tác dẫn đến lỗi, thông báo lỗi trên màn hình, sự cố diễn ra từ thời điểm nào..."
                                  required></textarea>
                    </div>

                    <!-- STEP 6: ĐÍNH KÈM -->
                    <div class="mb-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white rounded-circle"
                                 style="width:28px;height:28px;background:#0052CC;font-size:0.85rem;flex-shrink:0;">6</div>
                            <h6 class="fw-bold mb-0 text-uppercase" style="color:#172B4D; letter-spacing:.05em;">Đính kèm tệp</h6>
                        </div>
                        <label for="attachment" class="form-label fw-bold text-dark small">Tệp tin / Ảnh chụp màn hình lỗi đính kèm</label>
                        <input class="form-control"
                               style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;"
                               type="file" id="attachment" name="attachment">
                        <div class="text-muted small mt-1" style="font-size:0.775rem;">
                            <i class="bi bi-info-circle me-1"></i>Chấp nhận hình ảnh (JPG, PNG), tài liệu (PDF, Word, Excel, Log, TXT) dung lượng tối đa 15MB.
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="d-flex justify-content-end gap-3 pt-4" style="border-top:1.5px solid #DFE1E6;">
                        <a href="<?= $baseUrl ?>/tickets" class="btn btn-outline-secondary px-5 fw-semibold py-2">
                            <i class="bi bi-x-lg me-1"></i>Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary fw-bold px-5 py-2 shadow"
                                style="font-size:1rem; border-radius:8px;">
                            <i class="bi bi-send-fill me-2"></i>Gửi Yêu Cầu Hỗ Trợ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const descInput  = document.getElementById('description');
    const btnAi      = document.getElementById('btnAiTriage');
    const kbBox      = document.getElementById('kbLiveSuggestions');
    const kbList     = document.getElementById('kbSuggestionList');
    const aiBanner   = document.getElementById('aiTriageBanner');
    const aiReason   = document.getElementById('aiTriageReason');
    const aiCat      = document.getElementById('aiPredictedCategory');
    const aiPri      = document.getElementById('aiPredictedPriority');
    const aiConf     = document.getElementById('aiConfidence');

    let searchTimer = null;

    // Live search KB on title input
    titleInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimer);
        if (query.length < 3) { kbBox.classList.add('d-none'); return; }

        searchTimer = setTimeout(() => {
            fetch(`/api/kb/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        kbList.innerHTML = data.data.map(item => `
                            <a href="/kb/${item.id}" target="_blank" class="list-group-item list-group-item-action py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                    <span class="fw-semibold text-dark">${item.title}</span>
                                    <small class="text-muted d-block ps-4">${item.snippet || ''}</small>
                                </div>
                                <span class="badge bg-light text-primary border">Xem hướng dẫn <i class="bi bi-box-arrow-up-right ms-1"></i></span>
                            </a>
                        `).join('');
                        kbBox.classList.remove('d-none');
                    } else {
                        kbBox.classList.add('d-none');
                    }
                })
                .catch(() => kbBox.classList.add('d-none'));
        }, 300);
    });

    // AI Smart Triage button trigger
    btnAi.addEventListener('click', function() {
        const title       = titleInput.value.trim();
        const description = descInput.value.trim();

        if (!title && !description) {
            alert('Vui lòng nhập Tiêu đề sự cố hoặc Mô tả trước để AI phân tích!');
            titleInput.focus();
            return;
        }

        btnAi.disabled = true;
        btnAi.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Đang phân tích...';

        fetch('/api/ai/triage', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({ title, description })
        })
        .then(res => res.json())
        .then(data => {
            btnAi.disabled = false;
            btnAi.innerHTML = '<i class="bi bi-stars me-1 text-warning"></i>✨ AI Phân Tích & Gợi Ý';

            if (data.success) {
                aiBanner.classList.remove('d-none');
                aiReason.textContent = data.reasoning || 'Dựa trên từ khóa và ngữ cảnh mô tả của sự cố.';
                aiCat.textContent  = 'Chuyên mục: ' + (data.category?.name || 'Chung');
                aiPri.textContent  = 'Mức độ gợi ý: ' + (data.priority?.code || 'NORMAL');
                aiConf.textContent = 'Độ tin cậy: ' + (data.confidence || 80) + '%';

                if (data.category?.id) {
                    const catSelect = document.getElementById('ticket_category_select');
                    if (catSelect) { catSelect.value = data.category.id; catSelect.dispatchEvent(new Event('change')); }
                }
                if (data.priority?.code) {
                    document.querySelectorAll('input[name="priority_id"]').forEach(radio => {
                        const labelText = radio.closest('label')?.textContent || '';
                        if (labelText.toLowerCase().includes(data.priority.code.toLowerCase()) ||
                            (data.priority.code === 'URGENT' && labelText.includes('Khẩn cấp')) ||
                            (data.priority.code === 'HIGH'   && labelText.includes('Cao')) ||
                            (data.priority.code === 'NORMAL' && labelText.includes('Bình thường'))) {
                            radio.checked = true;
                        }
                    });
                }
                if (data.kb_suggestions && data.kb_suggestions.length > 0) {
                    kbList.innerHTML = data.kb_suggestions.map(item => `
                        <a href="/kb/${item.id}" target="_blank" class="list-group-item list-group-item-action py-2 px-3 d-flex justify-content-between align-items-center">
                            <div><i class="bi bi-lightbulb text-warning me-2"></i><span class="fw-semibold text-dark">${item.title}</span></div>
                            <span class="badge bg-primary-subtle text-primary border">Tự khắc phục ngay <i class="bi bi-box-arrow-up-right ms-1"></i></span>
                        </a>
                    `).join('');
                    kbBox.classList.remove('d-none');
                }
            } else {
                alert(data.message || 'Không thể phân tích, vui lòng thử lại.');
            }
        })
        .catch(err => {
            btnAi.disabled = false;
            btnAi.innerHTML = '<i class="bi bi-stars me-1 text-warning"></i>✨ AI Phân Tích & Gợi Ý';
            console.error('AI Triage Error:', err);
        });
    });

    // ITIL Type Selector
    const typeIncident       = document.getElementById('type_incident');
    const typeServiceRequest = document.getElementById('type_service_request');
    const approverSection    = document.getElementById('approverSection');

    function toggleTicketType() {
        if (typeServiceRequest && typeServiceRequest.checked) {
            approverSection?.classList.remove('d-none');
        } else {
            approverSection?.classList.add('d-none');
        }
    }
    typeIncident?.addEventListener('change', toggleTicketType);
    typeServiceRequest?.addEventListener('change', toggleTicketType);

    // Impact x Urgency Matrix
    const impactSelect  = document.getElementById('impactSelect');
    const urgencySelect = document.getElementById('urgencySelect');
    const matrixBadge   = document.getElementById('matrixCalculatedBadge');

    function calculateMatrixPriority() {
        if (!impactSelect || !urgencySelect || !matrixBadge) return;

        const impact  = impactSelect.value;
        const urgency = urgencySelect.value;
        let priorityCode = 'NORMAL';
        let badgeHtml    = '<span class="badge rounded-pill bg-primary">Bình thường (NORMAL)</span>';

        if (impact === 'HIGH' && urgency === 'HIGH') {
            priorityCode = 'URGENT';
            badgeHtml = '<span class="badge rounded-pill bg-danger">Khẩn cấp (URGENT)</span>';
        } else if ((impact === 'HIGH' && urgency === 'MEDIUM') ||
                   (impact === 'MEDIUM' && urgency === 'HIGH') ||
                   (impact === 'HIGH' && urgency === 'LOW')) {
            priorityCode = 'HIGH';
            badgeHtml = '<span class="badge rounded-pill" style="background:#FF991F; color:#fff;">Ưu tiên (HIGH)</span>';
        }

        matrixBadge.innerHTML = 'Mức ưu tiên tính toán: ' + badgeHtml;

        document.querySelectorAll('input[name="priority_id"]').forEach(r => {
            if (r.getAttribute('data-code') === priorityCode) r.checked = true;
        });
    }

    impactSelect?.addEventListener('change', calculateMatrixPriority);
    urgencySelect?.addEventListener('change', calculateMatrixPriority);

    // Prefill category
    const catSelect  = document.getElementById('ticket_category_select');
    const prefillSvc = '<?= $prefillService ?? '' ?>';
    if (catSelect && catSelect.value) {
        catSelect.dispatchEvent(new Event('change'));
        if (prefillSvc) {
            setTimeout(() => {
                const svcSelect = document.getElementById('ticket_service_select');
                if (svcSelect) svcSelect.value = prefillSvc;
            }, 500);
        }
    }
});
</script>
