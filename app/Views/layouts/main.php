<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;
use App\Models\Project;

/**
 * @var string $content
 * @var string|null $pageTitle
 */
$user = Auth::user();
$roles = Auth::roles();
$baseUrl = Response::baseUrl();
$flashes = Session::getFlashes();
$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($rawUri, PHP_URL_PATH) ?? '/';
if (!empty($baseUrl) && str_starts_with($currentPath, $baseUrl)) {
    $currentPath = substr($currentPath, strlen($baseUrl)) ?: '/';
}

// Fetch projects for topbar and project context (filtered by permission and membership)
$projectModel = new Project();
$hasProjectAccess = Auth::check() && Auth::can('menu.projects');
$allProjects = $hasProjectAccess 
    ? $projectModel->getVisibleProjects(Auth::id(), Auth::isManager(), 'ACTIVE') 
    : [];
$usersList = (new \App\Models\User())->getAllUsers();

$currentProjectId = null;
if (isset($project) && !empty($project['id'])) {
    $currentProjectId = (int)$project['id'];
} elseif (preg_match('#/projects/(\d+)#', $currentPath, $mPrj)) {
    $currentProjectId = (int)$mPrj[1];
    if (!isset($project)) {
        if ($hasProjectAccess && (Auth::isManager() || $projectModel->isMember($currentProjectId, (int)Auth::id()))) {
            $project = $projectModel->findById($currentProjectId);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'TaskFlow Agile & IT Service Management' ?></title>
    
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Enterprise Theme (Jira Style) -->
    <link href="<?= $baseUrl ?>/css/style.css" rel="stylesheet">
    
    <script>
        window.APP_BASE_URL = '<?= $baseUrl ?>';
    </script>
</head>
<body>

<div class="app-container">
    <!-- TASKFLOW AUTHENTIC TOP NAVIGATION BAR -->
    <header class="jira-topbar">
        <div class="d-flex align-items-center gap-2">
            <!-- App Switcher / Waffle Icon -->
            <button class="tf-waffle-btn" title="Ứng dụng & Dịch vụ" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </button>
            <ul class="dropdown-menu shadow border-0 mt-2 p-2" style="width: 250px;">
                <li><h6 class="dropdown-header text-uppercase small text-muted">Phân Hệ Làm Việc</h6></li>
                <?php if (Auth::can('menu.projects')): ?>
                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $baseUrl ?>/projects"><i class="bi bi-kanban text-primary fs-5"></i> <strong>TaskFlow Agile</strong></a></li>
                <?php endif; ?>
                <?php if (Auth::can('menu.tickets')): ?>
                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $baseUrl ?>/tickets"><i class="bi bi-headset text-success fs-5"></i> <strong>Service Desk ITSM</strong></a></li>
                <?php endif; ?>
                <?php if (Auth::can('menu.kb')): ?>
                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $baseUrl ?>/kb"><i class="bi bi-book-half text-info fs-5"></i> <strong>Knowledge Base (Docs)</strong></a></li>
                <?php endif; ?>
                <?php if (Auth::can('menu.jumpserver')): ?>
                    <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $baseUrl ?>/jump"><i class="bi bi-terminal-fill text-warning fs-5"></i> <strong>Bastion PAM</strong></a></li>
                <?php endif; ?>
            </ul>

            <!-- TaskFlow Brand Logo & Name (Replaced Jira) -->
            <a href="<?= Auth::can('menu.projects') ? ($baseUrl . '/projects') : ($baseUrl . '/dashboard') ?>" class="tf-brand-wrap me-3 text-decoration-none">
                <div style="width: 28px; height: 28px; border-radius: 7px; background: linear-gradient(135deg, #4F46E5 0%, #2563EB 50%, #06B6D4 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <span class="fw-bold fs-6 text-dark" style="letter-spacing: -0.3px;">Task<span style="color: #2563EB;">Flow</span></span>
            </a>

            <!-- GROUPED NAVIGATION MENUS (Modern borderless buttons) -->
            <nav class="d-none d-lg-flex align-items-center gap-1">
                <?php if (Auth::can('menu.projects')): ?>
                    <!-- 1. PROJECTS DROPDOWN -->
                    <div class="dropdown">
                        <button class="tf-nav-item jira-nav-item <?= str_starts_with($currentPath, '/projects') ? 'active' : '' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Dự án
                        </button>
                        <ul class="dropdown-menu shadow border-0 mt-1 py-2" style="min-width: 260px;">
                            <li><h6 class="dropdown-header text-uppercase small text-muted">Dự Án Gần Đây</h6></li>
                            <?php foreach (array_slice($allProjects, 0, 5) as $p): ?>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $baseUrl ?>/projects/<?= $p['id'] ?>/board">
                                        <div style="width: 24px; height: 24px; border-radius: 4px; background: <?= htmlspecialchars($p['avatar_bg'] ?? '#6554C0') ?>; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px;">
                                            <i class="bi <?= htmlspecialchars($p['avatar_icon'] ?? 'bi-rocket-takeoff-fill') ?>"></i>
                                        </div>
                                        <div class="lh-1">
                                            <div class="fw-semibold text-dark small"><?= htmlspecialchars($p['name']) ?></div>
                                            <span class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($p['project_key']) ?> &bull; <?= $p['project_type'] ?></span>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small" href="<?= $baseUrl ?>/projects"><i class="bi bi-folder2-open me-2 text-primary"></i> Xem tất cả dự án</a></li>
                            <?php if (Auth::can('projects.create')): ?>
                                <li><a class="dropdown-item small text-primary fw-semibold" href="<?= $baseUrl ?>/projects/create"><i class="bi bi-plus-circle me-2"></i> Tạo dự án mới</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- 2. YOUR WORK DROPDOWN -->
                <div class="dropdown">
                    <button class="tf-nav-item jira-nav-item <?= (str_starts_with($currentPath, '/staff') || str_starts_with($currentPath, '/approvals') || str_starts_with($currentPath, '/my-assets')) ? 'active' : '' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Công việc
                    </button>
                    <ul class="dropdown-menu shadow border-0 mt-1 py-2" style="min-width: 220px;">
                        <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/tickets"><i class="bi bi-ticket-detailed me-2 text-primary"></i> Phiếu yêu cầu của tôi</a></li>
                        <?php if (Auth::isStaff()): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/staff/work-queue"><i class="bi bi-list-task me-2 text-info"></i> Hàng đợi công việc</a></li>
                        <?php endif; ?>
                        <?php if (Auth::can('menu.approvals')): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/approvals"><i class="bi bi-check2-circle me-2 text-success"></i> Phê duyệt chờ tôi</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/my-assets"><i class="bi bi-laptop me-2 text-secondary"></i> Thiết bị / Tài sản của tôi</a></li>
                    </ul>
                </div>

                <?php if (Auth::can('menu.tickets')): ?>
                    <!-- 3. FILTERS & TICKETS DROPDOWN -->
                    <div class="dropdown">
                        <button class="tf-nav-item jira-nav-item <?= (str_starts_with($currentPath, '/tickets') || str_starts_with($currentPath, '/changes') || str_starts_with($currentPath, '/problems')) ? 'active' : '' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Bộ lọc & Phiếu
                        </button>
                        <ul class="dropdown-menu shadow border-0 mt-1 py-2" style="min-width: 230px;">
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/tickets"><i class="bi bi-collection me-2 text-primary"></i> Tất cả phiếu hỗ trợ</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/tickets/board"><i class="bi bi-kanban-fill me-2 text-warning"></i> Bảng vé hỗ trợ (ITSM)</a></li>
                            <?php if (Auth::isManager()): ?>
                                <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/tickets/unassigned"><i class="bi bi-inbox me-2 text-danger"></i> Phiếu chưa phân công</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/changes"><i class="bi bi-arrow-repeat me-2 text-info"></i> Yêu cầu thay đổi (Change)</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/problems"><i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i> Quản lý Problem (RCA)</a></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- 4. DASHBOARDS DROPDOWN -->
                <div class="dropdown">
                    <button class="tf-nav-item jira-nav-item <?= ($currentPath === '/dashboard' || str_starts_with($currentPath, '/manager') || str_starts_with($currentPath, '/reports')) ? 'active' : '' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Bảng điều khiển
                    </button>
                    <ul class="dropdown-menu shadow border-0 mt-1 py-2" style="min-width: 220px;">
                        <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/dashboard"><i class="bi bi-person-workspace me-2 text-primary"></i> Dashboard Cá Nhân</a></li>
                        <?php if (Auth::isManager()): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/manager/dashboard"><i class="bi bi-speedometer2 me-2 text-success"></i> Vận Hành IT (Operations)</a></li>
                        <?php endif; ?>
                        <?php if (Auth::can('menu.reports')): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/reports"><i class="bi bi-bar-chart-line me-2 text-info"></i> Phân Tích SLA & Xu Hướng</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/reports/staff"><i class="bi bi-people-fill me-2 text-warning"></i> Hiệu Suất Kỹ Thuật Viên</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- 5. APPS & SERVICES DROPDOWN -->
                <div class="dropdown">
                    <button class="tf-nav-item jira-nav-item <?= (str_starts_with($currentPath, '/catalog') || (str_starts_with($currentPath, '/assets') && !str_starts_with($currentPath, '/my-assets')) || str_starts_with($currentPath, '/kb') || str_starts_with($currentPath, '/jump')) ? 'active' : '' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Tiện ích
                    </button>
                    <ul class="dropdown-menu shadow border-0 mt-1 py-2" style="min-width: 240px;">
                        <?php if (Auth::can('menu.catalog')): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/catalog"><i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i> Cổng Dịch Vụ (Catalog)</a></li>
                        <?php endif; ?>
                        <?php if (Auth::can('menu.assets')): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/assets"><i class="bi bi-laptop me-2 text-success"></i> Quản Lý Tài Sản (ITAM)</a></li>
                        <?php endif; ?>
                        <?php if (Auth::can('menu.kb')): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/kb"><i class="bi bi-journal-bookmark me-2 text-info"></i> Cơ Sở Tri Thức (KB)</a></li>
                        <?php endif; ?>
                        <?php if (Auth::can('menu.jumpserver')): ?>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/jump"><i class="bi bi-terminal-fill me-2 text-dark"></i> JumpServer Bastion PAM</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/remote-support"><i class="bi bi-display me-2 text-primary"></i> Hỗ Trợ Từ Xa (Remote Live)</a></li>
                    </ul>
                </div>

                <!-- 6. ADMIN SETTINGS DROPDOWN -->
                <?php if (Auth::can('menu.admin')): ?>
                    <div class="dropdown">
                        <button class="tf-nav-item jira-nav-item <?= str_starts_with($currentPath, '/admin') ? 'active' : '' ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Cài đặt
                        </button>
                        <ul class="dropdown-menu shadow border-0 mt-1 py-2" style="min-width: 240px;">
                            <li><h6 class="dropdown-header text-uppercase small text-muted">Quản Trị Hệ Thống</h6></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/permissions"><i class="bi bi-shield-lock-fill me-2 text-primary"></i> Ma trận phân quyền (RBAC)</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/users"><i class="bi bi-person-badge me-2 text-primary"></i> Người dùng & Vai trò</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/groups"><i class="bi bi-people me-2 text-info"></i> Nhóm kỹ thuật (IT Groups)</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/categories"><i class="bi bi-tags me-2 text-success"></i> Danh mục yêu cầu</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/sla"><i class="bi bi-clock me-2 text-warning"></i> Cấu hình quy tắc SLA</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/jump-servers"><i class="bi bi-hdd-network-fill me-2 text-danger"></i> Quản lý Jump Server</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/backups"><i class="bi bi-database-fill-gear me-2 text-dark"></i> Sao lưu & Phục hồi CSDL</a></li>
                            <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/admin/audit-logs"><i class="bi bi-journal-text me-2 text-secondary"></i> Nhật ký kiểm toán</a></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- PROMINENT "+ CREATE" BUTTON -->
                <?php if (Auth::can('menu.projects') && !empty($allProjects)): ?>
                    <button class="tf-create-btn jira-create-btn ms-2" type="button" data-bs-toggle="modal" data-bs-target="#globalQuickCreateModal">
                        <i class="bi bi-plus-lg"></i> Tạo mới
                    </button>
                <?php endif; ?>
            </nav>
        </div>

        <!-- TOPBAR RIGHT CONTROLS -->
        <div class="d-flex align-items-center gap-2">
            <!-- Global Search Box -->
            <div class="position-relative d-none d-md-block">
                <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 8px; font-size: 13px;"></i>
                <form action="<?= $baseUrl ?>/tickets" method="GET" class="m-0">
                    <input type="text" name="search" class="jira-search-input" placeholder="Tìm kiếm trong Jira..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </form>
            </div>

            <!-- Notifications Bell -->
            <?php
            $notificationModel = new \App\Models\Notification();
            $unreadNotifications = $user ? $notificationModel->getUnreadForUser($user['id']) : [];
            $unreadCount = count($unreadNotifications);
            ?>
            <div class="dropdown">
                <button id="notif-btn" class="btn btn-sm btn-link text-secondary p-1 position-relative text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; margin-top: 6px; margin-left: -6px;">
                            <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0" style="width: 320px;">
                    <div class="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">Thông báo</span>
                        <a href="<?= $baseUrl ?>/notifications/read-all" id="notif-header-action" class="text-primary small text-decoration-none" style="display: <?= $unreadCount > 0 ? 'block' : 'none' ?>">Đánh dấu đã đọc</a>
                    </div>
                    <div id="notif-container" style="max-height: 350px; overflow-y: auto;">
                        <?php if (empty($unreadNotifications)): ?>
                            <div class="p-3 text-center text-muted small notif-empty">Không có thông báo mới</div>
                        <?php else: ?>
                            <?php foreach ($unreadNotifications as $notif): ?>
                                <a href="<?= $baseUrl ?>/notifications/read/<?= $notif['id'] ?>" class="dropdown-item p-3 border-bottom text-wrap" style="white-space: normal;">
                                    <div class="fw-bold small text-dark"><?= htmlspecialchars($notif['title']) ?></div>
                                    <div class="text-muted small mt-1" style="line-height: 1.4;"><?= htmlspecialchars($notif['message']) ?></div>
                                    <div class="text-secondary small mt-1" style="font-size: 0.7rem;"><?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?></div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Language Switcher -->
            <div class="dropdown">
                <button class="btn btn-sm btn-link text-secondary p-1 text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-semibold"><?= strtoupper(\App\Core\Language::current()) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><a class="dropdown-item small <?= \App\Core\Language::current() === 'vi' ? 'active' : '' ?>" href="<?= $baseUrl ?>/lang/vi">Tiếng Việt</a></li>
                    <li><a class="dropdown-item small <?= \App\Core\Language::current() === 'en' ? 'active' : '' ?>" href="<?= $baseUrl ?>/lang/en">English</a></li>
                </ul>
            </div>

            <!-- User Profile Avatar -->
            <div class="dropdown ms-1">
                <div class="jira-avatar-circle" data-bs-toggle="dropdown" aria-expanded="false" style="background: linear-gradient(135deg, #0052CC, #2684FF);">
                    <?= mb_strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="min-width: 220px;">
                    <li><h6 class="dropdown-header pb-1"><?= htmlspecialchars($user['full_name'] ?? '') ?></h6></li>
                    <li><span class="dropdown-item-text text-muted small pt-0"><?= htmlspecialchars($user['email'] ?? '') ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard của tôi</a></li>
                    <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/profile"><i class="bi bi-person-circle me-2 text-primary"></i> Hồ sơ cá nhân (Profile)</a></li>
                    <li><a class="dropdown-item small py-2" href="<?= $baseUrl ?>/my-assets"><i class="bi bi-laptop me-2"></i> Thiết bị của tôi</a></li>
                    <li><a class="dropdown-item small py-2 fw-bold text-primary" href="<?= $baseUrl ?>/mobile"><i class="bi bi-phone me-2"></i> Giao diện Mobile App</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item small text-danger py-2" href="<?= $baseUrl ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> <?= __('menu.logout') ?></a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="app-body">
        <!-- CONTEXT-AWARE JIRA SIDEBAR -->
        <?php if (!empty($project) && !empty($project['id'])): ?>
            <!-- 1. JIRA PROJECT SIDEBAR (Active when viewing a Project) -->
            <aside class="jira-project-sidebar">
                <!-- Project Identity Header -->
                <div class="jira-project-header border-bottom">
                    <div class="jira-project-avatar" style="background: <?= htmlspecialchars($project['avatar_bg'] ?? '#6554C0') ?>;">
                        <i class="bi <?= htmlspecialchars($project['avatar_icon'] ?? 'bi-rocket-takeoff-fill') ?>"></i>
                    </div>
                    <div class="jira-project-info">
                        <div class="jira-project-title" title="<?= htmlspecialchars($project['name']) ?>"><?= htmlspecialchars($project['name']) ?></div>
                        <div class="jira-project-subtitle"><?= htmlspecialchars($project['project_key']) ?> &bull; Software project</div>
                    </div>
                </div>

                <!-- Navigation Sections -->
                <div class="jira-sidebar-nav">
                    <!-- PLANNING -->
                    <div class="jira-sidebar-section">PLANNING</div>
                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board#roadmap" class="jira-sidebar-item">
                        <i class="bi bi-signpost-split"></i> Roadmap
                    </a>
                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/backlog" class="jira-sidebar-item <?= str_contains($currentPath, '/backlog') ? 'active' : '' ?>">
                        <i class="bi bi-layers"></i> Backlog
                    </a>
                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/board" class="jira-sidebar-item <?= str_contains($currentPath, '/board') ? 'active' : '' ?>">
                        <i class="bi bi-kanban"></i> Board
                    </a>
                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/issues" class="jira-sidebar-item <?= str_contains($currentPath, '/issues') && !str_contains($currentPath, '/board') ? 'active' : '' ?>">
                        <i class="bi bi-list-check"></i> Issues
                    </a>

                    <!-- DEVELOPMENT & QA -->
                    <div class="jira-sidebar-section">DEVELOPMENT & QA</div>
                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/testcases" class="jira-sidebar-item <?= str_contains($currentPath, '/testcases') ? 'active' : '' ?>">
                        <i class="bi bi-clipboard-check text-purple"></i> Test Cases
                    </a>
                    <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/bugs" class="jira-sidebar-item <?= str_contains($currentPath, '/bugs') ? 'active' : '' ?>">
                        <i class="bi bi-bug-fill text-danger"></i> Bug Tracking
                    </a>

                    <!-- BOTTOM SETTINGS -->
                    <?php if (Auth::isManager() || Auth::can('projects.manage') || (isset($project['lead_id']) && (int)$project['lead_id'] === (int)Auth::id())): ?>
                        <div class="jira-sidebar-section mt-3">PROJECT SETTINGS</div>
                        <a href="<?= $baseUrl ?>/projects/<?= $project['id'] ?>/settings" class="jira-sidebar-item <?= str_contains($currentPath, '/settings') ? 'active' : '' ?>">
                            <i class="bi bi-gear"></i> Project settings
                        </a>
                    <?php endif; ?>
                </div>
            </aside>

        <?php else: ?>
            <!-- 2. GENERAL SYSTEM SIDEBAR (When viewing general ITSM pages) -->
            <aside class="app-sidebar" style="width: 220px;">
                <div class="sidebar-nav p-2">
                    <?php if (Auth::can('menu.projects')): ?>
                        <div class="nav-section-title">DỰ ÁN AGILE</div>
                        <a href="<?= $baseUrl ?>/projects" class="nav-link <?= str_contains($currentPath, '/projects') ? 'active' : '' ?>">
                            <i class="bi bi-kanban-fill text-primary"></i> Danh Sách Dự Án
                        </a>
                        <?php if (!empty($allProjects)): ?>
                            <?php foreach (array_slice($allProjects, 0, 3) as $p): ?>
                                <a href="<?= $baseUrl ?>/projects/<?= $p['id'] ?>/board" class="nav-link text-truncate small ps-3">
                                    <i class="bi bi-arrow-right-short text-secondary"></i> <?= htmlspecialchars($p['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="nav-section-title">SERVICE DESK (ITSM)</div>
                    <?php if (Auth::can('menu.tickets')): ?>
                        <a href="<?= $baseUrl ?>/tickets" class="nav-link <?= str_contains($currentPath, '/tickets') && !str_contains($currentPath, '/tickets/board') ? 'active' : '' ?>">
                            <i class="bi bi-headset text-success"></i> Tất Cả Vé Hỗ Trợ
                        </a>
                        <a href="<?= $baseUrl ?>/tickets/board" class="nav-link <?= str_contains($currentPath, '/tickets/board') ? 'active' : '' ?>">
                            <i class="bi bi-layout-three-columns text-warning"></i> Kanban Vé IT
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::can('menu.catalog')): ?>
                        <a href="<?= $baseUrl ?>/catalog" class="nav-link <?= str_contains($currentPath, '/catalog') ? 'active' : '' ?>">
                            <i class="bi bi-grid-3x3-gap-fill text-info"></i> Danh Mục Dịch Vụ
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::can('menu.approvals')): ?>
                        <a href="<?= $baseUrl ?>/approvals" class="nav-link <?= str_contains($currentPath, '/approvals') ? 'active' : '' ?>">
                            <i class="bi bi-check2-circle text-success"></i> Phê Duyệt (Approvals)
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::can('menu.assets')): ?>
                        <a href="<?= $baseUrl ?>/assets" class="nav-link <?= str_contains($currentPath, '/assets') ? 'active' : '' ?>">
                            <i class="bi bi-laptop text-primary"></i> Quản Lý Tài Sản
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::can('menu.kb')): ?>
                        <a href="<?= $baseUrl ?>/kb" class="nav-link <?= str_contains($currentPath, '/kb') ? 'active' : '' ?>">
                            <i class="bi bi-journal-bookmark text-secondary"></i> Cơ Sở Tri Thức
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::can('menu.jumpserver')): ?>
                        <a href="<?= $baseUrl ?>/jump" class="nav-link <?= str_contains($currentPath, '/jump') ? 'active' : '' ?>">
                            <i class="bi bi-terminal-fill text-dark"></i> JumpServer PAM
                        </a>
                    <?php endif; ?>
                    <a href="<?= $baseUrl ?>/remote-support" class="nav-link <?= str_contains($currentPath, '/remote-support') ? 'active' : '' ?>">
                        <i class="bi bi-display text-primary"></i> Remote Hỗ Trợ
                    </a>

                    <?php if (Auth::can('menu.admin')): ?>
                        <div class="nav-section-title mt-2">HỆ THỐNG</div>
                        <a href="<?= $baseUrl ?>/admin/permissions" class="nav-link <?= str_contains($currentPath, '/admin/permissions') ? 'active' : '' ?>">
                            <i class="bi bi-shield-lock-fill text-primary"></i> Ma Trận Phân Quyền
                        </a>
                        <a href="<?= $baseUrl ?>/admin/backups" class="nav-link <?= str_contains($currentPath, '/admin/backups') ? 'active' : '' ?>">
                            <i class="bi bi-database-fill-gear text-dark"></i> Sao Lưu &amp; Khôi Phục
                        </a>
                    <?php endif; ?>
                </div>
            </aside>
        <?php endif; ?>

        <!-- MAIN APP CONTENT AREA -->
        <main class="app-main bg-white" style="flex: 1; overflow-y: auto; height: 100%; min-height: 0; padding: 20px 24px;">
            <!-- FLASH MESSAGES -->
            <?php if (!empty($flashes)): ?>
                <?php 
                $alertIcons = [
                    'success' => 'bi-check-circle-fill text-success',
                    'danger'  => 'bi-exclamation-triangle-fill text-danger',
                    'error'   => 'bi-exclamation-triangle-fill text-danger',
                    'warning' => 'bi-exclamation-circle-fill text-warning',
                    'info'    => 'bi-info-circle-fill text-info'
                ];
                ?>
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php 
                    $iconClass = $alertIcons[$type] ?? 'bi-info-circle-fill text-info';
                    $alertClass = ($type === 'error') ? 'danger' : $type;
                    ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show shadow-sm mb-3" role="alert">
                            <i class="bi <?= $iconClass ?> fs-5 me-2"></i>
                            <span class="flex-grow-1"><?= htmlspecialchars($msg) ?></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- DYNAMIC VIEW CONTENT -->
            <?= $content ?>
        </main>
    </div>
