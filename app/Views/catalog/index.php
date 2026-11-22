<?php
/**
 * @var array $categories
 */
$baseUrl = \App\Core\Response::baseUrl();

/* Category icon & color map — add more as needed */
$catIconMap = [
    'network'    => ['icon' => 'bi-router-fill',          'color' => '#0065FF', 'bg' => '#E6F0FF'],
    'hardware'   => ['icon' => 'bi-pc-display-horizontal', 'color' => '#6554C0', 'bg' => '#EAE6FF'],
    'software'   => ['icon' => 'bi-code-slash',            'color' => '#00875A', 'bg' => '#E3FCEF'],
    'account'    => ['icon' => 'bi-person-badge-fill',     'color' => '#0052CC', 'bg' => '#DEEBFF'],
    'email'      => ['icon' => 'bi-envelope-fill',         'color' => '#FF991F', 'bg' => '#FFFAE6'],
    'database'   => ['icon' => 'bi-database-fill',         'color' => '#DE350B', 'bg' => '#FFEBE6'],
    'security'   => ['icon' => 'bi-shield-lock-fill',      'color' => '#172B4D', 'bg' => '#EBECF0'],
    'server'     => ['icon' => 'bi-server',                'color' => '#344563', 'bg' => '#EBECF0'],
    'default'    => ['icon' => 'bi-folder2-open',          'color' => '#0052CC', 'bg' => '#DEEBFF'],
];

function getCatStyle(array $map, string $name): array {
    $nameLower = strtolower($name);
    foreach ($map as $key => $style) {
        if ($key !== 'default' && str_contains($nameLower, $key)) {
            return $style;
        }
    }
    return $map['default'];
}
?>

<!-- PAGE HERO -->
<div class="page-hero mb-4" style="border-left-color: var(--primary);">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;background:var(--primary-subtle);">
                    <i class="bi bi-grid-3x3-gap-fill text-primary" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <span class="badge rounded-pill fw-semibold mb-1"
                          style="background:var(--primary-subtle);color:var(--primary);padding:4px 12px;">
                        <i class="bi bi-shield-check me-1"></i> ITIL V4 Service Catalog
                    </span>
                    <h3 class="fw-bold mb-0" style="color:var(--n800);">Cổng Dịch Vụ CNTT (IT Service Portal)</h3>
                </div>
            </div>
            <p class="mb-0" style="color:var(--n400);max-width:640px;">
                Khám phá danh mục dịch vụ công nghệ thông tin. Gửi yêu cầu cấp phát hoặc báo cáo sự cố để được đội ngũ IT hỗ trợ theo cam kết SLA.
            </p>
        </div>
        <div class="col-lg-5">
            <div class="d-flex flex-sm-row flex-column gap-2 justify-content-lg-end">
                <a href="<?= $baseUrl ?>/tickets/create?type=SERVICE_REQUEST"
                   class="btn btn-primary btn-lg fw-semibold shadow-sm">
                    <i class="bi bi-plus-circle-dotted me-1"></i> Gửi Yêu Cầu
                </a>
                <a href="<?= $baseUrl ?>/tickets/create?type=INCIDENT"
                   class="btn btn-lg fw-semibold shadow-sm"
                   style="background:var(--danger);color:#fff;border:none;">
                    <i class="bi bi-exclamation-triangle me-1"></i> Báo Sự Cố
                </a>
            </div>
        </div>
    </div>
</div>

<!-- SEARCH BAR -->
<div class="mb-4">
    <div class="position-relative" style="max-width: 600px; margin: 0 auto;">
        <i class="bi bi-search position-absolute" style="left:18px;top:50%;transform:translateY(-50%);color:var(--n300);font-size:1.1rem;"></i>
        <input type="text" id="catalogSearch"
               class="form-control form-control-lg"
               style="padding-left:50px;border:1.5px solid var(--n60);border-radius:var(--radius-md);background:var(--n30);font-size:1rem;box-shadow:var(--shadow-sm);"
               placeholder="Tìm kiếm dịch vụ, nhóm dịch vụ...">
    </div>
</div>

<!-- SECTION TITLE -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0" style="color:var(--n800);">
        <i class="bi bi-collection me-2" style="color:var(--primary);"></i>
        Danh mục hệ thống
        <span class="badge rounded-pill ms-2"
              style="background:var(--primary-subtle);color:var(--primary);font-size:.8rem;">
            <?= count($categories) ?> nhóm dịch vụ
        </span>
    </h5>
</div>

