<?php
use App\Core\Response;
use App\Core\Session;

/**
 * @var array $allUsers
 */
$baseUrl = Response::baseUrl();
$usersList = $allUsers ?? [];
?>

<!-- Title and subtitle -->
<div class="mb-4">
    <h2 class="form-heading"><?= __('auth.login_title') ?></h2>
    <p class="form-subheading">
        <?= \App\Core\Language::current() === 'vi' 
            ? 'Vui lòng nhập tài khoản và mật khẩu của bạn để truy cập hệ thống.' 
            : 'Please enter your credentials to access your organization workspace.' ?>
    </p>
</div>

<!-- Selected Account Notification Badge (Flashes when selected from modal) -->
<div id="selectedAccountAlert" class="alert alert-success py-2 px-3 small rounded-3 mb-3 border-0 d-none align-items-center justify-content-between shadow-sm" role="alert">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill text-success fs-6"></i>
        <span>Đã chọn: <strong id="selectedAccountName"></strong> (<code id="selectedAccountUser" class="text-success font-monospace"></code>)</span>
    </div>
    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">Mật khẩu: 123456</span>
</div>

<!-- Login Form -->
<form action="<?= $baseUrl ?>/login" method="POST" id="loginForm">
    <input type="hidden" name="_csrf_token" value="<?= Session::csrfToken() ?>">
    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

    <!-- Account / Email Field -->
    <div class="mb-3">
        <label for="account" class="tf-input-label">
            <span><i class="bi bi-person-circle me-1 text-primary"></i> <?= __('auth.account') ?></span>
        </label>
        <div class="tf-input-group">
            <i class="bi bi-person-fill tf-input-icon"></i>
            <input type="text" 
                   class="tf-form-input" 
                   id="account" 
                   name="account" 
                   placeholder="<?= \App\Core\Language::current() === 'vi' ? 'Nhập username hoặc email (admin, manager...)' : 'Enter username or email' ?>" 
                   required 
                   autofocus
                   autocomplete="username">
        </div>
    </div>

    <!-- Password Field -->
    <div class="mb-3">
        <div class="tf-input-label">
            <span><i class="bi bi-key-fill me-1 text-primary"></i> <?= __('auth.password') ?></span>
            <span class="badge bg-light text-muted border py-1 px-2" style="font-size: 11px; cursor: pointer;" onclick="document.getElementById('password').value='123456'" title="Click để điền nhanh">
                <i class="bi bi-lightning-charge-fill text-warning"></i> <?= __('auth.default_pass') ?>: <strong>123456</strong>
            </span>
        </div>
        <div class="tf-input-group">
            <i class="bi bi-lock-fill tf-input-icon"></i>
            <input type="password" 
                   class="tf-form-input pe-5" 
                   id="password" 
                   name="password" 
                   placeholder="••••••••" 
                   required
                   autocomplete="current-password">
            <button type="button" class="password-toggle-btn" id="togglePasswordBtn" title="Hiện / Ẩn mật khẩu">
                <i class="bi bi-eye-fill" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <!-- Remember Me & Security Status -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" checked style="cursor: pointer;">
            <label class="form-check-label text-muted small user-select-none" for="rememberMe" style="cursor: pointer; font-size: 13px;">
                <?= \App\Core\Language::current() === 'vi' ? 'Ghi nhớ đăng nhập' : 'Remember me' ?>
            </label>
        </div>
        <span class="text-muted small" style="font-size: 12px;">
            <i class="bi bi-shield-check text-primary me-1"></i><?= \App\Core\Language::current() === 'vi' ? 'Không gian làm việc của bạn' : 'Your workspace' ?>
        </span>
    </div>

    <!-- Main Submit CTA Button -->
    <button type="submit" class="tf-submit-btn" id="submitLoginBtn">
        <span><?= __('auth.login_btn') ?></span>
        <i class="bi bi-arrow-right-short fs-4"></i>
    </button>
</form>

<!-- Mobile & Device Simulator Quick Links -->
<div class="d-flex align-items-center justify-content-between px-3 py-2 mt-3 mb-2 bg-light rounded-3 border" style="font-size: 0.82rem;">
    <a href="<?= $baseUrl ?>/mobile/login" class="text-decoration-none fw-bold text-primary d-flex align-items-center gap-1">
        <i class="bi bi-phone"></i> Giao diện Mobile
    </a>
    <span class="text-muted">·</span>
    <a href="<?= $baseUrl ?>/simulator" class="text-decoration-none fw-bold text-success d-flex align-items-center gap-1" target="_blank">
        <i class="bi bi-display"></i> Giả lập thiết bị (Studio) <i class="bi bi-arrow-up-right small"></i>
    </a>
