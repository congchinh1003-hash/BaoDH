<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle ?? 'TaskFlow Mobile') ?></title>
    
    <!-- PWA & Mobile Web App meta -->
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" type="image/png" href="<?= $baseUrl ?? '' ?>/favicon.ico">
    
    <!-- Google Fonts & Bootstrap 5 & Bootstrap Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- QR Code Scanner Library (Local first, fallback CDN) -->
    <script src="<?= $baseUrl ?? '' ?>/public/js/html5-qrcode.min.js"></script>
    <script>
        if (typeof Html5Qrcode === 'undefined') {
            document.write('<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"><\/script>');
        }
    </script>

    <style>
        :root {
            --app-primary: #3b82f6;
            --app-primary-dark: #1d4ed8;
            --app-primary-light: #eff6ff;
            --app-accent: #6366f1;
            --app-bg: #f8fafc;
            --app-card: #ffffff;
            --app-text: #0f172a;
            --app-muted: #64748b;
            --app-border: #e2e8f0;
            --app-success: #10b981;
            --app-warning: #f59e0b;
            --app-danger: #ef4444;
            --safe-bottom: env(safe-area-inset-bottom, 16px);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--app-bg);
            color: var(--app-text);
            margin: 0;
            padding: 0;
            padding-bottom: calc(75px + var(--safe-bottom));
            overflow-x: hidden;
            user-select: none;
            -webkit-user-select: none;
        }

        /* Top App Bar */
        .mobile-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand-logo {
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-badge {
            font-size: 0.65rem;
            padding: 2px 8px;
            background: rgba(59, 130, 246, 0.25);
            color: #60a5fa;
            border-radius: 999px;
            font-weight: 700;
            border: 1px solid rgba(96, 165, 250, 0.3);
        }

        /* Bottom Navigation Bar */
        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            height: calc(65px + var(--safe-bottom));
            padding-bottom: var(--safe-bottom);
            display: flex;
            align-items: center;
            justify-content: space-around;
            border-top: 1px solid var(--app-border);
            box-shadow: 0 -4px 25px rgba(0, 0, 0, 0.06);
            z-index: 1050;
        }

        .nav-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--app-muted);
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            flex: 1;
            height: 100%;
            cursor: pointer;
        }

        .nav-tab i {
            font-size: 1.35rem;
            margin-bottom: 2px;
            transition: transform 0.2s ease;
        }

        .nav-tab.active {
            color: var(--app-primary);
        }

        .nav-tab.active i {
            transform: translateY(-2px);
        }

        .nav-tab .badge-counter {
            position: absolute;
            top: 6px;
            right: calc(50% - 18px);
            background: var(--app-danger);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 10px;
            border: 2px solid #fff;
            min-width: 18px;
            text-align: center;
        }

        .nav-center-btn {
            position: relative;
            top: -14px;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
            border: 4px solid #ffffff;
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        .nav-center-btn:active {
            transform: scale(0.92);
        }

        .nav-center-btn i {
            font-size: 1.5rem;
            margin: 0;
        }

        /* Screen containers */
        .app-screen {
            display: none;
            animation: fadeIn 0.25s ease-out forwards;
            padding: 16px;
        }

        .app-screen.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Modern Card Component */
        .card-custom {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 14px;
            overflow: hidden;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .card-custom:active {
            transform: scale(0.99);
        }

        .stat-card {
            padding: 14px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Quick Action Button Grid */
        .quick-action-btn {
            background: #ffffff;
            border: 1px solid var(--app-border);
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .quick-action-btn:active {
            background-color: var(--app-primary-light);
            border-color: #bfdbfe;
            transform: scale(0.96);
        }

        .quick-action-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 8px auto;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Filter Chips */
        .chip-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .chip-scroll::-webkit-scrollbar {
            display: none;
        }

        .chip-btn {
            white-space: nowrap;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            background: #ffffff;
            color: var(--app-muted);
            border: 1px solid var(--app-border);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chip-btn.active {
            background: var(--app-primary);
            color: #ffffff;
            border-color: var(--app-primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* QR Scanner Viewfinder UI */
        .scanner-container {
            position: relative;
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            width: 100%;
            height: 380px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        }

        #qr-reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
        }

        #qr-reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
        }

        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .scan-target-box {
            width: 230px;
            height: 230px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            position: relative;
            box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.45);
        }

        /* Corner glowing markers */
        .scan-target-box::before, .scan-target-box::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 30px;
            border-color: #38bdf8;
            border-style: solid;
        }

        .scan-target-box::before {
            top: -2px;
            left: -2px;
            border-width: 4px 0 0 4px;
            border-top-left-radius: 22px;
        }

        .scan-target-box::after {
            bottom: -2px;
            right: -2px;
            border-width: 0 4px 4px 0;
            border-bottom-right-radius: 22px;
        }

        /* Red Laser Scanner Line */
        .scan-laser-line {
            position: absolute;
            left: 5%;
            right: 5%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ef4444, #f87171, transparent);
            box-shadow: 0 0 12px #ef4444;
            animation: laserScan 2.2s infinite ease-in-out;
        }

        @keyframes laserScan {
            0% { top: 8%; opacity: 0.2; }
            50% { top: 90%; opacity: 1; }
            100% { top: 8%; opacity: 0.2; }
        }

        /* Bottom Sheet Modal Style */
        .modal.bottom-sheet .modal-dialog {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0;
            max-width: 100%;
            transform: translate3d(0, 100%, 0);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal.bottom-sheet.show .modal-dialog {
            transform: translate3d(0, 0, 0);
        }

        .modal.bottom-sheet .modal-content {
            border-radius: 24px 24px 0 0;
            border: none;
            max-height: 88vh;
            overflow-y: auto;
            padding-bottom: var(--safe-bottom);
        }

        .sheet-drag-handle {
            width: 40px;
            height: 5px;
            background-color: #cbd5e1;
            border-radius: 999px;
            margin: 10px auto 14px auto;
        }

        /* Status Badge Utilities */
        .badge-status {
            padding: 4px 10px;
            font-size: 0.72rem;
            font-weight: 700;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-new { background-color: #dbeafe; color: #1d4ed8; }
        .badge-assigned { background-color: #e0e7ff; color: #4338ca; }
        .badge-progress { background-color: #fef3c7; color: #b45309; }
        .badge-pending { background-color: #ffedd5; color: #c2410c; }
        .badge-resolved { background-color: #dcfce7; color: #15803d; }
        .badge-closed { background-color: #f1f5f9; color: #475569; }

        /* Form elements for mobile */
        .form-control-mobile {
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.95rem;
            border: 1px solid var(--app-border);
            background-color: #ffffff;
        }

        .form-control-mobile:focus {
            border-color: var(--app-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* Pull to refresh indicator */
        .pull-indicator {
            text-align: center;
            padding: 8px;
            font-size: 0.8rem;
            color: var(--app-muted);
            display: none;
        }
    </style>
</head>
<body>

    <!-- Top Mobile App Header -->
    <header class="mobile-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-logo">
                <i class="bi bi-layers-fill text-primary"></i>
                <span>TaskFlow</span>
            </div>
            <span class="brand-badge">MOBILE</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-dark text-white rounded-circle p-0" style="width:36px; height:36px;" onclick="refreshCurrentTab()" title="Làm mới">
                <i class="bi bi-arrow-clockwise fs-6"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-sm p-0 rounded-circle border-0" type="button" data-bs-toggle="dropdown">
                    <div class="bg-primary text-white rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width:36px; height:36px; font-size:0.9rem;">
                        <?= mb_strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-dark text-truncate" style="max-width:180px;"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></div>
                        <div class="small text-muted font-monospace"><?= htmlspecialchars($user['username'] ?? '') ?></div>
                        <span class="badge bg-primary-subtle text-primary mt-1"><?= htmlspecialchars($user['role'] ?? 'USER') ?></span>
                    </li>
                    <li><a class="dropdown-item py-2" href="<?= $baseUrl ?? '' ?>/dashboard"><i class="bi bi-display me-2 text-primary"></i> Về Giao Diện Web PC</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="<?= $baseUrl ?? '' ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> Đăng Xuất</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- SCREEN 1: TRANG CHỦ (HOME DASHBOARD)                                     -->
    <!-- ========================================================================= -->
    <main id="screen-home" class="app-screen active">
        <!-- User Greeting Card -->
        <div class="card-custom p-3 bg-gradient" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-white-50 small mb-1">Xin chào bạn 👋</div>
                    <h5 class="fw-bold mb-0 text-white"><?= htmlspecialchars($user['full_name'] ?? 'Người dùng') ?></h5>
                    <div class="small text-info mt-1"><i class="bi bi-building me-1"></i> <?= htmlspecialchars($user['department'] ?? 'Doanh nghiệp') ?></div>
                </div>
                <button class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 font-monospace" style="font-size:0.75rem;" onclick="switchTab('screen-scanner')">
                    <i class="bi bi-qr-code me-1"></i> Quét QR
                </button>
            </div>
        </div>

        <!-- KPI Stats Grid -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="card-custom stat-card bg-white" onclick="switchTab('screen-tickets', 'OPEN')">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark"><?= (int)($stats['open_count'] ?? 0) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;">Đang Xử Lý</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card-custom stat-card bg-white" onclick="switchTab('screen-approvals')">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark"><?= count($pendingApprovals ?? []) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;">Cần Duyệt</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card-custom stat-card bg-white" onclick="switchTab('screen-tickets', 'CLOSED')">
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark"><?= (int)($stats['closed_count'] ?? 0) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;">Hoàn Thành</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card-custom stat-card bg-white" onclick="switchTab('screen-scanner')">
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 text-dark"><?= (int)($stats['my_asset_count'] ?? 0) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;">Thiết Bị Của Tôi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Row -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <h6 class="fw-bold mb-0 text-dark">Thao Tác Nhanh</h6>
            </div>
            <div class="row g-2">
                <div class="col-3">
                    <div class="quick-action-btn" onclick="openCreateTicketModal('INCIDENT')">
                        <div class="quick-action-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>
                        <div class="fw-bold text-dark" style="font-size:0.72rem;">Báo Sự Cố</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="quick-action-btn" onclick="openCreateTicketModal('SERVICE_REQUEST')">
                        <div class="quick-action-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <div class="fw-bold text-dark" style="font-size:0.72rem;">Xin Cấp Phát</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="quick-action-btn" onclick="switchTab('screen-scanner')">
                        <div class="quick-action-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <div class="fw-bold text-dark" style="font-size:0.72rem;">Quét Mã QR</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="quick-action-btn" onclick="switchTab('screen-approvals')">
                        <div class="quick-action-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <div class="fw-bold text-dark" style="font-size:0.72rem;">Phê Duyệt</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tickets Section -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <h6 class="fw-bold mb-0 text-dark">Yêu Cầu Gần Đây</h6>
                <a href="javascript:void(0)" onclick="switchTab('screen-tickets')" class="text-primary text-decoration-none small fw-bold">Xem tất cả <i class="bi bi-chevron-right"></i></a>
            </div>

            <div id="homeRecentTicketList">
                <?php if (!empty($recentTickets)): ?>
                    <?php foreach (array_slice($recentTickets, 0, 5) as $tk): ?>
                        <div class="card-custom p-3" onclick="openTicketDetail(<?= (int)$tk['id'] ?>)">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary-subtle text-secondary font-monospace fw-bold">#<?= htmlspecialchars($tk['ticket_code']) ?></span>
                                <?= renderMobileStatusBadge($tk['status']) ?>
                            </div>
                            <div class="fw-bold text-dark text-truncate mb-1" style="font-size:0.95rem;"><?= htmlspecialchars($tk['title']) ?></div>
                            <div class="d-flex justify-content-between align-items-center text-muted" style="font-size:0.75rem;">
                                <span><i class="bi bi-tag me-1"></i><?= htmlspecialchars($tk['category_name'] ?? 'IT') ?></span>
                                <span><i class="bi bi-clock me-1"></i><?= date('d/m H:i', strtotime($tk['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card-custom p-4 text-center text-muted">
                        <i class="bi bi-inbox fs-1 mb-2 text-secondary"></i>
                        <div class="small">Chưa có yêu cầu nào được tạo</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 2: DANH SÁCH YÊU CẦU (TICKETS LIST)                               -->
    <!-- ========================================================================= -->
    <main id="screen-tickets" class="app-screen">
        <!-- Search bar -->
        <div class="card-custom p-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-search text-muted ms-2"></i>
            <input type="text" id="ticketSearchInput" class="form-control border-0 shadow-none p-1" style="font-size:0.9rem;" placeholder="Tìm theo mã vé hoặc tiêu đề..." oninput="debounceTicketSearch()">
        </div>

        <!-- Filter Chips -->
        <div class="chip-scroll mb-3">
            <button class="chip-btn active" data-status="ALL" onclick="filterTickets(this, 'ALL')">Tất Cả</button>
            <button class="chip-btn" data-status="OPEN" onclick="filterTickets(this, 'OPEN')">Đang Xử Lý</button>
            <button class="chip-btn" data-status="PENDING_APPROVAL" onclick="filterTickets(this, 'PENDING_APPROVAL')">Chờ Phê Duyệt</button>
            <button class="chip-btn" data-status="CLOSED" onclick="filterTickets(this, 'CLOSED')">Đã Đóng</button>
        </div>

        <!-- Dynamic Tickets Container -->
        <div id="ticketsContainer">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <div class="small mt-2">Đang tải danh sách...</div>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 3: TRUNG TÂM PHÊ DUYỆT (APPROVALS)                                 -->
    <!-- ========================================================================= -->
    <main id="screen-approvals" class="app-screen">
        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <h6 class="fw-bold mb-0 text-dark">Phiếu Cần Phê Duyệt</h6>
            <span class="badge bg-danger rounded-pill px-2" id="approvalCountBadge">0 phiếu</span>
        </div>

        <div id="approvalsContainer">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <div class="small mt-2">Đang kiểm tra yêu cầu duyệt...</div>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 4: QUÉT MÃ QR THIẾT BỊ (ASSET SCANNER)                            -->
    <!-- ========================================================================= -->
    <main id="screen-scanner" class="app-screen">
        <div class="card-custom p-3 mb-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-camera-fill text-primary me-2"></i> Camera Quét QR</h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-2" id="btnToggleFlash" onclick="toggleFlashlight()" style="display:none;">
                        <i class="bi bi-lightning-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-2" onclick="switchCamera()">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
            </div>
            <p class="text-muted small mb-3">Hướng camera vào tem QR dán trên máy tính hoặc thiết bị để tra cứu thông tin.</p>

            <!-- Video Viewfinder Frame -->
            <div class="scanner-container mb-3" id="scannerWrapper">
                <div id="qr-reader"></div>
                <div class="scan-overlay">
                    <div class="scan-target-box">
                        <div class="scan-laser-line"></div>
                    </div>
                    <div class="text-white small mt-3 fw-bold bg-dark bg-opacity-75 px-3 py-1 rounded-pill" id="scanStatusText">
                        Đang quét mã QR...
                    </div>
                </div>
            </div>

            <!-- Alternative manual input & file upload -->
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input type="text" id="manualAssetCodeInput" class="form-control form-control-sm font-monospace" placeholder="Nhập mã (vd: AST-000001)">
                    <button class="btn btn-primary btn-sm px-3 fw-bold" onclick="lookupManualAsset()">Tra cứu</button>
                </div>
            </div>
            <div class="text-center mt-3">
                <label for="qrFileInput" class="text-primary small fw-bold cursor-pointer">
                    <i class="bi bi-image me-1"></i> Chọn ảnh chứa mã QR từ thư viện ảnh
                </label>
                <input type="file" id="qrFileInput" accept="image/*" class="d-none" onchange="scanQRFromImage(event)">
            </div>
        </div>

        <!-- Scanned Result Card (Hidden until scan) -->
        <div id="scannedAssetResult"></div>
    </main>

    <!-- ========================================================================= -->
    <!-- MODAL 1: TẠO YÊU CẦU MỚI (CREATE TICKET MODAL)                            -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalCreateTicket" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-handle"></div>
                <div class="modal-header border-0 pt-0 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="createTicketTitle">Gửi Yêu Cầu Hỗ Trợ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1">
                    <form id="mobileCreateTicketForm" onsubmit="submitMobileTicket(event)">
                        <!-- Type selector chips -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Loại yêu cầu:</label>
                            <div class="d-flex gap-2">
                                <label class="btn btn-outline-danger flex-fill active py-2 rounded-3 text-start small fw-bold" id="labelTypeIncident">
                                    <input type="radio" name="ticket_type" value="INCIDENT" class="d-none" checked onchange="toggleTicketType('INCIDENT')">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> Báo Sự Cố
                                </label>
                                <label class="btn btn-outline-primary flex-fill py-2 rounded-3 text-start small fw-bold" id="labelTypeRequest">
                                    <input type="radio" name="ticket_type" value="SERVICE_REQUEST" class="d-none" onchange="toggleTicketType('SERVICE_REQUEST')">
                                    <i class="bi bi-box-seam-fill me-1"></i> Dịch Vụ Mới
                                </label>
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Tiêu đề yêu cầu <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="formTicketTitle" class="form-control form-control-mobile" placeholder="Tóm tắt ngắn gọn sự cố..." required>
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Danh mục sự cố / dịch vụ <span class="text-danger">*</span></label>
                            <select name="category_id" id="formTicketCategory" class="form-select form-control-mobile" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Mức độ ưu tiên</label>
                            <select name="priority_id" id="formTicketPriority" class="form-select form-control-mobile">
                                <?php foreach ($priorities as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>" <?= ($p['level'] === 'MEDIUM' || $p['id'] == 3) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Linked Asset -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Thiết bị gặp sự cố (Tùy chọn)</label>
                            <select name="asset_id" id="formTicketAsset" class="form-select form-control-mobile font-monospace">
                                <option value="">-- Không đính kèm thiết bị --</option>
                                <?php foreach ($userAssets as $ast): ?>
                                    <option value="<?= (int)$ast['id'] ?>">
                                        <?= htmlspecialchars($ast['asset_code'] . ' - ' . $ast['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Mô tả chi tiết <span class="text-danger">*</span></label>
                            <textarea name="description" id="formTicketDescription" rows="4" class="form-control form-control-mobile" placeholder="Mô tả cụ thể hiện tượng gặp phải để kỹ thuật viên hỗ trợ nhanh nhất..." required></textarea>
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Vị trí / Bàn làm việc</label>
                            <input type="text" name="location" class="form-control form-control-mobile" placeholder="Ví dụ: Tầng 3, Phòng Kế Toán..." value="<?= htmlspecialchars($user['department'] ?? '') ?>">
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="btnSubmitMobileTicket" class="btn btn-primary w-100 py-3 fw-bold rounded-4 shadow mb-2">
                            <i class="bi bi-send-fill me-2"></i> Gửi Yêu Cầu Hỗ Trợ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: CHI TIẾT TICKET (TICKET DETAIL MODAL)                             -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalTicketDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-handle"></div>
                <div class="modal-header border-0 pt-0 px-4">
                    <div>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace" id="detailTicketCode">#CODE</span>
                        <h5 class="modal-title fw-bold text-dark mt-1" id="detailTicketTitle">Tiêu Đề Phiếu</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1" id="detailTicketBody">
                    <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: KẾT QUẢ QUÉT THIẾT BỊ (ASSET DETAIL MODAL)                       -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalAssetDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-handle"></div>
                <div class="modal-header border-0 pt-0 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-info bg-opacity-10 text-info rounded-circle">
                            <i class="bi bi-laptop fs-4"></i>
                        </div>
                        <div>
                            <span class="badge bg-info-subtle text-info font-monospace fw-bold" id="assetDetailCode">AST-000000</span>
                            <h5 class="modal-title fw-bold text-dark mt-1" id="assetDetailName">Tên Thiết Bị</h5>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1" id="assetDetailBody">
                    <!-- Dynamic details loaded via JS -->
                </div>
                <div class="modal-footer border-0 p-3 bg-light d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger flex-fill fw-bold rounded-pill" id="btnReportAssetIncident">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Báo Sự Cố Máy Này
                    </button>
                    <button type="button" class="btn btn-secondary px-4 fw-bold rounded-pill" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom App Navigation Bar -->
    <nav class="mobile-nav">
        <div class="nav-tab active" onclick="switchTab('screen-home')" id="tab-home">
            <i class="bi bi-house-door-fill"></i>
            <span>Trang chủ</span>
        </div>
        <div class="nav-tab" onclick="switchTab('screen-tickets')" id="tab-tickets">
            <i class="bi bi-ticket-detailed-fill"></i>
            <span>Yêu cầu</span>
        </div>
        <div class="nav-center-btn" onclick="openCreateTicketModal('INCIDENT')">
            <i class="bi bi-plus-lg"></i>
        </div>
        <div class="nav-tab" onclick="switchTab('screen-approvals')" id="tab-approvals">
            <i class="bi bi-patch-check-fill"></i>
            <span>Phê duyệt</span>
            <?php $pendingCount = count($pendingApprovals ?? []); if ($pendingCount > 0): ?>
                <span class="badge-counter" id="navApprovalBadge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </div>
        <div class="nav-tab" onclick="switchTab('screen-scanner')" id="tab-scanner">
            <i class="bi bi-qr-code-scan"></i>
            <span>Quét QR</span>
        </div>
    </nav>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const BASE_URL = '<?= $baseUrl ?? '' ?>';
        const CSRF_TOKEN = '<?= \App\Core\Session::csrfToken() ?>';

        let currentScreen = 'screen-home';
        let currentTicketFilter = 'ALL';
        let searchTimer = null;
        let html5QrCode = null;
        let isScannerRunning = false;
        let currentCameraFacing = "environment"; // default back camera
        let lastScannedAsset = null;

        // =========================================================================
        // NAVIGATION & TAB SWITCHING
        // =========================================================================
        function switchTab(screenId, optionalParam) {
            // Stop scanner if leaving scanner screen
            if (currentScreen === 'screen-scanner' && screenId !== 'screen-scanner') {
                stopScanner();
            }

            document.querySelectorAll('.app-screen').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(el => el.classList.remove('active'));

            const targetScreen = document.getElementById(screenId);
            if (targetScreen) targetScreen.classList.add('active');

            const tabMap = {
                'screen-home': 'tab-home',
                'screen-tickets': 'tab-tickets',
                'screen-approvals': 'tab-approvals',
                'screen-scanner': 'tab-scanner'
            };
            const activeTab = document.getElementById(tabMap[screenId]);
            if (activeTab) activeTab.classList.add('active');

            currentScreen = screenId;
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Screen specific initialization
            if (screenId === 'screen-tickets') {
                if (optionalParam) {
                    currentTicketFilter = optionalParam;
                    document.querySelectorAll('.chip-btn').forEach(btn => {
                        btn.classList.toggle('active', btn.dataset.status === optionalParam);
                    });
                }
                loadTickets();
            } else if (screenId === 'screen-approvals') {
                loadApprovals();
            } else if (screenId === 'screen-scanner') {
                startScanner();
            }
        }

        function refreshCurrentTab() {
            if (currentScreen === 'screen-home') {
                window.location.reload();
            } else if (currentScreen === 'screen-tickets') {
                loadTickets();
            } else if (currentScreen === 'screen-approvals') {
                loadApprovals();
            } else if (currentScreen === 'screen-scanner') {
                startScanner();
            }
        }

        // =========================================================================
        // TICKETS MANAGEMENT
        // =========================================================================
        function loadTickets() {
            const container = document.getElementById('ticketsContainer');
            const search = document.getElementById('ticketSearchInput') ? document.getElementById('ticketSearchInput').value.trim() : '';

            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <div class="small mt-2">Đang tải danh sách...</div>
                </div>
            `;

            fetch(`${BASE_URL}/mobile/api/tickets?status=${encodeURIComponent(currentTicketFilter)}&search=${encodeURIComponent(search)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.tickets || data.tickets.length === 0) {
                        container.innerHTML = `
                            <div class="card-custom p-5 text-center text-muted">
                                <i class="bi bi-inbox fs-1 mb-2 text-secondary"></i>
                                <div class="fw-bold">Không tìm thấy yêu cầu nào</div>
                                <div class="small text-muted mt-1">Thử thay đổi bộ lọc hoặc tạo phiếu mới.</div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.tickets.forEach(tk => {
                        const statusBadge = getStatusBadgeHtml(tk.status);
                        const priorityColor = tk.priority_color || '#3b82f6';
                        html += `
                            <div class="card-custom p-3" onclick="openTicketDetail(${tk.id})">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary-subtle text-secondary font-monospace fw-bold">#${tk.ticket_code}</span>
                                        <span class="badge" style="background-color:${priorityColor}20; color:${priorityColor}; border:1px solid ${priorityColor}40; font-size:0.68rem;">
                                            ${tk.priority_name || 'Trung bình'}
                                        </span>
                                    </div>
                                    ${statusBadge}
                                </div>
                                <div class="fw-bold text-dark text-truncate mb-1" style="font-size:0.95rem;">${escapeHtml(tk.title)}</div>
                                <div class="d-flex justify-content-between align-items-center text-muted mt-2" style="font-size:0.75rem;">
                                    <span><i class="bi bi-tag me-1"></i>${escapeHtml(tk.category_name || 'Sự cố IT')}</span>
                                    <span><i class="bi bi-clock me-1"></i>${formatDate(tk.created_at)}</span>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    container.innerHTML = `<div class="alert alert-danger small m-2">Lỗi tải dữ liệu: ${err.message}</div>`;
                });
        }

        function filterTickets(btn, status) {
            document.querySelectorAll('.chip-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTicketFilter = status;
            loadTickets();
        }

        function debounceTicketSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadTickets, 350);
        }

        function openTicketDetail(ticketId) {
            const modalEl = document.getElementById('modalTicketDetail');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const body = document.getElementById('detailTicketBody');

            document.getElementById('detailTicketCode').textContent = `Đang tải...`;
            document.getElementById('detailTicketTitle').textContent = `Chi tiết yêu cầu #${ticketId}`;
            body.innerHTML = `<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>`;
            modal.show();

            fetch(`${BASE_URL}/mobile/api/tickets/${ticketId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.ticket) {
                        body.innerHTML = `<div class="alert alert-danger small">Không thể tải thông tin phiếu.</div>`;
                        return;
                    }
                    const tk = data.ticket;
                    document.getElementById('detailTicketCode').textContent = `#${tk.ticket_code}`;
                    document.getElementById('detailTicketTitle').textContent = tk.title;

                    let checklistHtml = '';
                    if (data.checklists && data.checklists.length > 0) {
                        checklistHtml = `<div class="mt-3"><div class="fw-bold small text-dark mb-2">Đầu việc kỹ thuật (Checklist):</div>`;
                        data.checklists.forEach(c => {
                            checklistHtml += `
                                <div class="d-flex align-items-center gap-2 small py-1">
                                    <i class="bi ${c.is_done ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'}"></i>
                                    <span class="${c.is_done ? 'text-decoration-line-through text-muted' : ''}">${escapeHtml(c.item_text)}</span>
                                </div>
                            `;
                        });
                        checklistHtml += `</div>`;
                    }

                    body.innerHTML = `
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            ${getStatusBadgeHtml(tk.status)}
                            <span class="text-muted small"><i class="bi bi-clock me-1"></i>${formatDate(tk.created_at)}</span>
                        </div>
                        <div class="p-3 bg-light rounded-3 mb-3 text-dark small" style="white-space: pre-wrap;">
                            ${escapeHtml(tk.description || 'Không có mô tả chi tiết.')}
                        </div>
                        <div class="row g-2 mb-3 text-muted small">
                            <div class="col-6"><strong>Người yêu cầu:</strong><br>${escapeHtml(tk.requester_name || 'N/A')}</div>
                            <div class="col-6"><strong>Kỹ thuật viên:</strong><br>${escapeHtml(tk.assignee_name || 'Chưa phân công')}</div>
                            <div class="col-6 mt-2"><strong>Mức độ ưu tiên:</strong><br>${escapeHtml(tk.priority_name || 'Bình thường')}</div>
                            <div class="col-6 mt-2"><strong>Vị trí:</strong><br>${escapeHtml(tk.location || 'Văn phòng')}</div>
                        </div>
                        ${checklistHtml}
                    `;
                })
                .catch(err => {
                    body.innerHTML = `<div class="alert alert-danger small">Lỗi kết nối: ${err.message}</div>`;
                });
        }

        // =========================================================================
        // CREATE TICKET (GỬI YÊU CẦU MỚI)
        // =========================================================================
        function openCreateTicketModal(type = 'INCIDENT', prefillAssetId = null) {
            toggleTicketType(type);
            if (prefillAssetId) {
                const assetSelect = document.getElementById('formTicketAsset');
                if (assetSelect) assetSelect.value = prefillAssetId;
            }
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCreateTicket'));
            modal.show();
        }

        function toggleTicketType(type) {
            const btnInc = document.getElementById('labelTypeIncident');
            const btnReq = document.getElementById('labelTypeRequest');
            const title = document.getElementById('createTicketTitle');

            if (type === 'INCIDENT') {
                btnInc.classList.add('active');
                btnReq.classList.remove('active');
                title.textContent = 'Báo Cáo Sự Cố (Incident)';
                document.getElementById('formTicketTitle').placeholder = 'Ví dụ: Không kết nối được máy in tầng 2...';
            } else {
                btnReq.classList.add('active');
                btnInc.classList.remove('active');
                title.textContent = 'Yêu Cầu Dịch Vụ Mới';
                document.getElementById('formTicketTitle').placeholder = 'Ví dụ: Xin cấp quyền truy cập tài liệu kế toán...';
            }
        }

        function submitMobileTicket(e) {
            e.preventDefault();
            const form = document.getElementById('mobileCreateTicketForm');
            const btn = document.getElementById('btnSubmitMobileTicket');

            const payload = {
                ticket_type: form.querySelector('input[name="ticket_type"]:checked').value,
                title: form.title.value.trim(),
                category_id: form.category_id.value,
                priority_id: form.priority_id.value,
                asset_id: form.asset_id.value || null,
                description: form.description.value.trim(),
                location: form.location.value.trim(),
                _csrf_token: CSRF_TOKEN
            };

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang gửi yêu cầu...';

            fetch(`${BASE_URL}/mobile/api/tickets/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill me-2"></i> Gửi Yêu Cầu Hỗ Trợ';

                if (data.success) {
                    alert(data.message || 'Tạo yêu cầu thành công!');
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('modalCreateTicket')).hide();
                    switchTab('screen-tickets', 'ALL');
                } else {
                    alert(data.message || 'Lỗi tạo yêu cầu.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill me-2"></i> Gửi Yêu Cầu Hỗ Trợ';
                alert('Lỗi kết nối máy chủ: ' + err.message);
            });
        }

        // =========================================================================
        // APPROVALS (TRUNG TÂM PHÊ DUYỆT)
        // =========================================================================
        function loadApprovals() {
            const container = document.getElementById('approvalsContainer');
            const badge = document.getElementById('approvalCountBadge');
            const navBadge = document.getElementById('navApprovalBadge');

            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <div class="small mt-2">Đang tải danh sách phê duyệt...</div>
                </div>
            `;

            fetch(`${BASE_URL}/mobile/api/approvals?status=PENDING`)
                .then(res => res.json())
                .then(data => {
                    const approvals = data.approvals || [];
                    const count = approvals.length;
                    if (badge) badge.textContent = `${count} phiếu`;
                    if (navBadge) {
                        navBadge.textContent = count;
                        navBadge.style.display = count > 0 ? 'inline-block' : 'none';
                    }

                    if (count === 0) {
                        container.innerHTML = `
                            <div class="card-custom p-5 text-center text-muted">
                                <i class="bi bi-check-circle fs-1 mb-2 text-success"></i>
                                <div class="fw-bold">Bạn không có yêu cầu nào cần duyệt</div>
                                <div class="small text-muted mt-1">Toàn bộ phiếu đã được xử lý hoàn tất.</div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    approvals.forEach(ap => {
                        html += `
                            <div class="card-custom p-3" id="approvalCard-${ap.id}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-warning-subtle text-warning font-monospace fw-bold">CHỜ DUYỆT</span>
                                    <span class="text-muted small"><i class="bi bi-clock me-1"></i>${formatDate(ap.created_at)}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">${escapeHtml(ap.title || ap.step_name || 'Phê duyệt dịch vụ')}</h6>
                                <p class="text-muted small mb-2">${escapeHtml(ap.description || 'Không có mô tả thêm')}</p>
                                <div class="small text-muted mb-3">
                                    <i class="bi bi-person me-1"></i> Người yêu cầu: <strong>${escapeHtml(ap.requester_name || 'Nhân viên')}</strong>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-danger flex-fill py-2 fw-bold rounded-pill text-sm" onclick="decideApproval(${ap.id}, 'REJECTED')">
                                        <i class="bi bi-x-circle me-1"></i> Từ Chối
                                    </button>
                                    <button class="btn btn-success flex-fill py-2 fw-bold rounded-pill text-sm shadow-sm" onclick="decideApproval(${ap.id}, 'APPROVED')">
                                        <i class="bi bi-check-circle-fill me-1"></i> Phê Duyệt
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    container.innerHTML = `<div class="alert alert-danger small m-2">Lỗi: ${err.message}</div>`;
                });
        }

        function decideApproval(approvalId, decision) {
            const comment = decision === 'REJECTED' 
                ? prompt('Nhập lý do từ chối (bắt buộc):') 
                : prompt('Ghi chú phê duyệt (tùy chọn):', '');

            if (decision === 'REJECTED' && (comment === null || comment.trim() === '')) {
                alert('Bạn cần cung cấp lý do từ chối.');
                return;
            }

            fetch(`${BASE_URL}/mobile/api/approvals/decide`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ approval_id: approvalId, decision: decision, comment: comment || '' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playSuccessBeep();
                    const card = document.getElementById(`approvalCard-${approvalId}`);
                    if (card) {
                        card.style.opacity = '0.4';
                        card.style.pointerEvents = 'none';
                    }
                    setTimeout(loadApprovals, 500);
                } else {
                    alert(data.message || 'Lỗi xử lý phê duyệt.');
                }
            })
            .catch(err => alert('Lỗi kết nối: ' + err.message));
        }

        // =========================================================================
        // QR CODE SCANNER (CAMERA & IMAGE PARSER)
        // =========================================================================
        function startScanner() {
            if (isScannerRunning) return;
            const statusEl = document.getElementById('scanStatusText');
            if (statusEl) statusEl.textContent = 'Đang khởi động camera...';

            if (typeof Html5Qrcode === 'undefined') {
                if (statusEl) statusEl.textContent = 'Thư viện QR chưa sẵn sàng. Vui lòng nhập mã tay.';
                return;
            }

            try {
                html5QrCode = new Html5Qrcode("qr-reader");
                const config = { fps: 15, qrbox: { width: 220, height: 220 } };

                html5QrCode.start(
                    { facingMode: currentCameraFacing },
                    config,
                    onQrCodeScanned,
                    onQrScanError
                ).then(() => {
                    isScannerRunning = true;
                    if (statusEl) statusEl.textContent = 'Hướng camera vào mã QR thiết bị...';
                }).catch(err => {
                    console.warn("Camera start error:", err);
                    if (statusEl) statusEl.textContent = 'Không mở được camera. Dùng ô nhập mã bên dưới.';
                });
            } catch (e) {
                console.error(e);
            }
        }

        function stopScanner() {
            if (html5QrCode && isScannerRunning) {
                html5QrCode.stop().then(() => {
                    isScannerRunning = false;
                }).catch(err => console.warn(err));
            }
        }

        function switchCamera() {
            stopScanner();
            currentCameraFacing = currentCameraFacing === "environment" ? "user" : "environment";
            setTimeout(startScanner, 300);
        }

        function toggleFlashlight() {
            // Flashlight support if torch constraint available
        }

        function onQrCodeScanned(decodedText, decodedResult) {
            playSuccessBeep();
            if (navigator.vibrate) navigator.vibrate([80, 50, 80]);

            const statusEl = document.getElementById('scanStatusText');
            if (statusEl) statusEl.textContent = `Đã tìm thấy: ${decodedText}`;

            lookupAssetByCode(decodedText);
        }

        function onQrScanError(errorMessage) {
            // silent parse frame errors
        }

        function scanQRFromImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (typeof Html5Qrcode === 'undefined') {
                alert('Thư viện quét chưa tải xong.');
                return;
            }

            const qr = new Html5Qrcode("qr-reader");
            qr.scanFile(file, true)
                .then(decodedText => {
                    playSuccessBeep();
                    lookupAssetByCode(decodedText);
                })
                .catch(err => {
                    alert('Không tìm thấy mã QR hợp lệ trong ảnh: ' + err);
                });
        }

        function lookupManualAsset() {
            const code = document.getElementById('manualAssetCodeInput').value.trim();
            if (!code) {
                alert('Vui lòng nhập mã thiết bị (vd: AST-000001).');
                return;
            }
            lookupAssetByCode(code);
        }

        function lookupAssetByCode(code) {
            const modalEl = document.getElementById('modalAssetDetail');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const body = document.getElementById('assetDetailBody');

            document.getElementById('assetDetailCode').textContent = code;
            document.getElementById('assetDetailName').textContent = 'Đang tra cứu thiết bị...';
            body.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><div class="small mt-2">Đang lấy dữ liệu từ máy chủ...</div></div>`;
            modal.show();

            fetch(`${BASE_URL}/mobile/api/asset-scan/${encodeURIComponent(code)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.asset) {
                        body.innerHTML = `
                            <div class="alert alert-warning small text-center p-3">
                                <i class="bi bi-question-circle fs-3 d-block mb-2 text-warning"></i>
                                <strong>Không tìm thấy thiết bị!</strong><br>
                                Mã QR: <span class="font-monospace">${escapeHtml(code)}</span> không tồn tại trong hệ thống.
                            </div>
                        `;
                        return;
                    }

                    const a = data.asset;
                    lastScannedAsset = a;
                    document.getElementById('assetDetailCode').textContent = a.asset_code;
                    document.getElementById('assetDetailName').textContent = a.name;

                    // Configure report button
                    const btnReport = document.getElementById('btnReportAssetIncident');
                    if (btnReport) {
                        btnReport.onclick = function() {
                            modal.hide();
                            openCreateTicketModal('INCIDENT', a.id);
                        };
                    }

                    body.innerHTML = `
                        <div class="p-3 bg-light rounded-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-subtle text-primary fw-bold font-monospace">${escapeHtml(a.asset_code)}</span>
                                <span class="badge bg-success-subtle text-success fw-bold">${escapeHtml(a.status || 'Đang sử dụng')}</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">${escapeHtml(a.name)}</h6>
                            <div class="text-muted small"><i class="bi bi-tag me-1"></i> ${escapeHtml(a.category_name || 'Thiết bị IT')}</div>
                        </div>

                        <div class="card-custom p-3 mb-2">
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted">Model / Nhãn hiệu:</span><br>
                                    <strong class="text-dark">${escapeHtml(a.model || 'Chưa cập nhật')}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Số Serial:</span><br>
                                    <strong class="text-dark font-monospace">${escapeHtml(a.serial_number || 'N/A')}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted">Người đang sử dụng:</span><br>
                                    <strong class="text-primary">${escapeHtml(a.current_user_name || 'Chưa bàn giao')}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted">Vị trí thiết bị:</span><br>
                                    <strong class="text-dark">${escapeHtml(a.location || 'Tại văn phòng')}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .catch(err => {
                    body.innerHTML = `<div class="alert alert-danger small">Lỗi tra cứu: ${err.message}</div>`;
                });
        }

        // =========================================================================
        // UTILITIES & AUDIO
        // =========================================================================
        function playSuccessBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime); // A5 note
                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
            } catch (e) {}
        }

        function getStatusBadgeHtml(status) {
            const map = {
                'NEW': '<span class="badge-status badge-new"><i class="bi bi-dot"></i>Mới</span>',
                'ASSIGNED': '<span class="badge-status badge-assigned"><i class="bi bi-person"></i>Đã phân công</span>',
                'IN_PROGRESS': '<span class="badge-status badge-progress"><i class="bi bi-arrow-repeat"></i>Đang xử lý</span>',
                'PENDING_APPROVAL': '<span class="badge-status badge-pending"><i class="bi bi-shield-exclamation"></i>Chờ duyệt</span>',
                'RESOLVED': '<span class="badge-status badge-resolved"><i class="bi bi-check2"></i>Đã giải quyết</span>',
                'CLOSED': '<span class="badge-status badge-closed"><i class="bi bi-lock"></i>Đã đóng</span>'
            };
            return map[status] || `<span class="badge bg-secondary">${status}</span>`;
        }

        function formatDate(dStr) {
            if (!dStr) return '';
            const d = new Date(dStr);
            if (isNaN(d)) return dStr;
            const pad = (n) => n < 10 ? '0' + n : n;
            return `${pad(d.getDate())}/${pad(d.getMonth() + 1)} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
            });
        }
    </script>
</body>
</html>
