<?php
    $serverName = htmlspecialchars($server['name'] ?? 'Server');
    $serverIp = htmlspecialchars($server['ip_address'] ?? '127.0.0.1');
    $serverPort = htmlspecialchars($server['port'] ?? 22);
    $serverUser = htmlspecialchars($server['default_username'] ?: 'root');
    $hostSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', explode(' ', $server['name'])[0]));
    $dbSchemaJson = json_encode($dbSchema ?? []);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Terminal Console - <?= $serverName ?> (<?= $serverIp ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --term-bg: #0d1117;
            --term-text: #c9d1d9;
            --term-green: #3fb950;
            --term-blue: #58a6ff;
            --term-yellow: #d29922;
            --term-red: #f85149;
            --term-cyan: #39c5bb;
        }
        * { box-sizing: border-box; }
        body, html {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            background-color: var(--term-bg);
            color: var(--term-text);
            font-family: "Consolas", "Courier New", monospace;
            overflow: hidden;
        }
        .terminal-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
        }
        .terminal-header {
            background-color: #161b22;
            border-bottom: 1px solid #30363d;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            z-index: 10;
        }
        .quick-bar {
            background-color: #1b2028;
            border-bottom: 1px solid #30363d;
            padding: 6px 16px;
            display: flex;
            align-items: center;
            gap: 6px;
            overflow-x: auto;
            white-space: nowrap;
        }
        .quick-btn {
            background: #21262d;
            border: 1px solid #363b42;
            color: #8b949e;
            font-size: 11px;
            font-family: "Consolas", monospace;
            padding: 3px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .quick-btn:hover {
            background: #30363d;
            color: #58a6ff;
            border-color: #58a6ff;
        }
        .quick-btn.sql-btn {
            border-color: rgba(210, 153, 34, 0.4);
            color: #e3b341;
        }
        .quick-btn.sql-btn:hover {
            background: rgba(210, 153, 34, 0.2);
            color: #ffd166;
            border-color: #ffd166;
        }
        .terminal-body {
            flex: 1;
            padding: 16px 20px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
            line-height: 1.45;
            font-size: 14px;
            background-color: #0d1117;
            position: relative;
        }
        .terminal-line {
            margin-bottom: 4px;
        }
        .cmd-prompt {
            color: var(--term-green);
            font-weight: bold;
        }
        .cmd-prompt.mysql-prompt {
            color: var(--term-yellow);
        }
        .cmd-input-wrapper {
            position: relative;
            margin-top: 6px;
        }
        .cmd-input-container {
            display: flex;
            align-items: center;
            position: relative;
        }
        .cmd-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #f0f6fc;
            font-family: inherit;
            font-size: 14px;
            padding: 0;
            margin: 0;
            caret-color: #58a6ff;
            position: relative;
            z-index: 2;
        }
        .ghost-text {
            position: absolute;
            left: 0;
            top: 0;
            color: #6e7681;
            font-family: inherit;
            font-size: 14px;
            pointer-events: none;
            z-index: 1;
        }
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--term-green);
            box-shadow: 0 0 8px var(--term-green);
            animation: pulse 1.8s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.7; }
        }
        /* INTELLISENSE AUTOCOMPLETE FLOATING BOX */
        .intellisense-box {
            position: absolute;
            bottom: 28px;
            left: 0;
            background: #1c2128;
            border: 1px solid #388bfd;
            border-radius: 6px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
            max-height: 220px;
            width: 360px;
            overflow-y: auto;
            z-index: 1000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
        }
        .intelli-item {
            padding: 6px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.1s;
        }
        .intelli-item:last-child {
            border-bottom: none;
        }
        .intelli-item:hover, .intelli-item.active {
            background: #388bfd;
            color: #ffffff !important;
        }
        .intelli-item.active .text-secondary {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .intelli-tag {
            font-size: 9px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .intelli-tag.sql {
            background: #1f6feb;
            color: #fff;
        }
        .intelli-tag.table {
            background: #238636;
            color: #fff;
        }
        .intelli-tag.col {
            background: #d29922;
            color: #fff;
        }
        .intelli-tag.cmd {
            background: #8957e5;
            color: #fff;
        }

        /* MODAL KICK OVERLAY */
        .kick-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(13, 17, 23, 0.95);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .kick-card {
            background: #161b22;
            border: 2px solid #f85149;
            border-radius: 12px;
            max-width: 540px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(248, 81, 73, 0.4);
            text-align: center;
            padding: 32px;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }
        /* TOAST NOTIFICATION */
        .copy-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #238636;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-family: sans-serif;
            display: none;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>

<div class="terminal-container">
    <!-- TERMINAL HEADER -->
    <div class="terminal-header">
        <div class="d-flex align-items-center gap-3">
            <span class="pulse-dot" id="statusDot"></span>
            <span class="fw-bold text-white fs-6"><?= $serverName ?></span>
            <span class="text-secondary">|</span>
            <span class="text-muted"><i class="bi bi-hdd-network me-1"></i><?= $serverIp ?>:<?= $serverPort ?></span>
            <span class="text-secondary">|</span>
            <span class="text-info"><i class="bi bi-person-circle me-1"></i><?= $serverUser ?></span>
            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50">
                <i class="bi bi-lock-fill me-1"></i> Single Exclusive Lock
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small me-2"><i class="bi bi-stopwatch me-1"></i><span id="sessionTimer">00:00:00</span></span>
            <button class="btn btn-sm btn-outline-secondary text-white py-1 px-2 border-secondary" onclick="toggleFullscreen()" title="Toàn màn hình">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>
            <button class="btn btn-sm btn-outline-info py-1 px-2" onclick="openNewTab()" title="Mở trong tab riêng">
                <i class="bi bi-box-arrow-up-right me-1"></i> Tab mới
            </button>
            <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="disconnectSession()" title="Ngắt kết nối">
                <i class="bi bi-power me-1"></i> Ngắt kết nối
            </button>
            <button class="btn btn-sm btn-outline-light py-1 px-2" onclick="closeConsoleWindow()" title="Đóng cửa sổ">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- QUICK COMMAND TOOLBAR -->
    <div class="quick-bar">
        <span class="text-secondary small py-1 me-1"><i class="bi bi-lightning-charge"></i> Quick:</span>
        <button class="quick-btn" onclick="sendQuickCommand('top')">top</button>
        <button class="quick-btn" onclick="sendQuickCommand('df -h')">df -h</button>
        <button class="quick-btn" onclick="sendQuickCommand('free -m')">free -m</button>
        <button class="quick-btn" onclick="sendQuickCommand('docker ps')">docker ps</button>
        <button class="quick-btn" onclick="sendQuickCommand('systemctl status nginx')">systemctl</button>
        <button class="quick-btn" onclick="sendQuickCommand('php -v')">php -v</button>
        <button class="quick-btn" onclick="sendQuickCommand('git status')">git status</button>
        <button class="quick-btn" onclick="sendQuickCommand('cat .env')">cat .env</button>
        <span class="text-secondary small ms-2 me-1">| <i class="bi bi-database text-warning"></i> SQL Live:</span>
        <button class="quick-btn sql-btn" onclick="sendQuickCommand('mysql -u root -p')">mysql CLI</button>
        <button class="quick-btn sql-btn" onclick="sendQuickCommand('SHOW TABLES;')">SHOW TABLES</button>
        <button class="quick-btn sql-btn" onclick="sendQuickCommand('SELECT id, username, full_name, email FROM users LIMIT 5;')">SELECT users</button>
        <button class="quick-btn sql-btn" onclick="sendQuickCommand('SELECT id, ticket_code, title, status, priority_id FROM tickets LIMIT 5;')">SELECT tickets</button>
        <button class="quick-btn sql-btn" onclick="sendQuickCommand('SELECT count(*) as total_tickets FROM tickets;')">COUNT tickets</button>
        <button class="quick-btn" onclick="sendQuickCommand('clear')">clear</button>
        <button class="quick-btn" onclick="sendQuickCommand('help')">help</button>
    </div>

    <!-- TERMINAL SCREEN -->
    <div class="terminal-body" id="termScreen" onclick="focusInput()">
        <div class="terminal-line text-secondary">
Welcome to <?= $serverName ?> (GNU/Linux x86_64)
 * Management:     ITSM JumpServer Bastion PAM Gateway v2.0
 * Security:       Single Exclusive Session Locking (Strict Mode)
 * SQL Assistant:  IntelliSense Active (Syntax & Tables/Columns autocomplete with Tab)
 * Live DB Hook:   Active (Queries will query local live database)

<span class="text-warning">NOTICE:</span> All keystrokes and command logs are audited by Security Admin.
Type <span class="text-info">'help'</span> to see built-in commands or <span class="text-danger">'exit'</span> to terminate session.
Type <span class="text-warning">'mysql'</span> or SQL directly (e.g. <span class="text-warning">'SELECT * FROM users;'</span>) with Tab autocomplete.
--------------------------------------------------------------------------------
        </div>

        <div id="outputHistory"></div>

        <!-- ACTIVE INPUT LINE WITH INTELLISENSE AUTOCOMPLETE -->
        <div class="cmd-input-wrapper">
            <!-- FLOATING AUTOCOMPLETE DROPDOWN -->
            <div class="intellisense-box" id="intelliBox" style="display: none;"></div>

            <div class="cmd-input-container" id="activeInputContainer">
                <span class="cmd-prompt me-2" id="promptText"><?= $serverUser ?>@<?= $hostSlug ?>:~$</span>
                <div class="position-relative flex-grow-1 d-flex align-items-center">
                    <input type="text" id="cmdInput" class="cmd-input" autocomplete="off" spellcheck="false" autofocus>
                    <span id="ghostSuggestion" class="ghost-text"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- COPY TOAST -->
<div class="copy-toast" id="copyToast">
    <i class="bi bi-check-circle-fill me-1"></i> <span id="copyToastText">Đã sao chép!</span>
</div>

<!-- PREEMPTED / KICKED MODAL OVERLAY -->
<div class="kick-overlay" id="kickOverlay">
    <div class="kick-card">
        <div class="mb-3">
            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3.5rem;"></i>
        </div>
        <h4 class="fw-bold text-danger mb-2">BẠN ĐÃ BỊ NGẮT KẾT NỐI!</h4>
        <div class="badge bg-danger text-white mb-3 px-3 py-1 text-uppercase">Exclusive Preemption Alert</div>
        
        <div class="p-3 bg-dark rounded border border-danger border-opacity-50 text-start small mb-3">
            <div class="mb-2 text-white">
                <i class="bi bi-person-x-fill text-danger me-1"></i> 
                <strong>Lý do:</strong> Phiên kết nối của bạn đã bị ngắt do kỹ sư:
            </div>
            <div class="p-2 bg-danger bg-opacity-25 rounded text-center fw-bold text-white fs-5 mb-2" id="kickerNameDisplay">
                Quản trị viên
            </div>
            <div class="text-secondary small">
                <i class="bi bi-clock-history me-1"></i> Thời điểm: <span id="kickedTimeDisplay"><?= date('H:i:s d/m/Y') ?></span>
            </div>
            <div class="text-secondary small mt-1">
                <i class="bi bi-shield-lock me-1"></i> Máy chủ áp dụng chính sách <strong>Single Exclusive Session</strong>, chỉ 1 người được quyền thao tác tại một thời điểm.
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <button onclick="closeConsoleWindow()" class="btn btn-danger fw-bold px-3 py-2">
                <i class="bi bi-x-circle me-1"></i> Đóng Cửa Sổ
            </button>
            <a href="/jump/terminal/<?= $server['id'] ?>" class="btn btn-outline-light px-3 py-2">
                <i class="bi bi-arrow-clockwise me-1"></i> Chiếm Lại Quyền
            </a>
            <a href="/jump" class="btn btn-outline-secondary text-white px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Về Cổng Jump
            </a>
        </div>
    </div>
</div>

<script>
const SESSION_TOKEN = "<?= $sessionToken ?>";
const SERVER_ID = "<?= $server['id'] ?>";
const DB_SCHEMA = <?= $dbSchemaJson ?>;
const DB_TABLES = Object.keys(DB_SCHEMA);

// Comprehensive SQL Keywords
const SQL_KEYWORDS = [
    'SELECT', 'FROM', 'WHERE', 'ORDER BY', 'GROUP BY', 'HAVING', 'LIMIT',
    'JOIN', 'LEFT JOIN', 'INNER JOIN', 'RIGHT JOIN', 'ON', 'AS', 'DISTINCT',
    'AND', 'OR', 'NOT', 'LIKE', 'IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL',
    'COUNT(*)', 'SUM()', 'AVG()', 'MIN()', 'MAX()', 'NOW()', 'DATABASE()', 'VERSION()',
    'SHOW TABLES;', 'SHOW DATABASES;', 'DESCRIBE', 'DESC', 'EXPLAIN', 'USE', 'STATUS;', 'HELP;', 'EXIT;'
];

let isTerminated = false;
let currentMode = 'bash'; // 'bash' or 'mysql'
let commandHistory = [];
let historyIndex = -1;
let startTime = Date.now();

// Autocomplete State
let activeSuggestions = [];
let selectedSuggestionIndex = -1;

// Session Timer
setInterval(() => {
    if (isTerminated) return;
    const elapsedSec = Math.floor((Date.now() - startTime) / 1000);
    const hrs = String(Math.floor(elapsedSec / 3600)).padStart(2, '0');
    const mins = String(Math.floor((elapsedSec % 3600) / 60)).padStart(2, '0');
    const secs = String(elapsedSec % 60).padStart(2, '0');
    document.getElementById('sessionTimer').innerText = `${hrs}:${mins}:${secs}`;
}, 1000);

const cmdInput = document.getElementById('cmdInput');
const outputHistory = document.getElementById('outputHistory');
const promptText = document.getElementById('promptText');
const termScreen = document.getElementById('termScreen');
const intelliBox = document.getElementById('intelliBox');
const ghostSuggestion = document.getElementById('ghostSuggestion');

function focusInput() {
    if (!isTerminated && cmdInput) {
        cmdInput.focus();
    }
}

// ==========================================
// INTELLISENSE AUTOCOMPLETE ENGINE
// ==========================================
cmdInput.addEventListener('input', function() {
    updateAutocomplete();
});

cmdInput.addEventListener('keydown', function(e) {
    if (isTerminated) return;

    const isBoxVisible = (intelliBox.style.display !== 'none' && activeSuggestions.length > 0);

    // 1. Navigation inside suggestions
    if (isBoxVisible && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
        e.preventDefault();
        if (e.key === 'ArrowDown') {
            selectedSuggestionIndex = (selectedSuggestionIndex + 1) % activeSuggestions.length;
        } else {
            selectedSuggestionIndex = (selectedSuggestionIndex - 1 + activeSuggestions.length) % activeSuggestions.length;
        }
        renderSuggestions();
        return;
    }

    // 2. Accept suggestion with Tab or Enter
    if (e.key === 'Tab') {
        e.preventDefault();
        if (isBoxVisible && selectedSuggestionIndex >= 0) {
            applySuggestion(activeSuggestions[selectedSuggestionIndex]);
        } else if (isBoxVisible && activeSuggestions.length > 0) {
            applySuggestion(activeSuggestions[0]);
        }
        return;
    }

    if (e.key === 'Enter') {
        if (isBoxVisible && selectedSuggestionIndex >= 0) {
            e.preventDefault();
            applySuggestion(activeSuggestions[selectedSuggestionIndex]);
            return;
        }

        hideAutocomplete();
        const cmd = cmdInput.value;
        cmdInput.value = '';
        ghostSuggestion.innerText = '';
        if (cmd.trim()) {
            commandHistory.push(cmd);
            historyIndex = commandHistory.length;
        }
        executeCommand(cmd);
        return;
    }

    if (e.key === 'Escape') {
        hideAutocomplete();
        return;
    }

    // 3. Command history navigation (when no autocomplete box)
    if (!isBoxVisible) {
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (historyIndex > 0) {
                historyIndex--;
                cmdInput.value = commandHistory[historyIndex];
                updateAutocomplete();
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (historyIndex < commandHistory.length - 1) {
                historyIndex++;
                cmdInput.value = commandHistory[historyIndex];
                updateAutocomplete();
            } else {
                historyIndex = commandHistory.length;
                cmdInput.value = '';
                hideAutocomplete();
            }
        }
    }
});

function updateAutocomplete() {
    const val = cmdInput.value;
    const cursorPos = cmdInput.selectionStart || val.length;
    const textBeforeCursor = val.slice(0, cursorPos);

    if (!textBeforeCursor.trim()) {
        hideAutocomplete();
        return;
    }

    // Extract current word
    const match = textBeforeCursor.match(/([a-zA-Z0-9_\*]+)$/);
    if (!match) {
        hideAutocomplete();
        return;
    }

    const currentToken = match[1];
    if (currentToken.length < 1) {
        hideAutocomplete();
        return;
    }

    const tokenLower = currentToken.toLowerCase();
    const suggestions = [];

    // 1. Search SQL Keywords (always match starting letters)
    SQL_KEYWORDS.forEach(kw => {
        if (kw.toLowerCase().startsWith(tokenLower) && kw.toLowerCase() !== tokenLower) {
            suggestions.push({ text: kw, type: 'sql', desc: 'Cú pháp SQL' });
        }
    });

    // 2. Search Tables
    DB_TABLES.forEach(tbl => {
        if (tbl.toLowerCase().startsWith(tokenLower)) {
            suggestions.push({ text: tbl, type: 'table', desc: 'Bảng CSDL' });
        }
    });

    // 3. Search Columns in DB_SCHEMA
    for (const [tbl, cols] of Object.entries(DB_SCHEMA)) {
        if (Array.isArray(cols)) {
            cols.forEach(col => {
                if (col.toLowerCase().startsWith(tokenLower) && !suggestions.some(s => s.text === col)) {
                    suggestions.push({ text: col, type: 'col', desc: `Cột [${tbl}]` });
                }
            });
        }
    }

    // 4. Linux System Commands (if in bash mode)
    if (currentMode === 'bash') {
        const SYS_CMDS = ['docker ps', 'systemctl status nginx', 'systemctl status mysql', 'netstat -tuln', 'ip a', 'ping 8.8.8.8', 'cat .env', 'top', 'ps aux', 'df -h', 'free -m', 'uname -a', 'whoami', 'id', 'uptime', 'date', 'php -v', 'php -m', 'git status', 'git log', 'ls -la', 'pwd', 'help', 'clear', 'exit'];
        SYS_CMDS.forEach(cmd => {
            if (cmd.toLowerCase().startsWith(tokenLower) && !suggestions.some(s => s.text === cmd)) {
                suggestions.push({ text: cmd, type: 'cmd', desc: 'Lệnh Linux OS' });
            }
        });
    }

    activeSuggestions = suggestions.slice(0, 8);
    if (activeSuggestions.length === 0) {
        hideAutocomplete();
        return;
    }

    selectedSuggestionIndex = 0;
    renderSuggestions();

    // Ghost text completion
    const topSuggestion = activeSuggestions[0].text;
    if (topSuggestion.toLowerCase().startsWith(tokenLower)) {
        const remaining = topSuggestion.slice(currentToken.length);
        const padding = ' '.repeat(cmdInput.value.length);
        ghostSuggestion.innerText = padding + remaining;
    } else {
        ghostSuggestion.innerText = '';
    }
}

function renderSuggestions() {
    if (activeSuggestions.length === 0) {
        hideAutocomplete();
        return;
    }

    intelliBox.innerHTML = '';
    activeSuggestions.forEach((sug, idx) => {
        const item = document.createElement('div');
        item.className = 'intelli-item' + (idx === selectedSuggestionIndex ? ' active' : '');
        item.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span class="intelli-tag ${sug.type}">${sug.type}</span>
                <span class="fw-bold text-light">${escapeHtml(sug.text)}</span>
            </div>
            <span class="text-secondary small">${escapeHtml(sug.desc)}</span>
        `;
        item.addEventListener('click', () => {
            applySuggestion(sug);
        });
        intelliBox.appendChild(item);
    });

    intelliBox.style.display = 'block';
}

function applySuggestion(sug) {
    const val = cmdInput.value;
    const cursorPos = cmdInput.selectionStart || val.length;
    const textBefore = val.slice(0, cursorPos);
    const textAfter = val.slice(cursorPos);

    const replaced = textBefore.replace(/([a-zA-Z0-9_\*]+)$/, sug.text + ' ');
    cmdInput.value = replaced + textAfter;
    const newCursor = replaced.length;
    cmdInput.setSelectionRange(newCursor, newCursor);

    hideAutocomplete();
    focusInput();
}

function hideAutocomplete() {
    intelliBox.style.display = 'none';
    activeSuggestions = [];
    selectedSuggestionIndex = -1;
    ghostSuggestion.innerText = '';
}

function sendQuickCommand(cmd) {
    if (isTerminated) return;
    if (cmd === 'clear') {
        outputHistory.innerHTML = '';
        focusInput();
        return;
    }
    commandHistory.push(cmd);
    historyIndex = commandHistory.length;
    executeCommand(cmd);
}

function appendToTerminal(currentPrompt, cmd, output, promptMode) {
    const entry = document.createElement('div');
    entry.className = 'terminal-line';

    if (cmd !== null) {
        const cmdLine = document.createElement('div');
        const isSqlPrompt = (promptMode === 'mysql' || currentPrompt.includes('mysql>'));
        const promptClass = isSqlPrompt ? 'cmd-prompt mysql-prompt' : 'cmd-prompt';
        cmdLine.innerHTML = `<span class="${promptClass}">${escapeHtml(currentPrompt)}</span> ${escapeHtml(cmd)}`;
        entry.appendChild(cmdLine);
    }

    if (output) {
        const outLine = document.createElement('div');
        outLine.innerHTML = output;
        entry.appendChild(outLine);
    }

    outputHistory.appendChild(entry);
    termScreen.scrollTop = termScreen.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

function executeCommand(cmd) {
    const curPrompt = promptText.innerText;
    const curMode = currentMode;

    if (cmd.trim().toLowerCase() === 'clear' || (curMode === 'mysql' && cmd.trim() === '\\c')) {
        outputHistory.innerHTML = '';
        return;
    }

    const formData = new FormData();
    formData.append('token', SESSION_TOKEN);
    formData.append('command', cmd);
    formData.append('mode', curMode);

    fetch('/jump/api/command', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.error === 'SESSION_NOT_ACTIVE') {
            handleKicked(data.kicker_name);
            return;
        }

        if (data.exit) {
            appendToTerminal(curPrompt, cmd, 'Connection closed by remote host. Session terminated.', curMode);
            setTimeout(() => {
                closeConsoleWindow();
            }, 1000);
            return;
        }

        if (data.output === '__CLEAR__') {
            outputHistory.innerHTML = '';
        } else {
            appendToTerminal(curPrompt, cmd, data.output, curMode);
        }

        if (data.mode) {
            currentMode = data.mode;
        }

        if (data.prompt) {
            promptText.innerText = data.prompt;
            if (currentMode === 'mysql') {
                promptText.className = 'cmd-prompt mysql-prompt me-2';
            } else {
                promptText.className = 'cmd-prompt me-2';
            }
        }

        termScreen.scrollTop = termScreen.scrollHeight;
        focusInput();
    })
    .catch(err => {
        appendToTerminal(curPrompt, cmd, `Lỗi mạng hoặc kết nối máy chủ: ${err.message}`, curMode);
    });
}

// ==========================================
// INTERACTIVE DATAGRID HELPER FUNCTIONS
// ==========================================
function filterGridRows(gridId, query) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    const rows = grid.querySelectorAll('.grid-row');
    const q = query.toLowerCase().trim();

    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        if (!q || text.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}

function copyGridData(gridId, format) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    const jsonScript = grid.querySelector('.grid-data-json');
    if (!jsonScript) return;

    try {
        const rows = JSON.parse(jsonScript.textContent);
        let textToCopy = '';

        if (format === 'json') {
            textToCopy = JSON.stringify(rows, null, 2);
        } else if (format === 'csv') {
            if (rows.length > 0) {
                const headers = Object.keys(rows[0]);
                const csvLines = [headers.join(',')];
                rows.forEach(r => {
                    const rowVals = headers.map(h => {
                        const val = r[h] === null ? '' : String(r[h]).replace(/"/g, '""');
                        return `"${val}"`;
                    });
                    csvLines.push(rowVals.join(','));
                });
                textToCopy = csvLines.join('\n');
            }
        }

        navigator.clipboard.writeText(textToCopy).then(() => {
            showToast(`Đã sao chép ${rows.length} dòng dạng ${format.toUpperCase()}!`);
        });
    } catch (e) {
        showToast('Lỗi khi sao chép dữ liệu.');
    }
}

function toggleGridView(gridId) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    const tableWrap = grid.querySelector('.grid-table-wrap');
    const asciiView = grid.querySelector('.grid-ascii-view');

    if (tableWrap && asciiView) {
        if (asciiView.classList.contains('d-none')) {
            tableWrap.classList.add('d-none');
            asciiView.classList.remove('d-none');
        } else {
            asciiView.classList.add('d-none');
            tableWrap.classList.remove('d-none');
        }
    }
}

function showToast(msg) {
    const t = document.getElementById('copyToast');
    const txt = document.getElementById('copyToastText');
    if (t && txt) {
        txt.innerText = msg;
        t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 2000);
    }
}

// HEARTBEAT POLLING (Every 2.5 seconds)
const heartbeatInterval = setInterval(() => {
    if (isTerminated) return;

    const formData = new FormData();
    formData.append('token', SESSION_TOKEN);

    fetch('/jump/api/heartbeat', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'KICKED') {
            handleKicked(data.kicker_name, data.kicked_at);
        } else if (data.status === 'TERMINATED' || data.status === 'NOT_FOUND') {
            handleTerminated();
        }
    })
    .catch(() => {});
}, 2500);

function handleKicked(kickerName, kickedAt) {
    isTerminated = true;
    clearInterval(heartbeatInterval);
    hideAutocomplete();
    
    document.getElementById('statusDot').style.backgroundColor = '#f85149';
    document.getElementById('statusDot').style.boxShadow = '0 0 8px #f85149';
    document.getElementById('activeInputContainer').style.display = 'none';

    if (kickerName) {
        document.getElementById('kickerNameDisplay').innerText = kickerName;
    }
    if (kickedAt) {
        document.getElementById('kickedTimeDisplay').innerText = kickedAt;
    }

    document.getElementById('kickOverlay').style.display = 'flex';
}

function handleTerminated() {
    isTerminated = true;
    clearInterval(heartbeatInterval);
    alert('Phiên làm việc đã kết thúc.');
    closeConsoleWindow();
}

function disconnectSession() {
    if (!confirm('Bạn có chắc chắn muốn ngắt kết nối và giải phóng máy chủ?')) return;
    
    const formData = new FormData();
    formData.append('token', SESSION_TOKEN);
    fetch('/jump/api/disconnect', {
        method: 'POST',
        body: formData
    }).finally(() => {
        closeConsoleWindow();
    });
}

function closeConsoleWindow() {
    if (window.opener) {
        try { window.opener.location.reload(); } catch (e) {}
        window.close();
    } else {
        window.location.href = '/jump';
    }
}

function openNewTab() {
    window.open(window.location.href, '_blank');
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

window.addEventListener('beforeunload', function() {
    if (!isTerminated) {
        const data = new FormData();
        data.append('token', SESSION_TOKEN);
        navigator.sendBeacon('/jump/api/disconnect', data);
    }
});
</script>
</body>
</html>
