import os, sys, socket, subprocess

def valid_ip(v):
    try:
        socket.inet_aton(v)
        return True
    except Exception:
        return False

target_ip = sys.argv[1] if len(sys.argv) > 1 else os.environ.get('VNC_TARGET_IP', '').strip()
if not target_ip:
    print('[!] Chua co IP may enduser. Chay: python bridge.py <IP_ENDUSER>')
    print('[!] Chi khi test 1 may moi dung: python bridge.py 127.0.0.1')
    sys.exit(2)
if not valid_ip(target_ip):
    print('IP khong hop le')
    sys.exit(2)
web_root = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'public', 'novnc')
print('Target VNC may enduser: ' + target_ip + ':5900')
cmd = [sys.executable, '-m', 'websockify', '6080', target_ip + ':5900', '--web=' + web_root]
subprocess.run(cmd)
