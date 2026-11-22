<?php
if (!class_exists(\App\Core\Response::class)) {
    require_once dirname(__DIR__, 2) . '/autoload.php';
    require_once dirname(__DIR__, 2) . '/Helpers.php';
}

use App\Core\Auth;
use App\Core\Response;

/**
 * @var array $kpis
 * @var array $assets
 * @var array $categories
 * @var array $filters
 */
$baseUrl = Response::baseUrl();
$kpis = $kpis ?? [];
$assets = $assets ?? [];
$categories = $categories ?? [];
$filters = $filters ?? [];

if (php_sapi_name() !== 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    header('Location: ' . Response::baseUrl() . '/assets');
    exit;
}
?>

<!-- PAGE HERO + HEADER -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-3 shadow-sm"
             style="width:52px;height:52px;background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);">
            <i class="bi bi-laptop fs-4 text-white"></i>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:.78rem;">
                    <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/dashboard" class="text-decoration-none" style="color:#0052CC;">Trang chủ</a></li>
                    <li class="breadcrumb-item active fw-semibold" aria-current="page" style="color:#5E6C84;">Quản lý Tài sản CNTT</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-0" style="color:#172B4D;">Quản Lý Thiết Bị &amp; Tài Sản CNTT (IT Assets)</h3>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if (Auth::can('export.assets')): ?>
            <a href="<?= $baseUrl ?>/assets/export" class="btn btn-outline-success fw-semibold d-flex align-items-center gap-2 shadow-sm" style="border-radius:6px;padding:.5rem 1.1rem;" title="Xuất danh sách thiết bị CNTT ra file Excel">
                <i class="bi bi-file-earmark-excel-fill"></i>Xuất Excel
            </a>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>/assets/create" class="btn fw-bold d-flex align-items-center gap-2 shadow-sm"
           style="background:linear-gradient(135deg,#0052CC 0%,#0068E1 100%);color:#fff;border:none;border-radius:6px;padding:.5rem 1.2rem;">
            <i class="bi bi-plus-lg"></i>Thêm Mới Thiết Bị
        </a>
    </div>
</div>

