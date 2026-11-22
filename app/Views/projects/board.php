<?php
/**
 * @var array $project
 * @var array $boardColumns
 * @var array $members
 * @var array $stats
 * @var array $components
 * @var array $filters
 */
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

$colNames = [
    'TO_DO'       => ['title' => 'TO DO',       'icon' => ''],
    'IN_PROGRESS' => ['title' => 'IN PROGRESS', 'icon' => ''],
    'IN_REVIEW'   => ['title' => 'IN REVIEW',   'icon' => ''],
    'DONE'        => ['title' => 'DONE',        'icon' => '<i class="bi bi-check2 text-success me-1"></i>']
];
?>

<div class="container-fluid px-0">
    <!-- 1. BREADCRUMBS -->
    <div class="d-flex align-items-center justify-content-between mb-1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="/projects" class="text-decoration-none text-secondary">Projects</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= htmlspecialchars($project['name']) ?></li>
            </ol>
        </nav>
        <div class="small text-muted">
            <span class="badge bg-light text-secondary border"><?= htmlspecialchars($project['project_key']) ?></span>
            <span class="ms-1"><?= $project['project_type'] ?></span>
        </div>
    </div>

    <!-- 2. PAGE HEADER: TITLE, STAR, ACTIONS -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <h2 class="fw-bold text-dark mb-0" style="letter-spacing: -0.5px;">Board</h2>
            <button class="btn btn-sm btn-link text-secondary p-0 ms-2" title="Đánh dấu yêu thích" onclick="this.classList.toggle('text-warning')">
                <i class="bi bi-star fs-5"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm btn-light border-0 py-0 px-2 text-secondary" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots fs-5"></i>
                </button>
                <ul class="dropdown-menu shadow border-0 p-1">
                    <li><a class="dropdown-item small" href="/projects/<?= $project['id'] ?>/backlog"><i class="bi bi-layers me-2 text-info"></i> Xem Backlog</a></li>
                    <li><a class="dropdown-item small" href="/projects/<?= $project['id'] ?>/testcases"><i class="bi bi-clipboard-check me-2 text-purple"></i> Xem Test Cases</a></li>
                    <li><a class="dropdown-item small" href="/projects/<?= $project['id'] ?>/bugs"><i class="bi bi-bug me-2 text-danger"></i> Xem Bug Tracking</a></li>
                    <?php if (\App\Core\Auth::isManager() || \App\Core\Auth::can('projects.manage') || (isset($project['lead_id']) && (int)$project['lead_id'] === (int)\App\Core\Auth::id())): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item small" href="/projects/<?= $project['id'] ?>/settings"><i class="bi bi-gear me-2"></i> Cài đặt dự án</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- RIGHT ACTION BUTTONS -->
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-light border text-secondary dropdown-toggle py-1 px-2" style="font-size: 13px;" data-bs-toggle="dropdown" id="groupByDropdownBtn">
                    <span class="text-muted small">GROUP BY</span> <strong id="groupByCurrentLabel">None</strong>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" id="groupByDropdownMenu">
                    <li><a class="dropdown-item small active" href="javascript:void(0)" onclick="setGroupBy('none')"><i class="bi bi-grid-fill me-2 text-secondary"></i>None (Mặc định)</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setGroupBy('assignee')"><i class="bi bi-person-fill me-2 text-primary"></i>Assignee (Người nhận việc)</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setGroupBy('epic')"><i class="bi bi-diagram-3-fill me-2 text-success"></i>Backlog / Epic (Mục cha)</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setGroupBy('type')"><i class="bi bi-tag-fill me-2 text-info"></i>Sub-task / Loại công việc</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setGroupBy('priority')"><i class="bi bi-flag-fill me-2 text-danger"></i>Priority (Mức ưu tiên)</a></li>
                </ul>
            </div>

            <button class="btn btn-sm btn-light border text-dark py-1 px-3 d-flex align-items-center gap-2" style="font-size: 13px;" onclick="toggleBoardInsights()">
                <i class="bi bi-graph-up-arrow text-primary"></i> <strong>Insights</strong>
            </button>
        </div>
    </div>

    <!-- 3. JIRA FILTER TOOLBAR (SEARCH, AVATARS, DROPDOWNS) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <!-- Search Input -->
            <div class="position-relative" style="width: 200px;">
                <input type="text" id="boardSearchInput" class="form-control form-control-sm ps-4 pe-2" placeholder="Search this board" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" oninput="filterBoardCards()">
                <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 7px; font-size: 12px;"></i>
            </div>

            <!-- Overlapping Member Avatars Filter -->
            <div class="d-flex align-items-center" style="margin-left: -4px;">
                <div class="jira-avatar-circle me-1 <?= empty($filters['assignee_id']) ? 'active-filter' : '' ?>" title="Tất cả thành viên" style="background: #5E6C84;" onclick="filterByAssignee('')">
                    All
                </div>
                <?php 
                $visibleMembers = array_slice($members, 0, 5);
                $moreCount = max(0, count($members) - 5);
                $avatarColors = ['#0052CC', '#00875A', '#FF5630', '#6554C0', '#36B37E', '#FFAB00'];
                ?>
                <?php foreach ($visibleMembers as $idx => $m): ?>
                    <?php $color = $avatarColors[$idx % count($avatarColors)]; ?>
                    <div class="jira-avatar-circle <?= ($filters['assignee_id'] == $m['user_id']) ? 'active-filter' : '' ?>" 
                         style="background: <?= $color ?>; margin-left: -6px;" 
                         title="<?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['username']) ?>)" 
                         onclick="filterByAssignee(<?= $m['user_id'] ?>)">
                        <?= strtoupper(substr($m['full_name'] ?: $m['username'], 0, 1)) ?>
                    </div>
                <?php endforeach; ?>

                <?php if ($moreCount > 0): ?>
                    <div class="jira-avatar-circle text-dark bg-light border" style="margin-left: -6px; font-size: 10px;" title="Còn <?= $moreCount ?> thành viên khác">
                        +<?= $moreCount ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Epic Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light border dropdown-toggle py-1 px-2 text-secondary" style="font-size: 13px;" data-bs-toggle="dropdown">
                    Epic
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="filterByComponent('')">Tất cả Epics</a></li>
                    <?php foreach ($components as $c): ?>
                        <li><a class="dropdown-item small" href="javascript:void(0)" onclick="filterByComponent('<?= $c ?>')"><?= $c ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Type Dropdown (Task, Bug, TestCase, Story) -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light border dropdown-toggle py-1 px-2 text-secondary" style="font-size: 13px;" data-bs-toggle="dropdown">
                    <span id="currentTypeLabel"><?= !empty($filters['issue_type']) ? $filters['issue_type'] : 'Type' ?></span>
                </button>
                <ul class="dropdown-menu shadow border-0">
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="filterByType('')">Tất cả Types</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="filterByType('STORY')"><i class="bi bi-bookmark-star-fill text-success me-2"></i> Story</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="filterByType('TASK')"><i class="bi bi-check-square-fill text-primary me-2"></i> Task</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="filterByType('BUG')"><i class="bi bi-bug-fill text-danger me-2"></i> Bug</a></li>
                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="filterByType('TESTCASE')"><i class="bi bi-clipboard-check-fill text-purple me-2"></i> TestCase</a></li>
                </ul>
            </div>

            <?php if (!empty($filters['search']) || !empty($filters['assignee_id']) || !empty($filters['issue_type']) || !empty($filters['component'])): ?>
                <a href="/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small">
                    <i class="bi bi-x-circle me-1"></i> Xóa bộ lọc
                </a>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Nút tạo nhanh Issue trên Board -->
            <button class="btn btn-sm btn-primary py-1 px-3 fw-semibold" style="background:#0052cc; border-color:#0052cc; font-size:13px;" onclick="openBoardCreateModal('TO_DO')">
                <i class="bi bi-plus-lg me-1"></i> Tạo Issue
            </button>
        </div>
    </div>

    <!-- 4. INSIGHTS MINI PANEL (COLLAPSIBLE) -->
    <div id="boardInsightsPanel" class="p-3 bg-light rounded border mb-4 shadow-sm" style="display: none;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up me-1 text-primary"></i> Thống Kê Sprint Hiện Tại</h6>
            <button class="btn btn-sm btn-close" onclick="toggleBoardInsights()"></button>
        </div>
        <div class="row g-3 text-center">
            <div class="col-md-3">
                <div class="p-2 bg-white rounded border">
                    <div class="text-muted small">TỔNG ISSUES SPRINT</div>
                    <div class="fs-4 fw-bold text-dark"><?= count($boardColumns['TO_DO']) + count($boardColumns['IN_PROGRESS']) + count($boardColumns['IN_REVIEW']) + count($boardColumns['DONE']) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 bg-white rounded border">
                    <div class="text-muted small">HOÀN THÀNH (DONE)</div>
                    <div class="fs-4 fw-bold text-success"><?= count($boardColumns['DONE']) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 bg-white rounded border">
                    <div class="text-muted small">ĐANG THỰC HIỆN</div>
                    <div class="fs-4 fw-bold text-primary"><?= count($boardColumns['IN_PROGRESS']) + count($boardColumns['IN_REVIEW']) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 bg-white rounded border">
                    <div class="text-muted small">CẦN LÀM (TO DO)</div>
                    <div class="fs-4 fw-bold text-warning"><?= count($boardColumns['TO_DO']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. JIRA AGILE BOARD (4 COLUMNS: TO DO, IN PROGRESS, IN REVIEW, DONE) -->
    <div id="boardContainer">
        <div class="jira-board-wrap" id="defaultBoardWrap">
        <?php foreach ($colNames as $colKey => $colMeta): ?>
            <?php $cards = $boardColumns[$colKey] ?? []; ?>
            <div class="jira-board-col" data-status="<?= $colKey ?>">
                <!-- Column Header -->
                <div class="jira-col-header">
                    <div class="d-flex align-items-center">
                        <?= $colMeta['icon'] ?>
                        <span><?= $colMeta['title'] ?></span>
                        <span class="jira-col-count ms-2" id="count_<?= $colKey ?>"><?= count($cards) ?></span>
                    </div>
                    <button class="btn btn-sm btn-link text-secondary p-0 text-decoration-none" title="Thêm công việc vào <?= $colMeta['title'] ?>" onclick="openBoardCreateModal('<?= $colKey ?>')">
                        <i class="bi bi-plus-lg fs-6"></i>
                    </button>
                </div>

                <!-- Column Cards Drop Zone -->
                <div class="jira-col-cards" id="dropzone_<?= $colKey ?>" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event, '<?= $colKey ?>')">
                    <?php if (empty($cards)): ?>
                        <div class="empty-drop-placeholder text-center text-muted py-4 small opacity-50 fst-italic">
                            Kéo thẻ vào đây
                        </div>
                    <?php endif; ?>

                    <?php foreach ($cards as $iss): ?>
                        <?php 
                        $typeMeta = $typeIcons[$iss['issue_type']] ?? $typeIcons['TASK'];
                        $componentLower = strtolower($iss['component'] ?? 'forms');
                        ?>
                        <div class="jira-card" id="card_<?= $iss['id'] ?>" 
                             draggable="true" 
                             ondragstart="handleDragStart(event, <?= $iss['id'] ?>)" 
                             ondragend="handleDragEnd(event)"
                             data-id="<?= $iss['id'] ?>"
                             data-status="<?= $colKey ?>"
                             data-summary="<?= htmlspecialchars(strtolower($iss['summary'])) ?>"
                             data-key="<?= htmlspecialchars(strtolower($iss['issue_key'])) ?>"
                             data-assignee="<?= $iss['assignee_id'] ?>"
                             data-assignee-name="<?= htmlspecialchars($iss['assignee_name'] ?? 'Chưa phân công (Unassigned)') ?>"
                             data-type="<?= $iss['issue_type'] ?>"
                             data-priority="<?= $iss['priority'] ?? 'MEDIUM' ?>"
                             data-parent-id="<?= $iss['parent_id'] ?? '' ?>"
                             data-parent-key="<?= htmlspecialchars($iss['parent_key'] ?? '') ?>"
                             data-parent-summary="<?= htmlspecialchars($iss['parent_summary'] ?? 'Độc lập (Không có Backlog cha)') ?>"
                             data-component="<?= htmlspecialchars($iss['component'] ?? '') ?>"
                             onclick="viewIssueDetail(<?= $iss['id'] ?>)">
                            
                            <!-- Issue Title / Summary -->
                            <div class="jira-card-title">
                                <?= htmlspecialchars($iss['summary']) ?>
                            </div>

                            <!-- Component Tag Pill (e.g. BILLING, ACCOUNTS, FORMS, FEEDBACK) -->
                            <div class="d-flex align-items-center gap-1 flex-wrap mb-1">
                                <?php if (!empty($iss['component'])): ?>
                                    <span class="jira-card-tag tag-<?= $componentLower ?>">
                                        <?= htmlspecialchars($iss['component']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($iss['parent_key'])): ?>
                                    <span class="badge bg-light text-primary border" style="font-size: 10px;" title="Mục cha: <?= htmlspecialchars($iss['parent_summary'] ?? '') ?>">
                                        <i class="bi bi-diagram-3-fill me-1"></i><?= htmlspecialchars($iss['parent_key']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- TestCase / Bug Specific Indicator Badges -->
                            <?php if ($iss['issue_type'] === 'TESTCASE'): ?>
                                <div class="mb-2">
                                    <span class="badge py-1 px-2 border" style="font-size: 10px; background: #EAE6FF; color: #403294;">
                                        <i class="bi bi-clipboard-check me-1"></i> <?= $iss['test_execution_status'] ?>
                                    </span>
                                </div>
                            <?php elseif ($iss['issue_type'] === 'BUG'): ?>
                                <div class="mb-2">
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 10px;">
                                        <i class="bi bi-exclamation-octagon me-1"></i> <?= $iss['bug_severity'] ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Bottom Row: Type Icon + Key + Assignee Avatar -->
                            <div class="jira-card-footer">
                                <div class="jira-issue-key">
                                    <i class="bi <?= $typeMeta['icon'] ?>" style="color: <?= $typeMeta['color'] ?>; font-size: 13px;" title="<?= $typeMeta['label'] ?>"></i>
                                    <span><?= htmlspecialchars($iss['issue_key']) ?></span>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <?php if ($iss['assignee_id']): ?>
                                        <div class="jira-avatar-circle" style="width: 24px; height: 24px; font-size: 10px; background: #0052CC;" title="<?= htmlspecialchars($iss['assignee_name'] ?? 'Assignee') ?>">
                                            <?= strtoupper(substr($iss['assignee_name'] ?: 'U', 0, 1)) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="jira-avatar-circle text-muted bg-light border" style="width: 24px; height: 24px; font-size: 10px;" title="Chưa phân công">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Add Column / Plus button on far right -->
        <div class="d-flex align-items-start pt-2">
            <button class="btn btn-light border text-secondary py-2 px-3 rounded-3" title="Thêm cột mới" onclick="alert('Tính năng tùy biến cột đang được kích hoạt mặc định theo quy trình Scrum 4 bước.')">
                <i class="bi bi-plus-lg fs-5"></i>
            </button>
        </div>
        </div> <!-- End #defaultBoardWrap -->

        <!-- DYNAMIC SWIMLANES CONTAINER FOR GROUP BY -->
        <div id="swimlanesContainer" style="display: none;"></div>
    </div> <!-- End #boardContainer -->
</div>

<!-- ==========================================
     BOARD CREATE MODAL
     ========================================== -->
<div class="modal fade" id="boardCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill text-primary"></i> Tạo Công Việc Vào Cột <span id="targetColDisplay" class="badge bg-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="boardCreateForm" onsubmit="handleBoardCreate(event)">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <input type="hidden" name="status" id="bcm_status" value="TO_DO">
                <input type="hidden" name="sprint_status" value="ACTIVE_SPRINT">
                <input type="hidden" name="area" value="<?= htmlspecialchars($project['name']) ?>">
                <input type="hidden" name="iteration" value="<?= htmlspecialchars($project['name']) ?>\Sprint 1 (2026)">

                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">LOẠI CÔNG VIỆC <span class="text-danger">*</span></label>
                            <select name="issue_type" id="bcm_type" class="form-select form-select-sm" onchange="toggleBcmFields(this.value)">
                                <option value="TASK">📌 Task (Công việc)</option>
                                <option value="BUG">🐞 Bug (Lỗi phần mềm)</option>
                                <option value="TESTCASE">🧪 TestCase (Ca kiểm thử)</option>
                                <option value="STORY">📖 Story (User Story)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">NGƯỜI PHỤ TRÁCH (ASSIGNEE)</label>
                            <select name="assignee_id" class="form-select form-select-sm">
                                <option value="">-- Chưa phân công --</option>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m['user_id'] ?>"><?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['username']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-secondary">TIÊU ĐỀ TÓM TẮT <span class="text-danger">*</span></label>
                            <input type="text" name="summary" class="form-control" placeholder="Ví dụ: Fix purchasing bug, integrate payment..." required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-secondary">MÔ TẢ CHI TIẾT</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Chi tiết yêu cầu kỹ thuật..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">ĐỘ ƯU TIÊN</label>
                            <select name="priority" class="form-select form-select-sm">
                                <option value="HIGHEST">🔴 Highest</option>
                                <option value="HIGH">🟠 High</option>
                                <option value="MEDIUM" selected>🟡 Medium</option>
                                <option value="LOW">🔵 Low</option>
                                <option value="LOWEST">⚪ Lowest</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">COMPONENT</label>
                            <select name="component" class="form-select form-select-sm">
                                <option value="">-- Không có --</option>
                                <option value="BILLING">BILLING</option>
                                <option value="ACCOUNTS">ACCOUNTS</option>
                                <option value="FORMS">FORMS</option>
                                <option value="FEEDBACK">FEEDBACK</option>
                                <option value="SECURITY">SECURITY</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">STORY POINTS</label>
                            <input type="number" name="story_points" class="form-control form-control-sm" value="3" min="1" max="20">
                        </div>

                        <!-- TESTCASE SPECIFIC SECTION -->
                        <div id="bcm_testcase_sec" class="col-12 p-3 bg-light rounded border" style="display: none;">
                            <div class="fw-bold text-purple small mb-2"><i class="bi bi-clipboard-check me-1"></i> THÔNG TIN CA KIỂM THỬ</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Loại test</label>
                                    <select name="test_type" class="form-select form-select-sm">
                                        <option value="MANUAL">Manual Test</option>
                                        <option value="AUTOMATED">Automated Test</option>
                                        <option value="REGRESSION">Regression Test</option>
                                        <option value="SMOKE">Smoke Test</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Trạng thái ban đầu</label>
                                    <select name="test_execution_status" class="form-select form-select-sm">
                                        <option value="UNTESTED" selected>Untested</option>
                                        <option value="PASSED">Passed</option>
                                        <option value="FAILED">Failed</option>
                                        <option value="BLOCKED">Blocked</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-secondary">Các bước kiểm thử (Test steps)</label>
                                    <textarea name="test_steps" class="form-control form-control-sm" rows="2" placeholder="1. Vào giỏ hàng&#10;2. Bấm checkout..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-secondary">Kết quả mong đợi</label>
                                    <input type="text" name="test_expected_result" class="form-control form-control-sm" placeholder="Hệ thống xác nhận đơn hàng thành công...">
                                </div>
                            </div>
                        </div>

                        <!-- BUG SPECIFIC SECTION -->
                        <div id="bcm_bug_sec" class="col-12 p-3 bg-light rounded border border-danger border-opacity-25" style="display: none;">
                            <div class="fw-bold text-danger small mb-2"><i class="bi bi-bug-fill me-1"></i> THÔNG TIN LỖI (BUG DEFECT)</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Mức độ nghiêm trọng (Severity)</label>
                                    <select name="bug_severity" class="form-select form-select-sm">
                                        <option value="BLOCKER">🔴 Blocker</option>
                                        <option value="CRITICAL">🟠 Critical</option>
                                        <option value="MAJOR" selected>🟡 Major</option>
                                        <option value="MINOR">🔵 Minor</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Môi trường (Environment)</label>
                                    <input type="text" name="environment" class="form-control form-control-sm" placeholder="Chrome 122 Windows 11...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-secondary">Các bước tái hiện lỗi</label>
                                    <textarea name="steps_to_reproduce" class="form-control form-control-sm" rows="2" placeholder="Các bước làm phát sinh lỗi..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-2 border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" id="bcm_submit_btn" class="btn btn-sm btn-primary fw-bold px-3">
                        <i class="bi bi-check-lg me-1"></i> Tạo Issue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     DRAG & DROP + BOARD FILTER JAVASCRIPT
     ========================================== -->
<script>
let draggedIssueId = null;

function handleDragStart(e, id) {
    draggedIssueId = id;
    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', id);
}

function handleDragEnd(e) {
    e.target.classList.remove('dragging');
    document.querySelectorAll('.jira-col-cards').forEach(zone => zone.classList.remove('drag-over'));
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e, targetStatus) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    
    if (!draggedIssueId) return;

    const cardEl = document.getElementById('card_' + draggedIssueId);
    if (!cardEl) return;

    // Move DOM element to target zone immediately for instant responsiveness
    e.currentTarget.appendChild(cardEl);
    cardEl.dataset.status = targetStatus;

    // Remove empty placeholder if any
    const placeholder = e.currentTarget.querySelector('.empty-drop-placeholder');
    if (placeholder) placeholder.remove();

    // Update column counters
    updateAllColCounters();
    updateSwimlaneCounters();

    // Send AJAX update to server
    const formData = new FormData();
    formData.append('issue_id', draggedIssueId);
    formData.append('status', targetStatus);

    fetch('<?= $baseUrl ?>/projects/api/update-status', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            alert('Không thể lưu trạng thái: ' + (res.message || 'Lỗi'));
            window.location.reload();
        }
    })
    .catch(err => {
        console.error(err);
    });
}

