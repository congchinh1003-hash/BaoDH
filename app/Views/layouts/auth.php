<?php
use App\Core\Response;
use App\Core\Session;
use App\Core\Language;

/**
 * @var string $content
 * @var string|null $pageTitle
 */
$baseUrl = Response::baseUrl();
$flashes = Session::getFlashes();
$currentLang = Language::current();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Đăng nhập - TaskFlow IT Service Management' ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <link href="<?= htmlspecialchars(asset_url('css/auth-base.css')) ?>" rel="stylesheet">
<link href="<?= htmlspecialchars(asset_url('css/taskflow-ui.css')) ?>" rel="stylesheet">
<script src="<?= htmlspecialchars(asset_url('js/taskflow-ui.js')) ?>" defer></script>
</head>
<body class="tf-auth-page">

<div class="auth-split-layout">
    
    <!-- LEFT PANE: HERO SHOWCASE (Atlassian / Linear High-End SaaS) -->
    <div class="auth-hero-pane" id="heroPane">
        <!-- Three.js Interactive 3D Canvas -->

        <div class="hero-content-wrap">
            <!-- Brand Logo -->
            <a href="<?= $baseUrl ?>/login" class="hero-brand">
                <div class="hero-logo-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white" fill-opacity="0.95"/>
                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <span class="hero-brand-name">Task<span style="color: #38BDF8;">Flow</span></span>
                    <span class="hero-brand-badge">Enterprise ITSM</span>
                </div>
            </a>

            <!-- Headline & Value Prop -->
            <h1 class="hero-headline">
                Một không gian.<br>
                <span class="gradient-text">Mọi công việc IT.</span>
            </h1>
            <p class="hero-subhead">
                Kết nối yêu cầu hỗ trợ, dự án và tài sản trong một không gian rõ ràng. Để đội IT tập trung vào những việc quan trọng.
            </p>

            <!-- Interactive Showcase Cards -->
            <div class="glass-features">
                <div class="glass-feature-card">
                    <div class="glass-card-icon" style="background: rgba(12, 102, 228, 0.2); color: #38BDF8;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="glass-card-title">
                            ITSM Service Desk & SLA
                        </div>
                        <p class="glass-card-desc">Tự động hóa luồng tiếp nhận sự cố, phân bổ vé theo nhóm chuyên trách và giám sát thời gian xử lý vi phạm SLA.</p>
                    </div>
                </div>

                <div class="glass-feature-card">
                    <div class="glass-card-icon" style="background: rgba(168, 85, 247, 0.2); color: #C084FC;">
                        <i class="bi bi-kanban-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="glass-card-title">
                            Agile Scrum & Jira Backlog
                        </div>
                        <p class="glass-card-desc">Quản lý User Story, Bugs, Tasks với bảng kéo thả tương tác, biểu đồ Burndown Chart và quản lý phiên bản phát hành.</p>
                    </div>
                </div>

                <div class="glass-feature-card">
                    <div class="glass-card-icon" style="background: rgba(245, 158, 11, 0.2); color: #FCD34D;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="glass-card-title">
                            ITAM & JumpServer Bastion PAM
                        </div>
                        <p class="glass-card-desc">Quản lý vòng đời tài sản CNTT, mã QR bàn giao thiết bị và kiểm soát phiên truy cập từ xa Web SSH/RDP an toàn.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Footer -->
        <div class="hero-footer">
            <div class="hero-status-pill">
                <span class="status-dot"></span>
                <span>Không gian làm việc cho đội IT</span>
            </div>
            <div class="hero-badges-list">
                <span><i class="bi bi-check2-circle text-primary me-1"></i>ITIL v4</span>
                <span><i class="bi bi-check2-circle text-primary me-1"></i>Agile Scrum</span>
                <span><i class="bi bi-check2-circle text-primary me-1"></i>256-bit SSL</span>
            </div>
        </div>
    </div>

    <!-- RIGHT PANE: MODERN AUTH FORM -->
    <div class="auth-form-pane">
        <!-- Top Language Bar & Mobile Brand -->
        <div class="auth-form-header">
            <div class="mobile-brand">
                <div class="hero-logo-box" style="width: 36px; height: 36px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="white" fill-opacity="0.95"/>
                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="fw-bold fs-5 text-dark">Task<span style="color: #0C66E4;">Flow</span></span>
            </div>

            <div class="ms-auto dropdown">
                <button class="lang-switch-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-globe2 text-muted"></i>
                    <span><?= $currentLang === 'vi' ? 'Tiếng Việt' : 'English' ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-1 py-1">
                    <li>
                        <a class="dropdown-item py-2 small d-flex align-items-center gap-2 <?= $currentLang === 'vi' ? 'active' : '' ?>" href="<?= $baseUrl ?>/lang/vi">
                            <span>🇻🇳</span> Tiếng Việt
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 small d-flex align-items-center gap-2 <?= $currentLang === 'en' ? 'active' : '' ?>" href="<?= $baseUrl ?>/lang/en">
                            <span>🇬🇧</span> English
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Form Body Injector -->
        <div class="auth-form-body">
            <!-- Flash Alert Messages -->
            <?php if (!empty($flashes)): ?>
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show py-2 px-3 small rounded-3 mb-4 border-0 d-flex align-items-center gap-2 shadow-sm" role="alert">
                            <i class="bi <?= $type === 'danger' || $type === 'error' ? 'bi-exclamation-triangle-fill text-danger' : ($type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-info-circle-fill text-info') ?> fs-6"></i>
                            <div class="flex-grow-1 fw-medium"><?= htmlspecialchars($msg) ?></div>
                            <button type="button" class="btn-close py-2 px-3" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?= $content ?>
        </div>

        <!-- Footer Info -->
        <div class="auth-form-footer">
            <div>
                &copy; <?= date('Y') ?> TaskFlow Inc. Tất cả quyền được bảo lưu.
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-success"></i>
                <span>SSL Encrypted</span>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
