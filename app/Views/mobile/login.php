<?php
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $allUsers
 * @var array $flashes
 * @var string|null $pageTitle
 */
$baseUrl = Response::baseUrl();
$usersList = $allUsers ?? [];
$flashErrors = $flashes['danger'] ?? [];
$flashSuccess = $flashes['success'] ?? [];
$flashInfo = $flashes['info'] ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= $pageTitle ?? 'Đăng Nhập - TaskFlow Mobile' ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0C66E4;
            --primary-hover: #0052CC;
            --primary-soft: #EBF3FF;
            --text-dark: #0F172A;
            --text-sub: #64748B;
            --border-soft: #E2E8F0;
            --card-bg: #FFFFFF;
            --body-bg: #F8FAFC;
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: var(--font-main);
            background-color: var(--body-bg);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        .mobile-login-wrapper {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: max(16px, env(safe-area-inset-top)) 20px max(24px, env(safe-area-inset-bottom));
            position: relative;
        }

        /* Ambient Header Glow */
        .ambient-glow {
            position: absolute;
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: 380px;
            height: 300px;
            background: radial-gradient(circle, rgba(12, 102, 228, 0.16) 0%, rgba(99, 102, 241, 0.08) 50%, transparent 70%);
            border-radius: 50%;
            filter: blur(50px);
            pointer-events: none;
            z-index: 0;
        }

        /* App Branding */
        .brand-section {
            position: relative;
            z-index: 1;
            text-align: center;
            padding-top: 18px;
            padding-bottom: 14px;
        }

        .brand-icon-box {
            width: 68px;
            height: 68px;
            margin: 0 auto 12px;
            background: linear-gradient(135deg, #0C66E4 0%, #3B82F6 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 32px;
            box-shadow: 0 10px 24px rgba(12, 102, 228, 0.28), 0 2px 6px rgba(12, 102, 228, 0.15);
            transform: rotate(-1deg);
        }

        .app-name {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-dark);
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .app-tag {
            font-size: 0.65rem;
            background: #EBF3FF;
            color: var(--primary);
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
            border: 1px solid #D0E2FF;
        }

        .app-subheading {
            font-size: 0.82rem;
            color: var(--text-sub);
            margin-bottom: 0;
        }

        /* Mobile Form Card */
        .mobile-login-card {
            position: relative;
            z-index: 1;
            background: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: 22px;
            padding: 22px 20px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02);
            margin: 8px 0;
        }

        .login-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .login-card-desc {
            font-size: 0.8rem;
            color: var(--text-sub);
            margin-bottom: 16px;
        }

        /* Input Controls */
        .input-group-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-input-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .mobile-input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 0.15s ease;
        }

        .mobile-input {
            width: 100%;
            height: 48px;
            padding: 10px 14px 10px 42px;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--text-dark);
            outline: none;
            transition: all 0.2s ease;
        }

        .mobile-input:focus {
            background: #FFFFFF;
            border-color: var(--primary);
            box-shadow: 0 0 0 3.5px rgba(12, 102, 228, 0.12);
        }

        .mobile-input:focus + .input-icon,
        .mobile-input-wrap:focus-within .input-icon {
            color: var(--primary);
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94A3B8;
            padding: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            outline: none;
        }

        .toggle-password-btn:active {
            color: var(--primary);
        }

        /* Remember & Pass Helper */
        .form-row-extra {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            margin-bottom: 18px;
            margin-top: 4px;
        }

        .pass-chip {
            background: #F1F5F9;
            color: #475569;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid #E2E8F0;
            text-decoration: none;
        }

        .pass-chip:active {
            background: #E2E8F0;
        }

        /* Primary Submit Button */
        .btn-mobile-login {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #0C66E4 0%, #2563EB 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 999px;
            font-size: 0.94rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(12, 102, 228, 0.28);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-mobile-login:active {
            transform: scale(0.97);
            box-shadow: 0 2px 6px rgba(12, 102, 228, 0.2);
        }

        /* Quick Account Chips */
        .quick-accounts-sec {
            position: relative;
            z-index: 1;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .quick-sec-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748B;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quick-chips-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .quick-user-chip {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: left;
        }

        .quick-user-chip:active {
            transform: scale(0.96);
            background: #F1F5F9;
        }

        .chip-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .chip-admin { background: #FEF3C7; color: #D97706; }
        .chip-manager { background: #E0E7FF; color: #4338CA; }
        .chip-agent { background: #DCFCE7; color: #15803D; }
        .chip-user { background: #F1F5F9; color: #475569; }

        .chip-info {
            overflow: hidden;
        }

        .chip-role-name {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chip-username {
            font-size: 0.7rem;
            color: #64748B;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1.2;
        }

        /* Footer */
        .mobile-footer {
            position: relative;
            z-index: 1;
            text-align: center;
            padding-top: 10px;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            color: #64748B;
            margin-bottom: 8px;
        }

        .desktop-link {
            font-size: 0.78rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .desktop-link:hover {
            text-decoration: underline;
        }

        /* Toast notification when quick role chosen */
        .quick-fill-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: #1E293B;
            color: #FFFFFF;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 9999;
            pointer-events: none;
        }

        .quick-fill-toast.show {
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

    <div class="mobile-login-wrapper">
        <div class="ambient-glow"></div>

        <!-- App Brand Header -->
        <div class="brand-section">
            <div class="brand-icon-box">
                <i class="bi bi-layers-fill"></i>
            </div>
            <div class="app-name">
                TaskFlow <span class="app-tag">App</span>
            </div>
            <p class="app-subheading">Hệ Thống Quản Lý Sự Cố & Thiết Bị IT</p>
        </div>

        <!-- Login Form Card -->
        <div class="mobile-login-card">
            <div class="login-card-title">Đăng Nhập</div>
            <div class="login-card-desc">Vui lòng nhập tài khoản để vào không gian làm việc.</div>

            <!-- Flash Error Message -->
            <?php if (!empty($flashErrors)): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-0 shadow-sm" style="font-size:0.8rem;">
                    <i class="bi bi-exclamation-circle-fill text-danger flex-shrink-0"></i>
                    <span><?= htmlspecialchars(implode(', ', $flashErrors)) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2 border-0 shadow-sm" style="font-size:0.8rem;">
                    <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                    <span><?= htmlspecialchars(implode(', ', $flashSuccess)) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= $baseUrl ?>/login" method="POST" id="mobileLoginForm">
                <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="is_mobile" value="1">

                <!-- Account -->
                <div class="input-group-label">
                    <span>Tên đăng nhập / Email</span>
                </div>
                <div class="mobile-input-wrap">
                    <input type="text" 
                           id="accountInput" 
                           name="account" 
                           class="mobile-input" 
                           placeholder="admin, manager, user..." 
                           required 
                           autocomplete="username"
                           autofocus>
                    <i class="bi bi-person-fill input-icon"></i>
                </div>

                <!-- Password -->
                <div class="input-group-label">
                    <span>Mật khẩu</span>
                    <span class="pass-chip" onclick="fillDefaultPassword()" title="Điền nhanh 123456">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>123456
                    </span>
                </div>
                <div class="mobile-input-wrap">
                    <input type="password" 
                           id="passwordInput" 
                           name="password" 
                           class="mobile-input" 
                           placeholder="••••••••" 
                           required 
                           autocomplete="current-password">
                    <i class="bi bi-shield-lock-fill input-icon"></i>
                    <button type="button" class="toggle-password-btn" id="togglePasswordBtn" onclick="togglePasswordVisibility()">
                        <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
                    </button>
                </div>

                <!-- Remember Me Row -->
                <div class="form-row-extra">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="rememberMeMobile" name="remember" checked style="cursor: pointer;">
                        <label class="form-check-label text-muted user-select-none" for="rememberMeMobile" style="cursor: pointer;">
                            Ghi nhớ tài khoản
                        </label>
                    </div>
                    <span class="text-muted" style="font-size:0.75rem;">
                        <i class="bi bi-shield-check text-success me-1"></i>SSL 256-bit
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-mobile-login" id="btnLoginSubmit">
                    <span>Đăng Nhập</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </button>
            </form>
        </div>

        <!-- Quick Demo Account Selector (Pills) -->
        <div class="quick-accounts-sec">
            <div class="quick-sec-title">
                <i class="bi bi-lightning-fill text-warning"></i> Chọn nhanh vai trò thử nghiệm:
            </div>
            <div class="quick-chips-grid">
                <!-- Admin -->
                <div class="quick-user-chip" onclick="selectQuickUser('admin', '123456', 'Quản trị viên')">
                    <div class="chip-icon chip-admin"><i class="bi bi-shield-shaded"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Quản Trị Viên</div>
                        <div class="chip-username">admin</div>
                    </div>
                </div>

                <!-- Manager -->
                <div class="quick-user-chip" onclick="selectQuickUser('manager', '123456', 'Quản lý IT')">
                    <div class="chip-icon chip-manager"><i class="bi bi-person-badge-fill"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Quản Lý IT</div>
                        <div class="chip-username">manager</div>
                    </div>
                </div>

                <!-- Agent -->
                <div class="quick-user-chip" onclick="selectQuickUser('agent', '123456', 'Kỹ thuật viên')">
                    <div class="chip-icon chip-agent"><i class="bi bi-tools"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Kỹ Thuật Viên</div>
                        <div class="chip-username">agent</div>
                    </div>
                </div>

                <!-- End User -->
                <div class="quick-user-chip" onclick="selectQuickUser('user', '123456', 'Nhân viên')">
                    <div class="chip-icon chip-user"><i class="bi bi-person-fill"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Nhân Viên</div>
                        <div class="chip-username">user</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mobile-footer">
            <div class="security-badge">
                <i class="bi bi-lock-fill text-success"></i> Kết nối an toàn · TaskFlow Mobile 2026
            </div>
            <div>
                <a href="<?= $baseUrl ?>/login?view=desktop" class="desktop-link">
                    <i class="bi bi-display"></i> Chuyển sang giao diện máy tính
                </a>
                <span class="text-muted mx-2">·</span>
                <a href="<?= $baseUrl ?>/simulator" class="desktop-link text-success">
                    <i class="bi bi-phone"></i> Giả lập thiết bị
                </a>
            </div>
        </div>
    </div>

    <!-- Feedback Toast -->
    <div class="quick-fill-toast" id="quickToast">
        <i class="bi bi-check-circle-fill text-success"></i>
        <span id="toastMessage">Đã chọn tài khoản!</span>
    </div>

    <script>
        function fillDefaultPassword() {
            const passInput = document.getElementById('passwordInput');
            if (passInput) {
                passInput.value = '123456';
                showToast('Đã điền mật khẩu mặc định: 123456');
            }
        }

        function togglePasswordVisibility() {
            const passInput = document.getElementById('passwordInput');
            const icon = document.getElementById('togglePasswordIcon');
            if (!passInput || !icon) return;

            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                passInput.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        }

        function selectQuickUser(username, password, label) {
            const accInput = document.getElementById('accountInput');
            const passInput = document.getElementById('passwordInput');
            if (accInput && passInput) {
                accInput.value = username;
                passInput.value = password;
                showToast(`Đã chọn: ${label} (${username})`);
            }
        }

        let toastTimer = null;
        function showToast(text) {
            const toast = document.getElementById('quickToast');
            const msg = document.getElementById('toastMessage');
            if (!toast || !msg) return;

            msg.textContent = text;
            toast.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        }

        // Prevent accidental multiple form submits
        document.getElementById('mobileLoginForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('btnLoginSubmit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang đăng nhập...';
            }
        });
    </script>
</body>
</html>