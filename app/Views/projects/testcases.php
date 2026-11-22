<?php
/**
 * @var array $project
 * @var array $testcases
 * @var array $members
 * @var array $metrics
 * @var array $filters
 * @var array $pagination
 */

use App\Core\Response;
$baseUrl = Response::baseUrl();
?>

<div class="container-fluid px-0">
    <!-- HEADER -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 13px;">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects" class="text-decoration-none text-secondary">Projects</a></li>
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="text-decoration-none text-secondary"><?= htmlspecialchars($project['name']) ?></a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold">Test Cases</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-purple"></i> Quản Lý Ca Kiểm Thử (Test Cases)
            </h3>
            <p class="text-muted small mb-0">Theo dõi kịch bản kiểm thử, liên kết với Bug/Task cha và ghi nhận kết quả QA/QC (Tối đa 20/trang)</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-primary fw-semibold px-3" style="background:#6554c0; border-color:#6554c0;" onclick="openNewTestCaseModal()">
                <i class="bi bi-plus-lg me-1"></i> Tạo Test Case Mới
            </button>
        </div>
    </div>

    <!-- METRICS TILES -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="p-3 bg-light rounded border text-center">
                <div class="text-muted small fw-bold">TỔNG TEST CASES</div>
                <div class="fs-4 fw-bold text-dark"><?= $metrics['total'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded text-center">
                <div class="text-success small fw-bold"><i class="bi bi-check-circle-fill me-1"></i> ĐẠT (PASSED)</div>
                <div class="fs-4 fw-bold text-success"><?= $metrics['passed'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-center">
                <div class="text-danger small fw-bold"><i class="bi bi-x-circle-fill me-1"></i> THẤT BẠI (FAILED)</div>
                <div class="fs-4 fw-bold text-danger"><?= $metrics['failed'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-secondary bg-opacity-10 border rounded text-center">
                <div class="text-secondary small fw-bold"><i class="bi bi-hourglass-split me-1"></i> CHƯA TEST (UNTESTED)</div>
                <div class="fs-4 fw-bold text-secondary"><?= $metrics['untested'] ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded text-center">
                <div class="text-warning small fw-bold"><i class="bi bi-slash-circle me-1"></i> BỊ CHẶN (BLOCKED)</div>
                <div class="fs-4 fw-bold text-dark"><?= $metrics['blocked'] ?></div>
            </div>
        </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="card border rounded-3 shadow-sm mb-3 p-3 bg-light">
        <form action="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/testcases" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="position-relative">
                    <input type="text" name="search" class="form-control form-control-sm ps-4" placeholder="Tìm theo tên test case, mã key..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 7px; font-size: 12px;"></i>
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Tất cả kết quả thực thi --</option>
                    <option value="UNTESTED" <?= ($filters['status'] === 'UNTESTED') ? 'selected' : '' ?>>Chưa kiểm thử (Untested)</option>
                    <option value="PASSED" <?= ($filters['status'] === 'PASSED') ? 'selected' : '' ?>>Đạt (Passed)</option>
                    <option value="FAILED" <?= ($filters['status'] === 'FAILED') ? 'selected' : '' ?>>Thất bại (Failed)</option>
                    <option value="BLOCKED" <?= ($filters['status'] === 'BLOCKED') ? 'selected' : '' ?>>Bị chặn (Blocked)</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold" style="background:#6554c0; border-color:#6554c0;">Lọc dữ liệu</button>
            </div>
        </form>
    </div>

    <!-- TESTCASES TABLE -->
    <div class="card border rounded-3 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light text-secondary small" style="text-transform: uppercase; font-size: 11px;">
                    <tr>
                        <th style="width: 100px;">Key</th>
                        <th>Tên Ca Kiểm Thử (Summary)</th>
                        <th style="width: 160px;">Mục Cha (Bug/Task)</th>
                        <th style="width: 110px;">Loại Test</th>
                        <th style="width: 140px;">Kết Quả Thực Thi</th>
                        <th style="width: 140px;">Người Phụ Trách</th>
                        <th style="width: 170px;" class="text-end">Cập Nhật Nhanh</th>
                    </tr>
                </thead>
                <tbody id="tcTableBody">
                    <?php if (empty($testcases)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-check fs-2 opacity-50"></i>
                                <div class="mt-2">Không tìm thấy ca kiểm thử nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($testcases as $tc): ?>
                            <?php 
                            $statusPill = [
                                'PASSED'   => '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-50"><i class="bi bi-check-circle-fill me-1"></i> PASSED</span>',
                                'FAILED'   => '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-50"><i class="bi bi-x-circle-fill me-1"></i> FAILED</span>',
                                'BLOCKED'  => '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-50"><i class="bi bi-slash-circle me-1"></i> BLOCKED</span>',
                                'UNTESTED' => '<span class="badge bg-secondary bg-opacity-10 text-secondary border"><i class="bi bi-hourglass me-1"></i> UNTESTED</span>'
                            ][$tc['test_execution_status']] ?? $tc['test_execution_status'];
                            ?>
                            <tr class="tc-row" id="tcrow_<?= $tc['id'] ?>">
                                <td>
                                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $tc['id'] ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                        <?= htmlspecialchars($tc['issue_key']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $tc['id'] ?>" class="text-decoration-none text-dark hover-primary">
                                            <?= htmlspecialchars($tc['summary']) ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($tc['test_expected_result'])): ?>
                                        <div class="text-muted small mt-1 text-truncate" style="max-width: 400px;">
                                            <i class="bi bi-arrow-return-right text-purple me-1"></i> <strong>Kỳ vọng:</strong> <?= htmlspecialchars($tc['test_expected_result']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($tc['parent_key'])): ?>
                                        <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $tc['parent_id'] ?>" class="badge bg-light text-primary border text-decoration-none text-truncate d-inline-block" style="max-width: 150px;" title="<?= htmlspecialchars($tc['parent_summary'] ?? '') ?>">
                                            <i class="bi bi-diagram-3-fill me-1"></i> <?= htmlspecialchars($tc['parent_key']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?= $tc['test_type'] ?>
                                    </span>
                                </td>
                                <td id="tcstatus_<?= $tc['id'] ?>">
                                    <?= $statusPill ?>
                                </td>
                                <td>
                                    <?php if ($tc['assignee_id']): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="jira-avatar-circle" style="width: 22px; height: 22px; font-size: 9px; background: #6554C0;">
                                                <?= strtoupper(substr($tc['assignee_name'] ?: 'U', 0, 1)) ?>
                                            </div>
                                            <span class="small text-truncate" style="max-width: 90px;"><?= htmlspecialchars($tc['assignee_name']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success py-0 px-2" title="Đánh dấu ĐẠT (Pass)" onclick="executeTest(<?= $tc['id'] ?>, 'PASSED')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-outline-danger py-0 px-2" title="Đánh dấu THẤT BẠI (Fail)" onclick="executeTest(<?= $tc['id'] ?>, 'FAILED')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <button class="btn btn-outline-warning py-0 px-2" title="Bị chặn (Blocked)" onclick="executeTest(<?= $tc['id'] ?>, 'BLOCKED')">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                        <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues/<?= $tc['id'] ?>" class="btn btn-outline-secondary py-0 px-2" title="Xem chi tiết các bước">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION BAR (Tối đa 20 / trang) -->
        <?php if (isset($pagination) && $pagination['total'] > 0): ?>
            <div class="card-footer bg-white border-top py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small text-muted">
                    Hiển thị <?= min(($pagination['page'] - 1) * $pagination['per_page'] + 1, $pagination['total']) ?> - 
                    <?= min($pagination['page'] * $pagination['per_page'], $pagination['total']) ?> 
                    trên tổng số <strong><?= $pagination['total'] ?></strong> ca kiểm thử (tối đa 20 trên 1 trang)
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
    </div>
</div>

<script>
function openNewTestCaseModal() {
    const modalEl = document.getElementById('globalQuickCreateModal');
    if (modalEl) {
        const typeSelect = document.getElementById('qc_issue_type');
        if (typeSelect) {
            typeSelect.value = 'TESTCASE';
            toggleQcTypeFields('TESTCASE');
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function executeTest(issueId, status) {
    fetch('<?= $baseUrl ?>/projects/api/update-test-status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            issue_id: issueId,
            test_status: status
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            const statusCell = document.getElementById('tcstatus_' + issueId);
            if (statusCell) {
                const pills = {
                    'PASSED': '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-50"><i class="bi bi-check-circle-fill me-1"></i> PASSED</span>',
                    'FAILED': '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-50"><i class="bi bi-x-circle-fill me-1"></i> FAILED</span>',
                    'BLOCKED': '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-50"><i class="bi bi-slash-circle me-1"></i> BLOCKED</span>',
                    'UNTESTED': '<span class="badge bg-secondary bg-opacity-10 text-secondary border"><i class="bi bi-hourglass me-1"></i> UNTESTED</span>'
                };
                statusCell.innerHTML = pills[status] || status;
            }

            Swal.fire({
                icon: (status === 'PASSED') ? 'success' : ((status === 'FAILED') ? 'error' : 'warning'),
                title: 'Đã cập nhật: ' + status,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        }
    })
    .catch(e => alert('Lỗi kết nối: ' + e.message));
}
</script>
