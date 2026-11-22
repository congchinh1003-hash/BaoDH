/**
 * IT SERVICE DESK - CORE JAVASCRIPT & LIVE SLA COUNTDOWN
 */

document.addEventListener('DOMContentLoaded', () => {
    initSlaCountdowns();
    initCategoryServiceCascade();
});

/**
 * Live SLA Countdown Engine
 * Calculates real-time difference between now and due date
 */
function initSlaCountdowns() {
    const countdownElements = document.querySelectorAll('.sla-live-timer');
    if (!countdownElements.length) return;

    function updateTimers() {
        const now = new Date().getTime();

        countdownElements.forEach(el => {
            const status = (el.getAttribute('data-status') || '').toUpperCase();

            // Completed or stopped states do NOT run countdown
            if (['RESOLVED', 'CLOSED', 'CANCELLED', 'DUPLICATE'].includes(status)) {
                return;
            }

            const isPaused = el.getAttribute('data-paused') === '1' || ['WAITING_USER', 'WAITING_VENDOR'].includes(status);
            if (isPaused) {
                const pauseLabel = status === 'WAITING_VENDOR' ? 'Chờ Vendor' : 'Chờ User';
                el.innerHTML = `<span class="badge border" style="background:#FFF4E0; color:#974F0C; border-color:#FF991F !important; border-radius:999px;"><i class="bi bi-pause-fill me-1"></i>Tạm dừng (${pauseLabel})</span>`;
                return;
            }

            const dueStr = el.getAttribute('data-due');
            if (!dueStr) {
                el.innerHTML = '<span class="text-muted small">Không áp dụng SLA</span>';
                return;
            }

            // Parse due date
            const dueTime = new Date(dueStr.replace(/-/g, '/')).getTime();
            if (isNaN(dueTime)) {
                return;
            }

            const diff = dueTime - now;

            if (diff > 0) {
                // Within SLA
                const hours = Math.floor(diff / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                const formatted = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                
                // Color coding based on remaining time
                let badgeClass = 'text-primary';
                if (hours < 1) {
                    badgeClass = 'text-danger fw-bold';
                } else if (hours < 4) {
                    badgeClass = 'text-warning fw-bold';
                }

                el.innerHTML = `<span class="${badgeClass}"><i class="bi bi-stopwatch me-1"></i>Còn lại: ${formatted}</span>`;
            } else {
                // OVERDUE
                const overdueDiff = Math.abs(diff);
                const days = Math.floor(overdueDiff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((overdueDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((overdueDiff % (1000 * 60)) / 1000);
                const seconds = Math.floor((overdueDiff % 1000) / 1000);

                let formatted = '';
                if (days > 0) {
                    formatted = `${days} ngày ${hours} giờ`;
                } else {
                    formatted = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                }

                el.innerHTML = `<span class="badge text-white" style="background:#DE350B; border-radius:999px; font-size:0.68rem;"><i class="bi bi-alarm-fill me-1"></i>QUÁ SLA ${formatted}</span>`;
            }
        });
    }

    updateTimers();
    setInterval(updateTimers, 1000);
}

/**
 * Cascade load Services when Category dropdown changes
 */
function initCategoryServiceCascade() {
    const categorySelect = document.getElementById('ticket_category_select');
    const serviceSelect = document.getElementById('ticket_service_select');

    if (!categorySelect || !serviceSelect) return;

    categorySelect.addEventListener('change', async (e) => {
        const categoryId = e.target.value;
        serviceSelect.innerHTML = '<option value="">-- Đang tải dịch vụ... --</option>';

        if (!categoryId) {
            serviceSelect.innerHTML = '<option value="">-- Chọn danh mục trước --</option>';
            return;
        }

        try {
            const baseUrl = window.APP_BASE_URL || '';
            const res = await fetch(`${baseUrl}/api/services?category_id=${categoryId}`);
            const data = await res.json();

            if (data.success && data.data) {
                let options = '<option value="">-- Chọn dịch vụ cụ thể --</option>';
                data.data.forEach(svc => {
                    options += `<option value="${svc.id}">${svc.name}</option>`;
                });
                serviceSelect.innerHTML = options;
            } else {
                serviceSelect.innerHTML = '<option value="">Không có dịch vụ con</option>';
            }
        } catch (err) {
            console.error('Error fetching services:', err);
            serviceSelect.innerHTML = '<option value="">Lỗi tải danh sách dịch vụ</option>';
        }
    });
}

