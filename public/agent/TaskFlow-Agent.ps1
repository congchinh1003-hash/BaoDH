# ==============================================================================
# TASKFLOW ENTERPRISE REMOTE DESKTOP AGENT v2.0
# Zero-install native Windows remote control agent for IT Service Desk
# ==============================================================================

[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

# Compile native Win32 User32 APIs
$win32Source = @"
using System;
using System.Runtime.InteropServices;

public class Win32Input {
    [DllImport("user32.dll")]
    public static extern bool SetCursorPos(int X, int Y);

    [DllImport("user32.dll")]
    public static extern void mouse_event(uint dwFlags, uint dx, uint dy, uint dwData, int dwExtraInfo);

    [DllImport("user32.dll")]
    public static extern void keybd_event(byte bVk, byte bScan, uint dwFlags, int dwExtraInfo);

    public const uint MOUSEEVENTF_LEFTDOWN   = 0x0002;
    public const uint MOUSEEVENTF_LEFTUP     = 0x0004;
    public const uint MOUSEEVENTF_RIGHTDOWN  = 0x0008;
    public const uint MOUSEEVENTF_RIGHTUP    = 0x0010;
    public const uint MOUSEEVENTF_MIDDLEDOWN = 0x0020;
    public const uint MOUSEEVENTF_MIDDLEUP   = 0x0040;
    public const uint MOUSEEVENTF_WHEEL      = 0x0800;
    public const uint KEYEVENTF_KEYUP        = 0x0002;
    public const uint KEYEVENTF_EXTENDEDKEY  = 0x0001;

    public static void ClickLeft(int x, int y) {
        SetCursorPos(x, y);
        mouse_event(MOUSEEVENTF_LEFTDOWN, (uint)x, (uint)y, 0, 0);
        mouse_event(MOUSEEVENTF_LEFTUP, (uint)x, (uint)y, 0, 0);
    }

    public static void DoubleClick(int x, int y) {
        ClickLeft(x, y);
        System.Threading.Thread.Sleep(60);
        ClickLeft(x, y);
    }

    public static void ClickRight(int x, int y) {
        SetCursorPos(x, y);
        mouse_event(MOUSEEVENTF_RIGHTDOWN, (uint)x, (uint)y, 0, 0);
        mouse_event(MOUSEEVENTF_RIGHTUP, (uint)x, (uint)y, 0, 0);
    }

    public static void MouseDown(int x, int y) {
        SetCursorPos(x, y);
        mouse_event(MOUSEEVENTF_LEFTDOWN, (uint)x, (uint)y, 0, 0);
    }

    public static void MouseUp(int x, int y) {
        SetCursorPos(x, y);
        mouse_event(MOUSEEVENTF_LEFTUP, (uint)x, (uint)y, 0, 0);
    }

    public static void Scroll(int delta) {
        mouse_event(MOUSEEVENTF_WHEEL, 0, 0, (uint)delta, 0);
    }

    public static void PressKey(byte vkCode) {
        keybd_event(vkCode, 0, 0, 0);
        System.Threading.Thread.Sleep(20);
        keybd_event(vkCode, 0, KEYEVENTF_KEYUP, 0);
    }

    public static void SendHotkey(byte mod, byte key) {
        keybd_event(mod, 0, 0, 0);
        keybd_event(key, 0, 0, 0);
        System.Threading.Thread.Sleep(20);
        keybd_event(key, 0, KEYEVENTF_KEYUP, 0);
        keybd_event(mod, 0, KEYEVENTF_KEYUP, 0);
    }
}
"@

if (-not ([System.Management.Automation.PSTypeName]'Win32Input').Type) {
    Add-Type -TypeDefinition $win32Source
}

# Resolve Session Code and Host URL
if (-not $s) { $s = $sessionCode }
if (-not $h) { $h = $hostUrl }

if (-not $h) {
    $h = "https://baodh.onrender.com"
}
# Ensure https if on render
if ($h -match "onrender.com" -and $h.StartsWith("http://")) {
    $h = $h.Replace("http://", "https://")
}

Clear-Host
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "       TASKFLOW ENTERPRISE REMOTE DESKTOP AGENT v2.0" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Cyan

if (-not $s) {
    Write-Host ""
    $s = Read-Host "Nhap Ma Phien Ket Noi hoac PIN 6 so (Vi du: 614045 hoac REM-614045)"
}

$s = $s.Trim().ToUpper()
if (-not $s.StartsWith("REM-")) {
    $s = "REM-" + $s
}

$screen = [System.Windows.Forms.Screen]::PrimaryScreen.Bounds
$screenWidth = $screen.Width
$screenHeight = $screen.Height
$resStr = "$($screenWidth)x$($screenHeight)"

Write-Host ""
Write-Host " [INFO] May chu: $h" -ForegroundColor Yellow
Write-Host " [INFO] Phien ho tro: $s" -ForegroundColor Yellow
Write-Host " [INFO] Do phan giai man hinh: $resStr" -ForegroundColor Yellow
Write-Host ""
Write-Host " Dang thiet lap ket noi toi Ky thuat vien IT..." -ForegroundColor Gray

# Send Initial Heartbeat
try {
    $heartbeatBody = @{ resolution = $resStr; os = "Windows" } | ConvertTo-Json
    Invoke-RestMethod -Uri "$h/remote-support/api/control/heartbeat/$s" -Method Post -Body $heartbeatBody -ContentType "application/json" -TimeoutSec 5 | Out-Null
    Write-Host " [THANH CONG] Da ket noi truc tiep toi phien ho tro!" -ForegroundColor Green
    Write-Host " [SAN SANG] Ky thuat vien IT co the dieu khien chuot & ban phim tu xa." -ForegroundColor Cyan
    Write-Host " (Nhan Ctrl + C trong cua so nay de ngat quyen dieu khien bat ky luc nao)" -ForegroundColor DarkGray
    Write-Host "------------------------------------------------------------" -ForegroundColor Gray
} catch {
    Write-Host " [CANH BAO] Khong the gui heartbeat ban dau: $($_.Exception.Message)" -ForegroundColor Red
}

$lastId = 0
$lastHeartbeat = [DateTime]::Now

# Virtual Key Mapping
$vkMap = @{
    "ENTER"     = 0x0D
    "BACKSPACE" = 0x08
    "TAB"       = 0x09
    "ESCAPE"    = 0x1B
    "SPACE"     = 0x20
    "WIN"       = 0x5B
    "UP"        = 0x26
    "DOWN"      = 0x28
    "LEFT"      = 0x25
    "RIGHT"     = 0x27
    "DELETE"    = 0x2E
    "HOME"      = 0x24
    "END"       = 0x23
    "PAGEUP"    = 0x21
    "PAGEDOWN"  = 0x22
}

# Main Control Loop (75ms polling for real-time responsiveness)
while ($true) {
    try {
        # Send heartbeat every 4 seconds
        if (([DateTime]::Now - $lastHeartbeat).TotalSeconds -ge 4) {
            $lastHeartbeat = [DateTime]::Now
            $heartbeatBody = @{ resolution = $resStr } | ConvertTo-Json
            Invoke-RestMethod -Uri "$h/remote-support/api/control/heartbeat/$s" -Method Post -Body $heartbeatBody -ContentType "application/json" -TimeoutSec 3 | Out-Null
        }

        # Poll pending events
        $resp = Invoke-RestMethod -Uri "$h/remote-support/api/control/poll/$s?after_id=$lastId" -Method Get -TimeoutSec 4
        if ($resp -and $resp.success) {
            if ($resp.session_status -eq 'ENDED') {
                Write-Host ""
                Write-Host " [THONG BAO] Phien ho tro da duoc ket thuc boi Ky thuat vien IT." -ForegroundColor Yellow
                break
            }

            foreach ($ev in $resp.events) {
                $lastId = [Math]::Max($lastId, [int]$ev.id)
                $type = $ev.event_type
                $p = $ev.payload | ConvertFrom-Json

                # MOUSE MOVE
                if ($type -eq 'MOUSE_MOVE') {
                    $targetX = [Math]::Round($p.x * $screenWidth)
                    $targetY = [Math]::Round($p.y * $screenHeight)
                    [Win32Input]::SetCursorPos($targetX, $targetY)
                }
                # MOUSE CLICK (LEFT, RIGHT, DOUBLE)
                elseif ($type -eq 'MOUSE_CLICK') {
                    $targetX = [Math]::Round($p.x * $screenWidth)
                    $targetY = [Math]::Round($p.y * $screenHeight)
                    if ($p.button -eq 'right') {
                        [Win32Input]::ClickRight($targetX, $targetY)
                    } elseif ($p.double) {
                        [Win32Input]::DoubleClick($targetX, $targetY)
                    } else {
                        [Win32Input]::ClickLeft($targetX, $targetY)
                    }
                }
                # MOUSE DOWN / UP
                elseif ($type -eq 'MOUSE_DOWN') {
                    $targetX = [Math]::Round($p.x * $screenWidth)
                    $targetY = [Math]::Round($p.y * $screenHeight)
                    [Win32Input]::MouseDown($targetX, $targetY)
                }
                elseif ($type -eq 'MOUSE_UP') {
                    $targetX = [Math]::Round($p.x * $screenWidth)
                    $targetY = [Math]::Round($p.y * $screenHeight)
                    [Win32Input]::MouseUp($targetX, $targetY)
                }
                # KEY PRESS
                elseif ($type -eq 'KEY_PRESS') {
                    $keyUpper = "$($p.key)".ToUpper()
                    if ($vkMap.ContainsKey($keyUpper)) {
                        [Win32Input]::PressKey($vkMap[$keyUpper])
                    } else {
                        try {
                            [System.Windows.Forms.SendKeys]::SendWait($p.key)
                        } catch {}
                    }
                }
                # TEXT INPUT (PASTE OR TYPE STRINGS)
                elseif ($type -eq 'TEXT_INPUT') {
                    $textToType = $p.text
                    if ($textToType) {
                        [System.Windows.Forms.Clipboard]::SetText($textToType)
                        [Win32Input]::SendHotkey(0x11, 0x56) # Ctrl + V
                    }
                }
                # SHORTCUTS / HOTKEYS
                elseif ($type -eq 'HOTKEY') {
                    $hk = "$($p.hotkey)".ToLower()
                    if ($hk -eq 'win') {
                        [Win32Input]::PressKey(0x5B) # Windows key
                    } elseif ($hk -eq 'run') {
                        [Win32Input]::SendHotkey(0x5B, 0x52) # Win + R
                    } elseif ($hk -eq 'taskmgr') {
                        Start-Process "taskmgr.exe"
                    } elseif ($hk -eq 'explorer') {
                        [Win32Input]::SendHotkey(0x5B, 0x45) # Win + E
                    } elseif ($hk -eq 'cmd') {
                        Start-Process "cmd.exe"
                    } elseif ($hk -eq 'powershell') {
                        Start-Process "powershell.exe"
                    } elseif ($hk -eq 'alt_f4') {
                        [Win32Input]::SendHotkey(0x12, 0x73) # Alt + F4
                    }
                }
                # SCROLL
                elseif ($type -eq 'SCROLL') {
                    $delta = [int]($p.deltaY * -1)
                    [Win32Input]::Scroll($delta)
                }
            }
        }
    } catch {
        # Continue on temporary network blips
    }
    Start-Sleep -Milliseconds 70
}

Write-Host "Cam on ban da su dung TaskFlow Remote Assistance." -ForegroundColor Cyan
