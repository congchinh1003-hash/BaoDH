<?php
use App\Core\Response;
use App\Core\Auth;

$baseUrl = Response::baseUrl();
$isLoggedIn = Auth::check();
$initialPage = $targetPage ?? ($isLoggedIn ? '/mobile' : '/mobile/login');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'TaskFlow - Phòng Giả Lập Thiết Bị (Device Studio)' ?></title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --studio-bg: #090d16;
            --studio-card: #111726;
            --studio-border: rgba(255, 255, 255, 0.08);
            --studio-text: #f1f5f9;
            --studio-subtext: #94a3b8;
            --primary: #0C66E4;
            --primary-glow: rgba(12, 102, 228, 0.35);
        }

        body.light-mode {
            --studio-bg: #e2e8f0;
            --studio-card: #ffffff;
            --studio-border: rgba(0, 0, 0, 0.08);
            --studio-text: #0f172a;
            --studio-subtext: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--studio-bg);
            color: var(--studio-text);
            overflow: hidden;
            transition: background-color 0.3s ease;
        }

        /* Top Studio Header */
        .studio-navbar {
            height: 64px;
            background: var(--studio-card);
            border-bottom: 1px solid var(--studio-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            position: relative;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .studio-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--studio-text);
        }

        .studio-logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0C66E4 0%, #4f46e5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        .studio-title-box {
            line-height: 1.15;
        }

        .studio-title {
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .studio-badge {
            font-size: 0.65rem;
            font-weight: 700;
            background: rgba(12, 102, 228, 0.15);
            color: #38bdf8;
            padding: 1px 6px;
            border-radius: 999px;
            border: 1px solid rgba(56, 189, 248, 0.3);
            text-transform: uppercase;
        }

        /* Center Control Hub */
        .studio-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .segmented-group {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--studio-border);
            border-radius: 12px;
            padding: 3px;
            display: flex;
            gap: 2px;
        }

        body.light-mode .segmented-group {
            background: #f1f5f9;
        }

        .seg-btn {
            background: transparent;
            border: none;
            color: var(--studio-subtext);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .seg-btn:hover {
            color: var(--studio-text);
        }

        .seg-btn.active {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 2px 8px var(--primary-glow);
        }

        .tool-icon-btn {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--studio-border);
            color: var(--studio-subtext);
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        body.light-mode .tool-icon-btn {
            background: #f1f5f9;
        }

        .tool-icon-btn:hover {
            color: var(--studio-text);
            border-color: var(--primary);
        }

        .page-jumper-select {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--studio-border);
            color: var(--studio-text);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 9px;
            outline: none;
            cursor: pointer;
        }

        body.light-mode .page-jumper-select {
            background: #f1f5f9;
        }

        /* Studio Stage */
        .studio-stage {
            height: calc(100vh - 64px);
            width: 100vw;
            overflow: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 30px;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(12, 102, 228, 0.05) 0%, transparent 60%),
                radial-gradient(circle at 10% 90%, rgba(99, 102, 241, 0.03) 0%, transparent 40%);
        }

        /* Device Frame Container */
        .device-wrapper {
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            transform-origin: center center;
            position: relative;
            user-select: none;
        }

        /* ========================================================================= */
        /* DEVICE 1: iPHONE 18 PRO MAX (iOS)                                         */
        /* ========================================================================= */
        .frame-iphone {
            width: 430px;
            height: 932px;
            background: #000000;
            border-radius: 56px;
            position: relative;
            box-shadow: 
                0 0 0 11px #22252a,
                0 0 0 13px #3f444d,
                0 0 0 15px #1a1c20,
                0 30px 70px rgba(0, 0, 0, 0.65);
        }

        .frame-iphone::before {
            content: '';
            position: absolute;
            inset: -14px;
            border-radius: 68px;
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            pointer-events: none;
        }

        .iphone-btn-silent {
            position: absolute;
            left: -17px;
            top: 115px;
            width: 4px;
            height: 28px;
            background: #3f444d;
            border-radius: 2px 0 0 2px;
        }
        .iphone-btn-vol-up {
            position: absolute;
            left: -17px;
            top: 165px;
            width: 4px;
            height: 54px;
            background: #3f444d;
            border-radius: 2px 0 0 2px;
        }
        .iphone-btn-vol-down {
            position: absolute;
            left: -17px;
            top: 235px;
            width: 4px;
            height: 54px;
            background: #3f444d;
            border-radius: 2px 0 0 2px;
        }
        .iphone-btn-power {
            position: absolute;
            right: -17px;
            top: 185px;
            width: 4px;
            height: 80px;
            background: #3f444d;
            border-radius: 0 2px 2px 0;
        }

        /* Dynamic Island */
        .dynamic-island {
            position: absolute;
            top: 11px;
            left: 50%;
            transform: translateX(-50%);
            width: 126px;
            height: 35px;
            background: #000000;
            border-radius: 20px;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .dynamic-island:hover {
            width: 160px;
            height: 38px;
        }

        .island-camera-lens {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: radial-gradient(circle, #0e1e38 20%, #000 80%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .island-sensor {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #06080e;
        }

        .ios-status-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 48px;
            padding: 12px 26px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #000000;
            font-size: 0.85rem;
            font-weight: 700;
            z-index: 40;
            pointer-events: none;
        }

        .ios-home-indicator {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 138px;
            height: 5px;
            background: rgba(0, 0, 0, 0.65);
            border-radius: 999px;
            z-index: 50;
            pointer-events: none;
        }

        /* ========================================================================= */
        /* DEVICE 2: SAMSUNG GALAXY S25 ULTRA (Android)                              */
        /* ========================================================================= */
        .frame-samsung {
            width: 412px;
            height: 915px;
            background: #000000;
            border-radius: 28px;
            position: relative;
            box-shadow: 
                0 0 0 9px #1c1d21,
                0 0 0 11px #33363e,
                0 0 0 13px #121316,
                0 30px 70px rgba(0, 0, 0, 0.65);
        }

        .frame-samsung::before {
            content: '';
            position: absolute;
            inset: -12px;
            border-radius: 38px;
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            pointer-events: none;
        }

        .samsung-btn-power {
            position: absolute;
            right: -15px;
            top: 220px;
            width: 4px;
            height: 50px;
            background: #33363e;
            border-radius: 0 2px 2px 0;
        }
        .samsung-btn-vol {
            position: absolute;
            right: -15px;
            top: 140px;
            width: 4px;
            height: 65px;
            background: #33363e;
            border-radius: 0 2px 2px 0;
        }

        .punch-hole-cam {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: radial-gradient(circle, #0e172a 40%, #000 90%);
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            z-index: 50;
            pointer-events: none;
        }

        .android-status-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 38px;
            padding: 8px 18px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #000000;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 40;
            pointer-events: none;
        }

        .android-nav-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: rgba(255, 255, 255, 0.96);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 50;
            border-bottom-left-radius: 28px;
            border-bottom-right-radius: 28px;
            padding: 0 40px;
        }

        .android-nav-btn {
            background: none;
            border: none;
            color: #334155;
            font-size: 1.15rem;
            padding: 4px 16px;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.1s ease;
        }

        .android-nav-btn:active {
            background: #e2e8f0;
            transform: scale(0.9);
        }

        /* ========================================================================= */
        /* DEVICE 3: APPLE iPAD PRO (NGANG - LANDSCAPE DEFAULT)                      */
        /* ========================================================================= */
        .frame-ipad-landscape {
            width: 1180px;
            height: 820px;
            background: #000000;
            border-radius: 36px;
            position: relative;
            box-shadow: 
                0 0 0 16px #1e2229,
                0 0 0 18px #3b414d,
                0 0 0 20px #14171c,
                0 35px 80px rgba(0, 0, 0, 0.7);
        }

        .ipad-cam-notch-top {
            position: absolute;
            top: 7px;
            left: 50%;
            transform: translateX(-50%);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.15);
            z-index: 50;
            pointer-events: none;
        }

        .ipad-landscape-status {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 38px;
            padding: 8px 30px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #000000;
            font-size: 0.82rem;
            font-weight: 700;
            z-index: 40;
            pointer-events: none;
        }

        .ipad-home-bar {
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 220px;
            height: 5px;
            background: rgba(0, 0, 0, 0.65);
            border-radius: 999px;
            z-index: 50;
            pointer-events: none;
        }

        /* ========================================================================= */
        /* DEVICE 4: iPHONE DUO (MÀN HÌNH KÉP GẬP DUAL SCREEN)                       */
        /* ========================================================================= */
        .frame-duo {
            width: 880px;
            height: 860px;
            background: #000000;
            border-radius: 44px;
            position: relative;
            display: flex;
            box-shadow: 
                0 0 0 12px #202328,
                0 0 0 14px #3d434f,
                0 0 0 16px #16181c,
                0 35px 85px rgba(0, 0, 0, 0.75);
        }

        .frame-duo::before {
            content: '';
            position: absolute;
            inset: -15px;
            border-radius: 56px;
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            pointer-events: none;
        }

        .duo-half {
            flex: 1;
            height: 100%;
            position: relative;
            overflow: hidden;
            background: #ffffff;
        }

        .duo-half-left {
            border-top-left-radius: 36px;
            border-bottom-left-radius: 36px;
        }

        .duo-half-right {
            border-top-right-radius: 36px;
            border-bottom-right-radius: 36px;
        }

        .duo-hinge {
            width: 24px;
            height: 100%;
            background: linear-gradient(90deg, #18191d 0%, #3a3e47 25%, #646a78 50%, #3a3e47 75%, #18191d 100%);
            position: relative;
            z-index: 60;
            box-shadow: inset 0 0 8px rgba(0,0,0,0.9), 0 0 10px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 40px 0;
        }

        .duo-hinge-screw {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #252830;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 1px 2px #000;
        }

        .duo-screen-tag {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85);
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            z-index: 55;
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.02em;
        }

        /* Common Screen Iframe */
        .device-screen-container {
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: inherit;
            position: relative;
            background: #ffffff;
        }

        #simulatorIframe, #simulatorIframeRight {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            background: #ffffff;
        }

        /* Device Specs Toast */
        .specs-pill {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--studio-card);
            border: 1px solid var(--studio-border);
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.75rem;
            color: var(--studio-subtext);
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            z-index: 100;
        }

        .specs-pill strong {
            color: var(--studio-text);
        }

        /* Frameless mode option */
        .device-wrapper.frameless .frame-iphone,
        .device-wrapper.frameless .frame-samsung,
        .device-wrapper.frameless .frame-ipad-landscape,
        .device-wrapper.frameless .frame-duo {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2) !important;
            border: 1px solid var(--studio-border) !important;
        }
        .device-wrapper.frameless .iphone-btn-silent,
        .device-wrapper.frameless .iphone-btn-vol-up,
        .device-wrapper.frameless .iphone-btn-vol-down,
        .device-wrapper.frameless .iphone-btn-power,
        .device-wrapper.frameless .samsung-btn-power,
        .device-wrapper.frameless .samsung-btn-vol,
        .device-wrapper.frameless::before {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- Studio Navigation Control Bar -->
    <header class="studio-navbar">
        <!-- Logo & Title -->
        <a href="<?= $baseUrl ?>/mobile" class="studio-brand">
            <div class="studio-logo-icon">
                <i class="bi bi-phone-fill"></i>
            </div>
            <div class="studio-title-box">
                <div class="studio-title">
                    TaskFlow <span class="studio-badge">Device Studio</span>
                </div>
                <div class="text-muted small" style="font-size:0.7rem;">Phòng Giả Lập Trải Nghiệm Ứng Dụng</div>
            </div>
        </a>

        <!-- Center Controls: Device Switcher -->
        <div class="studio-controls">
            <!-- 4 Devices: iPhone, Samsung, iPad Ngang, iPhone Duo -->
            <div class="segmented-group">
                <button class="seg-btn active" id="btnDevIphone" onclick="switchDevice('iphone')">
                    <i class="bi bi-apple"></i> iPhone 18 Pro
                </button>
                <button class="seg-btn" id="btnDevSamsung" onclick="switchDevice('samsung')">
                    <i class="bi bi-android2"></i> Galaxy S25
                </button>
                <button class="seg-btn" id="btnDevIpad" onclick="switchDevice('ipad')">
                    <i class="bi bi-tablet-landscape"></i> iPad Pro (Ngang)
                </button>
                <button class="seg-btn" id="btnDevDuo" onclick="switchDevice('duo')">
                    <i class="bi bi-book-half"></i> iPhone Duo (Màn Kép)
                </button>
            </div>

            <!-- Page Quick Jumper -->
            <select class="page-jumper-select ms-1" id="pageJumper" onchange="changeIframePage(this.value)">
                <option value="<?= $baseUrl ?>/mobile">📱 Giao Diện App Chính (/mobile)</option>
                <option value="<?= $baseUrl ?>/mobile/login">🔐 Màn Hình Đăng Nhập (/mobile/login)</option>
                <option value="<?= $baseUrl ?>/login?view=desktop">💻 Đăng Nhập Desktop (/login)</option>
            </select>

            <!-- Zoom / Scale Selector -->
            <div class="segmented-group ms-1">
                <button class="seg-btn" onclick="setStudioScale(0.65)">65%</button>
                <button class="seg-btn" id="btnScale75" onclick="setStudioScale(0.75)">75%</button>
                <button class="seg-btn active" id="btnScale85" onclick="setStudioScale(0.85)">85%</button>
                <button class="seg-btn" onclick="setStudioScale(1.0)">100%</button>
            </div>

            <!-- Orientation (For iPhone/Samsung) -->
            <button class="tool-icon-btn ms-1" id="btnOrientation" onclick="toggleOrientation()" title="Xoay dọc / xoay ngang thiết bị">
                <i class="bi bi-phone-landscape"></i>
            </button>

            <!-- Reload Frame -->
            <button class="tool-icon-btn" onclick="reloadFrame()" title="Làm mới lại dữ liệu app">
                <i class="bi bi-arrow-clockwise"></i>
            </button>

            <!-- Toggle Hardware Bezel -->
            <button class="tool-icon-btn" id="btnToggleFrame" onclick="toggleFrameless()" title="Bật/Tắt viền khung máy">
                <i class="bi bi-border-outer"></i>
            </button>

            <!-- Toggle Light / Dark Studio Theme -->
            <button class="tool-icon-btn" onclick="toggleStudioTheme()" title="Đổi màu nền studio tối / sáng">
                <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
            </button>
        </div>

        <!-- Right Side: Links & Fullscreen -->
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $baseUrl ?>/mobile" target="_blank" class="tool-icon-btn" title="Mở trang app riêng biệt ở tab mới">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <a href="<?= $baseUrl ?>/dashboard" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" style="font-size:0.78rem;">
                <i class="bi bi-speedometer2 me-1"></i> Trang Quản Trị
            </a>
        </div>
    </header>

    <!-- Main Simulator Stage -->
    <main class="studio-stage" id="studioStage">
        <div class="device-wrapper" id="deviceWrapper" style="transform: scale(0.85);">
            
            <!-- 1. SINGLE DEVICE CONTAINER (iPhone, Samsung, iPad) -->
            <div class="frame-iphone" id="deviceFrame">
                <!-- iPhone Hardware buttons -->
                <div class="iphone-btn-silent" id="iphoneBtnSilent"></div>
                <div class="iphone-btn-vol-up" id="iphoneBtnVolUp"></div>
                <div class="iphone-btn-vol-down" id="iphoneBtnVolDown"></div>
                <div class="iphone-btn-power" id="iphoneBtnPower"></div>

                <!-- Dynamic Island (iOS) -->
                <div class="dynamic-island" id="dynamicIsland" title="Dynamic Island - Chạm để xem">
                    <div class="island-camera-lens"></div>
                    <div class="island-sensor"></div>
                </div>

                <!-- iOS Status Bar -->
                <div class="ios-status-bar" id="iosStatusBar">
                    <span id="liveClockIos">9:41</span>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-reception-4"></i>
                        <span style="font-size:0.75rem;">5G</span>
                        <i class="bi bi-wifi"></i>
                        <i class="bi bi-battery-full fs-6"></i>
                    </div>
                </div>

                <!-- Punch Hole Camera (Samsung only) -->
                <div class="punch-hole-cam d-none" id="samsungPunchHole"></div>

                <!-- Android Status Bar (Samsung only) -->
                <div class="android-status-bar d-none" id="androidStatusBar">
                    <div class="d-flex align-items-center gap-2">
                        <span id="liveClockAndroid">09:41</span>
                        <i class="bi bi-bell-fill" style="font-size:0.7rem;"></i>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:0.7rem;">VoLTE</span>
                        <i class="bi bi-wifi"></i>
                        <i class="bi bi-battery-charging"></i>
                    </div>
                </div>

                <!-- iPad Camera & Status (iPad Landscape only) -->
                <div class="ipad-cam-notch-top d-none" id="ipadCam"></div>
                <div class="ipad-landscape-status d-none" id="ipadStatus">
                    <span id="liveClockIpad">9:41 SA · Thứ Hai</span>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-wifi"></i>
                        <span style="font-size:0.78rem;">100%</span>
                        <i class="bi bi-battery-full fs-6"></i>
                    </div>
                </div>

                <!-- Single Device Screen Container -->
                <div class="device-screen-container" id="singleScreenContainer">
                    <iframe id="simulatorIframe" src="<?= htmlspecialchars($initialPage) ?>" allow="camera; microphone; geolocation"></iframe>
                </div>

                <!-- iOS Home Indicator -->
                <div class="ios-home-indicator" id="iosHomeIndicator"></div>

                <!-- iPad Home Indicator -->
                <div class="ipad-home-bar d-none" id="ipadHomeIndicator"></div>

                <!-- Android Bottom Nav Bar (Samsung only) -->
                <div class="android-nav-bar d-none" id="androidNavBar">
                    <button class="android-nav-btn" onclick="androidRecentApps()" title="Đa nhiệm">
                        <i class="bi bi-list"></i>
                    </button>
                    <button class="android-nav-btn" onclick="androidGoHome()" title="Màn hình chính">
                        <i class="bi bi-circle"></i>
                    </button>
                    <button class="android-nav-btn" onclick="androidGoBack()" title="Quay lại (Back)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
            </div>

            <!-- 2. iPHONE DUO CONTAINER (MÀN HÌNH KÉP SONG SONG) -->
            <div class="frame-duo d-none" id="duoFrame">
                <!-- Left Screen (Màn hình 1) -->
                <div class="duo-half duo-half-left">
                    <div class="duo-screen-tag">
                        <i class="bi bi-person-fill"></i> Màn hình 1 (Gửi yêu cầu)
                    </div>
                    <iframe id="simulatorIframeLeft" src="<?= htmlspecialchars($initialPage) ?>" allow="camera; microphone; geolocation" style="width:100%; height:100%; border:none;"></iframe>
                    <div class="ios-home-indicator" style="bottom:6px;"></div>
                </div>

                <!-- Center Titanium Hinge -->
                <div class="duo-hinge">
                    <div class="duo-hinge-screw"></div>
                    <div class="duo-hinge-screw"></div>
                    <div class="duo-hinge-screw"></div>
                    <div class="duo-hinge-screw"></div>
                    <div class="duo-hinge-screw"></div>
                </div>

                <!-- Right Screen (Màn hình 2) -->
                <div class="duo-half duo-half-right">
                    <div class="duo-screen-tag">
                        <i class="bi bi-patch-check-fill text-warning"></i> Màn hình 2 (Phê duyệt & QR)
                    </div>
                    <iframe id="simulatorIframeRight" src="<?= htmlspecialchars($initialPage) ?>" allow="camera; microphone; geolocation" style="width:100%; height:100%; border:none;"></iframe>
                    <div class="ios-home-indicator" style="bottom:6px;"></div>
                </div>
            </div>

        </div>
    </main>

    <!-- Floating Specs Pill -->
    <div class="specs-pill">
        <div>Thiết bị: <strong id="specDeviceName">iPhone 18 Pro Max</strong></div>
        <div>Màn hình: <strong id="specResolution">430 × 932 px</strong></div>
        <div>Độ phân giải: <strong id="specDpr">3.0x Super Retina</strong></div>
        <div>Dữ liệu: <span class="badge bg-success bg-opacity-25 text-success">Dữ liệu thật 100%</span></div>
    </div>

    <script>
        let currentDevice = 'iphone';
        let currentScale = 0.85;
        let isLandscape = false;
        let isFrameless = false;

        const singleFrame = document.getElementById('deviceFrame');
        const duoFrame = document.getElementById('duoFrame');
        const wrapperEl = document.getElementById('deviceWrapper');
        const iframeEl = document.getElementById('simulatorIframe');
        const iframeLeft = document.getElementById('simulatorIframeLeft');
        const iframeRight = document.getElementById('simulatorIframeRight');

        // Clock auto update
        function updateLiveClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const timeStr = `${h}:${m}`;
            const iosClock = document.getElementById('liveClockIos');
            const androidClock = document.getElementById('liveClockAndroid');
            const ipadClock = document.getElementById('liveClockIpad');
            if (iosClock) iosClock.textContent = timeStr;
            if (androidClock) androidClock.textContent = timeStr;
            if (ipadClock) ipadClock.textContent = `${timeStr} SA · Thứ Hai`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // Switch Device Logic
        function switchDevice(devType) {
            currentDevice = devType;
            document.querySelectorAll('.segmented-group .seg-btn').forEach(b => b.classList.remove('active'));

            // Hardware elements
            const dynamicIsland = document.getElementById('dynamicIsland');
            const iosStatusBar = document.getElementById('iosStatusBar');
            const iosHomeBar = document.getElementById('iosHomeIndicator');
            const samsungCam = document.getElementById('samsungPunchHole');
            const androidStatus = document.getElementById('androidStatusBar');
            const androidNav = document.getElementById('androidNavBar');
            const ipadCam = document.getElementById('ipadCam');
            const ipadStatus = document.getElementById('ipadStatus');
            const ipadHomeBar = document.getElementById('ipadHomeIndicator');

            // Reset frame styles
            singleFrame.style.width = '';
            singleFrame.style.height = '';
            singleFrame.className = '';

            // Handle DUO device mode
            if (devType === 'duo') {
                document.getElementById('btnDevDuo').classList.add('active');
                singleFrame.classList.add('d-none');
                duoFrame.classList.remove('d-none');

                document.getElementById('specDeviceName').textContent = 'iPhone Duo (Màn Hình Kép)';
                document.getElementById('specResolution').textContent = '880 × 860 px (Dual 425px)';
                document.getElementById('specDpr').textContent = '2x Super Retina XDR';

                setStudioScale(0.72);
                return;
            }

            // Normal single device mode
            singleFrame.classList.remove('d-none');
            duoFrame.classList.add('d-none');

            if (devType === 'iphone') {
                document.getElementById('btnDevIphone').classList.add('active');
                singleFrame.classList.add('frame-iphone');
                dynamicIsland.classList.remove('d-none');
                iosStatusBar.classList.remove('d-none');
                iosHomeBar.classList.remove('d-none');
                samsungCam.classList.add('d-none');
                androidStatus.classList.add('d-none');
                androidNav.classList.add('d-none');
                ipadCam.classList.add('d-none');
                ipadStatus.classList.add('d-none');
                ipadHomeBar.classList.add('d-none');

                document.getElementById('specDeviceName').textContent = 'iPhone 18 Pro Max';
                document.getElementById('specResolution').textContent = isLandscape ? '932 × 430 px' : '430 × 932 px';
                document.getElementById('specDpr').textContent = '3.0x ProMotion 120Hz';
                setStudioScale(0.85);
            } else if (devType === 'samsung') {
                document.getElementById('btnDevSamsung').classList.add('active');
                singleFrame.classList.add('frame-samsung');
                dynamicIsland.classList.add('d-none');
                iosStatusBar.classList.add('d-none');
                iosHomeBar.classList.add('d-none');
                samsungCam.classList.remove('d-none');
                androidStatus.classList.remove('d-none');
                androidNav.classList.remove('d-none');
                ipadCam.classList.add('d-none');
                ipadStatus.classList.add('d-none');
                ipadHomeBar.classList.add('d-none');

                document.getElementById('specDeviceName').textContent = 'Samsung Galaxy S25 Ultra';
                document.getElementById('specResolution').textContent = isLandscape ? '915 × 412 px' : '412 × 915 px';
                document.getElementById('specDpr').textContent = 'Dynamic AMOLED 2X';
                setStudioScale(0.85);
            } else if (devType === 'ipad') {
                // IPAD NGANG (LANDSCAPE DEFAULT)
                document.getElementById('btnDevIpad').classList.add('active');
                singleFrame.classList.add('frame-ipad-landscape');
                dynamicIsland.classList.add('d-none');
                iosStatusBar.classList.add('d-none');
                iosHomeBar.classList.add('d-none');
                samsungCam.classList.add('d-none');
                androidStatus.classList.add('d-none');
                androidNav.classList.add('d-none');
                ipadCam.classList.remove('d-none');
                ipadStatus.classList.remove('d-none');
                ipadHomeBar.classList.remove('d-none');

                document.getElementById('specDeviceName').textContent = 'Apple iPad Pro 11" (Ngang)';
                document.getElementById('specResolution').textContent = '1180 × 820 px (Landscape)';
                document.getElementById('specDpr').textContent = 'Ultra Retina XDR';

                // iPad landscape auto scale for screen
                setStudioScale(0.68);
            }
        }

        // Change Scale
        function setStudioScale(scale) {
            currentScale = scale;
            wrapperEl.style.transform = `scale(${scale})`;
            document.querySelectorAll('.studio-controls .segmented-group button').forEach(btn => {
                if (btn.textContent.includes(`${Math.round(scale * 100)}%`)) {
                    btn.classList.add('active');
                } else if (btn.textContent.includes('%')) {
                    btn.classList.remove('active');
                }
            });
        }

        // Toggle Orientation
        function toggleOrientation() {
            if (currentDevice === 'duo') return; // Duo is dual square

            isLandscape = !isLandscape;
            const btn = document.getElementById('btnOrientation');
            btn.classList.toggle('text-primary', isLandscape);

            if (currentDevice === 'iphone') {
                singleFrame.style.width = isLandscape ? '932px' : '430px';
                singleFrame.style.height = isLandscape ? '430px' : '932px';
            } else if (currentDevice === 'samsung') {
                singleFrame.style.width = isLandscape ? '915px' : '412px';
                singleFrame.style.height = isLandscape ? '412px' : '915px';
            } else if (currentDevice === 'ipad') {
                singleFrame.style.width = isLandscape ? '1180px' : '820px';
                singleFrame.style.height = isLandscape ? '820px' : '1180px';
            }
        }

        // Change Iframe Page
        function changeIframePage(url) {
            if (iframeEl && url) {
                iframeEl.src = url;
            }
            if (iframeLeft && url) {
                iframeLeft.src = url;
            }
            if (iframeRight && url) {
                iframeRight.src = url;
            }
        }

        function reloadFrame() {
            if (iframeEl) iframeEl.src = iframeEl.src;
            if (iframeLeft) iframeLeft.src = iframeLeft.src;
            if (iframeRight) iframeRight.src = iframeRight.src;
        }

        function toggleFrameless() {
            isFrameless = !isFrameless;
            wrapperEl.classList.toggle('frameless', isFrameless);
            document.getElementById('btnToggleFrame').classList.toggle('text-primary', isFrameless);
        }

        function toggleStudioTheme() {
            document.body.classList.toggle('light-mode');
            const icon = document.getElementById('themeIcon');
            if (document.body.classList.contains('light-mode')) {
                icon.className = 'bi bi-sun-fill text-warning';
            } else {
                icon.className = 'bi bi-moon-stars-fill';
            }
        }

        // Samsung Android Bottom Navigation Controls
        function androidGoHome() {
            changeIframePage('<?= $baseUrl ?>/mobile');
        }

        function androidGoBack() {
            try {
                if (iframeEl.contentWindow && typeof iframeEl.contentWindow.handleAppBack === 'function') {
                    if (iframeEl.contentWindow.handleAppBack()) return;
                }
                iframeEl.contentWindow.history.back();
            } catch (e) {
                console.warn(e);
            }
        }

        function androidRecentApps() {
            singleFrame.style.transform = 'scale(0.92)';
            setTimeout(() => {
                singleFrame.style.transform = '';
            }, 300);
        }

        // Listen for iframe navigation to update page selector
        iframeEl.addEventListener('load', () => {
            try {
                const currentHref = iframeEl.contentWindow.location.pathname;
                const jumper = document.getElementById('pageJumper');
                if (currentHref.includes('/login')) {
                    jumper.value = '<?= $baseUrl ?>/mobile/login';
                } else if (currentHref.includes('/mobile')) {
                    jumper.value = '<?= $baseUrl ?>/mobile';
                }
            } catch (e) {}
        });
    </script>
</body>
</html>