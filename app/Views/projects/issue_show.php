<?php
/**
 * @var array $project
 * @var array $issue
 * @var array $members
 * @var array $comments
 * @var array $children
 * @var array $eligibleParents
 */

use App\Core\Auth;
use App\Core\Response;

$baseUrl = Response::baseUrl();

$typeMetaMap = [
    'BACKLOG'  => ['icon' => 'bi-box-seam-fill',          'color' => '#00875A', 'bg' => '#E3FCEF', 'label' => 'BACKLOG'],
    'TASK'     => ['icon' => 'bi-check-square-fill',      'color' => '#0052CC', 'bg' => '#DEEBFF', 'label' => 'TASK'],
    'BUG'      => ['icon' => 'bi-bug-fill',               'color' => '#DE350B', 'bg' => '#FFEBE6', 'label' => 'BUG'],
    'TESTCASE' => ['icon' => 'bi-clipboard-check-fill',  'color' => '#6554C0', 'bg' => '#EAE6FF', 'label' => 'TESTCASE'],
    'STORY'    => ['icon' => 'bi-bookmark-star-fill',     'color' => '#00875A', 'bg' => '#E3FCEF', 'label' => 'STORY'],
    'EPIC'     => ['icon' => 'bi-lightning-charge-fill',  'color' => '#FFAB00', 'bg' => '#FFF0B3', 'label' => 'EPIC']
];

$typeMeta = $typeMetaMap[$issue['issue_type']] ?? $typeMetaMap['TASK'];

$stateColors = [
    'TO_DO'       => ['label' => 'New / To Do',       'badge' => 'secondary'],
    'IN_PROGRESS' => ['label' => 'Active / In Progress', 'badge' => 'primary'],
    'IN_REVIEW'   => ['label' => 'Resolved / In Review', 'badge' => 'info'],
    'DONE'        => ['label' => 'Closed / Done',     'badge' => 'success']
];
$stateInfo = $stateColors[$issue['status']] ?? $stateColors['TO_DO'];
?>

