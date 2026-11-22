<?php
use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;

$baseUrl = Response::baseUrl();
$user = Auth::user();
?>

<div class="container-fluid py-2 px-3">
    <!-- TOP STATUS BAR -->
    <div class="d-flex flex-wrap justify-content-between align-items-center bg-dark text-white p-3 rounded-3 mb-3 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= $baseUrl ?>/remote-support" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> Rời phòng
            </a>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary font-monospace fs-6 px-2"><?= htmlspecialchars($session['session_code']) ?></span>
                    <h5 class="mb-0 fw-bold text-white">Phiên Hỗ Trợ Từ Xa Trực Tiếp</h5>
                    <span id="connBadge" class="badge bg-warning text-dark">
                        <span class="spinner-grow spinner-grow-sm me-1" style="width:0.5rem; height:0.5rem;"></span> Đang chờ kết nối
                    </span>
                </div>
                <div class="small text-light text-opacity-75 mt-1">
                    Kỹ thuật viên: <strong class="text-white"><?= htmlspecialchars($session['staff_name']) ?></strong> | 
                    Khách hàng: <strong class="text-white"><?= htmlspecialchars($session['client_name']) ?></strong>
                    <?php if (!empty($session['ticket_code'])): ?>
                        | Ticket: <a href="<?= $baseUrl ?>/tickets/<?= $session['ticket_id'] ?>" target="_blank" class="text-info text-decoration-none fw-semibold">#<?= htmlspecialchars($session['ticket_code']) ?> - <?= htmlspecialchars($session['ticket_title']) ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            <?php if ($roleInSession === 'STAFF'): ?>
                <button type="button" id="btnFullscreen" class="btn btn-outline-light btn-sm" title="Xem toàn màn hình">
                    <i class="bi bi-fullscreen me-1"></i> Toàn màn hình
                </button>
            <?php endif; ?>
            <button type="button" id="btnEndSession" class="btn btn-danger btn-sm fw-bold">
                <i class="bi bi-power me-1"></i> Kết Thúc Phiên
            </button>
        </div>
    </div>

    <!-- MAIN INTERACTION WORKSPACE -->
    <div class="row g-3">
        <!-- CỘT TRÁI: KHU VỰC VIDEO CHIA SẺ MÀN HÌNH -->
        <div class="col-12 col-xl-9">
            <div class="card border-0 shadow-sm rounded-3 bg-dark text-white overflow-hidden" style="min-height: 580px; position: relative;">
                
                <!-- BỘ ĐIỀU KHIỂN DÀNH CHO CLIENT (NGƯỜI DÙNG CHIA SẺ MÀN HÌNH) -->
                <?php if ($roleInSession === 'CLIENT'): ?>
                    <div id="clientControlBox" class="p-4 text-center my-auto">
                        <div class="p-4 bg-secondary bg-opacity-25 rounded-4 d-inline-block border border-secondary mb-3" style="max-width: 600px;">
                            <div class="p-3 bg-primary bg-opacity-25 rounded-circle d-inline-block text-primary mb-3">
                                <i class="bi bi-display-fill fs-1 text-white"></i>
                            </div>
                            <h4 class="fw-bold text-white mb-2">Kỹ Thuật Viên IT Đang Đợi Bạn Chia Sẻ Màn Hình</h4>
                            <p class="text-light text-opacity-75 small mb-4">
                                Để kỹ thuật viên có thể xem sự cố và hướng dẫn bạn thao tác, vui lòng bấm vào nút bên dưới và chọn <strong>"Toàn bộ màn hình" (Entire Screen)</strong> trong hộp thoại của trình duyệt.
                            </p>
                            <button type="button" id="btnStartShare" class="btn btn-primary btn-lg fw-bold px-5 py-3 shadow-lg fs-5">
                                <i class="bi bi-broadcast me-2"></i> BẮT ĐẦU CHIA SẺ MÀN HÌNH
                            </button>
                            <div class="small text-warning mt-3">
                                <i class="bi bi-shield-check me-1"></i> Bạn có toàn quyền ngắt kết nối bất kỳ lúc nào chỉ với 1 click.
                            </div>
                        </div>
                    </div>

                    <!-- TRẠNG THÁI KHI CLIENT ĐANG LIVE SHARING -->
                    <div id="clientSharingBanner" class="p-4 text-center my-auto d-none">
                        <div class="p-4 bg-success bg-opacity-10 border border-success rounded-4 d-inline-block" style="max-width: 600px;">
                            <div class="spinner-grow text-success mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                            <h4 class="fw-bold text-success mb-2">MÀN HÌNH CỦA BẠN ĐANG ĐƯỢC CHIA SẺ VỚI IT</h4>
                            <p class="text-light text-opacity-75 small mb-3">
                                Kỹ thuật viên <strong><?= htmlspecialchars($session['staff_name']) ?></strong> đang quan sát trực tiếp màn hình của bạn. Khi kỹ thuật viên chỉ điểm laser, trên màn hình sẽ xuất hiện vòng tròn đỏ nhấp nháy để bạn dễ nhận biết.
                            </p>
                            <!-- Live mini-preview of what client is sharing -->
                            <div class="mb-3 d-flex justify-content-center">
                                <video id="clientLocalPreview" autoplay playsinline muted class="rounded-3 border border-success shadow-sm" style="max-width: 360px; max-height: 200px; width: 100%; object-fit: contain; background: #000;"></video>
                            </div>
                            <div class="d-flex justify-content-center gap-3">
                                <button type="button" id="btnStopShare" class="btn btn-outline-danger fw-bold px-4">
                                    <i class="bi bi-pause-circle me-1"></i> Tạm dừng chia sẻ
                                </button>
                                <button type="button" class="btn btn-danger fw-bold px-4" onclick="document.getElementById('btnEndSession').click();">
                                    <i class="bi bi-x-circle me-1"></i> Ngắt kết nối ngay
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- CONTAINER VIDEO STREAMING CHO KỸ THUẬT VIÊN (STAFF) HOẶC PREVIEW -->
                <div id="videoContainer" class="w-100 h-100 d-flex align-items-center justify-content-center position-relative <?= ($roleInSession === 'CLIENT') ? 'd-none' : '' ?>" style="background: #0d1117; min-height: 560px;">
                    <!-- Loading placeholder for staff -->
                    <div id="staffWaitingBox" class="text-center p-4">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                        <h5 class="fw-bold text-white mb-2">Đang Đợi Người Dùng Cấp Quyền Chia Sẻ Màn Hình...</h5>
                        <p class="text-muted small mb-0">Hệ thống đã gửi thông báo đến màn hình của <strong><?= htmlspecialchars($session['client_name']) ?></strong>.</p>
                        <div class="mt-3">
                            <span class="badge bg-secondary font-monospace p-2">MÃ KẾT NỐI: <?= htmlspecialchars($session['session_code']) ?></span>
                        </div>
                    </div>

                    <!-- Live Video Element (muted required for autoplay) -->
                    <video id="remoteVideo" autoplay playsinline muted class="w-100 h-100 d-none" style="object-fit: contain; max-height: 75vh; cursor: crosshair;"></video>

                    <!-- Guaranteed Live Fallback Frame Image (Zero-latency snapshot stream) -->
                    <img id="remoteFrameImg" class="w-100 h-100 d-none" style="object-fit: contain; max-height: 75vh; cursor: crosshair;" alt="Live Screen Feed" />

                    <!-- Laser Pointer Layer (Canvas overlay) -->
                    <canvas id="pointerOverlay" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; z-index: 10;"></canvas>

                    <!-- Staff Toolbar Overlay when streaming -->
                    <div id="videoToolbar" class="position-absolute bottom-0 start-50 translate-middle-x mb-3 px-3 py-2 bg-dark bg-opacity-75 rounded-pill border border-secondary d-none align-items-center gap-2" style="z-index: 20;">
                        <span class="badge bg-success me-2"><i class="bi bi-record-fill me-1 text-danger"></i> LIVE</span>
                        <button type="button" id="btnToggleLaser" class="btn btn-sm btn-outline-warning rounded-pill active" title="Bật/Tắt chế độ chỉ điểm Laser (Click vào video để chỉ điểm)">
                            <i class="bi bi-cursor-fill me-1"></i> Bút Chỉ Điểm (Laser)
                        </button>
                        <button type="button" id="btnTakeScreenshot" class="btn btn-sm btn-outline-info rounded-pill" title="Chụp ảnh màn hình lưu bằng chứng">
                            <i class="bi bi-camera me-1"></i> Chụp Màn Hình
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- CỘT PHẢI: CÔNG CỤ TRỢ GIÚP, CHAT, CLIPBOARD & ULTRAVIEWER -->
        <div class="col-12 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 d-flex flex-column" style="min-height: 580px;">
                <!-- Tabs -->
                <div class="card-header bg-white border-bottom p-0">
                    <ul class="nav nav-tabs border-0 px-2 pt-2" id="sideTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active small fw-bold" id="chat-tab" data-bs-toggle="tab" data-bs-target="#tabChat" type="button">
                                <i class="bi bi-chat-dots me-1 text-primary"></i> Trao Đổi
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link small fw-bold" id="tools-tab" data-bs-toggle="tab" data-bs-target="#tabTools" type="button">
                                <i class="bi bi-terminal me-1 text-success"></i> Lệnh Hỗ Trợ
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link small fw-bold" id="uv-tab" data-bs-toggle="tab" data-bs-target="#tabUV" type="button">
                                <i class="bi bi-laptop me-1 text-info"></i> UltraViewer
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0 flex-grow-1 tab-content d-flex flex-column">
                    <!-- TAB 1: CHAT TRONG PHIÊN -->
                    <div class="tab-pane fade show active p-3 flex-grow-1 d-flex flex-column" id="tabChat" role="tabpanel">
                        <div id="chatMessages" class="flex-grow-1 overflow-auto p-2 bg-light rounded-3 mb-3" style="max-height: 380px; min-height: 300px; font-size: 0.85rem;">
                            <div class="text-center text-muted small my-2">
                                <i class="bi bi-shield-lock me-1"></i> Phiên kết nối bảo mật đã khởi tạo.
                            </div>
                        </div>

                        <form id="chatForm" class="d-flex gap-2">
                            <input type="text" id="chatInput" class="form-control form-control-sm" placeholder="Nhập tin nhắn trao đổi..." autocomplete="off">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>

                    <!-- TAB 2: LỆNH HỖ TRỢ IT & CLIPBOARD -->
                    <div class="tab-pane fade p-3 overflow-auto" id="tabTools" role="tabpanel">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Gửi văn bản / Lệnh sang máy khách:</label>
                            <div class="input-group input-group-sm mb-2">
                                <input type="text" id="customCmdInput" class="form-control" placeholder="Nhập link, lệnh, hoặc mật khẩu...">
                                <button type="button" id="btnSendCmd" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Gửi
                                </button>
                            </div>
                            <div class="small text-muted" style="font-size:0.75rem;">Nội dung sẽ hiện popup kèm nút "1-Click Copy" trên màn hình người dùng.</div>
                        </div>

                        <label class="form-label fw-bold small text-muted text-uppercase mb-2">Các lệnh IT thường dùng:</label>
                        <div class="d-flex flex-column gap-1">
                            <button type="button" class="btn btn-light btn-sm text-start border d-flex justify-content-between align-items-center quick-cmd" data-cmd="ipconfig /flushdns">
                                <code>ipconfig /flushdns</code>
                                <span class="badge bg-secondary">Xóa DNS cache</span>
                            </button>
                            <button type="button" class="btn btn-light btn-sm text-start border d-flex justify-content-between align-items-center quick-cmd" data-cmd="ping 8.8.8.8 -t">
                                <code>ping 8.8.8.8</code>
                                <span class="badge bg-secondary">Test mạng</span>
                            </button>
                            <button type="button" class="btn btn-light btn-sm text-start border d-flex justify-content-between align-items-center quick-cmd" data-cmd="gpupdate /force">
                                <code>gpupdate /force</code>
                                <span class="badge bg-secondary">Cập nhật Policy</span>
                            </button>
                            <button type="button" class="btn btn-light btn-sm text-start border d-flex justify-content-between align-items-center quick-cmd" data-cmd="devmgmt.msc">
                                <code>devmgmt.msc</code>
                                <span class="badge bg-secondary">Device Manager</span>
                            </button>
                            <button type="button" class="btn btn-light btn-sm text-start border d-flex justify-content-between align-items-center quick-cmd" data-cmd="appwiz.cpl">
                                <code>appwiz.cpl</code>
                                <span class="badge bg-secondary">Gỡ phần mềm</span>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 3: ULTRAVIEWER / ANYDESK DỰ PHÒNG -->
                    <div class="tab-pane fade p-3" id="tabUV" role="tabpanel">
                        <div class="p-3 bg-light rounded-3 mb-3">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Phương án dự phòng:</h6>
                            <p class="text-muted small mb-0">Nếu không thể chia sẻ màn hình qua trình duyệt, người dùng có thể gửi ID/Pass UltraViewer hoặc AnyDesk tại đây để IT điều khiển.</p>
                        </div>

                        <?php if ($roleInSession === 'CLIENT'): ?>
                            <form id="uvForm">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">ID UltraViewer / AnyDesk:</label>
                                    <input type="text" id="uvId" class="form-control form-control-sm font-monospace" placeholder="Ví dụ: 12 345 678" value="<?= htmlspecialchars($session['ultraviewer_id'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Mật khẩu (Password):</label>
                                    <input type="text" id="uvPass" class="form-control form-control-sm font-monospace" placeholder="Ví dụ: 9876" value="<?= htmlspecialchars($session['ultraviewer_pass'] ?? '') ?>">
                                </div>
                                <button type="submit" class="btn btn-info btn-sm fw-bold w-100 text-white">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Gửi ID & Mật Khẩu Cho IT
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">ID UltraViewer / AnyDesk của khách:</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="staffUvId" class="form-control font-monospace fw-bold text-primary" readonly value="<?= htmlspecialchars($session['ultraviewer_id'] ?: 'Chưa cung cấp') ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('staffUvId').value)"><i class="bi bi-clipboard"></i></button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Mật khẩu (Password):</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="staffUvPass" class="form-control font-monospace fw-bold text-danger" readonly value="<?= htmlspecialchars($session['ultraviewer_pass'] ?: 'Chưa cung cấp') ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('staffUvPass').value)"><i class="bi bi-clipboard"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CLIENT LASER POINTER & COMMAND POPUP OVERLAY -->
<div id="clientLaserRipple" style="display:none; position:fixed; width:40px; height:40px; border-radius:50%; border:3px solid #ff0055; background:rgba(255,0,85,0.3); pointer-events:none; z-index:99999; transform:translate(-50%, -50%); transition: all 0.2s ease-out; animation: rippleAnim 1.2s infinite;"></div>

