<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
?>

<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active fw-bold" aria-current="page">Hỗ Trợ Từ Xa (Remote Assistance)</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-display text-primary"></i> Cổng Hỗ Trợ Từ Xa Trực Tiếp (Remote Desktop Hub)
            </h1>
            <p class="text-muted small mb-0">Hỗ trợ kỹ thuật trực tuyến: Kỹ thuật viên IT có thể yêu cầu xem màn hình, chỉ điểm laser và điều khiển hỗ trợ tức thì qua trình duyệt.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/tickets" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-ticket-perforated me-1"></i> Danh Sách Ticket
            </a>
        </div>
    </div>

    <!-- PENDING INVITATION BANNER FOR CLIENT -->
    <?php if (!empty($pendingForMe)): ?>
        <div class="alert alert-primary border-2 border-primary shadow-sm p-4 mb-4 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3 animate__animated animate__pulse animate__infinite">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary text-white rounded-circle shadow-sm">
                    <i class="bi bi-display fs-2"></i>
                </div>
                <div>
                    <span class="badge bg-danger mb-1">YÊU CẦU ĐANG CHỜ PHẢN HỒI</span>
                    <h5 class="fw-bold text-primary mb-1">Kỹ thuật viên <?= htmlspecialchars($pendingForMe['staff_name']) ?> đang yêu cầu kết nối Remote máy tính của bạn!</h5>
                    <p class="mb-0 text-muted small">
                        <?php if (!empty($pendingForMe['ticket_code'])): ?>
                            Liên quan đến sự cố: <strong>[<?= htmlspecialchars($pendingForMe['ticket_code']) ?>] <?= htmlspecialchars($pendingForMe['ticket_title']) ?></strong>.
                        <?php else: ?>
                            Phiên hỗ trợ trực tiếp mã PIN: <strong><?= htmlspecialchars($pendingForMe['session_code']) ?></strong>.
                        <?php endif; ?>
                        Vui lòng bấm vào nút bên cạnh để tham gia và cho phép chia sẻ màn hình.
                    </p>
                </div>
            </div>
            <a href="<?= $baseUrl ?>/remote-support/session/<?= $pendingForMe['session_code'] ?>" class="btn btn-primary btn-lg fw-bold shadow px-4">
                <i class="bi bi-broadcast me-2"></i> Tham Gia Ngay (Mã <?= htmlspecialchars($pendingForMe['session_code']) ?>)
            </a>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- CỘT TRÁI: THAM GIA BẰNG PIN HOẶC TẠO PHIÊN MỚI -->
        <div class="col-12 col-lg-5">
            <!-- THAM GIA BẰNG MÃ PIN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-key-fill text-warning"></i> Tham Gia Phiên Bằng Mã PIN
                    </h6>
                </div>
                <div class="card-body p-4 text-center">
                    <p class="text-muted small mb-3">Nhập mã PIN 6 số hoặc mã phiên dạng <code>REM-XXXXXX</code> do Kỹ thuật viên IT cung cấp cho bạn:</p>
                    <form action="<?= $baseUrl ?>/remote-support/join" method="POST" class="d-flex gap-2 justify-content-center">
                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="text" name="pin" class="form-control form-control-lg text-center font-monospace fw-bold letter-spacing-1 text-primary shadow-xs" 
                               placeholder="Ví dụ: 829104" style="max-width: 250px; font-size: 1.3rem;" required autofocus>
                        <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Kết Nối
                        </button>
                    </form>
                    <div class="text-muted small mt-2">Đường truyền bảo mật mã hóa SSL qua WebRTC P2P trực tiếp giữa hai máy tính.</div>
                </div>
            </div>

            <!-- TẠO PHIÊN NHANH (CHO KỸ THUẬT VIÊN IT) -->
            <?php if ($isStaff): ?>
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle-fill text-primary"></i> Khởi Tạo Phiên Hỗ Trợ Mới (Dành Cho IT)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= $baseUrl ?>/remote-support/create-quick" method="POST">
                            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Chọn nhân viên / khách hàng cần hỗ trợ <span class="text-danger">*</span>:</label>
                                <select name="client_id" class="form-select select2-user" required>
                                    <option value="">-- Chọn người dùng nhận hỗ trợ --</option>
                                    <?php foreach ($allUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>">
                                            <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?> - <?= htmlspecialchars($u['department'] ?: 'N/A') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Ghi chú sự cố / nội dung hỗ trợ:</label>
                                <input type="text" name="notes" class="form-control" placeholder="Ví dụ: Hỗ trợ lỗi Outlook không nhận mail, cài driver máy in...">
                            </div>
                            <button type="submit" class="btn btn-success fw-bold w-100 py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-display fs-5"></i> Bắt Đầu Phiên Hỗ Trợ Trực Tuyến
                            </button>
                            <div class="small text-muted text-center mt-2">Hệ thống sẽ lập tức gửi thông báo đẩy đến máy của người dùng.</div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- HƯỚNG DẪN DÀNH CHO USER -->
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-info-circle me-1"></i> Cách Thức Hoạt Động Của Tính Năng:</h6>
                        <ol class="small text-muted ps-3 mb-0" style="line-height: 1.8;">
                            <li>Khi máy tính gặp sự cố, Kỹ thuật viên IT sẽ gửi yêu cầu Remote trực tiếp trên màn hình của bạn.</li>
                            <li>Bạn bấm <strong>"Tham gia & Chia sẻ màn hình"</strong> để cho phép IT xem màn hình.</li>
                            <li>Không cần cài đặt thêm bất kỳ phần mềm nào, bảo mật và an toàn 100%.</li>
                            <li>Bạn có thể bấm <strong>"Ngắt kết nối"</strong> bất cứ lúc nào bạn muốn.</li>
                        </ol>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- CỘT PHẢI: DANH SÁCH CÁC PHIÊN GẦN ĐÂY -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-secondary"></i> Lịch Sử Các Phiên Remote Gần Đây
                    </h6>
                    <span class="badge bg-light text-dark border"><?= count($activeSessions) ?> phiên</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($activeSessions)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-display fs-1 d-block mb-2 opacity-50"></i>
                            <div class="fw-semibold">Chưa có phiên hỗ trợ nào được ghi nhận.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light text-muted text-uppercase" style="font-size: 0.72rem;">
                                    <tr>
                                        <th class="ps-3 py-3">Mã Phiên</th>
                                        <th>Khách Hàng / User</th>
                                        <th>Kỹ Thuật Viên (IT)</th>
                                        <th>Trạng Thái</th>
                                        <th>Thời Gian</th>
                                        <th class="pe-3 text-end">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activeSessions as $sess): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold font-monospace text-primary">
                                                <?= htmlspecialchars($sess['session_code']) ?>
                                                <?php if (!empty($sess['ticket_code'])): ?>
                                                    <div class="text-muted small fw-normal">Ticket #<?= htmlspecialchars($sess['ticket_code']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($sess['client_name']) ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($sess['client_department'] ?: 'Nhân viên') ?></div>
                                            </td>
                                            <td>
                                                <div class="text-dark fw-semibold"><?= htmlspecialchars($sess['staff_name']) ?></div>
                                            </td>
                                            <td>
                                                <?php if ($sess['status'] === 'CONNECTED'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success">
                                                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 0.5rem; height: 0.5rem;"></span> Đang Live
                                                    </span>
                                                <?php elseif ($sess['status'] === 'WAITING'): ?>
                                                    <span class="badge bg-warning-subtle text-dark border border-warning">Đang chờ</span>
                                                <?php elseif ($sess['status'] === 'ENDED'): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border">Đã kết thúc</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($sess['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($sess['created_at'])) ?>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <a href="<?= $baseUrl ?>/remote-support/session/<?= $sess['session_code'] ?>" class="btn btn-sm btn-outline-primary fw-semibold shadow-xs">
                                                    Vào phòng <i class="bi bi-chevron-right ms-1"></i>
                                                </a>
                                            </td>
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
