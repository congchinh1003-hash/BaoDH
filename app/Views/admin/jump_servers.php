<?php
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $servers
 * @var array $activeSessions
 * @var array $commandLogs
 * @var array $users
 * @var array $groups
 */
$baseUrl = Response::baseUrl();
$csrfToken = Session::csrfToken();
?>

<!-- PAGE HERO -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-hdd-network-fill fs-4 text-white"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Quản Trị Máy Chủ & Phân Quyền Truy Cập (Bastion PAM)</h4>
            <p class="mb-0 small" style="color:#5E6C84;">Cấu hình máy chủ, phân quyền chi tiết cho User/Nhóm IT, giám sát phiên làm việc và kiểm toán câu lệnh (Audit Logs)</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/jump" class="btn btn-outline-primary fw-semibold px-3 py-2 shadow-sm">
            <i class="bi bi-terminal me-1"></i> Vào Cổng JumpServer
        </a>
        <button type="button" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addServerModal">
            <i class="bi bi-plus-circle me-1"></i> Thêm Máy Chủ Mới
        </button>
        <button type="button" class="btn btn-success fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#grantPermissionModal">
            <i class="bi bi-shield-check me-1"></i> Cấp Quyền Truy Cập
        </button>
    </div>
</div>

<!-- NAV TABS -->
<ul class="nav nav-pills mb-4 gap-2" id="jumpAdminTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="servers-tab" data-bs-toggle="tab" data-bs-target="#tab-servers" type="button" role="tab">
            <i class="bi bi-server me-1"></i> Danh Sách Máy Chủ (<?= count($servers) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#tab-sessions" type="button" role="tab">
            <i class="bi bi-broadcast me-1 text-danger"></i> Giám Sát Phiên Đang Mở (<?= count($activeSessions) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="audit-tab" data-bs-toggle="tab" data-bs-target="#tab-audit" type="button" role="tab">
            <i class="bi bi-journal-code me-1 text-info"></i> Nhật Ký Lệnh Kiểm Toán (Audit Logs)
        </button>
    </li>
</ul>

<div class="tab-content" id="jumpAdminTabContent">
    <!-- TAB 1: DANH SÁCH MÁY CHỦ -->
    <div class="tab-pane fade show active" id="tab-servers" role="tabpanel">
        <div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold" style="color:#172B4D;">
                    <i class="bi bi-hdd-stack me-2 text-primary"></i>Danh Sách Hạ Tầng Máy Chủ Doanh Nghiệp
                </span>
                <span class="text-muted small">Cơ chế Khóa phiên độc quyền kích hoạt tự động</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead>
                        <tr style="background:#F4F5F7;color:#5E6C84;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                            <th class="ps-4 py-3">Tên Máy Chủ</th>
                            <th class="py-3">Địa Chỉ IP & Port</th>
                            <th class="py-3">Hệ Điều Hành / Giao Thức</th>
                            <th class="py-3">User Mặc Định</th>
                            <th class="py-3">Trạng Thái</th>
                            <th class="pe-4 py-3 text-end" style="width:200px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($servers)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i> Chưa có máy chủ nào trong hệ thống.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($servers as $srv): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($srv['os_type'] === 'WINDOWS'): ?>
                                                <i class="bi bi-windows text-info fs-5"></i>
                                            <?php else: ?>
                                                <i class="bi bi-terminal-fill text-dark fs-5"></i>
                                            <?php endif; ?>
                                            <div>
                                                <span class="fw-bold text-dark"><?= htmlspecialchars($srv['name']) ?></span>
                                                <?php if (!empty($srv['description'])): ?>
                                                    <div class="text-muted small text-truncate" style="max-width:260px;"><?= htmlspecialchars($srv['description']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <code class="fw-bold text-primary"><?= htmlspecialchars($srv['ip_address']) ?>:<?= htmlspecialchars($srv['port']) ?></code>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-light text-secondary border">
                                            <?= htmlspecialchars($srv['os_type']) ?> &bull; <?= htmlspecialchars($srv['protocol']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-secondary"><?= htmlspecialchars($srv['default_username'] ?: 'root') ?></span>
                                    </td>
                                    <td class="py-3">
                                        <?php if ($srv['status'] === 'ONLINE'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">ONLINE</span>
                                        <?php elseif ($srv['status'] === 'OFFLINE'): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">OFFLINE</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border px-2 py-1">MAINTENANCE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" onclick="openAdminJumpConsole(<?= $srv['id'] ?>)" class="btn btn-outline-primary" title="Mở Popup Console">
                                                <i class="bi bi-play-fill"></i> Vào
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editServerModal<?= $srv['id'] ?>" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form action="/admin/jump-servers/<?= $srv['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa máy chủ này? Mọi quyền truy cập liên quan sẽ bị gỡ bỏ.');">
                                                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- EDIT MODAL -->
                                <div class="modal fade" id="editServerModal<?= $srv['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <form action="/admin/jump-servers/<?= $srv['id'] ?>/update" method="POST">
                                                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Chỉnh Sửa Máy Chủ: <?= htmlspecialchars($srv['name']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Tên máy chủ <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($srv['name']) ?>" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-8">
                                                            <label class="form-label fw-semibold">Địa chỉ IP <span class="text-danger">*</span></label>
                                                            <input type="text" name="ip_address" class="form-control" value="<?= htmlspecialchars($srv['ip_address']) ?>" required>
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="form-label fw-semibold">Cổng (Port)</label>
                                                            <input type="number" name="port" class="form-control" value="<?= htmlspecialchars($srv['port']) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Hệ điều hành</label>
                                                            <select name="os_type" class="form-select">
                                                                <option value="LINUX" <?= $srv['os_type'] === 'LINUX' ? 'selected' : '' ?>>Linux (SSH)</option>
                                                                <option value="WINDOWS" <?= $srv['os_type'] === 'WINDOWS' ? 'selected' : '' ?>>Windows (RDP)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Giao thức</label>
                                                            <select name="protocol" class="form-select">
                                                                <option value="SSH" <?= $srv['protocol'] === 'SSH' ? 'selected' : '' ?>>SSH</option>
                                                                <option value="RDP" <?= $srv['protocol'] === 'RDP' ? 'selected' : '' ?>>RDP</option>
                                                                <option value="TELNET" <?= $srv['protocol'] === 'TELNET' ? 'selected' : '' ?>>TELNET</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">User mặc định</label>
                                                            <input type="text" name="default_username" class="form-control" value="<?= htmlspecialchars($srv['default_username']) ?>">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Trạng thái</label>
                                                            <select name="status" class="form-select">
                                                                <option value="ONLINE" <?= $srv['status'] === 'ONLINE' ? 'selected' : '' ?>>ONLINE</option>
                                                                <option value="OFFLINE" <?= $srv['status'] === 'OFFLINE' ? 'selected' : '' ?>>OFFLINE</option>
                                                                <option value="MAINTENANCE" <?= $srv['status'] === 'MAINTENANCE' ? 'selected' : '' ?>>MAINTENANCE</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Mô tả máy chủ</label>
                                                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($srv['description'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold">Lưu thay đổi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: GIÁM SÁT PHIÊN ĐANG MỞ (EXCLUSIVE SESSIONS) -->
    <div class="tab-pane fade" id="tab-sessions" role="tabpanel">
        <div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-danger">
                    <i class="bi bi-broadcast me-2"></i>Các Phiên Kết Nối Đang Hoạt Động (Live Sessions)
                </span>
                <span class="badge bg-danger">Mỗi máy chủ chỉ tối đa 1 người</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead>
                        <tr style="background:#F4F5F7;color:#5E6C84;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                            <th class="ps-4 py-3">Máy Chủ</th>
                            <th class="py-3">Kỹ Sư Đang Truy Cập</th>
                            <th class="py-3">Địa Chỉ IP Client</th>
                            <th class="py-3">Thời Gian Bắt Đầu</th>
                            <th class="py-3">Heartbeat Gần Nhất</th>
                            <th class="pe-4 py-3 text-end" style="width:140px;">Cưỡng Chế</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($activeSessions)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-circle fs-2 text-success d-block mb-2"></i>
                                    Hiện không có phiên làm việc nào đang mở. Toàn bộ máy chủ đều rảnh!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activeSessions as $sess): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($sess['server_name']) ?></span>
                                        <div class="small text-muted"><?= htmlspecialchars($sess['ip_address']) ?></div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:28px;height:28px;font-size:11px;">
                                                <?= strtoupper(substr($sess['full_name'] ?? $sess['user_name'] ?? $sess['username'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($sess['full_name'] ?? $sess['user_name'] ?? $sess['username'] ?? 'User') ?></div>
                                                <div class="text-muted small"><?= htmlspecialchars($sess['email'] ?? '') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <code><?= htmlspecialchars($sess['client_ip'] ?? '') ?></code>
                                    </td>
                                    <td class="py-3">
                                        <?= !empty($sess['started_at']) ? date('H:i:s d/m/Y', strtotime($sess['started_at'])) : '--' ?>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-success-subtle text-success">
                                            <?= !empty($sess['last_heartbeat']) ? date('H:i:s', strtotime($sess['last_heartbeat'])) : 'Vừa kết nối' ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 py-3 text-end">
                                        <form action="/admin/jump-servers/sessions/<?= $sess['id'] ?>/kick" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn ngắt kết nối cưỡng bức (Force Kick) người dùng <?= addslashes(htmlspecialchars($sess['full_name'] ?? $sess['user_name'] ?? 'User')) ?>?');">
                                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <button type="submit" class="btn btn-sm btn-danger fw-semibold">
                                                <i class="bi bi-x-octagon-fill me-1"></i> Kick Ngay
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: KIỂM TOÁN CÂU LỆNH (COMMAND AUDIT LOGS) -->
    <div class="tab-pane fade" id="tab-audit" role="tabpanel">
        <div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold" style="color:#172B4D;">
                    <i class="bi bi-shield-shaded me-2 text-info"></i>Nhật Ký Câu Lệnh Thực Thi (Command Audit Log)
                </span>
                <span class="text-muted small">Lưu lại 100% câu lệnh của mọi kỹ sư trên toàn bộ máy chủ</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead>
                        <tr style="background:#F4F5F7;color:#5E6C84;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                            <th class="ps-4 py-3">Thời Gian</th>
                            <th class="py-3">Kỹ Sư Thao Tác</th>
                            <th class="py-3">Máy Chủ Mục Tiêu</th>
                            <th class="pe-4 py-3">Câu Lệnh Thực Thi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($commandLogs)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-terminal fs-2 d-block mb-2"></i> Chưa có câu lệnh nào được ghi nhận.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($commandLogs as $log): ?>
                                <tr>
                                    <td class="ps-4 py-3 text-muted small" style="width:160px;">
                                        <?= date('H:i:s d/m/Y', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td class="py-3" style="width:200px;">
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($log['user_name'] ?: 'User #' . $log['user_id']) ?></div>
                                    </td>
                                    <td class="py-3" style="width:220px;">
                                        <span class="fw-bold"><?= htmlspecialchars($log['server_name'] ?: 'Server #' . $log['server_id']) ?></span>
                                        <div class="small text-muted"><?= htmlspecialchars($log['ip_address'] ?? '') ?></div>
                                    </td>
                                    <td class="pe-4 py-3">
                                        <code class="p-1 px-2 rounded bg-dark text-warning font-monospace" style="font-size:13px;">
                                            $ <?= htmlspecialchars($log['command']) ?>
                                        </code>
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

<!-- MODAL THÊM MÁY CHỦ MỚI -->
<div class="modal fade" id="addServerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="/admin/jump-servers/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-hdd-network text-primary me-2"></i>Thêm Máy Chủ Hạ Tầng Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên máy chủ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="vd: Ubuntu 22.04 LTS Production" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label fw-semibold">Địa chỉ IP <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control" placeholder="192.168.1.100" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold">Cổng (Port)</label>
                            <input type="number" name="port" class="form-control" value="22" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Hệ điều hành</label>
                            <select name="os_type" class="form-select">
                                <option value="LINUX">Linux (Ubuntu / CentOS)</option>
                                <option value="WINDOWS">Windows Server</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giao thức</label>
                            <select name="protocol" class="form-select">
                                <option value="SSH">SSH</option>
                                <option value="RDP">RDP</option>
                                <option value="TELNET">TELNET</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">User đăng nhập mặc định</label>
                        <input type="text" name="default_username" class="form-control" value="root">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mục đích sử dụng / Ghi chú</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="vd: Máy chủ chạy Database MySQL Cluster"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Thêm Máy Chủ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CẤP QUYỀN TRUY CẬP (GRANT PERMISSION) -->
<div class="modal fade" id="grantPermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="/admin/jump-servers/permissions/grant" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-check text-success me-2"></i>Cấp Quyền Truy Cập Máy Chủ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chọn Máy Chủ Mục Tiêu <span class="text-danger">*</span></label>
                        <select name="server_id" class="form-select" required>
                            <option value="">-- Chọn máy chủ --</option>
                            <?php foreach ($servers as $srv): ?>
                                <option value="<?= $srv['id'] ?>">
                                    <?= htmlspecialchars($srv['name']) ?> (<?= htmlspecialchars($srv['ip_address']) ?> - <?= htmlspecialchars($srv['os_type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hình thức cấp quyền <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4 p-2 bg-light rounded border">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="target_type" id="typeUser" value="USER" checked onchange="toggleGrantTarget()">
                                <label class="form-check-label fw-semibold" for="typeUser">Theo Từng Người Dùng (User)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="target_type" id="typeGroup" value="GROUP" onchange="toggleGrantTarget()">
                                <label class="form-check-label fw-semibold" for="typeGroup">Theo Nhóm IT (Group)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="selectUserContainer">
                        <label class="form-label fw-semibold">Chọn Người Dùng (User)</label>
                        <select name="user_id" id="userIdSelect" class="form-select">
                            <option value="">-- Chọn kỹ sư / người dùng --</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?> - <?= htmlspecialchars($u['role_names'] ?? $u['role'] ?? 'User') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="selectGroupContainer">
                        <label class="form-label fw-semibold">Chọn Nhóm Kỹ Thuật (Group)</label>
                        <select name="group_id" id="groupIdSelect" class="form-select">
                            <option value="">-- Chọn nhóm IT --</option>
                            <?php foreach ($groups as $g): ?>
                                <option value="<?= $g['id'] ?>">
                                    <?= htmlspecialchars($g['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="alert alert-warning small mb-0 d-flex gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                        <div>Chỉ những người dùng hoặc thành viên nhóm được cấp quyền ở đây mới nhìn thấy và truy cập được vào máy chủ này trong Cổng JumpServer.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4">Xác Nhận Cấp Quyền</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleGrantTarget() {
    const isUser = document.getElementById('typeUser').checked;
    const userContainer = document.getElementById('selectUserContainer');
    const groupContainer = document.getElementById('selectGroupContainer');
    const userSelect = document.getElementById('userIdSelect');
    const groupSelect = document.getElementById('groupIdSelect');

    if (isUser) {
        userContainer.classList.remove('d-none');
        groupContainer.classList.add('d-none');
        if (groupSelect) groupSelect.value = '';
    } else {
        userContainer.classList.add('d-none');
        groupContainer.classList.remove('d-none');
        if (userSelect) userSelect.value = '';
    }
}

function openAdminJumpConsole(serverId) {
    const url = '/jump/terminal/' + serverId;
    const w = Math.min(window.screen.availWidth - 60, 1366);
    const h = Math.min(window.screen.availHeight - 80, 820);
    const left = Math.max(0, Math.floor((window.screen.availWidth - w) / 2));
    const top = Math.max(0, Math.floor((window.screen.availHeight - h) / 2));

    const popup = window.open(
        url,
        'JumpPAM_AdminConsole_' + serverId,
        `width=${w},height=${h},top=${top},left=${left},menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes`
    );

    if (popup) {
        popup.focus();
    } else {
        window.open(url, '_blank');
    }
}
</script>
