<?php
use App\Core\{Response,Session};
$baseUrl=Response::baseUrl();
?>
<div class="container py-4">
    <h2>Hỗ trợ từ xa</h2>
    <?php if ($configurationError): ?><div class="alert alert-warning"><?= htmlspecialchars($configurationError) ?></div><?php endif; ?>
    <p><strong>Chỉ máy người dùng được hỗ trợ cần cài agent một lần.</strong> Máy IT/admin dùng trình duyệt, không cần cài agent. Lần đầu kết nối, chọn Accept hoặc Deny trên máy người dùng.</p>
    <?php if (!$isStaff): ?>
    <?php if ($installationError): ?><div class="alert alert-danger"><?= htmlspecialchars($installationError) ?></div><?php endif; ?>
    <form method="post" action="<?= $baseUrl ?>/remote-support/install" class="mb-4">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(Session::csrfToken()) ?>">
        <button type="submit" class="btn btn-outline-primary" <?= $installationError ? 'disabled' : '' ?>>Tải bộ cài agent cho máy của tôi</button>
        <span class="text-muted small">Cài trên máy cần được IT hỗ trợ. Chạy PowerShell với quyền Administrator; IT có thể nhập mật khẩu quản trị. Bộ cài có hiệu lực 15 phút.</span>
    </form>
    <?php else: ?>
    <div class="alert alert-info">Máy IT/admin không cần cài agent. Người dùng đăng nhập tài khoản của mình tại <strong>Hỗ trợ từ xa</strong>, tải và cài agent trên máy cần hỗ trợ; sau đó IT chọn máy ở danh sách bên dưới.</div>
    <?php endif; ?>
    <?php if ($devices): ?>
    <h5>Máy của tôi</h5>
    <?php foreach ($devices as $device): ?>
        <div class="d-flex gap-3 align-items-center mb-2"><span><?= htmlspecialchars($device['name']) ?> — <?= $device['enrolled'] ? 'Đã cài agent' : 'Chưa cài' ?></span>
        <button class="btn btn-sm btn-outline-danger" onclick="revokeRemote(<?= (int)$device['id'] ?>)">Revoke access</button></div>
    <?php endforeach; endif; ?>
    <?php if ($isStaff): ?>
    <form method="post" action="<?= $baseUrl ?>/remote-support/create-quick" class="card card-body my-4">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(Session::csrfToken()) ?>">
        <h5>Kết nối tới máy người dùng</h5>
        <select id="remoteDevice" name="device_id" class="form-select mb-2" required>
            <option value="">Chọn máy đã cài agent</option>
            <?php foreach ($allUsers as $user): foreach ($user['remote_devices'] as $device): if (!$device['enrolled']) continue; ?>
            <option value="<?= (int)$device['id'] ?>" data-client="<?= (int)$user['id'] ?>"><?= htmlspecialchars($user['full_name'].' / '.$device['name']) ?> — <?= $device['last_seen'] && strtotime($device['last_seen'].' UTC')>time()-20 ? 'Online' : 'Offline' ?></option>
            <?php endforeach; endforeach; ?>
        </select>
        <input type="hidden" name="client_id" id="remoteClient">
        <input name="notes" class="form-control mb-2" placeholder="Nội dung hỗ trợ" maxlength="2000">
        <button class="btn btn-primary" <?= $configurationError ? 'disabled' : '' ?>>Yêu cầu kết nối</button>
    </form>
    <?php endif; ?>
    <h5>Lịch sử phiên hỗ trợ</h5>
    <div class="table-responsive"><table class="table"><thead><tr><th>IT / Người dùng / Máy</th><th>Bắt đầu (UTC)</th><th>Thời lượng</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    <?php foreach ($sessions as $s): ?>
    <tr><td><?= htmlspecialchars(($s['staff_name']??'').' / '.$s['client_name'].' / '.($s['device_name']??'')) ?></td>
    <td><?= htmlspecialchars($s['started_at'] ?? $s['created_at']) ?></td>
    <td><?= $s['started_at'] && ($s['device_id'] || $s['ended_at']) ? max(0,($s['ended_at'] ? strtotime($s['ended_at'].' UTC') : time())-strtotime($s['started_at'].' UTC')).'s' : '—' ?></td>
    <td><?= $s['device_id'] ? htmlspecialchars($s['status']) : 'Phiên cũ (đã ngừng)' ?></td><td><?php if ($s['device_id']): ?><a href="<?= $baseUrl ?>/remote-support/session/<?= htmlspecialchars($s['session_code']) ?>">Mở phiên</a><?php endif; ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
</div>
<script>
document.getElementById('remoteDevice')?.addEventListener('change',function(){document.getElementById('remoteClient').value=this.selectedOptions[0]?.dataset.client||'';});
async function revokeRemote(device_id){
    const response=await fetch(<?= json_encode($baseUrl.'/remote-support/revoke') ?>,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({device_id,_csrf_token:<?= json_encode(Session::csrfToken()) ?>})});
    const data=await response.json(); if(!data.success) alert(data.message); else location.reload();
}
</script>
