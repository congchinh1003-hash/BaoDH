<?php
/**
 * @var array $profileUser
 * @var array $roles
 * @var array $myAssets
 * @var array $myTickets
 * @var array $myIssues
 */
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
$fullName = $profileUser['full_name'] ?? '';
$username = $profileUser['username'] ?? '';
$email = $profileUser['email'] ?? '';
$jobTitle = $profileUser['job_title'] ?? '';
$department = $profileUser['department'] ?? '';
$seatLocation = $profileUser['seat_location'] ?? '';
$phone = $profileUser['phone'] ?? '';
?>

<div class="container-fluid py-3 px-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none text-secondary">Trang chủ</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Hồ sơ cá nhân</li>
        </ol>
    </nav>

    <!-- USER HERO HEADER CARD -->
    <div class="card border shadow-sm rounded-3 mb-4 bg-white overflow-hidden tf-profile-hero">
        <div style="height: 100px; background: linear-gradient(135deg, #0052CC 0%, #2684FF 50%, #00B8D9 100%);"></div>
        <div class="card-body px-4 pb-4 pt-0 position-relative">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3" style="margin-top: -50px;">
                <div class="d-flex align-items-end gap-3">
                    <div class="jira-avatar-circle shadow border border-3 border-white flex-shrink-0" style="width: 90px; height: 90px; font-size: 34px; background: linear-gradient(135deg, #0052CC, #6554C0); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        <?= strtoupper(mb_substr($fullName ?: ($username ?: 'U'), 0, 1)) ?>
                    </div>
                    <div class="mb-1">
                        <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                            <?= htmlspecialchars($fullName ?: $username) ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">Hoạt động</span>
                        </h4>
                        <div class="text-secondary small d-flex flex-wrap align-items-center gap-3">
                            <span><i class="bi bi-person-badge me-1 text-primary"></i><strong><?= htmlspecialchars($jobTitle ?: 'Chưa cập nhật chức vụ') ?></strong></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-building me-1 text-info"></i><?= htmlspecialchars($department ?: 'Chưa có phòng ban') ?></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-geo-alt-fill me-1 text-danger"></i><?= htmlspecialchars($seatLocation ?: 'Chưa đăng ký chỗ ngồi') ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-sm btn-primary fw-semibold px-3 py-2 shadow-sm" style="background: #0052CC;">
                        <i class="bi bi-plus-circle me-1"></i> Gửi Yêu Cầu Hỗ Trợ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN SECTION -->
    <div class="row g-4">
        <!-- LEFT: EDIT PROFILE & PASSWORD FORM -->
        <div class="col-lg-7">
            <div class="card border shadow-sm rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-bottom p-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-primary"></i> Chỉnh Sửa Thông Tin Cá Nhân
                    </h6>
                    <span class="small text-muted">Dữ liệu sẽ được tự động điền khi tạo ticket</span>
                </div>
                
                <form action="<?= $baseUrl ?>/profile" method="POST" class="card-body p-4">
                    <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">TÊN ĐĂNG NHẬP (USERNAME)</label>
                            <input type="text" class="form-control form-control-sm bg-light text-muted font-monospace" value="<?= htmlspecialchars($username) ?>" readonly>
                            <div class="form-text small">Tên tài khoản không thể thay đổi.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">EMAIL CÔNG TY</label>
                            <input type="email" class="form-control form-control-sm bg-light text-muted" value="<?= htmlspecialchars($email) ?>" readonly>
                            <div class="form-text small">Email liên hệ mặc định của hệ thống.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">HỌ VÀ TÊN <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control form-control-sm" value="<?= htmlspecialchars($fullName) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">SỐ ĐIỆN THOẠI NỘI BỘ / DI ĐỘNG</label>
                            <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($phone) ?>" placeholder="VD: 0901234567 hoặc Ext 104">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">PHÒNG BAN / BỘ PHẬN</label>
                            <input type="text" name="department" class="form-control form-control-sm" value="<?= htmlspecialchars($department) ?>" placeholder="VD: Phòng Kế Toán, Ban Giám Đốc, IT...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">CHỨC DANH / CHỨC VỤ</label>
                            <input type="text" name="job_title" class="form-control form-control-sm" value="<?= htmlspecialchars($jobTitle) ?>" placeholder="VD: Kế toán trưởng, Kỹ sư Mạng, Nhân viên...">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-secondary d-flex align-items-center justify-content-between">
                                <span>VỊ TRÍ CHỖ NGỒI (SEAT / OFFICE LOCATION)</span>
                                <span class="badge bg-light text-primary border fw-normal" style="font-size: 11px;">Tự điền khi báo lỗi</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-secondary"><i class="bi bi-geo-alt-fill text-danger"></i></span>
                                <input type="text" name="seat_location" class="form-control" value="<?= htmlspecialchars($seatLocation) ?>" placeholder="VD: Tầng 2 - Phòng KT-01 - Bàn KT-04">
                            </div>
                            <div class="form-text text-muted small">Kỹ thuật viên IT sẽ dựa vào vị trí này để đến hỗ trợ trực tiếp khi có sự cố tại bàn.</div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- PASSWORD SECTION (OPTIONAL) -->
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock text-warning"></i> Thay Đổi Mật Khẩu (Để trống nếu không đổi)
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" class="form-control form-control-sm" placeholder="••••••">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control form-control-sm" placeholder="Tối thiểu 6 ký tự">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-secondary">Xác nhận mật khẩu</label>
                            <input type="password" name="confirm_password" class="form-control form-control-sm" placeholder="Nhập lại mật khẩu mới">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary fw-semibold px-4 py-2" style="background: #0052CC; border-color: #0052CC;">
                            <i class="bi bi-check-lg me-1"></i> Lưu Thay Đổi Hồ Sơ
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT: ASSETS & TICKETS & TASKS CARDS -->
        <div class="col-lg-5">
            <!-- 1. MY ASSIGNED ASSETS -->
            <div class="card border shadow-sm rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-bottom p-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-laptop text-primary"></i> Thiết Bị Được Cấp Phát (<?= count($myAssets) ?>)
                    </h6>
                    <a href="<?= $baseUrl ?>/my-assets" class="small text-decoration-none">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($myAssets)): ?>
                        <div class="p-4 text-center text-muted small">
                            <i class="bi bi-pc-display fs-2 d-block mb-1 text-secondary opacity-50"></i>
                            Bạn chưa có thiết bị IT nào được cấp phát trực tiếp.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush small">
                            <?php foreach ($myAssets as $ast): ?>
                                <div class="list-group-item p-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($ast['name'] ?? '') ?></div>
                                        <div class="text-muted" style="font-size: 11px;">Mã: <span class="font-monospace text-primary"><?= htmlspecialchars($ast['asset_code'] ?? '') ?></span> &bull; SN: <?= htmlspecialchars($ast['serial_number'] ?: 'N/A') ?></div>
                                    </div>
                                    <span class="badge bg-light text-secondary border"><?= htmlspecialchars($ast['status'] ?? 'IN_USE') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. RECENT TICKETS -->
            <div class="card border shadow-sm rounded-3 bg-white mb-4">
                <div class="card-header bg-white border-bottom p-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-ticket-detailed text-warning"></i> Yêu Cầu Hỗ Trợ Gần Đây
                    </h6>
                    <a href="<?= $baseUrl ?>/tickets" class="small text-decoration-none">Tất cả ticket</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($myTickets)): ?>
                        <div class="p-4 text-center text-muted small">
                            Chưa có yêu cầu hỗ trợ nào được tạo.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush small">
                            <?php foreach ($myTickets as $tk): ?>
                                <a href="<?= $baseUrl ?>/tickets/<?= $tk['id'] ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-monospace fw-bold text-primary"><?= htmlspecialchars($tk['ticket_code'] ?? '') ?></span>
                                        <span class="badge bg-light text-secondary border"><?= htmlspecialchars($tk['status'] ?? '') ?></span>
                                    </div>
                                    <div class="fw-semibold text-dark text-truncate"><?= htmlspecialchars($tk['title'] ?? '') ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 3. RECENT ASSIGNED TASKFLOW ITEMS -->
            <div class="card border shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white border-bottom p-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-kanban text-info"></i> Công Việc TaskFlow Của Tôi (<?= count($myIssues) ?>)
                    </h6>
                    <a href="<?= $baseUrl ?>/projects" class="small text-decoration-none">Xem dự án</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($myIssues)): ?>
                        <div class="p-4 text-center text-muted small">
                            Chưa có task dự án nào được gán cho bạn.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush small">
                            <?php foreach ($myIssues as $iss): ?>
                                <a href="<?= $baseUrl ?>/projects/<?= $iss['project_id'] ?>/issues/<?= $iss['id'] ?>" class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="font-monospace fw-bold text-primary"><?= htmlspecialchars($iss['issue_key'] ?? '') ?></span>
                                        <span class="badge bg-light text-secondary border"><?= htmlspecialchars($iss['status'] ?? '') ?></span>
                                    </div>
                                    <div class="fw-semibold text-dark text-truncate"><?= htmlspecialchars($iss['summary'] ?? '') ?></div>
                                    <?php if (!empty($iss['project_name'])): ?>
                                        <div class="text-muted small mt-1" style="font-size: 11px;">
                                            <i class="bi bi-folder me-1"></i><?= htmlspecialchars($iss['project_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
