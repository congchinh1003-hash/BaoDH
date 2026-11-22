<?php
use App\Core\Response;

/**
 * @var array $kpis
 * @var array $backlogAging
 * @var array $slaPriorities
 * @var array $catDistribution
 * @var array $groupWorkload
 * @var array $trendData
 * @var array $groups
 * @var array $filters
 */
$baseUrl = Response::baseUrl();
?>

<!-- BREADCRUMB & HEADER -->
<div class="page-hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3" style="width: 48px; height: 48px;">
                <i class="bi bi-bar-chart-line-fill fs-4"></i>
            </div>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none">Trang chủ</a></li>
                        <li class="breadcrumb-item active fw-semibold" aria-current="page">Báo cáo & Phân tích</li>
                    </ol>
                </nav>
                <h4 class="fw-bold text-dark mb-0">Báo Cáo Hoạt Động & Phân Tích SLA</h4>
                <p class="text-muted small mb-0">Hệ thống phân tích thời gian thực KPI vận hành, cam kết SLA, tuổi tồn đọng và cơ cấu yêu cầu hỗ trợ.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= $baseUrl ?>/reports/staff" class="btn btn-outline-primary btn-sm fw-semibold">
                <i class="bi bi-people-fill me-1"></i>Hiệu suất KTV
            </a>
            <a href="<?= $baseUrl ?>/reports/export?type=tickets" class="btn btn-outline-success btn-sm fw-semibold">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Xuất Excel
            </a>
            <button type="button" onclick="window.print()" class="btn btn-light btn-sm border fw-semibold">
                <i class="bi bi-printer me-1"></i>In báo cáo
            </button>
        </div>
    </div>
</div>

<!-- EXECUTIVE KPI CARDS (ATLASSIAN JIRA STYLE) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-primary">
            <div class="text-muted small fw-bold text-uppercase">Tổng số Ticket</div>
            <div class="fs-2 fw-bold text-dark mt-1"><?= number_format($kpis['total_tickets'] ?? 0) ?></div>
            <div class="small text-muted mt-1">Đang xử lý: <strong class="text-primary"><?= number_format($kpis['active_tickets'] ?? 0) ?></strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-success">
            <div class="text-muted small fw-bold text-uppercase">Tỉ Lệ Đạt SLA (Cam kết)</div>
            <div class="fs-2 fw-bold text-success mt-1"><?= $kpis['sla_achievement_rate'] ?>%</div>
            <div class="small text-muted mt-1">Quá SLA: <strong class="text-danger"><?= number_format($kpis['overdue_tickets'] ?? 0) ?></strong> ticket</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-info">
            <div class="text-muted small fw-bold text-uppercase">Thời gian xử lý TB</div>
            <div class="fs-2 fw-bold text-info mt-1"><?= $kpis['avg_resolution_hours'] ?: '2.5' ?> <span class="fs-6 fw-normal text-muted">giờ</span></div>
            <div class="small text-muted mt-1">Đã giải quyết: <strong class="text-dark"><?= number_format(($kpis['resolved_tickets'] ?? 0) + ($kpis['closed_tickets'] ?? 0)) ?></strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-4 border-warning">
            <div class="text-muted small fw-bold text-uppercase">Độ hài lòng (CSAT)</div>
            <div class="fs-2 fw-bold text-warning mt-1">
                <?= $kpis['avg_csat'] ?> <span class="fs-5 text-warning"><i class="bi bi-star-fill"></i></span>
            </div>
            <div class="small text-muted mt-1">Dựa trên <strong class="text-dark"><?= $kpis['total_ratings'] ?></strong> lượt đánh giá</div>
        </div>
    </div>
</div>

