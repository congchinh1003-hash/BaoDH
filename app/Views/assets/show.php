<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
$isStaffOrManager = Auth::isStaff() || Auth::isManager();

$statusBadge = match($asset['status']) {
    'IN_USE'     => 'bg-success-subtle text-success border border-success',
    'IN_STOCK'   => 'bg-primary-subtle text-primary border border-primary',
    'REPAIR'     => 'bg-warning-subtle text-dark border border-warning',
    'DAMAGED'    => 'bg-danger-subtle text-danger border border-danger',
    'LOST'       => 'bg-danger text-white',
    'LIQUIDATED' => 'bg-secondary-subtle text-secondary border',
    default      => 'bg-light text-dark'
};
$statusText = match($asset['status']) {
    'IN_USE'     => 'Đang sử dụng',
    'IN_STOCK'   => 'Trong kho IT',
    'REPAIR'     => 'Đang sửa chữa / Bảo hành',
    'DAMAGED'    => 'Hư hại',
    'LOST'       => 'Thất lạc',
    'LIQUIDATED' => 'Đã thanh lý',
    default      => $asset['status']
};
?>

<!-- BREADCRUMB -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/assets" class="text-decoration-none">Tài sản CNTT</a></li>
            <li class="breadcrumb-item active fw-semibold" aria-current="page"><?= htmlspecialchars($asset['asset_code']) ?></li>
        </ol>
    </nav>
    <a href="<?= $baseUrl ?>/assets" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Về danh sách
    </a>
</div>

