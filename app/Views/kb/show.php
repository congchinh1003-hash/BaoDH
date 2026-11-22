<?php
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
?>

<!-- BREADCRUMB -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/kb" class="text-decoration-none">Cơ sở tri thức</a></li>
            <li class="breadcrumb-item"><a href="<?= $baseUrl ?>/kb?category_id=<?= $article['category_id'] ?>" class="text-decoration-none"><?= htmlspecialchars($article['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page"><?= htmlspecialchars($article['title']) ?></li>
        </ol>
    </nav>
    <a href="<?= $baseUrl ?>/kb" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Về danh sách hướng dẫn
    </a>
</div>

<div class="row g-4">
    <!-- CỘT TRÁI: BÀI VIẾT NỘI DUNG -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm bg-white p-4 p-md-5">
            <!-- ARTICLE HEADER -->
            <div class="border-bottom pb-3 mb-4">
                <span class="badge bg-light text-primary border small mb-2">
                    <i class="bi <?= htmlspecialchars($article['category_icon'] ?: 'bi-book') ?> me-1"></i><?= htmlspecialchars($article['category_name']) ?>
                </span>
                <h3 class="fw-bold text-dark mb-3"><?= htmlspecialchars($article['title']) ?></h3>
                
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                    <div><i class="bi bi-person-fill text-secondary me-1"></i>Tác giả: <strong><?= htmlspecialchars($article['author_name']) ?></strong></div>
                    <div>•</div>
                    <div><i class="bi bi-calendar3 text-secondary me-1"></i>Ngày đăng: <?= date('d/m/Y', strtotime($article['created_at'])) ?></div>
                    <div>•</div>
                    <div><i class="bi bi-eye text-secondary me-1"></i><?= (int)$article['view_count'] ?> lượt xem</div>
                    <div>•</div>
                    <div class="text-success"><i class="bi bi-hand-thumbs-up-fill me-1"></i><?= (int)$article['helpful_count'] ?> người thấy hữu ích</div>
                </div>
            </div>

            <!-- ARTICLE BODY -->
            <div class="article-content text-dark mb-5" style="line-height: 1.8; font-size: 1rem; white-space: pre-line;">
                <?= htmlspecialchars($article['content']) ?>
            </div>

            <!-- HELPFUL VOTE WIDGET -->
            <div class="card bg-light border-0 p-4 text-center rounded-3">
                <h6 class="fw-bold text-dark mb-2">Hướng dẫn này có giải quyết được sự cố của bạn không?</h6>
                <p class="text-muted small mb-3">Đánh giá của bạn giúp chúng tôi cải thiện chất lượng các tài liệu kỹ thuật.</p>
                <div class="d-flex justify-content-center gap-3">
                    <form action="<?= $baseUrl ?>/kb/<?= $article['id'] ?>/vote" method="POST">
                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="is_helpful" value="1">
                        <button type="submit" class="btn btn-outline-success fw-bold px-3">
                            <i class="bi bi-hand-thumbs-up me-1"></i>Có, rất hữu ích (<?= (int)$article['helpful_count'] ?>)
                        </button>
                    </form>
                    <form action="<?= $baseUrl ?>/kb/<?= $article['id'] ?>/vote" method="POST">
                        <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="is_helpful" value="0">
                        <button type="submit" class="btn btn-outline-secondary px-3">
                            <i class="bi bi-hand-thumbs-down me-1"></i>Chưa hữu ích
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: BÀI VIẾT LIÊN QUAN & SUPPORT TICKET -->
    <div class="col-12 col-xl-4">
        <!-- RELATED ARTICLES -->
        <div class="card border-0 shadow-sm bg-white mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="fw-bold text-dark"><i class="bi bi-link-45deg text-primary me-2"></i>Hướng Dẫn Cùng Chủ Đề</span>
            </div>
            <div class="card-body p-3">
                <div class="list-group list-group-flush">
                    <?php foreach ($relatedArticles as $rel): ?>
                        <?php if ((int)$rel['id'] !== (int)$article['id']): ?>
                            <a href="<?= $baseUrl ?>/kb/<?= $rel['id'] ?>" class="list-group-item list-group-item-action border-0 px-2 py-2 d-flex align-items-start gap-2">
                                <i class="bi bi-file-earmark-text text-primary mt-1"></i>
                                <div class="flex-1">
                                    <div class="fw-semibold small text-dark"><?= htmlspecialchars($rel['title']) ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;"><?= (int)$rel['view_count'] ?> lượt xem</div>
                                </div>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- CALL TO ACTION TICKET -->
        <div class="card border-0 shadow-sm bg-light p-4 text-center">
            <div class="fs-1 text-primary mb-2"><i class="bi bi-tools"></i></div>
            <h6 class="fw-bold text-dark mb-1">Vẫn gặp trục trặc kỹ thuật?</h6>
            <p class="text-muted small mb-3">Nếu bạn đã thử theo hướng dẫn nhưng không thành công, hãy gửi ticket để kỹ thuật viên IT trực tiếp hỗ trợ.</p>
            <a href="<?= $baseUrl ?>/tickets/create" class="btn btn-primary fw-bold w-100 shadow-sm">
                <i class="bi bi-plus-circle me-1"></i>Gửi Yêu Cầu Cho Kỹ Thuật Viên
            </a>
        </div>
    </div>
</div>

<!-- AI KNOWLEDGE CHATBOT POPUP (BOTTOM-LEFT DOCKED) -->
<?php require __DIR__ . '/chatbot_popup.php'; ?>

