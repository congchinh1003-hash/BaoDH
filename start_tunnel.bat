@echo off
title Cloudflare Tunnel - Public URL for TaskFlow ITSM
cd /d "%~dp0"
echo ======================================================================
echo   DANG KHOI TAO DUONG TRUYEN CONG KHAI CHO TASKFLOW IT SERVICE DESK
echo ======================================================================
echo.
echo Hay chu y dong co chua 'https://....trycloudflare.com' ben duoi.
echo Do chinh la duong link de copy gui cho khach hang test!
echo.
echo Nhan Ctrl + C neu muon dung lai.
echo ======================================================================
echo.
cloudflared.exe tunnel --url http://localhost:3000
pause
