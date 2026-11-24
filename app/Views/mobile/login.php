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
$demoAccounts = demo_accounts_by_role($usersList);
$flashErrors = $flashes['danger'] ?? [];
$flashSuccess = $flashes['success'] ?? [];
$flashInfo = $flashes['info'] ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $pageTitle ?? 'Đăng Nhập - TaskFlow Mobile' ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="<?= htmlspecialchars(asset_url('css/mobile-login-base.css')) ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('css/taskflow-ui.css')) ?>" rel="stylesheet">
    <link href="<?= htmlspecialchars(asset_url('css/taskflow-mobile.css')) ?>" rel="stylesheet">
    <script src="<?= htmlspecialchars(asset_url('js/taskflow-ui.js')) ?>" defer></script>
</head>
<body class="tf-mobile-login">

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
                           placeholder="Tên đăng nhập hoặc email của bạn"
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
        <details class="quick-accounts-sec">
            <summary>Chọn tài khoản thử nghiệm</summary>
            <div class="quick-chips-grid">
                <!-- Admin -->
                <div class="quick-user-chip" onclick="selectQuickUser(<?= htmlspecialchars(json_encode($demoAccounts['ADMIN']['username'] ?? 'admin', JSON_HEX_APOS | JSON_HEX_QUOT)) ?>, '123456', 'Quản trị viên')">
                    <div class="chip-icon chip-admin"><i class="bi bi-shield-shaded"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Quản Trị Viên</div>
                        <div class="chip-username"><?= htmlspecialchars($demoAccounts['ADMIN']['username'] ?? 'admin') ?></div>
                    </div>
                </div>

                <!-- Manager -->
                <div class="quick-user-chip" onclick="selectQuickUser(<?= htmlspecialchars(json_encode($demoAccounts['IT_MANAGER']['username'] ?? 'manager', JSON_HEX_APOS | JSON_HEX_QUOT)) ?>, '123456', 'Quản lý IT')">
                    <div class="chip-icon chip-manager"><i class="bi bi-person-badge-fill"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Quản Lý IT</div>
                        <div class="chip-username"><?= htmlspecialchars($demoAccounts['IT_MANAGER']['username'] ?? 'manager') ?></div>
                    </div>
                </div>

                <!-- Agent -->
                <div class="quick-user-chip" onclick="selectQuickUser(<?= htmlspecialchars(json_encode($demoAccounts['IT_STAFF']['username'] ?? 'it_helpdesk', JSON_HEX_APOS | JSON_HEX_QUOT)) ?>, '123456', 'Kỹ thuật viên')">
                    <div class="chip-icon chip-agent"><i class="bi bi-tools"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Kỹ Thuật Viên</div>
                        <div class="chip-username"><?= htmlspecialchars($demoAccounts['IT_STAFF']['username'] ?? 'it_helpdesk') ?></div>
                    </div>
                </div>

                <!-- End User -->
                <div class="quick-user-chip" onclick="selectQuickUser(<?= htmlspecialchars(json_encode($demoAccounts['USER']['username'] ?? 'user1', JSON_HEX_APOS | JSON_HEX_QUOT)) ?>, '123456', 'Nhân viên')">
                    <div class="chip-icon chip-user"><i class="bi bi-person-fill"></i></div>
                    <div class="chip-info">
                        <div class="chip-role-name">Nhân Viên</div>
                        <div class="chip-username"><?= htmlspecialchars($demoAccounts['USER']['username'] ?? 'user1') ?></div>
                    </div>
                </div>
            </div>
        </details>

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