function updateAllColCounters() {
    ['TO_DO', 'IN_PROGRESS', 'IN_REVIEW', 'DONE'].forEach(colKey => {
        const zone = document.getElementById('dropzone_' + colKey);
        const countBadge = document.getElementById('count_' + colKey);
        if (zone && countBadge) {
            const cards = zone.querySelectorAll('.jira-card');
            countBadge.innerText = cards.length;
        }
    });
}

// ==========================================
// GROUP BY & SWIMLANES ENGINE
// ==========================================
let currentGroupBy = 'none';

function toggleSwimlane(headerEl) {
    const lane = headerEl.closest('.jira-swimlane');
    if (lane) lane.classList.toggle('collapsed');
}

function setGroupBy(mode) {
    currentGroupBy = mode;
    localStorage.setItem('tf_board_groupby_<?= $project['id'] ?>', mode);

    const modeLabels = {
        'none': 'None',
        'assignee': 'Assignee',
        'epic': 'Backlog / Epic',
        'type': 'Sub-task / Type',
        'priority': 'Priority'
    };
    const label = modeLabels[mode] || 'None';
    const labelEl = document.getElementById('groupByCurrentLabel');
    if (labelEl) labelEl.innerText = label;

    document.querySelectorAll('#groupByDropdownMenu .dropdown-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('onclick')?.includes(`'${mode}'`)) {
            item.classList.add('active');
        }
    });

    const defaultWrap = document.getElementById('defaultBoardWrap');
    const swimlanesContainer = document.getElementById('swimlanesContainer');

    if (mode === 'none') {
        // Return all cards to their default dropzones
        document.querySelectorAll('.jira-card').forEach(card => {
            const status = card.dataset.status || 'TO_DO';
            const defaultZone = document.getElementById('dropzone_' + status);
            if (defaultZone && !defaultZone.contains(card)) {
                defaultZone.appendChild(card);
            }
        });
        swimlanesContainer.innerHTML = '';
        swimlanesContainer.style.display = 'none';
        defaultWrap.style.display = 'flex';
        updateAllColCounters();
        filterBoardCards();
        return;
    }

    // Otherwise, build dynamic swimlanes
    defaultWrap.style.display = 'none';
    swimlanesContainer.style.display = 'block';
    swimlanesContainer.innerHTML = '';

    // Collect all cards
    const allCards = Array.from(document.querySelectorAll('.jira-card'));

    // Group cards
    const groups = {};
    allCards.forEach(card => {
        let groupKey = '';
        let groupTitle = '';
        let groupIcon = '';

        if (mode === 'assignee') {
            const uid = card.dataset.assignee || '0';
            const uname = card.dataset.assigneeName || 'Chưa phân công (Unassigned)';
            groupKey = 'assignee_' + uid;
            groupTitle = uname;
            groupIcon = '<i class="bi bi-person-circle text-primary me-2"></i>';
        } else if (mode === 'epic') {
            const pKey = card.dataset.parentKey || '';
            const pSum = card.dataset.parentSummary || 'Độc lập (Không có Backlog cha)';
            groupKey = pKey ? ('parent_' + pKey) : 'parent_none';
            groupTitle = pKey ? (pKey + ' &bull; ' + pSum) : pSum;
            groupIcon = '<i class="bi bi-diagram-3-fill text-success me-2"></i>';
        } else if (mode === 'type') {
            const t = card.dataset.type || 'TASK';
            groupKey = 'type_' + t;
            groupTitle = t;
            groupIcon = '<i class="bi bi-tag-fill text-info me-2"></i>';
        } else if (mode === 'priority') {
            const p = card.dataset.priority || 'MEDIUM';
            groupKey = 'priority_' + p;
            groupTitle = 'Ưu tiên: ' + p;
            groupIcon = '<i class="bi bi-flag-fill text-danger me-2"></i>';
        }

        if (!groups[groupKey]) {
            groups[groupKey] = {
                title: groupTitle,
                icon: groupIcon,
                cards: []
            };
        }
        groups[groupKey].cards.push(card);
    });

    const colMetaList = [
        { key: 'TO_DO', title: 'TO DO', icon: '' },
        { key: 'IN_PROGRESS', title: 'IN PROGRESS', icon: '' },
        { key: 'IN_REVIEW', title: 'IN REVIEW', icon: '' },
        { key: 'DONE', title: 'DONE', icon: '<i class="bi bi-check2 text-success me-1"></i>' }
    ];

    // Build each swimlane DOM
    Object.keys(groups).forEach(gKey => {
        const grp = groups[gKey];
        const laneEl = document.createElement('div');
        laneEl.className = 'jira-swimlane';
        laneEl.id = 'swimlane_' + gKey;

        let colHtml = '';
        colMetaList.forEach(col => {
            colHtml += `
                <div class="jira-board-col" data-status="${col.key}">
                    <div class="jira-col-header small">
                        <div class="d-flex align-items-center">
                            ${col.icon}
                            <span>${col.title}</span>
                            <span class="jira-col-count ms-2 sw-col-count" data-col="${col.key}">0</span>
                        </div>
                    </div>
                    <div class="jira-col-cards" id="dz_${gKey}_${col.key}"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         ondrop="handleDrop(event, '${col.key}')">
                    </div>
                </div>
            `;
        });

        laneEl.innerHTML = `
            <div class="jira-swimlane-header d-flex align-items-center justify-content-between" onclick="toggleSwimlane(this)">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chevron-down jira-swimlane-chevron me-1"></i>
                    ${grp.icon}
                    <strong class="text-dark">${grp.title}</strong>
                    <span class="badge bg-secondary rounded-pill swimlane-total-badge ms-2">${grp.cards.length}</span>
                </div>
            </div>
            <div class="jira-swimlane-body">
                <div class="jira-board-wrap">
                    ${colHtml}
                </div>
            </div>
        `;

        swimlanesContainer.appendChild(laneEl);

        // Place cards into the swimlane columns
        grp.cards.forEach(card => {
            const st = card.dataset.status || 'TO_DO';
            const targetDropzone = laneEl.querySelector(`#dz_${gKey}_${st}`);
            if (targetDropzone) {
                targetDropzone.appendChild(card);
            }
        });
    });

    updateSwimlaneCounters();
    filterBoardCards();
}

