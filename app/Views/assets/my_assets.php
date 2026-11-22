<?php
use App\Core\Response;

$baseUrl = Response::baseUrl();
?>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.78rem;">
                <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none" style="color:#0052CC;">Trang chủ</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page" style="color:#5E6C84;">Tài sản của tôi</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0" style="color:#172B4D;"><i class="bi bi-laptop me-2" style="color:#0052CC;"></i>Thiết Bị CNTT Của Tôi</h3>
        <p class="small mb-0 mt-1" style="color:#5E6C84;">Danh sách các thiết bị, máy tính và tài sản công nghệ công ty bàn giao cho bạn quản lý và sử dụng.</p>
    </div>
    <a href="<?= $baseUrl ?>/tickets/create" class="btn fw-bold"
       style="background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);color:#fff;border:none;border-radius:6px;font-size:.875rem;padding:.5rem 1.2rem;">
        <i class="bi bi-plus-lg me-1"></i>Báo Sự Cố / Yêu Cầu Hỗ Trợ
    </a>
</div>

<?php if (empty($assets)): ?>
    <div class="card text-center p-5" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
        <i class="bi bi-inbox d-block mb-3" style="font-size:3.5rem;color:#DFE1E6;"></i>
        <h5 class="fw-bold" style="color:#172B4D;">Hiện tại bạn chưa được cấp phát thiết bị CNTT nào.</h5>
        <p class="small mb-4" style="color:#5E6C84;">Nếu bạn cần thiết bị làm việc (Laptop, Màn hình, Bản quyền phần mềm...), vui lòng gửi một ticket yêu cầu cấp phát mới cho bộ phận IT.</p>
        <div>
            <a href="<?= $baseUrl ?>/tickets/create" class="btn fw-bold"
               style="background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);color:#fff;border:none;border-radius:6px;padding:.5rem 1.5rem;">
                <i class="bi bi-plus-circle me-1"></i>Gửi Yêu Cầu Cấp Phát Thiết Bị
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Stats bar -->
    <div class="mb-3 d-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fw-semibold" style="background:#EAF0FB;color:#0052CC;border:1px solid #B3C7F5;font-size:.82rem;">
            <i class="bi bi-laptop me-1"></i><?= count($assets) ?> thiết bị được cấp phát
        </span>
    </div>

    <div class="row g-3">
        <?php foreach ($assets as $a): ?>
            <?php
            $catIcon = htmlspecialchars($a['category_icon'] ?? 'bi-laptop');
            $accentColors = ['#0052CC','#00875A','#FF991F','#6554C0','#00B8D9'];
            $accent = $accentColors[crc32($a['name']) % count($accentColors)];
            ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 d-flex flex-column" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;border-top:3px solid <?= $accent ?>;">
                    <!-- Card Header -->
                    <div class="px-4 pt-3 pb-2 d-flex justify-content-between align-items-center" style="border-bottom:1px solid #DFE1E6;">
                        <span class="badge px-2 py-1" style="background:#F4F5F7;color:#172B4D;border:1px solid #DFE1E6;font-size:.75rem;">
                            <i class="bi <?= $catIcon ?> me-1" style="color:<?= $accent ?>;"></i>
                            <?= htmlspecialchars($a['category_name']) ?>
                        </span>
                        <span class="badge px-2 py-1 fw-bold d-inline-flex align-items-center gap-1"
                              style="background:#E3FCEF;color:#00875A;border:1px solid #ABF5D1;border-radius:20px;font-size:.72rem;">
                            <i class="bi bi-check-circle-fill"></i>Đang sử dụng
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-4 flex-grow-1">
                        <!-- Asset code pill -->
                        <div class="d-inline-flex align-items-center gap-1 mb-2 font-monospace fw-bold px-2 py-1 rounded"
                             style="background:#EAF0FB;color:#0052CC;border:1px solid #B3C7F5;font-size:.72rem;">
                            <i class="bi bi-upc-scan"></i><?= htmlspecialchars($a['asset_code']) ?>
                        </div>

                        <h5 class="fw-bold mb-3" style="color:#172B4D;"><?= htmlspecialchars($a['name']) ?></h5>

                        <!-- Asset details -->
                        <div class="small mb-3" style="color:#5E6C84;">
                            <div class="mb-1"><i class="bi bi-building me-1"></i>Hãng: <strong style="color:#172B4D;"><?= htmlspecialchars($a['manufacturer'] ?: 'N/A') ?></strong> (<?= htmlspecialchars($a['model'] ?: '') ?>)</div>
                            <div class="mb-1"><i class="bi bi-hash me-1"></i>Serial: <span class="font-monospace fw-bold" style="color:#172B4D;"><?= htmlspecialchars($a['serial_number'] ?: 'N/A') ?></span></div>
                            <?php if (!empty($a['location'])): ?>
                                <div><i class="bi bi-geo-alt me-1" style="color:#DE350B;"></i>Vị trí: <?= htmlspecialchars($a['location']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Specs preview -->
                        <?php if (!empty($a['specifications'])): ?>
                            <div class="p-2 rounded mb-2 font-monospace text-secondary small"
                                 style="font-size:.72rem;white-space:pre-line;max-height:72px;overflow-y:auto;background:#F4F5F7;border:1px solid #DFE1E6;">
                                <?= htmlspecialchars($a['specifications']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-4 py-3 d-flex justify-content-between align-items-center" style="border-top:1px solid #DFE1E6;">
                        <a href="<?= $baseUrl ?>/assets/<?= $a['id'] ?>" class="btn btn-sm"
                           style="border:1.5px solid #DFE1E6;border-radius:6px;color:#5E6C84;background:#fff;font-size:.78rem;">
                            <i class="bi bi-clock-history me-1"></i>Xem lịch sử máy
                        </a>
                        <a href="<?= $baseUrl ?>/tickets/create?asset_id=<?= $a['id'] ?>" class="btn btn-sm fw-bold"
                           style="border:1.5px solid #DE350B;color:#DE350B;border-radius:6px;background:#fff;font-size:.78rem;">
                            <i class="bi bi-exclamation-triangle me-1"></i>Báo sự cố
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
