<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle ?? 'TaskFlow') ?></title>
    
    <!-- Native App Meta -->
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="format-detection" content="telephone=no">
    <link rel="icon" type="image/png" href="<?= $baseUrl ?? '' ?>/favicon.ico">
    
    <!-- Modern Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Local & CDN QR Scanner -->
    <script src="<?= $baseUrl ?? '' ?>/public/js/html5-qrcode.min.js"></script>
    <script>
        if (typeof Html5Qrcode === 'undefined') {
            document.write('<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"><\/script>');
        }
    </script>

    <style>
        :root {
            --app-bg: #f8fafc;
            --surface: #ffffff;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --emerald: #10b981;
            --emerald-light: #ecfdf5;
            --amber: #f59e0b;
            --amber-light: #fffbeb;
            --rose: #ef4444;
            --rose-light: #fef2f2;
            --text-main: #0f172a;
            --text-sub: #475569;
            --text-muted: #94a3b8;
            --border-subtle: #f1f5f9;
            --border-card: #e2e8f0;
            --safe-bottom: env(safe-area-inset-bottom, 16px);
            --safe-top: env(safe-area-inset-top, 0px);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            margin: 0;
            padding: 0;
            background-color: var(--app-bg);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'SF Pro Display', Roboto, sans-serif;
            font-size: 16px;
            line-height: 1.45;
            -webkit-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
            user-select: none;
            -webkit-user-select: none;
            padding-bottom: calc(70px + var(--safe-bottom));
        }

        /* Native App Bar */
        .native-appbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 58px;
        }

        .native-appbar-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .native-appbar-title i {
            font-size: 1.3rem;
            color: var(--primary);
        }

        .native-appbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .appbar-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--border-card);
            background: var(--surface);
            color: var(--text-sub);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .appbar-icon-btn:active {
            transform: scale(0.92);
            background: #f1f5f9;
        }

        /* Screen Wrapper */
        .screen-container {
            display: none;
            padding: 14px 16px;
            animation: screenSlide 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .screen-container.active {
            display: block;
        }

        @keyframes screenSlide {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Segmented Control (iOS / Material 3 Pill Switcher) */
        .native-segmented {
            display: flex;
            background: #e2e8f0;
            border-radius: 14px;
            padding: 3px;
            margin-bottom: 14px;
            gap: 2px;
        }

        .segmented-item {
            flex: 1;
            border: none;
            background: transparent;
            border-radius: 11px;
            padding: 9px 4px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-sub);
            text-align: center;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .segmented-item.active {
            background: var(--surface);
            color: var(--text-main);
            font-weight: 800;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        /* Native Search Box */
        .native-search-bar {
            background: var(--surface);
            border: 1.5px solid var(--border-card);
            border-radius: 14px;
            height: 48px;
            padding: 0 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: border-color 0.15s ease;
        }

        .native-search-bar:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .native-search-bar input {
            border: none;
            outline: none;
            background: transparent;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--text-main);
            width: 100%;
        }

        .native-search-bar input::placeholder {
            color: var(--text-muted);
        }

        /* Quick Action Dock (2 Prominent Mobile Action Cards) */
        .action-dock {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .dock-btn {
            background: var(--surface);
            border: 1.5px solid var(--border-card);
            border-radius: 18px;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            min-height: 98px;
        }

        .dock-btn:active {
            transform: scale(0.96);
            background: #f8fafc;
        }

        .dock-btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.25);
        }

        .dock-btn-primary .dock-icon {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .dock-btn-emerald {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(5, 150, 105, 0.25);
        }

        .dock-btn-emerald .dock-icon {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .dock-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 10px;
        }

        .dock-text-title {
            font-size: 0.95rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .dock-text-sub {
            font-size: 0.74rem;
            opacity: 0.85;
        }

        /* Native Ticket Card (Standard App Layout) */
        .native-ticket-card {
            background: var(--surface);
            border: 1px solid var(--border-card);
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.12s ease;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .native-ticket-card:active {
            transform: scale(0.985);
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .ticket-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ticket-code-pill {
            font-family: monospace;
            font-weight: 800;
            font-size: 0.78rem;
            color: var(--text-sub);
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 8px;
        }

        .ticket-card-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1.38;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ticket-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--text-sub);
            margin-top: 4px;
        }

        .ticket-category-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Status Pills (Large & Highly Legible) */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 800;
        }

        .status-pill-new { background: #dbeafe; color: #1e40af; }
        .status-pill-assigned { background: #e0e7ff; color: #3730a3; }
        .status-pill-progress { background: #fef3c7; color: #92400e; }
        .status-pill-pending { background: #ffedd5; color: #9a3412; }
        .status-pill-resolved { background: #dcfce7; color: #166534; }
        .status-pill-closed { background: #f1f5f9; color: #475569; }

        /* Approval Card (Large Native Touch Targets) */
        .native-approval-card {
            background: var(--surface);
            border: 1px solid var(--border-card);
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .approval-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .approval-card-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
            line-height: 1.35;
        }

        .approval-card-desc {
            font-size: 0.88rem;
            color: var(--text-sub);
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 14px;
            line-height: 1.45;
        }

        .approval-btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-native-action {
            height: 48px;
            border-radius: 14px;
            font-size: 0.92rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            border: none;
            transition: transform 0.12s ease;
        }

        .btn-native-action:active {
            transform: scale(0.96);
        }

        .btn-native-reject {
            background: #fee2e2;
            color: #dc2626;
            border: 1.5px solid #fca5a5;
        }

        .btn-native-approve {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 3px 12px rgba(16, 185, 129, 0.3);
        }

        /* Native QR Scanner Viewport */
        .scanner-view-box {
            position: relative;
            background: #000;
            border-radius: 24px;
            overflow: hidden;
            width: 100%;
            height: 350px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
            margin-bottom: 16px;
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

        .scanner-hud {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 220px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            pointer-events: none;
            box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.5);
        }

        .scanner-hud::before, .scanner-hud::after {
            content: '';
            position: absolute;
            width: 28px;
            height: 28px;
            border-color: #10b981;
            border-style: solid;
        }

        .scanner-hud::before {
            top: -2px;
            left: -2px;
            border-width: 4px 0 0 4px;
            border-top-left-radius: 22px;
        }

        .scanner-hud::after {
            bottom: -2px;
            right: -2px;
            border-width: 0 4px 4px 0;
            border-bottom-right-radius: 22px;
        }

        .scanner-laser-line {
            position: absolute;
            left: 12px;
            right: 12px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            box-shadow: 0 0 12px #10b981;
            animation: laserScan 2.2s infinite ease-in-out alternate;
        }

        @keyframes laserScan {
            0% { top: 10%; opacity: 0.2; }
            50% { opacity: 1; }
            100% { top: 90%; opacity: 0.2; }
        }

        /* Native Bottom Navigation Bar */
        .native-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: calc(64px + var(--safe-bottom));
            padding-bottom: var(--safe-bottom);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-top: 1px solid rgba(226, 232, 240, 0.9);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1050;
            box-shadow: 0 -2px 14px rgba(0, 0, 0, 0.04);
        }

        .nav-tab-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 700;
            height: 100%;
            cursor: pointer;
            position: relative;
            transition: color 0.15s ease;
        }

        .nav-tab-item i {
            font-size: 1.45rem;
            margin-bottom: 2px;
            transition: transform 0.15s ease;
        }

        .nav-tab-item.active {
            color: var(--primary);
        }

        .nav-tab-item.active i {
            transform: translateY(-2px);
        }

        .nav-tab-badge {
            position: absolute;
            top: 7px;
            right: 25%;
            background: var(--rose);
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 999px;
            border: 2px solid #ffffff;
        }

        /* Native Floating Action Button (FAB) */
        .native-fab {
            position: fixed;
            bottom: calc(78px + var(--safe-bottom));
            right: 18px;
            width: 58px;
            height: 58px;
            border-radius: 29px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            border: none;
            cursor: pointer;
            z-index: 1040;
            transition: transform 0.12s ease;
        }

        .native-fab:active {
            transform: scale(0.9);
        }

        /* Native Bottom Sheet Modal */
        .modal.bottom-sheet .modal-dialog {
            position: fixed;
            bottom: 0;
            margin: 0;
            width: 100%;
            max-width: 100%;
        }

        .modal.bottom-sheet .modal-content {
            border-radius: 28px 28px 0 0;
            border: none;
            background: var(--surface);
            max-height: 90vh;
            overflow-y: auto;
            padding-bottom: var(--safe-bottom);
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
        }

        .sheet-drag-pill {
            width: 44px;
            height: 5px;
            background-color: #cbd5e1;
            border-radius: 999px;
            margin: 12px auto 16px auto;
        }

        .form-label-native {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-control-native {
            height: 48px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1.5px solid var(--border-card);
            padding: 0 14px;
            font-size: 0.95rem;
            color: var(--text-main);
            transition: border-color 0.15s ease;
        }

        .form-control-native:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-primary-native {
            height: 52px;
            border-radius: 16px;
            background: var(--primary);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 800;
            border: none;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.12s ease;
        }

        .btn-primary-native:active {
            transform: scale(0.97);
        }
    </style>
</head>
<body>

    <!-- Native App Bar -->
    <header class="native-appbar">
        <div class="native-appbar-title" id="appbarTitle">
            <i class="bi bi-layers-fill"></i>
            <span>TaskFlow</span>
        </div>
        <div class="native-appbar-actions">
            <div class="appbar-icon-btn" onclick="openCreateTicketModal('INCIDENT')" title="Gửi yêu cầu">
                <i class="bi bi-plus-lg text-primary fw-bold"></i>
            </div>
            <div class="appbar-icon-btn" onclick="switchTab('screen-profile')" title="Cá nhân">
                <i class="bi bi-person-fill"></i>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- SCREEN 1: YÊU CẦU (TICKETS HUB)                                           -->
    <!-- ========================================================================= -->
    <main id="screen-tickets" class="screen-container active">
        <!-- Action Dock (2 Prominent Buttons) -->
        <div class="action-dock">
            <div class="dock-btn dock-btn-primary" onclick="openCreateTicketModal('INCIDENT')">
                <div class="dock-icon">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <div>
                    <div class="dock-text-title">Gửi Yêu Cầu</div>
                    <div class="dock-text-sub">Báo sự cố & cấp phát</div>
                </div>
            </div>
            <div class="dock-btn dock-btn-emerald" onclick="switchTab('screen-scanner')">
                <div class="dock-icon">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <div>
                    <div class="dock-text-title">Quét QR Thiết Bị</div>
                    <div class="dock-text-sub">Mở máy & báo hỏng</div>
                </div>
            </div>
        </div>

        <!-- Search Input -->
        <div class="native-search-bar">
            <i class="bi bi-search text-muted fs-5"></i>
            <input type="text" id="ticketSearchInput" placeholder="Tìm theo mã số hoặc tiêu đề..." oninput="debounceTicketSearch()">
        </div>

        <!-- Segmented Filter Control -->
        <div class="native-segmented">
            <button class="segmented-item active" data-status="ALL" onclick="filterSegment(this, 'ALL')">Tất Cả</button>
            <button class="segmented-item" data-status="OPEN" onclick="filterSegment(this, 'OPEN')">Đang Xử Lý</button>
            <button class="segmented-item" data-status="PENDING_APPROVAL" onclick="filterSegment(this, 'PENDING_APPROVAL')">Chờ Duyệt</button>
            <button class="segmented-item" data-status="CLOSED" onclick="filterSegment(this, 'CLOSED')">Đã Đóng</button>
        </div>

        <!-- Ticket Cards Container -->
        <div id="ticketsContainer">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <div class="small mt-2">Đang tải danh sách...</div>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 2: PHÊ DUYỆT (APPROVALS)                                           -->
    <!-- ========================================================================= -->
    <main id="screen-approvals" class="screen-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark">Danh Sách Cần Duyệt</h5>
            <span class="badge bg-danger rounded-pill px-3 py-1 font-monospace" id="approvalCountBadge">0</span>
        </div>

        <div id="approvalsContainer">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <div class="small mt-2">Đang kiểm tra danh sách...</div>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 3: QUÉT MÃ QR (SCANNER)                                            -->
    <!-- ========================================================================= -->
    <main id="screen-scanner" class="screen-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Quét Tem QR Thiết Bị</h5>
                <div class="text-muted small">Hướng camera vào mã dán trên máy tính</div>
            </div>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-2 fw-bold" onclick="switchCamera()">
                <i class="bi bi-arrow-repeat me-1"></i> Đổi Camera
            </button>
        </div>

        <!-- Camera Frame -->
        <div class="scanner-view-box" id="scannerWrapper">
            <div id="qr-reader"></div>
            <div class="scanner-hud">
                <div class="scanner-laser-line"></div>
            </div>
        </div>

        <!-- Quick Input & Gallery Actions -->
        <div class="d-flex gap-2 mb-3">
            <input type="text" id="manualAssetCodeInput" class="form-control form-control-native font-monospace" placeholder="Mã máy (vd: AST-000001)">
            <button class="btn btn-primary px-4 fw-bold rounded-3" onclick="lookupManualAsset()" style="white-space:nowrap; height:48px;">Tra cứu</button>
        </div>

        <label for="qrFileInput" class="w-100 btn btn-light border py-3 fw-bold rounded-3 text-primary d-flex align-items-center justify-content-center gap-2 cursor-pointer">
            <i class="bi bi-image fs-5"></i> Chọn ảnh chứa mã QR từ thư viện
        </label>
        <input type="file" id="qrFileInput" accept="image/*" class="d-none" onchange="scanQRFromImage(event)">
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 4: CÁ NHÂN & CÀI ĐẶT                                               -->
    <!-- ========================================================================= -->
    <main id="screen-profile" class="screen-container">
        <!-- User Info Card -->
        <div class="bg-white border rounded-4 p-4 text-center mb-3 shadow-sm">
            <div class="mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width:68px; height:68px;">
                <?= mb_strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)) ?>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user['full_name'] ?? 'Người dùng') ?></h5>
            <div class="text-muted small font-monospace mb-2"><?= htmlspecialchars($user['username'] ?? '') ?></div>
            <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill"><?= htmlspecialchars($user['role'] ?? 'USER') ?></span>
            <div class="small text-muted mt-2"><i class="bi bi-building me-1"></i><?= htmlspecialchars($user['department'] ?? 'Doanh nghiệp') ?></div>
        </div>

        <!-- Action Links -->
        <div class="bg-white border rounded-4 p-2 shadow-sm mb-3">
            <a href="<?= $baseUrl ?? '' ?>/TaskFlow-v1.2.apk" download class="d-flex align-items-center justify-content-between p-3 text-decoration-none text-dark border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3"><i class="bi bi-android2 fs-4"></i></div>
                    <div>
                        <div class="fw-bold">Cập Nhật APK v1.2</div>
                        <div class="text-muted small">Tải gói cài đặt Android mới nhất</div>
                    </div>
                </div>
                <i class="bi bi-download text-muted fs-5"></i>
            </a>
            <a href="<?= $baseUrl ?? '' ?>/dashboard" class="d-flex align-items-center justify-content-between p-3 text-decoration-none text-dark border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3"><i class="bi bi-display fs-4"></i></div>
                    <div>
                        <div class="fw-bold">Giao Diện Web Máy Tính</div>
                        <div class="text-muted small">Mở bảng điều khiển quản trị PC</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted fs-5"></i>
            </a>
            <a href="<?= $baseUrl ?? '' ?>/logout" class="d-flex align-items-center justify-content-between p-3 text-decoration-none text-danger">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-danger bg-opacity-10 text-danger rounded-3"><i class="bi bi-box-arrow-right fs-4"></i></div>
                    <div class="fw-bold">Đăng Xuất Tài Khoản</div>
                </div>
                <i class="bi bi-chevron-right text-muted fs-5"></i>
            </a>
        </div>
    </main>

    <!-- Floating Action Button for Instant Creation -->
    <button class="native-fab" onclick="openCreateTicketModal('INCIDENT')" title="Tạo yêu cầu mới">
        <i class="bi bi-plus-lg"></i>
    </button>

    <!-- ========================================================================= -->
    <!-- NATIVE BOTTOM SHEET 1: FORM GỬI YÊU CẦU MỚI                               -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalCreateTicket" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-pill"></div>
                <div class="modal-header border-0 pt-0 px-4 pb-2">
                    <h5 class="modal-title fw-bold text-dark" id="createTicketTitle">Gửi Yêu Cầu Hỗ Trợ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1">
                    <form id="mobileCreateTicketForm" onsubmit="submitMobileTicket(event)">
                        <!-- Type selector pills -->
                        <div class="d-flex gap-2 mb-3">
                            <label class="btn btn-outline-danger flex-fill active py-3 rounded-3 text-start fw-bold" id="labelTypeIncident" style="min-height:50px;">
                                <input type="radio" name="ticket_type" value="INCIDENT" class="d-none" checked onchange="toggleTicketType('INCIDENT')">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i> Báo Sự Cố
                            </label>
                            <label class="btn btn-outline-primary flex-fill py-3 rounded-3 text-start fw-bold" id="labelTypeRequest" style="min-height:50px;">
                                <input type="radio" name="ticket_type" value="SERVICE_REQUEST" class="d-none" onchange="toggleTicketType('SERVICE_REQUEST')">
                                <i class="bi bi-box-seam-fill me-1"></i> Cấp Thiết Bị
                            </label>
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label-native">Tiêu đề yêu cầu <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="formTicketTitle" class="form-control form-control-native" placeholder="Tóm tắt ngắn gọn vấn đề..." required>
                        </div>

                        <!-- Category & Priority Row -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label-native">Danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" id="formTicketCategory" class="form-select form-control-native" required>
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label-native">Mức ưu tiên</label>
                                <select name="priority_id" id="formTicketPriority" class="form-select form-control-native">
                                    <?php foreach ($priorities as $p): ?>
                                        <option value="<?= (int)$p['id'] ?>" <?= ($p['level'] === 'MEDIUM' || $p['id'] == 3) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Linked Asset -->
                        <div class="mb-3">
                            <label class="form-label-native">Thiết bị gặp sự cố (Tùy chọn)</label>
                            <select name="asset_id" id="formTicketAsset" class="form-select form-control-native font-monospace">
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
                            <label class="form-label-native">Nội dung chi tiết <span class="text-danger">*</span></label>
                            <textarea name="description" id="formTicketDescription" rows="3" class="form-control" style="border-radius:14px; background:#f8fafc; border:1.5px solid var(--border-card); font-size:0.95rem; padding:12px;" placeholder="Mô tả cụ thể sự cố để IT giải quyết nhanh nhất..." required></textarea>
                        </div>

                        <input type="hidden" name="location" value="<?= htmlspecialchars($user['department'] ?? '') ?>">

                        <!-- Submit Button -->
                        <button type="submit" id="btnSubmitMobileTicket" class="btn-primary-native mb-2">
                            <i class="bi bi-send-fill"></i> Gửi Yêu Cầu Hỗ Trợ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- NATIVE BOTTOM SHEET 2: CHI TIẾT YÊU CẦU                                   -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalTicketDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-pill"></div>
                <div class="modal-header border-0 pt-0 px-4 pb-2">
                    <div>
                        <span class="ticket-code-pill" id="detailTicketCode">#CODE</span>
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
    <!-- NATIVE BOTTOM SHEET 3: KẾT QUẢ QUÉT THIẾT BỊ                             -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalAssetDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-pill"></div>
                <div class="modal-header border-0 pt-0 px-4 pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                            <i class="bi bi-laptop fs-4"></i>
                        </div>
                        <div>
                            <span class="ticket-code-pill" id="assetDetailCode">AST-000000</span>
                            <h5 class="modal-title fw-bold text-dark mt-1 mb-0" id="assetDetailName">Tên Thiết Bị</h5>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1" id="assetDetailBody">
                    <!-- Dynamic details loaded via JS -->
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-danger flex-fill fw-bold rounded-3 py-3" id="btnReportAssetIncident">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Báo Sự Cố Máy Này
                    </button>
                    <button type="button" class="btn btn-light px-4 fw-bold rounded-3" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Native Bottom Navigation -->
    <nav class="native-bottom-nav">
        <div class="nav-tab-item active" onclick="switchTab('screen-tickets')" id="tab-tickets">
            <i class="bi bi-ticket-detailed-fill"></i>
            <span>Yêu cầu</span>
        </div>
        <div class="nav-tab-item" onclick="switchTab('screen-approvals')" id="tab-approvals">
            <i class="bi bi-patch-check-fill"></i>
            <span>Phê duyệt</span>
            <?php $pendingCount = count($pendingApprovals ?? []); if ($pendingCount > 0): ?>
                <span class="nav-tab-badge" id="navApprovalBadge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </div>
        <div class="nav-tab-item" onclick="switchTab('screen-scanner')" id="tab-scanner">
            <i class="bi bi-qr-code-scan"></i>
            <span>Quét QR</span>
        </div>
        <div class="nav-tab-item" onclick="switchTab('screen-profile')" id="tab-profile">
            <i class="bi bi-person-circle"></i>
            <span>Cá nhân</span>
        </div>
    </nav>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const BASE_URL = '<?= $baseUrl ?? '' ?>';
        const CSRF_TOKEN = '<?= \App\Core\Session::csrfToken() ?>';

        let currentScreen = 'screen-tickets';
        let currentTicketFilter = 'ALL';
        let searchTimer = null;
        let html5QrCode = null;
        let isScannerRunning = false;
        let currentCameraFacing = "environment";
        let lastScannedAsset = null;

        // Auto load tickets on startup
        document.addEventListener('DOMContentLoaded', () => {
            loadTickets();
        });

        // =========================================================================
        // NAVIGATION & SCREEN SWITCHING
        // =========================================================================
        function switchTab(screenId, optionalParam) {
            if (currentScreen === 'screen-scanner' && screenId !== 'screen-scanner') {
                stopScanner();
            }

            document.querySelectorAll('.screen-container').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-tab-item').forEach(el => el.classList.remove('active'));

            const targetScreen = document.getElementById(screenId);
            if (targetScreen) targetScreen.classList.add('active');

            const tabMap = {
                'screen-tickets': 'tab-tickets',
                'screen-approvals': 'tab-approvals',
                'screen-scanner': 'tab-scanner',
                'screen-profile': 'tab-profile'
            };
            const activeTab = document.getElementById(tabMap[screenId]);
            if (activeTab) activeTab.classList.add('active');

            const titleMap = {
                'screen-tickets': '<i class="bi bi-layers-fill"></i><span>TaskFlow</span>',
                'screen-approvals': '<i class="bi bi-shield-check"></i><span>Phê Duyệt</span>',
                'screen-scanner': '<i class="bi bi-qr-code"></i><span>Quét Mã Thiết Bị</span>',
                'screen-profile': '<i class="bi bi-person-circle"></i><span>Tài Khoản</span>'
            };
            const appbarTitle = document.getElementById('appbarTitle');
            if (appbarTitle && titleMap[screenId]) {
                appbarTitle.innerHTML = titleMap[screenId];
            }

            currentScreen = screenId;
            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (screenId === 'screen-tickets') {
                if (optionalParam) {
                    currentTicketFilter = optionalParam;
                    document.querySelectorAll('.segmented-item').forEach(btn => {
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

        function filterSegment(btn, status) {
            document.querySelectorAll('.segmented-item').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTicketFilter = status;
            loadTickets();
        }

        function debounceTicketSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadTickets, 300);
        }

        // =========================================================================
        // TICKETS MANAGEMENT
        // =========================================================================
        function loadTickets() {
            const container = document.getElementById('ticketsContainer');
            const search = document.getElementById('ticketSearchInput') ? document.getElementById('ticketSearchInput').value.trim() : '';

            container.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <div class="small mt-2">Đang tải danh sách yêu cầu...</div>
                </div>
            `;

            fetch(`${BASE_URL}/mobile/api/tickets?status=${encodeURIComponent(currentTicketFilter)}&search=${encodeURIComponent(search)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.tickets || data.tickets.length === 0) {
                        container.innerHTML = `
                            <div class="bg-white border rounded-4 p-5 text-center text-muted my-2 shadow-sm">
                                <i class="bi bi-inbox fs-1 mb-2 text-secondary d-block"></i>
                                <div class="fw-bold text-dark fs-6">Chưa có yêu cầu nào</div>
                                <div class="text-muted small mt-1">Bấm nút (+) để gửi yêu cầu hỗ trợ mới.</div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.tickets.forEach(tk => {
                        const statusBadge = getStatusBadgeHtml(tk.status);
                        const priorityColor = tk.priority_color || '#3b82f6';
                        html += `
                            <div class="native-ticket-card" onclick="openTicketDetail(${tk.id})">
                                <div class="ticket-card-top">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="ticket-code-pill">#${tk.ticket_code}</span>
                                        <span style="font-size:0.75rem; font-weight:700; color:${priorityColor}">
                                            ● ${tk.priority_name || 'Bình thường'}
                                        </span>
                                    </div>
                                    ${statusBadge}
                                </div>
                                <div class="ticket-card-title">${escapeHtml(tk.title)}</div>
                                <div class="ticket-card-meta">
                                    <span class="ticket-category-pill"><i class="bi bi-tag-fill me-1"></i> ${escapeHtml(tk.category_name || 'Sự cố IT')}</span>
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
                        <div class="p-3 bg-light rounded-4 mb-3 text-dark small" style="white-space: pre-wrap; line-height:1.5;">
                            ${escapeHtml(tk.description || 'Không có mô tả chi tiết.')}
                        </div>
                        <div class="row g-2 mb-3 text-muted small bg-white border rounded-4 p-3">
                            <div class="col-6"><strong>Người yêu cầu:</strong><br>${escapeHtml(tk.requester_name || 'N/A')}</div>
                            <div class="col-6"><strong>Kỹ thuật viên:</strong><br>${escapeHtml(tk.assignee_name || 'Chưa phân công')}</div>
                            <div class="col-6 mt-2"><strong>Mức ưu tiên:</strong><br>${escapeHtml(tk.priority_name || 'Bình thường')}</div>
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
                title.textContent = 'Báo Sự Cố Kỹ Thuật';
                document.getElementById('formTicketTitle').placeholder = 'Ví dụ: Hỏng màn hình, bàn phím lỗi...';
            } else {
                btnReq.classList.add('active');
                btnInc.classList.remove('active');
                title.textContent = 'Xin Cấp Phát Thiết Bị';
                document.getElementById('formTicketTitle').placeholder = 'Ví dụ: Xin cấp chuột máy tính, nâng cấp RAM...';
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
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Đang gửi...';

            fetch(`${BASE_URL}/mobile/api/tickets/create`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill"></i> Gửi Yêu Cầu Hỗ Trợ';

                if (data.success) {
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('modalCreateTicket')).hide();
                    switchTab('screen-tickets', 'ALL');
                } else {
                    alert(data.message || 'Lỗi tạo yêu cầu.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill"></i> Gửi Yêu Cầu Hỗ Trợ';
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
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <div class="small mt-2">Đang tải danh sách phê duyệt...</div>
                </div>
            `;

            fetch(`${BASE_URL}/mobile/api/approvals?status=PENDING`)
                .then(res => res.json())
                .then(data => {
                    const approvals = data.approvals || [];
                    const count = approvals.length;
                    if (badge) badge.textContent = `${count}`;
                    if (navBadge) {
                        navBadge.textContent = count;
                        navBadge.style.display = count > 0 ? 'inline-block' : 'none';
                    }

                    if (count === 0) {
                        container.innerHTML = `
                            <div class="bg-white border rounded-4 p-5 text-center text-muted my-2 shadow-sm">
                                <i class="bi bi-check-circle fs-1 mb-2 text-success d-block"></i>
                                <div class="fw-bold text-dark fs-6">Không có yêu cầu nào cần duyệt</div>
                                <div class="text-muted small mt-1">Toàn bộ đề xuất đã được xử lý hoàn tất.</div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    approvals.forEach(ap => {
                        html += `
                            <div class="native-approval-card" id="approvalCard-${ap.id}">
                                <div class="approval-card-header">
                                    <span class="ticket-code-pill" style="background:#fef3c7; color:#92400e;">CHỜ DUYỆT</span>
                                    <span class="text-muted small"><i class="bi bi-clock me-1"></i>${formatDate(ap.created_at)}</span>
                                </div>
                                <div class="approval-card-title">${escapeHtml(ap.title || ap.step_name || 'Phê duyệt dịch vụ')}</div>
                                <div class="approval-card-desc">${escapeHtml(ap.description || 'Không có mô tả chi tiết')}</div>
                                <div class="small text-muted mb-3">
                                    <i class="bi bi-person me-1"></i> Người yêu cầu: <strong>${escapeHtml(ap.requester_name || 'Nhân viên')}</strong>
                                </div>
                                <div class="approval-btn-group">
                                    <button class="btn-native-action btn-native-reject" onclick="decideApproval(${ap.id}, 'REJECTED')">
                                        <i class="bi bi-x-circle"></i> Từ Chối
                                    </button>
                                    <button class="btn-native-action btn-native-approve" onclick="decideApproval(${ap.id}, 'APPROVED')">
                                        <i class="bi bi-check-circle-fill"></i> Phê Duyệt
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
                        card.style.opacity = '0.3';
                        card.style.pointerEvents = 'none';
                    }
                    setTimeout(loadApprovals, 400);
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

            if (typeof Html5Qrcode === 'undefined') {
                alert('Thư viện quét mã đang tải, vui lòng thử lại sau vài giây.');
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
                }).catch(err => {
                    console.warn("Camera start error:", err);
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
            setTimeout(startScanner, 250);
        }

        function onQrCodeScanned(decodedText, decodedResult) {
            playSuccessBeep();
            if (navigator.vibrate) navigator.vibrate([70, 40, 70]);
            lookupAssetByCode(decodedText);
        }

        function onQrScanError(errorMessage) {
            // silent parse
        }

        function scanQRFromImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (typeof Html5Qrcode === 'undefined') {
                alert('Thư viện quét chưa sẵn sàng.');
                return;
            }

            const qr = new Html5Qrcode("qr-reader");
            qr.scanFile(file, true)
                .then(decodedText => {
                    playSuccessBeep();
                    lookupAssetByCode(decodedText);
                })
                .catch(err => {
                    alert('Không tìm thấy mã QR trong ảnh: ' + err);
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
            body.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><div class="small mt-2">Đang tải thông tin thiết bị...</div></div>`;
            modal.show();

            fetch(`${BASE_URL}/mobile/api/asset-scan/${encodeURIComponent(code)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.asset) {
                        body.innerHTML = `
                            <div class="alert alert-warning small text-center p-4 rounded-4">
                                <i class="bi bi-question-circle fs-2 d-block mb-2 text-warning"></i>
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
                                <span class="ticket-code-pill">${escapeHtml(a.asset_code)}</span>
                                <span class="status-pill status-pill-resolved">${escapeHtml(a.status || 'Đang sử dụng')}</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">${escapeHtml(a.name)}</h5>
                            <div class="text-muted small"><i class="bi bi-tag-fill me-1"></i> ${escapeHtml(a.category_name || 'Thiết bị IT')}</div>
                        </div>

                        <div class="bg-white border rounded-4 p-3">
                            <div class="row g-3 small">
                                <div class="col-6">
                                    <span class="text-muted">Model:</span><br>
                                    <strong class="text-dark fs-6">${escapeHtml(a.model || 'N/A')}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Serial:</span><br>
                                    <strong class="text-dark font-monospace fs-6">${escapeHtml(a.serial_number || 'N/A')}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Người sử dụng:</span><br>
                                    <strong class="text-primary fs-6">${escapeHtml(a.current_user_name || 'Chưa bàn giao')}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Vị trí:</span><br>
                                    <strong class="text-dark fs-6">${escapeHtml(a.location || 'Tại văn phòng')}</strong>
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
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.12);
            } catch (e) {}
        }

        function getStatusBadgeHtml(status) {
            const map = {
                'NEW': '<span class="status-pill status-pill-new">● Mới</span>',
                'ASSIGNED': '<span class="status-pill status-pill-assigned">● Đã nhận</span>',
                'IN_PROGRESS': '<span class="status-pill status-pill-progress">● Đang xử lý</span>',
                'PENDING_APPROVAL': '<span class="status-pill status-pill-pending">● Chờ duyệt</span>',
                'RESOLVED': '<span class="status-pill status-pill-resolved">● Đã xong</span>',
                'CLOSED': '<span class="status-pill status-pill-closed">● Đã đóng</span>'
            };
            return map[status] || `<span class="status-pill status-pill-closed">${status}</span>`;
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