</div>

<!-- Quick Role Demo Selector (Interactive 2x2 Grid) -->
<details class="tf-demo-access">
<summary>Chọn tài khoản thử nghiệm</summary>
<div class="quick-roles-wrap">
    <div class="quick-roles-title">
        <span><i class="bi bi-lightning-fill text-warning me-1"></i> <?= __('auth.quick_login') ?></span>
        <span class="badge bg-light text-muted border font-monospace" style="font-size: 10px; font-weight: normal;">PASS: 123456</span>
    </div>

    <div class="row g-2">
        <!-- 1. ADMIN -->
        <div class="col-6">
            <div class="quick-role-card" onclick="selectQuickRole('admin', 'Administrator Updated', this)">
                <div class="role-avatar" style="background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="text-truncate">
                    <div class="role-info-title">Admin</div>
                    <div class="role-info-desc text-truncate"><?= __('auth.admin_desc') ?></div>
                </div>
            </div>
        </div>

        <!-- 2. MANAGER -->
        <div class="col-6">
            <div class="quick-role-card" onclick="selectQuickRole('manager', 'Trần Văn Hoàng (IT Leader)', this)">
                <div class="role-avatar" style="background: linear-gradient(135deg, #D97706 0%, #F59E0B 100%);">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <div class="text-truncate">
                    <div class="role-info-title">Manager</div>
                    <div class="role-info-desc text-truncate"><?= __('auth.manager_desc') ?></div>
                </div>
            </div>
        </div>

        <!-- 3. IT STAFF -->
        <div class="col-6">
            <div class="quick-role-card" onclick="selectQuickRole('it_helpdesk', 'Lê Quốc Bảo (Helpdesk)', this)">
                <div class="role-avatar" style="background: linear-gradient(135deg, #0284C7 0%, #38BDF8 100%);">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="text-truncate">
                    <div class="role-info-title">IT Staff</div>
                    <div class="role-info-desc text-truncate"><?= __('auth.staff_desc') ?></div>
                </div>
            </div>
        </div>

        <!-- 4. END USER -->
        <div class="col-6">
            <div class="quick-role-card" onclick="selectQuickRole('user1', 'Đỗ Mai Phương', this)">
                <div class="role-avatar" style="background: linear-gradient(135deg, #059669 0%, #10B981 100%);">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div class="text-truncate">
                    <div class="role-info-title">End User</div>
                    <div class="role-info-desc text-truncate"><?= __('auth.user_desc') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- BUTTON TO OPEN ALL ACCOUNTS MODAL -->
    <button type="button" 
            class="btn btn-outline-primary w-100 py-2 mt-3 d-flex align-items-center justify-content-center gap-2 rounded-3 fw-bold"
            style="border-color: #0C66E4; color: #0C66E4; background: #F0F6FF; font-size: 13px; transition: all 0.2s ease;"
            data-bs-toggle="modal" 
            data-bs-target="#accountsModal">
        <i class="bi bi-people-fill text-primary"></i>
        <span><?= \App\Core\Language::current() === 'vi' ? 'Xem toàn bộ ' . count($usersList) . ' tài khoản demo hệ thống' : 'Browse all ' . count($usersList) . ' demo accounts' ?></span>
        <i class="bi bi-arrow-right-short fs-5"></i>
    </button>
</div>

</details>

