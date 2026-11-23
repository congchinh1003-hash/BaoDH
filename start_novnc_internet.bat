@echo off
chcp 65001 >nul
title TaskFlow noVNC Internet Tunnel (Cho phep IT dieu khien tu xa khac mang)
color 0B
cd /d "%~dp0"

echo ======================================================================
echo    TASKFLOW noVNC INTERNET CONNECTOR (DIEU KHIEN QUA 2 MANG KHAC NHAU)
echo ======================================================================
echo.
echo [1/2] Dang kiem tra TightVNC Server (Cong 5900)...
netstat -ano | findstr ":5900" >nul
if %errorlevel% neq 0 (
    echo [CANH BAO] TightVNC Server chua chay! Vui long bat TightVNC Server tren may nay.
) else (
    echo [OK] TightVNC Server dang hoat dong san sang.
)

echo.
echo [2/2] Dang khoi dong Cau noi Websockify va Cloudflare Tunnel...
echo.

:: Khoi dong websockify chay ngam o background
start /b python -m websockify 6080 127.0.0.1:5900 --web="%~dp0public\novnc" >nul 2>&1

:: Khoi dong cloudflared tao link cong khai
echo ======================================================================
echo QUAN TRONG: Hay chu y dong ben duoi co chua:
echo          https://[ten-ngau-nhien].trycloudflare.com
echo Copy link do va them duoi: /vnc_lite.html?password=123456
echo gui cho IT la IT dieu khien duoc ngay qua Internet!
echo ======================================================================
echo.

cloudflared.exe tunnel --url http://127.0.0.1:6080
pause
