<?php
use App\Core\Response;
use App\Services\SlaService;

$baseUrl = Response::baseUrl();
?>

<!-- BREADCRUMB -->
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb breadcrumb-sm mb-0" style="font-size:0.8rem;">
        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none text-muted"><i class="bi bi-house me-1"></i>Trang chủ</a></li>
        <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Bảng Kanban</li>
    </ol>
</nav>

<!-- PAGE HERO -->
<div class="page-hero mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3"
     style="background:#fff; border-left:4px solid #FF991F; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); padding:20px 24px;">
    <div class="d-flex align-items-center gap-3">
        <div style="background:#FFF4E0; border-radius:10px; width:48px; height:48px; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-kanban-fill fs-4" style="color:#FF991F;"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-1" style="color:#172B4D;">Bảng Kanban Sự Cố</h4>
            <p class="text-muted small mb-0" style="color:#5E6C84;">Theo dõi tiến độ, điều phối và chuyển trạng thái ticket trực quan theo thời gian thực.</p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $baseUrl ?>/tickets" class="btn btn-outline-secondary fw-semibold btn-sm px-3">
            <i class="bi bi-list-ul me-1"></i>Dạng danh sách
        </a>
        <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary fw-semibold btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i>Tạo Ticket Mới
        </a>
    </div>
</div>