<!-- =============================================================== -->
<!-- MODAL: DANH SÁCH TOÀN BỘ TÀI KHOẢN (37 ACCOUNTS) -->
<!-- =============================================================== -->
<div class="modal fade" id="accountsModal" tabindex="-1" aria-labelledby="accountsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="accountsModalLabel">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Danh Sách Toàn Bộ Tài Khoản Hệ Thống (<?= count($usersList) ?>)</span>
                    </h5>
                    <div class="text-muted small mt-1">
                        Bấm chọn bất kỳ tài khoản nào để tự động điền vào ô đăng nhập. Mật khẩu mặc định: <code class="fw-bold text-dark font-monospace">123456</code>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Search & Role Filter Tabs -->
            <div class="p-3 bg-white border-bottom">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   id="accountSearchInput" 
                                   class="form-control border-start-0 ps-0" 
                                   placeholder="Tìm theo tên, username, phòng ban..." 
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="d-flex gap-1 flex-wrap justify-content-md-end" id="roleFilterButtons">
                            <button type="button" class="btn btn-sm btn-primary active rounded-pill px-3 filter-role-btn" data-role="ALL">Tất cả</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 filter-role-btn" data-role="ADMIN">Admin</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 filter-role-btn" data-role="IT_MANAGER">Manager</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 filter-role-btn" data-role="IT_STAFF">IT Staff</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 filter-role-btn" data-role="USER">End User</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Body: Accounts List -->
            <div class="modal-body p-3 bg-light" style="max-height: 480px; overflow-y: auto;">
                <div class="row g-2" id="accountsListContainer">
                    <?php foreach ($usersList as $u): 
                        $rolesUpper = strtoupper($u['roles'] ?? 'USER');
                        $primaryRole = 'USER';
                        if (str_contains($rolesUpper, 'ADMIN')) {
                            $primaryRole = 'ADMIN';
                            $avatarBg = 'linear-gradient(135deg, #4F46E5 0%, #6366F1 100%)';
                            $avatarIcon = 'bi-shield-lock-fill';
                            $badgeClass = 'bg-danger text-white';
                            $roleLabel = 'ADMIN';
                        } elseif (str_contains($rolesUpper, 'IT_MANAGER')) {
                            $primaryRole = 'IT_MANAGER';
                            $avatarBg = 'linear-gradient(135deg, #D97706 0%, #F59E0B 100%)';
                            $avatarIcon = 'bi-speedometer2';
                            $badgeClass = 'bg-warning text-dark';
                            $roleLabel = 'IT MANAGER';
                        } elseif (str_contains($rolesUpper, 'IT_STAFF')) {
                            $primaryRole = 'IT_STAFF';
                            $avatarBg = 'linear-gradient(135deg, #0284C7 0%, #38BDF8 100%)';
                            $avatarIcon = 'bi-tools';
                            $badgeClass = 'bg-info text-dark';
                            $roleLabel = 'IT STAFF';
                        } else {
                            $primaryRole = 'USER';
                            $avatarBg = 'linear-gradient(135deg, #059669 0%, #10B981 100%)';
                            $avatarIcon = 'bi-person-check-fill';
                            $badgeClass = 'bg-success text-white';
                            $roleLabel = 'END USER';
                        }
                    ?>
                        <div class="col-12 col-md-6 account-item-col" 
                             data-username="<?= strtolower($u['username']) ?>" 
                             data-name="<?= strtolower($u['full_name']) ?>" 
                             data-dept="<?= strtolower($u['department'] ?? '') ?>"
                             data-role="<?= $primaryRole ?>">
                            <div class="card h-100 border p-2.5 rounded-3 account-card" 
                                 style="cursor: pointer; transition: all 0.2s ease; background: #FFFFFF;"
                                 onclick="chooseAccountFromModal('<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['full_name'], ENT_QUOTES) ?>')">
                                <div class="d-flex align-items-center gap-3">
                                    <!-- Avatar -->
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: <?= $avatarBg ?>; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0;">
                                        <i class="bi <?= $avatarIcon ?>"></i>
                                    </div>
                                    <!-- Details -->
                                    <div class="flex-grow-1 min-w-0 lh-sm">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="fw-bold text-dark text-truncate small" title="<?= htmlspecialchars($u['full_name']) ?>">
                                                <?= htmlspecialchars($u['full_name']) ?>
                                            </div>
                                            <span class="badge <?= $badgeClass ?> font-monospace ms-1" style="font-size: 9px;"><?= $roleLabel ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 11.5px;">
                                            <span class="font-monospace fw-semibold text-primary">@<?= htmlspecialchars($u['username']) ?></span>
                                            <span>&bull;</span>
                                            <span class="text-truncate"><?= htmlspecialchars($u['department'] ?? 'Chung') ?></span>
                                        </div>
                                    </div>
                                    <!-- Select Button -->
                                    <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 rounded-pill text-primary fw-bold" style="font-size: 11px;">
                                        Chọn <i class="bi bi-arrow-right-short"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Empty state when no results match search -->
                <div id="noAccountsFound" class="text-center py-5 d-none">
                    <i class="bi bi-search text-muted fs-1 mb-2 d-block"></i>
                    <h6 class="text-muted fw-bold">Không tìm thấy tài khoản phù hợp</h6>
                    <p class="text-muted small">Hãy thử tìm với từ khóa khác (ví dụ: admin, helpdesk, kế toán...)</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-2 px-4 bg-white border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Hệ thống tự động điền mật khẩu mặc định <strong>123456</strong></span>
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<style>
.account-card:hover {
    border-color: #0C66E4 !important;
    background-color: #F8FAFC !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(12, 102, 228, 0.12);
}
.account-card:active {
    transform: scale(0.99);
}
</style>