<!-- CHARTS ROW: TREND (LINE) + DOUGHNUTS -->
<div class="row g-4 mb-4">
    <!-- CHART 1: XU HƯỚNG TICKET PHÁT SINH VS GIẢI QUYẾT (14 NGÀY) -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Xu Hướng Yêu Cầu Phát Sinh vs Đã Xử Lý (14 ngày gần nhất)</span>
                <span class="badge bg-light text-muted border">Thời gian thực</span>
            </div>
            <div class="card-body p-4">
                <div style="height: 280px; position: relative;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- CHART 2: CƠ CẤU THEO NHÓM IT & CATEGORY -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="fw-bold text-dark"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Phân Bổ Theo Nhóm IT</span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div style="height: 220px; position: relative;">
                    <canvas id="groupPieChart"></canvas>
                </div>
                <div class="text-center text-muted small mt-2">Tỉ lệ khối lượng công việc đang xử lý</div>
            </div>
        </div>
    </div>
</div>

<!-- BACKLOG AGING REPORT (RULE 43) -->
<div class="card border-0 shadow-sm bg-white mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold text-dark"><i class="bi bi-hourglass-split me-2 text-warning"></i>Báo Cáo Tuổi Tồn Đọng Ticket (Backlog Aging Analysis - Rule 43)</span>
            <span class="text-muted small ms-2">• Các ticket đang tồn đọng chưa đóng</span>
        </div>
        <span class="badge bg-warning-subtle text-dark border border-warning">Tổng tồn: <?= number_format($backlogAging['total_backlog'] ?? 0) ?></span>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 text-center">
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3 border-top border-4 border-success">
                    <div class="text-muted small fw-bold">0 - 1 Ngày</div>
                    <div class="fs-3 fw-bold text-success mt-1"><?= (int)$backlogAging['age_0_1'] ?></div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Mới phát sinh</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3 border-top border-4 border-info">
                    <div class="text-muted small fw-bold">2 - 3 Ngày</div>
                    <div class="fs-3 fw-bold text-info mt-1"><?= (int)$backlogAging['age_2_3'] ?></div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Đang trong tầm kiểm soát</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3 border-top border-4 border-warning">
                    <div class="text-muted small fw-bold">4 - 7 Ngày</div>
                    <div class="fs-3 fw-bold text-warning mt-1"><?= (int)$backlogAging['age_4_7'] ?></div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Cần đôn đốc xử lý</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3 border-top border-4 border-danger">
                    <div class="text-muted small fw-bold">8 - 30 Ngày</div>
                    <div class="fs-3 fw-bold text-danger mt-1"><?= (int)$backlogAging['age_8_30'] ?></div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Cảnh báo tồn lâu</div>
                </div>
            </div>
            <div class="col-12 col-md">
                <div class="p-3 bg-light rounded-3 border-top border-4 border-dark">
                    <div class="text-muted small fw-bold">> 30 Ngày</div>
                    <div class="fs-3 fw-bold text-dark mt-1"><?= (int)$backlogAging['age_over_30'] ?></div>
                    <div class="text-muted small" style="font-size: 0.72rem;">Nghiêm trọng / Đình trệ</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLES ROW: SLA BY PRIORITY + GROUP WORKLOAD -->
