<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $users
 * @var array $roles
 * @var array $groups
 */
$baseUrl = Response::baseUrl();
$roles = $roles ?? [];
$groups = $groups ?? [];
$csrfToken = Session::csrfToken();
?>

<!-- PAGE HERO -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-people-fill fs-4 text-white"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Quản Lý Người Dùng &amp; Phân Quyền Vai Trò (RBAC)</h4>
            <p class="mb-0 small" style="color:#5E6C84;">Quản lý danh sách tài khoản, thông tin phòng ban và nhóm quyền (User, Staff, Manager, Admin)</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill me-1"></i>Thêm Tài Khoản Mới
        </button>
    </div>
</div>

<!-- MAIN CARD -->
<div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center"
         style="border-bottom:1px solid #DFE1E6;border-radius:10px 10px 0 0;">
        <span class="fw-bold" style="color:#172B4D;">
            <i class="bi bi-person-badge-fill me-2" style="color:#0052CC;"></i>Danh Sách Tài Khoản Hệ Thống (<?= count($users) ?>)
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
            <thead>
                <tr style="background:#F4F5F7;color:#5E6C84;font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;">
                    <th class="ps-4 py-3" style="width:70px;">ID</th>
                    <th class="py-3">Họ và Tên</th>
                    <th class="py-3">Tài khoản &amp; Email</th>
                    <th class="py-3">Phòng ban</th>
                    <th class="py-3">Số điện thoại</th>
                    <th class="py-3">Vai trò (Roles)</th>
                    <th class="py-3 text-center" style="width:120px;">Trạng thái</th>
                    <th class="pe-4 py-3 text-end" style="width:150px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php
                    $rolesStr = $u['role_names'] ?? 'USER';
                    if (str_contains($rolesStr, 'ADMIN')) {
                        $roleBg = '#172B4D'; $roleColor = '#fff'; $roleIcon = 'bi-shield-fill-check';
                    } elseif (str_contains($rolesStr, 'MANAGER')) {
                        $roleBg = '#0052CC'; $roleColor = '#fff'; $roleIcon = 'bi-person-gear';
                    } elseif (str_contains($rolesStr, 'STAFF')) {
                        $roleBg = '#E3F2FF'; $roleColor = '#0052CC'; $roleIcon = 'bi-tools';
                    } else {
                        $roleBg = '#F4F5F7'; $roleColor = '#5E6C84'; $roleIcon = 'bi-person';
                    }
                    $avatarColors = ['#0052CC','#00875A','#FF991F','#6554C0','#00B8D9','#DE350B'];
                    $avatarBg = $avatarColors[crc32($u['full_name']) % count($avatarColors)];
                    $isSelf = ((int)$u['id'] === (int)Auth::id());
                    ?>
                    <tr>
                        <td class="ps-4 font-monospace small" style="color:#5E6C84;">#<?= $u['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                                     style="width:36px;height:36px;font-size:.82rem;background:<?= $avatarBg ?>;">
                                    <?= strtoupper(mb_substr($u['full_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:#172B4D;">
                                        <?= htmlspecialchars($u['full_name']) ?>
                                        <?php if ($isSelf): ?>
                                            <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.65rem;">Bạn</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($u['job_title'])): ?>
                                        <div class="small text-primary fw-medium" style="font-size:.72rem;">
                                            <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($u['job_title']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="font-size:.72rem;color:#5E6C84;">
                                        <i class="bi bi-calendar3 me-1"></i>Tham gia: <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold small font-monospace" style="color:#172B4D;"><?= htmlspecialchars($u['username']) ?></div>
                            <div class="small" style="color:#5E6C84;"><?= htmlspecialchars($u['email']) ?></div>
                        </td>
                        <td>
                            <span class="badge px-2 py-1" style="background:#F4F5F7;color:#172B4D;border:1px solid #DFE1E6;font-weight:500;">
                                <i class="bi bi-building me-1 opacity-75"></i><?= htmlspecialchars($u['department'] ?? 'Chưa cập nhật') ?>
                            </span>
                            <?php if (!empty($u['seat_location'])): ?>
                                <div class="small text-muted mt-1" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($u['seat_location']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="small font-monospace" style="color:#5E6C84;"><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                        <td>
                            <span class="badge px-2 py-1 fw-bold d-inline-flex align-items-center gap-1"
                                  style="background:<?= $roleBg ?>;color:<?= $roleColor ?>;border-radius:6px;font-size:.75rem;">
                                <i class="bi <?= $roleIcon ?>"></i>
                                <?= htmlspecialchars($rolesStr) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($u['is_active']): ?>
                                <span class="badge px-3 py-1 fw-bold d-inline-flex align-items-center gap-1"
                                      style="background:#E3FCEF;color:#006644;border-radius:20px;font-size:.72rem;">
                                    <i class="bi bi-check-circle-fill"></i> Hoạt động
                                </span>
                            <?php else: ?>
                                <span class="badge px-3 py-1 fw-bold d-inline-flex align-items-center gap-1"
                                      style="background:#FFEBE6;color:#DE350B;border-radius:20px;font-size:.72rem;">
                                    <i class="bi bi-lock-fill"></i> Đã khóa
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <!-- Nút Sửa -->
                                <button type="button" class="btn btn-outline-primary btn-sm p-1 px-2"
                                        title="Chỉnh sửa tài khoản"
                                        onclick="openEditUserModal(<?= htmlspecialchars(json_encode([
                                            'id'            => $u['id'],
                                            'username'      => $u['username'],
                                            'full_name'     => $u['full_name'],
                                            'email'         => $u['email'],
                                            'department'    => $u['department'] ?? '',
                                            'job_title'     => $u['job_title'] ?? '',
                                            'seat_location' => $u['seat_location'] ?? '',
                                            'phone'         => $u['phone'] ?? '',
                                            'roles'         => explode(', ', $u['role_names'] ?? 'USER')
                                        ]), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <?php if (!$isSelf): ?>
                                    <!-- Nút Khóa / Mở khóa -->
                                    <form action="<?= $baseUrl ?>/admin/users/<?= $u['id'] ?>/toggle-status" method="POST" class="d-inline" onsubmit="return confirm('<?= $u['is_active'] ? 'Bạn có chắc chắn muốn khóa tài khoản này?' : 'Mở khóa kích hoạt tài khoản này?' ?>');">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn btn-outline-warning btn-sm p-1 px-2 text-warning-emphasis" title="<?= $u['is_active'] ? 'Khóa tài khoản' : 'Mở khóa' ?>">
                                            <i class="bi <?= $u['is_active'] ? 'bi-lock-fill' : 'bi-unlock-fill' ?>"></i>
                                        </button>
                                    </form>

                                    <!-- Nút Xóa -->
                                    <form action="<?= $baseUrl ?>/admin/users/<?= $u['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này? Nếu tài khoản đã có ticket, hệ thống sẽ chuyển sang trạng thái Khóa để bảo toàn dữ liệu.');">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Xóa tài khoản">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: THÊM NGƯỜI DÙNG MỚI -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/admin/users/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus-fill me-2"></i>Thêm Tài Khoản Người Dùng Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tên đăng nhập (Username) <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="nguyenvana, it_staff..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Họ và Tên đầy đủ <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Email công vụ <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="a.nguyen@company.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Mật khẩu khởi tạo</label>
                            <input type="password" name="password" class="form-control" placeholder="Mặc định: 123456 (nếu để trống)">
                            <div class="form-text small">Để trống sẽ tự động đặt mật khẩu mặc định là <code>123456</code>.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Phòng ban / Bộ phận</label>
                            <input type="text" name="department" class="form-control" placeholder="Phòng Kế toán, Kinh doanh, IT...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="0901234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Chức danh / Chức vụ</label>
                            <input type="text" name="job_title" class="form-control" placeholder="Kế toán trưởng, Kỹ sư Mạng, Nhân viên...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Vị trí chỗ ngồi (Seat Location)</label>
                            <input type="text" name="seat_location" class="form-control" placeholder="Tầng 2 - Phòng KT-01 - Bàn KT-04">
                        </div>

                        <!-- Gán Vai trò RBAC -->
                        <div class="col-12">
                            <label class="form-label fw-bold small d-block">Phân quyền Vai trò (RBAC Roles) <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3 p-3 rounded-3 bg-light border">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="USER" class="form-check-input" checked>
                                    <span class="badge bg-secondary-subtle text-secondary">USER (Người dùng cuối)</span>
                                </label>
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="STAFF" class="form-check-input">
                                    <span class="badge bg-primary-subtle text-primary">STAFF (Kỹ thuật viên IT)</span>
                                </label>
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="MANAGER" class="form-check-input">
                                    <span class="badge bg-warning-subtle text-warning">MANAGER (Quản lý IT)</span>
                                </label>
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="ADMIN" class="form-check-input">
                                    <span class="badge bg-dark text-white">ADMIN (Quản trị hệ thống)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Gán Nhóm IT -->
                        <div class="col-12">
                            <label class="form-label fw-bold small">Nhóm IT Phụ trách (Áp dụng nếu là Staff/Manager)</label>
                            <select name="group_id" class="form-select">
                                <option value="">-- Không thuộc nhóm IT cố định --</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Lưu Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: CHỈNH SỬA NGƯỜI DÙNG -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editUserForm" method="POST" action="">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Tài Khoản: <span id="editUserTitle" class="text-warning"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Họ và Tên đầy đủ <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="editFullName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Email công vụ <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Đổi mật khẩu mới (Tùy chọn)</label>
                            <input type="password" name="password" class="form-control" placeholder="Để trống nếu không muốn đổi mật khẩu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Phòng ban / Bộ phận</label>
                            <input type="text" name="department" id="editDepartment" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Số điện thoại</label>
                            <input type="text" name="phone" id="editPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Chức danh / Chức vụ</label>
                            <input type="text" name="job_title" id="editJobTitle" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Vị trí chỗ ngồi (Seat Location)</label>
                            <input type="text" name="seat_location" id="editSeatLocation" class="form-control">
                        </div>

                        <!-- Gán Vai trò RBAC -->
                        <div class="col-12">
                            <label class="form-label fw-bold small d-block">Phân quyền Vai trò (RBAC Roles) <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3 p-3 rounded-3 bg-light border">
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="USER" id="editRoleUser" class="form-check-input">
                                    <span class="badge bg-secondary-subtle text-secondary">USER</span>
                                </label>
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="STAFF" id="editRoleStaff" class="form-check-input">
                                    <span class="badge bg-primary-subtle text-primary">STAFF</span>
                                </label>
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="MANAGER" id="editRoleManager" class="form-check-input">
                                    <span class="badge bg-warning-subtle text-warning">MANAGER</span>
                                </label>
                                <label class="form-check-label d-flex align-items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="ADMIN" id="editRoleAdmin" class="form-check-input">
                                    <span class="badge bg-dark text-white">ADMIN</span>
                                </label>
                            </div>
                        </div>

                        <!-- Gán Nhóm IT -->
                        <div class="col-12">
                            <label class="form-label fw-bold small">Nhóm IT Phụ trách</label>
                            <select name="group_id" id="editGroupId" class="form-select">
                                <option value="">-- Không phân nhóm IT --</option>
                                <?php foreach ($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-save me-1"></i>Cập Nhật Tài Khoản
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditUserModal(user) {
    document.getElementById('editUserForm').action = '<?= $baseUrl ?>/admin/users/' + user.id + '/update';
    document.getElementById('editUserTitle').textContent = user.username + ' (#' + user.id + ')';
    document.getElementById('editFullName').value = user.full_name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editDepartment').value = user.department || '';
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editJobTitle').value = user.job_title || '';
    document.getElementById('editSeatLocation').value = user.seat_location || '';

    // Check roles
    var roles = user.roles || [];
    document.getElementById('editRoleUser').checked = roles.includes('USER');
    document.getElementById('editRoleStaff').checked = roles.includes('STAFF');
    document.getElementById('editRoleManager').checked = roles.includes('MANAGER');
    document.getElementById('editRoleAdmin').checked = roles.includes('ADMIN');

    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}
</script>