<div class="container-fluid px-0" style="max-width: 1300px;">
    <!-- BREADCRUMBS & TOP CONTROLS -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects" class="text-decoration-none text-secondary">Projects</a></li>
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="text-decoration-none text-secondary"><?= htmlspecialchars($project['name']) ?></a></li>
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues" class="text-decoration-none text-secondary">Work Items</a></li>
                <li class="breadcrumb-item active text-dark fw-bold"><?= htmlspecialchars($issue['issue_key']) ?></li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-outline-secondary py-1 px-3">
                <i class="bi bi-kanban me-1"></i> Xem Board
            </a>
            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues" class="btn btn-sm btn-outline-secondary py-1 px-3">
                <i class="bi bi-list-task me-1"></i> Danh Sách
            </a>
        </div>
    </div>

    <!-- MAIN WORK ITEM FORM MATCHING IMAGE 2 -->
    <form action="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $issue['id'] ?>" method="POST" id="almWorkItemForm">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">

        <div class="alm-work-item-wrapper mb-4">
            <!-- TOP BAR (TYPE IDENTIFIER & ACTION BUTTONS) -->
            <div class="alm-top-action-bar">
                <div class="d-flex align-items-center gap-2">
                    <span class="alm-badge-type" style="background: <?= $typeMeta['bg'] ?>; color: <?= $typeMeta['color'] ?>;">
                        <i class="bi <?= $typeMeta['icon'] ?>"></i> <?= $typeMeta['label'] ?> <?= htmlspecialchars($issue['issue_key']) ?>
                    </span>
                    <?php if (!empty($issue['parent_key'])): ?>
                        <span class="badge bg-light text-dark border d-flex align-items-center gap-1" style="font-size: 11px;">
                            <i class="bi bi-diagram-3-fill text-primary"></i>
                            <span>Parent:</span>
                            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $issue['parent_id'] ?>" class="text-decoration-none fw-bold text-primary">
                                <?= htmlspecialchars($issue['parent_key']) ?>
                            </a>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3 d-flex align-items-center gap-1" style="background: #0C66E4; border-color: #0C66E4;">
                        <i class="bi bi-floppy-fill"></i> Save & Close
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()" title="Làm mới">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép link liên kết!')"><i class="bi bi-link-45deg me-2"></i> Sao chép liên kết</a></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)"><i class="bi bi-trash me-2"></i> Xóa công việc</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- LARGE TITLE INPUT (Image 2: Enter title) -->
            <div class="px-3 pt-3 pb-1">
                <input type="text" name="summary" class="alm-title-input" placeholder="Enter title" value="<?= htmlspecialchars($issue['summary']) ?>" required>
            </div>

            <!-- METADATA STRIP (State, Assigned To, Reason, Area, Iteration, Tags) -->
            <div class="alm-meta-strip">
                <!-- State Selector -->
                <div class="alm-meta-item">
                    <span class="alm-meta-label">State:</span>
                    <select name="status" class="form-select form-select-sm py-0 ps-2 pe-4 fw-semibold border" style="width: auto; height: 28px; font-size: 12px;">
                        <option value="TO_DO" <?= ($issue['status'] === 'TO_DO') ? 'selected' : '' ?>>⚪ New / To Do</option>
                        <option value="IN_PROGRESS" <?= ($issue['status'] === 'IN_PROGRESS') ? 'selected' : '' ?>>🔵 Active / In Progress</option>
                        <option value="IN_REVIEW" <?= ($issue['status'] === 'IN_REVIEW') ? 'selected' : '' ?>>🟡 Resolved / In Review</option>
                        <option value="DONE" <?= ($issue['status'] === 'DONE') ? 'selected' : '' ?>>🟢 Closed / Done</option>
                    </select>
                </div>

                <!-- Assigned To with Avatar & Self-Assign button -->
                <div class="alm-meta-item">
                    <span class="alm-meta-label">Assigned To:</span>
                    <div class="d-flex align-items-center gap-1">
                        <select name="assignee_id" id="issue_assignee_id" class="form-select form-select-sm py-0 ps-2 pe-4 border" style="width: auto; height: 28px; font-size: 12px;">
                            <option value="">-- Chưa phân công --</option>
                            <?php foreach ($members as $m): 
                                $uid = (int)($m['user_id'] ?? $m['id']);
                            ?>
                                <option value="<?= $uid ?>" <?= ((int)($issue['assignee_id'] ?? 0) === $uid) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-light border py-0 px-2 text-primary" style="height: 28px; font-size: 11px;" onclick="document.getElementById('issue_assignee_id').value='<?= Auth::id() ?>'">
                            Tự nhận việc
                        </button>
                    </div>
                </div>

                <!-- Area (Theo dự án thực tế) -->
                <?php
                $currentArea = trim($issue['area'] ?? '');
                if (empty($currentArea) || $currentArea === 'TC8') {
                    $currentArea = $project['name'];
                }
                $projectAreas = [
                    $project['name'],
                    $project['name'] . '\Frontend & UI',
                    $project['name'] . '\Backend & API',
                    $project['name'] . '\Database & Infrastructure',
                    $project['name'] . '\Core Banking & Integration',
                    $project['name'] . '\Security & Compliance',
                ];
                if (!in_array($currentArea, $projectAreas)) {
                    array_unshift($projectAreas, $currentArea);
                }
                ?>
                <div class="alm-meta-item">
                    <span class="alm-meta-label">Area:</span>
                    <select name="area" class="form-select form-select-sm py-0 ps-2 pe-4 border fw-medium" style="width: auto; min-width: 150px; height: 28px; font-size: 12px;">
                        <?php foreach ($projectAreas as $ar): ?>
                            <option value="<?= htmlspecialchars($ar) ?>" <?= ($currentArea === $ar) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ar) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Iteration (Theo dự án thực tế) -->
                <?php
                $currentIter = trim($issue['iteration'] ?? '');
                if (empty($currentIter) || str_contains($currentIter, 'TC8')) {
                    $currentIter = $project['name'] . '\Sprint 1 (2026)';
                }
                $projectIterations = [
                    $project['name'] . '\Sprint 1 (2026)',
                    $project['name'] . '\Sprint 2 (2026)',
                    $project['name'] . '\Sprint 3 (2026)',
                    $project['name'] . '\Product Backlog',
                    'Sprint 1 (2026)',
                    'Sprint 2 (2026)',
                    'Backlog'
                ];
                if (!in_array($currentIter, $projectIterations)) {
                    array_unshift($projectIterations, $currentIter);
                }
                ?>
                <div class="alm-meta-item">
                    <span class="alm-meta-label">Iteration:</span>
                    <select name="iteration" class="form-select form-select-sm py-0 ps-2 pe-4 border fw-medium" style="width: auto; min-width: 190px; height: 28px; font-size: 12px;">
                        <?php foreach ($projectIterations as $it): ?>
                            <option value="<?= htmlspecialchars($it) ?>" <?= ($currentIter === $it) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($it) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tags -->
                <div class="alm-meta-item">
                    <span class="alm-meta-label"><i class="bi bi-tags me-1"></i> Tags:</span>
                    <input type="text" name="tags" class="form-control form-control-sm py-0 px-2" style="width: 130px; height: 28px; font-size: 12px;" placeholder="Add tag..." value="<?= htmlspecialchars($issue['tags'] ?? '') ?>">
                </div>

                <!-- Comments count -->
                <div class="alm-meta-item ms-auto text-muted small">
                    <i class="bi bi-chat-left-text me-1"></i> <?= count($comments) ?> comments
                </div>
            </div>

            <!-- 3-COLUMN WORK ITEM BODY (Description/Notes/Discussion | Planning/Effort | Related Work) -->
            <div class="p-4">
                <div class="row g-4">
                    <!-- ==========================================
                         COLUMN 1: DESCRIPTION, NOTES, DISCUSSION
                         ========================================== -->
                    <div class="col-lg-6 border-end pe-lg-4">
                        <!-- DESCRIPTION -->
                        <div class="alm-field-group mb-4">
                            <div class="alm-section-label">
                                <span>DESCRIPTION</span>
                            </div>
                            <textarea name="description" class="form-control" rows="5" placeholder="Click to add Description..." style="font-size: 13.5px; line-height: 1.5;"><?= htmlspecialchars($issue['description'] ?? '') ?></textarea>
                        </div>

                        <!-- NOTES -->
                        <div class="alm-field-group mb-4">
                            <div class="alm-section-label">
                                <span>NOTES</span>
                            </div>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Click to add Notes..." style="font-size: 13.5px; line-height: 1.5;"><?= htmlspecialchars($issue['notes'] ?? '') ?></textarea>
                        </div>

                        <!-- SPECIAL SECTION FOR TESTCASE (IF APPLICABLE) -->
                        <?php if ($issue['issue_type'] === 'TESTCASE'): ?>
                            <div class="card border rounded-3 p-3 mb-4 bg-light">
                                <div class="alm-section-label text-purple">
                                    <span><i class="bi bi-clipboard-check me-1"></i> TEST EXECUTION & SCENARIOS</span>
                                    <span class="badge bg-purple text-white"><?= $issue['test_execution_status'] ?></span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="alm-field-label">Loại Kiểm Thử</label>
                                        <select name="test_type" class="form-select form-select-sm">
                                            <option value="MANUAL" <?= ($issue['test_type'] === 'MANUAL') ? 'selected' : '' ?>>Thủ công (Manual)</option>
                                            <option value="AUTOMATED" <?= ($issue['test_type'] === 'AUTOMATED') ? 'selected' : '' ?>>Tự động (Automated)</option>
                                            <option value="REGRESSION" <?= ($issue['test_type'] === 'REGRESSION') ? 'selected' : '' ?>>Hồi quy (Regression)</option>
                                            <option value="SMOKE" <?= ($issue['test_type'] === 'SMOKE') ? 'selected' : '' ?>>Smoke Test</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="alm-field-label">Kết Quả Thực Thi</label>
                                        <select name="test_execution_status" class="form-select form-select-sm fw-bold">
                                            <option value="UNTESTED" <?= ($issue['test_execution_status'] === 'UNTESTED') ? 'selected' : '' ?>>⚪ UNTESTED</option>
                                            <option value="PASSED" <?= ($issue['test_execution_status'] === 'PASSED') ? 'selected' : '' ?> class="text-success">🟢 PASSED (Đạt)</option>
                                            <option value="FAILED" <?= ($issue['test_execution_status'] === 'FAILED') ? 'selected' : '' ?> class="text-danger">🔴 FAILED (Thất bại)</option>
                                            <option value="BLOCKED" <?= ($issue['test_execution_status'] === 'BLOCKED') ? 'selected' : '' ?> class="text-warning">🟡 BLOCKED (Bị chặn)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="alm-field-label">Các bước kiểm thử (Test Steps)</label>
                                    <textarea name="test_steps" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($issue['test_steps'] ?? '') ?></textarea>
                                </div>
                                <div>
                                    <label class="alm-field-label">Kết quả mong đợi (Expected Result)</label>
                                    <textarea name="test_expected_result" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($issue['test_expected_result'] ?? '') ?></textarea>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- SPECIAL SECTION FOR BUG (IF APPLICABLE) -->
                        <?php if ($issue['issue_type'] === 'BUG'): ?>
                            <div class="card border border-danger border-opacity-25 rounded-3 p-3 mb-4 bg-light">
                                <div class="alm-section-label text-danger">
                                    <span><i class="bi bi-bug-fill me-1"></i> BUG DEFECT DETAILS</span>
                                    <span class="badge bg-danger"><?= $issue['bug_severity'] ?></span>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="alm-field-label">Mức độ nghiêm trọng</label>
                                        <select name="bug_severity" class="form-select form-select-sm">
                                            <option value="BLOCKER" <?= ($issue['bug_severity'] === 'BLOCKER') ? 'selected' : '' ?>>🔴 Blocker</option>
                                            <option value="CRITICAL" <?= ($issue['bug_severity'] === 'CRITICAL') ? 'selected' : '' ?>>🟠 Critical</option>
                                            <option value="MAJOR" <?= ($issue['bug_severity'] === 'MAJOR') ? 'selected' : '' ?>>🟡 Major</option>
                                            <option value="MINOR" <?= ($issue['bug_severity'] === 'MINOR') ? 'selected' : '' ?>>🔵 Minor</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="alm-field-label">Môi trường</label>
                                        <input type="text" name="environment" class="form-control form-control-sm" value="<?= htmlspecialchars($issue['environment'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="alm-field-label">Các bước tái hiện (Steps to Reproduce)</label>
                                    <textarea name="steps_to_reproduce" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($issue['steps_to_reproduce'] ?? '') ?></textarea>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- DISCUSSION (COMMENTS & AUDIT TIMELINE) -->
                        <div class="alm-field-group">
                            <div class="alm-section-label">
                                <span>DISCUSSION</span>
                                <span class="small fw-normal text-muted">Thảo luận theo thời gian thực</span>
                            </div>

                            <!-- Comment Input Box -->
                            <div class="d-flex gap-2 mb-3">
                                <div class="jira-avatar-circle flex-shrink-0" style="width: 32px; height: 32px; background: #0052CC; font-size: 12px;">
                                    <?= strtoupper(substr(Auth::user()['full_name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <textarea id="discussionInput" class="form-control" rows="2" placeholder="Add a comment. Use # to link a work item, ! to link a pull request, or @ to mention a person..."></textarea>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="button" class="btn btn-sm btn-primary px-3 fw-semibold" style="background:#0C66E4; border-color:#0C66E4;" onclick="submitComment()">
                                            <i class="bi bi-send-fill me-1"></i> Gửi thảo luận
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments Stream -->
                            <div id="commentsTimeline">
                                <?php if (empty($comments)): ?>
                                    <div class="text-muted small py-3 text-center border rounded bg-light" id="noCommentsBox">
                                        Chưa có bình luận thảo luận nào. Hãy là người đầu tiên để lại ý kiến!
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($comments as $c): ?>
                                        <div class="alm-comment-bubble">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="jira-avatar-circle" style="width: 24px; height: 24px; background: #4F46E5; font-size: 10px;">
                                                        <?= strtoupper(substr($c['full_name'] ?: 'U', 0, 1)) ?>
                                                    </div>
                                                    <span class="fw-bold text-dark small"><?= htmlspecialchars($c['full_name']) ?></span>
                                                    <span class="text-muted" style="font-size: 11px;">@<?= htmlspecialchars($c['username']) ?></span>
                                                </div>
                                                <span class="text-muted" style="font-size: 11px;"><?= date('H:i d/m/Y', strtotime($c['created_at'])) ?></span>
                                            </div>
                                            <div class="text-dark small lh-base ps-4" style="white-space: pre-wrap;">
                                                <?= htmlspecialchars($c['content']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- ==========================================
                         COLUMN 2: PLANNING & EFFORT
                         ========================================== -->
                    <div class="col-lg-3 border-end pe-lg-4">
                        <!-- PLANNING SECTION -->
                        <div class="alm-field-group mb-4">
                            <div class="alm-section-label">
                                <span>PLANNING</span>
                            </div>

                            <div class="mb-3">
                                <label class="alm-field-label">Priority (Độ ưu tiên)</label>
                                <select name="priority" class="form-select form-select-sm">
                                    <option value="HIGHEST" <?= ($issue['priority'] === 'HIGHEST') ? 'selected' : '' ?>>🔴 1 - Highest</option>
                                    <option value="HIGH" <?= ($issue['priority'] === 'HIGH') ? 'selected' : '' ?>>🟠 2 - High</option>
                                    <option value="MEDIUM" <?= ($issue['priority'] === 'MEDIUM') ? 'selected' : '' ?>>🟡 3 - Medium</option>
                                    <option value="LOW" <?= ($issue['priority'] === 'LOW') ? 'selected' : '' ?>>🔵 4 - Low</option>
                                    <option value="LOWEST" <?= ($issue['priority'] === 'LOWEST') ? 'selected' : '' ?>>⚪ 5 - Lowest</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="alm-field-label">Activity / Work Item Type</label>
                                <select name="issue_type" class="form-select form-select-sm" onchange="onTypeChangeDetail(this.value)">
                                    <option value="BACKLOG" <?= ($issue['issue_type'] === 'BACKLOG') ? 'selected' : '' ?>>📦 Backlog Item</option>
                                    <option value="TASK" <?= ($issue['issue_type'] === 'TASK') ? 'selected' : '' ?>>📌 Task</option>
                                    <option value="BUG" <?= ($issue['issue_type'] === 'BUG') ? 'selected' : '' ?>>🐞 Bug</option>
                                    <option value="TESTCASE" <?= ($issue['issue_type'] === 'TESTCASE') ? 'selected' : '' ?>>🧪 TestCase</option>
                                    <option value="STORY" <?= ($issue['issue_type'] === 'STORY') ? 'selected' : '' ?>>📖 Story</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="alm-field-label">Component / Phân Hệ</label>
                                <select name="component" class="form-select form-select-sm">
                                    <option value="">-- Không chọn --</option>
                                    <option value="BILLING" <?= ($issue['component'] === 'BILLING') ? 'selected' : '' ?>>BILLING</option>
                                    <option value="ACCOUNTS" <?= ($issue['component'] === 'ACCOUNTS') ? 'selected' : '' ?>>ACCOUNTS</option>
                                    <option value="FORMS" <?= ($issue['component'] === 'FORMS') ? 'selected' : '' ?>>FORMS</option>
                                    <option value="FEEDBACK" <?= ($issue['component'] === 'FEEDBACK') ? 'selected' : '' ?>>FEEDBACK</option>
                                    <option value="SECURITY" <?= ($issue['component'] === 'SECURITY') ? 'selected' : '' ?>>SECURITY</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="alm-field-label">Story Points</label>
                                <input type="number" name="story_points" class="form-control form-control-sm" value="<?= (int)($issue['story_points'] ?? 1) ?>" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="alm-field-label">Risks (Rủi ro tiềm ẩn)</label>
                                <textarea name="risks" class="form-control form-control-sm" rows="2" placeholder="Ghi nhận các rủi ro..."><?= htmlspecialchars($issue['risks'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="alm-field-label">Start Date (Ngày bắt đầu)</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($issue['start_date'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="alm-field-label">Due Date (Hạn hoàn thành)</label>
                                <input type="date" name="due_date" class="form-control form-control-sm" value="<?= htmlspecialchars($issue['due_date'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- EFFORT (HOURS) SECTION -->
                        <div class="alm-field-group">
                            <div class="alm-section-label">
                                <span>EFFORT (HOURS)</span>
                            </div>

                            <div class="mb-2">
                                <label class="alm-field-label">Original Estimate</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" name="original_estimate_hours" class="form-control" value="<?= (float)($issue['original_estimate_hours'] ?? 0) ?>">
                                    <span class="input-group-text">h</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="alm-field-label">Remaining</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" name="remaining_hours" class="form-control" value="<?= (float)($issue['remaining_hours'] ?? 0) ?>">
                                    <span class="input-group-text">h</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="alm-field-label">Completed</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" name="completed_hours" class="form-control" value="<?= (float)($issue['completed_hours'] ?? 0) ?>">
                                    <span class="input-group-text">h</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==========================================
                         COLUMN 3: RELATED WORK & HIERARCHY
                         ========================================== -->
                    <div class="col-lg-3">
                        <div class="alm-field-group mb-4">
                            <div class="alm-section-label">
                                <span>RELATED WORK</span>
                                <span class="badge bg-light text-secondary border">Hierarchy</span>
                            </div>

                            <!-- MỤC CHA (PARENT WORK ITEM) -->
                            <div class="mb-4">
                                <label class="alm-field-label d-flex align-items-center justify-content-between">
                                    <span>MỤC CHA (PARENT ITEM)</span>
                                    <small class="text-muted">
                                        <?= ($issue['issue_type'] === 'TESTCASE') ? 'Bug/Task cha' : 'Backlog cha' ?>
                                    </small>
                                </label>

                                <?php if (!empty($issue['parent_id'])): ?>
                                    <div class="p-2 border rounded bg-light mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="badge bg-primary text-white" style="font-size: 10px;"><?= $issue['parent_type'] ?? 'PARENT' ?></span>
                                            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $issue['parent_id'] ?>" class="fw-bold font-monospace text-primary text-decoration-none small">
                                                <?= htmlspecialchars($issue['parent_key']) ?>
                                            </a>
                                        </div>
                                        <div class="small text-truncate mt-1 text-dark fw-semibold" title="<?= htmlspecialchars($issue['parent_summary']) ?>">
                                            <?= htmlspecialchars($issue['parent_summary']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <select name="parent_id" class="form-select form-select-sm" id="detail_parent_select">
                                    <option value="">-- Không liên kết mục cha --</option>
                                    <?php foreach ($eligibleParents as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= ($issue['parent_id'] == $p['id']) ? 'selected' : '' ?>>
                                            [<?= $p['issue_key'] ?>] <?= htmlspecialchars($p['summary']) ?> (<?= $p['issue_type'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted" style="font-size: 11px;">
                                    <?php if ($issue['issue_type'] === 'TESTCASE'): ?>
                                        TestCase là con của Bug hoặc Task.
                                    <?php else: ?>
                                        Bug, Task, Issue là con của Backlog item.
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- MỤC CON LIÊN KẾT (CHILD WORK ITEMS) -->
                            <div>
                                <label class="alm-field-label d-flex align-items-center justify-content-between">
                                    <span>CÁC MỤC CON (CHILD ITEMS)</span>
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary"><?= count($children) ?></span>
                                </label>

                                <?php if (empty($children)): ?>
                                    <div class="p-3 border rounded bg-light text-center text-muted small mb-2">
                                        Chưa có công việc con trực thuộc.
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column gap-2 mb-2">
                                        <?php foreach ($children as $ch): ?>
                                            <?php $chMeta = $typeMetaMap[$ch['issue_type']] ?? $typeMetaMap['TASK']; ?>
                                            <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $ch['id'] ?>" class="alm-child-badge d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-1 text-truncate">
                                                    <i class="bi <?= $chMeta['icon'] ?>" style="color: <?= $chMeta['color'] ?>;"></i>
                                                    <span class="fw-bold font-monospace small"><?= $ch['issue_key'] ?>:</span>
                                                    <span class="text-truncate small"><?= htmlspecialchars($ch['summary']) ?></span>
                                                </div>
                                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 10px;"><?= $ch['status'] ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-outline-primary w-100 py-1" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#globalQuickCreateModal">
                                    <i class="bi bi-plus-lg me-1"></i> Thêm việc con mới
                                </button>
                            </div>
                        </div>

                        <!-- CLASSIFICATION -->
                        <div class="alm-field-group">
                            <div class="alm-section-label">
                                <span>CLASSIFICATION</span>
                            </div>
                            <div class="p-3 border rounded bg-light small">
                                <div class="mb-2 text-muted"><strong>Project:</strong> <?= htmlspecialchars($project['name']) ?> (<?= htmlspecialchars($project['project_key']) ?>)</div>
                                <div class="mb-2 text-muted"><strong>Reporter:</strong> <?= htmlspecialchars($issue['reporter_name'] ?? 'System') ?></div>
                                <div class="mb-2 text-muted"><strong>Created:</strong> <?= date('d/m/Y H:i', strtotime($issue['created_at'])) ?></div>
                                <div class="text-muted"><strong>Updated:</strong> <?= date('d/m/Y H:i', strtotime($issue['updated_at'] ?? $issue['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function submitComment() {
    const input = document.getElementById('discussionInput');
    const content = input.value.trim();
    if (!content) return;

    fetch('<?= $baseUrl ?>/projects/api/add-comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            issue_id: '<?= $issue['id'] ?>',
            content: content
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success && Array.isArray(res.comments)) {
            input.value = '';
            renderComments(res.comments);
        } else {
            alert(res.message || 'Không thể đăng bình luận');
        }
    })
    .catch(e => alert('Lỗi kết nối: ' + e.message));
}

function renderComments(comments) {
    const container = document.getElementById('commentsTimeline');
    if (!container) return;

    if (comments.length === 0) {
        container.innerHTML = '<div class="text-muted small py-3 text-center border rounded bg-light">Chưa có bình luận thảo luận nào.</div>';
        return;
    }

    let html = '';
    comments.forEach(c => {
        const initial = (c.full_name || 'U').charAt(0).toUpperCase();
        html += `
            <div class="alm-comment-bubble">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center gap-2">
                        <div class="jira-avatar-circle" style="width: 24px; height: 24px; background: #4F46E5; font-size: 10px;">
                            ${initial}
                        </div>
                        <span class="fw-bold text-dark small">${escapeHtml(c.full_name)}</span>
                        <span class="text-muted" style="font-size: 11px;">@${escapeHtml(c.username)}</span>
                    </div>
                    <span class="text-muted" style="font-size: 11px;">Vừa xong</span>
                </div>
                <div class="text-dark small lh-base ps-4" style="white-space: pre-wrap;">
                    ${escapeHtml(c.content)}
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function escapeHtml(text) {
    return text ? text.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]) : '';
}

function onTypeChangeDetail(newType) {
    // Reload eligible parents when issue type changes in detail view
    fetch('<?= $baseUrl ?>/projects/api/eligible-parents?project_id=<?= $project['id'] ?>&type=' + newType + '&exclude_id=<?= $issue['id'] ?>')
        .then(r => r.json())
        .then(res => {
            const select = document.getElementById('detail_parent_select');
            if (select && res.success) {
                let html = '<option value="">-- Không liên kết mục cha --</option>';
                res.parents.forEach(p => {
                    html += `<option value="${p.id}">[${p.issue_key}] ${p.summary} (${p.issue_type})</option>`;
                });
                select.innerHTML = html;
            }
        })
        .catch(() => {});
}
</script>