<div class="row g-4">
    <!-- TABLE 1: SLA THEO MỨC ĐỘ ƯU TIÊN (RULE 45) -->
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="fw-bold text-dark"><i class="bi bi-stopwatch-fill me-2 text-primary"></i>Cam Kết SLA Theo Mức Độ Khẩn Cấp (Rule 45)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Mức độ</th>
                                <th class="text-center">Tổng Ticket</th>
                                <th class="text-center">Đạt SLA</th>
                                <th class="text-center">Quá SLA</th>
                                <th class="pe-3 text-end">Tỉ lệ đạt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slaPriorities as $sp): ?>
                                <?php 
                                $total = (int)$sp['total_count'];
                                $met = (int)$sp['within_sla_count'];
                                $over = (int)$sp['overdue_count'];
                                $rate = $total > 0 ? round(($met / $total) * 100, 1) : 100;
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold" style="color: <?= htmlspecialchars($sp['color']) ?>;">
                                        <i class="bi bi-flag-fill me-1"></i><?= htmlspecialchars($sp['name']) ?>
                                    </td>
                                    <td class="text-center font-monospace fw-bold"><?= $total ?></td>
                                    <td class="text-center font-monospace text-success fw-bold"><?= $met ?></td>
                                    <td class="text-center font-monospace text-danger fw-bold"><?= $over ?></td>
                                    <td class="pe-3 text-end fw-bold <?= $rate >= 80 ? 'text-success' : 'text-danger' ?>">
                                        <?= $rate ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE 2: KHỐI LƯỢNG CÔNG VIỆC THEO NHÓM IT (RULE 40) -->
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm bg-white h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="fw-bold text-dark"><i class="bi bi-diagram-3-fill me-2 text-primary"></i>Khối Lượng Xử Lý Theo Nhóm IT (Rule 40)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Nhóm IT</th>
                                <th class="text-center">Tổng</th>
                                <th class="text-center">Active</th>
                                <th class="text-center">Waiting</th>
                                <th class="text-center">Overdue</th>
                                <th class="pe-3 text-end">Unassigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groupWorkload as $gw): ?>
                                <tr>
                                    <td class="ps-3 fw-bold">
                                        <a href="<?= $baseUrl ?>/tickets?group_id=<?= $gw['id'] ?>" class="text-decoration-none text-primary hover-underline" title="Xem tất cả ticket của nhóm">
                                            <?= htmlspecialchars($gw['name']) ?> <i class="bi bi-box-arrow-up-right ms-1 small" style="font-size: 0.7rem;"></i>
                                        </a>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <a href="<?= $baseUrl ?>/tickets?group_id=<?= $gw['id'] ?>" class="text-decoration-none text-dark fw-bold">
                                            <?= (int)$gw['total_count'] ?>
                                        </a>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <a href="<?= $baseUrl ?>/tickets?group_id=<?= $gw['id'] ?>&status=IN_PROGRESS" class="text-decoration-none text-primary fw-bold">
                                            <?= (int)$gw['active_count'] ?>
                                        </a>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <a href="<?= $baseUrl ?>/tickets?group_id=<?= $gw['id'] ?>&status=WAITING_USER" class="text-decoration-none text-warning fw-bold">
                                            <?= (int)$gw['waiting_count'] ?>
                                        </a>
                                    </td>
                                    <td class="text-center font-monospace">
                                        <a href="<?= $baseUrl ?>/tickets?group_id=<?= $gw['id'] ?>&sla_status=OVERDUE" class="text-decoration-none text-danger fw-bold">
                                            <?= (int)$gw['overdue_count'] ?>
                                        </a>
                                    </td>
                                    <td class="pe-3 text-end font-monospace">
                                        <a href="<?= $baseUrl ?>/tickets/unassigned?group_id=<?= $gw['id'] ?>" class="text-decoration-none text-secondary fw-bold">
                                            <?= (int)$gw['unassigned_count'] ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHART.JS INTEGRATION SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Line/Bar Trend Chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($trendData['labels']) ?>,
                datasets: [
                    {
                        label: 'Yêu cầu phát sinh',
                        data: <?= json_encode($trendData['created']) ?>,
                        borderColor: '#0052cc',
                        backgroundColor: 'rgba(0, 82, 204, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4
                    },
                    {
                        label: 'Đã hoàn thành',
                        data: <?= json_encode($trendData['resolved']) ?>,
                        borderColor: '#36b37e',
                        backgroundColor: 'rgba(54, 179, 126, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // 2. Group Doughnut Chart
    const groupCtx = document.getElementById('groupPieChart');
    if (groupCtx) {
        const groupLabels = <?= json_encode(array_column($groupWorkload, 'name')) ?>;
        const groupValues = <?= json_encode(array_column($groupWorkload, 'total_count')) ?>;
        const colors = ['#0052cc', '#ffab00', '#36b37e', '#ff5630', '#6554c0', '#00b8d9'];

        new Chart(groupCtx, {
            type: 'doughnut',
            data: {
                labels: groupLabels,
                datasets: [{
                    data: groupValues,
                    backgroundColor: colors.slice(0, groupLabels.length),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } }
                },
                cutout: '65%'
            }
        });
    }
});
</script>

