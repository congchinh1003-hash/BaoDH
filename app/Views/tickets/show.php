<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $ticket
 * @var array $comments
 * @var array $history
 * @var array $attachments
 * @var array $itGroups
 * @var array $staffMembers
 * @var array|null $rating
 * @var array $watchers
 * @var bool $isWatching
 * @var array $allPriorities
 * @var array $allAssets
 * @var array $worklogs
 * @var int $totalWorklogMinutes
 * @var string $totalWorklogFormatted
 * @var array $checklist
 * @var array $linkedIssues
 */
$worklogs = $worklogs ?? [];
$totalWorklogMinutes = $totalWorklogMinutes ?? 0;
$totalWorklogFormatted = $totalWorklogFormatted ?? '0m';
$checklist = $checklist ?? [];
$linkedIssues = $linkedIssues ?? [];
$allPriorities = $allPriorities ?? [];
$allAssets = $allAssets ?? [];
$watchers = $watchers ?? [];
$isWatching = !empty($isWatching);
$rating = $rating ?? null;
$baseUrl = Response::baseUrl();
$isStaffOrManager = Auth::isStaff() || Auth::isManager();
$isRequester = (int)($ticket['requester_id'] ?? 0) === Auth::id();
$canManageTicket = $isStaffOrManager || Auth::isAdmin();
?>

<!-- BREADCRUMB -->
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb breadcrumb-sm mb-0" style="font-size:0.8rem;">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/tickets" class="text-decoration-none text-muted"><i class="bi bi-collection me-1"></i>Tickets</a></li>
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page"><?= htmlspecialchars($ticket['ticket_code'] ?? 'TICKET') ?></li>
    </ol>
</nav>

<!-- ACTIVE REMOTE SUPPORT BANNER -->
<?php if (!empty($activeRemoteSession)): ?>
    <div class="alert alert-primary border-2 border-primary shadow-sm p-3 mb-3 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3 animate__animated animate__fadeIn">
        <div class="d-flex align-items-center gap-3">
            <div class="spinner-grow text-primary" role="status" style="width: 2rem; height: 2rem;"></div>
            <div>
                <h6 class="fw-bold text-primary mb-1">
                    <i class="bi bi-display me-2"></i>
                    <?= $isRequester ? 'Kỹ thuật viên IT đang yêu cầu kết nối Remote máy tính của bạn!' : 'Phiên Hỗ Trợ Từ Xa Đang Hoạt Động Cho Ticket Này' ?>
                </h6>
                <p class="mb-0 text-muted small">
                    Mã phiên: <strong class="text-dark font-monospace"><?= htmlspecialchars($activeRemoteSession['session_code']) ?></strong> | 
                    Kỹ thuật viên: <strong class="text-dark"><?= htmlspecialchars($activeRemoteSession['staff_name']) ?></strong> | 
                    Trạng thái: <span class="badge bg-success"><?= htmlspecialchars($activeRemoteSession['status']) ?></span>
                </p>
            </div>
        </div>
        <a href="<?= $baseUrl ?>/remote-support/session/<?= $activeRemoteSession['session_code'] ?>" class="btn btn-primary fw-bold shadow-sm px-4">
            <i class="bi bi-broadcast me-1"></i> <?= $isRequester ? 'Tham Gia & Cấp Quyền Ngay' : 'Vào Phòng Điều Khiển' ?>
        </a>
    </div>
<?php endif; ?>

<!-- TICKET HERO HEADER -->
<div class="mb-4 bg-white rounded-3 shadow-sm p-4"
     style="border-left:4px solid #0052CC; border-radius:10px !important;">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div class="flex-1" style="min-width:0;">
            <!-- Badges Row -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="ticket-code-pill px-3 py-1" style="font-size:0.9rem;">
                    <i class="bi bi-hash"></i><?= htmlspecialchars($ticket['ticket_code'] ?? '') ?>
                </span>

                <!-- ITIL TYPE BADGE -->
                <?php if (($ticket['ticket_type'] ?? 'INCIDENT') === 'SERVICE_REQUEST'): ?>
                    <span class="badge px-3 py-1" style="background:#EAF0FB; color:#0052CC; border:1px solid #0052CC; border-radius:999px; font-size:0.8rem;">
                        <i class="bi bi-send-check-fill me-1"></i>YÊU CẦU DỊCH VỤ
                    </span>
                <?php else: ?>
                    <span class="badge px-3 py-1" style="background:#FFEBE6; color:#DE350B; border:1px solid #DE350B; border-radius:999px; font-size:0.8rem;">
                        <i class="bi bi-bug-fill me-1"></i>SỰ CỐ (INCIDENT)
                    </span>
                <?php endif; ?>

                <!-- SUPPORT TIER BADGE -->
                <span class="badge bg-dark text-white px-3 py-1" style="border-radius:999px; font-size:0.8rem;">
                    <i class="bi bi-layers-fill me-1"></i>TIER <?= htmlspecialchars($ticket['support_tier'] ?? 'L1') ?>
                </span>

                <span class="badge-priority priority-<?= strtolower($ticket['priority_code'] ?? 'normal') ?> px-3 py-1" style="font-size:0.8rem;">
                    <i class="bi bi-flag-fill me-1"></i><?= htmlspecialchars($ticket['priority_name'] ?? 'Bình thường') ?>
                </span>
                <span class="badge-status status-<?= strtolower($ticket['status'] ?? 'new') ?> px-3 py-1" style="font-size:0.8rem;">
                    <?= htmlspecialchars($ticket['status_name'] ?? ($ticket['status'] ?? 'NEW')) ?>
                </span>

                <!-- APPROVAL STATUS BADGE -->
                <?php if (!empty($ticket['approval_status']) && $ticket['approval_status'] !== 'NONE'): ?>
                    <?php if ($ticket['approval_status'] === 'PENDING'): ?>
                        <span class="badge px-3 py-1" style="background:#FFF4E0; color:#974F0C; border:1px solid #FF991F; border-radius:999px; font-size:0.8rem;">
                            <i class="bi bi-hourglass-split me-1"></i>CHỜ DUYỆT
                        </span>
                    <?php elseif ($ticket['approval_status'] === 'APPROVED'): ?>
                        <span class="badge bg-success text-white px-3 py-1" style="border-radius:999px; font-size:0.8rem;">
                            <i class="bi bi-check-circle-fill me-1"></i>ĐÃ DUYỆT
                        </span>
                    <?php elseif ($ticket['approval_status'] === 'REJECTED'): ?>
                        <span class="badge bg-danger text-white px-3 py-1" style="border-radius:999px; font-size:0.8rem;">
                            <i class="bi bi-x-circle-fill me-1"></i>TỪ CHỐI
                        </span>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- LINKED PROBLEM BADGE -->
                <?php if (!empty($ticket['problem_id'])): ?>
                    <a href="<?= $baseUrl ?>/problems/<?= $ticket['problem_id'] ?>" class="badge bg-danger text-white text-decoration-none px-3 py-1" style="border-radius:999px; font-size:0.8rem;">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i><?= htmlspecialchars($ticket['problem_code'] ?? ('PRB-' . $ticket['problem_id'])) ?>
                    </a>
                <?php endif; ?>

                <!-- LINKED CHANGE BADGE -->
                <?php if (!empty($ticket['change_id'])): ?>
                    <a href="<?= $baseUrl ?>/changes/<?= $ticket['change_id'] ?>" class="badge bg-primary text-white text-decoration-none px-3 py-1" style="border-radius:999px; font-size:0.8rem;">
                        <i class="bi bi-arrow-repeat me-1"></i><?= htmlspecialchars($ticket['change_code'] ?? ('CHG-' . $ticket['change_id'])) ?>
                    </a>
                <?php endif; ?>

                <?php if (!empty($ticket['reopen_count'])): ?>
                    <span class="badge bg-danger text-white rounded-pill px-2">Đã mở lại <?= $ticket['reopen_count'] ?> lần</span>
                <?php endif; ?>

                <!-- SLA STATUS BADGES -->
                <?php if (($ticket['calculated_sla_status'] ?? '') === 'OVERDUE'): ?>
                    <span class="badge bg-danger px-3 py-1" style="border-radius:999px; font-size:0.8rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?= \App\Services\SlaService::formatOverdueTime($ticket['sla_due_at']) ?: 'QUÁ HẠN SLA' ?>
                    </span>
                <?php elseif (($ticket['calculated_sla_status'] ?? '') === 'CRITICAL'): ?>
                    <span class="badge px-3 py-1" style="background:#FFEBE6; color:#DE350B; border:1px solid #DE350B; border-radius:999px; font-size:0.8rem;">
                        <i class="bi bi-alarm-fill me-1"></i>SẮP QUÁ HẠN (&lt;10% SLA)
                    </span>
                <?php elseif (($ticket['calculated_sla_status'] ?? '') === 'WARNING'): ?>
                    <span class="badge px-3 py-1" style="background:#FFF4E0; color:#974F0C; border:1px solid #FF991F; border-radius:999px; font-size:0.8rem;">
                        <i class="bi bi-hourglass-split me-1"></i>CẢNH BÁO SLA (&lt;30%)
                    </span>
                <?php endif; ?>
            </div>

            <!-- Title -->
            <h3 class="fw-bold mb-2" style="color:#172B4D;"><?= htmlspecialchars($ticket['title'] ?? 'Không có tiêu đề') ?></h3>

            <!-- Meta info -->
            <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                <div><i class="bi bi-person-fill me-1 text-secondary"></i>Người gửi: <strong style="color:#172B4D;"><?= htmlspecialchars($ticket['requester_name'] ?? 'N/A') ?></strong></div>
                <div class="text-secondary">•</div>
                <div><i class="bi bi-calendar3 me-1 text-secondary"></i>Khởi tạo: <?= !empty($ticket['created_at']) ? date('d/m/Y H:i', strtotime($ticket['created_at'])) : 'N/A' ?></div>
                <div class="text-secondary">•</div>
                <div><i class="bi bi-folder2 me-1 text-secondary"></i>Danh mục: <strong style="color:#172B4D;"><?= htmlspecialchars($ticket['category_name'] ?? 'Chưa phân loại') ?></strong></div>
            </div>
        </div>
        <!-- Back button -->
        <a href="<?= $baseUrl ?>/tickets" class="btn btn-outline-secondary fw-semibold px-3 flex-shrink-0">
            <i class="bi bi-arrow-left me-1"></i>Về danh sách
        </a>
    </div>
</div>