function updateSwimlaneCounters() {
    document.querySelectorAll('.jira-swimlane').forEach(lane => {
        let total = 0;
        lane.querySelectorAll('.sw-col-count').forEach(badge => {
            const col = badge.dataset.col;
            const zone = lane.querySelector(`.jira-col-cards[id$="_${col}"]`);
            if (zone) {
                const count = zone.querySelectorAll('.jira-card').length;
                badge.innerText = count;
                total += count;
            }
        });
        const totalBadge = lane.querySelector('.swimlane-total-badge');
        if (totalBadge) totalBadge.innerText = total;
    });
}

// ==========================================
// REAL-TIME BOARD CARD FILTERS
// ==========================================
let currentAssigneeFilter = '<?= $filters['assignee_id'] ?? '' ?>';
let currentTypeFilter = '<?= $filters['issue_type'] ?? '' ?>';
let currentComponentFilter = '<?= $filters['component'] ?? '' ?>';

function filterBoardCards() {
    const searchVal = (document.getElementById('boardSearchInput').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.jira-card');

    cards.forEach(c => {
        const sum = c.dataset.summary || '';
        const key = c.dataset.key || '';
        const assignee = c.dataset.assignee || '';
        const type = c.dataset.type || '';
        const comp = c.dataset.component || '';

        const matchSearch = (!searchVal || sum.includes(searchVal) || key.includes(searchVal) || comp.toLowerCase().includes(searchVal));
        const matchAssignee = (!currentAssigneeFilter || assignee == currentAssigneeFilter);
        const matchType = (!currentTypeFilter || type == currentTypeFilter);
        const matchComp = (!currentComponentFilter || comp == currentComponentFilter);

        c.style.display = (matchSearch && matchAssignee && matchType && matchComp) ? 'block' : 'none';
    });

    // If in swimlane mode, hide empty swimlanes
    if (currentGroupBy !== 'none') {
        document.querySelectorAll('.jira-swimlane').forEach(lane => {
            const visibleCards = lane.querySelectorAll('.jira-card[style*="display: block"], .jira-card:not([style*="display: none"])');
            lane.style.display = visibleCards.length > 0 ? 'block' : 'none';
            const totalBadge = lane.querySelector('.swimlane-total-badge');
            if (totalBadge) totalBadge.innerText = visibleCards.length;
        });
    }
}

function filterByAssignee(uid) {
    currentAssigneeFilter = uid ? String(uid) : '';
    document.querySelectorAll('.jira-avatar-circle').forEach(av => av.classList.remove('active-filter'));
    event.currentTarget.classList.add('active-filter');
    filterBoardCards();
}

function filterByType(t) {
    currentTypeFilter = t;
    document.getElementById('currentTypeLabel').innerText = t ? t : 'Type';
    filterBoardCards();
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('tf_board_groupby_<?= $project['id'] ?>');
    if (saved && saved !== 'none') {
        setGroupBy(saved);
    }
});

