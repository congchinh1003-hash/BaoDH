<?php
use App\Core\Auth;
use App\Core\Response;

$baseUrl = Response::baseUrl();
$currentUser = Auth::user();
?>

<!-- AI KNOWLEDGE CHATBOT POPUP WIDGET (BOTTOM-LEFT DOCKED) -->
<div id="kbChatbotContainer" class="kb-chatbot-container">
    <!-- 1. FLOATING ACTION BUTTON (Dính dưới góc trái) -->
    <button type="button" id="kbChatbotToggleBtn" class="kb-chatbot-toggle-btn shadow-lg" title="Mở Trợ Lý AI Kỹ Thuật & Cơ Sở Tri Thức">
        <div class="d-flex align-items-center gap-2">
            <div class="kb-bot-avatar-ring">
                <i class="bi bi-robot fs-5"></i>
                <span class="kb-online-indicator"></span>
            </div>
            <span class="kb-bot-label fw-bold small d-none d-sm-inline">AI Trợ Lý Tri Thức</span>
            <span class="badge bg-warning text-dark px-1.5 py-0.5 rounded-pill fw-bold" style="font-size:0.65rem;">
                <i class="bi bi-stars"></i> IT
            </span>
        </div>
    </button>

    <!-- 2. POPUP CHAT WINDOW -->
    <div id="kbChatbotWindow" class="kb-chatbot-window shadow-2xl border" style="display: none;">
        <!-- Header -->
        <div class="kb-chat-header d-flex align-items-center justify-content-between p-3 border-bottom text-white">
            <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                <div class="kb-header-icon-box">
                    <i class="bi bi-robot fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="fw-bold small text-truncate">AI Trợ Lý Tri Thức &amp; Kỹ Thuật IT</div>
                    <div class="kb-header-status text-truncate">
                        <span class="kb-status-dot"></span> Sẵn sàng hỗ trợ 24/7 • Tra cứu tri thức
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-link text-white-50 p-1 hover-white" id="kbChatClearBtn" title="Làm mới cuộc trò chuyện">
                    <i class="bi bi-arrow-counterclockwise fs-6"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-white-50 p-1 hover-white" id="kbChatExpandBtn" title="Phóng to / Thu nhỏ">
                    <i class="bi bi-arrows-angle-expand fs-6" id="kbChatExpandIcon"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-white p-1 hover-white" id="kbChatCloseBtn" title="Đóng cửa sổ">
                    <i class="bi bi-dash-lg fs-5"></i>
                </button>
            </div>
        </div>

        <!-- Chat Stream Body -->
        <div class="kb-chat-body p-3" id="kbChatMessages">
            <!-- Welcome Message -->
            <div class="kb-msg-bubble-ai mb-3">
                <div class="d-flex gap-2">
                    <div class="kb-msg-avatar flex-shrink-0">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="kb-msg-content flex-grow-1">
                        <div class="fw-semibold text-dark mb-1">Xin chào <?= htmlspecialchars($currentUser['full_name'] ?? 'bạn') ?>! 👋</div>
                        <p class="small text-muted mb-2">Tôi là <strong>AI Trợ Lý Tri Thức</strong> của hệ thống TaskFlow IT Service Desk. Tôi có thể giải đáp các sự cố kỹ thuật máy tính, mạng, máy in, phần mềm và hướng dẫn bạn sử dụng hệ thống.</p>
                        
                        <div class="small fw-bold text-secondary mb-1.5">Bạn có thể hỏi nhanh hoặc chọn gợi ý bên dưới:</div>
                        <div class="d-flex flex-wrap gap-1.5 kb-quick-prompts">
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark kb-prompt-btn" data-prompt="Tôi muốn đổi mật khẩu máy tính thì làm thế nào?">
                                🔐 Đổi mật khẩu Windows
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark kb-prompt-btn" data-prompt="Hướng dẫn kết nối VPN làm việc từ xa">
                                🌐 Kết nối VPN từ xa
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark kb-prompt-btn" data-prompt="Cách kết nối máy in mạng tầng 2 và tầng 3">
                                🖨️ Kết nối máy in văn phòng
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark kb-prompt-btn" data-prompt="Outlook báo Disconnected không nhận được thư">
                                ✉️ Lỗi Outlook Disconnected
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark kb-prompt-btn" data-prompt="Ổ C của tôi bị đầy báo màu đỏ cần dọn dẹp">
                                💾 Dọn dẹp ổ đĩa C
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark kb-prompt-btn" data-prompt="Hướng dẫn tạo phiếu hỗ trợ kỹ thuật ticket">
                                🎫 Cách tạo phiếu hỗ trợ IT
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark kb-prompt-btn" data-prompt="Hướng dẫn tạo backlog và phân việc dự án">
                                📋 Tạo Backlog dự án
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="kbChatTyping" class="px-3 py-1.5 d-none bg-light border-top">
            <div class="d-flex align-items-center gap-2 text-muted" style="font-size:0.75rem;">
                <div class="spinner-grow spinner-grow-sm text-primary" role="status" style="width:0.65rem; height:0.65rem;"></div>
                <span>AI đang phân tích câu hỏi và tra cứu tri thức...</span>
            </div>
        </div>

        <!-- Input Footer -->
        <div class="kb-chat-footer p-2.5 bg-white border-top">
            <form id="kbChatForm" class="d-flex align-items-center gap-1.5 m-0">
                <input type="text" id="kbChatInput" class="form-control form-control-sm border-0 bg-light rounded-pill px-3 py-2 text-dark" 
                       placeholder="Hỏi về sự cố IT, mật khẩu, mạng, máy in, hệ thống..." autocomplete="off">
                <button type="submit" id="kbChatSendBtn" class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px; height:36px;" title="Gửi câu hỏi">
                    <i class="bi bi-send-fill fs-6"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* KB Chatbot Widget Styling - Fixed Bottom Left */
