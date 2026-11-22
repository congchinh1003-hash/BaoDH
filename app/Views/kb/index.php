<?php
use App\Core\Auth;
use App\Core\Response;

/**
 * @var array $categories
 * @var array $articles
 * @var array $popular
 * @var array $filters
 * @var array|null $selectedCategory
 */
$baseUrl = Response::baseUrl();
$isStaff = Auth::isStaff() || Auth::isManager();
?>
<style>
.kb-search-hero {
    background: linear-gradient(135deg, #0052CC 0%, #0747A6 100%);
    border-radius: 10px;
    padding: 40px 48px;
    margin-bottom: 28px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0,82,204,0.2);
}
.kb-search-hero .search-input-group {
    max-width: 680px;
    margin: 0 auto;
}
.kb-search-hero .form-control {
    border: none;
    border-radius: 8px 0 0 8px;
    padding: 14px 18px;
    font-size: 1rem;
    box-shadow: none;
}
.kb-search-hero .btn-search {
    background: #FF991F;
    color: #172B4D;
    border: none;
    padding: 14px 28px;
    font-weight: 700;
    border-radius: 0 8px 8px 0;
    font-size: 0.95rem;
}
.kb-search-hero .btn-search:hover { background: #FF8B00; }
.kb-search-hero .input-group-text {
    background: #fff;
    border: none;
    border-radius: 8px 0 0 8px;
    padding-left: 16px;
}
.kb-cat-card {
    background: #fff;
    border: 1px solid #DFE1E6;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(9,30,66,0.08);
    padding: 20px 16px;
    text-align: center;
    text-decoration: none;
    display: block;
    transition: box-shadow 0.15s, border-color 0.15s, transform 0.15s;
    height: 100%;
}
.kb-cat-card:hover { box-shadow: 0 4px 12px rgba(0,82,204,0.15); border-color: #4C9AFF; transform: translateY(-2px); }
.kb-cat-card.active { border-color: #0052CC; border-width: 2px; background: #DEEBFF; }
.kb-cat-card .cat-icon { font-size: 2rem; color: #0052CC; margin-bottom: 10px; }
.kb-cat-card.active .cat-icon { color: #0052CC; }
.kb-article-card {
    background: #fff;
    border: 1px solid #DFE1E6;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(9,30,66,0.08);
    padding: 20px 24px;
    transition: box-shadow 0.15s, border-color 0.15s;
}
.kb-article-card:hover { box-shadow: 0 4px 12px rgba(0,82,204,0.12); border-color: #4C9AFF; }
.kb-article-card h5 a:hover { color: #0052CC !important; }
.sidebar-card {
    background: #fff;
    border: 1px solid #DFE1E6;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(9,30,66,0.08);
    overflow: hidden;
}
.sidebar-card .card-header { background: #fff; border-bottom: 1px solid #DFE1E6; padding: 14px 20px; }
.sidebar-cta { background: linear-gradient(135deg,#DEEBFF 0%,#B3D4FF 100%); border: 1px solid #B3D4FF; border-radius: 10px; padding: 24px; text-align: center; }
</style>

<!-- HERO SEARCH -->
<div class="kb-search-hero">
    <span class="badge fw-bold px-3 py-1 mb-3 d-inline-block" style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);">
        <i class="bi bi-book-half me-1"></i>Trung Tâm Trợ Giúp Kỹ Thuật
    </span>
    <h2 class="fw-bold text-white mb-2">Cơ Sở Tri Thức &amp; Hướng Dẫn Tự Khắc Phục</h2>
    <p class="mb-4" style="color:rgba(255,255,255,0.7);font-size:0.95rem;">Tìm kiếm hướng dẫn xử lý nhanh sự cố máy tính, mạng WiFi, VPN, máy in và đổi mật khẩu.</p>

    <div class="search-input-group">
        <form action="<?= $baseUrl ?>/kb" method="GET">
            <div class="input-group shadow-lg" style="border-radius:8px;overflow:hidden;">
                <span class="input-group-text"><i class="bi bi-search fs-5 text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Ví dụ: Đổi mật khẩu, lỗi in, vpn, wifi..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                <button class="btn-search" type="submit">Tìm Kiếm</button>
            </div>
        </form>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
        <span style="color:rgba(255,255,255,0.6);font-size:13px;">Tìm kiếm phổ biến:</span>
        <a href="<?= $baseUrl ?>/kb?search=mật+khẩu" class="badge text-decoration-none" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);padding:5px 12px;">Đổi mật khẩu</a>
        <a href="<?= $baseUrl ?>/kb?search=vpn" class="badge text-decoration-none" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);padding:5px 12px;">Cài đặt VPN</a>
        <a href="<?= $baseUrl ?>/kb?search=máy+in" class="badge text-decoration-none" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);padding:5px 12px;">Máy in mạng</a>
        <a href="<?= $baseUrl ?>/kb?search=outlook" class="badge text-decoration-none" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);padding:5px 12px;">Lỗi Outlook</a>
    </div>
</div>

<!-- Header Actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold text-dark mb-0">
        <i class="bi bi-grid-fill me-2" style="color:#0052CC;"></i>Danh Mục Hướng Dẫn Kỹ Thuật
    </h5>
    <div class="d-flex gap-2">
        <?php if ($isStaff): ?>
            <a href="<?= $baseUrl ?>/kb/manage" class="btn btn-sm fw-semibold" style="background:#F4F5F7;color:#172B4D;border:1px solid #DFE1E6;">
                <i class="bi bi-gear-fill me-1"></i>Quản trị KB
            </a>
            <a href="<?= $baseUrl ?>/kb/create" class="btn btn-sm fw-semibold" style="background:#0052CC;color:#fff;border:none;">
                <i class="bi bi-plus-lg me-1"></i>Viết bài mới
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Categories -->
<div class="row g-3 mb-5">
    <?php foreach ($categories as $cat): ?>
        <?php $isActive = (!empty($filters['category_id']) && (int)$filters['category_id'] === (int)$cat['id']); ?>
        <div class="col-6 col-md-4 col-xl">
            <a href="<?= $baseUrl ?>/kb?category_id=<?= $cat['id'] ?>" class="kb-cat-card <?= $isActive ? 'active' : '' ?>">
                <div class="cat-icon"><i class="bi <?= htmlspecialchars($cat['icon'] ?: 'bi-book') ?>"></i></div>
                <div class="fw-bold text-dark small mb-1"><?= htmlspecialchars($cat['name']) ?></div>
                <div class="text-muted" style="font-size:12px;"><?= $cat['article_count'] ?> bài viết</div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Articles Column -->
    <div class="col-12 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-dark mb-0">
                <?php if ($selectedCategory): ?>
                    Chủ đề: <?= htmlspecialchars($selectedCategory['name']) ?> <span class="text-muted fw-normal">(<?= count($articles) ?> bài viết)</span>
                <?php elseif (!empty($filters['search'])): ?>
                    Kết quả: "<span class="text-primary"><?= htmlspecialchars($filters['search']) ?></span>" <span class="text-muted fw-normal">(<?= count($articles) ?>)</span>
                <?php else: ?>
                    Tất Cả Bài Viết Hướng Dẫn <span class="text-muted fw-normal">(<?= count($articles) ?>)</span>
                <?php endif; ?>
            </h5>
            <?php if (!empty($filters['category_id']) || !empty($filters['search'])): ?>
                <a href="<?= $baseUrl ?>/kb" class="btn btn-link btn-sm text-decoration-none text-muted">
                    <i class="bi bi-x-circle me-1"></i>Xóa bộ lọc
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($articles)): ?>
            <div class="kb-article-card text-center py-5">
                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">Chưa tìm thấy bài viết phù hợp.</h5>
                <p class="text-muted small mb-4">Không có bài viết nào khớp với từ khóa của bạn. Hãy gửi yêu cầu hỗ trợ để nhân viên IT giải quyết trực tiếp.</p>
                <a href="<?= $baseUrl ?>/tickets/create" class="btn fw-bold px-4" style="background:#0052CC;color:#fff;border:none;">
                    <i class="bi bi-plus-circle me-1"></i>Gửi Yêu Cầu Hỗ Trợ
                </a>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($articles as $art): ?>
                    <div class="kb-article-card">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge" style="background:#DEEBFF;color:#0052CC;border:1px solid #B3D4FF;font-size:12px;">
                                <i class="bi <?= htmlspecialchars($art['category_icon'] ?: 'bi-book') ?> me-1"></i><?= htmlspecialchars($art['category_name']) ?>
                            </span>
                            <div class="text-muted d-flex align-items-center gap-2" style="font-size:12px;">
                                <span><i class="bi bi-eye me-1"></i><?= (int)$art['view_count'] ?> lượt xem</span>
                                <span>·</span>
                                <span style="color:#00875A;"><i class="bi bi-hand-thumbs-up-fill me-1"></i><?= (int)$art['helpful_count'] ?> hữu ích</span>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-2">
                            <a href="<?= $baseUrl ?>/kb/<?= $art['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($art['title']) ?>
                            </a>
                        </h5>

                        <p class="text-muted small mb-3" style="line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            <?= htmlspecialchars(strip_tags($art['content'])) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="text-muted" style="font-size:12px;">
                                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($art['author_name']) ?>
                            </span>
                            <a href="<?= $baseUrl ?>/kb/<?= $art['id'] ?>" class="btn btn-sm fw-semibold" style="background:#DEEBFF;color:#0052CC;border:1px solid #B3D4FF;padding:5px 16px;">
                                Xem hướng dẫn <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Sidebar -->
    <div class="col-12 col-xl-4">
        <!-- Popular Articles -->
        <div class="sidebar-card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-fire text-danger"></i>
                <span class="fw-bold text-dark">Hướng Dẫn Được Quan Tâm Nhất</span>
            </div>
            <div class="p-3">
                <div class="list-group list-group-flush">
                    <?php foreach ($popular as $p): ?>
                        <a href="<?= $baseUrl ?>/kb/<?= $p['id'] ?>" class="list-group-item list-group-item-action border-0 px-2 py-2 d-flex align-items-start gap-2" style="border-radius:6px;">
                            <i class="bi bi-file-earmark-text mt-1" style="color:#0052CC;"></i>
                            <div>
                                <div class="fw-semibold small text-dark"><?= htmlspecialchars($p['title']) ?></div>
                                <div style="font-size:11px;color:#5E6C84;">
                                    <?= htmlspecialchars($p['category_name']) ?> · <span style="color:#00875A;font-weight:600;"><?= $p['helpful_count'] ?> đánh giá tốt</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="sidebar-cta">
            <div class="fs-1 mb-2" style="color:#0052CC;"><i class="bi bi-headset"></i></div>
            <h6 class="fw-bold text-dark mb-1">Vẫn chưa giải quyết được sự cố?</h6>
            <p class="text-muted small mb-3">Nếu bạn đã thử theo bài viết mà lỗi vẫn tiếp diễn, hãy tạo yêu cầu hỗ trợ mới để kỹ thuật viên IT tiếp nhận.</p>
            <a href="<?= $baseUrl ?>/tickets/create" class="btn fw-bold w-100 shadow-sm" style="background:#0052CC;color:#fff;border:none;">
                <i class="bi bi-plus-circle me-1"></i>Tạo Yêu Cầu Hỗ Trợ Mới
            </a>
        </div>
    </div>
</div>

<!-- AI KNOWLEDGE CHATBOT POPUP (BOTTOM-LEFT DOCKED) -->
<?php require __DIR__ . '/chatbot_popup.php'; ?>
