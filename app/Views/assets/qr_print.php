<?php
$host = $_SERVER['HTTP_HOST'] ?? 'baodh.onrender.com';
if (strpos($host, 'baodh') !== false || strpos($host, 'onrender.com') !== false) {
    $baseUrlDomain = 'https://baodh.onrender.com';
} else {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https' : 'http';
    $baseUrlDomain = ($host === 'localhost' || $host === 'localhost:3000' || $host === '127.0.0.1:3000')
        ? 'https://baodh.onrender.com'
        : "$protocol://$host";
}

$scanUrl = $baseUrlDomain . '/asset/' . urlencode($asset['asset_code']);
$qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($scanUrl);
?>

<div class="d-print-none mb-4 d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size: 13px;">
                <li class="breadcrumb-item"><a href="/assets" class="text-decoration-none">Tài Sản IT</a></li>
                <li class="breadcrumb-item"><a href="/assets/<?= $asset['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($asset['asset_code']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">In Tem QR Code</li>
            </ol>
        </nav>
        <h1 class="h3 font-weight-bold text-gray-800 mb-0">Tem Nhãn Mã QR Thiết Bị CNTT</h1>
        <p class="text-muted small mb-0">Nhãn dán chuẩn 70mm x 45mm dùng để dán lên thân máy tính, màn hình, máy in.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/assets/<?= $asset['id'] ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
            <i class="bi bi-printer me-1"></i> In Tem Nhãn (Ctrl + P)
        </button>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <!-- Printable Sticker Tag Container -->
        <div class="card shadow border-2 print-tag-box" style="border: 2px dashed #0052cc; background: #fff; border-radius: 8px;">
            <div class="card-body p-4">
                <!-- Tag Header -->
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded p-1 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <i class="bi bi-laptop-fill font-weight-bold" style="font-size: 16px;"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 14px; letter-spacing: 0.5px; line-height: 1.1;">IT SERVICE DESK</div>
                            <div class="text-muted" style="font-size: 10px; font-weight: 600;">PROPERTY OF COMPANY</div>
                        </div>
                    </div>
                    <span class="badge bg-secondary font-monospace" style="font-size: 11px;"><?= htmlspecialchars($asset['category_name'] ?? 'HARDWARE') ?></span>
                </div>

                <!-- Tag Body -->
                <div class="row align-items-center">
                    <div class="col-5 text-center">
                        <div class="p-1 bg-white border rounded shadow-sm d-inline-block">
                            <img src="<?= $qrImageUrl ?>" alt="QR Code <?= htmlspecialchars($asset['asset_code']) ?>" style="width: 130px; height: 130px;" class="img-fluid">
                        </div>
                        <div class="text-muted font-monospace mt-1" style="font-size: 9.5px;">Quét mở trên điện thoại</div>
                        <div class="text-primary font-monospace fw-bold" style="font-size: 8.5px; word-break: break-all;">baodh.onrender.com</div>
                    </div>
                    <div class="col-7 ps-2">
                        <div class="mb-1">
                            <div class="text-muted text-uppercase" style="font-size: 9.5px; font-weight: 700;">Mã Tài Sản</div>
                            <div class="font-monospace font-weight-bold text-primary" style="font-size: 16px;"><?= htmlspecialchars($asset['asset_code']) ?></div>
                        </div>
                        <div class="mb-1">
                            <div class="text-muted text-uppercase" style="font-size: 9.5px; font-weight: 700;">Tên Thiết Bị</div>
                            <div class="font-weight-semibold text-dark text-truncate" style="font-size: 12.5px;" title="<?= htmlspecialchars($asset['name']) ?>">
                                <?= htmlspecialchars($asset['name']) ?>
                            </div>
                        </div>
                        <div class="mb-1">
                            <div class="text-muted text-uppercase" style="font-size: 9.5px; font-weight: 700;">Model / Serial</div>
                            <div class="text-muted font-monospace text-truncate" style="font-size: 11.5px;">
                                <?= htmlspecialchars(($asset['model'] ?: 'N/A') . ' / ' . ($asset['serial_number'] ?: 'N/A')) ?>
                            </div>
                        </div>
                        <div class="mb-0">
                            <div class="text-muted text-uppercase" style="font-size: 9.5px; font-weight: 700;">Đang Bàn Giao Cho</div>
                            <div class="font-weight-semibold text-success" style="font-size: 11.5px;">
                                <?= htmlspecialchars($asset['assigned_user_name'] ?? 'Kho IT (Sẵn sàng)') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tag Footer -->
                <div class="border-top pt-2 mt-3 d-flex justify-content-between align-items-center" style="font-size: 10px; color: #6c757d;">
                    <div><i class="bi bi-telephone-fill me-1 text-primary"></i> IT Hotline: <strong>1900-IT-DESK</strong></div>
                    <div>Vị trí: <strong><?= htmlspecialchars($asset['location'] ?: 'Trụ sở chính') ?></strong></div>
                </div>
            </div>
        </div>

        <!-- Instructions (hidden on print) -->
        <div class="d-print-none mt-4 alert alert-info py-3 px-4 shadow-sm" style="font-size: 13px;">
            <h6 class="alert-heading font-weight-bold mb-2"><i class="bi bi-info-circle me-1"></i> Hướng Dẫn In Tem Nhãn Thiết Bị:</h6>
            <ol class="mb-0 ps-3" style="line-height: 1.6;">
                <li>Bấm nút <strong>"In Tem Nhãn"</strong> hoặc nhấn <code>Ctrl + P</code>.</li>
                <li>Chọn khổ giấy in là <strong>Label/Sticker</strong> (hoặc A4 với tùy chọn in nhiều tem).</li>
                <li>Tích chọn <strong>"Background graphics"</strong> trong hộp thoại in để giữ đầy đủ viền và màu sắc tem.</li>
                <li>Dán tem lên góc trên bên trái của thân case máy tính để bàn hoặc mặt đáy của Laptop.</li>
            </ol>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-tag-box, .print-tag-box * {
        visibility: visible;
    }
    .print-tag-box {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm !important;
        max-width: 80mm !important;
        border: 2px solid #000 !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .d-print-none {
        display: none !important;
    }
}
</style>