.kb-chatbot-container {
    position: fixed;
    bottom: 24px;
    left: 24px;
    z-index: 1060;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* Floating Action Button */
.kb-chatbot-toggle-btn {
    background: linear-gradient(135deg, #0052CC 0%, #4C9AFF 50%, #6554C0 100%);
    color: #ffffff;
    border: none;
    border-radius: 999px;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
    box-shadow: 0 8px 24px rgba(0, 82, 204, 0.35);
}
.kb-chatbot-toggle-btn:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 28px rgba(0, 82, 204, 0.45);
    background: linear-gradient(135deg, #0747A6 0%, #0052CC 50%, #5243AA 100%);
}
.kb-chatbot-toggle-btn:active {
    transform: translateY(-1px) scale(0.98);
}
.kb-bot-avatar-ring {
    position: relative;
    width: 30px;
    height: 30px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.kb-online-indicator {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 9px;
    height: 9px;
    background: #36B37E;
    border: 1.5px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(54, 179, 126, 0.4);
}

/* Popup Chat Window */
.kb-chatbot-window {
    position: fixed;
    bottom: 84px;
    left: 24px;
    width: 400px;
    max-width: calc(100vw - 36px);
    height: 560px;
    max-height: calc(100vh - 110px);
    background: #ffffff;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 1060;
    border-color: #DFE1E6 !important;
    animation: kbSlideUp 0.25s cubic-bezier(0.2, 0, 0, 1);
}
.kb-chatbot-window.kb-chat-expanded {
    width: 600px;
    height: 700px;
}

@keyframes kbSlideUp {
    from {
        opacity: 0;
        transform: translateY(16px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Header */
.kb-chat-header {
    background: linear-gradient(135deg, #0747A6 0%, #0052CC 100%);
}
.kb-header-icon-box {
    width: 38px;
    height: 38px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.kb-header-status {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 4px;
}
.kb-status-dot {
    width: 7px;
    height: 7px;
    background: #36B37E;
    border-radius: 50%;
    display: inline-block;
}
.hover-white:hover {
    color: #ffffff !important;
}

/* Messages Body */
.kb-chat-body {
    flex: 1;
    overflow-y: auto;
    background: #F8F9FA;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.kb-msg-bubble-ai {
    display: flex;
    flex-direction: column;
    width: 100%;
}
.kb-msg-avatar {
    width: 28px;
    height: 28px;
    background: #DEEBFF;
    color: #0052CC;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}
.kb-msg-content {
    background: #ffffff;
    padding: 12px 14px;
    border-radius: 4px 14px 14px 14px;
    box-shadow: 0 1px 3px rgba(9, 30, 66, 0.08);
    border: 1px solid #EBECF0;
    font-size: 0.85rem;
    line-height: 1.55;
    color: #172B4D;
    max-width: 92%;
}
.kb-msg-content h6 {
    font-size: 0.9rem;
}
.kb-msg-content p:last-child {
    margin-bottom: 0;
}
.kb-msg-content a {
    color: #0052CC;
    text-decoration: underline;
}

/* User Message */
.kb-msg-user {
    display: flex;
    justify-content: flex-end;
    width: 100%;
}
.kb-msg-user-content {
    background: #0052CC;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 14px 14px 4px 14px;
    font-size: 0.85rem;
    line-height: 1.5;
    max-width: 85%;
    box-shadow: 0 2px 6px rgba(0, 82, 204, 0.25);
    word-break: break-word;
}

/* Quick prompt pills */
.kb-quick-prompts .kb-prompt-btn {
    font-size: 0.72rem;
    border-radius: 999px;
    background: #FAFBFC;
    transition: all 0.15s;
    text-align: left;
}
.kb-quick-prompts .kb-prompt-btn:hover {
    background: #DEEBFF;
    border-color: #B3D4FF;
    color: #0052CC;
}

/* Action button inside AI response */
.kb-action-chip {
    font-size: 0.75rem;
    border-radius: 6px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Responsive adjustment */
@media (min-width: 992px) {
    /* Position with clear clearance next to sidebar */
    body:has(.app-sidebar) .kb-chatbot-container,
    body:has(.jira-project-sidebar) .kb-chatbot-container {
        left: 240px;
    }
    body:has(.app-sidebar) .kb-chatbot-window,
    body:has(.jira-project-sidebar) .kb-chatbot-window {
        left: 240px;
    }
}
</style>

<script>
(function() {
    const toggleBtn = document.getElementById('kbChatbotToggleBtn');
    const chatWindow = document.getElementById('kbChatbotWindow');
    const closeBtn = document.getElementById('kbChatCloseBtn');
    const clearBtn = document.getElementById('kbChatClearBtn');
    const expandBtn = document.getElementById('kbChatExpandBtn');
    const expandIcon = document.getElementById('kbChatExpandIcon');
    const chatForm = document.getElementById('kbChatForm');
    const chatInput = document.getElementById('kbChatInput');
    const sendBtn = document.getElementById('kbChatSendBtn');
    const messagesContainer = document.getElementById('kbChatMessages');
    const typingIndicator = document.getElementById('kbChatTyping');

    let isOpen = false;
    let isExpanded = false;

    // Toggle chat window
    function toggleChat() {
        isOpen = !isOpen;
        if (isOpen) {
            chatWindow.style.display = 'flex';
            setTimeout(() => { chatInput.focus(); }, 100);
            scrollToBottom();
        } else {
            chatWindow.style.display = 'none';
        }
    }

    toggleBtn?.addEventListener('click', toggleChat);
    closeBtn?.addEventListener('click', () => {
        isOpen = false;
        chatWindow.style.display = 'none';
    });

    // Expand / Restore size
    expandBtn?.addEventListener('click', () => {
        isExpanded = !isExpanded;
        if (isExpanded) {
            chatWindow.classList.add('kb-chat-expanded');
            expandIcon.className = 'bi bi-arrows-angle-contract fs-6';
        } else {
            chatWindow.classList.remove('kb-chat-expanded');
            expandIcon.className = 'bi bi-arrows-angle-expand fs-6';
        }
    });

    // Clear chat
    clearBtn?.addEventListener('click', () => {
        if (!confirm('Bạn có muốn làm mới lại cuộc trò chuyện này không?')) return;
        // Keep initial welcome message
        const welcome = messagesContainer.querySelector('.kb-msg-bubble-ai');
        messagesContainer.innerHTML = '';
        if (welcome) messagesContainer.appendChild(welcome);
    });

    // Quick prompt buttons
    document.addEventListener('click', function(e) {
        const promptBtn = e.target.closest('.kb-prompt-btn');
        if (promptBtn) {
            const text = promptBtn.getAttribute('data-prompt');
            if (text) {
                sendMessage(text);
            }
        }
    });

    // Submit form
    chatForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;
        sendMessage(text);
    });

    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMarkdown(text) {
        if (!text) return '';
        let html = escapeHtml(text);
        // Headings
        html = html.replace(/^### (.*$)/gim, '<h6 class="fw-bold text-dark mt-2 mb-1">$1</h6>');
        html = html.replace(/^## (.*$)/gim, '<h6 class="fw-bold text-primary mt-2 mb-1">$1</h6>');
        // Bold & Italic
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Inline code
        html = html.replace(/`([^`]+)`/g, '<code class="bg-light text-danger px-1 py-0.5 rounded font-monospace" style="font-size:0.8rem;">$1</code>');
        // Code block
        html = html.replace(/```([a-z]*)\n([\s\S]*?)```/g, '<pre class="bg-dark text-light p-2 rounded font-monospace my-1" style="font-size:0.78rem;"><code>$2</code></pre>');
        // Links [text](url)
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-primary fw-semibold text-decoration-underline">$1</a>');
        // Bullet points
        html = html.replace(/^\s*-\s+(.*$)/gim, '<li class="ms-2">$1</li>');
        // Line breaks
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    function appendUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'kb-msg-user mb-2';
        div.innerHTML = `<div class="kb-msg-user-content">${escapeHtml(text)}</div>`;
        messagesContainer.appendChild(div);
        scrollToBottom();
    }

    function appendAiMessage(replyHtml, suggestedActions) {
        const div = document.createElement('div');
        div.className = 'kb-msg-bubble-ai mb-3';

        let actionButtonsHtml = '';
        if (suggestedActions && suggestedActions.length > 0) {
            actionButtonsHtml = `
                <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-1.5">
                    ${suggestedActions.map(act => `
                        <a href="${act.url}" class="btn btn-sm btn-outline-primary py-0.5 px-2 kb-action-chip">
                            <i class="bi bi-box-arrow-up-right"></i> ${escapeHtml(act.label)}
                        </a>
                    `).join('')}
                </div>
            `;
        }

        div.innerHTML = `
            <div class="d-flex gap-2">
                <div class="kb-msg-avatar flex-shrink-0">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="kb-msg-content flex-grow-1">
                    <div>${replyHtml}</div>
                    ${actionButtonsHtml}
                </div>
            </div>
        `;
        messagesContainer.appendChild(div);
        scrollToBottom();
    }

    function sendMessage(text) {
        appendUserMessage(text);
        chatInput.value = '';
        chatInput.disabled = true;
        sendBtn.disabled = true;
        if (typingIndicator) typingIndicator.classList.remove('d-none');
        scrollToBottom();

        const baseUrl = window.APP_BASE_URL || '';

        fetch(baseUrl + '/api/ai/kb-chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ message: text })
        })
        .then(res => res.json())
        .then(data => {
            if (typingIndicator) typingIndicator.classList.add('d-none');
            chatInput.disabled = false;
            sendBtn.disabled = false;
            chatInput.focus();

            if (data.success) {
                const formattedHtml = formatMarkdown(data.reply);
                appendAiMessage(formattedHtml, data.suggested_actions);
            } else {
                appendAiMessage('<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>' + escapeHtml(data.message || 'Lỗi xử lý phản hồi từ AI') + '</span>');
            }
        })
        .catch(err => {
            if (typingIndicator) typingIndicator.classList.add('d-none');
            chatInput.disabled = false;
            sendBtn.disabled = false;
            chatInput.focus();
            appendAiMessage('<span class="text-danger"><i class="bi bi-wifi-off me-1"></i>Không thể kết nối đến máy chủ AI. Vui lòng thử lại.</span>');
            console.error('KB Chat error:', err);
        });
    }
})();
</script>