</div>

<?php if (Auth::can('menu.projects') && !empty($allProjects)): ?>
<!-- ====================================================================
     GLOBAL TASKFLOW QUICK CREATE MODAL (BACKLOG, TASK, BUG, TESTCASE, STORY)
     ==================================================================== -->
<div class="modal fade" id="globalQuickCreateModal" tabindex="-1" aria-labelledby="quickCreateLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom px-4 py-2 bg-light d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 28px; height: 28px; border-radius: 6px; background: #0C66E4; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <span class="fw-bold text-dark fs-6" id="quickCreateLabel">Tạo Công Việc Mới (New Work Item)</span>
                    <span class="badge bg-white text-secondary border font-monospace ms-2" style="font-size: 10px;">ALM WORK ITEM</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="globalQuickCreateForm" onsubmit="handleGlobalQuickCreate(event)">
                <!-- 1. HEADER TITLE INPUT (LARGE, BORDERLESS) -->
                <div class="px-4 pt-3 pb-2 border-bottom bg-white">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-semibold text-secondary">Dự án:</span>
                            <select name="project_id" id="qc_project_id" class="form-select form-select-sm" style="width: auto; min-width: 170px;" onchange="onQcProjectChange()" required>
                                <?php foreach ($allProjects as $prj): ?>
                                    <option value="<?= $prj['id'] ?>" <?= ($currentProjectId === (int)$prj['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prj['name']) ?> (<?= htmlspecialchars($prj['project_key']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="issue_type" id="qc_issue_type" class="form-select form-select-sm fw-bold border-primary text-primary" style="width: auto; min-width: 170px;" onchange="toggleQcTypeFields(this.value)" required>
                                <option value="BACKLOG">📦 Backlog Item</option>
                                <option value="TASK" selected>📌 Task</option>
                                <option value="BUG">🐞 Bug</option>
                                <option value="TESTCASE">🧪 TestCase</option>
                                <option value="STORY">📖 User Story</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted">Trạng thái:</span>
                            <select name="status" class="form-select form-select-sm fw-semibold" style="width: 130px;">
                                <option value="TO_DO" selected>TO DO</option>
                                <option value="IN_PROGRESS">IN PROGRESS</option>
                                <option value="IN_REVIEW">IN REVIEW</option>
                                <option value="DONE">DONE</option>
                            </select>
                        </div>
                    </div>

                    <input type="text" name="summary" id="qc_summary" class="form-control form-control-lg border-0 px-0 fw-bold text-dark shadow-none" placeholder="Enter title" style="font-size: 24px; letter-spacing: -0.5px;" required autocomplete="off">
                </div>

                <!-- 2. META STRIP (ASSIGNED TO, SPRINT, TAGS, COMPONENT) -->
                <div class="px-4 py-2 bg-light border-bottom d-flex flex-wrap align-items-center gap-3 text-secondary small">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold text-dark"><i class="bi bi-person me-1"></i>Assigned to:</span>
                        <select name="assignee_id" id="qc_assignee_id" class="form-select form-select-sm" style="width: auto; min-width: 170px;">
                            <option value="">-- Chưa phân công --</option>
                            <?php foreach ($usersList as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($u['id'] == Auth::id()) ? 'data-self="1"' : '' ?>>
                                    <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-check form-switch m-0 ms-1">
                            <input class="form-check-input" type="checkbox" id="qc_self_assign_toggle" onchange="toggleSelfAssign(this.checked)">
                            <label class="form-check-label text-primary small" for="qc_self_assign_toggle">Tự nhận</label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold text-dark"><i class="bi bi-layers me-1"></i>Vị trí:</span>
                        <select name="sprint_status" id="qc_sprint_status" class="form-select form-select-sm" style="width: auto;">
                            <option value="ACTIVE_SPRINT" selected>Bảng Sprint</option>
                            <option value="BACKLOG">Backlog</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold text-dark"><i class="bi bi-grid-3x3-gap me-1"></i>Module:</span>
                        <select name="component" class="form-select form-select-sm" style="width: auto;">
                            <option value="">None</option>
                            <option value="BILLING">BILLING</option>
                            <option value="ACCOUNTS">ACCOUNTS</option>
                            <option value="FORMS">FORMS</option>
                            <option value="FEEDBACK">FEEDBACK</option>
                            <option value="SECURITY">SECURITY</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold text-dark"><i class="bi bi-flag me-1"></i>Ưu tiên:</span>
                        <select name="priority" class="form-select form-select-sm" style="width: auto;">
                            <option value="HIGHEST">🔴 1 - Highest</option>
                            <option value="HIGH">🟠 2 - High</option>
                            <option value="MEDIUM" selected>🟡 3 - Medium</option>
                            <option value="LOW">🔵 4 - Low</option>
                            <option value="LOWEST">⚪ 5 - Lowest</option>
                        </select>
                    </div>
                </div>

                <!-- 3. TWO-COLUMN ALM BODY -->
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- LEFT COLUMN: DESCRIPTION, NOTES, TESTCASE/BUG, DISCUSSION (IMAGE 2) -->
                        <div class="col-lg-8 border-end pe-lg-4">
                            <!-- Description Section -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">Description</label>
                                <textarea name="description" id="qc_desc" class="form-control form-control-sm" rows="3" placeholder="Click to add Description..."></textarea>
                            </div>

                            <!-- Notes Section -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">Notes</label>
                                <textarea name="notes" id="qc_notes" class="form-control form-control-sm" rows="2" placeholder="Click to add Notes..."></textarea>
                            </div>

                            <!-- CÁC TRƯỜNG CHUYÊN BIỆT CHO BUG -->
                            <div id="qc_bug_section" class="mb-3 p-3 bg-light rounded border border-danger border-opacity-25" style="display: none;">
                                <div class="fw-bold text-danger small mb-2"><i class="bi bi-bug-fill me-1"></i> THÔNG TIN BÁO LỖI (BUG DETAILS)</div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small text-secondary">Mức độ nghiêm trọng (Severity)</label>
                                        <select name="bug_severity" class="form-select form-select-sm">
                                            <option value="BLOCKER">🔴 Blocker</option>
                                            <option value="CRITICAL">🟠 Critical</option>
                                            <option value="MAJOR" selected>🟡 Major</option>
                                            <option value="MINOR">🔵 Minor</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-secondary">Môi trường phát sinh (Environment)</label>
                                        <input type="text" name="environment" class="form-control form-control-sm" placeholder="Chrome Windows 11...">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-secondary">Các bước tái hiện (Steps to Reproduce)</label>
                                        <textarea name="steps_to_reproduce" class="form-control form-control-sm" rows="2" placeholder="1. Mở trang...&#10;2. Bấm thao tác..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- CÁC TRƯỜNG CHUYÊN BIỆT CHO TESTCASE -->
                            <div id="qc_testcase_section" class="mb-3 p-3 bg-light rounded border border-purple border-opacity-25" style="display: none;">
                                <div class="fw-bold text-purple small mb-2"><i class="bi bi-clipboard-check me-1"></i> THIẾT LẬP CA KIỂM THỬ (TESTCASE DETAILS)</div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small text-secondary">Loại kiểm thử</label>
                                        <select name="test_type" class="form-select form-select-sm">
                                            <option value="MANUAL" selected>Manual Test</option>
                                            <option value="AUTOMATED">Automated</option>
                                            <option value="REGRESSION">Regression</option>
                                            <option value="SMOKE">Smoke</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-secondary">Trạng thái thực thi</label>
                                        <select name="test_execution_status" class="form-select form-select-sm">
                                            <option value="UNTESTED" selected>Chưa kiểm thử</option>
                                            <option value="PASSED">Đạt (Passed)</option>
                                            <option value="FAILED">Thất bại (Failed)</option>
                                            <option value="BLOCKED">Bị chặn (Blocked)</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-secondary">Các bước kiểm thử (Test Steps)</label>
                                        <textarea name="test_steps" class="form-control form-control-sm" rows="2" placeholder="1. Mở trang...&#10;2. Nhập dữ liệu..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-secondary">Kết quả mong đợi (Expected Result)</label>
                                        <input type="text" name="test_expected_result" class="form-control form-control-sm" placeholder="Hệ thống phản hồi thành công...">
                                    </div>
                                </div>
                            </div>

                            <!-- Discussion Section (Image 2) -->
                            <div class="mb-2">
                                <label class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">Discussion</label>
                                <div class="d-flex gap-2 align-items-start">
                                    <div class="jira-avatar-circle flex-shrink-0" style="background: #0052CC; width: 32px; height: 32px; font-size: 13px;">
                                        <?= strtoupper(substr(Auth::user()['full_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <textarea name="initial_comment" class="form-control form-control-sm" rows="2" placeholder="Add a comment or discussion note for the team..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: RELATED WORK, EFFORT, PLANNING (IMAGE 2) -->
                        <div class="col-lg-4">
                            <!-- Related Work (Parent-Child Hierarchy) -->
                            <div class="p-3 bg-light rounded-2 border mb-3" id="qc_parent_wrap">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold text-dark small text-uppercase" id="qc_parent_label"><i class="bi bi-diagram-3-fill me-1 text-primary"></i>Related Work</span>
                                    <span class="badge bg-white text-secondary border font-monospace" style="font-size: 10px;">Hierarchy</span>
                                </div>
                                <label class="form-label small text-secondary mb-1">Mục cha liên kết (Parent Work Item)</label>
                                <select name="parent_id" id="qc_parent_id" class="form-select form-select-sm">
                                    <option value="">-- Không liên kết mục cha --</option>
                                </select>
                                <div class="form-text text-muted small" id="qc_parent_hint" style="font-size: 11px;">
                                    Bug, Task, Issue sẽ trực thuộc mục Backlog cha đã chọn.
                                </div>
                            </div>

                            <!-- Effort (Hours) (Image 2) -->
                            <div class="p-3 bg-light rounded-2 border mb-3">
                                <div class="fw-bold text-dark small text-uppercase mb-2"><i class="bi bi-stopwatch me-1 text-primary"></i>Effort (Hours)</div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small text-secondary mb-0">Original Estimate</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.5" min="0" name="original_estimate_hours" class="form-control" value="0">
                                            <span class="input-group-text">h</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-secondary mb-0">Remaining</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.5" min="0" name="remaining_hours" class="form-control" value="0">
                                            <span class="input-group-text">h</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Planning (Image 2) -->
                            <div class="p-3 bg-light rounded-2 border mb-2">
                                <div class="fw-bold text-dark small text-uppercase mb-2"><i class="bi bi-calendar3 me-1 text-primary"></i>Planning</div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small text-secondary mb-0">Start Date</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-secondary mb-0">Due Date</label>
                                        <input type="date" name="due_date" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                                    </div>
                                    <div class="col-12 mt-1">
                                        <label class="form-label small text-secondary mb-0">Area / Module</label>
                                        <input type="text" name="area" class="form-control form-control-sm" value="Main/Core" placeholder="VD: Backend, Frontend...">
                                    </div>
                                    <div class="col-12 mt-1">
                                        <label class="form-label small text-secondary mb-0">Risks</label>
                                        <input type="text" name="risks" class="form-control form-control-sm" placeholder="Rủi ro kỹ thuật...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-2 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" id="qc_submit_btn" class="btn btn-sm btn-primary fw-bold px-4" style="background:#0C66E4; border-color:#0C66E4;">
                        <i class="bi bi-check-lg me-1"></i> Lưu & Tạo Công Việc (Save & Close)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 for polished confirmations -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- App JS -->
<script src="<?= $baseUrl ?>/js/app.js"></script>

<script>
const CURRENT_USER_ID = <?= Auth::id() ?: 0 ?>;

function onQcProjectChange() {
    const type = document.getElementById('qc_issue_type').value;
    loadQcParents(type);
}

function toggleQcTypeFields(type) {
    const testSec = document.getElementById('qc_testcase_section');
    const bugSec = document.getElementById('qc_bug_section');
    const parentWrap = document.getElementById('qc_parent_wrap');
    const sprintSelect = document.getElementById('qc_sprint_status');

    if (testSec) testSec.style.display = (type === 'TESTCASE') ? 'block' : 'none';
    if (bugSec) bugSec.style.display = (type === 'BUG') ? 'block' : 'none';

    if (type === 'BACKLOG') {
        if (parentWrap) parentWrap.style.display = 'none';
        if (sprintSelect) sprintSelect.value = 'BACKLOG';
    } else {
        if (parentWrap) parentWrap.style.display = 'block';
        loadQcParents(type);
    }
}

function loadQcParents(type) {
    const prjId = document.getElementById('qc_project_id').value;
    const parentSelect = document.getElementById('qc_parent_id');
    const parentLabel = document.getElementById('qc_parent_label');
    const parentHint = document.getElementById('qc_parent_hint');
    if (!parentSelect || !prjId) return;

    if (type === 'TESTCASE') {
        parentLabel.innerHTML = '<i class="bi bi-diagram-3-fill me-1 text-purple"></i> MỤC CHA LIÊN KẾT (PARENT BUG / TASK)';
        parentHint.innerText = 'TestCase là con trực thuộc Bug hoặc Task được chọn.';
    } else {
        parentLabel.innerHTML = '<i class="bi bi-diagram-3-fill me-1 text-primary"></i> MỤC BACKLOG CHA (PARENT BACKLOG ITEM)';
        parentHint.innerText = 'Bug, Task, Story là con trực thuộc Hạng mục Backlog cha.';
    }

    parentSelect.innerHTML = '<option value="">-- Đang tải danh sách mục cha... --</option>';

    fetch('<?= $baseUrl ?>/projects/api/eligible-parents?project_id=' + prjId + '&type=' + type)
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.parents)) {
                let html = '<option value="">-- Không liên kết mục cha --</option>';
                res.parents.forEach(p => {
                    html += `<option value="${p.id}">[${p.issue_key}] ${p.summary} (${p.issue_type})</option>`;
                });
                parentSelect.innerHTML = html;
            } else {
                parentSelect.innerHTML = '<option value="">-- Chưa có mục cha phù hợp --</option>';
            }
        })
        .catch(() => {
            parentSelect.innerHTML = '<option value="">-- Không liên kết mục cha --</option>';
        });
}

function toggleSelfAssign(checked) {
    const select = document.getElementById('qc_assignee_id');
    if (!select) return;
    if (checked && CURRENT_USER_ID) {
        select.value = CURRENT_USER_ID;
    }
}

// Khi modal mở lên, tự load eligible parents
document.getElementById('globalQuickCreateModal')?.addEventListener('show.bs.modal', function () {
    const type = document.getElementById('qc_issue_type').value;
    toggleQcTypeFields(type);
});

function handleGlobalQuickCreate(e) {
    e.preventDefault();
    const form = document.getElementById('globalQuickCreateForm');
    const submitBtn = document.getElementById('qc_submit_btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang tạo...';

    const formData = new FormData(form);

    fetch('<?= $baseUrl ?>/projects/api/quick-create', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Tạo Công Việc';
        if (res.success) {
            const modalEl = document.getElementById('globalQuickCreateModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            form.reset();

            Swal.fire({
                icon: 'success',
                title: 'Tạo thành công!',
                text: res.message,
                showConfirmButton: false,
                timer: 1400
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: res.message || 'Không thể tạo issue'
            });
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Tạo Công Việc';
    });
}

// Unread notifications poller
document.addEventListener('DOMContentLoaded', function() {
    setInterval(function() {
        var baseUrl = window.APP_BASE_URL || '';
        fetch(baseUrl + '/api/notifications/unread')
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    var count = res.data.length;
                    var btn = document.getElementById('notif-btn');
                    var badge = document.getElementById('notif-badge');
                    var container = document.getElementById('notif-container');
                    var headerAction = document.getElementById('notif-header-action');
                    
                    if (count > 0) {
                        if (badge) {
                            badge.textContent = count > 9 ? '9+' : count;
                        } else if(btn) {
                            btn.innerHTML += '<span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; margin-top: 6px; margin-left: -6px;">' + (count > 9 ? '9+' : count) + '</span>';
                        }
                        if (headerAction) headerAction.style.display = 'block';
                    } else {
                        if (badge) badge.remove();
                        if (headerAction) headerAction.style.display = 'none';
                    }
                }
            })
            .catch(e => {});

        // Check for pending remote assistance invitations
        fetch(baseUrl + '/remote-support/api/check-pending')
            .then(res => res.json())
            .then(res => {
                if (res && res.pending) {
                    var sess = res.pending;
                    if (!document.getElementById('remoteRequestFloatingAlert')) {
                        var box = document.createElement('div');
                        box.id = 'remoteRequestFloatingAlert';
                        box.className = 'position-fixed bottom-0 end-0 m-3 p-3 bg-white border border-primary border-3 rounded-4 shadow-lg animate__animated animate__bounceInUp';
                        box.style.zIndex = '999999';
                        box.style.maxWidth = '360px';
                        box.innerHTML = `
                            <div class="d-flex align-items-start gap-3">
                                <div class="p-2 bg-primary text-white rounded-circle shadow-sm">
                                    <i class="bi bi-display fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-danger">YÊU CẦU REMOTE</span>
                                        <button type="button" class="btn-close btn-sm" onclick="this.closest('#remoteRequestFloatingAlert').remove()"></button>
                                    </div>
                                    <div class="fw-bold text-dark small mb-1">Kỹ thuật viên <strong>${sess.staff_name}</strong> đang yêu cầu kết nối remote máy tính của bạn!</div>
                                    <div class="text-muted small mb-2" style="font-size:0.75rem;">Mã phiên: <strong class="text-primary">${sess.session_code}</strong></div>
                                    <a href="${baseUrl}/remote-support/session/${sess.session_code}" class="btn btn-primary btn-sm w-100 fw-bold shadow-xs">
                                        <i class="bi bi-broadcast me-1"></i> Tham gia & Cấp quyền ngay
                                    </a>
                                </div>
                            </div>
                        `;
                        document.body.appendChild(box);
                    }
                } else {
                    var el = document.getElementById('remoteRequestFloatingAlert');
                    if (el) el.remove();
                }
            })
            .catch(e => {});
    }, 10000);

    // =========================================================================
    // noVNC / TightVNC REALTIME REQUEST POLLER (REQUEST -> ACCEPT -> CONTROL)
    // =========================================================================
    let currentVncSessionId = null;
    let vncCountdownTimer = null;
    let vncShownSessionId = null;
    function stopVncCountdown() { if (vncCountdownTimer) { clearInterval(vncCountdownTimer); vncCountdownTimer = null; } }
    function hideVncModal() { stopVncCountdown(); vncShownSessionId = null; currentVncSessionId = null; const el = document.getElementById('novncIncomingModal'); if (el) { const m = bootstrap.Modal.getInstance(el); if (m) m.hide(); } }
    function startVncCountdown(req) { stopVncCountdown(); let remain = 30; if (req.seconds_remaining != null) { remain = parseInt(req.seconds_remaining, 10); } if (req.expires_at) { const left = Math.ceil((new Date(req.expires_at.replace(' ', 'T')) - Date.now()) / 1000); if (!isNaN(left)) { remain = left; } } if (isNaN(remain) || remain < 0) { remain = 0; } const badge = document.getElementById('novncCountdown'); const tick = function() { if (badge) { badge.textContent = remain + 's'; } if (remain <= 0) { hideVncModal(); return; } remain--; }; tick(); vncCountdownTimer = setInterval(tick, 1000); }
    setInterval(function() {
        fetch(baseUrl + '/remote-support/vnc/check-request')
            .then(res => res.json())
            .then(data => {
                if (data && data.has_request && data.request) {
                    const req = data.request;
                    currentVncSessionId = req.id; if (vncShownSessionId !== req.id) { vncShownSessionId = req.id; startVncCountdown(req); }
                    const reqText = document.getElementById('novncRequesterText');
                    const tickText = document.getElementById('novncTicketText');
                    if (reqText) {
                        if (req.is_test_self) {
                            reqText.innerHTML = '<span class="badge bg-warning text-dark me-1">Test 1 Máy</span> Yêu cầu điều khiển máy khách: ' + (req.enduser_name || 'Đỗ Mai Phương');
                        } else {
                            reqText.textContent = `IT [${req.it_name}] muốn điều khiển máy bạn`;
                        }
                    }
                    if (tickText) tickText.textContent = `#${req.ticket_code || req.ticket_id}: ${req.ticket_title}`;
                    const modalEl = document.getElementById('novncIncomingModal');
                    if (modalEl && (!modalEl.classList.contains('show'))) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                }
            })
            .catch(() => {});
    }, 1000);

    const btnAcceptNovnc = document.getElementById('btnAcceptNovnc');
    const btnRejectNovnc = document.getElementById('btnRejectNovnc');
    if (btnAcceptNovnc) {
        btnAcceptNovnc.addEventListener('click', function() {
            if (!currentVncSessionId) return;
            fetch(baseUrl + '/remote-support/vnc/respond', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= \App\Core\Session::csrfToken() ?>' },
                body: JSON.stringify({ session_id: currentVncSessionId, action: 'accept', _csrf_token: '<?= \App\Core\Session::csrfToken() ?>' })
            }).then(() => {
                stopVncCountdown(); vncShownSessionId = null; const modal = bootstrap.Modal.getInstance(document.getElementById('novncIncomingModal'));
                if (modal) modal.hide();
                showVncActiveBar(currentVncSessionId);
            });
        });
    }
    if (btnRejectNovnc) {
        btnRejectNovnc.addEventListener('click', function() {
            if (!currentVncSessionId) return;
            fetch(baseUrl + '/remote-support/vnc/respond', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= \App\Core\Session::csrfToken() ?>' },
                body: JSON.stringify({ session_id: currentVncSessionId, action: 'reject', _csrf_token: '<?= \App\Core\Session::csrfToken() ?>' })
            }).then(() => {
                stopVncCountdown(); vncShownSessionId = null; const modal = bootstrap.Modal.getInstance(document.getElementById('novncIncomingModal'));
                if (modal) modal.hide();
            });
        });
    }

    function showVncActiveBar(sessId) {
        if (document.getElementById('vncActiveFloatingBar')) return;
        const bar = document.createElement('div');
        bar.id = 'vncActiveFloatingBar';
        bar.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-3 px-4 py-2 bg-dark text-white rounded-pill shadow-lg border border-danger d-flex align-items-center gap-3 animate__animated animate__fadeInUp';
        bar.style.zIndex = '1000000';
        bar.innerHTML = `
            <span class="spinner-grow spinner-grow-sm text-danger" role="status"></span>
            <span class="small fw-bold">Màn hình đang được kết nối với IT qua noVNC</span>
            <button type="button" class="btn btn-danger btn-sm rounded-pill fw-bold py-0 px-3" onclick="endVncSession(${sessId})">
                <i class="bi bi-x-circle me-1"></i> Ngắt Kết Nối
            </button>
        `;
        document.body.appendChild(bar);
    }

    window.endVncSession = function(sessId) {
        fetch(baseUrl + '/remote-support/vnc/end', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= \App\Core\Session::csrfToken() ?>' },
            body: JSON.stringify({ session_id: sessId, _csrf_token: '<?= \App\Core\Session::csrfToken() ?>' })
        }).then(() => {
            const bar = document.getElementById('vncActiveFloatingBar');
            if (bar) bar.remove();
        });
    };
});
</script>