<!-- KPI SUMMARY CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 p-3" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;border-left:4px solid #0052CC;">
            <div class="fw-bold text-uppercase" style="color:#5E6C84;font-size:.72rem;letter-spacing:.05em;">Tổng số thiết bị</div>
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:#172B4D;"><?= number_format($kpis['total'] ?? 0) ?></div>
            <div class="small mt-1" style="color:#5E6C84;"><i class="bi bi-cpu me-1"></i>Toàn bộ danh mục</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 p-3" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;border-left:4px solid #00875A;">
            <div class="fw-bold text-uppercase" style="color:#5E6C84;font-size:.72rem;letter-spacing:.05em;">Đang sử dụng</div>
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:#00875A;"><?= number_format($kpis['in_use'] ?? 0) ?></div>
            <div class="small mt-1" style="color:#5E6C84;"><i class="bi bi-person-check-fill me-1"></i>Đã cấp phát cho User</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 p-3" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;border-left:4px solid #00B8D9;">
            <div class="fw-bold text-uppercase" style="color:#5E6C84;font-size:.72rem;letter-spacing:.05em;">Tồn trong kho IT</div>
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:#00B8D9;"><?= number_format($kpis['in_stock'] ?? 0) ?></div>
            <div class="small mt-1" style="color:#5E6C84;"><i class="bi bi-box-seam-fill me-1"></i>Sẵn sàng bàn giao</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 p-3" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;border-left:4px solid #FF991F;">
            <div class="fw-bold text-uppercase" style="color:#5E6C84;font-size:.72rem;letter-spacing:.05em;">Đang sửa chữa / BH</div>
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:#FF991F;"><?= number_format($kpis['in_repair'] ?? 0) ?></div>
            <div class="small mt-1" style="color:#5E6C84;"><i class="bi bi-tools me-1"></i>Chờ hãng hoặc thay thế</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card h-100 p-3" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;border-left:4px solid #5E6C84;">
            <div class="fw-bold text-uppercase" style="color:#5E6C84;font-size:.72rem;letter-spacing:.05em;">Thanh lý / Hỏng</div>
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:#5E6C84;"><?= number_format($kpis['liquidated'] ?? 0) ?></div>
            <div class="small mt-1" style="color:#5E6C84;"><i class="bi bi-trash3-fill me-1"></i>Đã thu hồi / Hủy</div>
        </div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="card mb-4" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
    <div class="card-body p-3">
        <form action="<?= $baseUrl ?>/assets" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold mb-1" style="color:#5E6C84;font-size:.75rem;">TÌM KIẾM</label>
                <div class="input-group" style="border:1.5px solid #DFE1E6;border-radius:6px;overflow:hidden;">
                    <span class="input-group-text border-0" style="background:#F4F5F7;"><i class="bi bi-search" style="color:#5E6C84;"></i></span>
                    <input type="text" name="search" class="form-control border-0" style="background:#F4F5F7;"
                           placeholder="Mã máy, Model, Serial, người dùng..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold mb-1" style="color:#5E6C84;font-size:.75rem;">LOẠI THIẾT BỊ</label>
                <select name="category_id" class="form-select" style="border:1.5px solid #DFE1E6;border-radius:6px;font-size:.875rem;color:#172B4D;">
                    <option value="">-- Tất cả Loại --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($filters['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold mb-1" style="color:#5E6C84;font-size:.75rem;">TRẠNG THÁI</label>
                <select name="status" class="form-select" style="border:1.5px solid #DFE1E6;border-radius:6px;font-size:.875rem;color:#172B4D;">
                    <option value="">-- Tất cả Trạng thái --</option>
                    <option value="IN_USE"     <?= (($filters['status'] ?? '') === 'IN_USE')     ? 'selected' : '' ?>>Đang sử dụng (IN_USE)</option>
                    <option value="IN_STOCK"   <?= (($filters['status'] ?? '') === 'IN_STOCK')   ? 'selected' : '' ?>>Trong kho IT (IN_STOCK)</option>
                    <option value="REPAIR"     <?= (($filters['status'] ?? '') === 'REPAIR')     ? 'selected' : '' ?>>Đang sửa chữa (REPAIR)</option>
                    <option value="DAMAGED"    <?= (($filters['status'] ?? '') === 'DAMAGED')    ? 'selected' : '' ?>>Hư hại (DAMAGED)</option>
                    <option value="LOST"       <?= (($filters['status'] ?? '') === 'LOST')       ? 'selected' : '' ?>>Thất lạc (LOST)</option>
                    <option value="LIQUIDATED" <?= (($filters['status'] ?? '') === 'LIQUIDATED') ? 'selected' : '' ?>>Đã thanh lý (LIQUIDATED)</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn fw-bold flex-grow-1"
                        style="background:#0052CC;color:#fff;border:none;border-radius:6px;font-size:.875rem;">
                    <i class="bi bi-funnel me-1"></i>Lọc
                </button>
                <a href="<?= $baseUrl ?>/assets" class="btn" title="Đặt lại bộ lọc"
                   style="border:1.5px solid #DFE1E6;border-radius:6px;color:#5E6C84;background:#fff;">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ASSETS TABLE -->
<div class="card" style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(9,30,66,.12);border:1px solid #DFE1E6;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
            <thead>
                <tr style="background:#F4F5F7;color:#5E6C84;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;">
                    <th class="ps-4 py-3">Mã tài sản</th>
                    <th class="py-3">Tên thiết bị &amp; Cấu hình</th>
                    <th class="py-3">Loại</th>
                    <th class="py-3">Người sử dụng</th>
                    <th class="py-3">Phòng ban / Vị trí</th>
                    <th class="py-3">Trạng thái</th>
                    <th class="py-3">Bảo hành</th>
                    <th class="pe-4 py-3 text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assets)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color:#5E6C84;">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2.5rem;color:#DFE1E6;"></i>
                            Không tìm thấy thiết bị nào khớp với điều kiện lọc.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assets as $a): ?>
                        <?php
                        $statusBadgeStyle = match($a['status']) {
                            'IN_USE'     => 'background:#E3FCEF;color:#00875A;border:1px solid #ABF5D1;',
                            'IN_STOCK'   => 'background:#EAF0FB;color:#0052CC;border:1px solid #B3C7F5;',
                            'REPAIR'     => 'background:#FFFAE6;color:#FF991F;border:1px solid #FFE380;',
                            'DAMAGED'    => 'background:#FFEBE6;color:#DE350B;border:1px solid #FFBDAD;',
                            'LOST'       => 'background:#DE350B;color:#fff;border:none;',
                            'LIQUIDATED' => 'background:#F4F5F7;color:#5E6C84;border:1px solid #DFE1E6;',
                            default      => 'background:#F4F5F7;color:#172B4D;border:1px solid #DFE1E6;'
                        };
                        $statusText = match($a['status']) {
                            'IN_USE'     => 'Đang sử dụng',
                            'IN_STOCK'   => 'Trong kho IT',
                            'REPAIR'     => 'Đang sửa chữa',
                            'DAMAGED'    => 'Hư hại',
                            'LOST'       => 'Thất lạc',
                            'LIQUIDATED' => 'Đã thanh lý',
                            default      => $a['status']
                        };
                        $avatarColors = ['#0052CC','#00875A','#FF991F','#6554C0','#00B8D9'];
                        $uAvatar = !empty($a['current_user_name']) ? $avatarColors[abs(crc32($a['current_user_name'])) % count($avatarColors)] : '#5E6C84';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <a href="<?= $baseUrl ?>/assets/<?= $a['id'] ?>"
                                   class="fw-bold text-decoration-none font-monospace"
                                   style="color:#0052CC;"><?= htmlspecialchars($a['asset_code'] ?? '') ?></a>
                            </td>
                            <td>
                                <div class="fw-semibold" style="color:#172B4D;">
                                    <a href="<?= $baseUrl ?>/assets/<?= $a['id'] ?>" class="text-decoration-none" style="color:#172B4D;">
                                        <?= htmlspecialchars($a['name'] ?? '') ?>
                                    </a>
                                </div>
                                <div style="font-size:.72rem;color:#5E6C84;">
                                    <?= htmlspecialchars($a['manufacturer'] ?? '') ?> <?= htmlspecialchars($a['model'] ?? '') ?>
                                    <?php if (!empty($a['serial_number'])): ?>
                                        &bull; <span class="font-monospace">SN: <?= htmlspecialchars($a['serial_number'] ?? '') ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge px-2 py-1" style="background:#F4F5F7;color:#172B4D;border:1px solid #DFE1E6;font-size:.75rem;">
                                    <i class="bi <?= htmlspecialchars($a['category_icon'] ?? 'bi-laptop') ?> me-1" style="color:#0052CC;"></i>
                                    <?= htmlspecialchars($a['category_name'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($a['current_user_name'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold flex-shrink-0"
                                             style="width:26px;height:26px;font-size:.65rem;background:<?= $uAvatar ?>;">
                                            <?= strtoupper(mb_substr($a['current_user_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold small" style="color:#172B4D;"><?= htmlspecialchars($a['current_user_name'] ?? '') ?></div>
                                            <div style="font-size:.68rem;color:#5E6C84;"><?= htmlspecialchars($a['current_user_email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="small fst-italic" style="color:#5E6C84;">-- Chưa cấp phát --</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small fw-medium" style="color:#172B4D;"><?= htmlspecialchars($a['department'] ?? 'Kho IT') ?></div>
                                <div style="font-size:.70rem;color:#5E6C84;"><?= htmlspecialchars($a['location'] ?? 'Chưa cập nhật') ?></div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1 small fw-semibold" style="<?= $statusBadgeStyle ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($a['warranty_expire'])): ?>
                                    <?php
                                    $exp = strtotime($a['warranty_expire']);
                                    $isExpired = time() > $exp;
                                    ?>
                                    <span class="small" style="color:<?= $isExpired ? '#DE350B' : '#5E6C84' ?>;font-weight:<?= $isExpired ? '700' : '400' ?>;">
                                        <?= date('d/m/Y', $exp) ?>
                                        <?= $isExpired ? '<i class="bi bi-exclamation-circle-fill ms-1" title="Hết hạn bảo hành"></i>' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="small" style="color:#5E6C84;">--</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end text-nowrap">
                                <a href="<?= $baseUrl ?>/assets/<?= $a['id'] ?>/qr" class="btn btn-sm btn-outline-dark fw-semibold me-1"
                                   style="border-radius:6px;font-size:.78rem;padding:.25rem .6rem;" title="Xem & In Tem QR Code">
                                    <i class="bi bi-qr-code me-1"></i>In QR
                                </a>
                                <a href="<?= $baseUrl ?>/assets/<?= $a['id'] ?>" class="btn btn-sm fw-semibold"
                                   style="border:1.5px solid #0052CC;color:#0052CC;border-radius:6px;font-size:.78rem;padding:.25rem .75rem;">
                                    Chi tiết <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
