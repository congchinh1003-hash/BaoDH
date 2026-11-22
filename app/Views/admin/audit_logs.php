<?php
use App\Core\Response;

/**
 * @var array $logs
 */
$baseUrl = Response::baseUrl();
?>

<!-- PAGE HERO -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-journal-text fs-4 text-white"></i>
        </div>
        <div>
            <h4 class="fw-bold mb-0" style="color:#172B4D;">Nhật Ký Kiểm Toán Toàn Bộ Hệ Thống (System Audit Logs)</h4>
            <p class="mb-0 small" style="color:#5E6C84;">Theo dõi toàn diện các thao tác nhạy cảm, thay đổi dữ liệu phân công và trạng thái theo thời gian thực</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if (\App\Core\Auth::can('export.audit_logs')): ?>
            <a href="<?= $baseUrl ?>/admin/audit-logs/export" class="btn btn-outline-success btn-sm fw-semibold px-3 shadow-sm" title="Xuất toàn bộ nhật ký kiểm toán ra file Excel">
                <i class="bi bi-file-earmark-excel-fill me-1"></i>Xuất Excel
            </a>
        <?php endif; ?>
        <span class="badge px-3 py-2 fw-semibold" style="background:#F4F5F7;color:#5E6C84;border:1px solid #DFE1E6;font-size:.82rem;">
            <i class="bi bi-clock-history me-1"></i>Hiển thị 100 thao tác gần nhất
        </span>
    </div>
</div>

<!-- TIMELINE CARD -->
<div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
    <div class="card-header bg-white py-3 px-4 border-bottom" style="border-bottom:1px solid #DFE1E6;border-radius:10px 10px 0 0;">
        <span class="fw-bold" style="color:#172B4D;">
            <i class="bi bi-journal-text me-2" style="color:#0052CC;"></i>Bản Ghi Hoạt Động Gần Đây
        </span>
    </div>

    <?php if (empty($logs)): ?>
        <div class="p-5 text-center" style="color:#5E6C84;">
            <i class="bi bi-journal-check d-block mb-3" style="font-size:3rem;color:#DFE1E6;"></i>
            <div class="fw-semibold">Chưa ghi nhận bản ghi kiểm toán nào trong hệ thống.</div>
        </div>
    <?php else: ?>
        <!-- Timeline -->
        <div class="p-4">
            <?php
            $actionIconMap = [
                'CREATE'   => ['icon' => 'bi-plus-circle-fill', 'bg' => '#E3FCEF', 'color' => '#00875A'],
                'UPDATE'   => ['icon' => 'bi-pencil-fill',       'bg' => '#EAF0FB', 'color' => '#0052CC'],
                'DELETE'   => ['icon' => 'bi-trash3-fill',        'bg' => '#FFEBE6', 'color' => '#DE350B'],
                'ASSIGN'   => ['icon' => 'bi-person-plus-fill',   'bg' => '#EAF0FB', 'color' => '#0052CC'],
                'TRANSFER' => ['icon' => 'bi-arrow-left-right',   'bg' => '#FFFAE6', 'color' => '#FF991F'],
                'CLOSE'    => ['icon' => 'bi-x-circle-fill',      'bg' => '#F4F5F7', 'color' => '#5E6C84'],
                'REOPEN'   => ['icon' => 'bi-arrow-counterclockwise','bg'=>'#FFFAE6','color'=>'#FF991F'],
                'LOGIN'    => ['icon' => 'bi-box-arrow-in-right', 'bg' => '#E3FCEF', 'color' => '#00875A'],
                'LOGOUT'   => ['icon' => 'bi-box-arrow-right',    'bg' => '#F4F5F7', 'color' => '#5E6C84'],
            ];
            $avatarPalette = ['#0052CC','#00875A','#FF991F','#6554C0','#00B8D9','#DE350B'];
            ?>

            <div style="position:relative;">
                <!-- Vertical line -->
                <div style="position:absolute;left:19px;top:0;bottom:0;width:2px;background:#DFE1E6;z-index:0;"></div>

                <?php foreach ($logs as $idx => $l): ?>
                    <?php
                    $actionKey = strtoupper($l['action'] ?? '');
                    // Partial match
                    $aStyle = ['icon' => 'bi-activity', 'bg' => '#F4F5F7', 'color' => '#5E6C84'];
                    foreach ($actionIconMap as $k => $v) {
                        if (str_contains($actionKey, $k)) { $aStyle = $v; break; }
                    }
                    $uname = $l['user_name'] ?? 'System';
                    $aBg = $avatarPalette[crc32($uname) % count($avatarPalette)];
                    ?>
                    <div class="d-flex gap-3 mb-4 position-relative" style="z-index:1;">
                        <!-- Action Icon -->
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                             style="width:38px;height:38px;background:<?= $aStyle['bg'] ?>;border:2px solid <?= $aStyle['color'] ?>20;z-index:2;">
                            <i class="bi <?= $aStyle['icon'] ?>" style="color:<?= $aStyle['color'] ?>;font-size:.9rem;"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1 rounded-3 p-3" style="background:#F8F9FA;border:1px solid #DFE1E6;">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <!-- User avatar -->
                                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold flex-shrink-0"
                                         style="width:24px;height:24px;font-size:.62rem;background:<?= $aBg ?>;">
                                        <?= strtoupper(mb_substr($uname, 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold small" style="color:#172B4D;"><?= htmlspecialchars($uname) ?></span>
                                    <span class="badge px-2 py-0 font-monospace fw-bold" style="background:<?= $aStyle['bg'] ?>;color:<?= $aStyle['color'] ?>;border:1px solid <?= $aStyle['color'] ?>30;font-size:.72rem;">
                                        <?= htmlspecialchars($l['action']) ?>
                                    </span>
                                    <span class="badge px-2 py-0" style="background:#F4F5F7;color:#5E6C84;border:1px solid #DFE1E6;font-size:.72rem;font-family:monospace;">
                                        <?= htmlspecialchars($l['object_type']) ?> #<?= $l['object_id'] ?>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-shrink-0" style="font-size:.72rem;color:#5E6C84;">
                                    <span class="font-monospace"><i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?></span>
                                    <span class="font-monospace"><i class="bi bi-globe me-1"></i><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></span>
                                    <span class="font-monospace opacity-75">#<?= $l['id'] ?></span>
                                </div>
                            </div>
                            <?php if (!empty($l['reason']) && $l['reason'] !== '-'): ?>
                                <div class="mt-2 small" style="color:#5E6C84;">
                                    <i class="bi bi-chat-left-quote me-1"></i><em><?= htmlspecialchars($l['reason']) ?></em>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