<script>
// Toggle Password Visibility
const togglePasswordBtn = document.getElementById('togglePasswordBtn');
const passwordInput = document.getElementById('password');
const togglePasswordIcon = document.getElementById('togglePasswordIcon');

if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
    togglePasswordBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePasswordIcon.classList.remove('bi-eye-fill');
            togglePasswordIcon.classList.add('bi-eye-slash-fill');
        } else {
            passwordInput.type = 'password';
            togglePasswordIcon.classList.remove('bi-eye-slash-fill');
            togglePasswordIcon.classList.add('bi-eye-fill');
        }
    });
}

// Quick Role Auto-fill & Interactive Feedback
function selectQuickRole(username, fullName, cardEl) {
    fillAccountAndHighlight(username, fullName);

    // Highlight card
    document.querySelectorAll('.quick-role-card').forEach(el => el.classList.remove('active-selected'));
    if (cardEl) {
        cardEl.classList.add('active-selected');
    }
}

// Choose account from Modal
function chooseAccountFromModal(username, fullName) {
    fillAccountAndHighlight(username, fullName);

    // Close modal
    const modalEl = document.getElementById('accountsModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}

// Common function to fill account and trigger UX feedback
function fillAccountAndHighlight(username, fullName) {
    const accountInput = document.getElementById('account');
    const passInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitLoginBtn');
    const alertBox = document.getElementById('selectedAccountAlert');
    const nameEl = document.getElementById('selectedAccountName');
    const userEl = document.getElementById('selectedAccountUser');

    if (accountInput) {
        accountInput.value = username;
        accountInput.focus();
    }
    if (passInput) {
        passInput.value = '123456';
    }

    // Flash selected badge
    if (alertBox && nameEl && userEl) {
        nameEl.textContent = fullName;
        userEl.textContent = '@' + username;
        alertBox.classList.remove('d-none');
        alertBox.classList.add('d-flex');
    }

    // Micro button highlight pulse
    if (submitBtn) {
        submitBtn.style.transform = 'scale(1.02)';
        submitBtn.classList.add('shadow-lg');
        setTimeout(() => {
            submitBtn.style.transform = '';
            submitBtn.classList.remove('shadow-lg');
        }, 300);
    }
}

// Modal Filter & Search Logic
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('accountSearchInput');
    const filterButtons = document.querySelectorAll('.filter-role-btn');
    const accountItems = document.querySelectorAll('.account-item-col');
    const noResultsEl = document.getElementById('noAccountsFound');

    let currentRoleFilter = 'ALL';
    let currentSearchTerm = '';

    function applyFilter() {
        let visibleCount = 0;
        accountItems.forEach(item => {
            const role = item.getAttribute('data-role');
            const username = item.getAttribute('data-username');
            const name = item.getAttribute('data-name');
            const dept = item.getAttribute('data-dept');

            const matchesRole = (currentRoleFilter === 'ALL' || role === currentRoleFilter);
            const matchesSearch = !currentSearchTerm || 
                                  username.includes(currentSearchTerm) || 
                                  name.includes(currentSearchTerm) || 
                                  dept.includes(currentSearchTerm);

            if (matchesRole && matchesSearch) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (noResultsEl) {
            if (visibleCount === 0) {
                noResultsEl.classList.remove('d-none');
            } else {
                noResultsEl.classList.add('d-none');
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearchTerm = this.value.trim().toLowerCase();
            applyFilter();
        });
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary', 'active');

            currentRoleFilter = this.getAttribute('data-role');
            applyFilter();
        });
    });
});

// Form Submission Loading State
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitLoginBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> <?= \App\Core\Language::current() === 'vi' ? 'Đang xác thực...' : 'Authenticating...' ?>';
        }
    });
}
</script>