<!-- Catalog Grid -->
<div class="row g-4" id="catalogGrid">
    <?php foreach ($categories as $cat): ?>
        <?php $style = getCatStyle($catIconMap, $cat['name']); ?>
        <div class="col-md-6 col-xl-4 catalog-item">
            <div class="card h-100 catalog-service-card"
                 style="border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--n60);transition:box-shadow .2s,transform .2s;"
                 onmouseover="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateY(-3px)';"
                 onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='none';">

                <!-- Card Header -->
                <div class="card-header bg-white pt-4 px-4 pb-3"
                     style="border-radius:var(--radius-lg) var(--radius-lg) 0 0;border-bottom:1px solid var(--n50);">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:52px;height:52px;background:<?= $style['bg'] ?>;">
                                <i class="bi <?= $style['icon'] ?>" style="font-size:1.6rem;color:<?= $style['color'] ?>;"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color:var(--n800);"><?= htmlspecialchars($cat['name']) ?></h5>
                                <span class="badge mt-1" style="background:var(--n50);color:var(--n400);border:1px solid var(--n60);font-size:.7rem;">
                                    Mã: <?= htmlspecialchars($cat['code']) ?>
                                </span>
                            </div>
                        </div>
                        <span class="badge rounded-pill flex-shrink-0"
                              style="background:<?= $style['bg'] ?>;color:<?= $style['color'] ?>;padding:5px 12px;font-size:.8rem;font-weight:600;">
                            <?= count($cat['services'] ?? []) ?> dịch vụ
                        </span>
                    </div>
                    <?php if (!empty($cat['description'])): ?>
                        <p class="small mt-2 mb-0" style="color:var(--n400);"><?= htmlspecialchars($cat['description']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Service List -->
                <div class="card-body px-4 py-3">
                    <div class="small fw-semibold text-uppercase mb-2" style="color:var(--n300);letter-spacing:.5px;font-size:.7rem;">
                        Dịch vụ trực thuộc
                    </div>
                    <?php if (empty($cat['services'])): ?>
                        <div class="py-3 text-center">
                            <i class="bi bi-inbox d-block mb-1" style="font-size:1.8rem;color:var(--n80);"></i>
                            <span class="small fst-italic" style="color:var(--n300);">Chưa có dịch vụ con. Bạn có thể tạo ticket chung cho nhóm này.</span>
                        </div>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <?php foreach ($cat['services'] as $svc): ?>
                                <li class="d-flex justify-content-between align-items-center p-2 rounded-2 service-row"
                                    style="background:var(--n30);border:1px solid var(--n60);">
                                    <div class="me-2 overflow-hidden">
                                        <div class="fw-semibold text-truncate" style="color:var(--n800);font-size:.88rem;">
                                            <i class="bi bi-gear-fill me-1" style="color:var(--n300);font-size:.78rem;"></i>
                                            <?= htmlspecialchars($svc['name']) ?>
                                        </div>
                                        <?php if (!empty($svc['description'])): ?>
                                            <div class="small text-truncate" style="color:var(--n300);font-size:.73rem;">
                                                <?= htmlspecialchars($svc['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dropdown flex-shrink-0">
                                        <button class="btn btn-sm dropdown-toggle py-1 px-2"
                                                type="button" data-bs-toggle="dropdown"
                                                style="font-size:.78rem;border:1.5px solid <?= $style['color'] ?>;color:<?= $style['color'] ?>;background:transparent;">
                                            Tạo
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:var(--radius-md);">
                                            <li>
                                                <a class="dropdown-item py-2"
                                                   href="<?= $baseUrl ?>/tickets/create?type=SERVICE_REQUEST&category_id=<?= $cat['id'] ?>&service_id=<?= $svc['id'] ?>">
                                                    <i class="bi bi-send-plus me-2" style="color:var(--primary);"></i> Yêu cầu dịch vụ này
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2 text-danger"
                                                   href="<?= $baseUrl ?>/tickets/create?type=INCIDENT&category_id=<?= $cat['id'] ?>&service_id=<?= $svc['id'] ?>">
                                                    <i class="bi bi-bug me-2 text-danger"></i> Báo cáo lỗi dịch vụ này
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Footer Actions -->
                <div class="card-footer bg-white px-4 pb-4 pt-2"
                     style="border-radius:0 0 var(--radius-lg) var(--radius-lg);border-top:1px solid var(--n50);">
                    <div class="d-flex gap-2">
                        <a href="<?= $baseUrl ?>/tickets/create?type=SERVICE_REQUEST&category_id=<?= $cat['id'] ?>"
                           class="btn btn-sm flex-grow-1 fw-semibold"
                           style="background:<?= $style['color'] ?>;color:#fff;border:none;">
                            <i class="bi bi-plus-lg me-1"></i> Yêu Cầu Dịch Vụ
                        </a>
                        <a href="<?= $baseUrl ?>/tickets/create?type=INCIDENT&category_id=<?= $cat['id'] ?>"
                           class="btn btn-sm fw-semibold"
                           style="border:1.5px solid var(--danger);color:var(--danger);background:transparent;"
                           title="Báo cáo sự cố liên quan">
                            <i class="bi bi-exclamation-triangle"></i> Báo Lỗi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Empty state if no categories -->
<?php if (empty($categories)): ?>
<div class="text-center py-5">
    <i class="bi bi-collection d-block mb-3" style="font-size:4rem;color:var(--n80);"></i>
    <div class="fw-bold mb-1" style="color:var(--n500);font-size:1.1rem;">Chưa có danh mục dịch vụ nào</div>
    <div class="small" style="color:var(--n300);">Hãy liên hệ với bộ phận IT để được hỗ trợ thiết lập danh mục dịch vụ.</div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('catalogSearch');
    const items = document.querySelectorAll('.catalog-item');

    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        let visibleCount = 0;
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            const show = !query || text.includes(query);
            item.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });
    });
});
</script>
