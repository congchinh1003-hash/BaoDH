@echo off
chcp 65001 >nul
title TaskFlow noVNC Bridge (Websockify)
color 0A
cd /d "%~dp0"
if "%~1"=="" (echo Nhap IP may enduser, vd: start_novnc_bridge.bat 192.168.1.50 ^& pause ^& exit /b 2)
python bridge.py %~1
pause
