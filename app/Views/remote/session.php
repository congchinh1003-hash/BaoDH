<?php
use App\Core\{Response,Session};
$baseUrl=Response::baseUrl();
?>
<div class="container-fluid py-3">
    <div class="d-flex align-items-center gap-3 mb-3"><a href="<?= $baseUrl ?>/remote-support" class="btn btn-outline-secondary">Quay lại</a>
        <h4 class="mb-0"><?= htmlspecialchars($session['client_name'].' / '.$session['device_name']) ?></h4>
        <span id="remoteStatus" class="badge bg-warning text-dark" role="status">Đang chờ agent / người dùng</span>
        <button id="remoteEnd" class="btn btn-danger ms-auto">Disconnect</button>
    </div>
    <p>IT: <?= htmlspecialchars($session['staff_name']) ?>. Người dùng có thể ngắt kết nối hoặc thu hồi quyền bằng biểu tượng TaskFlow ở khay hệ thống.</p>
    <?php if ($isViewer): ?>
    <button id="remoteFullscreen" class="btn btn-sm btn-outline-primary mb-2">Toàn màn hình</button>
    <div id="remoteScreen" style="background:#111;height:75vh;display:flex;align-items:center;justify-content:center;overflow:hidden" tabindex="0" aria-label="Màn hình máy từ xa">
        <img id="remoteImage" alt="Đang chờ màn hình máy từ xa" style="max-width:100%;max-height:100%;object-fit:contain;user-select:none" draggable="false">
    </div>
    <script src="<?= $baseUrl ?>/js/remote-transport.js"></script>
    <script src="<?= $baseUrl ?>/js/remote-viewer.js"></script>
    <script>TaskFlowViewer.start(<?= json_encode(['baseUrl'=>$baseUrl,'code'=>$session['session_code'],'csrf'=>Session::csrfToken(),'config'=>$remoteConfig],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>);</script>
    <?php else: ?>
    <div class="alert alert-info">Chấp nhận yêu cầu trên hộp thoại của agent Windows. Sau khi chấp nhận, toàn bộ màn hình được chia sẻ tự động trong các phiên sau của cùng IT và thiết bị.</div>
    <script>document.getElementById('remoteEnd').onclick=async()=>{await fetch(<?= json_encode($baseUrl.'/remote-support/end') ?>,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({session_code:<?= json_encode($session['session_code']) ?>,_csrf_token:<?= json_encode(Session::csrfToken()) ?>})});location.href=<?= json_encode($baseUrl.'/remote-support') ?>;};</script>
    <?php endif; ?>
</div>
