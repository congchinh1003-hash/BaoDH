<?php
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $groups
 * @var array $staffUsers
 */
$baseUrl = Response::baseUrl();
$staffUsers = $staffUsers ?? [];
$csrfToken = Session::csrfToken();
$groupAccentColors = ['#0052CC','#00875A','#FF991F','#6554C0','#00B8D9','#DE350B','#172B4D'];
?>

<!-- PAGE HERO -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-diagram-3-fill fs-4 text-white"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Quản Lý Nhóm Kỹ Thuật IT (IT Groups)</h4>
            <p class="mb-0 small" style="color:#5E6C84;">Cấu hình các phòng ban, nhóm IT hỗ trợ kỹ thuật và phân định trưởng nhóm (Team Leader)</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary fw-semibold px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addGroupModal">
            <i class="bi bi-plus-circle me-1"></i>Thêm Nhóm IT Mới
        </button>
    </div>
</div>

<!-- CARD GRID -->
<div class="row g-4">
    <?php if (empty($groups)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-people display-4 d-block mb-3 opacity-50"></i>
            Chưa có nhóm IT nào được tạo.
        </div>
    <?php else: ?>
        <?php foreach ($groups as $i => $g): ?>
            <?php
            $accent = $groupAccentColors[$i % count($groupAccentColors)];
            $avatarColors = ['#0052CC','#00875A','#FF991F','#6554C0','#00B8D9'];
            $memberCount = (int)$g['member_count'];
            ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 position-relative" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;border-top:3px solid <?= $accent ?>;">
                    <div class="card-body p-4 d-flex flex-column">
                        <!-- Header row -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="font-monospace fw-bold px-2 py-1 rounded" style="font-size:.72rem;background:#F4F5F7;color:#5E6C84;border:1px solid #DFE1E6;">
                                    <?= htmlspecialchars($g['code']) ?>
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <!-- Nút Sửa Group -->
                                <button type="button" class="btn btn-outline-secondary btn-sm p-1 px-2"
                                        title="Chỉnh sửa nhóm"
                                        onclick="openEditGroupModal(<?= htmlspecialchars(json_encode([
                                            'id'          => $g['id'],
                                            'name'        => $g['name'],
                                            'code'        => $g['code'],
                                            'description' => $g['description'] ?? '',
                                            'leader_id'   => $g['leader_id'] ?? ''
                                        ]), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Nút Xóa Group -->
                                <form action="<?= $baseUrl ?>/admin/groups/<?= $g['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa nhóm IT này?');">
                                    <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Xóa nhóm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Group name -->
                        <h5 class="fw-bold mb-1" style="color:#172B4D;"><?= htmlspecialchars($g['name']) ?></h5>
                        <p class="small mb-3 text-muted flex-grow-1" style="min-height:36px;"><?= htmlspecialchars($g['description'] ?? 'Chưa cập nhật mô tả') ?></p>

                        <!-- Team Leader -->
                        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3" style="background:#FAFBFC;border:1px solid #F4F5F7;">
                            <?php if (!empty($g['leader_name'])): ?>
                                <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold flex-shrink-0"
                                     style="width:32px;height:32px;font-size:.75rem;background:<?= $accent ?>;">
                                    <?= strtoupper(mb_substr($g['leader_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-size:.70rem;color:#5E6C84;text-transform:uppercase;font-weight:700;">Trưởng nhóm (Team Leader)</div>
                                    <div class="fw-semibold small" style="color:#172B4D;"><?= htmlspecialchars($g['leader_name']) ?></div>
                                </div>
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                     style="width:32px;height:32px;background:#F4F5F7;border:2px dashed #DFE1E6;">
                                    <i class="bi bi-person text-muted" style="font-size:.75rem;"></i>
                                </div>
                                <div>
                                    <div style="font-size:.70rem;color:#5E6C84;text-transform:uppercase;font-weight:700;">Trưởng nhóm</div>
                                    <span class="small text-muted fst-italic">Chưa chỉ định</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Member count + Avatar stack -->
                        <div class="d-flex align-items-center justify-content-between pt-3" style="border-top:1px solid #DFE1E6;">
                            <div class="d-flex align-items-center">
                                <?php for ($m = 0; $m < min($memberCount, 4); $m++): ?>
                                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                         style="width:28px;height:28px;font-size:.65rem;background:<?= $avatarColors[$m % count($avatarColors)] ?>;margin-left:<?= $m > 0 ? '-8px' : '0' ?>;border:2px solid #fff;z-index:<?= 10 - $m ?>;">
                                        <?= chr(65 + $m) ?>
                                    </div>
                                <?php endfor; ?>
                                <?php if ($memberCount > 4): ?>
                                    <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold"
                                         style="width:28px;height:28px;font-size:.62rem;background:#F4F5F7;color:#5E6C84;margin-left:-8px;border:2px solid #fff;">
                                        +<?= $memberCount - 4 ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold" style="color:<?= $accent ?>;font-size:1.2rem;"><?= $memberCount ?></span>
                                <span class="small" style="color:#5E6C84;"> thành viên</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- MODAL: THÊM NHÓM IT -->
<div class="modal fade" id="addGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/admin/groups/store" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-diagram-3-fill me-2"></i>Thêm Nhóm IT Mới
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên nhóm IT <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: IT Helpdesk, Hạ tầng Mạng & Máy chủ, ERP Support..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mã nhóm (Code)</label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="Để trống tự sinh: GRP_HELPDESK...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Trưởng nhóm (Team Leader)</label>
                        <select name="leader_id" class="form-select">
                            <option value="">-- Chưa chọn trưởng nhóm --</option>
                            <?php foreach ($staffUsers as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['full_name']) ?> (<?= htmlspecialchars($st['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Mô tả chức năng nhiệm vụ</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Mô tả phạm vi xử lý của nhóm kỹ thuật..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Lưu Nhóm IT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: SỬA NHÓM IT -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editGroupForm" method="POST" action="">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Chỉnh Sửa Nhóm IT
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tên nhóm IT <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editGroupName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mã nhóm (Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="editGroupCode" class="form-control font-monospace" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Trưởng nhóm (Team Leader)</label>
                        <select name="leader_id" id="editGroupLeaderId" class="form-select">
                            <option value="">-- Chưa chọn trưởng nhóm --</option>
                            <?php foreach ($staffUsers as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['full_name']) ?> (<?= htmlspecialchars($st['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Mô tả chức năng nhiệm vụ</label>
                        <textarea name="description" id="editGroupDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="bi bi-save me-1"></i>Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditGroupModal(g) {
    document.getElementById('editGroupForm').action = '<?= $baseUrl ?>/admin/groups/' + g.id + '/update';
    document.getElementById('editGroupName').value = g.name;
    document.getElementById('editGroupCode').value = g.code;
    document.getElementById('editGroupDesc').value = g.description || '';
    document.getElementById('editGroupLeaderId').value = g.leader_id || '';
    new bootstrap.Modal(document.getElementById('editGroupModal')).show();
}
</script>