<!-- QUICK FILTERS BAR -->
<div class="card border-0 mb-4" style="border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08);">
    <div class="card-body p-3">
        <form action="<?= $baseUrl ?>/tickets/board" method="GET" class="row g-2 align-items-center" id="boardFiltersForm">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"
                          style="border:1.5px solid #DFE1E6; border-radius:6px 0 0 6px; background:#FAFBFC !important;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0"
                           style="border:1.5px solid #DFE1E6; border-radius:0 6px 6px 0; background:#FAFBFC;"
                           placeholder="Tìm mã hoặc tiêu đề..."
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="group_id" class="form-select form-select-sm"
                        style="border:1.5px solid #DFE1E6; border-radius:6px; background:#FAFBFC;"
                        onchange="this.form.submit()">
                    <option value="">-- Tất cả nhóm IT --</option>
                    <?php foreach ($itGroups as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= ($filters['group_id'] ?? '') == $g['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6 d-flex flex-wrap gap-2 align-items-center">
                <a href="<?= $baseUrl ?>/tickets/board?my_only=1"
                   class="btn btn-sm fw-semibold <?= !empty($filters['assignee_id']) ? 'btn-primary' : 'btn-outline-secondary' ?>"
                   style="border-radius:999px;">
                    <i class="bi bi-person-check me-1"></i>Chỉ của tôi
                </a>
                <a href="<?= $baseUrl ?>/tickets/board?urgent_only=1"
                   class="btn btn-sm fw-semibold <?= !empty($filters['urgent_only']) ? 'btn-danger' : 'btn-outline-danger' ?>"
                   style="border-radius:999px;">
                    <i class="bi bi-fire me-1"></i>Khẩn cấp
                </a>
                <a href="<?= $baseUrl ?>/tickets/board?overdue_only=1"
                   class="btn btn-sm fw-semibold <?= !empty($filters['overdue_only']) ? 'btn-danger' : 'btn-outline-secondary' ?>"
                   style="border-radius:999px;">
                    <i class="bi bi-exclamation-octagon me-1"></i>Quá hạn SLA
                </a>
                <?php if (!empty($filters['group_id']) || !empty($filters['assignee_id']) || !empty($filters['urgent_only']) || !empty($filters['overdue_only']) || !empty($filters['search'])): ?>
                    <a href="<?= $baseUrl ?>/tickets/board"
                       class="btn btn-sm btn-link text-muted text-decoration-none" style="border-radius:999px;">
                        <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">
                    <i class="bi bi-funnel-fill me-1"></i>Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KANBAN BOARD -->
<div class="kanban-board-container pb-4" style="overflow-x:auto;">
    <div class="d-flex gap-3 align-items-start" style="min-width:1280px;">

        <!-- COLUMN: NEW -->
        <?php
        $columns = [
            'NEW'         => ['label'=>'Mới tạo (To Do)',         'icon'=>'bi-circle',              'color'=>'#5E6C84', 'bg'=>'#F4F5F7', 'accent'=>'#DFE1E6', 'badgeBg'=>'#5E6C84'],
            'IN_PROGRESS' => ['label'=>'Đang xử lý (In Progress)','icon'=>'bi-play-circle-fill',   'color'=>'#0052CC', 'bg'=>'#E8F0FE', 'accent'=>'#0052CC', 'badgeBg'=>'#0052CC'],
            'WAITING'     => ['label'=>'Chờ phản hồi / Vendor',   'icon'=>'bi-pause-circle-fill',  'color'=>'#974F0C', 'bg'=>'#FFF4E0', 'accent'=>'#FF991F', 'badgeBg'=>'#FF991F'],
            'RESOLVED'    => ['label'=>'Đã xử lý (Resolved)',      'icon'=>'bi-check-circle-fill',  'color'=>'#006644', 'bg'=>'#E3FCEF', 'accent'=>'#00875A', 'badgeBg'=>'#00875A'],
            'CLOSED'      => ['label'=>'Đã đóng (Done)',           'icon'=>'bi-check-all',           'color'=>'#006644', 'bg'=>'#E3FCEF', 'accent'=>'#00875A', 'badgeBg'=>'#00875A'],
        ];
        foreach ($columns as $status => $col):
            $count = count($board[$status] ?? []);
        ?>
        <div class="kanban-col flex-fill rounded-3 p-2 border-0 position-relative"
             data-status="<?= $status === 'IN_PROGRESS' ? 'IN_PROGRESS' : $status ?>"
             style="min-width:245px; background:<?= $col['bg'] ?>; border-top:3px solid <?= $col['accent'] ?> !important; border-radius:10px !important;">
            <!-- Column Header -->
            <div class="d-flex justify-content-between align-items-center px-2 py-2 mb-2">
                <span class="fw-bold text-uppercase small d-flex align-items-center gap-2" style="color:<?= $col['color'] ?>; font-size:0.75rem; letter-spacing:.05em;">
                    <i class="bi <?= $col['icon'] ?>"></i>
                    <?= $col['label'] ?>
                </span>
                <span class="badge rounded-pill fw-bold text-white"
                      style="background:<?= $col['badgeBg'] ?>; font-size:0.72rem; min-width:22px; text-align:center;">
                    <?= $count ?>
                </span>
            </div>

            <!-- Cards List -->
            <div class="kanban-cards-list d-flex flex-column gap-2"
                 id="col-<?= $status ?>"
                 style="min-height:450px;">
                <?php foreach (($board[$status] ?? []) as $t): ?>
                    <?= renderKanbanCard($t, $baseUrl) ?>
                <?php endforeach; ?>
                <?php if (empty($board[$status] ?? [])): ?>
                    <div class="kanban-empty d-flex flex-column align-items-center justify-content-center py-5 text-center"
                         style="min-height:200px; border:2px dashed <?= $col['accent'] ?>; border-radius:8px; background:rgba(255,255,255,.5);">
                        <i class="bi bi-inbox text-muted fs-3 mb-2 opacity-50"></i>
                        <span class="text-muted small fst-italic">Không có ticket nào</span>
                        <span class="text-muted" style="font-size:0.7rem;">Kéo thả ticket vào đây</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

<?php
/**
 * Helper to render individual Jira Kanban card
 */
function renderKanbanCard(array $t, string $baseUrl): string {
    $isOverdue = (!empty($t['sla_due_at']) && strtotime($t['sla_due_at']) < time() && !in_array($t['status'], ['RESOLVED', 'CLOSED']));
    $priorityColor = $t['priority_color'] ?? '#0ea5e9';
    $assigneeInitials = !empty($t['assignee_name']) ? strtoupper(substr($t['assignee_name'], 0, 1)) : '?';

    ob_start();
    ?>
    <div class="kanban-card bg-white p-3 rounded-2 position-relative"
         id="card-<?= $t['id'] ?>"
         data-id="<?= $t['id'] ?>"
         data-current-status="<?= $t['status'] ?>"
         draggable="true"
         style="cursor:grab; border-left:4px solid <?= $priorityColor ?> !important; box-shadow:0 1px 3px rgba(0,0,0,.09); transition:box-shadow .15s, opacity .15s; border-radius:8px !important;">

        <!-- Header: Code + Dropdown -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>"
               class="text-decoration-none fw-bold text-primary"
               style="font-size:0.75rem; letter-spacing:.02em;">
                <?= htmlspecialchars($t['ticket_code']) ?>
            </a>
            <div class="dropdown">
                <button class="btn btn-sm border-0 p-0 text-muted d-flex align-items-center justify-content-center"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="width:20px;height:20px;background:transparent;">
                    <i class="bi bi-three-dots" style="font-size:0.85rem;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm py-1" style="font-size:0.78rem; border-radius:8px; min-width:170px;">
                    <li><h6 class="dropdown-header py-1" style="font-size:0.7rem;">Chuyển sang trạng thái:</h6></li>
                    <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="transitionTicket(<?= $t['id'] ?>, 'NEW')"><i class="bi bi-circle me-2 text-secondary"></i>Mới tạo</a></li>
                    <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="transitionTicket(<?= $t['id'] ?>, 'IN_PROGRESS')"><i class="bi bi-play-circle-fill me-2 text-primary"></i>Đang xử lý</a></li>
                    <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="transitionTicket(<?= $t['id'] ?>, 'WAITING_USER')"><i class="bi bi-pause-circle-fill me-2 text-warning"></i>Chờ phản hồi</a></li>
                    <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="transitionTicket(<?= $t['id'] ?>, 'RESOLVED')"><i class="bi bi-check-circle-fill me-2 text-success"></i>Đã xử lý</a></li>
                    <li><a class="dropdown-item py-1" href="javascript:void(0)" onclick="transitionTicket(<?= $t['id'] ?>, 'CLOSED')"><i class="bi bi-check-all me-2 text-success"></i>Đã đóng</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item py-1" href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>"><i class="bi bi-box-arrow-up-right me-2 text-primary"></i>Xem chi tiết</a></li>
                </ul>
            </div>
        </div>

        <!-- Title -->
        <div class="mb-2">
            <a href="<?= $baseUrl ?>/tickets/<?= $t['id'] ?>"
               class="text-dark fw-semibold text-decoration-none d-block small"
               style="line-height:1.4; overflow:hidden; display:-webkit-box !important; -webkit-line-clamp:2; -webkit-box-orient:vertical;"
               title="<?= htmlspecialchars($t['title']) ?>">
                <?= htmlspecialchars($t['title']) ?>
            </a>
        </div>

        <!-- SLA Alert -->
        <?php if ($isOverdue): ?>
            <div class="mb-2">
                <span class="badge text-white px-2 py-1" style="background:#DE350B; border-radius:999px; font-size:0.66rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>QUÁ HẠN SLA
                </span>
            </div>
        <?php elseif (($t['sla_status'] ?? '') === 'WARNING'): ?>
            <div class="mb-2">
                <span class="badge text-dark px-2 py-1" style="background:#FFF4E0; border:1px solid #FF991F; border-radius:999px; font-size:0.66rem;">
                    <i class="bi bi-clock-history me-1"></i>SẮP HẾT SLA
                </span>
            </div>
        <?php endif; ?>

        <!-- Footer: Priority + Group + Avatar -->
        <div class="d-flex justify-content-between align-items-center pt-2 mt-1" style="border-top:1px solid #F4F5F7;">
            <div class="d-flex align-items-center gap-1 flex-wrap">
                <span class="badge px-2 py-0 small"
                      style="background:<?= $priorityColor ?>; color:#fff; font-size:0.66rem; border-radius:999px;">
                    <i class="bi bi-flag-fill me-1"></i><?= htmlspecialchars($t['priority_name'] ?? 'Thường') ?>
                </span>
                <?php if (!empty($t['group_name'])): ?>
                    <span class="badge bg-light text-muted border px-2 py-0 small" style="font-size:0.66rem; border-radius:999px;">
                        <?= htmlspecialchars(substr($t['group_name'], 0, 14)) ?>
                    </span>
                <?php endif; ?>
            </div>
            <!-- Avatar -->
            <div class="user-avatar-circle text-white d-flex align-items-center justify-content-center fw-bold"
                 style="width:26px;height:26px;font-size:0.68rem;border-radius:50%;background:<?= !empty($t['assignee_name']) ? '#0052CC' : '#5E6C84' ?>; flex-shrink:0;"
                 title="Phụ trách: <?= htmlspecialchars($t['assignee_name'] ?? 'Chưa phân công') ?>">
                <?= $assigneeInitials ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<!-- KANBAN JAVASCRIPT -->
<script>
window.APP_BASE_URL = '<?= $baseUrl ?>';

function transitionTicket(ticketId, newStatus) {
    const card = document.getElementById('card-' + ticketId);
    if (!card) return;

    card.style.opacity = '0.5';
    card.style.transform = 'scale(.97)';

    fetch(`${window.APP_BASE_URL}/tickets/${ticketId}/transition-ajax`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            status: newStatus,
            reason: 'Chuyển cột trên Kanban Board'
        })
    })
    .then(res => res.json())
    .then(data => {
        card.style.opacity = '1';
        card.style.transform = '';
        if (data.success) {
            let targetColId = 'col-NEW';
            if (['ASSIGNED', 'IN_PROGRESS', 'REOPENED'].includes(newStatus)) {
                targetColId = 'col-IN_PROGRESS';
            } else if (['WAITING_USER', 'WAITING_VENDOR'].includes(newStatus)) {
                targetColId = 'col-WAITING';
            } else if (newStatus === 'RESOLVED') {
                targetColId = 'col-RESOLVED';
            } else if (newStatus === 'CLOSED') {
                targetColId = 'col-CLOSED';
            }
            const targetContainer = document.getElementById(targetColId);
            if (targetContainer) {
                targetContainer.prepend(card);
                card.setAttribute('data-current-status', newStatus);
                const emptyMsg = targetContainer.querySelector('.kanban-empty');
                if (emptyMsg) emptyMsg.remove();
            }
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể cập nhật trạng thái.'));
        }
    })
    .catch(() => {
        card.style.opacity = '1';
        card.style.transform = '';
        alert('Lỗi mạng khi chuyển trạng thái.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.kanban-card');
    const cols  = document.querySelectorAll('.kanban-col');

    cards.forEach(card => {
        card.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', card.dataset.id);
            card.classList.add('opacity-50');
            card.style.transform = 'rotate(2deg)';
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('opacity-50');
            card.style.transform = '';
        });
    });

    cols.forEach(col => {
        col.addEventListener('dragover', (e) => {
            e.preventDefault();
            col.style.boxShadow = 'inset 0 0 0 2px #0052CC';
        });
        col.addEventListener('dragleave', () => {
            col.style.boxShadow = '';
        });
        col.addEventListener('drop', (e) => {
            e.preventDefault();
            col.style.boxShadow = '';
            const ticketId   = e.dataTransfer.getData('text/plain');
            const targetStatus = col.dataset.status;
            if (ticketId && targetStatus) {
                transitionTicket(ticketId, targetStatus);
            }
        });
    });
});
</script>