<!-- ASSET HEADER CARD -->
<div class="card mb-4 shadow-sm border-0 bg-white">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="ticket-code-pill fs-6 px-3 py-1">
                        <i class="bi bi-upc-scan me-1"></i><?= htmlspecialchars($asset['asset_code']) ?>
                    </span>
                    <span class="badge bg-light text-dark border fs-6 px-3 py-1">
                        <i class="bi <?= htmlspecialchars($asset['category_icon'] ?? 'bi-laptop') ?> me-1 text-primary"></i>
                        <?= htmlspecialchars($asset['category_name']) ?>
                    </span>
                    <span class="badge rounded-pill fs-6 px-3 py-1 <?= $statusBadge ?>">
                        <?= $statusText ?>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-2"><?= htmlspecialchars($asset['name']) ?></h3>
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                    <div><i class="bi bi-building text-secondary me-1"></i>Hãng / Model: <strong><?= htmlspecialchars($asset['manufacturer'] ?: '') ?> <?= htmlspecialchars($asset['model'] ?: 'N/A') ?></strong></div>
                    <div>•</div>
                    <div><i class="bi bi-hash text-secondary me-1"></i>Serial Number: <strong class="font-monospace"><?= htmlspecialchars($asset['serial_number'] ?: 'Chưa cập nhật') ?></strong></div>
                    <div>•</div>
                    <div><i class="bi bi-geo-alt-fill text-danger me-1"></i>Vị trí: <strong><?= htmlspecialchars($asset['location'] ?: 'Chưa có vị trí') ?></strong></div>
                </div>
            </div>

            <!-- ACTION BUTTONS FOR IT STAFF -->
            <?php if ($isStaffOrManager): ?>
                <div class="d-flex flex-wrap gap-2">
                    <!-- BÀN GIAO -->
                    <?php if ($asset['status'] === 'IN_STOCK'): ?>
                        <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#handoverModal">
                            <i class="bi bi-person-plus-fill me-1"></i>Bàn giao cho User
                        </button>
                    <?php endif; ?>

                    <!-- THU HỒI -->
                    <?php if ($asset['status'] === 'IN_USE'): ?>
                        <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#recallModal">
                            <i class="bi bi-box-arrow-in-down me-1"></i>Thu hồi về kho
                        </button>
                    <?php endif; ?>

                    <!-- ĐIỀU CHUYỂN -->
                    <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#transferModal">
                        <i class="bi bi-arrow-left-right me-1"></i>Điều chuyển
                    </button>

                    <!-- GHI NHẬN SỬA CHỮA -->
                    <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#repairModal">
                        <i class="bi bi-tools me-1"></i>Sửa chữa / Bảo trì
                    </button>

                    <!-- NẾU ĐANG SỬA -> NÚT HOÀN TẤT -->
                    <?php if ($asset['status'] === 'REPAIR'): ?>
                        <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#completeRepairModal">
                            <i class="bi bi-check2-circle me-1"></i>Xong sửa chữa
                        </button>
                    <?php endif; ?>

                    <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                        <i class="bi bi-shield-x me-1"></i>Hỏng / Mất / Thanh lý
                    </button>

                    <a href="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/qr" class="btn btn-outline-dark btn-sm fw-semibold shadow-sm">
                        <i class="bi bi-qr-code me-1"></i>In Tem QR Code
                    </a>

                    <a href="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/edit" class="btn btn-light btn-sm border fw-semibold">
                        <i class="bi bi-pencil me-1"></i>Sửa thông tin
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- CỘT TRÁI: TABS NỘI DUNG (SPECS, HANDOVER AUDIT, REPAIR, TICKETS) -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs border-0 px-3 pt-2" id="assetTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold text-dark" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button">
                            <i class="bi bi-cpu me-1 text-primary"></i>Thông số kỹ thuật
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold text-dark" id="handover-tab" data-bs-toggle="tab" data-bs-target="#handover" type="button">
                            <i class="bi bi-clock-history me-1 text-primary"></i>Lịch sử Bàn giao (<?= count($handovers) ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold text-dark" id="repair-tab" data-bs-toggle="tab" data-bs-target="#repair" type="button">
                            <i class="bi bi-tools me-1 text-warning"></i>Nhật ký Sửa chữa (<?= count($repairs) ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold text-dark" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button">
                            <i class="bi bi-ticket-perforated me-1 text-danger"></i>Ticket Sự cố (<?= count($tickets) ?>)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="assetTabContent">
                    <!-- TAB 1: THÔNG SỐ KỸ THUẬT -->
                    <div class="tab-pane fade show active" id="specs" role="tabpanel">
                        <h6 class="fw-bold text-dark mb-3">Chi tiết Cấu hình & Thông số phần cứng:</h6>
                        <?php if (!empty($asset['specifications'])): ?>
                            <div class="p-3 bg-light rounded-3 mb-4 font-monospace text-dark small" style="white-space: pre-line; line-height: 1.8;">
                                <?= htmlspecialchars($asset['specifications']) ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-muted small border mb-4">Chưa có thông số chi tiết cấu hình phần cứng.</div>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded border-0">
                                    <span class="text-muted small fw-bold text-uppercase d-block">Ngày mua thiết bị:</span>
                                    <div class="fw-bold text-dark mt-1"><?= !empty($asset['purchase_date']) ? date('d/m/Y', strtotime($asset['purchase_date'])) : 'Chưa cập nhật' ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded border-0">
                                    <span class="text-muted small fw-bold text-uppercase d-block">Hạn bảo hành chính hãng:</span>
                                    <div class="fw-bold text-dark mt-1"><?= !empty($asset['warranty_expire']) ? date('d/m/Y', strtotime($asset['warranty_expire'])) : 'Chưa cập nhật' ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($asset['notes'])): ?>
                            <div class="mt-4 pt-3 border-top">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Ghi chú quản lý:</span>
                                <div class="text-dark small" style="white-space: pre-line;"><?= htmlspecialchars($asset['notes']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 2: LỊCH SỬ BÀN GIAO / THU HỒI (RULE 53) -->
                    <div class="tab-pane fade" id="handover" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">Nhật ký Bàn giao, Thu hồi & Điều chuyển (Audit Trail):</h6>
                            <span class="badge bg-light text-muted border">Bất biến - Không thể xóa</span>
                        </div>

                        <?php if (empty($handovers)): ?>
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-clock-history fs-2 d-block text-secondary mb-1 opacity-50"></i>
                                Chưa có ghi nhận bàn giao nào cho thiết bị này.
                            </div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($handovers as $h): ?>
                                    <?php
                                    $actBadge = match($h['action_type']) {
                                        'HANDOVER' => 'bg-success text-white',
                                        'RECALL'   => 'bg-warning text-dark',
                                        'TRANSFER' => 'bg-info text-white',
                                        'DISPOSAL' => 'bg-danger text-white',
                                        default    => 'bg-secondary text-white'
                                    };
                                    $actText = match($h['action_type']) {
                                        'HANDOVER' => 'Bàn giao cho User',
                                        'RECALL'   => 'Thu hồi về kho IT',
                                        'TRANSFER' => 'Điều chuyển',
                                        'DISPOSAL' => 'Thanh lý / Hủy',
                                        default    => $h['action_type']
                                    };
                                    ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot">
                                            <i class="bi bi-dot text-primary fs-3"></i>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge <?= $actBadge ?> fw-bold me-2"><?= $actText ?></span>
                                                <span class="fw-bold text-dark small">
                                                    <?php if ($h['action_type'] === 'HANDOVER'): ?>
                                                        Cấp cho: <strong><?= htmlspecialchars($h['to_user_name'] ?: 'N/A') ?></strong>
                                                    <?php elseif ($h['action_type'] === 'RECALL'): ?>
                                                        Thu hồi từ: <strong><?= htmlspecialchars($h['from_user_name'] ?: 'N/A') ?></strong>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($h['from_user_name'] ?: 'Kho IT') ?> <i class="bi bi-arrow-right mx-1"></i> <?= htmlspecialchars($h['to_user_name'] ?: 'Kho IT') ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <span class="text-muted small font-monospace" style="font-size: 0.75rem;">
                                                <?= date('d/m/Y H:i', strtotime($h['handover_date'])) ?>
                                            </span>
                                        </div>

                                        <div class="text-secondary small mt-1 ps-1">
                                            <i class="bi bi-chat-left-quote me-1"></i>Lý do: <em><?= htmlspecialchars($h['reason']) ?></em>
                                        </div>

                                        <div class="text-muted small mt-1 ps-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-person-check me-1"></i>Thực hiện bởi: <strong><?= htmlspecialchars($h['performer_name']) ?></strong>
                                            <?php if (!empty($h['to_location'])): ?>
                                                • Vị trí: <?= htmlspecialchars($h['to_location']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 3: NHẬT KÝ SỬA CHỮA / BẢO TRÌ (RULE 54) -->
                    <div class="tab-pane fade" id="repair" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">Lịch sử Sửa chữa, Thay thế linh kiện & Bảo trì:</h6>
                            <?php if ($isStaffOrManager): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#repairModal">
                                    <i class="bi bi-plus-lg me-1"></i>Thêm ghi nhận sửa chữa
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($repairs)): ?>
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-shield-check fs-2 d-block text-success mb-1 opacity-75"></i>
                                Thiết bị này chưa từng phát sinh sửa chữa hoặc bảo trì.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle small">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Ngày sửa</th>
                                            <th>Hiện tượng & Sự cố</th>
                                            <th>Biện pháp & Linh kiện thay thế</th>
                                            <th>Đơn vị thực hiện</th>
                                            <th>Chi phí</th>
                                            <th>KTV</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($repairs as $r): ?>
                                            <tr>
                                                <td class="text-nowrap fw-bold text-dark"><?= date('d/m/Y', strtotime($r['repair_date'])) ?></td>
                                                <td>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($r['issue_description']) ?></div>
                                                    <?php if (!empty($r['root_cause'])): ?>
                                                        <div class="text-muted" style="font-size: 0.72rem;">Nguyên nhân: <?= htmlspecialchars($r['root_cause']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="text-dark"><?= htmlspecialchars($r['solution'] ?: 'Kiểm tra & Xử lý') ?></div>
                                                    <?php if (!empty($r['replaced_parts'])): ?>
                                                        <div class="text-danger fw-bold" style="font-size: 0.72rem;">Thay: <?= htmlspecialchars($r['replaced_parts']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($r['vendor'] ?: 'Nội bộ IT') ?></td>
                                                <td class="text-end fw-bold font-monospace"><?= number_format($r['cost'], 0, ',', '.') ?> đ</td>
                                                <td><?= htmlspecialchars($r['performer_name']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TAB 4: TICKET SỰ CỐ LIÊN QUAN (RULE 55) -->
                    <div class="tab-pane fade" id="tickets" role="tabpanel">
                        <h6 class="fw-bold text-dark mb-3">Danh sách các Ticket Service Desk liên kết với thiết bị này:</h6>
                        <?php if (empty($tickets)): ?>
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-check2-circle fs-2 d-block text-success mb-1 opacity-75"></i>
                                Không có ticket sự cố nào phát sinh từ thiết bị này.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle small mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Mã Ticket</th>
                                            <th>Tiêu đề yêu cầu</th>
                                            <th>Người gửi</th>
                                            <th>Mức độ</th>
                                            <th>Trạng thái</th>
                                            <th>Khởi tạo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tickets as $tk): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/tickets/<?= $tk['id'] ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                                        <?= htmlspecialchars($tk['ticket_code']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/tickets/<?= $tk['id'] ?>" class="text-dark text-decoration-none hover-primary fw-semibold">
                                                        <?= htmlspecialchars($tk['title']) ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($tk['requester_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($tk['priority_name']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($tk['status_name'] ?? $tk['status']) ?></span>
                                                </td>
                                                <td class="text-muted"><?= date('d/m/Y H:i', strtotime($tk['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: THÔNG TIN PHỤ TRÁCH & NGƯỜI SỬ DỤNG -->
    <div class="col-12 col-xl-4">
        <!-- CURRENT USER CARD -->
        <div class="card mb-4 shadow-sm border-0 bg-white">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="fw-bold text-dark"><i class="bi bi-person-badge-fill me-2 text-primary"></i>Nhân Viên Đang Sử Dụng</span>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($asset['current_user_name'])): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="user-avatar-circle bg-primary text-white fs-5" style="width: 46px; height: 46px;">
                            <?= strtoupper(substr($asset['current_user_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($asset['current_user_name']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($asset['current_user_email']) ?></div>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded-3 small">
                        <div class="mb-2">
                            <span class="text-muted">Phòng ban:</span>
                            <span class="fw-semibold text-dark ms-1"><?= htmlspecialchars($asset['user_department'] ?: 'Chưa cập nhật') ?></span>
                        </div>
                        <div>
                            <span class="text-muted">Số điện thoại:</span>
                            <span class="fw-semibold text-dark ms-1"><?= htmlspecialchars($asset['current_user_phone'] ?: 'Chưa cập nhật') ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-inbox fs-2 d-block text-secondary mb-1 opacity-50"></i>
                        <span class="small fw-semibold">Thiết bị đang nằm trong kho IT.</span>
                        <div class="text-muted small mt-1">Chưa cấp phát cho nhân viên nào.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DEPARTMENT & LOCATION CARD -->
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="fw-bold text-dark"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>Phân Bổ & Quản Trị</span>
            </div>
            <div class="card-body p-4 small">
                <div class="mb-3">
                    <span class="text-muted fw-bold text-uppercase d-block">Phòng ban sở hữu:</span>
                    <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($asset['department'] ?: 'Kho IT') ?></span>
                </div>
                <div class="mb-3 pt-3 border-top">
                    <span class="text-muted fw-bold text-uppercase d-block">Vị trí lắp đặt / Bàn làm việc:</span>
                    <span class="fw-bold text-dark"><?= htmlspecialchars($asset['location'] ?: 'Chưa cập nhật') ?></span>
                </div>
                <div class="pt-3 border-top">
                    <span class="text-muted fw-bold text-uppercase d-block">Cập nhật lần cuối:</span>
                    <span class="text-muted"><?= date('d/m/Y H:i', strtotime($asset['updated_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====================================================================
     MODALS: HANDOVER, RECALL, TRANSFER, REPAIR, COMPLETE REPAIR
==================================================================== -->

<!-- MODAL: BÀN GIAO (HANDOVER) -->
<div class="modal fade" id="handoverModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/handover" method="POST" class="modal-content">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-person-plus-fill me-2"></i>Bàn Giao Thiết Bị Cho Nhân Viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nhân viên tiếp nhận <span class="text-danger">*</span>:</label>
                    <select name="to_user_id" class="form-select" required>
                        <option value="">-- Chọn nhân viên tiếp nhận --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['department'] ?: 'N/A') ?> - <?= htmlspecialchars($u['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Vị trí bàn giao / Bàn làm việc:</label>
                    <input type="text" name="location" class="form-control" placeholder="Ví dụ: Tầng 3 - Bàn 14 Phòng Kinh doanh" value="<?= htmlspecialchars($asset['location'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Lý do bàn giao:</label>
                    <input type="text" name="reason" class="form-control" placeholder="Ví dụ: Cấp máy mới thử việc, đổi máy theo yêu cầu...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary fw-bold">Xác nhận Bàn giao</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: THU HỒI (RECALL) -->
<div class="modal fade" id="recallModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/recall" method="POST" class="modal-content">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-warning"><i class="bi bi-box-arrow-in-down me-2"></i>Thu Hồi Thiết Bị Về Kho IT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Vị trí lưu kho:</label>
                    <input type="text" name="location" class="form-control" value="Kho IT - Kệ A" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Lý do thu hồi <span class="text-danger">*</span>:</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Nhân viên nghỉ việc, đổi máy mới, thiết bị không còn sử dụng..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark">Xác nhận Thu hồi</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ĐIỀU CHUYỂN (TRANSFER) -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/transfer" method="POST" class="modal-content">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Điều Chuyển Thiết Bị</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Chuyển giao cho nhân viên:</label>
                    <select name="to_user_id" class="form-select">
                        <option value="">-- Để trống nếu chuyển về phòng ban/kho chung --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['department'] ?: 'N/A') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Vị trí / Phòng ban mới <span class="text-danger">*</span>:</label>
                    <input type="text" name="location" class="form-control" placeholder="Ví dụ: Tầng 4 - Phòng Marketing" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Lý do điều chuyển <span class="text-danger">*</span>:</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Điều chuyển tài sản theo cơ cấu phòng ban..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary fw-bold">Xác nhận Điều chuyển</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: GHI NHẬN SỬA CHỮA (REPAIR) -->
<div class="modal fade" id="repairModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/repair" method="POST" class="modal-content">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-tools me-2"></i>Ghi Nhận Sửa Chữa / Bảo Trì</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Ngày sửa chữa:</label>
                        <input type="date" name="repair_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Đơn vị sửa chữa / Hãng bảo hành:</label>
                        <input type="text" name="vendor" class="form-control" placeholder="Ví dụ: Trung tâm bảo hành Dell / IT Nội bộ">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">Hiện tượng / Mô tả sự cố <span class="text-danger">*</span>:</label>
                        <textarea name="issue_description" class="form-control" rows="2" placeholder="Ví dụ: Máy sập nguồn, kêu rè rè ở quạt, liệt phím..." required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Nguyên nhân gốc rễ:</label>
                        <input type="text" name="root_cause" class="form-control" placeholder="Chập mạch nguồn, bụi bẩn, phồng pin...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Linh kiện thay thế (nếu có):</label>
                        <input type="text" name="replaced_parts" class="form-control" placeholder="Pin Dell 5420, Ram 16GB...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Chi phí sửa chữa (VNĐ):</label>
                        <input type="number" name="cost" class="form-control" placeholder="0" min="0" step="1000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Biện pháp xử lý:</label>
                        <input type="text" name="solution" class="form-control" placeholder="Thay pin mới, vệ sinh tra keo tản nhiệt...">
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="set_status_repair" value="1" id="setRepairStatus" <?= ($asset['status'] === 'REPAIR') ? '' : 'checked' ?>>
                            <label class="form-check-label fw-bold text-danger small" for="setRepairStatus">
                                Đổi trạng thái thiết bị sang "ĐANG SỬA CHỮA / BẢO HÀNH (REPAIR)"
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger fw-bold">Lưu Thông Tin Sửa Chữa</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: HOÀN TẤT SỬA CHỮA (COMPLETE REPAIR) -->
<div class="modal fade" id="completeRepairModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/complete-repair" method="POST" class="modal-content">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success"><i class="bi bi-check2-circle me-2"></i>Hoàn Tất Sửa Chữa Thiết Bị</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Thiết bị đã được sửa chữa/bảo hành hoàn tất. Chọn trạng thái tiếp theo cho thiết bị:</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Trạng thái sau sửa chữa:</label>
                    <select name="next_status" class="form-select">
                        <option value="IN_STOCK" selected>Nhập lại Kho IT (Sẵn sàng cấp phát - IN_STOCK)</option>
                        <?php if (!empty($asset['current_user_id'])): ?>
                            <option value="IN_USE">Bàn giao lại cho User cũ (<?= htmlspecialchars($asset['current_user_name']) ?> - IN_USE)</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success fw-bold">Xác Nhận Hoàn Tất Sửa Chữa</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ĐỔI TRẠNG THÁI (DAMAGED, LOST, LIQUIDATED, DISPOSED - RULE 52) -->
<div class="modal fade" id="changeStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/assets/<?= $asset['id'] ?>/status" method="POST" class="modal-content">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-shield-x me-2"></i>Cập Nhật Trạng Thái Thiết Bị</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>Lịch sử thay đổi trạng thái sẽ được lưu vĩnh viễn vào nhật ký bàn giao/điều chuyển của thiết bị (Rule 53).
                </div>
                <div class="mb-3">
                    <label for="asset_new_status" class="form-label fw-bold small">Chọn trạng thái mới <span class="text-danger">*</span>:</label>
                    <select name="status" id="asset_new_status" class="form-select" required>
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="DAMAGED">Hư hại nặng / Hỏng hóc (DAMAGED)</option>
                        <option value="LOST">Thất lạc / Báo mất (LOST)</option>
                        <option value="LIQUIDATED">Đã thanh lý (LIQUIDATED)</option>
                        <option value="DISPOSED">Tiêu hủy / Hủy bỏ (DISPOSED)</option>
                        <option value="IN_STOCK">Thu hồi về Kho IT (IN_STOCK)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="asset_status_reason" class="form-label fw-bold small">Lý do & Ghi chú kiểm toán <span class="text-danger">*</span>:</label>
                    <textarea name="reason" id="asset_status_reason" class="form-control" rows="3" placeholder="Mô tả nguyên nhân hư hỏng, hoàn cảnh mất hoặc biên bản thanh lý..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-danger fw-bold">Xác Nhận Đổi Trạng Thái</button>
            </div>
        </form>
    </div>
</div>

