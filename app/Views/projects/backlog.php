<?php
/**
 * @var array $project
 * @var array $activeSprint
 * @var array $backlog
 * @var array $pagination
 * @var array $members
 * @var array $stats
 * @var array $filters
 */

use App\Core\Auth;
use App\Core\Response;
$baseUrl = Response::baseUrl();

$typeIcons = [
    'BACKLOG'  => ['icon' => 'bi-box-seam-fill',          'color' => '#00875A', 'label' => 'Backlog'],
    'STORY'    => ['icon' => 'bi-bookmark-star-fill',     'color' => '#00875A', 'label' => 'Story'],
    'TASK'     => ['icon' => 'bi-check-square-fill',      'color' => '#0052CC', 'label' => 'Task'],
    'BUG'      => ['icon' => 'bi-bug-fill',               'color' => '#DE350B', 'label' => 'Bug'],
    'TESTCASE' => ['icon' => 'bi-clipboard-check-fill',  'color' => '#6554C0', 'label' => 'TestCase'],
    'EPIC'     => ['icon' => 'bi-lightning-charge-fill',  'color' => '#FFAB00', 'label' => 'Epic']
];
?>

<div class="container-fluid px-0">
    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects" class="text-decoration-none text-secondary">Projects</a></li>
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="text-decoration-none text-secondary"><?= htmlspecialchars($project['name']) ?></a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold">Backlog</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-layers-half text-success"></i> Quản Lý Backlog & Sprints
            </h3>
            <p class="text-muted small mb-0">Hạng mục Backlog cấp cha, phân rã công việc cho thành viên và đưa vào Sprint (Tối đa 20/trang)</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-outline-primary fw-semibold px-3">
                <i class="bi bi-kanban me-1"></i> Xem Bảng Board
            </a>
            <!-- Nút tạo Backlog (User & Manager đều tự tạo được) -->
            <button class="btn btn-sm btn-success fw-semibold px-3" onclick="openCreateBacklogModal()">
                <i class="bi bi-plus-circle me-1"></i> Tạo Backlog
            </button>
            <!-- Nút tạo Task (Manager gán người, User tự tạo tự gán) -->
            <button class="btn btn-sm btn-primary fw-semibold px-3" style="background:#0C66E4; border-color:#0C66E4;" onclick="openCreateTaskModal()">
                <i class="bi bi-plus-lg me-1"></i> Tạo Task
            </button>
        </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="position-relative" style="width: 320px;">
            <input type="text" id="backlogSearch" class="form-control form-control-sm ps-4" placeholder="Tìm kiếm nhanh tiêu đề, mã key..." oninput="filterBacklogRows(this.value)">
            <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 7px; font-size: 12px;"></i>
        </div>
        <div class="text-muted small">
            <i class="bi bi-info-circle me-1 text-primary"></i> <strong>Quy tắc phân cấp:</strong> Bug, Task, Issue là con của Backlog; TestCase là con của Bug hoặc Issue.
        </div>
    </div>

    <!-- SECTION 1: ACTIVE SPRINT (BOARD ISSUES) -->
    <div class="card border rounded-3 mb-4 shadow-sm">
        <div class="card-header bg-light border-bottom p-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-down text-secondary"></i>
                <h6 class="fw-bold text-dark mb-0">Sprint Hiện Tại (Active Sprint)</h6>
                <span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill px-2" id="activeSprintCount">
                    <?= count($activeSprint) ?> issues
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-primary py-0 px-2 fw-semibold" style="font-size: 12px; background:#0C66E4; border-color:#0C66E4;">
                    Vào Bảng Board <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="list-group list-group-flush" id="activeSprintList">
            <?php if (empty($activeSprint)): ?>
                <div class="p-4 text-center text-muted small fst-italic">
                    Chưa có công việc nào trong Sprint. Chuyển công việc từ Backlog lên hoặc tạo mới bên dưới.
                </div>
            <?php else: ?>
                <?php foreach ($activeSprint as $iss): ?>
                    <?php $typeMeta = $typeIcons[$iss['issue_type']] ?? $typeIcons['TASK']; ?>
                    <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 backlog-row" id="row_<?= $iss['id'] ?>" data-text="<?= htmlspecialchars(strtolower($iss['summary'] . ' ' . $iss['issue_key'])) ?>">
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-3">
                            <i class="bi <?= $typeMeta['icon'] ?>" style="color: <?= $typeMeta['color'] ?>; font-size: 15px;" title="<?= $iss['issue_type'] ?>"></i>
                            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['id'] ?>" class="text-decoration-none fw-semibold text-secondary small hover-primary text-nowrap">
                                <?= htmlspecialchars($iss['issue_key']) ?>
                            </a>
                            <span class="text-dark small text-truncate fw-semibold" title="<?= htmlspecialchars($iss['summary']) ?>">
                                <?= htmlspecialchars($iss['summary']) ?>
                            </span>

                            <!-- Hiển thị liên kết Backlog Cha nếu có -->
                            <?php if (!empty($iss['parent_key'])): ?>
                                <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['parent_id'] ?>" class="badge bg-light text-primary border text-decoration-none text-nowrap" style="font-size: 10px;" title="Mục Backlog cha">
                                    <i class="bi bi-arrow-return-right me-1"></i><?= htmlspecialchars($iss['parent_key']) ?>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($iss['component'])): ?>
                                <span class="badge bg-light text-secondary border" style="font-size: 10px;">
                                    <?= htmlspecialchars($iss['component']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($iss['issue_type'] === 'TESTCASE'): ?>
                                <span class="badge py-0 px-2 border" style="font-size: 10px; background: #EAE6FF; color: #403294;">
                                    <?= $iss['test_execution_status'] ?>
                                </span>
                            <?php elseif ($iss['issue_type'] === 'BUG'): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 10px;">
                                    <?= $iss['bug_severity'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <!-- Status pill -->
                            <span class="badge bg-light text-secondary border" style="font-size: 11px;">
                                <?= $iss['status'] ?>
                            </span>
                            <!-- Story points -->
                            <span class="badge bg-secondary bg-opacity-10 text-dark rounded-circle" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;" title="Story Points">
                                <?= $iss['story_points'] ?>
                            </span>
                            <!-- Assignee -->
                            <?php if ($iss['assignee_id']): ?>
                                <div class="jira-avatar-circle" style="width: 24px; height: 24px; font-size: 10px; background: #0C66E4;" title="<?= htmlspecialchars($iss['assignee_name'] ?? 'Assignee') ?>">
                                    <?= strtoupper(substr($iss['assignee_name'] ?: 'U', 0, 1)) ?>
                                </div>
                            <?php else: ?>
                                <div class="jira-avatar-circle text-muted bg-light border" style="width: 24px; height: 24px; font-size: 10px;" title="Chưa phân công">
                                    <i class="bi bi-person"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Move to backlog button -->
                            <button class="btn btn-sm btn-outline-secondary py-0 px-2 text-nowrap" style="font-size: 11px;" onclick="moveIssueSprint(<?= $iss['id'] ?>, 'BACKLOG')" title="Chuyển xuống Backlog">
                                <i class="bi bi-arrow-down"></i> Backlog
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Inline Quick Create Row in Active Sprint -->
        <div class="card-footer bg-white p-2 border-top">
            <form onsubmit="handleInlineCreate(event, 'ACTIVE_SPRINT')" class="d-flex align-items-center gap-2 m-0">
                <i class="bi bi-plus-lg text-primary ms-2"></i>
                <input type="text" class="form-control form-control-sm border-0 shadow-none" placeholder="+ Tạo nhanh công việc trong Sprint (gõ tiêu đề và Enter)..." required name="summary">
                <select name="issue_type" class="form-select form-select-sm border-0 bg-light" style="width: 120px;">
                    <option value="TASK">Task</option>
                    <option value="BUG">Bug</option>
                    <option value="TESTCASE">TestCase</option>
                    <option value="STORY">Story</option>
                </select>
                <button type="submit" class="btn btn-sm btn-light border py-1 px-2 text-nowrap" style="font-size: 11px;">Thêm</button>
            </form>
        </div>
    </div>

    <!-- SECTION 2: PRODUCT BACKLOG -->
    <div class="card border rounded-3 shadow-sm mb-4">
        <div class="card-header bg-light border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-down text-secondary"></i>
                <h6 class="fw-bold text-dark mb-0">Hạng Mục Backlog (Công Việc Chờ Đưa Vào Sprint)</h6>
                <span class="badge bg-secondary bg-opacity-25 text-secondary rounded-pill px-2" id="backlogCount">
                    <?= $pagination['total'] ?? count($backlog) ?> items
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-success py-0 px-2 fw-semibold" style="font-size: 12px;" onclick="openCreateBacklogModal()">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Hạng Mục Backlog
                </button>
                <span class="text-muted small">Tối đa 20 mục / trang</span>
            </div>
        </div>

        <div class="list-group list-group-flush" id="backlogList">
            <?php if (empty($backlog)): ?>
                <div class="p-4 text-center text-muted small fst-italic">
                    Không có công việc nào trong Backlog. Bấm nút "Tạo Backlog" hoặc "Tạo Task" ở góc trên để bổ sung.
                </div>
            <?php else: ?>
                <?php foreach ($backlog as $iss): ?>
                    <?php $typeMeta = $typeIcons[$iss['issue_type']] ?? $typeIcons['TASK']; ?>
                    <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 backlog-row" id="row_<?= $iss['id'] ?>" data-text="<?= htmlspecialchars(strtolower($iss['summary'] . ' ' . $iss['issue_key'])) ?>">
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-3">
                            <i class="bi <?= $typeMeta['icon'] ?>" style="color: <?= $typeMeta['color'] ?>; font-size: 15px;" title="<?= $iss['issue_type'] ?>"></i>
                            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['id'] ?>" class="text-decoration-none fw-semibold text-secondary small hover-primary text-nowrap">
                                <?= htmlspecialchars($iss['issue_key']) ?>
                            </a>
                            <span class="text-dark small text-truncate fw-semibold" title="<?= htmlspecialchars($iss['summary']) ?>">
                                <?= htmlspecialchars($iss['summary']) ?>
                            </span>

                            <!-- Badge số lượng việc con liên kết nếu là Backlog item -->
                            <?php if ($iss['issue_type'] === 'BACKLOG' || !empty($iss['children_count'])): ?>
                                <span class="badge bg-light text-success border text-nowrap" style="font-size: 10px;" title="Số lượng công việc con trực thuộc">
                                    <i class="bi bi-diagram-3-fill me-1"></i><?= (int)($iss['children_count'] ?? 0) ?> việc con
                                </span>
                            <?php endif; ?>

                            <!-- Hiển thị liên kết Backlog Cha nếu là Task con -->
                            <?php if (!empty($iss['parent_key'])): ?>
                                <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $iss['parent_id'] ?>" class="badge bg-light text-primary border text-decoration-none text-nowrap" style="font-size: 10px;" title="Mục Backlog cha">
                                    <i class="bi bi-arrow-return-right me-1"></i><?= htmlspecialchars($iss['parent_key']) ?>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($iss['component'])): ?>
                                <span class="badge bg-light text-secondary border" style="font-size: 10px;">
                                    <?= htmlspecialchars($iss['component']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <!-- Story points -->
                            <span class="badge bg-secondary bg-opacity-10 text-dark rounded-circle" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;" title="Story Points">
                                <?= $iss['story_points'] ?>
                            </span>
                            <!-- Assignee -->
                            <?php if ($iss['assignee_id']): ?>
                                <div class="jira-avatar-circle" style="width: 24px; height: 24px; font-size: 10px; background: #0C66E4;" title="<?= htmlspecialchars($iss['assignee_name'] ?? 'Assignee') ?>">
                                    <?= strtoupper(substr($iss['assignee_name'] ?: 'U', 0, 1)) ?>
                                </div>
                            <?php else: ?>
                                <div class="jira-avatar-circle text-muted bg-light border" style="width: 24px; height: 24px; font-size: 10px;" title="Chưa phân công">
                                    <i class="bi bi-person"></i>
                                </div>
                            <?php endif; ?>
                            <!-- Move to sprint button -->
                            <button class="btn btn-sm btn-primary py-0 px-2 text-nowrap" style="font-size: 11px; background:#0C66E4; border-color:#0C66E4;" onclick="moveIssueSprint(<?= $iss['id'] ?>, 'ACTIVE_SPRINT')" title="Đưa vào Sprint">
                                <i class="bi bi-arrow-up"></i> Vào Sprint
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- PAGINATION BAR CHO BACKLOG (Tối đa 20 / trang) -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-footer bg-white border-top py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small text-muted">
                    Hiển thị <?= min(($pagination['page'] - 1) * $pagination['per_page'] + 1, $pagination['total']) ?> - 
                    <?= min($pagination['page'] * $pagination['per_page'], $pagination['total']) ?> 
                    trên tổng số <strong><?= $pagination['total'] ?></strong> mục Backlog (tối đa 20 trên 1 trang)
                </div>
                <?php if ($pagination['total_pages'] > 1): ?>
                    <ul class="pagination pagination-sm tf-pagination mb-0">
                        <li class="page-item <?= ($pagination['page'] <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])) ?>">« Trước</a>
                        </li>
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                            <li class="page-item <?= ($p === $pagination['page']) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagination['page'] >= $pagination['total_pages']) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])) ?>">Sau »</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Inline Quick Create Row in Backlog -->
        <div class="card-footer bg-white p-2 border-top">
            <form onsubmit="handleInlineCreate(event, 'BACKLOG')" class="d-flex align-items-center gap-2 m-0">
                <i class="bi bi-plus-lg text-primary ms-2"></i>
                <input type="text" class="form-control form-control-sm border-0 shadow-none" placeholder="+ Tạo nhanh công việc vào Backlog (gõ tiêu đề và Enter)..." required name="summary">
                <select name="issue_type" class="form-select form-select-sm border-0 bg-light" style="width: 130px;">
                    <option value="TASK">📌 Task</option>
                    <option value="BACKLOG">📦 Backlog</option>
                    <option value="BUG">🐞 Bug</option>
                    <option value="TESTCASE">🧪 TestCase</option>
                    <option value="STORY">📖 Story</option>
                </select>
                <button type="submit" class="btn btn-sm btn-light border py-1 px-2 text-nowrap" style="font-size: 11px;">Thêm</button>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateBacklogModal() {
    const modalEl = document.getElementById('globalQuickCreateModal');
    if (modalEl) {
        const typeSelect = document.getElementById('qc_issue_type');
        if (typeSelect) {
            typeSelect.value = 'BACKLOG';
            toggleQcTypeFields('BACKLOG');
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function openCreateTaskModal() {
    const modalEl = document.getElementById('globalQuickCreateModal');
    if (modalEl) {
        const typeSelect = document.getElementById('qc_issue_type');
        if (typeSelect) {
            typeSelect.value = 'TASK';
            toggleQcTypeFields('TASK');
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function filterBacklogRows(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('.backlog-row').forEach(row => {
        const text = row.dataset.text || '';
        row.style.display = (!q || text.includes(q)) ? 'flex' : 'none';
    });
}

function moveIssueSprint(issueId, targetSprint) {
    const formData = new FormData();
    formData.append('issue_id', issueId);
    formData.append('sprint_status', targetSprint);

    fetch('<?= $baseUrl ?>/projects/api/move-sprint', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể di chuyển'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối: ' + err.message);
    });
}

function handleInlineCreate(e, sprintStatus) {
    e.preventDefault();
    const form = e.target;
    const summaryInput = form.querySelector('input[name="summary"]');
    const typeSelect = form.querySelector('select[name="issue_type"]');

    const formData = new FormData();
    formData.append('project_id', <?= $project['id'] ?>);
    formData.append('summary', summaryInput.value);
    formData.append('issue_type', typeSelect.value);
    formData.append('sprint_status', sprintStatus);
    formData.append('status', 'TO_DO');

    fetch('<?= $baseUrl ?>/projects/api/quick-create', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể tạo'));
        }
    })
    .catch(err => {
        alert('Lỗi kết nối: ' + err.message);
    });
}
</script>
