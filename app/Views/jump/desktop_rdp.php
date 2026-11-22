<?php
    $serverName = htmlspecialchars($server['name'] ?? 'Windows Server');
    $serverIp = htmlspecialchars($server['ip_address'] ?? '127.0.0.1');
    $serverPort = htmlspecialchars($server['port'] ?? 3389);
    $serverUser = htmlspecialchars($server['default_username'] ?: 'Administrator');
    $dbSchemaJson = json_encode($dbSchema ?? []);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remote Desktop - <?= $serverName ?> (<?= $serverIp ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --win-bg: #004e98;
            --taskbar-bg: #1c1c1c;
            --win-blue: #0078d4;
        }
        * { box-sizing: border-box; }
        body, html {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            user-select: none;
            background: #001e3d;
        }
        .rdp-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
            background: linear-gradient(135deg, #001e3d 0%, #004e98 100%);
            position: relative;
        }
        /* TOP RDP CLIENT BAR */
        .rdp-topbar {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            background: #202020;
            border-bottom: 2px solid #0078d4;
            border-radius: 0 0 8px 8px;
            padding: 4px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #fff;
            font-size: 12px;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
        .desktop-area {
            flex: 1;
            padding: 44px 24px 24px;
            position: relative;
        }
        /* DESKTOP ICONS */
        .desktop-icons {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 80px;
        }
        .desktop-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
            text-align: center;
            font-size: 11px;
            cursor: pointer;
            padding: 6px 4px;
            border-radius: 4px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
            transition: background 0.15s;
        }
        .desktop-icon:hover {
            background: rgba(255,255,255,0.15);
        }
        .desktop-icon i {
            font-size: 28px;
            margin-bottom: 4px;
        }

        /* WINDOWS POWERSHELL WINDOW */
        .win-window {
            position: absolute;
            top: 60px;
            left: 110px;
            width: 860px;
            height: 520px;
            background: #0c1021;
            border: 1px solid #2a3148;
            border-radius: 8px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.6);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 10;
        }
        .win-header {
            background: #1a1e2e;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #fff;
            font-size: 12px;
            border-bottom: 1px solid #2a3148;
        }
        .win-controls {
            display: flex;
            gap: 12px;
            color: #8b949e;
        }
        .win-controls i:hover {
            color: #fff;
            cursor: pointer;
        }
        .win-body {
            flex: 1;
            background: #012456; /* Classic PowerShell Blue */
            color: #eeedf0;
            font-family: "Consolas", monospace;
            font-size: 13px;
            padding: 12px;
            overflow-y: auto;
            white-space: pre-wrap;
            line-height: 1.4;
        }
        .ps-input-row {
            display: flex;
            align-items: center;
            margin-top: 4px;
        }
        .ps-prompt {
            color: #ffff00;
            margin-right: 6px;
            font-weight: bold;
        }
        .ps-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #ffffff;
            font-family: inherit;
            font-size: 13px;
        }

        /* INTELLISENSE AUTOCOMPLETE FOR POWERSHELL */
        .intellisense-box {
            position: absolute;
            bottom: 28px;
            left: 0;
            background: #161b22;
            border: 1px solid #0078d4;
            border-radius: 6px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            max-height: 220px;
            width: 380px;
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
            background: #0078d4;
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
        .intelli-tag.ps {
            background: #0078d4;
            color: #fff;
        }
        .ghost-text {
            position: absolute;
            top: 0;
            left: 0;
            color: rgba(255, 255, 255, 0.35);
            font-family: inherit;
            font-size: 13px;
            pointer-events: none;
            white-space: pre;
            z-index: 1;
        }

        /* TASKBAR */
        .win-taskbar {
            height: 44px;
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid #333;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            z-index: 50;
        }
        .start-btn {
            background: transparent;
            border: none;
            color: #0078d4;
            font-size: 20px;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .start-btn:hover {
            background: rgba(255,255,255,0.1);
        }
        .taskbar-apps {
            display: flex;
            gap: 4px;
        }
        .taskbar-item {
            padding: 6px 14px;
            background: rgba(255,255,255,0.08);
            border-bottom: 2px solid #0078d4;
            color: #fff;
            font-size: 12px;
            border-radius: 4px 4px 0 0;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .systray {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ddd;
            font-size: 12px;
        }

        /* PREEMPTED / KICK OVERLAY */
        .kick-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(10, 15, 25, 0.95);
            backdrop-filter: blur(10px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .kick-card {
            background: #1e1e1e;
            border: 2px solid #f85149;
            border-radius: 12px;
            max-width: 520px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(248, 81, 73, 0.4);
            text-align: center;
            padding: 32px;
        }
    </style>
</head>
<body>

<div class="rdp-container">
    <!-- TOP RDP CLIENT PIN BAR -->
    <div class="rdp-topbar">
        <i class="bi bi-windows text-info"></i>
        <span class="fw-bold"><?= $serverName ?> (<?= $serverIp ?>:<?= $serverPort ?>)</span>
        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50">
            <i class="bi bi-shield-check me-1"></i> RDP Secure Session
        </span>
        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50">
            Exclusive Lock
        </span>
        <button class="btn btn-sm btn-outline-info py-0 px-2 text-white border-0" onclick="window.open(window.location.href, '_blank')" title="Mở Tab Mới">
            <i class="bi bi-box-arrow-up-right"></i>
        </button>
        <button class="btn btn-sm btn-outline-secondary py-0 px-2 text-white border-0" onclick="toggleFullscreen()" title="Toàn Màn Hình">
            <i class="bi bi-arrows-fullscreen"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger py-0 px-2 text-white border-0" onclick="disconnectSession()" title="Ngắt Kết Nối">
            <i class="bi bi-power"></i>
        </button>
        <button class="btn btn-sm btn-outline-light py-0 px-2 text-white border-0" onclick="closeConsoleWindow()" title="Đóng Cửa Sổ">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- DESKTOP WORKSPACE -->
    <div class="desktop-area">
        <div class="desktop-icons">
            <div class="desktop-icon" onclick="focusPsInput()">
                <i class="bi bi-terminal-fill text-info"></i>
                <span>PowerShell</span>
            </div>
            <div class="desktop-icon" onclick="openQuerySample()">
                <i class="bi bi-database-fill text-warning"></i>
                <span>Live DB SQL</span>
            </div>
            <div class="desktop-icon">
                <i class="bi bi-server text-primary"></i>
                <span>Server Manager</span>
            </div>
            <div class="desktop-icon">
                <i class="bi bi-pc-display text-light"></i>
                <span>This PC</span>
            </div>
            <div class="desktop-icon">
                <i class="bi bi-folder-fill text-warning"></i>
                <span>C:\Logs</span>
            </div>
            <div class="desktop-icon">
                <i class="bi bi-trash text-light"></i>
                <span>Recycle Bin</span>
            </div>
        </div>

        <!-- SIMULATED POWERSHELL WINDOW -->
        <div class="win-window" id="psWindow">
            <div class="win-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-terminal text-primary"></i>
                    <span>Administrator: Windows PowerShell (Connected to Live DB)</span>
                </div>
                <div class="win-controls">
                    <i class="bi bi-dash"></i>
                    <i class="bi bi-square"></i>
                    <i class="bi bi-x-lg" onclick="document.getElementById('psWindow').style.display='none'"></i>
                </div>
            </div>

            <div class="win-body" id="psBody" onclick="focusPsInput()">
                <div>Windows PowerShell</div>
                <div>Copyright (C) Microsoft Corporation. All rights reserved.</div>
                <div class="text-info my-1">Try the new cross-platform PowerShell https://aka.ms/pscore6</div>
                <div class="text-warning">NOTICE: JumpServer Bastion PAM Active. Single Exclusive Session. Real DB queries enabled.</div>
                <div class="mb-2">Type: 'ipconfig', 'Get-Service', 'dir', 'systeminfo' or SQL like <span class="text-warning">'SELECT * FROM users;'</span></div>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 8px 0;">

                <div id="psHistory"></div>

                <div class="ps-input-wrapper position-relative" style="margin-top: 4px;">
                    <!-- FLOATING AUTOCOMPLETE DROPDOWN -->
                    <div class="intellisense-box" id="psIntelliBox" style="display: none;"></div>

                    <div class="ps-input-row" id="activePsRow">
                        <span class="ps-prompt" id="psPrompt">PS C:\Users\<?= $serverUser ?>></span>
                        <div class="position-relative flex-grow-1 d-flex align-items-center">
                            <input type="text" id="psInput" class="ps-input w-100" autocomplete="off" spellcheck="false" autofocus>
                            <span id="psGhostSuggestion" class="ghost-text"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WINDOWS TASKBAR -->
    <div class="win-taskbar">
        <div class="d-flex align-items-center gap-2">
            <button class="start-btn" title="Start">
                <i class="bi bi-windows"></i>
            </button>
            <div class="taskbar-apps">
                <div class="taskbar-item" onclick="document.getElementById('psWindow').style.display='flex'; focusPsInput();">
                    <i class="bi bi-terminal-fill text-info"></i>
                    <span>PowerShell</span>
                </div>
                <div class="taskbar-item" onclick="executePsCommand('SELECT * FROM users LIMIT 5;')">
                    <i class="bi bi-database text-warning"></i>
                    <span>Query Users</span>
                </div>
                <div class="taskbar-item" onclick="executePsCommand('SELECT id, ticket_code, title, status, priority_id FROM tickets LIMIT 5;')">
                    <i class="bi bi-ticket-detailed text-info"></i>
                    <span>Query Tickets</span>
                </div>
                <div class="taskbar-item" onclick="executePsCommand('Get-Service')">
                    <i class="bi bi-gear text-success"></i>
                    <span>Get-Service</span>
                </div>
                <div class="taskbar-item">
                    <i class="bi bi-server text-primary"></i>
                    <span>Server Manager</span>
                </div>
            </div>
        </div>

        <div class="systray">
            <span><i class="bi bi-wifi"></i> Internet</span>
            <span><i class="bi bi-volume-up"></i></span>
            <div class="text-end lh-1">
                <div><?= date('H:i') ?></div>
                <div class="small text-muted" style="font-size: 10px;"><?= date('d/m/Y') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- PREEMPTED / KICK OVERLAY -->
<div class="kick-overlay" id="kickOverlay">
    <div class="kick-card text-white">
        <div class="mb-3">
            <i class="bi bi-exclamation-octagon-fill text-danger" style="font-size: 3.5rem;"></i>
        </div>
        <h4 class="fw-bold text-danger mb-2">PHIÊN REMOTE DESKTOP ĐÃ BỊ NGẮT!</h4>
        <div class="badge bg-danger text-white mb-3 px-3 py-1 text-uppercase">Exclusive Preemption Alert</div>
        
        <div class="p-3 bg-dark rounded border border-danger border-opacity-50 text-start small mb-3">
            <div class="mb-2 text-white">
                <i class="bi bi-person-x-fill text-danger me-1"></i> 
                <strong>Lý do:</strong> Bạn đã bị ngắt kết nối do kỹ sư:
            </div>
            <div class="p-2 bg-danger bg-opacity-25 rounded text-center fw-bold text-white fs-5 mb-2" id="kickerNameDisplay">
                Quản trị viên
            </div>
            <div class="text-secondary small">
                <i class="bi bi-clock-history me-1"></i> Thời điểm: <span id="kickedTimeDisplay"><?= date('H:i:s d/m/Y') ?></span>
            </div>
            <div class="text-secondary small mt-1">
                <i class="bi bi-shield-lock me-1"></i> Mỗi máy chủ chỉ cho phép <strong>1 người kết nối độc quyền</strong>.
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-center">
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
const DB_SCHEMA = <?= $dbSchemaJson ?>;
const DB_TABLES = Object.keys(DB_SCHEMA);

// Comprehensive SQL Keywords
const SQL_KEYWORDS = [
    'SELECT', 'FROM', 'WHERE', 'ORDER BY', 'GROUP BY', 'HAVING', 'LIMIT',
    'JOIN', 'LEFT JOIN', 'INNER JOIN', 'RIGHT JOIN', 'ON', 'AS', 'DISTINCT',
    'AND', 'OR', 'NOT', 'LIKE', 'IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL',
    'COUNT(*)', 'SUM()', 'AVG()', 'MIN()', 'MAX()', 'NOW()', 'DATABASE()', 'VERSION()',
    'SHOW TABLES;', 'SHOW DATABASES;', 'DESCRIBE', 'DESC', 'EXPLAIN', 'USE', 'STATUS;', 'HELP;'
];

// Comprehensive PowerShell & Windows System Commands
const PS_COMMANDS = [
    'Get-Service', 'Get-Process', 'ipconfig', 'ipconfig /all', 'dir', 'tasklist',
    'systeminfo', 'hostname', 'ping 8.8.8.8', 'cls', 'clear', 'netstat', 'exit'
];

let isTerminated = false;
let currentMode = 'bash';
let psCommandHistory = [];
let psHistoryIndex = -1;
let psActiveSuggestions = [];
let psSelectedSuggestionIndex = -1;

const psInput = document.getElementById('psInput');
const psHistory = document.getElementById('psHistory');
const psBody = document.getElementById('psBody');
const psPrompt = document.getElementById('psPrompt');
const psIntelliBox = document.getElementById('psIntelliBox');
const psGhostSuggestion = document.getElementById('psGhostSuggestion');

function focusPsInput() {
    if (!isTerminated && psInput) psInput.focus();
}

function openQuerySample() {
    document.getElementById('psWindow').style.display = 'flex';
    executePsCommand('SELECT id, ticket_code, title, status, priority_id FROM tickets LIMIT 5;');
}

// ==========================================
// POWERSHELL & SQL INTELLISENSE AUTOCOMPLETE
// ==========================================
psInput.addEventListener('input', function() {
    updatePsAutocomplete();
});

psInput.addEventListener('keydown', function(e) {
    if (isTerminated) return;

    const isBoxVisible = (psIntelliBox && psIntelliBox.style.display !== 'none' && psActiveSuggestions.length > 0);

    // 1. Navigation inside suggestion list
    if (isBoxVisible && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
        e.preventDefault();
        if (e.key === 'ArrowDown') {
            psSelectedSuggestionIndex = (psSelectedSuggestionIndex + 1) % psActiveSuggestions.length;
        } else {
            psSelectedSuggestionIndex = (psSelectedSuggestionIndex - 1 + psActiveSuggestions.length) % psActiveSuggestions.length;
        }
        renderPsSuggestions();
        return;
    }

    // 2. Accept suggestion with Tab or Enter
    if (e.key === 'Tab') {
        e.preventDefault();
        if (isBoxVisible && psSelectedSuggestionIndex >= 0) {
            applyPsSuggestion(psActiveSuggestions[psSelectedSuggestionIndex]);
        } else if (isBoxVisible && psActiveSuggestions.length > 0) {
            applyPsSuggestion(psActiveSuggestions[0]);
        } else {
            updatePsAutocomplete();
            if (psActiveSuggestions.length > 0) {
                applyPsSuggestion(psActiveSuggestions[0]);
            }
        }
        return;
    }

    if (e.key === 'Enter') {
        if (isBoxVisible && psSelectedSuggestionIndex >= 0) {
            e.preventDefault();
            applyPsSuggestion(psActiveSuggestions[psSelectedSuggestionIndex]);
            return;
        }

        hidePsAutocomplete();
        const cmd = psInput.value;
        psInput.value = '';
        if (psGhostSuggestion) psGhostSuggestion.innerText = '';
        if (cmd.trim()) {
            psCommandHistory.push(cmd);
            psHistoryIndex = psCommandHistory.length;
        }
        executePsCommand(cmd);
        return;
    }

    if (e.key === 'Escape') {
        hidePsAutocomplete();
        return;
    }

    // 3. Command history navigation (when suggestion box is closed)
    if (!isBoxVisible) {
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (psHistoryIndex > 0) {
                psHistoryIndex--;
                psInput.value = psCommandHistory[psHistoryIndex];
                updatePsAutocomplete();
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (psHistoryIndex < psCommandHistory.length - 1) {
                psHistoryIndex++;
                psInput.value = psCommandHistory[psHistoryIndex];
                updatePsAutocomplete();
            } else {
                psHistoryIndex = psCommandHistory.length;
                psInput.value = '';
                hidePsAutocomplete();
            }
        }
    }
});

function updatePsAutocomplete() {
    const val = psInput.value;
    const cursorPos = psInput.selectionStart || val.length;
    const textBeforeCursor = val.slice(0, cursorPos);

    if (!textBeforeCursor.trim()) {
        hidePsAutocomplete();
        return;
    }

    const match = textBeforeCursor.match(/([a-zA-Z0-9_\*]+)$/);
    if (!match) {
        hidePsAutocomplete();
        return;
    }

    const currentToken = match[1];
    if (currentToken.length < 1) {
        hidePsAutocomplete();
        return;
    }

    const tokenLower = currentToken.toLowerCase();
    const suggestions = [];

    // 1. Search SQL Keywords (always match partial keyword)
    SQL_KEYWORDS.forEach(kw => {
        if (kw.toLowerCase().startsWith(tokenLower) && kw.toLowerCase() !== tokenLower) {
            suggestions.push({ text: kw, type: 'sql', desc: 'Cú pháp SQL' });
        }
    });

    // 2. Search DB Tables
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

    // 4. PowerShell & Windows System Commands
    PS_COMMANDS.forEach(cmd => {
        if (cmd.toLowerCase().startsWith(tokenLower) && !suggestions.some(s => s.text === cmd)) {
            suggestions.push({ text: cmd, type: 'ps', desc: 'Lệnh PowerShell' });
        }
    });

    psActiveSuggestions = suggestions.slice(0, 8);
    if (psActiveSuggestions.length === 0) {
        hidePsAutocomplete();
        return;
    }

    psSelectedSuggestionIndex = 0;
    renderPsSuggestions();

    // Ghost text completion
    const topSuggestion = psActiveSuggestions[0].text;
    if (topSuggestion.toLowerCase().startsWith(tokenLower)) {
        const remaining = topSuggestion.slice(currentToken.length);
        const padding = ' '.repeat(psInput.value.length);
        if (psGhostSuggestion) psGhostSuggestion.innerText = padding + remaining;
    } else {
        if (psGhostSuggestion) psGhostSuggestion.innerText = '';
    }
}

function renderPsSuggestions() {
    if (!psIntelliBox || psActiveSuggestions.length === 0) {
        hidePsAutocomplete();
        return;
    }

    psIntelliBox.innerHTML = '';
    psActiveSuggestions.forEach((sug, idx) => {
        const item = document.createElement('div');
        item.className = 'intelli-item' + (idx === psSelectedSuggestionIndex ? ' active' : '');
        item.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <span class="intelli-tag ${sug.type}">${sug.type}</span>
                <span class="fw-bold text-light">${escapeHtml(sug.text)}</span>
            </div>
            <span class="text-secondary small">${escapeHtml(sug.desc)}</span>
        `;
        item.addEventListener('click', () => {
            applyPsSuggestion(sug);
        });
        psIntelliBox.appendChild(item);
    });

    psIntelliBox.style.display = 'block';
}

function applyPsSuggestion(sug) {
    const val = psInput.value;
    const cursorPos = psInput.selectionStart || val.length;
    const textBefore = val.slice(0, cursorPos);
    const textAfter = val.slice(cursorPos);

    const replaced = textBefore.replace(/([a-zA-Z0-9_\*]+)$/, sug.text + ' ');
    psInput.value = replaced + textAfter;
    const newCursor = replaced.length;
    psInput.setSelectionRange(newCursor, newCursor);

    hidePsAutocomplete();
    focusPsInput();
}

function hidePsAutocomplete() {
    if (psIntelliBox) psIntelliBox.style.display = 'none';
    psActiveSuggestions = [];
    psSelectedSuggestionIndex = -1;
    if (psGhostSuggestion) psGhostSuggestion.innerText = '';
}

function executePsCommand(cmd) {
    const curPrompt = psPrompt.innerText;
    if (cmd.trim().toLowerCase() === 'clear' || cmd.trim().toLowerCase() === 'cls') {
        psHistory.innerHTML = '';
        return;
    }

    const formData = new FormData();
    formData.append('token', SESSION_TOKEN);
    formData.append('command', cmd);
    formData.append('mode', currentMode);

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
            closeConsoleWindow();
            return;
        }

        if (data.output === '__CLEAR__') {
            psHistory.innerHTML = '';
        } else {
            const div = document.createElement('div');
            div.style.marginBottom = '6px';
            div.innerHTML = `<div><span class="ps-prompt">${escapeHtml(curPrompt)}</span> ${escapeHtml(cmd)}</div><div>${data.output || ''}</div>`;
            psHistory.appendChild(div);
        }

        if (data.mode) currentMode = data.mode;
        if (data.prompt) psPrompt.innerText = data.prompt;

        psBody.scrollTop = psBody.scrollHeight;
        focusPsInput();
    })
    .catch(err => {
        const div = document.createElement('div');
        div.innerHTML = `<span class="text-danger">Error: ${escapeHtml(err.message)}</span>`;
        psHistory.appendChild(div);
    });
}

function filterGridRows(gridId, query) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    const rows = grid.querySelectorAll('.grid-row');
    const q = query.toLowerCase().trim();
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = (!q || text.includes(q)) ? '' : 'none';
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
                    const rowVals = headers.map(h => `"${r[h] === null ? '' : String(r[h]).replace(/"/g, '""')}"`);
                    csvLines.push(rowVals.join(','));
                });
                textToCopy = csvLines.join('\n');
            }
        }
        navigator.clipboard.writeText(textToCopy).then(() => {
            alert(`Đã sao chép ${rows.length} dòng dạng ${format.toUpperCase()}!`);
        });
    } catch (e) {}
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

// HEARTBEAT POLLING
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
    document.getElementById('activePsRow').style.display = 'none';
    if (kickerName) document.getElementById('kickerNameDisplay').innerText = kickerName;
    if (kickedAt) document.getElementById('kickedTimeDisplay').innerText = kickedAt;
    document.getElementById('kickOverlay').style.display = 'flex';
}

function handleTerminated() {
    isTerminated = true;
    clearInterval(heartbeatInterval);
    alert('Phiên Remote Desktop đã kết thúc.');
    closeConsoleWindow();
}

function disconnectSession() {
    if (!confirm('Bạn có chắc chắn muốn ngắt kết nối Remote Desktop?')) return;
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

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        if (document.exitFullscreen) document.exitFullscreen();
    }
}
</script>
</body>
</html>
