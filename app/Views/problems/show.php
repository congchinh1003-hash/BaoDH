<?php
$baseUrl = \App\Core\Response::baseUrl();

$statusBadge = match($problem['status']) {
    'LOGGED'           => '<span class="badge badge-logged">Mới ghi nhận</span>',
    'INVESTIGATING'    => '<span class="badge badge-investigating"><i class="bi bi-search me-1"></i>Đang điều tra RCA</span>',
    'WORKAROUND_FOUND' => '<span class="badge badge-workaround"><i class="bi bi-bandaid me-1"></i>Có giải pháp tạm</span>',
    'KNOWN_ERROR'      => '<span class="badge badge-known-error"><i class="bi bi-bookmark-x me-1"></i>Known Error</span>',
    'RESOLVED'         => '<span class="badge badge-resolved"><i class="bi bi-check-all me-1"></i>Đã xử lý xong</span>',
    'CLOSED'           => '<span class="badge badge-closed">Đã đóng</span>',
    default            => '<span class="badge badge-logged">' . htmlspecialchars($problem['status']) . '</span>'
};
?>
<style>
.badge-logged { background:#DFE1E6; color:#172B4D; }
.badge-investigating { background:#FFF0B3; color:#172B4D; }
.badge-workaround { background:#DEEBFF; color:#0052CC; }
.badge-known-error { background:#172B4D; color:#FFC400; border:1px solid #FFC400; }
.badge-resolved { background:#E3FCEF; color:#006644; }
.badge-closed { background:#F4F5F7; color:#5E6C84; }
.itil-card { background:#fff; border:1px solid #DFE1E6; border-radius:10px; box-shadow:0 1px 4px rgba(9,30,66,0.12); margin-bottom:20px; }
.itil-card .card-header { background:#fff; border-bottom:1px solid #DFE1E6; border-radius:10px 10px 0 0; padding:14px 20px; }
.itil-card .card-body { padding:20px; }
.kedb-highlight { background:linear-gradient(135deg,#172B4D 0%,#091E42 100%); border-left:4px solid #FFC400; border-radius:10px; padding:20px 24px; color:#fff; }
.rca-field label { font-weight:600; color:#172B4D; font-size:0.9rem; margin-bottom:6px; }
.attr-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F4F5F7; }
.attr-row:last-child { border-bottom:none; }
.attr-label { color:#5E6C84; font-size:13px; }
.itil-step { display:flex; align-items:flex-start; gap:12px; padding:8px 0; border-bottom:1px solid #F4F5F7; }
.itil-step:last-child { border-bottom:none; }
.step-dot { width:22px; height:22px; border-radius:50%; background:#0052CC; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; }
.table-incidents thead th { background:#F4F5F7; color:#5E6C84; font-size:11px; letter-spacing:0.5px; text-transform:uppercase; font-weight:700; padding:10px 14px; }
.table-incidents tbody td { padding:10px 14px; vertical-align:middle; border-bottom:1px solid #F4F5F7; }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <a href="<?= $baseUrl ?>/problems" class="text-muted text-decoration-none small d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Problem Management
                </a>
                <span class="text-muted">/</span>
                <span class="badge fw-bold px-2 py-1" style="background:#FFEBE6;color:#DE350B;font-size:12px;"><?= htmlspecialchars($problem['problem_code']) ?></span>
                <?= $statusBadge ?>
                <?php if (!empty($problem['is_known_error'])): ?>
                    <span class="badge badge-known-error px-2 py-1">
                        <i class="bi bi-bookmark-star me-1"></i>KEDB · KNOWN ERROR
                    </span>
                <?php endif; ?>
            </div>
            <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($problem['title']) ?></h4>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Publish to KB -->
            <form action="<?= $baseUrl ?>/problems/<?= $problem['id'] ?>/publish-kb" method="POST" onsubmit="return confirm('Bạn có muốn tạo bài viết hướng dẫn trên Knowledge Base từ giải pháp của Problem này?');">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                <button type="submit" class="btn btn-sm fw-semibold" style="background:#DEEBFF;color:#0052CC;border:1px solid #B3D4FF;">
                    <i class="bi bi-journal-plus me-1"></i> Xuất Bản KEDB (KB)
                </button>
            </form>

            <?php if ($problem['status'] !== 'RESOLVED' && $problem['status'] !== 'CLOSED'): ?>
                <button type="button" class="btn btn-sm fw-semibold" style="background:#00875A;color:#fff;border:none;" data-bs-toggle="modal" data-bs-target="#resolveModal">
                    <i class="bi bi-check2-circle me-1"></i> Giải Quyết Triệt Để
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main RCA Section -->
        <div class="col-lg-8">
            <!-- Known Error / Workaround highlight -->
            <?php if (!empty($problem['is_known_error']) && !empty($problem['workaround'])): ?>
                <div class="kedb-highlight mb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-bookmark-star-fill text-warning fs-5"></i>
                        <span class="fw-bold" style="color:#FFC400;font-size:13px;letter-spacing:0.5px;">KNOWN ERROR DATABASE (KEDB)</span>
                    </div>
                    <h6 class="fw-bold text-white mb-2">Workaround đã được xác nhận:</h6>
                    <p class="mb-0" style="color:#B3D4FF;font-size:0.9rem;line-height:1.7;"><?= htmlspecialchars($problem['workaround']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Description -->
            <div class="itil-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-card-text text-primary"></i>
                    <h6 class="fw-bold text-dark mb-0">Mô Tả Triệu Chứng Sự Cố</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-secondary" style="white-space:pre-line;line-height:1.75;"><?= htmlspecialchars($problem['description']) ?></p>
                </div>
            </div>

            <!-- RCA & Workaround Form -->
            <div class="itil-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-tools text-warning"></i>
                        <h6 class="fw-bold text-dark mb-0">Hồ Sơ RCA &amp; Giải Pháp</h6>
                    </div>
                    <span class="badge" style="background:#F4F5F7;color:#5E6C84;border:1px solid #DFE1E6;font-size:11px;">ITIL KEDB Engine</span>
                </div>
                <div class="card-body">
                    <form action="<?= $baseUrl ?>/problems/<?= $problem['id'] ?>/rca" method="POST">
                        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">

                        <div class="mb-3 rca-field">
                            <label>
                                <i class="bi bi-search me-1" style="color:#DE350B;"></i> Nguyên Nhân Gốc Rễ (Root Cause)
                            </label>
                            <textarea name="root_cause" class="form-control" rows="4" placeholder="Ghi nhận phân tích 5 Whys, kết quả dump log hoặc nguyên nhân lỗi do bug, database deadlock, v.v..."><?= htmlspecialchars($problem['root_cause'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 rca-field">
                            <label>
                                <i class="bi bi-bandaid me-1" style="color:#0052CC;"></i> Giải Pháp Tạm Thời (Workaround)
                            </label>
                            <textarea name="workaround" class="form-control" rows="4" placeholder="Hướng dẫn bước xử lý khôi phục nhanh dịch vụ cho người dùng / L1 Service Desk..."><?= htmlspecialchars($problem['workaround'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3 rca-field">
                            <label>
                                <i class="bi bi-shield-check me-1" style="color:#00875A;"></i> Giải Pháp Triệt Để (Permanent Solution)
                            </label>
                            <textarea name="permanent_solution" class="form-control" rows="3" placeholder="Bản vá code (hotfix), nâng cấp cấu hình phần cứng hoặc tối ưu kiến trúc..."><?= htmlspecialchars($problem['permanent_solution'] ?? '') ?></textarea>
                        </div>

                        <div class="row align-items-center mb-4 g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background:#FFF0B3;border:1px solid #FFC400;">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_known_error" id="knownErrorSwitch" value="1" <?= !empty($problem['is_known_error']) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold text-dark" for="knownErrorSwitch">
                                            Đánh dấu là Lỗi Đã Biết (Known Error - KEDB)
                                        </label>
                                    </div>
                                    <div class="form-text small mt-1" style="color:#5E6C84;">Khi bật, L1 Agent có thể tra cứu Workaround ngay.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Cập nhật trạng thái Problem</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="LOGGED" <?= $problem['status'] === 'LOGGED' ? 'selected' : '' ?>>LOGGED (Mới ghi nhận)</option>
                                    <option value="INVESTIGATING" <?= $problem['status'] === 'INVESTIGATING' ? 'selected' : '' ?>>INVESTIGATING (Đang điều tra)</option>
                                    <option value="WORKAROUND_FOUND" <?= $problem['status'] === 'WORKAROUND_FOUND' ? 'selected' : '' ?>>WORKAROUND_FOUND (Có giải pháp tạm)</option>
                                    <option value="KNOWN_ERROR" <?= $problem['status'] === 'KNOWN_ERROR' ? 'selected' : '' ?>>KNOWN_ERROR (Lỗi đã biết)</option>
                                    <option value="RESOLVED" <?= $problem['status'] === 'RESOLVED' ? 'selected' : '' ?>>RESOLVED (Đã giải quyết)</option>
                                    <option value="CLOSED" <?= $problem['status'] === 'CLOSED' ? 'selected' : '' ?>>CLOSED (Đã đóng)</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-sm px-4 fw-semibold" style="background:#0052CC;color:#fff;border:none;padding:8px 24px;">
                                <i class="bi bi-save me-1"></i> Lưu Cập Nhật RCA
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Linked Incidents -->
            <div class="itil-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-link-45deg text-primary"></i>
                        <h6 class="fw-bold text-dark mb-0">Các Sự Cố Liên Quan (Linked Incidents: <?= count($linkedIncidents) ?>)</h6>
                    </div>
                    <button class="btn btn-sm fw-semibold" style="background:#FFEBE6;color:#DE350B;border:1px solid #FFBDAD;" data-bs-toggle="collapse" data-bs-target="#linkIncidentForm">
                        <i class="bi bi-plus-lg me-1"></i> Liên Kết Incident
                    </button>
                </div>

                <div class="collapse" id="linkIncidentForm">
                    <div class="border-bottom p-3" style="background:#F4F5F7;">
                        <form action="<?= $baseUrl ?>/problems/<?= $problem['id'] ?>/link-incident" method="POST" class="d-flex gap-2">
                            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                            <input type="text" name="ticket_identifier" class="form-control form-control-sm" placeholder="Nhập mã Ticket (ví dụ IT-000001) hoặc Ticket ID..." required>
                            <button type="submit" class="btn btn-sm px-3 text-nowrap fw-semibold" style="background:#DE350B;color:#fff;border:none;">
                                <i class="bi bi-link me-1"></i> Liên kết
                            </button>
                        </form>
                    </div>
                </div>

                <div style="overflow:auto;">
                    <table class="table table-incidents align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="padding-left:20px;">Mã Ticket</th>
                                <th>Tiêu Đề</th>
                                <th>Người Gửi</th>
                                <th>Trạng Thái</th>
                                <th>Thời Gian Liên Kết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($linkedIncidents)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        <i class="bi bi-link-45deg fs-3 d-block mb-1 opacity-50"></i>
                                        Chưa có Incident nào được liên kết với Problem này.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($linkedIncidents as $inc): ?>
                                    <tr>
                                        <td style="padding-left:20px;font-weight:600;">
                                            <a href="<?= $baseUrl ?>/tickets/<?= $inc['ticket_id'] ?>" class="text-decoration-none" style="color:#0052CC;">
                                                <?= htmlspecialchars($inc['ticket_code']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= $baseUrl ?>/tickets/<?= $inc['ticket_id'] ?>" class="text-dark fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($inc['title']) ?>
                                            </a>
                                        </td>
                                        <td class="small text-muted"><?= htmlspecialchars($inc['requester_name']) ?></td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $inc['status_color'] ?? '#6c757d' ?>;">
                                                <?= htmlspecialchars($inc['status_name'] ?? $inc['status']) ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($inc['linked_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Attributes -->
            <div class="itil-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <h6 class="fw-bold text-dark mb-0">Thuộc Tính Problem</h6>
                </div>
                <div class="card-body">
                    <div class="attr-row">
                        <span class="attr-label">Mã sự cố gốc:</span>
                        <span class="fw-bold" style="color:#DE350B;"><?= htmlspecialchars($problem['problem_code']) ?></span>
                    </div>
                    <div class="attr-row">
                        <span class="attr-label">Danh mục:</span>
                        <span class="badge" style="background:#F4F5F7;color:#172B4D;border:1px solid #DFE1E6;"><?= htmlspecialchars($problem['category_name'] ?? 'Chung') ?></span>
                    </div>
                    <div class="attr-row">
                        <span class="attr-label">Mức độ ưu tiên:</span>
                        <span class="badge" style="background-color: <?= $problem['priority_color'] ?? '#6c757d' ?>;">
                            <?= htmlspecialchars($problem['priority_name'] ?? '') ?>
                        </span>
                    </div>
                    <div class="attr-row">
                        <span class="attr-label">Người phân tích:</span>
                        <span class="fw-semibold text-dark small"><?= htmlspecialchars($problem['assignee_name'] ?? 'Chưa chỉ định') ?></span>
                    </div>
                    <div class="attr-row">
                        <span class="attr-label">Người khởi tạo:</span>
                        <span class="small text-dark"><?= htmlspecialchars($problem['creator_name'] ?? 'Hệ thống') ?></span>
                    </div>
                    <div class="attr-row">
                        <span class="attr-label">Ngày tạo:</span>
                        <span class="small text-muted"><?= date('d/m/Y H:i', strtotime($problem['created_at'])) ?></span>
                    </div>
                    <?php if (!empty($problem['resolved_at'])): ?>
                        <div class="attr-row">
                            <span class="attr-label">Ngày giải quyết:</span>
                            <span class="small fw-semibold" style="color:#00875A;"><?= date('d/m/Y H:i', strtotime($problem['resolved_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ITIL Lifecycle -->
            <div class="itil-card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge text-warning"></i>
                    <h6 class="fw-bold text-dark mb-0">Quy Trình ITIL Problem</h6>
                </div>
                <div class="card-body">
                    <div class="itil-step">
                        <div class="step-dot">1</div>
                        <div><strong class="small text-dark">Logged:</strong><div class="text-muted small">Ghi nhận sự cố lặp lại.</div></div>
                    </div>
                    <div class="itil-step">
                        <div class="step-dot" style="background:#FF991F;">2</div>
                        <div><strong class="small text-dark">Investigating:</strong><div class="text-muted small">Phân tích nguyên nhân gốc rễ.</div></div>
                    </div>
                    <div class="itil-step">
                        <div class="step-dot" style="background:#0052CC;">3</div>
                        <div><strong class="small text-dark">Workaround:</strong><div class="text-muted small">Đưa ra phương án tạm thời cho L1/User.</div></div>
                    </div>
                    <div class="itil-step">
                        <div class="step-dot" style="background:#172B4D;">4</div>
                        <div><strong class="small text-dark">Known Error:</strong><div class="text-muted small">Lưu vào cơ sở dữ liệu KEDB.</div></div>
                    </div>
                    <div class="itil-step">
                        <div class="step-dot" style="background:#00875A;">5</div>
                        <div><strong class="small text-dark">Resolved:</strong><div class="text-muted small">Triển khai bản vá và đóng hàng loạt tickets.</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resolve Problem -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= $baseUrl ?>/problems/<?= $problem['id'] ?>/resolve" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">

                <div class="modal-header" style="background:#00875A;">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-check2-all me-2"></i>Xác Nhận Giải Quyết Triệt Để
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-secondary small">Vui lòng cung cấp giải pháp triệt để cuối cùng đã áp dụng (ví dụ: đã deploy hotfix v2.1, fix query deadlock, v.v.):</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Giải pháp triệt để <span class="text-danger">*</span></label>
                        <textarea name="permanent_solution" class="form-control" rows="3" required placeholder="Nội dung giải pháp xử lý dứt điểm..."><?= htmlspecialchars($problem['permanent_solution'] ?? '') ?></textarea>
                    </div>
                    <div class="p-3 rounded-3" style="background:#E3FCEF;border:1px solid #ABF5D1;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="batch_resolve_incidents" id="batchResolveCheck" value="1" checked>
                            <label class="form-check-label fw-bold text-dark" for="batchResolveCheck">
                                Tự động Đóng / Giải Quyết hàng loạt <?= count($linkedIncidents) ?> Incident liên kết
                            </label>
                            <div class="form-text small mt-1" style="color:#006644;">Tất cả ticket liên kết sẽ được chuyển sang RESOLVED và nhận thông báo cập nhật.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background:#F4F5F7;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-sm fw-semibold" style="background:#00875A;color:#fff;border:none;padding:7px 20px;">
                        <i class="bi bi-check2-circle me-1"></i> Xác Nhận Giải Quyết
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
