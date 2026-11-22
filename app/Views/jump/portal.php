<div class="container-fluid py-4">
    <!-- HERO BANNER -->
    <div class="page-hero d-flex justify-content-between align-items-center mb-4 p-4 rounded shadow-sm bg-white border-start border-primary border-4">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3">
                <i class="bi bi-shield-lock-fill fs-2"></i>
            </div>
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small text-uppercase fw-semibold">
                        <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Trang chủ</a></li>
                        <li class="breadcrumb-item active" aria-current="page">JumpServer PAM</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-1 text-dark">Cổng Quản Trị Máy Chủ An Toàn (JumpServer PAM)</h3>
                <p class="text-muted mb-0 small">
                    Truy cập máy chủ tập trung có kiểm soát phân quyền và khóa phiên độc quyền (Single Exclusive Session).
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($isAdmin)): ?>
                <a href="/admin/jump-servers" class="btn btn-outline-primary d-flex align-items-center gap-2">
                    <i class="bi bi-gear-fill"></i> Quản Trị Máy Chủ & Phân Quyền
                </a>
            <?php endif; ?>
            <button onclick="location.reload()" class="btn btn-light border d-flex align-items-center gap-2">
                <i class="bi bi-arrow-clockwise"></i> Làm mới
            </button>
        </div>
    </div>

    <!-- NOTICE BANNER -->
    <div class="alert alert-info border-info border-opacity-25 d-flex align-items-center gap-3 mb-4 py-3 shadow-sm bg-white">
        <i class="bi bi-info-circle-fill text-primary fs-3 flex-shrink-0"></i>
        <div class="small">
            <strong>Cơ chế Khóa phiên độc quyền (Exclusive Preemption):</strong> Nhằm đảm bảo an toàn vận hành, mỗi máy chủ tại một thời điểm chỉ cho phép <strong>duy nhất 1 phiên làm việc</strong>. Khi bạn bấm kết nối tới máy chủ đang có kỹ sư khác làm việc, hệ thống sẽ tự động <strong>đá phiên của người trước ra</strong> và cấp toàn quyền điều khiển cho bạn.
        </div>
    </div>

    <!-- METRICS OVERVIEW -->
    <?php
        $totalServers = count($servers);
        $busyCount = 0;
        $freeCount = 0;
        foreach ($servers as $s) {
            if (!empty($s['active_occupant'])) {
                $busyCount++;
            } else {
                $freeCount++;
            }
        }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Máy Chủ Bạn Được Quyền Vào</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark"><?= $totalServers ?></h3>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
                        <i class="bi bi-hdd-network fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Máy Chủ Đang Rảnh</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success"><?= $freeCount ?></h3>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Máy Chủ Đang Bận (In-Use)</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $busyCount ?></h3>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle">
                        <i class="bi bi-person-fill-lock fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="jumpSearch" class="form-control border-start-0" placeholder="Tìm theo tên máy chủ, IP, hệ điều hành...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="jumpFilterOs" class="form-select">
                        <option value="">Tất cả hệ điều hành</option>
                        <option value="LINUX">Linux (Ubuntu, CentOS, Debian)</option>
                        <option value="WINDOWS">Windows Server</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-end text-muted small">
                    <i class="bi bi-shield-check text-success"></i> Kết nối an toàn qua Web Bastion Gateway
                </div>
            </div>
        </div>
    </div>

    <!-- SERVER LIST GRID -->
    <?php if (empty($servers)): ?>
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center bg-white">
            <div class="mb-3">
                <i class="bi bi-shield-x text-muted" style="font-size: 4rem;"></i>
            </div>
            <h5 class="fw-bold text-dark">Chưa Có Máy Chủ Nào Được Phân Quyền</h5>
            <p class="text-muted small max-w-500 mx-auto">
                Tài khoản của bạn hiện tại chưa được cấp quyền truy cập vào bất kỳ máy chủ nào. Vui lòng liên hệ Người quản trị hệ thống để được gán quyền theo cá nhân hoặc theo Nhóm IT.
            </p>
            <?php if (!empty($isAdmin)): ?>
                <div class="mt-2">
                    <a href="/admin/jump-servers" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Quản lý máy chủ và cấp quyền ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row g-4" id="serverGrid">
            <?php foreach ($servers as $srv): ?>
                <?php
                    $isWindows = ($srv['os_type'] === 'WINDOWS');
                    $isBusy = !empty($srv['active_occupant']);
                    $occupantName = $srv['occupant_name'] ?? 'Kỹ sư khác';
                    $occupantTime = !empty($srv['occupant_started_at']) ? date('H:i:s d/m', strtotime($srv['occupant_started_at'])) : '';
                ?>
                <div class="col-md-6 col-xl-4 server-card-item" 
                     data-name="<?= strtolower(htmlspecialchars($srv['name'])) ?>"
                     data-ip="<?= htmlspecialchars($srv['ip_address']) ?>"
                     data-os="<?= htmlspecialchars($srv['os_type']) ?>">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden position-relative hover-shadow transition">
                        <!-- Top Accent Line -->
                        <div style="height: 4px;" class="<?= $isBusy ? 'bg-warning' : 'bg-primary' ?>"></div>
                        
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-3 rounded-3 <?= $isWindows ? 'bg-info bg-opacity-10 text-info' : 'bg-dark text-white' ?>">
                                        <?php if ($isWindows): ?>
                                            <i class="bi bi-windows fs-3"></i>
                                        <?php else: ?>
                                            <i class="bi bi-terminal fs-3"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($srv['name']) ?></h5>
                                        <span class="badge bg-light text-secondary border mt-1">
                                            <?= htmlspecialchars($srv['os_type']) ?> &bull; <?= htmlspecialchars($srv['protocol']) ?>:<?= htmlspecialchars($srv['port']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div>
                                    <?php if ($isBusy): ?>
                                        <span class="badge bg-warning text-dark px-2 py-1 d-flex align-items-center gap-1 shadow-sm">
                                            <span class="spinner-grow spinner-grow-sm text-dark" style="width: 0.5rem; height: 0.5rem;"></span>
                                            Đang có người dùng
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Sẵn sàng
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <p class="text-muted small mb-3 flex-grow-1">
                                <?= !empty($srv['description']) ? htmlspecialchars($srv['description']) : 'Máy chủ hạ tầng phục vụ kiểm thử và vận hành dịch vụ.' ?>
                            </p>

                            <!-- Server Specs Info Box -->
                            <div class="p-3 rounded-2 bg-light mb-3 border small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="bi bi-hdd-network me-1"></i> Địa chỉ IP:</span>
                                    <code class="fw-bold text-primary"><?= htmlspecialchars($srv['ip_address']) ?></code>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted"><i class="bi bi-person me-1"></i> User mặc định:</span>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($srv['default_username'] ?: 'root') ?></span>
                                </div>
                                <?php if ($isBusy): ?>
                                    <div class="d-flex justify-content-between mt-2 pt-2 border-top text-warning-emphasis">
                                        <span><i class="bi bi-person-lock me-1"></i> Đang truy cập:</span>
                                        <span class="fw-bold"><?= htmlspecialchars($occupantName) ?> (<?= $occupantTime ?>)</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Connect Action (Opens in dedicated Popup Window) -->
                            <div class="mt-auto">
                                <?php if ($isBusy): ?>
                                    <div class="d-flex gap-2">
                                        <button type="button" 
                                                onclick="openJumpConsole(<?= $srv['id'] ?>, true, '<?= addslashes(htmlspecialchars($occupantName)) ?>')"
                                                class="btn btn-warning flex-grow-1 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                            <i class="bi bi-lightning-charge-fill"></i> Chiếm quyền (Popup)
                                        </button>
                                        <a href="/jump/terminal/<?= $srv['id'] ?>" target="_blank" 
                                           onclick="return confirm('CẢNH BÁO: Máy chủ này đang có <?= addslashes(htmlspecialchars($occupantName)) ?> sử dụng. Bạn có chắc muốn chiếm quyền?');"
                                           class="btn btn-outline-warning" title="Mở trong Tab mới">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex gap-2">
                                        <button type="button" 
                                                onclick="openJumpConsole(<?= $srv['id'] ?>, false, '')"
                                                class="btn btn-primary flex-grow-1 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                            <i class="bi bi-window-stack me-1"></i> Mở Popup Console
                                        </button>
                                        <a href="/jump/terminal/<?= $srv['id'] ?>" target="_blank" class="btn btn-outline-primary" title="Mở trong Tab mới">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function openJumpConsole(serverId, isBusy, occupantName) {
    if (isBusy) {
        const ok = confirm('CẢNH BÁO: Máy chủ này hiện đang có ' + occupantName + ' sử dụng.\n\nNếu bạn kết nối, người dùng này sẽ lập tức BỊ ĐÁ RA khỏi phiên làm việc.\n\nBạn có chắc chắn muốn chiếm quyền và kết nối?');
        if (!ok) return;
    }

    const url = '/jump/terminal/' + serverId;
    const w = Math.min(window.screen.availWidth - 60, 1366);
    const h = Math.min(window.screen.availHeight - 80, 820);
    const left = Math.max(0, Math.floor((window.screen.availWidth - w) / 2));
    const top = Math.max(0, Math.floor((window.screen.availHeight - h) / 2));

    const popup = window.open(
        url,
        'JumpPAM_Console_' + serverId,
        `width=${w},height=${h},top=${top},left=${left},menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes`
    );

    if (popup) {
        popup.focus();
    } else {
        window.open(url, '_blank');
    }

    // Refresh portal status after 2 seconds to reflect new active occupant
    setTimeout(() => {
        location.reload();
    }, 2000);
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('jumpSearch');
    const osFilter = document.getElementById('jumpFilterOs');
    const serverItems = document.querySelectorAll('.server-card-item');

    function filterServers() {
        const q = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const os = osFilter ? osFilter.value.toUpperCase() : '';

        serverItems.forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const ip = item.getAttribute('data-ip') || '';
            const itemOs = item.getAttribute('data-os') || '';

            const matchSearch = (!q || name.includes(q) || ip.includes(q));
            const matchOs = (!os || itemOs === os);

            if (matchSearch && matchOs) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterServers);
    if (osFilter) osFilter.addEventListener('change', filterServers);
});
</script>