<!-- noVNC Incoming Request Modal for Enduser -->
<div class="modal fade" id="novncIncomingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" style="z-index: 1000000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-display me-2"></i> Yêu Cầu Điều Khiển Từ Xa (noVNC)
                </h5>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="p-3 bg-danger bg-opacity-10 rounded-circle d-inline-block text-danger mb-3">
                    <i class="bi bi-person-workspace fs-1"></i>
                </div>
                <h5 class="fw-bold mb-2" id="novncRequesterText">Kỹ thuật viên IT đang yêu cầu điều khiển máy bạn</h5>
                <p class="text-muted small mb-3">
                    Sự cố: <strong id="novncTicketText" class="text-dark">#TICKET</strong><br>
                    Kỹ thuật viên sẽ có thể thao tác chuột và bàn phím trên máy tính của bạn thông qua noVNC để kiểm tra và xử lý sự cố.
                </p>
                <div class="alert alert-warning py-2 px-3 small text-start">
                    <i class="bi bi-shield-check me-1"></i> <strong>Lưu ý:</strong> Đảm bảo máy tính của bạn đã bật TightVNC Server (mặc định cổng 5900). Bạn có thể ngắt kết nối bất cứ lúc nào.
                </div>
            </div>
            <div class="modal-footer bg-light p-3 d-flex justify-content-between">
                <span id="novncCountdown" class="badge bg-warning text-dark align-self-center fw-bold">30s</span>
                <button type="button" class="btn btn-outline-secondary px-4 fw-bold" id="btnRejectNovnc">
                    <i class="bi bi-x-circle me-1"></i> Từ Chối
                </button>
                <button type="button" class="btn btn-danger px-4 fw-bold shadow-sm" id="btnAcceptNovnc">
                    <i class="bi bi-check-circle me-1"></i> Chấp Nhận Cho Điều Khiển
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