<style>
@keyframes rippleAnim {
    0% { transform: translate(-50%, -50%) scale(0.6); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(2.2); opacity: 0; }
}
.letter-spacing-1 { letter-spacing: 2px; }
</style>

<!-- ===================================================================== -->
<!-- REALTIME WEBRTC & SIGNALING SCRIPT -->
<!-- ===================================================================== -->
<script>
(function() {
    const SESSION_CODE = <?= json_encode($session['session_code']) ?>;
    const ROLE = <?= json_encode($roleInSession) ?>; // 'STAFF' or 'CLIENT'
    const BASE_URL = window.APP_BASE_URL || '';
    const CSRF_TOKEN = <?= json_encode(Session::csrfToken()) ?>;

    let localStream = null;
    let peerConnection = null;
    let lastSignalId = 0;
    let pollInterval = null;
    let frameBroadcastInterval = null;
    let isLaserEnabled = true;
    let candidateQueue = [];

    const rtcConfig = {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' },
            { urls: 'stun:stun2.l.google.com:19302' },
            { urls: 'stun:stun3.l.google.com:19302' },
            { urls: 'stun:stun4.l.google.com:19302' }
        ]
    };

    // UI elements
    const connBadge = document.getElementById('connBadge');
    const remoteVideo = document.getElementById('remoteVideo');
    const remoteFrameImg = document.getElementById('remoteFrameImg');
    const staffWaitingBox = document.getElementById('staffWaitingBox');
    const videoToolbar = document.getElementById('videoToolbar');
    const videoContainer = document.getElementById('videoContainer');
    const chatMessages = document.getElementById('chatMessages');

    function updateBadge(status, text) {
        if (!connBadge) return;
        connBadge.className = 'badge ' + (status === 'connected' ? 'bg-success text-white' : (status === 'waiting' ? 'bg-warning text-dark' : 'bg-danger text-white'));
        connBadge.innerHTML = (status === 'connected' ? '<i class="bi bi-check-circle-fill me-1"></i> ' : '') + text;
    }

    function appendMessage(sender, text, isMe) {
        if (!chatMessages) return;
        const div = document.createElement('div');
        div.className = 'mb-2 p-2 rounded-3 ' + (isMe ? 'bg-primary text-white ms-auto' : 'bg-white border text-dark me-auto');
        div.style.maxWidth = '85%';
        div.innerHTML = `<div style="font-size:0.7rem; opacity:0.8;">${sender}</div><div>${text}</div>`;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Signaling helper with CSRF header & JSON payload
    async function sendSignal(type, payload) {
        try {
            const res = await fetch(BASE_URL + '/remote-support/api/signal', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({
                    session_code: SESSION_CODE,
                    sender_type: ROLE,
                    signal_type: type,
                    payload: payload,
                    _csrf_token: CSRF_TOKEN
                })
            });
            const data = await res.json();
            if (!data.success) {
                console.warn('[Remote] sendSignal rejected:', data);
            }
        } catch (e) {
            console.error('[Remote] sendSignal error:', e);
        }
    }

    // Queue-safe ICE Candidate helper
    async function addCandidateSafely(candidate) {
        if (!peerConnection || !peerConnection.remoteDescription || !peerConnection.remoteDescription.type) {
            candidateQueue.push(candidate);
            return;
        }
        try {
            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        } catch (e) {
            console.warn('[Remote] addIceCandidate catch:', e);
        }
    }

    async function drainCandidates() {
        while (candidateQueue.length > 0) {
            const c = candidateQueue.shift();
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(c));
            } catch (e) {
                console.warn('[Remote] drainCandidates catch:', e);
            }
        }
    }

    // Initialize WebRTC PeerConnection
    function createPeer() {
        if (peerConnection) {
            try { peerConnection.close(); } catch (e) {}
        }
        peerConnection = new RTCPeerConnection(rtcConfig);

        peerConnection.onicecandidate = function(event) {
            if (event.candidate) {
                sendSignal('CANDIDATE', event.candidate);
            }
        };

        peerConnection.ontrack = function(event) {
            console.log('[Remote] ontrack stream received:', event.streams);
            const stream = (event.streams && event.streams[0]) ? event.streams[0] : new MediaStream([event.track]);
            if (remoteVideo) {
                remoteVideo.srcObject = stream;
                remoteVideo.muted = true;

                remoteVideo.onloadedmetadata = function() {
                    console.log('[Remote] Video loadedmetadata:', remoteVideo.videoWidth, 'x', remoteVideo.videoHeight);
                    remoteVideo.play().catch(e => console.warn(e));
                };

                event.track.onunmute = function() {
                    console.log('[Remote] Video track unmuted! Playing stream...');
                    remoteVideo.play().catch(e => console.warn(e));
                };

                remoteVideo.onplaying = function() {
                    console.log('[Remote] WebRTC stream is live and playing!');
                    if (remoteFrameImg) remoteFrameImg.classList.add('d-none');
                    remoteVideo.classList.remove('d-none');
                    if (staffWaitingBox) staffWaitingBox.classList.add('d-none');
                    if (videoToolbar) {
                        videoToolbar.classList.remove('d-none');
                        videoToolbar.classList.add('d-flex');
                    }
                    updateBadge('connected', 'Đang kết nối Live (WebRTC 60 FPS)');
                };

                remoteVideo.play().catch(e => console.warn('[Remote] Autoplay catch:', e));
            }
        };

        peerConnection.oniceconnectionstatechange = function() {
            console.log('[Remote] ICE state:', peerConnection.iceConnectionState);
            if (peerConnection.iceConnectionState === 'connected' || peerConnection.iceConnectionState === 'completed') {
                updateBadge('connected', 'Đã kết nối Live');
            } else if (peerConnection.iceConnectionState === 'disconnected' || peerConnection.iceConnectionState === 'failed') {
                console.warn('[Remote] ICE interrupted, relying on frame broadcaster...');
            }
        };
    }

    // Offscreen Canvas Frame Broadcaster (Guarantees zero-latency image streaming even if WebRTC UDP blocked)
    const offscreenCanvas = document.createElement('canvas');
    let hiddenVideoElem = null;

    function startFrameBroadcaster() {
        if (!hiddenVideoElem) {
            hiddenVideoElem = document.createElement('video');
            hiddenVideoElem.muted = true;
            hiddenVideoElem.playsInline = true;
        }
        hiddenVideoElem.srcObject = localStream;
        hiddenVideoElem.play().catch(e => {});

        if (frameBroadcastInterval) clearInterval(frameBroadcastInterval);

        // Immediate snapshot
        setTimeout(captureAndBroadcastFrame, 400);

        // Send a compressed JPEG snapshot every 1.2s
        frameBroadcastInterval = setInterval(captureAndBroadcastFrame, 1200);
    }

    function captureAndBroadcastFrame() {
        if (!localStream || !hiddenVideoElem || !hiddenVideoElem.videoWidth) return;

        const maxWidth = 960;
        const scale = Math.min(1, maxWidth / hiddenVideoElem.videoWidth);
        offscreenCanvas.width = Math.round(hiddenVideoElem.videoWidth * scale);
        offscreenCanvas.height = Math.round(hiddenVideoElem.videoHeight * scale);

        const ctx = offscreenCanvas.getContext('2d');
        ctx.drawImage(hiddenVideoElem, 0, 0, offscreenCanvas.width, offscreenCanvas.height);
        const dataUrl = offscreenCanvas.toDataURL('image/jpeg', 0.52);

        sendSignal('FRAME', dataUrl);
    }

    function stopFrameBroadcaster() {
        if (frameBroadcastInterval) {
            clearInterval(frameBroadcastInterval);
            frameBroadcastInterval = null;
        }
        if (hiddenVideoElem) {
            hiddenVideoElem.srcObject = null;
        }
    }

    // CLIENT: Start Screen Sharing
    const btnStartShare = document.getElementById('btnStartShare');
    if (btnStartShare) {
        btnStartShare.addEventListener('click', async function() {
            try {
                localStream = await navigator.mediaDevices.getDisplayMedia({
                    video: { cursor: 'always', frameRate: 30 },
                    audio: false
                });

                // Attach to client preview
                const clientPreview = document.getElementById('clientLocalPreview');
                if (clientPreview) {
                    clientPreview.srcObject = localStream;
                }

                document.getElementById('clientControlBox').classList.add('d-none');
                document.getElementById('clientSharingBanner').classList.remove('d-none');
                updateBadge('connected', 'Đang chia sẻ màn hình');

                createPeer();
                localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

                // Handle user stopping share via browser native button
                localStream.getVideoTracks()[0].onended = function() {
                    handleStopSharing();
                };

                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                console.log('[Remote] Local Offer created & sent');
                await sendSignal('OFFER', offer);

                // Start guaranteed fallback frame broadcaster
                startFrameBroadcaster();

            } catch (err) {
                console.error('[Remote] getDisplayMedia error:', err);
                alert('Không thể chia sẻ màn hình: ' + err.message);
            }
        });
    }

    function handleStopSharing() {
        stopFrameBroadcaster();
        if (localStream) {
            localStream.getTracks().forEach(t => t.stop());
            localStream = null;
        }
        const clientPreview = document.getElementById('clientLocalPreview');
        if (clientPreview) clientPreview.srcObject = null;

        document.getElementById('clientControlBox').classList.remove('d-none');
        document.getElementById('clientSharingBanner').classList.add('d-none');
        updateBadge('waiting', 'Đã tạm dừng chia sẻ');
        sendSignal('STATUS', { status: 'PAUSED' });
    }

    const btnStopShare = document.getElementById('btnStopShare');
    if (btnStopShare) {
        btnStopShare.addEventListener('click', handleStopSharing);
    }

    // STAFF: Laser pointer visual ripple on canvas
    function drawStaffLaser(xRatio, yRatio) {
        const canvas = document.getElementById('pointerOverlay');
        if (!canvas) return;
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        const ctx = canvas.getContext('2d');
        const cx = xRatio * canvas.width;
        const cy = yRatio * canvas.height;
        
        let radius = 5;
        const anim = setInterval(() => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.beginPath();
            ctx.arc(cx, cy, radius, 0, 2 * Math.PI);
            ctx.fillStyle = 'rgba(255, 0, 85, 0.35)';
            ctx.fill();
            ctx.lineWidth = 3;
            ctx.strokeStyle = '#ff0055';
            ctx.stroke();
            radius += 4;
            if (radius > 40) {
                clearInterval(anim);
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }, 25);
    }

    // STAFF: Laser pointer interaction on clicking videoContainer
    if (videoContainer && ROLE === 'STAFF') {
        videoContainer.addEventListener('click', function(e) {
            if (!isLaserEnabled) return;
            if (e.target.closest('#videoToolbar') || e.target.closest('#staffWaitingBox')) return;

            const targetElem = (!remoteVideo.classList.contains('d-none')) ? remoteVideo : remoteFrameImg;
            const rect = (targetElem && !targetElem.classList.contains('d-none')) 
                ? targetElem.getBoundingClientRect() 
                : videoContainer.getBoundingClientRect();

            const x = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const y = Math.max(0, Math.min(1, (e.clientY - rect.top) / rect.height));

            drawStaffLaser(x, y);
            sendSignal('POINTER', { x: x, y: y });
        });
    }

    // STAFF: Toggle Laser Button
    const btnToggleLaser = document.getElementById('btnToggleLaser');
    if (btnToggleLaser) {
        btnToggleLaser.addEventListener('click', function(e) {
            e.stopPropagation();
            isLaserEnabled = !isLaserEnabled;
            this.classList.toggle('active', isLaserEnabled);
            this.classList.toggle('btn-warning', isLaserEnabled);
            this.classList.toggle('btn-outline-warning', !isLaserEnabled);
        });
    }

    // STAFF: Screenshot
    const btnTakeScreenshot = document.getElementById('btnTakeScreenshot');
    if (btnTakeScreenshot) {
        btnTakeScreenshot.addEventListener('click', function(e) {
            e.stopPropagation();
            if (remoteVideo && !remoteVideo.classList.contains('d-none') && remoteVideo.videoWidth > 0) {
                const canvas = document.createElement('canvas');
                canvas.width = remoteVideo.videoWidth;
                canvas.height = remoteVideo.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(remoteVideo, 0, 0, canvas.width, canvas.height);
                downloadImg(canvas.toDataURL('image/png'));
            } else if (remoteFrameImg && remoteFrameImg.src) {
                downloadImg(remoteFrameImg.src);
            }
        });
    }

    function downloadImg(dataUrl) {
        const a = document.createElement('a');
        a.href = dataUrl;
        a.download = `Screenshot_${SESSION_CODE}_${Date.now()}.png`;
        a.click();
    }

    // STAFF: Fullscreen
    const btnFullscreen = document.getElementById('btnFullscreen');
    if (btnFullscreen) {
        btnFullscreen.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                videoContainer.requestFullscreen().catch(err => {});
            } else {
                document.exitFullscreen();
            }
        });
    }

    // CHAT FORM
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    if (chatForm && chatInput) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;
            appendMessage('Tôi', text, true);
            sendSignal('CHAT', { message: text });
            chatInput.value = '';
        });
    }

    // SEND COMMAND / CLIPBOARD
    const btnSendCmd = document.getElementById('btnSendCmd');
    const customCmdInput = document.getElementById('customCmdInput');
    if (btnSendCmd && customCmdInput) {
        btnSendCmd.addEventListener('click', function() {
            const cmd = customCmdInput.value.trim();
            if (!cmd) return;
            sendSignal('COMMAND', { command: cmd });
            appendMessage('Lệnh đã gửi', cmd, true);
            customCmdInput.value = '';
            alert('Đã gửi lệnh sang máy khách!');
        });
    }

    // QUICK COMMANDS
    document.querySelectorAll('.quick-cmd').forEach(btn => {
        btn.addEventListener('click', function() {
            const cmd = this.getAttribute('data-cmd');
            if (customCmdInput) customCmdInput.value = cmd;
            if (btnSendCmd) btnSendCmd.click();
        });
    });

    // ULTRAVIEWER FORM
    const uvForm = document.getElementById('uvForm');
    if (uvForm) {
        uvForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const uvId = document.getElementById('uvId').value.trim();
            const uvPass = document.getElementById('uvPass').value.trim();
            try {
                await fetch(BASE_URL + '/remote-support/api/ultraviewer/' + SESSION_CODE, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({ ultraviewer_id: uvId, ultraviewer_pass: uvPass, _csrf_token: CSRF_TOKEN })
                });
                alert('Đã gửi thông tin UltraViewer sang Kỹ thuật viên!');
            } catch (err) {
                alert('Lỗi: ' + err.message);
            }
        });
    }

    // END SESSION
    const btnEndSession = document.getElementById('btnEndSession');
    if (btnEndSession) {
        btnEndSession.addEventListener('click', async function() {
            if (!confirm('Bạn có chắc chắn muốn kết thúc phiên hỗ trợ từ xa này không?')) return;
            try {
                await fetch(BASE_URL + '/remote-support/api/status/' + SESSION_CODE, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({ status: 'ENDED', _csrf_token: CSRF_TOKEN })
                });
            } catch (e) {}
            stopFrameBroadcaster();
            if (localStream) localStream.getTracks().forEach(t => t.stop());
            if (peerConnection) peerConnection.close();
            window.location.href = BASE_URL + '/remote-support';
        });
    }

    // SIGNAL POLLER
    async function pollSignals() {
        try {
            const res = await fetch(`${BASE_URL}/remote-support/api/signal/${SESSION_CODE}?for_type=${ROLE}&after_id=${lastSignalId}`);
            const data = await res.json();
            if (data.success) {
                if (data.session_status === 'ENDED') {
                    updateBadge('danger', 'Phiên hỗ trợ đã kết thúc');
                    stopFrameBroadcaster();
                    if (ROLE === 'CLIENT' && localStream) {
                        localStream.getTracks().forEach(t => t.stop());
                    }
                    clearInterval(pollInterval);
                    alert('Phiên hỗ trợ đã được kết thúc bởi kỹ thuật viên.');
                    window.location.href = BASE_URL + '/remote-support';
                    return;
                }

                // Update Ultraviewer on staff side
                if (ROLE === 'STAFF') {
                    if (data.ultraviewer_id && document.getElementById('staffUvId')) {
                        document.getElementById('staffUvId').value = data.ultraviewer_id;
                    }
                    if (data.ultraviewer_pass && document.getElementById('staffUvPass')) {
                        document.getElementById('staffUvPass').value = data.ultraviewer_pass;
                    }
                }

                for (const sig of data.signals) {
                    lastSignalId = Math.max(lastSignalId, parseInt(sig.id));
                    const payload = typeof sig.payload === 'string' ? JSON.parse(sig.payload) : sig.payload;

                    if (sig.signal_type === 'OFFER' && ROLE === 'STAFF') {
                        console.log('[Remote] Staff processing OFFER from client...');
                        createPeer();
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(payload));
                        await drainCandidates();
                        const answer = await peerConnection.createAnswer();
                        await peerConnection.setLocalDescription(answer);
                        console.log('[Remote] Staff setLocalDescription ANSWER, sending...');
                        await sendSignal('ANSWER', answer);
                    }
                    else if (sig.signal_type === 'ANSWER' && ROLE === 'CLIENT') {
                        console.log('[Remote] Client processing ANSWER from staff...');
                        if (peerConnection) {
                            await peerConnection.setRemoteDescription(new RTCSessionDescription(payload));
                            await drainCandidates();
                        }
                    }
                    else if (sig.signal_type === 'CANDIDATE') {
                        await addCandidateSafely(payload);
                    }
                    else if (sig.signal_type === 'FRAME' && ROLE === 'STAFF') {
                        if (remoteFrameImg) {
                            remoteFrameImg.src = payload;

                            // Display image feed if WebRTC video has not started rendering yet
                            if (!remoteVideo || remoteVideo.classList.contains('d-none') || remoteVideo.videoWidth === 0) {
                                remoteFrameImg.classList.remove('d-none');
                                if (remoteVideo) remoteVideo.classList.add('d-none');
                                if (staffWaitingBox) staffWaitingBox.classList.add('d-none');
                                if (videoToolbar) {
                                    videoToolbar.classList.remove('d-none');
                                    videoToolbar.classList.add('d-flex');
                                }
                                updateBadge('connected', 'Đang kết nối Live (Đang xem màn hình)');
                            }
                        }
                    }
                    else if (sig.signal_type === 'CHAT') {
                        appendMessage(ROLE === 'STAFF' ? 'Khách hàng' : 'Kỹ thuật viên', payload.message, false);
                    }
                    else if (sig.signal_type === 'POINTER' && ROLE === 'CLIENT') {
                        // Show laser ripple on client's screen
                        const ripple = document.getElementById('clientLaserRipple');
                        if (ripple) {
                            const x = payload.x * window.innerWidth;
                            const y = payload.y * window.innerHeight;
                            ripple.style.left = x + 'px';
                            ripple.style.top = y + 'px';
                            ripple.style.display = 'block';
                            clearTimeout(window.rippleTimeout);
                            window.rippleTimeout = setTimeout(() => { ripple.style.display = 'none'; }, 4000);
                        }
                    }
                    else if (sig.signal_type === 'COMMAND' && ROLE === 'CLIENT') {
                        // Popup instruction for user
                        const cmd = payload.command;
                        appendMessage('IT gửi lệnh', `<code>${cmd}</code>`, false);
                        if (confirm(`Kỹ thuật viên IT gửi cho bạn lệnh/văn bản sau:\n\n"${cmd}"\n\nBạn có muốn sao chép lệnh này vào bộ nhớ tạm không?`)) {
                            navigator.clipboard.writeText(cmd);
                            alert('Đã sao chép vào bộ nhớ tạm! Bạn có thể nhấn Ctrl + V để dán.');
                        }
                    }
                }
            }
        } catch (err) {
            console.error('pollSignals error:', err);
        }
    }

    // Start polling every 750ms for responsive interaction
    pollInterval = setInterval(pollSignals, 750);
    pollSignals();
})();
</script>
