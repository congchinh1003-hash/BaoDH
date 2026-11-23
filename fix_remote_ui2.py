import pathlib

NL = chr(10)

def write(path, text):
    pathlib.Path(path).write_text(text, encoding='utf-8')

p = 'app/Views/layouts/main.php'
t = pathlib.Path(p).read_text(encoding='utf-8')
old_btn = '<button type="button" class="btn btn-outline-secondary px-4 fw-bold" id="btnRejectNovnc">'
if 'novncCountdown' not in t:
    t = t.replace(old_btn, '<span id="novncCountdown" class="badge bg-warning text-dark align-self-center fw-bold">30s</span>' + NL + '                ' + old_btn, 1)
helpers = []
helpers.append('    let vncCountdownTimer = null;')
helpers.append('    let vncShownSessionId = null;')
helpers.append('    function stopVncCountdown() { if (vncCountdownTimer) { clearInterval(vncCountdownTimer); vncCountdownTimer = null; } }')
helpers.append("    function hideVncModal() { stopVncCountdown(); vncShownSessionId = null; currentVncSessionId = null; const el = document.getElementById('novncIncomingModal'); if (el) { const m = bootstrap.Modal.getInstance(el); if (m) m.hide(); } }")
helpers.append("    function startVncCountdown(req) { stopVncCountdown(); let remain = 30; if (req.seconds_remaining != null) { remain = parseInt(req.seconds_remaining, 10); } if (req.expires_at) { const left = Math.ceil((new Date(req.expires_at.replace(' ', 'T')) - Date.now()) / 1000); if (!isNaN(left)) { remain = left; } } if (isNaN(remain) || remain < 0) { remain = 0; } const badge = document.getElementById('novncCountdown'); const tick = function() { if (badge) { badge.textContent = remain + 's'; } if (remain <= 0) { hideVncModal(); return; } remain--; }; tick(); vncCountdownTimer = setInterval(tick, 1000); }")
if 'startVncCountdown' not in t:
    t = t.replace('    let currentVncSessionId = null;', '    let currentVncSessionId = null;' + NL + NL.join(helpers), 1)
t = t.replace('currentVncSessionId = req.id;', 'currentVncSessionId = req.id; if (vncShownSessionId !== req.id) { vncShownSessionId = req.id; startVncCountdown(req); }', 1)
t = t.replace('    }, 2500);', '    }, 1000);', 1)
t = t.replace("const modal = bootstrap.Modal.getInstance(document.getElementById('novncIncomingModal'));", "stopVncCountdown(); vncShownSessionId = null; const modal = bootstrap.Modal.getInstance(document.getElementById('novncIncomingModal'));")
write(p, t)
print('main countdown: ' + str(t.count('novncCountdown')))
print('main start: ' + str(t.count('startVncCountdown')))

p2 = 'app/Views/tickets/show.php'
t2 = pathlib.Path(p2).read_text(encoding='utf-8')
idx = t2.find('id="btnBypassAcceptTest"')
if idx != -1:
    bs = t2.rfind('<button', 0, idx)
    be = t2.find('</button>', bs) + 9
    repl = '<div class="text-warning fw-bold" id="vncCountdownText">Con 30 giay cho user chap nhan</div>' + NL + '                        <div class="mt-3"><input id="vncTargetIp" class="form-control form-control-sm mx-auto" style="max-width:320px" placeholder="IP LAN may enduser, vd 192.168.1.50"></div>'
    t2 = t2[:bs] + repl + t2[be:]
s = t2.find('    const btnBypassAcceptTest')
e = t2.find('    const btnOpenNovncModal')
if s != -1 and e != -1 and e > s:
    t2 = t2[:s] + t2[e:]
lines = t2.split(NL)
out = []
i = 0
while i < len(lines):
    if 'if (btnBypassAcceptTest)' in lines[i]:
        i = i + 4
        continue
    out.append(lines[i])
    i = i + 1
t2 = NL.join(out)
t2 = t2.replace("session_pass: '123456',", "session_pass: '123456', target_ip: (document.getElementById('vncTargetIp') ? document.getElementById('vncTargetIp').value.trim() : ''),", 1)
t2 = t2.replace("} else if (data.status === 'rejected') {", "} else if (data.status === 'expired') { clearInterval(vncPollTimer); document.getElementById('vncStatusBadge').className = 'badge bg-secondary'; document.getElementById('vncStatusBadge').textContent = 'Het han 30 giay - da tu huy'; alert('Yeu cau remote da het han 30 giay vi user chua chap nhan.'); bootstrap.Modal.getInstance(document.getElementById('itNovncModal')).hide(); } else if (data.status === 'rejected') {", 1)
t2 = t2.replace('setInterval(pollVncSessionStatus, 2000);', 'setInterval(pollVncSessionStatus, 1000);', 1)
write(p2, t2)
print('show bypass left: ' + str(t2.count('btnBypassAcceptTest')))
print('show target_ip: ' + str(t2.count('target_ip')))
print('show expired: ' + str(t2.count("data.status === 'expired'")))

bridge_lines = []
bridge_lines.append('import os, sys, socket, subprocess')
bridge_lines.append('')
bridge_lines.append('def valid_ip(v):')
bridge_lines.append('    try:')
bridge_lines.append('        socket.inet_aton(v)')
bridge_lines.append('        return True')
bridge_lines.append('    except Exception:')
bridge_lines.append('        return False')
bridge_lines.append('')
bridge_lines.append("target_ip = sys.argv[1] if len(sys.argv) > 1 else os.environ.get('VNC_TARGET_IP', '').strip()")
bridge_lines.append('if not target_ip:')
bridge_lines.append("    print('[!] Chua co IP may enduser. Chay: python bridge.py <IP_ENDUSER>')")
bridge_lines.append("    print('[!] Chi khi test 1 may moi dung: python bridge.py 127.0.0.1')")
bridge_lines.append('    sys.exit(2)')
bridge_lines.append('if not valid_ip(target_ip):')
bridge_lines.append("    print('IP khong hop le')")
bridge_lines.append('    sys.exit(2)')
bridge_lines.append("web_root = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'public', 'novnc')")
bridge_lines.append("print('Target VNC may enduser: ' + target_ip + ':5900')")
bridge_lines.append("cmd = [sys.executable, '-m', 'websockify', '6080', target_ip + ':5900', '--web=' + web_root]")
bridge_lines.append('subprocess.run(cmd)')
write('bridge.py', NL.join(bridge_lines) + NL)
bat_lines = []
bat_lines.append('@echo off')
bat_lines.append('chcp 65001 >nul')
bat_lines.append('title TaskFlow noVNC Bridge (Websockify)')
bat_lines.append('color 0A')
bat_lines.append('cd /d "%~dp0"')
bat_lines.append('if "%~1"=="" (echo Nhap IP may enduser, vd: start_novnc_bridge.bat 192.168.1.50 ^& pause ^& exit /b 2)')
bat_lines.append('python bridge.py %~1')
bat_lines.append('pause')
write('start_novnc_bridge.bat', NL.join(bat_lines) + NL)
print('bridge done')
