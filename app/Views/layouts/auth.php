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
    
    <style>
        :root {
            --tf-primary: #0C66E4;
            --tf-primary-hover: #0052CC;
            --tf-primary-light: #E9F2FF;
            --tf-dark: #091E42;
            --tf-navy-bg: #071328;
            --tf-card-border: rgba(226, 232, 240, 0.8);
            --font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: #F8FAFC;
            color: #1E293B;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Full Screen Split Layout */
        .auth-split-layout {
            display: flex;
            min-height: 100vh;
            width: 100vw;
        }

        /* Left Hero Showcase */
        .auth-hero-pane {
            flex: 0 0 52%;
            background: var(--tf-navy-bg);
            background-image: 
                radial-gradient(circle at 12% 18%, rgba(12, 102, 228, 0.45) 0%, transparent 42%),
                radial-gradient(circle at 88% 82%, rgba(99, 102, 241, 0.38) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.15) 0%, transparent 60%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 56px 64px;
            color: #FFFFFF;
        }

        /* Subtle grid texture overlay */
        .auth-hero-pane::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
            opacity: 0.8;
        }

        /* Ambient floating light orbs */
        .hero-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 1;
        }
        .hero-orb-1 {
            width: 380px;
            height: 380px;
            background: rgba(12, 102, 228, 0.25);
            top: -60px;
            left: -60px;
        }
        .hero-orb-2 {
            width: 320px;
            height: 320px;
            background: rgba(139, 92, 246, 0.22);
            bottom: 40px;
            right: -40px;
        }

        .hero-content-wrap {
            position: relative;
            z-index: 2;
        }

        /* Brand Header */
        .hero-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .hero-logo-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0C66E4 0%, #38BDF8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(12, 102, 228, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .hero-brand-name {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #FFFFFF;
        }
        .hero-brand-badge {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(12, 102, 228, 0.25);
            color: #38BDF8;
            border: 1px solid rgba(56, 189, 248, 0.35);
            margin-left: 6px;
        }

        /* Hero Titles */
        .hero-headline {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.8px;
            color: #FFFFFF;
            margin-top: 48px;
            margin-bottom: 16px;
        }
        .hero-headline span.gradient-text {
            background: linear-gradient(135deg, #38BDF8 0%, #818CF8 50%, #C084FC 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subhead {
            font-size: 15.5px;
            line-height: 1.6;
            color: rgba(226, 232, 240, 0.82);
            max-width: 520px;
            margin-bottom: 40px;
        }

        /* Glass Feature Showcase Cards */
        .glass-features {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 540px;
        }
        .glass-feature-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .glass-feature-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(56, 189, 248, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.25);
        }
        .glass-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .glass-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .glass-card-desc {
            font-size: 12.5px;
            color: rgba(226, 232, 240, 0.72);
            margin: 0;
            line-height: 1.45;
        }

        /* Hero Footer */
        .hero-footer {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .hero-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #34D399;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 10px #10B981;
            animation: pulse-dot 2s infinite ease-in-out;
        }
        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.65; }
        }
        .hero-badges-list {
            display: flex;
            align-items: center;
            gap: 16px;
            color: rgba(226, 232, 240, 0.65);
            font-size: 12px;
            font-weight: 500;
        }

        /* Right Form Pane */
        .auth-form-pane {
            flex: 0 0 48%;
            background: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            overflow-y: auto;
            position: relative;
        }

        .auth-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .mobile-brand {
            display: none;
            align-items: center;
            gap: 10px;
        }

        .auth-form-body {
            max-width: 440px;
            width: 100%;
            margin: auto;
            padding: 16px 0;
        }

        .form-heading {
            font-size: 26px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }
        .form-subheading {
            font-size: 14px;
            color: #64748B;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        /* Form Inputs */
        .tf-input-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tf-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        .tf-input-icon {
            position: absolute;
            left: 14px;
            color: #94A3B8;
            font-size: 16px;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 4;
        }
        .tf-form-input {
            width: 100%;
            height: 46px;
            padding: 10px 14px 10px 42px;
            font-size: 14px;
            font-weight: 500;
            color: #0F172A;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            outline: none;
        }
        .tf-form-input:focus {
            background: #FFFFFF;
            border-color: var(--tf-primary);
            box-shadow: 0 0 0 4px rgba(12, 102, 228, 0.12);
        }
        .tf-input-group:focus-within .tf-input-icon {
            color: var(--tf-primary);
        }

        /* Password Toggle */
        .password-toggle-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94A3B8;
            padding: 6px 10px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: color 0.15s ease;
            z-index: 4;
        }
        .password-toggle-btn:hover {
            color: #0F172A;
        }

        /* Submit Button */
        .tf-submit-btn {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #0C66E4 0%, #0052CC 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.2px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(12, 102, 228, 0.32);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .tf-submit-btn:hover {
            background: linear-gradient(135deg, #0052CC 0%, #0747A6 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(12, 102, 228, 0.42);
            color: #FFFFFF;
        }
        .tf-submit-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(12, 102, 228, 0.25);
        }

        /* Quick Roles Grid */
        .quick-roles-wrap {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #E2E8F0;
        }
        .quick-roles-title {
            font-size: 12.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748B;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .quick-role-card {
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 12px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            width: 100%;
        }
        .quick-role-card:hover {
            border-color: #0C66E4;
            background: #F8FAFC;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(12, 102, 228, 0.08);
        }
        .quick-role-card.active-selected {
            border-color: #0C66E4;
            background: #EBF3FF;
            box-shadow: 0 0 0 3px rgba(12, 102, 228, 0.15);
        }
        .role-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            color: #FFFFFF;
        }
        .role-info-title {
            font-size: 13px;
            font-weight: 700;
            color: #0F172A;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .role-info-desc {
            font-size: 11px;
            color: #64748B;
            line-height: 1.2;
        }

        /* Language Switcher Dropdown */
        .lang-switch-btn {
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .lang-switch-btn:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        /* Footer */
        .auth-form-footer {
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #94A3B8;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991.98px) {
            .auth-split-layout {
                flex-direction: column;
            }
            .auth-hero-pane {
                flex: none;
                padding: 32px 24px;
            }
            .hero-headline {
                font-size: 24px;
                margin-top: 20px;
                margin-bottom: 8px;
            }
            .hero-subhead {
                font-size: 14px;
                margin-bottom: 20px;
            }
            .glass-features, .hero-footer {
                display: none;
            }
            .auth-form-pane {
                flex: 1 1 auto;
                padding: 32px 24px;
            }
            .mobile-brand {
                display: flex;
            }
        }
    </style>
</head>
<body>

<div class="auth-split-layout">
    
    <!-- LEFT PANE: HERO SHOWCASE (Atlassian / Linear High-End SaaS) -->
    <div class="auth-hero-pane" id="heroPane">
        <!-- Three.js Interactive 3D Canvas -->
        <canvas id="three-hero-canvas" style="position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none;"></canvas>

        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>

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
                Nền tảng Vận hành CNTT<br>
                <span class="gradient-text">& Quản trị Dự án Agile</span>
            </h1>
            <p class="hero-subhead">
                Chuẩn hóa quy trình dịch vụ ITIL v4, tối ưu năng suất làm việc nhóm với bảng Kanban Jira và bảo mật hạ tầng JumpServer Bastion.
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
                            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34D399; font-size: 10px; font-weight: 700;">SLA 99.8%</span>
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
                            <span class="badge" style="background: rgba(168, 85, 247, 0.2); color: #E9D5FF; font-size: 10px; font-weight: 700;">Sprint 14 Active</span>
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
                            <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #FDE68A; font-size: 10px; font-weight: 700;">Zero Trust</span>
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
                <span>Hệ thống hoạt động bình thường</span>
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

<!-- Three.js 3D Visual Experience -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('three-hero-canvas');
    const heroPane = document.getElementById('heroPane');
    if (!canvas || !heroPane || typeof THREE === 'undefined') return;

    let width = heroPane.clientWidth;
    let height = heroPane.clientHeight;

    // 1. Scene, Camera, Renderer
    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x071328, 0.0016);

    const camera = new THREE.PerspectiveCamera(60, width / height, 1, 3000);
    camera.position.set(0, 240, 500);
    camera.lookAt(0, 0, 0);

    const renderer = new THREE.WebGLRenderer({
        canvas: canvas,
        alpha: true,
        antialias: true,
        powerPreference: 'high-performance'
    });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    // 2. Interactive Cyber Wave Matrix (Particles)
    const SEPARATION = 36;
    const AMOUNTX = 42;
    const AMOUNTY = 42;
    const numParticles = AMOUNTX * AMOUNTY;

    const positions = new Float32Array(numParticles * 3);
    const scales = new Float32Array(numParticles);
    const colors = new Float32Array(numParticles * 3);

    const color1 = new THREE.Color(0x0C66E4); // Primary Blue
    const color2 = new THREE.Color(0x38BDF8); // Cyan Accent
    const color3 = new THREE.Color(0x818CF8); // Violet Accent

    let i = 0, j = 0;
    for (let ix = 0; ix < AMOUNTX; ix++) {
        for (let iy = 0; iy < AMOUNTY; iy++) {
            positions[i] = ix * SEPARATION - ((AMOUNTX * SEPARATION) / 2); // x
            positions[i + 1] = 0; // y
            positions[i + 2] = iy * SEPARATION - ((AMOUNTY * SEPARATION) / 2); // z

            // Gradient color based on position
            const ratio = (ix + iy) / (AMOUNTX + AMOUNTY);
            let pColor = color1.clone().lerp(color2, ratio);
            if (ratio > 0.6) {
                pColor.lerp(color3, (ratio - 0.6) * 2.5);
            }
            colors[i] = pColor.r;
            colors[i + 1] = pColor.g;
            colors[i + 2] = pColor.b;

            scales[j] = 1;
            i += 3;
            j++;
        }
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    // Create custom smooth circle particle texture
    const particleCanvas = document.createElement('canvas');
    particleCanvas.width = 32;
    particleCanvas.height = 32;
    const pCtx = particleCanvas.getContext('2d');
    const grad = pCtx.createRadialGradient(16, 16, 0, 16, 16, 16);
    grad.addColorStop(0, 'rgba(255,255,255,1)');
    grad.addColorStop(0.3, 'rgba(56,189,248,0.9)');
    grad.addColorStop(0.7, 'rgba(12,102,228,0.3)');
    grad.addColorStop(1, 'rgba(0,0,0,0)');
    pCtx.fillStyle = grad;
    pCtx.beginPath();
    pCtx.arc(16, 16, 16, 0, Math.PI * 2);
    pCtx.fill();

    const particleTexture = new THREE.CanvasTexture(particleCanvas);

    const material = new THREE.PointsMaterial({
        size: 5.5,
        map: particleTexture,
        transparent: true,
        vertexColors: true,
        blending: THREE.AdditiveBlending,
        depthWrite: false
    });

    const particles = new THREE.Points(geometry, material);
    particles.position.y = -60;
    scene.add(particles);

    // 3. Floating 3D Geometric Wireframe Nodes (Servers / Microservices)
    const floatingNodes = [];

    function createWireframeNode(geom, colorHex, posX, posY, posZ, rotSpeedX, rotSpeedY) {
        const wireMat = new THREE.MeshBasicMaterial({
            color: colorHex,
            wireframe: true,
            transparent: true,
            opacity: 0.4
        });
        const mesh = new THREE.Mesh(geom, wireMat);
        mesh.position.set(posX, posY, posZ);

        // Inner glowing core
        const coreMat = new THREE.MeshBasicMaterial({
            color: 0xffffff,
            transparent: true,
            opacity: 0.15
        });
        const coreMesh = new THREE.Mesh(geom, coreMat);
        coreMesh.scale.set(0.75, 0.75, 0.75);
        mesh.add(coreMesh);

        scene.add(mesh);
        floatingNodes.push({ mesh, rotSpeedX, rotSpeedY, baseY: posY, speed: Math.random() * 0.002 + 0.001 });
    }

    createWireframeNode(new THREE.IcosahedronGeometry(42, 1), 0x38BDF8, 220, 120, -100, 0.008, 0.012);
    createWireframeNode(new THREE.OctahedronGeometry(36, 0), 0x818CF8, -200, 80, 80, -0.01, 0.007);
    createWireframeNode(new THREE.TorusGeometry(32, 8, 12, 24), 0x0C66E4, 180, -40, 140, 0.006, -0.009);

    // 4. Mouse Interactive Parallax
    let mouseX = 0, mouseY = 0;
    let targetX = 0, targetY = 0;

    window.addEventListener('mousemove', function(e) {
        mouseX = (e.clientX - (window.innerWidth / 2)) * 0.25;
        mouseY = (e.clientY - (window.innerHeight / 2)) * 0.25;
    });

    // 5. Responsive Resize
    function onWindowResize() {
        if (!heroPane) return;
        width = heroPane.clientWidth;
        height = heroPane.clientHeight;
        if (width === 0 || height === 0) return;

        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    }
    window.addEventListener('resize', onWindowResize);

    // 6. Animation Loop
    let count = 0;
    function animate() {
        requestAnimationFrame(animate);

        count += 0.035;

        // Animate particles wave
        const positionAttr = geometry.attributes.position;
        const posArray = positionAttr.array;

        let index = 0;
        for (let ix = 0; ix < AMOUNTX; ix++) {
            for (let iy = 0; iy < AMOUNTY; iy++) {
                // Wave formula: combination of 2 sines + diagonal motion
                posArray[index + 1] = (Math.sin((ix + count) * 0.35) * 32) +
                                      (Math.sin((iy + count) * 0.45) * 32);
                index += 3;
            }
        }
        positionAttr.needsUpdate = true;

        // Animate floating nodes
        floatingNodes.forEach(node => {
            node.mesh.rotation.x += node.rotSpeedX;
            node.mesh.rotation.y += node.rotSpeedY;
            node.mesh.position.y = node.baseY + Math.sin(count * 0.8 + node.baseY) * 14;
        });

        // Smooth camera damping toward mouse
        targetX = mouseX;
        targetY = -mouseY;
        camera.position.x += (targetX - camera.position.x) * 0.04;
        camera.position.y += (240 + targetY * 0.5 - camera.position.y) * 0.04;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }

    animate();
});
</script>

</body>
</html>
