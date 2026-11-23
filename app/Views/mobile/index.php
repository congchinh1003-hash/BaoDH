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
    
    <!-- Modern Soft Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --primary-soft: #eff6ff;
            --primary-border: #bfdbfe;
            --emerald: #10b981;
            --emerald-soft: #ecfdf5;
            --emerald-border: #a7f3d0;
            --amber: #f59e0b;
            --amber-soft: #fffbeb;
            --rose: #ef4444;
            --rose-soft: #fef2f2;
            --text-main: #0f172a;
            --text-sub: #475569;
            --text-muted: #94a3b8;
            --border-soft: #f1f5f9;
            --border-card: #e2e8f0;
            --safe-bottom: env(safe-area-inset-bottom, 16px);
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
            font-size: 15px;
            line-height: 1.45;
            -webkit-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
            user-select: none;
            -webkit-user-select: none;
            padding-bottom: calc(72px + var(--safe-bottom));
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }

        /* Soft Floating App Bar */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }

        .app-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.3px;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Soft Pill Buttons */
        .pill-btn-qr {
            background: var(--emerald-soft);
            color: #047857;
            border: 1px solid var(--emerald-border);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 0.78rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .pill-btn-qr:active {
            transform: scale(0.96);
            background: #d1fae5;
        }

        .user-avatar-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid var(--primary-border);
            cursor: pointer;
        }

        /* Main Container */
        .app-content {
            display: none;
            padding: 12px 14px;
            animation: fadeInSoft 0.18s ease-out forwards;
        }

        .app-content.active {
            display: block;
        }

        @keyframes fadeInSoft {
            from { opacity: 0; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Hero QR Scanner Banner */
        .qr-banner-card {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-radius: 16px;
            padding: 14px 16px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            box-shadow: 0 3px 12px rgba(5, 150, 105, 0.2);
            cursor: pointer;
            transition: transform 0.12s ease;
        }

        .qr-banner-card:active {
            transform: scale(0.98);
        }

        .qr-banner-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .qr-banner-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .qr-banner-title {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 1px;
        }

        .qr-banner-sub {
            font-size: 0.74rem;
            opacity: 0.9;
        }

        .qr-banner-pill {
            background: #ffffff;
            color: #047857;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 999px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Soft Segmented Filter */
        .soft-segmented {
            display: flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 3px;
            gap: 3px;
            margin-bottom: 12px;
        }

        .soft-segmented-btn {
            flex: 1;
            border: none;
            background: transparent;
            border-radius: 9px;
            padding: 7px 4px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-sub);
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .soft-segmented-btn.active {
            background: #ffffff;
            color: var(--text-main);
            font-weight: 700;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        /* Search Input */
        .soft-search-bar {
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            height: 42px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .soft-search-bar input {
            border: none;
            outline: none;
            background: transparent;
            font-family: inherit;
            font-size: 0.88rem;
            color: var(--text-main);
            width: 100%;
        }

        .soft-search-bar input::placeholder {
            color: var(--text-muted);
        }

        /* Soft Ticket Item */
        .soft-ticket-card {
            background: #ffffff;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 13px 14px;
            margin-bottom: 9px;
            cursor: pointer;
            transition: all 0.12s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .soft-ticket-card:active {
            transform: scale(0.985);
            background: #f8fafc;
        }

        .ticket-row-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .ticket-code-tag {
            font-family: monospace;
            font-weight: 700;
            font-size: 0.72rem;
            color: var(--text-sub);
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 6px;
        }

        .ticket-item-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.35;
            margin-bottom: 5px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ticket-row-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.74rem;
            color: var(--text-muted);
        }

        /* Soft Status Pill */
        .status-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .tag-new { background: #dbeafe; color: #1e40af; }
        .tag-assigned { background: #e0e7ff; color: #3730a3; }
        .tag-progress { background: #fef3c7; color: #92400e; }
        .tag-pending { background: #ffedd5; color: #9a3412; }
        .tag-resolved { background: #dcfce7; color: #166534; }
        .tag-closed { background: #f1f5f9; color: #475569; }

        /* Approval Card */
        .soft-approval-card {
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 11px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .approval-title-text {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .approval-desc-box {
            font-size: 0.82rem;
            color: var(--text-sub);
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .approval-actions-row {
            display: flex;
            gap: 8px;
        }

        .soft-action-btn {
            flex: 1;
            height: 42px;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            border: none;
            transition: transform 0.12s ease;
        }

        .soft-action-btn:active {
            transform: scale(0.96);
        }

        .btn-soft-reject {
            background: var(--rose-soft);
            color: var(--rose);
            border: 1px solid #fee2e2;
        }

        .btn-soft-approve {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
        }

        /* QR Scanner Camera UI */
        .camera-viewport {
            position: relative;
            background: #000;
            border-radius: 20px;
            overflow: hidden;
            width: 100%;
            height: 320px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            margin-bottom: 14px;
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

        .camera-reticle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-radius: 20px;
            pointer-events: none;
            box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.45);
        }

        .camera-reticle::before, .camera-reticle::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            border-color: #10b981;
            border-style: solid;
        }

        .camera-reticle::before {
            top: -2px;
            left: -2px;
            border-width: 3px 0 0 3px;
            border-top-left-radius: 18px;
        }

        .camera-reticle::after {
            bottom: -2px;
            right: -2px;
            border-width: 0 3px 3px 0;
            border-bottom-right-radius: 18px;
        }

        .camera-laser {
            position: absolute;
            left: 10px;
            right: 10px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            box-shadow: 0 0 10px #10b981;
            animation: laserMove 2s infinite ease-in-out alternate;
        }

        @keyframes laserMove {
            0% { top: 12%; opacity: 0.3; }
            50% { opacity: 1; }
            100% { top: 88%; opacity: 0.3; }
        }

        /* Soft Floating Bottom Nav */
        .soft-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: calc(62px + var(--safe-bottom));
            padding-bottom: var(--safe-bottom);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1050;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.03);
        }

        .nav-item-btn {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 600;
            height: 100%;
            cursor: pointer;
            position: relative;
            transition: color 0.15s ease;
        }

        .nav-item-btn i {
            font-size: 1.35rem;
            margin-bottom: 2px;
            transition: transform 0.15s ease;
        }

        .nav-item-btn.active {
            color: var(--primary);
        }

        .nav-item-btn.active i {
            transform: translateY(-2px);
        }

        .nav-badge-pill {
            position: absolute;
            top: 7px;
            right: 25%;
            background: var(--rose);
            color: #ffffff;
            font-size: 0.62rem;
            font-weight: 800;
            padding: 1px 5px;
            border-radius: 999px;
            border: 1.5px solid #ffffff;
        }

        /* Center QR Scan Nav Pill */
        .nav-center-qr {
            color: var(--emerald) !important;
        }

        .nav-center-qr i {
            font-size: 1.55rem;
        }

        /* Floating Action Button (FAB) */
        .soft-fab {
            position: fixed;
            bottom: calc(74px + var(--safe-bottom));
            right: 16px;
            width: 52px;
            height: 52px;
            border-radius: 26px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
            border: none;
            cursor: pointer;
            z-index: 1040;
            transition: transform 0.12s ease;
        }

        .soft-fab:active {
            transform: scale(0.92);
        }

        /* Soft Bottom Sheet */
        .modal.bottom-sheet .modal-dialog {
            position: fixed;
            bottom: 0;
            margin: 0;
            width: 100%;
            max-width: 100%;
        }

        .modal.bottom-sheet .modal-content {
            border-radius: 24px 24px 0 0;
            border: none;
            background: var(--surface);
            max-height: 88vh;
            overflow-y: auto;
            padding-bottom: var(--safe-bottom);
        }

        .sheet-drag-pill {
            width: 36px;
            height: 4px;
            background-color: #cbd5e1;
            border-radius: 999px;
            margin: 10px auto 14px auto;
        }

        .form-control-soft {
            height: 44px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid var(--border-card);
            padding: 0 12px;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        .form-control-soft:focus {
            background: #ffffff;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
        }

        .btn-soft-primary {
            height: 46px;
            border-radius: 999px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 700;
            border: none;
            box-shadow: 0 3px 12px rgba(37, 99, 235, 0.25);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            transition: transform 0.12s ease;
        }

        .btn-soft-primary:active {
            transform: scale(0.97);
        }
    </style>
</head>
<body>

    <!-- Soft App Bar -->
    <header class="app-header">
        <div class="app-brand" id="appHeaderTitle">
            <div class="brand-icon">
                <i class="bi bi-layers-fill"></i>
            </div>
            <span>TaskFlow</span>
        </div>
        <div class="header-actions">
            <div class="pill-btn-qr" onclick="switchTab('screen-scanner')">
                <i class="bi bi-qr-code-scan"></i>
                <span>Quét QR</span>
            </div>
            <div class="user-avatar-circle" onclick="switchTab('screen-profile')" title="Cá nhân">
                <?= mb_strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)) ?>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- SCREEN 1: YÊU CẦU & HUB CHÍNH                                             -->
    <!-- ========================================================================= -->
    <main id="screen-tickets" class="app-content active">
        <!-- Prominent QR Scanner Hero Banner -->
        <div class="qr-banner-card" onclick="switchTab('screen-scanner')">
            <div class="qr-banner-left">
                <div class="qr-banner-icon">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <div>
                    <div class="qr-banner-title">Quét QR Thiết Bị</div>
                    <div class="qr-banner-sub">Xem máy & báo sự cố 1 chạm</div>
                </div>
            </div>
            <div class="qr-banner-pill">
                <i class="bi bi-camera-fill me-1"></i> Quét ngay
            </div>
        </div>

        <!-- Search Input -->
        <div class="soft-search-bar">
            <i class="bi bi-search text-muted"></i>
            <input type="text" id="ticketSearchInput" placeholder="Tìm theo mã vé hoặc tiêu đề..." oninput="debounceTicketSearch()">
        </div>

        <!-- Soft Segmented Control -->
        <div class="soft-segmented">
            <button class="soft-segmented-btn active" data-status="ALL" onclick="filterSegment(this, 'ALL')">Tất Cả</button>
            <button class="soft-segmented-btn" data-status="OPEN" onclick="filterSegment(this, 'OPEN')">Đang Xử Lý</button>
            <button class="soft-segmented-btn" data-status="PENDING_APPROVAL" onclick="filterSegment(this, 'PENDING_APPROVAL')">Chờ Duyệt</button>
            <button class="soft-segmented-btn" data-status="CLOSED" onclick="filterSegment(this, 'CLOSED')">Đã Đóng</button>
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
    <main id="screen-approvals" class="app-content">
        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <h6 class="fw-bold mb-0 text-dark">Phiếu Cần Bạn Phê Duyệt</h6>
            <span class="badge bg-danger rounded-pill px-2 py-1 font-monospace" id="approvalCountBadge">0</span>
        </div>

        <div id="approvalsContainer">
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <div class="small mt-2">Đang kiểm tra danh sách...</div>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 3: QUÉT QR THIẾT BỊ (ASSET SCANNER)                                -->
    <!-- ========================================================================= -->
    <main id="screen-scanner" class="app-content">
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <div>
                <h6 class="fw-bold text-dark mb-0">Camera Quét Mã QR</h6>
                <div class="text-muted" style="font-size:0.75rem;">Hướng camera vào tem dán trên thiết bị</div>
            </div>
            <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 fw-semibold" onclick="switchCamera()">
                <i class="bi bi-arrow-repeat me-1"></i> Đổi Camera
            </button>
        </div>

        <!-- Camera Viewport -->
        <div class="camera-viewport" id="scannerWrapper">
            <div id="qr-reader"></div>
            <div class="camera-reticle">
                <div class="camera-laser"></div>
            </div>
        </div>

        <!-- Manual Input / Gallery Options -->
        <div class="d-flex gap-2 mb-2">
            <input type="text" id="manualAssetCodeInput" class="form-control form-control-soft font-monospace" placeholder="Mã thiết bị (vd: AST-000001)">
            <button class="btn btn-primary px-3 fw-bold rounded-pill" onclick="lookupManualAsset()" style="white-space:nowrap; height:44px; font-size:0.85rem;">Tra cứu</button>
        </div>

        <label for="qrFileInput" class="w-100 btn btn-light border py-2 fw-semibold rounded-pill text-primary d-flex align-items-center justify-content-center gap-2 cursor-pointer" style="font-size:0.85rem;">
            <i class="bi bi-image"></i> Chọn ảnh chứa mã QR từ thư viện ảnh
        </label>
        <input type="file" id="qrFileInput" accept="image/*" class="d-none" onchange="scanQRFromImage(event)">
    </main>

    <!-- ========================================================================= -->
    <!-- SCREEN 4: CÁ NHÂN & CÀI ĐẶT                                               -->
    <!-- ========================================================================= -->
    <main id="screen-profile" class="app-content">
        <div class="bg-white border rounded-4 p-4 text-center mb-3 shadow-sm">
            <div class="mx-auto mb-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width:60px; height:60px;">
                <?= mb_strtoupper(mb_substr($user['full_name'] ?? 'U', 0, 1)) ?>
            </div>
            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user['full_name'] ?? 'Người dùng') ?></h6>
            <div class="text-muted small font-monospace mb-2"><?= htmlspecialchars($user['username'] ?? '') ?></div>
            <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill"><?= htmlspecialchars($user['role'] ?? 'USER') ?></span>
            <div class="small text-muted mt-2"><i class="bi bi-building me-1"></i><?= htmlspecialchars($user['department'] ?? 'Doanh nghiệp') ?></div>
        </div>

        <div class="bg-white border rounded-4 p-2 shadow-sm mb-3">
            <a href="<?= $baseUrl ?? '' ?>/TaskFlow-v1.3.apk" download class="d-flex align-items-center justify-content-between p-3 text-decoration-none text-dark border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3"><i class="bi bi-android2 fs-5"></i></div>
                    <div>
                        <div class="fw-bold small">Cập Nhật APK v1.3</div>
                        <div class="text-muted" style="font-size:0.72rem;">Phiên bản mới nhất</div>
                    </div>
                </div>
                <i class="bi bi-download text-muted"></i>
            </a>
            <a href="<?= $baseUrl ?? '' ?>/simulator" target="_blank" class="d-flex align-items-center justify-content-between p-3 text-decoration-none text-dark border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3"><i class="bi bi-display fs-5"></i></div>
                    <div>
                        <div class="fw-bold small">Giả Lập Thiết Bị (Studio)</div>
                        <div class="text-muted" style="font-size:0.72rem;">iPhone 18, Samsung S25, iPad Pro</div>
                    </div>
                </div>
                <i class="bi bi-box-arrow-up-right text-muted"></i>
            </a>
            <a href="<?= $baseUrl ?? '' ?>/logout" class="d-flex align-items-center justify-content-between p-3 text-decoration-none text-danger">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-danger bg-opacity-10 text-danger rounded-3"><i class="bi bi-box-arrow-right fs-5"></i></div>
                    <div class="fw-bold small">Đăng Xuất Tài Khoản</div>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        </div>
    </main>

    <!-- Floating Action Button for Instant Creation -->
    <button class="soft-fab" onclick="openCreateTicketModal('INCIDENT')" title="Tạo yêu cầu mới">
        <i class="bi bi-plus-lg"></i>
    </button>

    <!-- ========================================================================= -->
    <!-- BOTTOM SHEET 1: FORM GỬI YÊU CẦU MỚI                                      -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalCreateTicket" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-pill"></div>
                <div class="modal-header border-0 pt-0 px-4 pb-2">
                    <h6 class="modal-title fw-bold text-dark" id="createTicketTitle">Gửi Yêu Cầu Hỗ Trợ</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1">
                    <form id="mobileCreateTicketForm" onsubmit="submitMobileTicket(event)">
                        <!-- Type selector pills -->
                        <div class="d-flex gap-2 mb-3">
                            <label class="btn btn-outline-danger flex-fill active py-2 rounded-pill text-center fw-bold" id="labelTypeIncident" style="font-size:0.82rem;">
                                <input type="radio" name="ticket_type" value="INCIDENT" class="d-none" checked onchange="toggleTicketType('INCIDENT')">
                                <i class="bi bi-exclamation-octagon-fill me-1"></i> Báo Sự Cố
                            </label>
                            <label class="btn btn-outline-primary flex-fill py-2 rounded-pill text-center fw-bold" id="labelTypeRequest" style="font-size:0.82rem;">
                                <input type="radio" name="ticket_type" value="SERVICE_REQUEST" class="d-none" onchange="toggleTicketType('SERVICE_REQUEST')">
                                <i class="bi bi-box-seam-fill me-1"></i> Cấp Thiết Bị
                            </label>
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark mb-1">Tiêu đề yêu cầu <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="formTicketTitle" class="form-control form-control-soft" placeholder="Tóm tắt ngắn gọn vấn đề..." required>
                        </div>

                        <!-- Category & Priority Row -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark mb-1">Danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" id="formTicketCategory" class="form-select form-control-soft" required>
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark mb-1">Mức ưu tiên</label>
                                <select name="priority_id" id="formTicketPriority" class="form-select form-control-soft">
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
                            <label class="form-label small fw-bold text-dark mb-1">Thiết bị gặp sự cố (Tùy chọn)</label>
                            <select name="asset_id" id="formTicketAsset" class="form-select form-control-soft font-monospace">
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
                            <label class="form-label small fw-bold text-dark mb-1">Nội dung chi tiết <span class="text-danger">*</span></label>
                            <textarea name="description" id="formTicketDescription" rows="3" class="form-control" style="border-radius:12px; background:#f8fafc; border:1px solid var(--border-card); font-size:0.9rem; padding:10px;" placeholder="Mô tả cụ thể sự cố để IT giải quyết nhanh nhất..." required></textarea>
                        </div>

                        <input type="hidden" name="location" value="<?= htmlspecialchars($user['department'] ?? '') ?>">

                        <!-- Submit Button -->
                        <button type="submit" id="btnSubmitMobileTicket" class="btn-soft-primary mb-2">
                            <i class="bi bi-send-fill"></i> Gửi Yêu Cầu Hỗ Trợ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BOTTOM SHEET 2: CHI TIẾT YÊU CẦU                                          -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalTicketDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-pill"></div>
                <div class="modal-header border-0 pt-0 px-4 pb-2">
                    <div class="w-100 pe-2">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="ticket-code-tag" id="detailTicketCode">#CODE</span>
                            <span id="detailTicketStatus"></span>
                        </div>
                        <h6 class="modal-title fw-bold text-dark mt-1 mb-0" id="detailTicketTitle" style="font-size:1.02rem; line-height:1.35;">Tiêu Đề Phiếu</h6>
                    </div>
                    <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1" id="detailTicketBody" style="max-height: 72vh; overflow-y: auto;">
                    <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>
                <div class="modal-footer border-0 px-4 py-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary w-100 fw-bold rounded-pill py-2" data-bs-dismiss="modal" style="font-size:0.88rem;">
                        <i class="bi bi-x-circle me-1"></i> Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BOTTOM SHEET 3: KẾT QUẢ QUÉT THIẾT BỊ                                     -->
    <!-- ========================================================================= -->
    <div class="modal bottom-sheet fade" id="modalAssetDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow-lg">
                <div class="sheet-drag-pill"></div>
                <div class="modal-header border-0 pt-0 px-4 pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
                            <i class="bi bi-laptop fs-4"></i>
                        </div>
                        <div>
                            <span class="ticket-code-tag" id="assetDetailCode">AST-000000</span>
                            <h6 class="modal-title fw-bold text-dark mt-1 mb-0" id="assetDetailName">Tên Thiết Bị</h6>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-1" id="assetDetailBody">
                    <!-- Dynamic details loaded via JS -->
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-danger flex-fill fw-bold rounded-pill py-2" id="btnReportAssetIncident" style="font-size:0.88rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Báo Sự Cố Máy Này
                    </button>
                    <button type="button" class="btn btn-light px-4 fw-bold rounded-pill" data-bs-dismiss="modal" style="font-size:0.88rem;">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Soft Bottom Navigation Bar -->
    <nav class="soft-bottom-nav">
        <div class="nav-item-btn active" onclick="switchTab('screen-tickets')" id="tab-tickets">
            <i class="bi bi-ticket-detailed-fill"></i>
            <span>Yêu cầu</span>
        </div>
        <div class="nav-item-btn" onclick="switchTab('screen-approvals')" id="tab-approvals">
            <i class="bi bi-patch-check-fill"></i>
            <span>Phê duyệt</span>
            <?php $pendingCount = count($pendingApprovals ?? []); if ($pendingCount > 0): ?>
                <span class="nav-badge-pill" id="navApprovalBadge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </div>
        <div class="nav-item-btn nav-center-qr" onclick="switchTab('screen-scanner')" id="tab-scanner">
            <i class="bi bi-qr-code-scan"></i>
            <span>Quét QR</span>
        </div>
        <div class="nav-item-btn" onclick="switchTab('screen-profile')" id="tab-profile">
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
        let isScanLocked = false;
        let currentCameraFacing = "environment";
        let lastScannedAsset = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadTickets();

            // When asset popup is closed, resume scanner if still on scanner tab
            const assetModal = document.getElementById('modalAssetDetail');
            if (assetModal) {
                assetModal.addEventListener('hidden.bs.modal', () => {
                    resumeScannerIfActive();
                });
            }

            // When ticket create modal is closed, resume scanner if on scanner tab
            const createModal = document.getElementById('modalCreateTicket');
            if (createModal) {
                createModal.addEventListener('hidden.bs.modal', () => {
                    resumeScannerIfActive();
                });
            }
        });

        // =========================================================================
        // NAVIGATION & SCREEN SWITCHING
        // =========================================================================
        function switchTab(screenId, optionalParam) {
            if (currentScreen === 'screen-scanner' && screenId !== 'screen-scanner') {
                stopScanner();
            }

            document.querySelectorAll('.app-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-item-btn').forEach(el => el.classList.remove('active'));

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
                'screen-tickets': '<div class="brand-icon"><i class="bi bi-layers-fill"></i></div><span>TaskFlow</span>',
                'screen-approvals': '<div class="brand-icon" style="background:#f59e0b;"><i class="bi bi-patch-check-fill"></i></div><span>Phê Duyệt</span>',
                'screen-scanner': '<div class="brand-icon" style="background:#10b981;"><i class="bi bi-qr-code-scan"></i></div><span>Quét Mã Thiết Bị</span>',
                'screen-profile': '<div class="brand-icon" style="background:#64748b;"><i class="bi bi-person-circle"></i></div><span>Tài Khoản</span>'
            };
            const appHeaderTitle = document.getElementById('appHeaderTitle');
            if (appHeaderTitle && titleMap[screenId]) {
                appHeaderTitle.innerHTML = titleMap[screenId];
            }

            currentScreen = screenId;
            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (screenId === 'screen-tickets') {
                if (optionalParam) {
                    currentTicketFilter = optionalParam;
                    document.querySelectorAll('.soft-segmented-btn').forEach(btn => {
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
            document.querySelectorAll('.soft-segmented-btn').forEach(b => b.classList.remove('active'));
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
                    <div class="small mt-2">Đang tải danh sách...</div>
                </div>
            `;

            fetch(`${BASE_URL}/mobile/api/tickets?status=${encodeURIComponent(currentTicketFilter)}&search=${encodeURIComponent(search)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.tickets || data.tickets.length === 0) {
                        container.innerHTML = `
                            <div class="bg-white border rounded-4 p-4 text-center text-muted my-2 shadow-sm">
                                <i class="bi bi-inbox fs-2 mb-2 text-secondary d-block"></i>
                                <div class="fw-bold text-dark small">Không tìm thấy yêu cầu nào</div>
                                <div class="text-muted" style="font-size:0.75rem;">Bấm nút (+) để gửi yêu cầu hỗ trợ mới.</div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.tickets.forEach(tk => {
                        const statusBadge = getStatusBadgeHtml(tk.status);
                        const priorityColor = tk.priority_color || '#3b82f6';
                        html += `
                            <div class="soft-ticket-card" onclick="openTicketDetail(${tk.id})">
                                <div class="ticket-row-top">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="ticket-code-tag">#${tk.ticket_code}</span>
                                        <span style="font-size:0.72rem; font-weight:700; color:${priorityColor}">
                                            ● ${tk.priority_name || 'Bình thường'}
                                        </span>
                                    </div>
                                    ${statusBadge}
                                </div>
                                <div class="ticket-item-title">${escapeHtml(tk.title)}</div>
                                <div class="ticket-row-bottom">
                                    <span><i class="bi bi-tag-fill me-1"></i>${escapeHtml(tk.category_name || 'Sự cố IT')}</span>
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
            const statusEl = document.getElementById('detailTicketStatus');
            if (statusEl) statusEl.innerHTML = '';
            body.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><div class="small text-muted mt-2">Đang tải thông tin phiếu...</div></div>`;
            modal.show();

            fetch(`${BASE_URL}/mobile/api/tickets/${ticketId}`)
                .then(res => {
                    return res.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Máy chủ phản hồi không đúng định dạng JSON.');
                        }
                    });
                })
                .then(data => {
                    if (!data.success || !data.ticket) {
                        body.innerHTML = `<div class="alert alert-warning small rounded-3 p-3 text-center"><i class="bi bi-info-circle me-1"></i> ${escapeHtml(data.message || 'Không thể tải thông tin phiếu.')}</div>`;
                        return;
                    }
                    const tk = data.ticket;
                    document.getElementById('detailTicketCode').textContent = `#${tk.ticket_code}`;
                    document.getElementById('detailTicketTitle').textContent = tk.title;
                    if (statusEl) {
                        statusEl.innerHTML = getStatusBadgeHtml(tk.status);
                    }

                    // Checklist
                    let checklistHtml = '';
                    if (data.checklists && data.checklists.length > 0) {
                        checklistHtml = `
                            <div class="mt-3">
                                <div class="fw-bold small text-dark mb-2 d-flex align-items-center gap-1">
                                    <i class="bi bi-check2-square text-primary"></i> Tiến độ công việc (Checklist):
                                </div>
                                <div class="bg-light rounded-3 p-2 border">
                        `;
                        data.checklists.forEach(c => {
                            const isDone = c.is_completed == 1 || c.is_done == 1;
                            checklistHtml += `
                                <div class="d-flex align-items-start gap-2 small py-1 px-1">
                                    <i class="bi ${isDone ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'} mt-1"></i>
                                    <span class="${isDone ? 'text-decoration-line-through text-muted' : 'text-dark'}">${escapeHtml(c.item_text)}</span>
                                </div>
                            `;
                        });
                        checklistHtml += `</div></div>`;
                    }

                    // Comments / History
                    let commentsHtml = '';
                    if (data.comments && data.comments.length > 0) {
                        commentsHtml = `
                            <div class="mt-3">
                                <div class="fw-bold small text-dark mb-2 d-flex align-items-center gap-1">
                                    <i class="bi bi-chat-left-text text-primary"></i> Lịch sử phản hồi (${data.comments.length}):
                                </div>
                        `;
                        data.comments.forEach(cm => {
                            commentsHtml += `
                                <div class="bg-white border rounded-3 p-2 mb-2 shadow-none">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark" style="font-size:0.8rem;">${escapeHtml(cm.full_name || 'Người dùng')}</strong>
                                        <span class="text-muted" style="font-size:0.7rem;"><i class="bi bi-clock me-1"></i>${formatDate(cm.created_at)}</span>
                                    </div>
                                    <div class="text-secondary small" style="white-space:pre-wrap; line-height:1.4;">${escapeHtml(cm.content)}</div>
                                </div>
                            `;
                        });
                        commentsHtml += `</div>`;
                    }

                    const priorityColor = tk.priority_color || '#3b82f6';
                    const assetInfo = tk.asset_code ? `${tk.asset_code} - ${tk.asset_name || ''}` : 'Không đính kèm';

                    body.innerHTML = `
                        <!-- Priority & Time bar -->
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom mb-3" style="font-size:0.8rem;">
                            <span class="fw-bold" style="color: ${priorityColor};">
                                ● Mức độ: ${escapeHtml(tk.priority_name || 'Bình thường')}
                            </span>
                            <span class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>${formatDate(tk.created_at)}
                            </span>
                        </div>

                        <!-- Info grid -->
                        <div class="bg-white border rounded-3 p-3 mb-3 shadow-none">
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted" style="font-size:0.75rem;">Người gửi:</span><br>
                                    <strong class="text-dark">${escapeHtml(tk.requester_name || 'N/A')}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted" style="font-size:0.75rem;">Kỹ thuật viên:</span><br>
                                    <strong class="text-primary">${escapeHtml(tk.assignee_name || 'Chưa tiếp nhận')}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted" style="font-size:0.75rem;">Danh mục:</span><br>
                                    <strong class="text-dark">${escapeHtml(tk.category_name || 'Sự cố chung')}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted" style="font-size:0.75rem;">Vị trí / Phòng:</span><br>
                                    <strong class="text-dark">${escapeHtml(tk.location || tk.requester_department || 'Văn phòng')}</strong>
                                </div>
                                ${tk.asset_code ? `
                                <div class="col-12 mt-2 pt-2 border-top">
                                    <span class="text-muted" style="font-size:0.75rem;">Thiết bị liên quan:</span><br>
                                    <strong class="text-dark font-monospace">${escapeHtml(assetInfo)}</strong>
                                </div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-2">
                            <div class="fw-bold small text-dark mb-1 d-flex align-items-center gap-1">
                                <i class="bi bi-file-earmark-text text-primary"></i> Nội dung mô tả sự cố:
                            </div>
                            <div class="p-3 bg-light rounded-3 text-dark small" style="white-space: pre-wrap; line-height: 1.5; border: 1px solid #f1f5f9;">
                                ${escapeHtml(tk.description || 'Không có mô tả chi tiết.')}
                            </div>
                        </div>

                        ${checklistHtml}
                        ${commentsHtml}
                    `;
                })
                .catch(err => {
                    body.innerHTML = `<div class="alert alert-danger small rounded-3 p-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> Lỗi kết nối: ${escapeHtml(err.message)}</div>`;
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
                            <div class="bg-white border rounded-4 p-4 text-center text-muted my-2 shadow-sm">
                                <i class="bi bi-check-circle fs-2 mb-2 text-success d-block"></i>
                                <div class="fw-bold text-dark small">Không có yêu cầu nào cần duyệt</div>
                                <div class="text-muted" style="font-size:0.75rem;">Toàn bộ đề xuất đã được xử lý hoàn tất.</div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    approvals.forEach(ap => {
                        html += `
                            <div class="soft-approval-card" id="approvalCard-${ap.id}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="status-tag tag-pending">CHỜ DUYỆT</span>
                                    <span class="text-muted small"><i class="bi bi-clock me-1"></i>${formatDate(ap.created_at)}</span>
                                </div>
                                <div class="approval-title-text">${escapeHtml(ap.title || ap.step_name || 'Phê duyệt dịch vụ')}</div>
                                <div class="approval-desc-box">${escapeHtml(ap.description || 'Không có mô tả chi tiết')}</div>
                                <div class="small text-muted mb-3">
                                    <i class="bi bi-person me-1"></i> Người yêu cầu: <strong>${escapeHtml(ap.requester_name || 'Nhân viên')}</strong>
                                </div>
                                <div class="approval-actions-row">
                                    <button class="soft-action-btn btn-soft-reject" onclick="decideApproval(${ap.id}, 'REJECTED')">
                                        <i class="bi bi-x-circle"></i> Từ Chối
                                    </button>
                                    <button class="soft-action-btn btn-soft-approve" onclick="decideApproval(${ap.id}, 'APPROVED')">
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
            isScanLocked = false;

            if (typeof Html5Qrcode === 'undefined') {
                alert('Thư viện quét mã đang tải, vui lòng thử lại sau vài giây.');
                return;
            }

            try {
                html5QrCode = new Html5Qrcode("qr-reader");
                const config = { fps: 15, qrbox: { width: 200, height: 200 } };

                html5QrCode.start(
                    { facingMode: currentCameraFacing },
                    config,
                    onQrCodeScanned,
                    onQrScanError
                ).then(() => {
                    isScannerRunning = true;
                    isScanLocked = false;
                }).catch(err => {
                    console.warn("Camera start error:", err);
                });
            } catch (e) {
                console.error(e);
            }
        }

        function stopScanner() {
            isScanLocked = false;
            if (html5QrCode && isScannerRunning) {
                html5QrCode.stop().then(() => {
                    isScannerRunning = false;
                }).catch(err => console.warn(err));
            }
        }

        function switchCamera() {
            isScanLocked = false;
            stopScanner();
            currentCameraFacing = currentCameraFacing === "environment" ? "user" : "environment";
            setTimeout(startScanner, 250);
        }

        function onQrCodeScanned(decodedText, decodedResult) {
            // Guard: Scan only once!
            if (isScanLocked) return;
            isScanLocked = true;

            // Pause camera stream immediately so it stops scanning while popup modal is open
            if (html5QrCode && isScannerRunning) {
                try {
                    html5QrCode.pause(true);
                } catch (e) {
                    console.warn("Could not pause scanner:", e);
                }
            }

            playSuccessBeep();
            if (navigator.vibrate) navigator.vibrate([70, 40, 70]);
            lookupAssetByCode(decodedText);
        }

        function resumeScannerIfActive() {
            isScanLocked = false;
            if (currentScreen === 'screen-scanner' && html5QrCode && isScannerRunning) {
                try {
                    html5QrCode.resume();
                } catch (e) {
                    console.warn("Resume scan failed, restarting camera:", e);
                    stopScanner();
                    setTimeout(startScanner, 250);
                }
            }
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

        function extractAssetCode(raw) {
            if (!raw) return '';
            let str = String(raw).trim();
            try { str = decodeURIComponent(str); } catch (e) {}

            // Match /asset/AST-MON-002 or /assets/AST-MON-002
            const match = str.match(/(?:asset|assets)\/([A-Za-z0-9\-_]+)/i);
            if (match && match[1]) {
                return match[1];
            }

            // If it's a full URL or contains slashes, extract last segment
            if (str.includes('/')) {
                const parts = str.split('/').map(p => p.trim()).filter(p => p.length > 0);
                if (parts.length > 0) {
                    return parts[parts.length - 1];
                }
            }
            return str;
        }

        function lookupManualAsset() {
            const code = document.getElementById('manualAssetCodeInput').value.trim();
            if (!code) {
                alert('Vui lòng nhập mã thiết bị (vd: AST-000001).');
                return;
            }
            lookupAssetByCode(code);
        }

        function lookupAssetByCode(rawCode) {
            const cleanCode = extractAssetCode(rawCode);
            const modalEl = document.getElementById('modalAssetDetail');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const body = document.getElementById('assetDetailBody');

            // Display ONLY clean asset code, never full URL
            document.getElementById('assetDetailCode').textContent = cleanCode;
            document.getElementById('assetDetailName').textContent = 'Đang tra cứu thiết bị...';
            body.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><div class="small mt-2">Đang tải thông tin...</div></div>`;
            modal.show();

            fetch(`${BASE_URL}/mobile/api/asset-scan?code=${encodeURIComponent(cleanCode)}`)
                .then(res => {
                    return res.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Máy chủ phản hồi định dạng không hợp lệ.');
                        }
                    });
                })
                .then(data => {
                    if (!data.success || !data.asset) {
                        body.innerHTML = `
                            <div class="alert alert-warning small text-center p-3 rounded-4">
                                <i class="bi bi-question-circle fs-3 d-block mb-2 text-warning"></i>
                                <strong>Không tìm thấy thiết bị!</strong><br>
                                Mã: <span class="font-monospace fw-bold">${escapeHtml(cleanCode)}</span> không tồn tại trong hệ thống.
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
                            isScanLocked = true;
                            modal.hide();
                            setTimeout(() => {
                                openCreateTicketModal('INCIDENT', a.id);
                            }, 250);
                        };
                    }

                    body.innerHTML = `
                        <div class="p-3 bg-light rounded-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="ticket-code-tag">${escapeHtml(a.asset_code)}</span>
                                <span class="status-tag tag-resolved">${escapeHtml(a.status || 'Đang sử dụng')}</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">${escapeHtml(a.name)}</h6>
                            <div class="text-muted small"><i class="bi bi-tag-fill me-1"></i> ${escapeHtml(a.category_name || 'Thiết bị IT')}</div>
                        </div>

                        <div class="bg-white border rounded-3 p-3">
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted">Model:</span><br>
                                    <strong class="text-dark">${escapeHtml(a.model || 'N/A')}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted">Serial:</span><br>
                                    <strong class="text-dark font-monospace">${escapeHtml(a.serial_number || 'N/A')}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted">Người sử dụng:</span><br>
                                    <strong class="text-primary">${escapeHtml(a.current_user_name || 'Chưa bàn giao')}</strong>
                                </div>
                                <div class="col-6 mt-2">
                                    <span class="text-muted">Vị trí:</span><br>
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
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                gain.gain.setValueAtTime(0.1, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.12);
            } catch (e) {}
        }

        function getStatusBadgeHtml(status) {
            const map = {
                'NEW': '<span class="status-tag tag-new">● Mới</span>',
                'ASSIGNED': '<span class="status-tag tag-assigned">● Đã nhận</span>',
                'IN_PROGRESS': '<span class="status-tag tag-progress">● Đang xử lý</span>',
                'PENDING_APPROVAL': '<span class="status-tag tag-pending">● Chờ duyệt</span>',
                'RESOLVED': '<span class="status-tag tag-resolved">● Đã xong</span>',
                'CLOSED': '<span class="status-tag tag-closed">● Đã đóng</span>'
            };
            return map[status] || `<span class="status-tag tag-closed">${status}</span>`;
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

        // Mobile Back Gesture Navigation Management
        function handleAppBack() {
            // Check if any modal is currently visible
            const openModals = document.querySelectorAll('.modal.show');
            if (openModals.length > 0) {
                openModals.forEach(m => {
                    const instance = bootstrap.Modal.getInstance(m);
                    if (instance) instance.hide();
                });
                return true;
            }

            // If user is on scanner, approvals, or profile tab, go back to tickets hub
            if (currentScreen !== 'screen-tickets') {
                switchTab('screen-tickets');
                return true;
            }

            return false;
        }

        window.addEventListener('popstate', function (e) {
            if (handleAppBack()) {
                history.pushState(null, '', location.href);
            }
        });

        // Initialize history guard so swipe back doesn't go to old desktop site
        try {
            history.pushState(null, '', location.href);
        } catch (e) {}
    </script>
</body>
</html>
