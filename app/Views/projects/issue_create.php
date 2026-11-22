<?php
/**
 * Create Work Item View (100% matched to Image 2 ALM Layout)
 * @var array $project
 * @var array $members
 * @var array $eligibleParents
 * @var array $components
 * @var string $defaultType
 */
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
$defaultType = $defaultType ?? ($_GET['type'] ?? 'TASK');
$defaultParentId = (int)($_GET['parent_id'] ?? 0);
?>

<div class="container-fluid py-2 px-3">
    <!-- Breadcrumb & Top Actions Bar -->
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
        <div class="d-flex align-items-center gap-2">
            <a href="/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-light border text-secondary py-1 px-2" title="Quay lại bảng">
                <i class="bi bi-arrow-left"></i>
            </a>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="/projects" class="text-decoration-none text-secondary">Projects</a></li>
                    <li class="breadcrumb-item"><a href="/projects/<?= $project['id'] ?>/board" class="text-decoration-none text-secondary"><?= htmlspecialchars($project['name']) ?></a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold">New Work Item</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="/projects/<?= $project['id'] ?>/board" class="btn btn-sm btn-outline-secondary px-3">Hủy</a>
            <button type="button" class="btn btn-sm btn-primary fw-semibold px-4" onclick="document.getElementById('fullCreateIssueForm').submit();" style="background: #0C66E4; border-color: #0C66E4;">
                <i class="bi bi-check2 me-1"></i> Lưu & Đóng (Save & Close)
            </button>
        </div>
    </div>

    <!-- MAIN ALM WORK ITEM CREATION FORM (IMAGE 2 LAYOUT) -->
    <div class="card border shadow-sm rounded-3 bg-white">
        <form id="fullCreateIssueForm" action="/projects/api/quick-create" method="POST" class="p-0">
            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

            <!-- 1. HEADER TITLE INPUT (LARGE, BORDERLESS) -->
            <div class="px-4 pt-4 pb-2 border-bottom">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <select name="issue_type" id="page_issue_type" class="form-select form-select-sm fw-bold border-primary text-primary" style="width: auto; min-width: 180px;" onchange="onPageTypeChange(this.value)">
                            <option value="BACKLOG" <?= ($defaultType === 'BACKLOG') ? 'selected' : '' ?>>📦 Backlog Item</option>
                            <option value="TASK" <?= ($defaultType === 'TASK') ? 'selected' : '' ?>>📌 Task</option>
                            <option value="BUG" <?= ($defaultType === 'BUG') ? 'selected' : '' ?>>🐞 Bug</option>
                            <option value="TESTCASE" <?= ($defaultType === 'TESTCASE') ? 'selected' : '' ?>>🧪 TestCase</option>
                            <option value="STORY" <?= ($defaultType === 'STORY') ? 'selected' : '' ?>>📖 User Story</option>
                        </select>

                        <span class="badge bg-light text-muted border font-monospace">NEW ITEM</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">Trạng thái ban đầu:</span>
                        <select name="status" class="form-select form-select-sm fw-semibold" style="width: 140px;">
                            <option value="TO_DO" selected>TO DO</option>
                            <option value="IN_PROGRESS">IN PROGRESS</option>
                            <option value="IN_REVIEW">IN REVIEW</option>
                            <option value="DONE">DONE</option>
                        </select>
                    </div>
                </div>

                <input type="text" name="summary" id="page_summary_input" class="form-control form-control-lg border-0 px-0 fw-bold text-dark shadow-none" placeholder="Enter title" style="font-size: 26px; letter-spacing: -0.5px;" required autocomplete="off" autofocus>
            </div>

            <!-- 2. META STRIP (ASSIGNED TO, SPRINT, TAGS, COMPONENT) -->
            <div class="px-4 py-2 bg-light border-bottom d-flex flex-wrap align-items-center gap-4 text-secondary small">
                <!-- Assignee with Self-assign switch -->
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark"><i class="bi bi-person me-1"></i>Assigned to:</span>
                    <select name="assignee_id" id="page_assignee_id" class="form-select form-select-sm" style="width: auto; min-width: 180px;">
                        <option value="">-- Chưa phân công --</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['user_id'] ?>" <?= ($m['user_id'] == Auth::id()) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-check form-switch m-0 ms-1" title="Tự gán công việc này cho tài khoản của bạn">
                        <input class="form-check-input" type="checkbox" id="page_self_assign_toggle" onchange="togglePageSelfAssign(this.checked)" checked>
                        <label class="form-check-label text-primary small" for="page_self_assign_toggle">Tự nhận việc</label>
                    </div>
                </div>

                <!-- Priority -->
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark"><i class="bi bi-flag me-1"></i>Priority:</span>
                    <select name="priority" class="form-select form-select-sm" style="width: auto;">
                        <option value="HIGHEST">🔴 1 - Highest</option>
                        <option value="HIGH">🟠 2 - High</option>
                        <option value="MEDIUM" selected>🟡 3 - Medium</option>
                        <option value="LOW">🔵 4 - Low</option>
                        <option value="LOWEST">⚪ 5 - Lowest</option>
                    </select>
                </div>

                <!-- Sprint Location -->
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark"><i class="bi bi-layers me-1"></i>Iteration:</span>
                    <select name="sprint_status" id="page_sprint_status" class="form-select form-select-sm" style="width: auto;">
                        <option value="ACTIVE_SPRINT" selected>Active Sprint (Board)</option>
                        <option value="BACKLOG">Product Backlog</option>
                    </select>
                </div>

                <!-- Component -->
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark"><i class="bi bi-grid-3x3-gap me-1"></i>Component:</span>
                    <select name="component" class="form-select form-select-sm" style="width: auto;">
                        <option value="">None</option>
                        <option value="BILLING">BILLING</option>
                        <option value="ACCOUNTS">ACCOUNTS</option>
                        <option value="FORMS">FORMS</option>
                        <option value="FEEDBACK">FEEDBACK</option>
                        <option value="SECURITY">SECURITY</option>
                    </select>
                </div>
            </div>

            <!-- 3. TWO-COLUMN ALM BODY -->
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- LEFT COLUMN: DESCRIPTION, NOTES, DISCUSSION (IMAGE 2) -->
                    <div class="col-lg-8 border-end pe-lg-4">
                        <!-- Description Section -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">Description</label>
                            <div class="border rounded-2 p-2 bg-light mb-1 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-white border-0 py-0 px-1 text-secondary" title="In đậm"><i class="bi bi-type-bold"></i></button>
                                <button type="button" class="btn btn-sm btn-white border-0 py-0 px-1 text-secondary" title="In nghiêng"><i class="bi bi-type-italic"></i></button>
                                <button type="button" class="btn btn-sm btn-white border-0 py-0 px-1 text-secondary" title="Gạch đầu dòng"><i class="bi bi-list-ul"></i></button>
                                <button type="button" class="btn btn-sm btn-white border-0 py-0 px-1 text-secondary" title="Đoạn mã"><i class="bi bi-code-slash"></i></button>
                            </div>
                            <textarea name="description" class="form-control" rows="4" placeholder="Click to add Description..."></textarea>
                        </div>

                        <!-- Notes Section -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Click to add Notes..."></textarea>
                        </div>

                        <!-- Type Specific: Bug Details -->
                        <div id="page_bug_sec" class="mb-4 p-3 bg-light rounded border border-danger border-opacity-25" style="display: <?= ($defaultType === 'BUG') ? 'block' : 'none' ?>;">
                            <div class="fw-bold text-danger small mb-2"><i class="bi bi-bug-fill me-1"></i> THÔNG TIN BÁO LỖI (BUG DETAILS)</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Mức độ nghiêm trọng</label>
                                    <select name="bug_severity" class="form-select form-select-sm">
                                        <option value="BLOCKER">🔴 Blocker</option>
                                        <option value="CRITICAL">🟠 Critical</option>
                                        <option value="MAJOR" selected>🟡 Major</option>
                                        <option value="MINOR">🔵 Minor</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Môi trường phát sinh</label>
                                    <input type="text" name="environment" class="form-control form-control-sm" placeholder="Windows 11 Chrome / Production...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-secondary">Các bước tái hiện (Steps to Reproduce)</label>
                                    <textarea name="steps_to_reproduce" class="form-control form-control-sm" rows="2" placeholder="1. Bước một...&#10;2. Bước hai..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Type Specific: TestCase Details -->
                        <div id="page_testcase_sec" class="mb-4 p-3 bg-light rounded border border-purple border-opacity-25" style="display: <?= ($defaultType === 'TESTCASE') ? 'block' : 'none' ?>;">
                            <div class="fw-bold text-purple small mb-2"><i class="bi bi-clipboard-check me-1"></i> THIẾT LẬP CA KIỂM THỬ (TESTCASE DETAILS)</div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Loại kiểm thử</label>
                                    <select name="test_type" class="form-select form-select-sm">
                                        <option value="MANUAL" selected>Manual Test (Thủ công)</option>
                                        <option value="AUTOMATED">Automated Test (Tự động)</option>
                                        <option value="REGRESSION">Regression Test (Hồi quy)</option>
                                        <option value="SMOKE">Smoke Test</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Trạng thái thực thi</label>
                                    <select name="test_execution_status" class="form-select form-select-sm">
                                        <option value="UNTESTED" selected>Chưa kiểm thử (Untested)</option>
                                        <option value="PASSED">Đạt (Passed)</option>
                                        <option value="FAILED">Thất bại (Failed)</option>
                                        <option value="BLOCKED">Bị chặn (Blocked)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-secondary">Các bước kiểm thử (Test Steps)</label>
                                    <textarea name="test_steps" class="form-control form-control-sm" rows="2" placeholder="1. Mở trang...&#10;2. Bấm nút..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small text-secondary">Kết quả mong đợi</label>
                                    <input type="text" name="test_expected_result" class="form-control form-control-sm" placeholder="Hệ thống phản hồi 200 OK và hiển thị dữ liệu chính xác...">
                                </div>
                            </div>
                        </div>

                        <!-- Discussion Section (Image 2) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">Discussion</label>
                            <div class="d-flex gap-3 align-items-start">
                                <div class="jira-avatar-circle flex-shrink-0" style="background: #0052CC; width: 36px; height: 36px; font-size: 14px;">
                                    <?= strtoupper(substr(Auth::user()['full_name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <textarea name="initial_comment" class="form-control form-control-sm mb-2" rows="2" placeholder="Add a comment or discussion note for the team..."></textarea>
                                    <div class="text-muted small fst-italic">Bình luận này sẽ được lưu ngay vào dòng thời gian thảo luận của công việc.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: RELATED WORK, EFFORT, PLANNING (IMAGE 2) -->
                    <div class="col-lg-4">
                        <!-- Related Work (Parent-Child Hierarchy) -->
                        <div class="p-3 bg-light rounded-2 border mb-4" id="page_parent_wrap">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-dark small text-uppercase"><i class="bi bi-diagram-3-fill me-1 text-primary"></i>Related Work</span>
                                <span class="badge bg-white text-secondary border font-monospace" style="font-size: 10px;">Hierarchy</span>
                            </div>
                            <label class="form-label small text-secondary mb-1" id="page_parent_label">Mục cha liên kết (Parent Work Item)</label>
                            <select name="parent_id" id="page_parent_id" class="form-select form-select-sm">
                                <option value="">-- Không liên kết mục cha --</option>
                                <?php foreach ($eligibleParents as $pItem): ?>
                                    <option value="<?= $pItem['id'] ?>" <?= ($defaultParentId === (int)$pItem['id']) ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($pItem['issue_key']) ?>] <?= htmlspecialchars($pItem['summary']) ?> (<?= $pItem['issue_type'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted small" style="font-size: 11px;">
                                Bug, Task, Issue sẽ trực thuộc mục Backlog cha đã chọn. TestCase trực thuộc Bug hoặc Task.
                            </div>
                        </div>

                        <!-- Effort (Hours) (Image 2) -->
                        <div class="p-3 bg-light rounded-2 border mb-4">
                            <div class="fw-bold text-dark small text-uppercase mb-2"><i class="bi bi-stopwatch me-1 text-primary"></i>Effort (Hours)</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-secondary mb-0">Original Estimate</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.5" min="0" name="original_estimate_hours" class="form-control" value="0">
                                        <span class="input-group-text">h</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-secondary mb-0">Remaining</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.5" min="0" name="remaining_hours" class="form-control" value="0">
                                        <span class="input-group-text">h</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Planning (Image 2) -->
                        <div class="p-3 bg-light rounded-2 border mb-4">
                            <div class="fw-bold text-dark small text-uppercase mb-2"><i class="bi bi-calendar3 me-1 text-primary"></i>Planning</div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-secondary mb-0">Start Date</label>
                                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-secondary mb-0">Due Date</label>
                                    <input type="date" name="due_date" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label small text-secondary mb-0">Area / Module</label>
                                    <select name="area" class="form-select form-select-sm">
                                        <option value="<?= htmlspecialchars($project['name']) ?>"><?= htmlspecialchars($project['name']) ?> (Root)</option>
                                        <option value="<?= htmlspecialchars($project['name']) ?>\Frontend & UI"><?= htmlspecialchars($project['name']) ?>\Frontend & UI</option>
                                        <option value="<?= htmlspecialchars($project['name']) ?>\Backend & API" selected><?= htmlspecialchars($project['name']) ?>\Backend & API</option>
                                        <option value="<?= htmlspecialchars($project['name']) ?>\Database & Infrastructure"><?= htmlspecialchars($project['name']) ?>\Database & Infrastructure</option>
                                        <option value="<?= htmlspecialchars($project['name']) ?>\Security & Compliance"><?= htmlspecialchars($project['name']) ?>\Security & Compliance</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label small text-secondary mb-0">Risks / Potential Blocks</label>
                                    <input type="text" name="risks" class="form-control form-control-sm" placeholder="Rủi ro kỹ thuật hoặc phụ thuộc bên thứ 3...">
                                </div>
                            </div>
                        </div>

                        <!-- Action Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2" style="background: #0C66E4; border-color: #0C66E4;">
                                <i class="bi bi-plus-lg me-1"></i> Tạo Công Việc Mới
                            </button>
                            <a href="/projects/<?= $project['id'] ?>/board" class="btn btn-light border py-2">
                                Hủy Bỏ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const CURRENT_USER_ID = <?= Auth::id() ?: 0 ?>;

function togglePageSelfAssign(isSelf) {
    const sel = document.getElementById('page_assignee_id');
    if (isSelf) {
        sel.value = CURRENT_USER_ID;
    } else {
        sel.value = '';
    }
}

function onPageTypeChange(type) {
    const bugSec = document.getElementById('page_bug_sec');
    const testSec = document.getElementById('page_testcase_sec');
    const parentWrap = document.getElementById('page_parent_wrap');
    const sprintSel = document.getElementById('page_sprint_status');

    if (bugSec) bugSec.style.display = (type === 'BUG') ? 'block' : 'none';
    if (testSec) testSec.style.display = (type === 'TESTCASE') ? 'block' : 'none';

    if (type === 'BACKLOG') {
        if (parentWrap) parentWrap.style.display = 'none';
        if (sprintSel) sprintSel.value = 'BACKLOG';
    } else {
        if (parentWrap) parentWrap.style.display = 'block';
        loadPageEligibleParents(type);
    }
}

function loadPageEligibleParents(type) {
    fetch('/projects/api/eligible-parents?project_id=<?= $project['id'] ?>&child_type=' + encodeURIComponent(type))
    .then(r => r.json())
    .then(res => {
        if (res.success && res.parents) {
            const sel = document.getElementById('page_parent_id');
            const currentVal = sel.value;
            sel.innerHTML = '<option value="">-- Không liên kết mục cha --</option>';
            res.parents.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = `[${p.issue_key}] ${p.summary} (${p.issue_type})`;
                if (String(p.id) === String(currentVal)) {
                    opt.selected = true;
                }
                sel.appendChild(opt);
            });
        }
    })
    .catch(console.error);
}
</script>

