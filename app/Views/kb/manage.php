<div class="page-hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3" style="width: 48px; height: 48px;">
                <i class="bi bi-journal-text fs-4"></i>
            </div>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="/kb" class="text-decoration-none">Knowledge Base</a></li>
                        <li class="breadcrumb-item active fw-semibold" aria-current="page">Quản trị bài viết</li>
                    </ol>
                </nav>
                <h4 class="fw-bold text-dark mb-0">Quản Trị Cơ Sở Tri Thức (KB)</h4>
                <p class="text-muted small mb-0">Quản lý các tài liệu hướng dẫn kỹ thuật, quy trình tự xử lý sự cố cho End-User.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="/kb" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Về Cổng Tri Thức
            </a>
            <a href="/kb/create" class="btn btn-primary btn-sm d-flex align-items-center gap-1 font-weight-semibold">
                <i class="bi bi-plus-circle"></i> Soạn Thảo Bài Mới
            </a>
        </div>
    </div>
</div>

<div class="card jira-card shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Danh Sách Bài Viết Đã Xuất Bản & Nháp (<?= count($articles) ?>)</h6>
        <span class="badge badge-light border text-muted">IT Support Helpdesk Standard</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="bg-light text-muted uppercase font-weight-bold" style="font-size: 11.5px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3" style="width: 50px;">ID</th>
                        <th>Tiêu Đề Bài Viết</th>
                        <th>Chuyên Mục</th>
                        <th>Tác Giả</th>
                        <th class="text-center">Lượt Xem</th>
                        <th class="text-center">Đánh Giá Hữu Ích</th>
                        <th class="text-center">Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th class="text-end pe-3">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x display-6 d-block mb-2 text-secondary"></i>
                                Chưa có bài viết nào trong kho tri thức.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($articles as $art): ?>
                            <tr>
                                <td class="ps-3 font-monospace text-muted">#<?= $art['id'] ?></td>
                                <td>
                                    <a href="/kb/<?= $art['id'] ?>" class="text-dark font-weight-bold text-decoration-none hover-primary">
                                        <?= htmlspecialchars($art['title']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #f1f3f5; color: #495057; border: 1px solid #dee2e6;">
                                        <i class="bi <?= htmlspecialchars($art['category_icon'] ?? 'bi-folder') ?> me-1"></i>
                                        <?= htmlspecialchars($art['category_name'] ?? 'Chung') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-secondary font-weight-semibold">
                                        <?= htmlspecialchars($art['author_name'] ?? 'IT Staff') ?>
                                    </span>
                                </td>
                                <td class="text-center font-monospace">
                                    <i class="bi bi-eye text-muted me-1"></i><?= number_format($art['view_count'] ?? 0) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-hand-thumbs-up me-1"></i><?= $art['helpful_count'] ?? 0 ?>
                                    </span>
                                    <?php if (($art['unhelpful_count'] ?? 0) > 0): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1">
                                            <i class="bi bi-hand-thumbs-down me-1"></i><?= $art['unhelpful_count'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($art['status'] === 'PUBLISHED'): ?>
                                        <span class="badge bg-success px-2 py-1">Đã Xuất Bản</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">Bản Nháp</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d/m/Y', strtotime($art['created_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="/kb/<?= $art['id'] ?>" class="btn btn-outline-primary btn-sm px-2 py-1" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" action="/kb/<?= $art['id'] ?>/delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');" class="d-inline">
                                            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1" title="Xóa bài viết">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
