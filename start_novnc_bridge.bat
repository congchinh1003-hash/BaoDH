@echo off
chcp 65001 >nul
title TaskFlow noVNC Bridge (Websockify)
color 0A
echo ============================================================
echo      TASKFLOW noVNC WEBSOCKET BRIDGE (Port 6080 -^> 5900)
echo ============================================================
echo Dang khoi chay cau noi noVNC Websockify...
echo Dia chi truyen hinh anh: http://127.0.0.1:6080/vnc_lite.html
echo ------------------------------------------------------------
python -m websockify 6080 127.0.0.1:5900 --web="%~dp0public\novnc"
pause