function filterByComponent(comp) {
    currentComponentFilter = comp;
    filterBoardCards();
}

function toggleBoardInsights() {
    const p = document.getElementById('boardInsightsPanel');
    if (p) p.style.display = (p.style.display === 'none') ? 'block' : 'none';
}

function viewIssueDetail(issueId) {
    window.location.href = '/projects/<?= $project['id'] ?>/issues/' + issueId;
}

// Quick Create on Board
function openBoardCreateModal(statusKey) {
    document.getElementById('bcm_status').value = statusKey;
    document.getElementById('targetColDisplay').innerText = statusKey.replace('_', ' ');
    const modal = new bootstrap.Modal(document.getElementById('boardCreateModal'));
    modal.show();
}

function toggleBcmFields(type) {
    const testSec = document.getElementById('bcm_testcase_sec');
    const bugSec = document.getElementById('bcm_bug_sec');
    if (testSec) testSec.style.display = (type === 'TESTCASE') ? 'block' : 'none';
    if (bugSec) bugSec.style.display = (type === 'BUG') ? 'block' : 'none';
}

function handleBoardCreate(e) {
    e.preventDefault();
    const form = document.getElementById('boardCreateForm');
    const btn = document.getElementById('bcm_submit_btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang tạo...';

    const formData = new FormData(form);

    fetch('<?= $baseUrl ?>/projects/api/quick-create', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Server returned non-JSON:', text);
            throw new Error(text.substring(0, 150));
        }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Tạo Issue';
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('boardCreateModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Tạo thành công!',
                text: res.message,
                timer: 1200,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            alert('Lỗi: ' + (res.message || 'Không thể tạo'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Tạo Issue';
        alert('Lỗi kết nối: ' + err.message);
    });
}
</script>