<!-- BANNER: TICKET TRÙNG LẶP (RULE 24) -->
<?php if (!empty($ticket['duplicate_of_id'])): ?>
    <div class="alert d-flex align-items-center gap-2 mb-4 py-2 px-3 shadow-sm"
         style="background:#F4F5F7; border:1px solid #DFE1E6; border-radius:8px;">
        <i class="bi bi-copy fs-4 text-secondary"></i>
        <div>
            <strong>Ticket Trùng Lặp (Duplicate):</strong> Yêu cầu này được đánh dấu trùng lặp với ticket
            <a href="<?= $baseUrl ?>/tickets/<?= $ticket['duplicate_of_id'] ?>" class="fw-bold text-primary text-decoration-underline">#<?= htmlspecialchars($ticket['duplicate_ticket_code'] ?? $ticket['duplicate_of_id']) ?></a>
            <?php if (!empty($ticket['duplicate_ticket_title'])): ?>
                - "<?= htmlspecialchars($ticket['duplicate_ticket_title'] ?? '') ?>"
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- BANNER: TICKET ĐÃ BỊ HỦY (RULE 25) -->
<?php if ($ticket['status'] === 'CANCELLED'): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2 px-3 shadow-sm" style="border-radius:8px;">
        <i class="bi bi-x-circle-fill fs-4 text-danger"></i>
        <div>
            <strong>Yêu Cầu Này Đã Bị Hủy:</strong> <?= htmlspecialchars($ticket['cancelled_reason'] ?? 'Không có lý do chi tiết') ?>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">

    <!-- LEFT COLUMN: Content, Results, Activity -->
    <div class="col-12 col-xl-8">

        <!-- RESOLVED CONFIRMATION BANNER (RULE 20, 21) -->
        <?php if ($ticket['status'] === 'RESOLVED' && ($isRequester || Auth::isAdmin())): ?>
            <div class="alert border-2 border-success shadow-sm mb-4 p-4" style="background:#EBFBF0; border-radius:10px;">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-patch-check-fill text-success fs-1"></i>
                    <div class="flex-1">
                        <h5 class="fw-bold mb-1" style="color:#006644;">Kỹ thuật viên đã xử lý xong yêu cầu của bạn!</h5>
                        <p class="mb-3 small" style="color:#172B4D;">Vui lòng kiểm tra kết quả xử lý và bấm <strong>"Xác nhận hoàn thành"</strong> để đóng ticket, hoặc bấm <strong>"Chưa hoàn thành"</strong> nếu lỗi vẫn chưa được xử lý triệt để.</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmCloseModal">
                                <i class="bi bi-check-lg me-1"></i>Xác nhận Đã hoàn thành (Closed)
                            </button>
                            <button type="button" class="btn btn-outline-danger fw-bold bg-white" data-bs-toggle="modal" data-bs-target="#reopenModal">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Chưa hoàn thành (Mở lại yêu cầu)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- RESOLUTION DETAILS -->
        <?php if (!empty($ticket['resolution'])): ?>
            <div class="card mb-4 border-0 shadow-sm" style="border-left:4px solid #00875A !important; border-radius:10px !important;">
                <div class="card-header py-3 d-flex align-items-center gap-2 border-0"
                     style="background:#EBFBF0; border-radius:10px 10px 0 0;">
                    <div style="background:#00875A; border-radius:8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-clipboard2-check-fill text-white"></i>
                    </div>
                    <span class="fw-bold" style="color:#006644;">Nội Dung &amp; Giải Pháp Xử Lý (Resolution)</span>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 rounded-3 text-dark mb-3 fw-medium" style="background:#F4F5F7; white-space:pre-line; line-height:1.6;">
                        <?= htmlspecialchars($ticket['resolution'] ?? '') ?>
                    </div>
                    <div class="row g-2 small text-muted">
                        <?php if (!empty($ticket['root_cause'])): ?>
                            <div class="col-md-6">
                                <strong style="color:#172B4D;">Nguyên nhân gốc (Root cause):</strong>
                                <div class="text-secondary mt-1"><?= htmlspecialchars($ticket['root_cause'] ?? '') ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['solution'])): ?>
                            <div class="col-md-6">
                                <strong style="color:#172B4D;">Biện pháp khắc phục (Solution):</strong>
                                <div class="text-secondary mt-1"><?= htmlspecialchars($ticket['solution'] ?? '') ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- CSAT RATING (RULE 49) -->
        <?php if ($ticket['status'] === 'CLOSED'): ?>
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-radius:10px 10px 0 0;">
                    <span class="fw-bold text-dark"><i class="bi bi-star-fill text-warning me-2"></i>Đánh Giá Chất Lượng Hỗ Trợ (CSAT)</span>
                    <span class="badge bg-light text-muted border" style="border-radius:999px;">Sau khi đóng ticket</span>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($rating)): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="fs-2 text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star-fill <?= $i <= $rating['score'] ? 'text-warning' : 'text-secondary opacity-25' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6">Đã đánh giá: <?= (int)($rating['score'] ?? 5) ?> / 5 sao</div>
                                <div class="text-muted small">Bởi <?= htmlspecialchars($rating['user_name'] ?? 'Người dùng') ?> vào ngày <?= !empty($rating['created_at']) ? date('d/m/Y H:i', strtotime($rating['created_at'])) : '' ?></div>
                            </div>
                        </div>
                        <?php if (!empty($rating['comment'])): ?>
                            <div class="mt-3 p-3 rounded-3 text-secondary small fst-italic" style="background:#F4F5F7; border-left:3px solid #FF991F;">
                                <i class="bi bi-quote me-1"></i><?= htmlspecialchars($rating['comment'] ?? '') ?>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($isRequester || Auth::isAdmin()): ?>
                        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/rate" method="POST">
                            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                            <p class="text-dark small mb-3">Bạn cảm thấy dịch vụ hỗ trợ của kỹ thuật viên đối với yêu cầu này như thế nào? Vui lòng chia sẻ cảm nhận để chúng tôi cải thiện chất lượng dịch vụ:</p>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Chọn số sao đánh giá:</label>
                                <div class="d-flex gap-3 rating-stars-group">
                                    <?php for ($s = 5; $s >= 1; $s--): ?>
                                        <div class="form-check form-check-inline m-0">
                                            <input class="form-check-input visually-hidden" type="radio" name="score" id="star_<?= $s ?>" value="<?= $s ?>" <?= $s === 5 ? 'checked' : '' ?>>
                                            <label class="form-check-label cursor-pointer p-2 border rounded-3 text-center d-flex flex-column align-items-center" for="star_<?= $s ?>" style="min-width:70px;">
                                                <span class="fs-4 text-warning"><i class="bi bi-star-fill"></i></span>
                                                <span class="fw-bold small mt-1 text-dark"><?= $s ?> Sao</span>
                                            </label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase">Ý kiến nhận xét thêm (Tùy chọn):</label>
                                <textarea name="comment" class="form-control" rows="2" placeholder="Ví dụ: KTV nhiệt tình, giải quyết sự cố rất nhanh..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning fw-bold text-dark px-4 shadow-sm">
                                <i class="bi bi-send-fill me-1"></i>Gửi Đánh Giá Dịch Vụ
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-muted small">Người dùng chưa gửi đánh giá cho ticket này.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- MÔ TẢ CHI TIẾT -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2" style="border-radius:10px 10px 0 0;">
                <div style="background:#EAF0FB; border-radius:8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                    <i class="bi bi-file-text-fill text-primary"></i>
                </div>
                <span class="fw-bold text-dark">Mô Tả Chi Tiết Yêu Cầu</span>
            </div>
            <div class="card-body p-4">
                <div class="text-dark mb-4" style="white-space:pre-line; line-height:1.7; font-size:0.95rem; color:#172B4D;">
                    <?= htmlspecialchars($ticket['description'] ?? 'Không có nội dung mô tả.') ?>
                </div>

                <div class="row g-3 p-3 rounded-3 small" style="background:#F4F5F7;">
                    <div class="col-sm-6">
                        <span class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Vị trí / Phòng ban:</span>
                        <div class="fw-semibold text-dark mt-1"><?= htmlspecialchars($ticket['location'] ?? 'Chưa cập nhật') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted"><i class="bi bi-telephone-fill text-primary me-1"></i>Thông tin liên hệ:</span>
                        <div class="fw-semibold text-dark mt-1"><?= htmlspecialchars($ticket['contact_info'] ?? 'Chưa cập nhật') ?></div>
                    </div>
                </div>

                <!-- ATTACHMENTS -->
                <?php if (!empty($attachments)): ?>
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <label class="fw-bold text-dark small mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-paperclip text-primary"></i>
                                Tệp đính kèm (<?= count($attachments) ?>)
                            </label>
                            <span class="text-muted" style="font-size:0.75rem;">Hỗ trợ xem trực tiếp Word, Excel, PDF, Hình ảnh, Text</span>
                        </div>
                        <div class="row g-2.5">
                            <?php foreach ($attachments as $att): ?>
                                <?php
                                $attId = (int)$att['id'];
                                $fileName = $att['file_name'] ?? 'Tệp đính kèm';
                                $fileSizeKb = round(((float)($att['file_size'] ?? 0)) / 1024, 1);
                                $uploaderName = $att['uploader_name'] ?? 'Hệ thống';
                                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                                $badgeBg = '#EFF8FF';
                                $badgeColor = '#175CD3';
                                $iconClass = 'bi-file-earmark-fill';
                                $canDirectPreview = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'pdf', 'docx', 'doc', 'xlsx', 'xls', 'csv', 'txt', 'log', 'json', 'sql', 'md']);

                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                                    $iconClass = 'bi-file-earmark-image-fill';
                                    $badgeBg = '#FDF2FA';
                                    $badgeColor = '#C11574';
                                } elseif ($ext === 'pdf') {
                                    $iconClass = 'bi-file-earmark-pdf-fill';
                                    $badgeBg = '#FEF3F2';
                                    $badgeColor = '#B42318';
                                } elseif (in_array($ext, ['docx', 'doc'])) {
                                    $iconClass = 'bi-file-earmark-word-fill';
                                    $badgeBg = '#EFF8FF';
                                    $badgeColor = '#175CD3';
                                } elseif (in_array($ext, ['xlsx', 'xls', 'csv'])) {
                                    $iconClass = 'bi-file-earmark-excel-fill';
                                    $badgeBg = '#ECFDF3';
                                    $badgeColor = '#027A48';
                                } elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                                    $iconClass = 'bi-file-earmark-zip-fill';
                                    $badgeBg = '#FFF8EB';
                                    $badgeColor = '#B54708';
                                } elseif (in_array($ext, ['txt', 'log', 'json', 'sql', 'md', 'html', 'xml'])) {
                                    $iconClass = 'bi-file-earmark-code-fill';
                                    $badgeBg = '#F8F9FA';
                                    $badgeColor = '#344054';
                                }
                                ?>
                                <div class="col-sm-6 col-md-4">
                                    <div class="attachment-item-card p-2.5 border rounded-3 bg-white shadow-sm d-flex flex-column justify-content-between h-100" 
                                         style="border-color:#DFE1E6 !important; transition: all .2s ease-in-out;">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" 
                                                 style="background:<?= $badgeBg ?>; color:<?= $badgeColor ?>; width:38px; height:38px;">
                                                <i class="bi <?= $iconClass ?> fs-5"></i>
                                            </div>
                                            <div class="overflow-hidden flex-grow-1" style="min-width:0;">
                                                <div class="fw-bold small text-truncate text-dark" title="<?= htmlspecialchars($fileName) ?>">
                                                    <?= htmlspecialchars($fileName) ?>
                                                </div>
                                                <div class="text-muted" style="font-size:0.72rem;">
                                                    <?= $fileSizeKb ?> KB • <?= htmlspecialchars($uploaderName) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1" style="border-color:#F4F5F7 !important;">
                                            <div class="d-flex align-items-center gap-1">
                                                <?php if ($canDirectPreview): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-primary py-1 px-2.5 rounded-2 d-flex align-items-center gap-1 fw-semibold preview-file-btn"
                                                            style="font-size:0.75rem;"
                                                            data-file-id="<?= $attId ?>"
                                                            data-file-name="<?= htmlspecialchars($fileName) ?>"
                                                            data-file-size="<?= $fileSizeKb ?> KB"
                                                            data-file-ext="<?= $ext ?>"
                                                            data-view-url="<?= $baseUrl ?>/attachments/<?= $attId ?>/view"
                                                            data-download-url="<?= $baseUrl ?>/attachments/<?= $attId ?>/download"
                                                            data-uploader="<?= htmlspecialchars($uploaderName) ?>"
                                                            data-date="<?= !empty($att['created_at']) ? date('d/m/Y H:i', strtotime($att['created_at'])) : '' ?>"
                                                            title="Xem file trực tiếp trên trình duyệt">
                                                        <i class="bi bi-eye-fill"></i> Xem trực tiếp
                                                    </button>
                                                <?php endif; ?>
                                                <a href="<?= $baseUrl ?>/attachments/<?= $attId ?>/view" target="_blank"
                                                   class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-2"
                                                   style="font-size:0.75rem;" title="Mở trong tab mới">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            </div>
                                            <a href="<?= $baseUrl ?>/attachments/<?= $attId ?>/download" 
                                               class="btn btn-sm btn-light border py-1 px-2.5 rounded-2 text-dark fw-medium d-flex align-items-center gap-1"
                                               style="font-size:0.75rem;" title="Tải xuống tệp gốc về máy">
                                                <i class="bi bi-download"></i> Tải về
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CHECKLIST / SUB-TASKS -->
        <?php
        $totalChecklist = count($checklist ?? []);
        $completedChecklist = count(array_filter($checklist ?? [], fn($i) => (bool)$i['is_completed']));
        $checklistPercent = $totalChecklist > 0 ? round(($completedChecklist / $totalChecklist) * 100) : 0;
        ?>
        <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-radius:10px 10px 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <div style="background:#EAF0FB; border-radius:8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-check2-square text-primary"></i>
                    </div>
                    <span class="fw-bold text-dark">Đầu Việc Kỹ Thuật (Checklist / Sub-tasks)</span>
                    <span class="badge bg-light text-muted border ms-1" id="checklist-counter" style="border-radius:999px;">
                        <?= $completedChecklist ?> / <?= $totalChecklist ?> hoàn thành (<?= $checklistPercent ?>%)
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="progress mb-3" style="height:6px; border-radius:999px;">
                    <div class="progress-bar bg-success" id="checklist-progress-bar" role="progressbar" style="width:<?= $checklistPercent ?>%; border-radius:999px;"></div>
                </div>
                <div id="checklist-container" class="d-flex flex-column gap-2 mb-3">
                    <?php if (empty($checklist)): ?>
                        <div class="text-muted small fst-italic py-2" id="checklist-empty-msg">Chưa có đầu việc nào. KTV có thể thêm các bước xử lý cụ thể dưới đây.</div>
                    <?php else: ?>
                        <?php foreach ($checklist as $item): ?>
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 border checklist-item-row"
                                 id="checklist-item-<?= $item['id'] ?>"
                                 style="background:<?= $item['is_completed'] ? '#EBFBF0' : '#FAFBFC' ?>;">
                                <div class="form-check d-flex align-items-center gap-2 m-0">
                                    <input class="form-check-input mt-0 checklist-checkbox" type="checkbox"
                                           data-id="<?= $item['id'] ?>"
                                           <?= $item['is_completed'] ? 'checked' : '' ?>
                                           onchange="toggleChecklist(<?= $item['id'] ?>)">
                                    <label class="form-check-label small <?= $item['is_completed'] ? 'text-decoration-line-through text-muted' : 'text-dark fw-medium' ?>"
                                           id="label-checklist-<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['item_text'] ?? '') ?>
                                    </label>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($item['completed_by_name'])): ?>
                                        <span class="badge bg-white text-muted border small" style="font-size:0.68rem; border-radius:999px;">Đã xong bởi <?= htmlspecialchars($item['completed_by_name'] ?? '') ?></span>
                                    <?php endif; ?>
                                    <?php if ($canManageTicket): ?>
                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none"
                                                onclick="deleteChecklist(<?= $item['id'] ?>)" title="Xóa đầu việc">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($canManageTicket): ?>
                    <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/checklist" method="POST"
                          class="d-flex gap-2" id="addChecklistForm" onsubmit="return submitChecklist(event)">
                        <input type="text" name="item_text" id="new_item_text"
                               class="form-control form-control-sm"
                               style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;"
                               placeholder="Thêm bước kỹ thuật cần kiểm tra / xử lý (Enter để thêm)..." required>
                        <button type="submit" class="btn btn-outline-primary btn-sm px-3 fw-semibold text-nowrap">
                            <i class="bi bi-plus-lg me-1"></i>Thêm
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- LINKED ISSUES -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-radius:10px 10px 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <div style="background:#EAF0FB; border-radius:8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-link-45deg text-primary"></i>
                    </div>
                    <span class="fw-bold text-dark">Sự Cố Liên Quan (Linked Issues — <?= count($linkedIssues ?? []) ?>)</span>
                </div>
                <?php if ($canManageTicket): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#linkIssueModal">
                        <i class="bi bi-link me-1"></i>Liên kết sự cố
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($linkedIssues)): ?>
                    <div class="p-4 text-center text-muted small fst-italic">
                        <i class="bi bi-link text-secondary opacity-50 fs-3 d-block mb-2"></i>
                        Ticket này hiện chưa được liên kết với sự cố nào khác.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead style="background:#F4F5F7;">
                                <tr>
                                    <th class="ps-3" style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Mối quan hệ</th>
                                    <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Mã Ticket</th>
                                    <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Tiêu đề</th>
                                    <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Mức độ</th>
                                    <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Trạng thái</th>
                                    <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Phụ trách</th>
                                    <?php if ($canManageTicket): ?><th class="text-end pe-3" style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Thao tác</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linkedIssues as $link): ?>
                                    <?php
                                    $linkMap = [
                                        'relates_to'    => ['label' => 'Liên quan tới',     'color' => 'secondary'],
                                        'blocks'        => ['label' => 'Chặn tiến độ của', 'color' => 'danger'],
                                        'is_blocked_by' => ['label' => 'Bị chặn bởi',       'color' => 'warning'],
                                        'causes'        => ['label' => 'Gây ra sự cố cho',  'color' => 'danger'],
                                    ];
                                    $linkMeta = $linkMap[$link['link_type']] ?? ['label' => $link['link_type'], 'color' => 'secondary'];
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-<?= $linkMeta['color'] ?>-subtle text-<?= $linkMeta['color'] ?> border border-<?= $linkMeta['color'] ?> py-1" style="border-radius:999px;">
                                                <?= $linkMeta['label'] ?>
                                            </span>
                                        </td>
                                        <td class="font-monospace fw-bold">
                                            <a href="<?= $baseUrl ?>/tickets/<?= $link['target_id'] ?>" class="text-decoration-none text-primary">
                                                <?= htmlspecialchars($link['ticket_code'] ?? 'TICKET') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= $baseUrl ?>/tickets/<?= $link['target_id'] ?>" class="text-dark text-decoration-none fw-semibold">
                                                <?= htmlspecialchars($link['title'] ?? 'Liên kết') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge px-2 py-1" style="background-color:<?= $link['priority_color'] ?? '#0ea5e9' ?>; color:#fff; font-size:0.7rem; border-radius:999px;">
                                                <?= htmlspecialchars($link['priority_name'] ?? 'Thường') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border" style="border-radius:999px;"><?= htmlspecialchars($link['status'] ?? 'OPEN') ?></span>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($link['assignee_name'] ?? 'Chưa gán') ?></td>
                                        <?php if ($canManageTicket): ?>
                                            <td class="text-end pe-3">
                                                <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/unlink-issue/<?= $link['link_id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn gỡ liên kết này?')">
                                                    <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Gỡ liên kết">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ACTIVITY TABS: COMMENTS, WORKLOG, HISTORY -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-header bg-white py-2 border-bottom" style="border-radius:10px 10px 0 0;">
                <ul class="nav nav-tabs card-header-tabs" id="jiraActivityTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark small" id="comments-tab"
                                data-bs-toggle="tab" data-bs-target="#comments-pane" type="button" role="tab">
                            <i class="bi bi-chat-square-text-fill me-1 text-primary"></i>Bình luận (<?= count($comments) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark small" id="worklog-tab"
                                data-bs-toggle="tab" data-bs-target="#worklog-pane" type="button" role="tab">
                            <i class="bi bi-stopwatch-fill me-1 text-success"></i>Worklog (<?= count($worklogs ?? []) ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark small" id="history-tab"
                                data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">
                            <i class="bi bi-clock-history me-1 text-secondary"></i>Lịch sử (<?= count($history) ?>)
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4 tab-content" id="jiraActivityTabsContent">

                <!-- TAB 1: COMMENTS -->
                <div class="tab-pane fade show active" id="comments-pane" role="tabpanel">
                    <div class="comments-stream mb-4">
                        <?php if (empty($comments)): ?>
                            <div class="text-center py-5 text-muted">
                                <div style="width:56px;height:56px;background:#F4F5F7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                                    <i class="bi bi-chat-dots fs-3 text-secondary opacity-50"></i>
                                </div>
                                <div class="fw-semibold mb-1">Chưa có phản hồi nào</div>
                                <div class="small">Hãy là người đầu tiên phản hồi trên ticket này.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                                <?php $isInternal = (bool)$c['is_internal']; ?>
                                <div class="d-flex gap-3 mb-4">
                                    <!-- Avatar -->
                                    <div class="flex-shrink-0">
                                        <div class="user-avatar-circle text-white fw-bold d-flex align-items-center justify-content-center"
                                             style="width:36px;height:36px;font-size:0.8rem;border-radius:50%;background:<?= $isInternal ? 'linear-gradient(135deg,#d97706,#b45309)' : 'linear-gradient(135deg,#2563eb,#1d4ed8)' ?>;">
                                            <?= mb_strtoupper(mb_substr($c['author_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?>
                                        </div>
                                    </div>
                                    <!-- Comment Body -->
                                    <div class="flex-1" style="min-width:0;">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="fw-bold text-dark small"><?= htmlspecialchars($c['author_name'] ?? 'Người dùng') ?></span>
                                            <?php if ($isInternal): ?>
                                                <span class="badge fw-bold border" style="background:#FFF4E0; color:#974F0C; border-color:#FF991F !important; border-radius:999px; font-size:0.68rem;">
                                                    <i class="bi bi-lock-fill me-1"></i>Ghi chú nội bộ
                                                </span>
                                            <?php endif; ?>
                                            <span class="text-muted ms-auto" style="font-size:0.75rem;"><?= !empty($c['created_at']) ? date('d/m/Y H:i', strtotime($c['created_at'])) : '' ?></span>
                                        </div>
                                        <div class="p-3 rounded-3 small text-dark"
                                             style="background:<?= $isInternal ? '#FFF9E0' : '#F4F5F7' ?>; border-left:3px solid <?= $isInternal ? '#FF991F' : '#DFE1E6' ?>; white-space:pre-line; line-height:1.6;">
                                            <?php
                                            $contentHtml = htmlspecialchars($c['content'] ?? '');
                                            $contentHtml = preg_replace('/@([a-zA-Z0-9_.-]+)/', '<span class="text-primary fw-bold bg-primary-subtle px-1 rounded">@$1</span>', $contentHtml);
                                            echo $contentHtml;
                                            ?>
                                            <?php
                                            $commentAttachments = array_filter($attachments ?? [], fn($a) => (int)($a['comment_id'] ?? 0) === (int)$c['id']);
                                            ?>
                                            <?php if (!empty($commentAttachments)): ?>
                                                <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2">
                                                    <?php foreach ($commentAttachments as $cAtt): ?>
                                                        <?php
                                                        $cAttId = (int)$cAtt['id'];
                                                        $cFileName = $cAtt['file_name'] ?? 'Tệp';
                                                        $cSizeKb = round(((float)($cAtt['file_size'] ?? 0)) / 1024, 1);
                                                        $cExt = strtolower(pathinfo($cFileName, PATHINFO_EXTENSION));
                                                        ?>
                                                        <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-2 border bg-white small shadow-xs" style="border-color:#DFE1E6 !important;">
                                                            <i class="bi bi-paperclip text-primary"></i>
                                                            <span class="text-truncate fw-medium text-dark" style="max-width:200px;" title="<?= htmlspecialchars($cFileName) ?>"><?= htmlspecialchars($cFileName) ?></span>
                                                            <span class="text-muted" style="font-size:0.7rem;">(<?= $cSizeKb ?> KB)</span>
                                                            <button type="button" class="btn btn-sm btn-link p-0 text-primary fw-semibold preview-file-btn ms-1"
                                                                    data-file-id="<?= $cAttId ?>"
                                                                    data-file-name="<?= htmlspecialchars($cFileName) ?>"
                                                                    data-file-size="<?= $cSizeKb ?> KB"
                                                                    data-file-ext="<?= $cExt ?>"
                                                                    data-view-url="<?= $baseUrl ?>/attachments/<?= $cAttId ?>/view"
                                                                    data-download-url="<?= $baseUrl ?>/attachments/<?= $cAttId ?>/download"
                                                                    data-uploader="<?= htmlspecialchars($cAtt['uploader_name'] ?? 'Hệ thống') ?>"
                                                                    data-date="<?= !empty($cAtt['created_at']) ? date('d/m/Y H:i', strtotime($cAtt['created_at'])) : '' ?>"
                                                                    title="Xem trực tiếp">
                                                                <i class="bi bi-eye"></i> Xem
                                                            </button>
                                                            <a href="<?= $baseUrl ?>/attachments/<?= $cAttId ?>/download" class="btn btn-sm btn-link p-0 text-secondary ms-1" title="Tải về">
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- COMMENT FORM -->
                    <?php if ($ticket['status'] !== 'CLOSED' && $ticket['status'] !== 'CANCELLED'): ?>
                        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/comment" method="POST" enctype="multipart/form-data" class="pt-3 border-top">
                            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                            <div class="mb-3">
                                <label for="comment_content" class="form-label fw-bold text-dark small">Nhập nội dung phản hồi:</label>
                                <textarea class="form-control" id="comment_content" name="content" rows="3"
                                          style="border:1.5px solid #DFE1E6; border-radius:8px; background:#FAFBFC;"
                                          placeholder="Nhập câu trả lời hoặc trao đổi thêm..." required></textarea>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <input type="file" name="attachment" id="comment_attachment" class="form-control form-control-sm"
                                               style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;">
                                    </div>
                                    <?php if ($canManageTicket): ?>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_internal" name="is_internal" value="1">
                                            <label class="form-check-label fw-bold small" for="is_internal" style="color:#FF991F;">
                                                <i class="bi bi-lock-fill me-1"></i>Ghi chú nội bộ (Internal Note)
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary fw-bold px-4">
                                    <i class="bi bi-send-fill me-1"></i>Gửi phản hồi
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- TAB 2: WORKLOG -->
                <div class="tab-pane fade" id="worklog-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded-3 border"
                         style="background:#F4F5F7;">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Tổng thời gian đã làm việc:</div>
                            <div class="fs-4 fw-bold text-dark font-monospace"><?= htmlspecialchars($totalWorklogFormatted ?? '0m') ?> <span class="fs-6 fw-normal text-muted">(<?= (int)($totalWorklogMinutes ?? 0) ?> phút)</span></div>
                        </div>
                        <?php if ($canManageTicket && !in_array($ticket['status'], ['CLOSED', 'CANCELLED'])): ?>
                            <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#logWorkModal">
                                <i class="bi bi-plus-lg me-1"></i>Ghi nhận giờ làm
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($worklogs)): ?>
                        <div class="text-center py-4 text-muted small fst-italic">Chưa có phiên làm việc nào được ghi nhận.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead style="background:#F4F5F7;">
                                    <tr>
                                        <th class="ps-3" style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Người thực hiện</th>
                                        <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Thời gian</th>
                                        <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Ngày ghi nhận</th>
                                        <th style="font-size:11px; text-transform:uppercase; color:#5E6C84; font-weight:700;">Mô tả công việc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($worklogs as $wl): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="user-avatar-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                         style="width:26px;height:26px;font-size:0.72rem;border-radius:50%;">
                                                        <?= mb_strtoupper(mb_substr($wl['full_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?>
                                                    </div>
                                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($wl['full_name'] ?? '') ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge border border-success font-monospace fw-bold px-2 py-1"
                                                      style="background:#EBFBF0; color:#006644; border-radius:999px;">
                                                    <?= htmlspecialchars($wl['time_spent_formatted'] ?? '0m') ?>
                                                </span>
                                            </td>
                                            <td class="text-muted font-monospace"><?= !empty($wl['logged_date']) ? date('d/m/Y', strtotime($wl['logged_date'])) : '' ?></td>
                                            <td class="text-dark"><?= htmlspecialchars($wl['description'] ?? 'Không có mô tả') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 3: AUDIT TIMELINE -->
                <div class="tab-pane fade" id="history-pane" role="tabpanel">
                    <div class="timeline" style="position:relative; padding-left:24px;">
                        <?php foreach ($history as $h): ?>
                            <div class="timeline-item mb-3 position-relative" style="padding-left:16px; border-left:2px solid #DFE1E6;">
                                <div style="position:absolute; left:-7px; top:4px; width:12px; height:12px; background:#0052CC; border-radius:50%; border:2px solid #fff;"></div>
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                                    <span class="fw-bold text-dark small">
                                        <?= htmlspecialchars($h['user_name'] ?? 'Hệ thống') ?>
                                        <span class="text-muted fw-normal ms-1">thao tác:</span>
                                        <span class="badge bg-light text-dark border ms-1" style="border-radius:6px;"><?= htmlspecialchars($h['action'] ?? '') ?></span>
                                    </span>
                                    <span class="text-muted font-monospace" style="font-size:0.75rem;"><?= !empty($h['created_at']) ? date('d/m/Y H:i:s', strtotime($h['created_at'])) : '' ?></span>
                                </div>
                                <?php if (!empty($h['note'])): ?>
                                    <div class="text-secondary small mt-1"><?= htmlspecialchars($h['note'] ?? '') ?></div>
                                <?php endif; ?>
                                <?php if (!empty($h['old_value']) || !empty($h['new_value'])): ?>
                                    <div class="text-muted small mt-1 font-monospace" style="font-size:0.775rem;">
                                        <span class="badge" style="background:#FFEBE6; color:#DE350B; border-radius:6px;"><?= htmlspecialchars($h['old_value'] ?? 'None') ?></span>
                                        <i class="bi bi-arrow-right mx-1"></i>
                                        <span class="badge" style="background:#EBFBF0; color:#006644; border-radius:6px;"><?= htmlspecialchars($h['new_value'] ?? 'None') ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /LEFT COLUMN -->

    <!-- RIGHT COLUMN: SLA + Actions + Sidebar -->
    <div class="col-12 col-xl-4">

        <!-- SLA WIDGET -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
            <div class="card-header py-3 d-flex justify-content-between align-items-center text-white"
                 style="background:#0052CC;">
                <span class="fw-bold"><i class="bi bi-stopwatch-fill me-2"></i>Cam Kết SLA Snapshot</span>
                <span class="badge bg-white fw-bold" style="color:#0052CC; border-radius:999px;">
                    <?= $ticket['sla_value'] ?> <?= $ticket['sla_unit'] === 'HOUR' ? 'Giờ' : 'Ngày' ?>
                </span>
            </div>
            <div class="card-body p-4">
                <div class="text-center py-2">
                    <div class="text-muted small fw-bold mb-1" style="text-transform:uppercase; letter-spacing:.05em;">THỜI GIAN CÒN LẠI:</div>
                    <?php if (in_array($ticket['status'], ['RESOLVED', 'CLOSED'])): ?>
                        <?php
                        $metSlaDetail = true;
                        if (!empty($ticket['resolved_at']) && !empty($ticket['sla_due_at'])) {
                            $metSlaDetail = (strtotime($ticket['resolved_at']) <= strtotime($ticket['sla_due_at']));
                        }
                        ?>
                        <?php if ($metSlaDetail): ?>
                            <div class="text-success fs-5 fw-bold mb-2"><i class="bi bi-check-circle-fill me-1"></i>SLA Đã Dừng (Đạt SLA)</div>
                        <?php else: ?>
                            <div class="text-danger fs-5 fw-bold mb-2"><i class="bi bi-clock-history me-1"></i>SLA Đã Dừng (Trễ SLA)</div>
                        <?php endif; ?>
                    <?php elseif (in_array($ticket['status'], ['CANCELLED', 'DUPLICATE'])): ?>
                        <div class="text-muted fs-5 fw-bold mb-2"><i class="bi bi-slash-circle me-1"></i>SLA Đã Dừng</div>
                    <?php else: ?>
                        <div class="sla-live-timer sla-countdown-display mb-2"
                             data-due="<?= htmlspecialchars($ticket['sla_due_at'] ?? '') ?>"
                             data-status="<?= htmlspecialchars($ticket['status'] ?? '') ?>"
                             data-paused="<?= (in_array($ticket['status'] ?? '', ['WAITING_USER', 'WAITING_VENDOR'])) ? '1' : '0' ?>"
                             data-paused-seconds="<?= (int)($ticket['sla_paused_seconds'] ?? 0) ?>">
                            <?= !empty($ticket['sla_due_at']) ? date('d/m/Y H:i', strtotime($ticket['sla_due_at'])) : 'N/A' ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="pt-3 border-top mt-2 small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Bắt đầu tính SLA:</span>
                        <span class="fw-bold text-dark"><?= !empty($ticket['sla_started_at']) ? date('d/m/Y H:i', strtotime($ticket['sla_started_at'])) : 'N/A' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Hạn chót cam kết (Due):</span>
                        <span class="fw-bold text-danger"><?= !empty($ticket['sla_due_at']) ? date('d/m/Y H:i', strtotime($ticket['sla_due_at'])) : 'N/A' ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Đã tạm dừng (Chờ User):</span>
                        <span class="fw-bold" style="color:#FF991F;"><?= round(((float)($ticket['sla_paused_seconds'] ?? 0)) / 60, 1) ?> phút</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI IT DESK COPILOT -->
        <?php if ($canManageTicket): ?>
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px; overflow:hidden;">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center"
                     style="background:#0052CC; color:#fff;">
                    <span class="fw-bold small d-flex align-items-center gap-2">
                        <i class="bi bi-robot fs-5 text-warning"></i>AI IT Desk Copilot
                    </span>
                    <button type="button" id="btnRunCopilot"
                            class="btn btn-light btn-sm py-0 px-2 fw-bold text-primary"
                            style="font-size:11px; border-radius:999px;">
                        <i class="bi bi-stars me-1 text-warning"></i>Phân Tích Ngay
                    </button>
                </div>
                <div class="card-body p-3 small" id="copilotContent">
                    <div class="text-muted text-center py-2" id="copilotPlaceholder">
                        <i class="bi bi-stars text-primary fs-4 d-block mb-1"></i>
                        <span>Bấm <strong>Phân Tích Ngay</strong> để AI tóm tắt sự cố, gợi ý các bước xử lý và soạn phản hồi mẫu.</span>
                    </div>
                    <div id="copilotResult" class="d-none">
                        <div class="mb-3">
                            <span class="fw-bold text-dark d-block mb-1"><i class="bi bi-card-text text-primary me-1"></i>Tóm Tắt Tình Trạng:</span>
                            <div class="p-2 rounded border text-secondary" id="aiSummary" style="background:#F4F5F7; font-size:12px; line-height:1.5;"></div>
                        </div>
                        <div class="mb-3">
                            <span class="fw-bold text-dark d-block mb-1"><i class="bi bi-check2-square text-success me-1"></i>Checklist Các Bước Xử Lý:</span>
                            <ul class="list-unstyled ps-1 mb-0" id="aiSteps" style="font-size:12px;"></ul>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark"><i class="bi bi-chat-quote-fill text-info me-1"></i>Soạn Tin Mẫu Trả Lời:</span>
                                <button type="button" id="btnApplyDraft" class="btn btn-outline-primary btn-sm py-0 px-1 fw-bold" style="font-size:10.5px; border-radius:999px;">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>Dán vào ô phản hồi
                                </button>
                            </div>
                            <div class="p-2 rounded border text-muted font-monospace" id="aiDraft" style="background:#F4F5F7; font-size:11.5px; white-space:pre-wrap;"></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ITIL APPROVAL DECISION BOX -->
        <?php if (!empty($ticket['approval_status']) && $ticket['approval_status'] === 'PENDING'): ?>
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px; border-top:3px solid #FF991F !important;">
                <div class="card-header py-3 d-flex justify-content-between align-items-center"
                     style="background:#FFF4E0; border-radius:10px 10px 0 0;">
                    <span class="fw-bold" style="color:#974F0C;"><i class="bi bi-shield-check me-2"></i>Yêu Cầu Chờ Phê Duyệt</span>
                    <span class="badge rounded-pill fw-bold" style="background:#FF991F; color:#fff;">PENDING</span>
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-2">Yêu cầu dịch vụ này cần quản lý hoặc bộ phận liên quan phê duyệt trước khi IT triển khai.</p>
                    <?php if (!empty($ticket['approved_by_name'])): ?>
                        <div class="small mb-3"><strong>Người phê duyệt:</strong> <?= htmlspecialchars($ticket['approved_by_name'] ?? '') ?></div>
                    <?php endif; ?>
                    <?php if (Auth::isManager() || Auth::isAdmin() || (int)($ticket['approved_by'] ?? 0) === Auth::id()): ?>
                        <button type="button" class="btn btn-warning w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#ticketApprovalModal" style="border-radius:8px;">
                            <i class="bi bi-check2-circle me-1"></i>Xét Duyệt Yêu Cầu Này
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- TECHNICAL ACTIONS (DÀNH CHO IT/MANAGER) -->
        <?php if ($canManageTicket && !in_array($ticket['status'], ['CLOSED', 'CANCELLED', 'DUPLICATE'])): ?>
            <style>
            .action-tile {
                background: #FFFFFF;
                border-color: #DFE1E6 !important;
                transition: all 0.15s ease;
                min-height: 68px;
            }
            .action-tile:hover {
                background: #F4F5F7 !important;
                border-color: #4C9AFF !important;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(9, 30, 66, 0.08);
            }
            .action-list-btn {
                background: #FAFBFC;
                border-color: #EBECF0 !important;
                transition: all 0.15s ease;
            }
            .action-list-btn:hover {
                background: #F4F5F7;
                border-color: #C1C7D0 !important;
            }
            </style>

            <div class="card mb-4 border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
                <!-- Header -->
                <div class="card-header py-3 px-3 border-bottom d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(135deg, #F4F5F7 0%, #EAF0FB 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <div style="background:#0052CC; border-radius:8px; width:28px; height:28px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="bi bi-sliders text-white" style="font-size:0.85rem;"></i>
                        </div>
                        <span class="fw-bold text-dark" style="font-size:0.9rem;">Bàn Điều Phối &amp; Xử Lý</span>
                    </div>
                    <span class="badge bg-light text-secondary border font-monospace" style="font-size:0.7rem;">
                        <?= htmlspecialchars($ticket['status']) ?>
                    </span>
                </div>

                <div class="card-body p-3">

                    <!-- PHẦN 1: HÀNH ĐỘNG TRẠNG THÁI CHÍNH (PRIMARY STATUS HERO) -->
                    <div class="mb-3 p-2 rounded-3" style="background:#FAFBFC; border:1px solid #EBECF0;">
                        <div class="text-uppercase fw-bold text-muted mb-2 ps-1" style="font-size:0.68rem; letter-spacing:0.5px;">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Tiến trình xử lý
                        </div>

                        <?php if ($ticket['status'] === 'NEW'): ?>
                            <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/status" method="POST" class="mb-0">
                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="status" value="IN_PROGRESS">
                                <input type="hidden" name="note" value="Kỹ thuật viên bắt đầu xử lý">
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-2" style="border-radius:8px;">
                                    <i class="bi bi-play-circle-fill fs-5"></i> Bắt đầu xử lý phiếu
                                </button>
                            </form>
                        <?php elseif ($ticket['status'] === 'IN_PROGRESS'): ?>
                            <button type="button" class="btn btn-success w-100 fw-bold py-2 shadow-xs mb-2 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#resolveModal" style="border-radius:8px; background:linear-gradient(135deg, #00875A 0%, #00A36C 100%);">
                                <i class="bi bi-check2-circle fs-5"></i> Hoàn thành xử lý (Resolve)
                            </button>

                            <div class="row g-2">
                                <div class="col-6">
                                    <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/status" method="POST" class="mb-0">
                                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="status" value="WAITING_USER">
                                        <input type="hidden" name="note" value="Chờ User phản hồi thông tin (Tạm dừng SLA)">
                                        <button type="submit" class="btn btn-outline-warning w-100 py-1 text-dark fw-semibold small text-truncate" title="Tạm dừng SLA chờ người dùng" style="border-radius:6px; font-size:0.75rem;">
                                            <i class="bi bi-pause-circle me-1 text-warning"></i> Chờ User
                                        </button>
                                    </form>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-info w-100 py-1 fw-semibold small text-truncate text-dark" data-bs-toggle="modal" data-bs-target="#vendorModal" title="Tạm dừng SLA gửi hãng bảo hành" style="border-radius:6px; font-size:0.75rem;">
                                        <i class="bi bi-truck me-1 text-info"></i> Chờ Hãng
                                    </button>
                                </div>
                            </div>
                        <?php elseif (in_array($ticket['status'], ['WAITING_USER', 'WAITING_VENDOR'])): ?>
                            <div class="alert alert-warning py-1 px-2 mb-2 small d-flex align-items-center gap-1 rounded-2" style="font-size:0.75rem;">
                                <i class="bi bi-pause-circle-fill text-warning"></i>
                                <span>Đang tạm dừng SLA (<?= $ticket['status'] === 'WAITING_USER' ? 'Chờ User' : 'Chờ Vendor' ?>)</span>
                            </div>
                            <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/status" method="POST" class="mb-2">
                                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="status" value="IN_PROGRESS">
                                <input type="hidden" name="note" value="Tiếp tục xử lý sau khi nhận thông tin">
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-2" style="border-radius:8px;">
                                    <i class="bi bi-play-circle-fill fs-5"></i> Tiếp tục xử lý (Resume SLA)
                                </button>
                            </form>
                            <button type="button" class="btn btn-success w-100 fw-bold py-1 small" data-bs-toggle="modal" data-bs-target="#resolveModal" style="border-radius:6px;">
                                <i class="bi bi-check2-circle me-1"></i> Giải quyết luôn (Resolve)
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- REMOTE ASSISTANCE BUTTON FOR IT -->
                    <?php if (Auth::isStaff() || Auth::isManager() || Auth::isAdmin()): ?>
                        <div class="mb-3">
                            <?php if (!empty($activeRemoteSession)): ?>
                                <a href="<?= $baseUrl ?>/remote-support/session/<?= $activeRemoteSession['session_code'] ?>" class="btn btn-warning w-100 p-2 text-start rounded-3 d-flex align-items-center justify-content-between shadow-xs border-warning">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="spinner-grow spinner-grow-sm text-dark"></span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:0.8rem;">Phiên Remote Đang Mở</div>
                                            <div class="text-muted" style="font-size:0.68rem;">Mã: <?= $activeRemoteSession['session_code'] ?> (Bấm vào xem)</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-box-arrow-in-right text-dark"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= $baseUrl ?>/remote-support/request/<?= $ticket['id'] ?>" class="btn btn-outline-primary w-100 p-2 text-start rounded-3 d-flex align-items-center justify-content-between action-tile border-primary-subtle" style="background:#F0F7FF;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-2 bg-primary text-white rounded-2">
                                            <i class="bi bi-display fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-primary" style="font-size:0.8rem;">Yêu cầu Remote Máy Tính</div>
                                            <div class="text-muted" style="font-size:0.68rem;">Xem &amp; Hỗ trợ màn hình User trực tiếp</div>
                                        </div>
                                    </div>
                                    <i class="bi bi-arrow-right text-primary"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- PHẦN 2: ĐIỀU PHỐI & PHỤ TRÁCH (2 CỘT GỌN GÀNG) -->
                    <div class="mb-3">
                        <div class="text-uppercase fw-bold text-muted mb-2 ps-1" style="font-size:0.68rem; letter-spacing:0.5px;">
                            <i class="bi bi-people-fill text-primary me-1"></i>Điều phối &amp; Phân bổ
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 h-100 d-flex flex-column justify-content-between action-tile" data-bs-toggle="modal" data-bs-target="#assignModal">
                                    <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                        <i class="bi bi-person-gear text-primary fs-5"></i>
                                        <i class="bi bi-pencil small text-muted"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.75rem;">Phân công</div>
                                        <div class="text-muted text-truncate" style="font-size:0.68rem;">
                                            <?= htmlspecialchars(!empty($ticket['assignee_name']) ? $ticket['assignee_name'] : 'Chưa gán') ?>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 h-100 d-flex flex-column justify-content-between action-tile" data-bs-toggle="modal" data-bs-target="#transferModal">
                                    <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                        <i class="bi bi-arrow-left-right text-success fs-5"></i>
                                        <i class="bi bi-chevron-right small text-muted"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.75rem;">Chuyển nhóm</div>
                                        <div class="text-muted text-truncate" style="font-size:0.68rem;">
                                            <?= htmlspecialchars(!empty($ticket['group_name']) ? $ticket['group_name'] : 'Chưa chọn') ?>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 h-100 d-flex flex-column justify-content-between action-tile" data-bs-toggle="modal" data-bs-target="#tierEscalateModal">
                                    <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                        <i class="bi bi-layers-fill text-dark fs-5"></i>
                                        <span class="badge bg-secondary" style="font-size:0.6rem;"><?= htmlspecialchars($ticket['support_tier'] ?? 'L1') ?></span>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.75rem;">Chuyển cấp Tier</div>
                                        <div class="text-muted text-truncate" style="font-size:0.68rem;">
                                            Tier <?= htmlspecialchars($ticket['support_tier'] ?? 'L1') ?>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div class="col-6">
                                <?php if (Auth::isManager() || Auth::isAdmin()): ?>
                                    <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 h-100 d-flex flex-column justify-content-between action-tile" data-bs-toggle="modal" data-bs-target="#adjustPriorityModal">
                                        <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                            <i class="bi bi-flag-fill text-warning fs-5"></i>
                                            <span class="badge-priority priority-<?= strtolower($ticket['priority_code'] ?? 'normal') ?>" style="font-size:0.6rem; padding:1px 4px;">
                                                <?= htmlspecialchars($ticket['priority_name'] ?? 'Normal') ?>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size:0.75rem;">Đổi Priority</div>
                                            <div class="text-muted text-truncate" style="font-size:0.68rem;">Tính lại SLA</div>
                                        </div>
                                    </button>
                                <?php else: ?>
                                    <div class="p-2 text-start rounded-3 h-100 d-flex flex-column justify-content-between border bg-light">
                                        <div class="d-flex align-items-center justify-content-between w-100 mb-1">
                                            <i class="bi bi-flag-fill text-muted fs-5"></i>
                                            <span class="badge-priority priority-<?= strtolower($ticket['priority_code'] ?? 'normal') ?>" style="font-size:0.6rem; padding:1px 4px;">
                                                <?= htmlspecialchars($ticket['priority_name'] ?? 'Normal') ?>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-muted" style="font-size:0.75rem;">Mức ưu tiên</div>
                                            <div class="text-muted text-truncate" style="font-size:0.68rem;">Cố định</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- PHẦN 3: CÔNG CỤ TÁC VỤ (QUICK TOOLS) -->
                    <div class="mb-3">
                        <div class="text-uppercase fw-bold text-muted mb-2 ps-1" style="font-size:0.68rem; letter-spacing:0.5px;">
                            <i class="bi bi-tools text-secondary me-1"></i>Công cụ tác vụ
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <button type="button" class="btn btn-light border text-start py-2 px-3 fw-semibold small d-flex align-items-center justify-content-between rounded-2 action-list-btn" data-bs-toggle="modal" data-bs-target="#logWorkModal">
                                <span><i class="bi bi-stopwatch text-success me-2"></i>Ghi nhận giờ làm (Log Work)</span>
                                <span class="badge bg-success-subtle text-success border font-monospace" style="font-size:0.68rem;"><?= htmlspecialchars($totalWorklogFormatted ?? '0m') ?></span>
                            </button>

                            <button type="button" class="btn btn-light border text-start py-2 px-3 fw-semibold small d-flex align-items-center justify-content-between rounded-2 action-list-btn" data-bs-toggle="modal" data-bs-target="#linkAssetModal">
                                <span><i class="bi bi-laptop text-primary me-2"></i><?= empty($ticket['asset_id']) ? 'Gán máy tính sự cố (ITAM)' : 'Đổi máy tính liên quan' ?></span>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </button>

                            <button type="button" class="btn btn-light border text-start py-2 px-3 fw-semibold small d-flex align-items-center justify-content-between rounded-2 action-list-btn" data-bs-toggle="modal" data-bs-target="#linkIssueModal">
                                <span><i class="bi bi-link-45deg text-info me-2"></i>Liên kết sự cố (Link Issue)</span>
                                <span class="badge bg-light text-muted border" style="font-size:0.68rem;"><?= count($linkedIssues ?? []) ?></span>
                            </button>
                        </div>
                    </div>

                    <!-- PHẦN 4: ITIL NÂNG CAO & TÁC VỤ PHỤ -->
                    <div>
                        <div class="text-uppercase fw-bold text-muted mb-2 ps-1" style="font-size:0.68rem; letter-spacing:0.5px;">
                            <i class="bi bi-diagram-3 text-info me-1"></i>Quy trình ITIL &amp; Thao tác khác
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <a href="<?= $baseUrl ?>/problems/create?from_ticket_id=<?= $ticket['id'] ?>" class="btn btn-outline-danger w-100 py-1 small fw-semibold text-truncate text-decoration-none rounded-2 d-flex align-items-center justify-content-center gap-1" style="font-size:0.75rem;">
                                    <i class="bi bi-exclamation-octagon"></i> Tạo Problem
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= $baseUrl ?>/changes/create?from_ticket_id=<?= $ticket['id'] ?>" class="btn btn-outline-primary w-100 py-1 small fw-semibold text-truncate text-decoration-none rounded-2 d-flex align-items-center justify-content-center gap-1" style="font-size:0.75rem;">
                                    <i class="bi bi-arrow-repeat"></i> Tạo RFC
                                </a>
                            </div>
                        </div>

                        <!-- Dropdown thao tác đóng/hủy -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary w-100 py-1 small dropdown-toggle fw-medium" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.75rem; border-radius:6px;">
                                <i class="bi bi-three-dots me-1"></i> Thao tác khác...
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm small border-0 py-1">
                                <li>
                                    <a class="dropdown-item py-2" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#duplicateModal">
                                        <i class="bi bi-copy text-secondary me-2"></i> Đánh dấu trùng lặp (Duplicate)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item text-danger py-2" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="bi bi-x-circle me-2"></i> Hủy yêu cầu này (Cancel)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TIME TRACKING MINI CARD -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-radius:10px 10px 0 0;">
                    <span class="fw-bold text-dark"><i class="bi bi-hourglass-bottom text-success me-2"></i>Theo Dõi Thời Gian (Worklog)</span>
                    <button type="button" class="btn btn-sm btn-link text-primary p-0 fw-semibold text-decoration-none" data-bs-toggle="modal" data-bs-target="#logWorkModal">
                        + Log Work
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small">Thời gian đã làm:</span>
                        <span class="font-monospace fw-bold text-dark fs-6"><?= htmlspecialchars($totalWorklogFormatted ?? '0m') ?></span>
                    </div>
                    <div class="progress" style="height:6px; border-radius:999px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width:<?= min(100, max(5, (((int)($totalWorklogMinutes ?? 0)) / 480) * 100)) ?>%; border-radius:999px;"></div>
                    </div>
                    <div class="text-muted small mt-2" style="font-size:0.72rem;">
                        Tổng số: <strong><?= (int)($totalWorklogMinutes ?? 0) ?></strong> phút qua <strong><?= count($worklogs ?? []) ?></strong> lần ghi nhận.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- USER CANCEL BUTTON (RULE 25) -->
        <?php if ($isRequester && $ticket['status'] === 'NEW'): ?>
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px; border-top:3px solid #DE350B !important;">
                <div class="card-body p-3">
                    <button type="button" class="btn btn-outline-danger w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#cancelModal" style="border-radius:8px;">
                        <i class="bi bi-x-circle me-2"></i>Hủy yêu cầu hỗ trợ này
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- METADATA CARD: PHỤ TRÁCH & LIÊN HỆ -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2" style="border-radius:10px 10px 0 0;">
                <div style="background:#EAF0FB; border-radius:8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                </div>
                <span class="fw-bold text-dark">Phụ Trách &amp; Liên Hệ</span>
            </div>
            <div class="card-body p-4">
                <!-- Requester -->
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="user-avatar-circle text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;font-size:0.9rem;border-radius:50%;background:linear-gradient(135deg,#5E6C84,#172B4D);">
                        <?= mb_strtoupper(mb_substr($ticket['requester_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?>
                    </div>
                    <div>
                        <label class="text-muted" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Người gửi yêu cầu</label>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($ticket['requester_name'] ?? 'Chưa xác định') ?></div>
                        <div class="text-muted small"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($ticket['requester_email'] ?? 'N/A') ?></div>
                        <div class="text-muted small"><i class="bi bi-building me-1"></i><?= htmlspecialchars($ticket['requester_department'] ?? 'N/A') ?></div>
                    </div>
                </div>

                <!-- IT Group -->
                <div class="mb-3 pb-3 border-bottom">
                    <label class="text-muted" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Nhóm IT chịu trách nhiệm</label>
                    <div class="fw-bold text-primary"><?= htmlspecialchars($ticket['group_name'] ?? 'Chưa phân nhóm') ?></div>
                </div>

                <!-- Assignee -->
                <div class="mb-3 pb-3 border-bottom">
                    <label class="text-muted" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Kỹ thuật viên xử lý</label>
                    <div class="mt-1">
                        <?php if (!empty($ticket['assignee_name'])): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center"
                                     style="width:30px;height:30px;font-size:0.8rem;border-radius:50%;">
                                    <?= mb_strtoupper(mb_substr($ticket['assignee_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?>
                                </div>
                                <span class="fw-bold text-dark"><i class="bi bi-person-check-fill text-success me-1"></i><?= htmlspecialchars($ticket['assignee_name'] ?? '') ?></span>
                            </div>
                        <?php else: ?>
                            <span class="badge fw-bold" style="background:#FFF4E0; color:#974F0C; border-radius:999px; border:1px solid #FF991F;">
                                <i class="bi bi-inbox me-1"></i>Chờ phân công
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Category & Service -->
                <div>
                    <label class="text-muted" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:700;">Phân loại &amp; Dịch vụ</label>
                    <div class="fw-bold text-dark mt-1"><?= htmlspecialchars($ticket['category_name'] ?? 'Chưa phân loại') ?></div>
                    <?php if (!empty($ticket['service_name'])): ?>
                        <div class="text-muted small"><?= htmlspecialchars($ticket['service_name'] ?? '') ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- LINKED ASSET CARD (RULE 55) -->
        <?php if (!empty($ticket['asset_id'])): ?>
            <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-radius:10px 10px 0 0;">
                    <span class="fw-bold text-dark"><i class="bi bi-laptop text-primary me-2"></i>Thiết Bị Gặp Sự Cố</span>
                    <span class="badge bg-light text-primary border font-monospace" style="border-radius:999px;"><?= htmlspecialchars($ticket['asset_code'] ?? '') ?></span>
                </div>
                <div class="card-body p-3">
                    <div class="fw-bold text-dark">
                        <a href="<?= $baseUrl ?>/assets/<?= $ticket['asset_id'] ?>" class="text-dark text-decoration-none hover-primary">
                            <?= htmlspecialchars($ticket['asset_name'] ?? 'Tài sản') ?>
                        </a>
                    </div>
                    <div class="text-muted small mt-1">
                        <div>Model: <strong><?= htmlspecialchars($ticket['asset_model'] ?? 'N/A') ?></strong></div>
                        <?php if (!empty($ticket['asset_serial'])): ?>
                            <div>Serial: <span class="font-monospace text-dark"><?= htmlspecialchars($ticket['asset_serial'] ?? '') ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 pt-2 border-top">
                        <a href="<?= $baseUrl ?>/assets/<?= $ticket['asset_id'] ?>" class="btn btn-outline-primary btn-sm w-100 fw-semibold" style="border-radius:8px;">
                            <i class="bi bi-clock-history me-1"></i>Xem lý lịch &amp; lịch sử máy
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- WATCHERS CARD (RULE 27) -->
        <div class="card mb-4 border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center" style="border-radius:10px 10px 0 0;">
                <span class="fw-bold text-dark"><i class="bi bi-eye-fill me-2 text-primary"></i>Người Theo Dõi (<?= count($watchers ?? []) ?>)</span>
                <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/watch" method="POST" class="d-inline">
                    <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                    <?php if (!empty($isWatching)): ?>
                        <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2 fw-semibold" style="font-size:11px; border-radius:999px;" title="Dừng nhận thông báo">
                            <i class="bi bi-eye-slash me-1"></i>Bỏ theo dõi
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-outline-primary btn-sm py-0 px-2 fw-semibold" style="font-size:11px; border-radius:999px;" title="Nhận thông báo khi có thay đổi">
                            <i class="bi bi-eye me-1"></i>Theo dõi
                        </button>
                    <?php endif; ?>
                </form>
            </div>
            <div class="card-body p-3">
                <?php if (empty($watchers)): ?>
                    <div class="text-muted small text-center py-2">Chưa có người theo dõi ticket này.</div>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($watchers as $w): ?>
                            <span class="badge bg-light text-dark border p-2 d-flex align-items-center gap-1" style="border-radius:999px;" title="<?= htmlspecialchars($w['email'] ?? '') ?>">
                                <i class="bi bi-person-circle text-primary"></i>
                                <?= htmlspecialchars($w['full_name'] ?? 'User') ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /RIGHT COLUMN -->

</div><!-- /row -->

<!-- ================================================================
     MODALS (All logic preserved 100%)
================================================================ -->

<!-- MODAL: ĐIỀU CHỈNH PRIORITY (RULE 9) -->
<div class="modal fade" id="adjustPriorityModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/priority" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-warning"><i class="bi bi-flag-fill me-2"></i>Điều Chỉnh Mức Ưu Tiên (Priority)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 small"><i class="bi bi-shield-exclamation me-1"></i>Thao tác dành cho IT Manager/Leader. Mọi thay đổi đều được ghi vết và kiểm toán (Audit).</div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Mức độ ưu tiên hiện tại:</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($ticket['priority_name'] ?? 'Bình thường') ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="new_priority_id" class="form-label fw-bold small">Chọn mức độ ưu tiên mới <span class="text-danger">*</span>:</label>
                    <select name="priority_id" id="new_priority_id" class="form-select" required>
                        <option value="">-- Chọn Priority --</option>
                        <?php foreach ($allPriorities as $pri): ?>
                            <option value="<?= $pri['id'] ?>" <?= ((int)($ticket['priority_id'] ?? 0) === (int)$pri['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pri['name'] ?? '') ?> (SLA: <?= (int)($pri['resolution_time'] ?? 0) ?> <?= htmlspecialchars($pri['resolution_unit'] ?? 'Giờ') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="adjust_priority_reason" class="form-label fw-bold small">Lý do điều chỉnh <span class="text-danger">*</span>:</label>
                    <textarea name="reason" id="adjust_priority_reason" class="form-control" rows="2" placeholder="Ví dụ: Hạ cấp do người dùng chọn sai mức khẩn cấp..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ITIL TIER ESCALATION -->
<div class="modal fade" id="tierEscalateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/escalate-tier" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-layers-fill me-2 text-warning"></i>Chuyển Cấp Hỗ Trợ (Tier Escalation)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Quy trình điều phối hỗ trợ ITIL theo cấp độ chuyên môn:</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Cấp hỗ trợ hiện tại:</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="Tier <?= htmlspecialchars($ticket['support_tier'] ?? 'L1') ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Chuyển sang cấp độ <span class="text-danger">*</span>:</label>
                    <select name="target_tier" class="form-select" required>
                        <option value="L1" <?= ($ticket['support_tier'] ?? 'L1') === 'L1' ? 'selected' : '' ?>>Tier L1 - Service Desk (Tiếp nhận &amp; xử lý cơ bản)</option>
                        <option value="L2" <?= ($ticket['support_tier'] ?? '') === 'L2' ? 'selected' : '' ?>>Tier L2 - Specialized Support (Kỹ thuật chuyên sâu / Hệ thống)</option>
                        <option value="L3" <?= ($ticket['support_tier'] ?? '') === 'L3' ? 'selected' : '' ?>>Tier L3 - Expert / DevOps / Vendor Lead (Chuyên gia / Bug mã nguồn)</option>
                        <option value="VENDOR" <?= ($ticket['support_tier'] ?? '') === 'VENDOR' ? 'selected' : '' ?>>VENDOR - Chuyển tiếp hãng / Nhà thầu ngoài</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Ghi chú chuyển cấp (Lý do / Hiện tượng kỹ thuật):</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Đã kiểm tra cơ bản không fix được, chuyển L2 phân tích log server..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-arrow-up-circle me-1"></i>Xác Nhận Chuyển Cấp</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ITIL TICKET APPROVAL -->
<div class="modal fade" id="ticketApprovalModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/approvals/decide" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/tickets/' . $ticket['id']) ?>">
            <?php
            $ticketApprId = 0;
            try {
                $stmtApprFind = \App\Core\Database::getConnection()->prepare("SELECT id FROM ticket_approvals WHERE ticket_id = :tid AND status = 'PENDING' LIMIT 1");
                $stmtApprFind->execute(['tid' => (int)($ticket['id'] ?? 0)]);
                $ticketApprId = (int)($stmtApprFind->fetchColumn() ?: 0);
            } catch (\Throwable $e) {
                $ticketApprId = 0;
            }
            ?>
            <input type="hidden" name="approval_id" value="<?= $ticketApprId ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-check me-2"></i>Xét Duyệt Yêu Cầu Dịch Vụ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Ticket: <strong>#<?= htmlspecialchars($ticket['ticket_code'] ?? '') ?> - <?= htmlspecialchars($ticket['title'] ?? '') ?></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Quyết định của bạn <span class="text-danger">*</span>:</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="decision" id="tAppr_ok" value="APPROVED" checked>
                            <label class="form-check-label text-success fw-bold" for="tAppr_ok"><i class="bi bi-check-circle me-1"></i>Phê Duyệt</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="decision" id="tAppr_no" value="REJECTED">
                            <label class="form-check-label text-danger fw-bold" for="tAppr_no"><i class="bi bi-x-circle me-1"></i>Từ Chối</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="decision" id="tAppr_ret" value="RETURNED">
                            <label class="form-check-label text-info fw-bold" for="tAppr_ret"><i class="bi bi-arrow-return-left me-1"></i>Yêu Cầu Bổ Sung</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Ý kiến / Nhận xét phê duyệt:</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Nhập lý do hoặc hướng dẫn phê duyệt..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check2 me-1"></i>Lưu Quyết Định</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: CHỜ BÊN THỨ BA / VENDOR -->
<div class="modal fade" id="vendorModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/status" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <input type="hidden" name="status" value="WAITING_VENDOR">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-info"><i class="bi bi-truck me-2"></i>Chờ Nhà Cung Cấp / Bên Thứ Ba</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small"><i class="bi bi-clock-history me-1"></i>Khi chuyển sang trạng thái <strong>WAITING_VENDOR</strong>, thời gian đếm ngược SLA sẽ <strong>tạm dừng (Pause)</strong> tự động.</div>
                <div class="mb-3">
                    <label for="vendor_note" class="form-label fw-bold small">Tên đối tác / Vendor &amp; Nội dung chờ <span class="text-danger">*</span>:</label>
                    <textarea name="note" id="vendor_note" class="form-control" rows="3" placeholder="Ví dụ: Chờ FPT Telecom kiểm tra đường truyền cáp quang bên ngoài..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-info text-white fw-bold">Chuyển sang WAITING_VENDOR</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: GÁN THIẾT BỊ (RULE 55) -->
<div class="modal fade" id="linkAssetModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/link-asset" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-laptop me-2"></i>Gán Thiết Bị Liên Quan Đến Sự Cố</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="link_asset_select" class="form-label fw-bold small">Chọn tài sản CNTT trong hệ thống:</label>
                    <select name="asset_id" id="link_asset_select" class="form-select">
                        <option value="">-- Không liên kết thiết bị nào (Gỡ bỏ) --</option>
                        <?php foreach ($allAssets as $ast): ?>
                            <option value="<?= $ast['id'] ?>" <?= ((int)($ticket['asset_id'] ?? 0) === (int)$ast['id']) ? 'selected' : '' ?>>
                                [<?= htmlspecialchars($ast['asset_code'] ?? '') ?>] <?= htmlspecialchars($ast['name'] ?? '') ?> (SN: <?= htmlspecialchars($ast['serial_number'] ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text small mt-1">Việc liên kết thiết bị giúp theo dõi toàn bộ lịch sử hỏng hóc và bảo trì của máy tính.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary fw-bold">Lưu Thiết Bị</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ĐÁNH DẤU TRÙNG LẶP (RULE 24) -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/duplicate" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-secondary"><i class="bi bi-copy me-2"></i>Đánh Dấu Ticket Trùng Lặp (Duplicate)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-secondary py-2 small"><i class="bi bi-info-circle me-1"></i>Ticket bị đánh dấu trùng sẽ chuyển sang trạng thái <strong>DUPLICATE</strong> và dừng SLA. Dữ liệu lịch sử vẫn được bảo toàn nguyên vẹn.</div>
                <div class="mb-3">
                    <label for="duplicate_of_id" class="form-label fw-bold small">ID Ticket Gốc (Parent Ticket ID) <span class="text-danger">*</span>:</label>
                    <input type="number" name="duplicate_of_id" id="duplicate_of_id" class="form-control" placeholder="Ví dụ: 1 (cho ticket #IT-000001)" required>
                    <small class="form-text text-muted">Nhập mã số ID của ticket gốc đã được tạo trước đó.</small>
                </div>
                <div class="mb-3">
                    <label for="duplicate_reason" class="form-label fw-bold small">Ghi chú lý do trùng lặp:</label>
                    <textarea name="reason" id="duplicate_reason" class="form-control" rows="2" placeholder="Ví dụ: Cùng một sự cố mất điện phòng kế toán do user khác gửi..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-secondary fw-bold">Xác Nhận Đánh Dấu Trùng</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: HỦY TICKET (RULE 25) -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/cancel" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle me-2"></i>Hủy Yêu Cầu Hỗ Trợ (Cancel Ticket)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>Yêu cầu sẽ chuyển sang trạng thái <strong>CANCELLED</strong>. Hệ thống không xóa vật lý bản ghi để đảm bảo tính minh bạch.</div>
                <div class="mb-3">
                    <label for="cancel_reason" class="form-label fw-bold small">Lý do hủy yêu cầu <span class="text-danger">*</span>:</label>
                    <textarea name="cancel_reason" id="cancel_reason" class="form-control" rows="3" placeholder="Ví dụ: Sự cố tự hết, tạo nhầm yêu cầu, không cần hỗ trợ nữa..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-danger fw-bold">Xác Nhận Hủy Ticket</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: GHI NHẬN THỜI GIAN (LOG WORK) -->
<div class="modal fade" id="logWorkModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/worklog" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success"><i class="bi bi-stopwatch-fill me-2"></i>Ghi Nhận Thời Gian Làm Việc (Log Work)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success py-2 small"><i class="bi bi-info-circle me-1"></i>Ghi lại thời gian thực tế bạn đã xử lý sự cố này theo định dạng linh hoạt của Jira.</div>
                <div class="mb-3">
                    <label for="time_spent" class="form-label fw-bold small">Thời gian đã làm <span class="text-danger">*</span>:</label>
                    <input type="text" name="time_spent" id="time_spent" class="form-control font-monospace" placeholder="Ví dụ: 1h 30m, 45m, 2h, 1d" required>
                    <div class="form-text small">Quy ước chuẩn: <strong>1d = 8h</strong>, <strong>1h = 60m</strong>. Ví dụ: <code>2h 30m</code> hoặc <code>45m</code>.</div>
                </div>
                <div class="mb-3">
                    <label for="logged_date" class="form-label fw-bold small">Ngày làm việc:</label>
                    <input type="date" name="logged_date" id="logged_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label for="worklog_desc" class="form-label fw-bold small">Nội dung công việc đã thực hiện:</label>
                    <textarea name="description" id="worklog_desc" class="form-control" rows="3" placeholder="Ví dụ: Kiểm tra cấu hình router, thay thế jack cắm RJ45, chạy ping test..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success fw-bold text-white"><i class="bi bi-check2 me-1"></i>Lưu Worklog</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: LIÊN KẾT SỰ CỐ (LINK ISSUE) -->
<div class="modal fade" id="linkIssueModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/link-issue" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-link-45deg me-2"></i>Liên Kết Sự Cố (Link Issue)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-primary py-2 small"><i class="bi bi-diagram-3 me-1"></i>Thiết lập mối quan hệ phụ thuộc giữa ticket hiện tại và ticket khác trong hệ thống.</div>
                <div class="mb-3">
                    <label for="link_type" class="form-label fw-bold small">Kiểu quan hệ <span class="text-danger">*</span>:</label>
                    <select name="link_type" id="link_type" class="form-select" required>
                        <option value="relates_to">liên quan tới (relates to)</option>
                        <option value="blocks">chặn tiến độ của (blocks)</option>
                        <option value="is_blocked_by">bị chặn bởi (is blocked by)</option>
                        <option value="causes">gây ra sự cố cho (causes)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="target_ticket" class="form-label fw-bold small">Mã Ticket hoặc ID cần liên kết <span class="text-danger">*</span>:</label>
                    <input type="text" name="target_ticket" id="target_ticket" class="form-control font-monospace" placeholder="Ví dụ: IT-000001 hoặc 1" required>
                    <div class="form-text small">Nhập mã ticket dạng <code>IT-XXXXXX</code> hoặc ID số của ticket.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-link me-1"></i>Tạo Liên Kết</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: PHÂN CÔNG -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/assign" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Phân công Kỹ thuật viên Phụ trách</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small"><i class="bi bi-info-circle me-1"></i>Theo quy định SLA: Việc chuyển giao người xử lý <strong>không làm reset thời hạn SLA</strong>.</div>
                <div class="mb-3">
                    <label for="assignee_id" class="form-label fw-bold small">Chọn nhân viên IT:</label>
                    <select name="assignee_id" id="assignee_id" class="form-select" required>
                        <option value="">-- Chọn kỹ thuật viên --</option>
                        <?php foreach ($staffMembers as $sm): ?>
                            <option value="<?= $sm['id'] ?>" <?= ((int)($ticket['assignee_id'] ?? 0) === (int)$sm['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sm['full_name'] ?? 'Nhân viên') ?> (<?= htmlspecialchars($sm['email'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="assign_reason" class="form-label fw-bold small">Lý do phân công / điều chuyển:</label>
                    <input type="text" name="reason" id="assign_reason" class="form-control" placeholder="Ví dụ: Phân công theo chuyên môn...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary fw-bold">Xác nhận phân công</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: CHUYỂN NHÓM IT (TRANSFER GROUP) -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/transfer-group" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chuyển tiếp sang Nhóm IT khác</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>Chuyển nhóm sẽ lưu lịch sử chuyển giao và <strong>giữ nguyên SLA</strong> đã cam kết.</div>
                <div class="mb-3">
                    <label for="to_group_id" class="form-label fw-bold small">Chọn Nhóm IT đích:</label>
                    <select name="to_group_id" id="to_group_id" class="form-select" required>
                        <option value="">-- Chọn nhóm IT --</option>
                        <?php foreach ($itGroups as $grp): ?>
                            <?php if ((int)$grp['id'] !== (int)($ticket['group_id'] ?? 0)): ?>
                                <option value="<?= $grp['id'] ?>"><?= htmlspecialchars($grp['name'] ?? '') ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="transfer_reason" class="form-label fw-bold small">Lý do chuyển nhóm <span class="text-danger">*</span>:</label>
                    <textarea name="reason" id="transfer_reason" class="form-control" rows="3" placeholder="Nêu rõ lý do cần nhóm khác can thiệp..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary fw-bold">Xác nhận chuyển nhóm</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: RESOLVE TICKET -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/resolve" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success"><i class="bi bi-check2-circle me-2"></i>Hoàn thành xử lý Ticket (Resolve)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="resolution" class="form-label fw-bold small">Nội dung kết quả xử lý (Resolution) <span class="text-danger">*</span>:</label>
                    <textarea name="resolution" id="resolution" class="form-control" rows="3" placeholder="Mô tả các bước đã xử lý để giải quyết sự cố cho người dùng..." required></textarea>
                </div>
                <div class="mb-3">
                    <label for="root_cause" class="form-label fw-bold small">Nguyên nhân gốc rễ (Root cause nếu có):</label>
                    <input type="text" name="root_cause" id="root_cause" class="form-control" placeholder="Ví dụ: Đứt cáp mạng LAN, lỗi bản quyền Windows...">
                </div>
                <div class="mb-3">
                    <label for="solution" class="form-label fw-bold small">Biện pháp phòng ngừa / Hướng dẫn thêm:</label>
                    <input type="text" name="solution" id="solution" class="form-control" placeholder="Ví dụ: Đã thay dây mạng mới cat6...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success fw-bold">Chuyển sang RESOLVED</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: USER XÁC NHẬN NGHIỆM THU (CLOSE) -->
<div class="modal fade" id="confirmCloseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/confirm-close" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-success">Xác nhận Nghiệm thu &amp; Đóng Yêu Cầu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Bạn xác nhận rằng sự cố kỹ thuật này đã được IT giải quyết thỏa đáng?</p>
                <div class="mb-3">
                    <label for="confirm_note" class="form-label fw-bold small">Ý kiến đóng góp thêm (nếu có):</label>
                    <textarea name="confirm_note" id="confirm_note" class="form-control" rows="2" placeholder="Cảm ơn IT đã hỗ trợ kịp thời..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-success fw-bold">Xác nhận Đã hoàn thành</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: USER REOPEN TICKET -->
<div class="modal fade" id="reopenModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/reopen" method="POST" class="modal-content" style="border-radius:10px; overflow:hidden;">
            <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger">Mở lại Yêu Cầu (Reopen)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="reopen_reason" class="form-label fw-bold small">Lý do chưa hoàn thành <span class="text-danger">*</span>:</label>
                    <textarea name="reopen_reason" id="reopen_reason" class="form-control" rows="3" placeholder="Mô tả biểu hiện lỗi vẫn còn tồn tại..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger fw-bold">Mở lại yêu cầu</button>
            </div>
        </form>
    </div>
</div>

<!-- TRIBUTE.JS CHO @MENTION -->
<link rel="stylesheet" href="https://unpkg.com/tributejs@5.1.3/dist/tribute.css" />
<style>
.tribute-container { box-shadow:0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06); border-radius:.375rem; border:1px solid #e5e7eb; background:#fff; font-size:.875rem; z-index:9999; }
.tribute-container ul { margin:0; padding:0; list-style:none; }
.tribute-container li { padding:.5rem .75rem; cursor:pointer; border-bottom:1px solid #f3f4f6; }
.tribute-container li:last-child { border-bottom:none; }
.tribute-container li.highlight { background:#eff6ff; }
</style>
<script src="https://unpkg.com/tributejs@5.1.3/dist/tribute.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var commentArea = document.getElementById('comment_content');
    if (commentArea && typeof Tribute !== 'undefined') {
        var tribute = new Tribute({
            values: function (text, cb) {
                var baseUrl = window.APP_BASE_URL || '';
                fetch(baseUrl + '/api/users/search?q=' + text)
                    .then(res => res.json())
                    .then(data => {
                        cb(data.map(u => ({ key: u.full_name, value: u.username })));
                    })
                    .catch(err => cb([]));
            },
            selectTemplate: function (item) {
                return '@' + item.original.value;
            },
            menuItemTemplate: function (item) {
                return '<div class="d-flex align-items-center gap-2 p-2"><span class="badge bg-primary rounded-circle">' + item.original.value.charAt(0).toUpperCase() + '</span><span class="fw-semibold">' + item.string + '</span> <span class="text-muted small">(@' + item.original.value + ')</span></div>';
            },
            noMatchTemplate: function () {
                return '<li class="text-muted small px-3 py-2">Không tìm thấy...</li>';
            }
        });
        tribute.attach(commentArea);
    }

    // AI IT Desk Copilot Trigger
    var btnRunCopilot = document.getElementById('btnRunCopilot');
    if (btnRunCopilot) {
        var copilotPlaceholder = document.getElementById('copilotPlaceholder');
        var copilotResult      = document.getElementById('copilotResult');
        var aiSummary          = document.getElementById('aiSummary');
        var aiSteps            = document.getElementById('aiSteps');
        var aiDraft            = document.getElementById('aiDraft');
        var btnApplyDraft      = document.getElementById('btnApplyDraft');

        btnRunCopilot.addEventListener('click', function() {
            btnRunCopilot.disabled = true;
            btnRunCopilot.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';

            fetch('<?= $baseUrl ?>/api/ai/suggest', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({ ticket_id: '<?= $ticket['id'] ?>' })
            })
            .then(res => res.json())
            .then(data => {
                btnRunCopilot.disabled = false;
                btnRunCopilot.innerHTML = '<i class="bi bi-stars me-1 text-warning"></i>Phân Tích Lại';
                if (data.success) {
                    if (copilotPlaceholder) copilotPlaceholder.classList.add('d-none');
                    if (copilotResult) copilotResult.classList.remove('d-none');
                    if (aiSummary) aiSummary.textContent = data.summary || 'Chưa có thông tin tóm tắt.';
                    if (aiSteps) {
                        aiSteps.innerHTML = (data.solution_steps || []).map(s => `
                            <li class="mb-1 d-flex align-items-start gap-2">
                                <i class="bi bi-arrow-right-circle text-primary mt-1"></i>
                                <span>${s}</span>
                            </li>
                        `).join('');
                    }
                    if (aiDraft) aiDraft.textContent = data.draft_reply || '';
                } else {
                    alert(data.message || 'Lỗi khi gọi AI Copilot.');
                }
            })
            .catch(err => {
                btnRunCopilot.disabled = false;
                btnRunCopilot.innerHTML = '<i class="bi bi-stars me-1 text-warning"></i>Phân Tích Ngay';
                console.error(err);
            });
        });

        if (btnApplyDraft && commentArea) {
            btnApplyDraft.addEventListener('click', function() {
                if (aiDraft && aiDraft.textContent) {
                    commentArea.value = aiDraft.textContent;
                    commentArea.focus();
                    commentArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
    }
});

// Checklist AJAX Handlers
function toggleChecklist(itemId) {
    const label = document.getElementById('label-checklist-' + itemId);
    const cb    = document.querySelector('.checklist-checkbox[data-id="' + itemId + '"]');
    const row   = document.getElementById('checklist-item-' + itemId);

    fetch('<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/checklist/' + itemId + '/toggle', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.data.is_completed) {
                label.classList.add('text-decoration-line-through', 'text-muted');
                label.classList.remove('text-dark', 'fw-medium');
                if (row) row.style.background = '#EBFBF0';
            } else {
                label.classList.remove('text-decoration-line-through', 'text-muted');
                label.classList.add('text-dark', 'fw-medium');
                if (row) row.style.background = '#FAFBFC';
            }
            updateChecklistProgress();
        }
    })
    .catch(e => console.error(e));
}

function deleteChecklist(itemId) {
    if (!confirm('Bạn có chắc muốn xóa đầu việc này?')) return;
    const row = document.getElementById('checklist-item-' + itemId);

    fetch('<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/checklist/' + itemId + '/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) { if (row) row.remove(); updateChecklistProgress(); }
    })
    .catch(e => console.error(e));
}

function submitChecklist(e) {
    e.preventDefault();
    const input = document.getElementById('new_item_text');
    const text  = input.value.trim();
    if (!text) return false;

    fetch('<?= $baseUrl ?>/tickets/<?= $ticket['id'] ?>/checklist', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ item_text: text })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) { input.value = ''; location.reload(); }
        else { alert(data.message || 'Lỗi thêm checklist'); }
    })
    .catch(e => location.reload());

    return false;
}

function updateChecklistProgress() {
    const cbs = document.querySelectorAll('.checklist-checkbox');
    const total = cbs.length;
    let completed = 0;
    cbs.forEach(c => { if (c.checked) completed++; });
    const pct = total > 0 ? Math.round((completed / total) * 100) : 0;

    const bar     = document.getElementById('checklist-progress-bar');
    const counter = document.getElementById('checklist-counter');
    if (bar) bar.style.width = pct + '%';
    if (counter) counter.textContent = `${completed} / ${total} hoàn thành (${pct}%)`;
}
</script>

<!-- UNIVERSAL FILE PREVIEW MODAL (INLINE VIEWER FOR WORD, EXCEL, PDF, IMAGES, TEXT) -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" id="previewModalDialog" style="max-width: 90vw;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:12px; height:88vh; display:flex; flex-direction:column; overflow:hidden;">
            <!-- Modal Header -->
            <div class="modal-header py-2.5 px-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5 overflow-hidden me-3" style="min-width:0;">
                    <div id="previewHeaderIcon" class="rounded-2 p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px; height:34px;">
                        <i class="bi bi-file-earmark fs-5"></i>
                    </div>
                    <div class="overflow-hidden" style="min-width:0;">
                        <h6 class="modal-title fw-bold text-dark mb-0 text-truncate" id="filePreviewModalLabel" style="font-size:0.95rem;">Xem tệp đính kèm</h6>
                        <div class="text-muted" style="font-size:0.75rem;" id="previewFileMeta">Đang chuẩn bị dữ liệu...</div>
                    </div>
                </div>

                <!-- Modal Actions Toolbar -->
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                    <!-- Image Zoom Controls -->
                    <div id="previewImageControls" class="btn-group btn-group-sm me-1 d-none" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="btnZoomIn" title="Phóng to"><i class="bi bi-zoom-in"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnZoomOut" title="Thu nhỏ"><i class="bi bi-zoom-out"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnRotate" title="Xoay ảnh 90°"><i class="bi bi-arrow-clockwise"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnResetZoom" title="Khôi phục kích thước"><i class="bi bi-aspect-ratio"></i></button>
                    </div>

                    <!-- Text Copy Button -->
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="btnCopyText" title="Sao chép nội dung">
                        <i class="bi bi-clipboard me-1"></i>Sao chép
                    </button>

                    <!-- Open in New Tab -->
                    <a href="#" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-2 d-flex align-items-center gap-1" id="btnPreviewNewTab" title="Mở trực tiếp trên tab mới của trình duyệt">
                        <i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-sm-inline">Tab mới</span>
                    </a>

                    <!-- Download Button -->
                    <a href="#" class="btn btn-sm btn-primary py-1 px-2.5 rounded-2 d-flex align-items-center gap-1" id="btnPreviewDownload" title="Tải tệp tin gốc về máy tính">
                        <i class="bi bi-download"></i> <span class="d-none d-sm-inline">Tải về</span>
                    </a>

                    <!-- Fullscreen Toggle -->
                    <button type="button" class="btn btn-sm btn-outline-dark py-1 px-2 rounded-2" id="btnToggleFullscreen" title="Bật/Tắt toàn màn hình">
                        <i class="bi bi-fullscreen" id="fullscreenIcon"></i>
                    </button>

                    <!-- Close Button -->
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
            </div>

            <!-- Modal Body (Preview Canvas) -->
            <div class="modal-body p-0 position-relative flex-grow-1" id="previewModalBody" style="background:#525659; overflow:auto;">
                <!-- Loading Spinner -->
                <div id="previewLoading" class="position-absolute top-50 start-50 translate-middle text-center py-5 bg-white bg-opacity-75 rounded-3 p-4 shadow-sm" style="z-index:10;">
                    <div class="spinner-border text-primary mb-2" role="status" style="width:2.5rem; height:2.5rem;">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <div class="text-dark small fw-semibold" id="previewLoadingText">Đang tải dữ liệu tệp...</div>
                </div>

                <!-- Dynamic Content Injection Container -->
                <div id="previewContent" class="h-100 w-100 position-relative" style="min-height:350px;"></div>
            </div>

            <!-- Excel Sheet Tabs (Visible only for spreadsheet files) -->
            <div id="previewSheetTabs" class="bg-white border-top px-3 py-1.5 d-none" style="overflow-x:auto; white-space:nowrap;"></div>
        </div>
    </div>
</div>

<style>
/* Word document sheet preview styling */
.word-preview-wrapper {
    background: #525659;
    padding: 30px 15px;
    display: flex;
    justify-content: center;
    min-height: 100%;
}
.word-sheet-page {
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
    border-radius: 4px;
    width: 100%;
    max-width: 860px;
    min-height: 900px;
    padding: 48px 56px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #172B4D;
    line-height: 1.65;
    font-size: 0.95rem;
}
.word-sheet-page h1, .word-sheet-page h2, .word-sheet-page h3, .word-sheet-page h4 {
    color: #091E42;
    margin-top: 1.4rem;
    margin-bottom: 0.7rem;
    font-weight: 700;
}
.word-sheet-page table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.2rem 0;
    font-size: 0.88rem;
}
.word-sheet-page table, .word-sheet-page th, .word-sheet-page td {
    border: 1px solid #DFE1E6;
    padding: 8px 12px;
}
.word-sheet-page th {
    background: #F4F5F7;
    font-weight: 600;
    text-align: left;
}
.word-sheet-page img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin: 8px 0;
}
.word-sheet-page ul, .word-sheet-page ol {
    padding-left: 24px;
}

/* Excel preview table styling */
.excel-table-scroll {
    background: #ffffff;
    overflow: auto;
    width: 100%;
    height: 100%;
    min-height: 60vh;
}
.excel-table-scroll table {
    border-collapse: collapse;
    font-size: 0.82rem;
    font-family: Consolas, "Segoe UI", Tahoma, sans-serif;
    width: max-content;
    min-width: 100%;
}
.excel-table-scroll th, .excel-table-scroll td {
    border: 1px solid #D0D5DD;
    padding: 5px 10px;
    white-space: nowrap;
}
.excel-table-scroll tr:first-child td, .excel-table-scroll th {
    background: #F8F9FA;
    font-weight: 600;
    color: #344054;
    position: sticky;
    top: 0;
    z-index: 2;
}

/* Image preview canvas */
.image-preview-wrapper {
    background: #2b2d30;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    min-height: 65vh;
    overflow: auto;
    padding: 20px;
}
.image-preview-wrapper img {
    max-width: 95%;
    max-height: 75vh;
    object-fit: contain;
    transition: transform 0.2s cubic-bezier(0.2, 0, 0, 1);
    box-shadow: 0 4px 20px rgba(0,0,0,0.4);
    border-radius: 6px;
}
</style>

<!-- SCRIPT LIBRARIES FOR IN-BROWSER PREVIEW (DOCX & XLSX) -->
<script src="<?= $baseUrl ?>/js/mammoth.browser.min.js"></script>
<script src="<?= $baseUrl ?>/js/xlsx.full.min.js"></script>

<script>
// Universal Attachment Preview Controller
(function() {
    let currentZoom = 1;
    let currentRotation = 0;
    let currentRawText = '';

    const previewModalEl = document.getElementById('filePreviewModal');
    const previewModalDialog = document.getElementById('previewModalDialog');
    const previewLoading = document.getElementById('previewLoading');
    const previewLoadingText = document.getElementById('previewLoadingText');
    const previewContent = document.getElementById('previewContent');
    const previewModalLabel = document.getElementById('filePreviewModalLabel');
    const previewFileMeta = document.getElementById('previewFileMeta');
    const previewHeaderIcon = document.getElementById('previewHeaderIcon');
    const btnPreviewNewTab = document.getElementById('btnPreviewNewTab');
    const btnPreviewDownload = document.getElementById('btnPreviewDownload');
    const btnToggleFullscreen = document.getElementById('btnToggleFullscreen');
    const fullscreenIcon = document.getElementById('fullscreenIcon');
    const previewImageControls = document.getElementById('previewImageControls');
    const btnCopyText = document.getElementById('btnCopyText');
    const previewSheetTabs = document.getElementById('previewSheetTabs');

    let previewBsModal = null;

    function showLoading(text) {
        if (previewLoading) {
            previewLoading.classList.remove('d-none');
            if (previewLoadingText) previewLoadingText.textContent = text || 'Đang tải dữ liệu tệp...';
        }
    }

    function hideLoading() {
        if (previewLoading) {
            previewLoading.classList.add('d-none');
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderErrorPlaceholder(message, downloadUrl, fileName) {
        hideLoading();
        previewContent.innerHTML = `
            <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5 bg-white rounded-3 m-3 shadow-sm text-center">
                <div class="rounded-circle p-3 bg-light text-muted mb-3" style="width:64px;height:64px;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-exclamation-triangle fs-2 text-warning"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Không thể hiển thị xem trước trực tiếp</h5>
                <p class="text-muted small mb-3 max-w-md" style="max-width:480px;">${escapeHtml(message)}</p>
                <div class="d-flex gap-2">
                    <a href="${downloadUrl}" class="btn btn-primary d-flex align-items-center gap-1.5">
                        <i class="bi bi-download"></i> Tải về tệp "${escapeHtml(fileName)}"
                    </a>
                </div>
            </div>
        `;
    }

    // Image Zoom & Rotation Handlers
    function updateImageTransform() {
        const img = document.getElementById('previewImgElement');
        if (img) {
            img.style.transform = `scale(${currentZoom}) rotate(${currentRotation}deg)`;
        }
    }

    document.getElementById('btnZoomIn')?.addEventListener('click', function() {
        currentZoom = Math.min(currentZoom + 0.25, 3.5);
        updateImageTransform();
    });

    document.getElementById('btnZoomOut')?.addEventListener('click', function() {
        currentZoom = Math.max(currentZoom - 0.25, 0.4);
        updateImageTransform();
    });

    document.getElementById('btnRotate')?.addEventListener('click', function() {
        currentRotation = (currentRotation + 90) % 360;
        updateImageTransform();
    });

    document.getElementById('btnResetZoom')?.addEventListener('click', function() {
        currentZoom = 1;
        currentRotation = 0;
        updateImageTransform();
    });

    // Copy Text Handler
    btnCopyText?.addEventListener('click', function() {
        if (!currentRawText) return;
        navigator.clipboard.writeText(currentRawText).then(() => {
            const originalHtml = btnCopyText.innerHTML;
            btnCopyText.innerHTML = '<i class="bi bi-check2 text-success me-1"></i>Đã sao chép';
            setTimeout(() => { btnCopyText.innerHTML = originalHtml; }, 2000);
        }).catch(err => {
            alert('Không thể sao chép: ' + err);
        });
    });

    // Fullscreen Toggle Handler
    btnToggleFullscreen?.addEventListener('click', function() {
        if (previewModalDialog.classList.contains('modal-fullscreen')) {
            previewModalDialog.classList.remove('modal-fullscreen');
            fullscreenIcon.className = 'bi bi-fullscreen';
        } else {
            previewModalDialog.classList.add('modal-fullscreen');
            fullscreenIcon.className = 'bi bi-fullscreen-exit';
        }
    });

    // Main Preview Trigger Function
    function openPreviewModal(btn) {
        const fileId = btn.getAttribute('data-file-id');
        const fileName = btn.getAttribute('data-file-name') || 'Tệp đính kèm';
        const fileSize = btn.getAttribute('data-file-size') || '';
        const fileExt = (btn.getAttribute('data-file-ext') || '').toLowerCase();
        const viewUrl = btn.getAttribute('data-view-url');
        const downloadUrl = btn.getAttribute('data-download-url');
        const uploader = btn.getAttribute('data-uploader') || 'Hệ thống';
        const date = btn.getAttribute('data-date') || '';

        // Reset state
        currentZoom = 1;
        currentRotation = 0;
        currentRawText = '';
        previewContent.innerHTML = '';
        if (previewSheetTabs) {
            previewSheetTabs.innerHTML = '';
            previewSheetTabs.classList.add('d-none');
        }
        previewImageControls?.classList.add('d-none');
        btnCopyText?.classList.add('d-none');

        // Setup Header & Action Links
        if (previewModalLabel) previewModalLabel.textContent = fileName;
        if (previewFileMeta) previewFileMeta.textContent = `${fileSize} • Đăng bởi ${uploader}${date ? ' • ' + date : ''}`;
        if (btnPreviewNewTab) btnPreviewNewTab.href = viewUrl;
        if (btnPreviewDownload) btnPreviewDownload.href = downloadUrl;

        // Set Header Icon
        let iconClass = 'bi-file-earmark-fill';
        let iconBg = '#EFF8FF';
        let iconColor = '#175CD3';

        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExt)) {
            iconClass = 'bi-file-earmark-image-fill';
            iconBg = '#FDF2FA';
            iconColor = '#C11574';
        } else if (fileExt === 'pdf') {
            iconClass = 'bi-file-earmark-pdf-fill';
            iconBg = '#FEF3F2';
            iconColor = '#B42318';
        } else if (['docx', 'doc'].includes(fileExt)) {
            iconClass = 'bi-file-earmark-word-fill';
            iconBg = '#EFF8FF';
            iconColor = '#175CD3';
        } else if (['xlsx', 'xls', 'csv'].includes(fileExt)) {
            iconClass = 'bi-file-earmark-excel-fill';
            iconBg = '#ECFDF3';
            iconColor = '#027A48';
        } else if (['txt', 'log', 'json', 'sql', 'md'].includes(fileExt)) {
            iconClass = 'bi-file-earmark-code-fill';
            iconBg = '#F8F9FA';
            iconColor = '#344054';
        }

        if (previewHeaderIcon) {
            previewHeaderIcon.style.background = iconBg;
            previewHeaderIcon.style.color = iconColor;
            previewHeaderIcon.innerHTML = `<i class="bi ${iconClass} fs-5"></i>`;
        }

        // Show Modal
        if (!previewBsModal) {
            previewBsModal = new bootstrap.Modal(previewModalEl);
        }
        previewBsModal.show();
        showLoading('Đang kết nối và tải tệp tin...');

        // 1. IMAGE PREVIEW
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExt)) {
            previewImageControls?.classList.remove('d-none');
            const img = new Image();
            img.id = 'previewImgElement';
            img.alt = fileName;
            img.onload = function() {
                hideLoading();
                previewContent.innerHTML = `
                    <div class="image-preview-wrapper">
                        <img id="previewImgElement" src="${viewUrl}" alt="${escapeHtml(fileName)}" style="max-height:75vh; max-width:95%; object-fit:contain; border-radius:6px; box-shadow:0 4px 20px rgba(0,0,0,0.35);">
                    </div>
                `;
            };
            img.onerror = function() {
                renderErrorPlaceholder('Không thể hiển thị hình ảnh này. File có thể bị hỏng hoặc liên kết đã thay đổi.', downloadUrl, fileName);
            };
            img.src = viewUrl;
            return;
        }

        // 2. PDF PREVIEW
        if (fileExt === 'pdf') {
            hideLoading();
            previewContent.innerHTML = `
                <div class="w-100 h-100" style="min-height:75vh; background:#ffffff;">
                    <iframe src="${viewUrl}#toolbar=1" class="w-100 h-100 border-0" style="min-height:75vh;" title="${escapeHtml(fileName)}"></iframe>
                </div>
            `;
            return;
        }

        // 3. WORD DOCUMENT (.docx)
        if (fileExt === 'docx') {
            showLoading('Đang phân tích cấu trúc tài liệu Word (.docx)...');
            fetch(viewUrl)
                .then(res => {
                    if (!res.ok) throw new Error('Máy chủ phản hồi mã lỗi ' + res.status);
                    return res.arrayBuffer();
                })
                .then(arrayBuffer => {
                    if (typeof mammoth === 'undefined') {
                        throw new Error('Thư viện đọc Word Mammoth.js chưa được nạp.');
                    }
                    return mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
                })
                .then(result => {
                    hideLoading();
                    const html = result.value || '';
                    previewContent.innerHTML = `
                        <div class="word-preview-wrapper">
                            <div class="word-sheet-page">
                                ${html || '<p class="text-muted fst-italic text-center py-5">Tài liệu Word này không chứa văn bản.</p>'}
                            </div>
                        </div>
                    `;
                })
                .catch(err => {
                    renderErrorPlaceholder('Lỗi khi đọc file Word: ' + err.message + '. Bạn có thể tải file về để xem bằng Microsoft Word.', downloadUrl, fileName);
                });
            return;
        }

        // 4. EXCEL SPREADSHEET (.xlsx, .xls, .csv)
        if (['xlsx', 'xls', 'csv'].includes(fileExt)) {
            showLoading('Đang đọc các bảng tính Excel (.xlsx)...');
            fetch(viewUrl)
                .then(res => {
                    if (!res.ok) throw new Error('Máy chủ phản hồi mã lỗi ' + res.status);
                    return res.arrayBuffer();
                })
                .then(arrayBuffer => {
                    if (typeof XLSX === 'undefined') {
                        throw new Error('Thư viện bảng tính XLSX chưa được nạp.');
                    }
                    const data = new Uint8Array(arrayBuffer);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const sheetNames = workbook.SheetNames || [];

                    if (sheetNames.length === 0) {
                        throw new Error('Không tìm thấy trang tính nào trong file Excel này.');
                    }

                    // Render Sheet Tabs Bar
                    if (previewSheetTabs) {
                        previewSheetTabs.classList.remove('d-none');
                        previewSheetTabs.innerHTML = `
                            <div class="d-flex align-items-center gap-1.5 py-1">
                                <span class="text-muted small me-2"><i class="bi bi-file-earmark-spreadsheet me-1 text-success"></i>Trang tính (${sheetNames.length}):</span>
                                ${sheetNames.map((name, idx) => `
                                    <button type="button" class="btn btn-sm ${idx === 0 ? 'btn-success text-white' : 'btn-outline-secondary'} py-0 px-2.5 rounded-1 excel-sheet-tab" data-sheet-index="${idx}" style="font-size:0.75rem;">
                                        ${escapeHtml(name)}
                                    </button>
                                `).join('')}
                            </div>
                        `;
                    }

                    function displaySheet(sheetIdx) {
                        const targetSheetName = sheetNames[sheetIdx];
                        const worksheet = workbook.Sheets[targetSheetName];
                        const htmlTable = XLSX.utils.sheet_to_html(worksheet, { id: 'excel-live-table' });
                        previewContent.innerHTML = `
                            <div class="excel-table-scroll p-3">
                                ${htmlTable}
                            </div>
                        `;
                        const tableEl = document.getElementById('excel-live-table');
                        if (tableEl) {
                            tableEl.className = 'table table-sm table-bordered table-striped table-hover mb-0 bg-white';
                        }
                    }

                    // Attach tab clicks
                    previewSheetTabs?.querySelectorAll('.excel-sheet-tab').forEach(tabBtn => {
                        tabBtn.addEventListener('click', function() {
                            previewSheetTabs.querySelectorAll('.excel-sheet-tab').forEach(b => {
                                b.className = 'btn btn-sm btn-outline-secondary py-0 px-2.5 rounded-1 excel-sheet-tab';
                            });
                            this.className = 'btn btn-sm btn-success text-white py-0 px-2.5 rounded-1 excel-sheet-tab';
                            const sIdx = parseInt(this.getAttribute('data-sheet-index')) || 0;
                            displaySheet(sIdx);
                        });
                    });

                    displaySheet(0);
                    hideLoading();
                })
                .catch(err => {
                    renderErrorPlaceholder('Lỗi đọc bảng tính Excel: ' + err.message + '. Bạn có thể tải file về để mở bằng Microsoft Excel.', downloadUrl, fileName);
                });
            return;
        }

        // 5. TEXT / LOG / CODE / SQL / JSON / MD
        if (['txt', 'log', 'json', 'sql', 'md', 'html', 'xml'].includes(fileExt)) {
            showLoading('Đang đọc tệp văn bản...');
            btnCopyText?.classList.remove('d-none');
            fetch(viewUrl)
                .then(res => {
                    if (!res.ok) throw new Error('Máy chủ phản hồi mã lỗi ' + res.status);
                    return res.text();
                })
                .then(text => {
                    hideLoading();
                    currentRawText = text;
                    previewContent.innerHTML = `
                        <div class="p-3 bg-white h-100" style="overflow:auto; min-height:65vh;">
                            <pre class="bg-light p-3 rounded-2 border text-dark font-monospace mb-0" style="font-size:0.85rem; line-height:1.6; tab-size:4; max-height:75vh; overflow:auto; white-space:pre-wrap;"><code>${escapeHtml(text)}</code></pre>
                        </div>
                    `;
                })
                .catch(err => {
                    renderErrorPlaceholder('Lỗi đọc tệp văn bản: ' + err.message, downloadUrl, fileName);
                });
            return;
        }

        // 6. FALLBACK FOR OTHER FILE TYPES (ZIP, RAR, etc.)
        renderErrorPlaceholder(`Định dạng .${fileExt.toUpperCase()} không hỗ trợ xem trực tiếp trên trình duyệt. Vui lòng tải về máy để giải nén hoặc sử dụng phần mềm chuyên dụng.`, downloadUrl, fileName);
    }

    // Bind click events on all preview buttons (event delegation)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.preview-file-btn');
        if (btn) {
            e.preventDefault();
            openPreviewModal(btn);
        }
    });
})();
</script>

