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

    [DllImport("user32.dll")]
    public static extern short VkKeyScan(char ch);

    [DllImport("user32.dll")]
    public static extern bool SetProcessDPIAware();

    public const uint MOUSEEVENTF_MOVE       = 0x0001;
    public const uint MOUSEEVENTF_LEFTDOWN   = 0x0002;
    public const uint MOUSEEVENTF_LEFTUP     = 0x0004;
    public const uint MOUSEEVENTF_RIGHTDOWN  = 0x0008;
    public const uint MOUSEEVENTF_RIGHTUP    = 0x0010;
    public const uint MOUSEEVENTF_MIDDLEDOWN = 0x0020;
    public const uint MOUSEEVENTF_MIDDLEUP   = 0x0040;
    public const uint MOUSEEVENTF_WHEEL      = 0x0800;
    public const uint MOUSEEVENTF_ABSOLUTE   = 0x8000;
    public const uint KEYEVENTF_KEYUP        = 0x0002;

    public static void ClickLeft(int x, int y, int sw, int sh) {
        SetCursorPos(x, y);
        uint nx = (uint)Math.Round((double)x * 65535.0 / (double)(sw > 0 ? sw : 1920));
        uint ny = (uint)Math.Round((double)y * 65535.0 / (double)(sh > 0 ? sh : 1080));
        mouse_event(MOUSEEVENTF_ABSOLUTE | MOUSEEVENTF_MOVE | MOUSEEVENTF_LEFTDOWN, nx, ny, 0, 0);
        mouse_event(MOUSEEVENTF_LEFTDOWN, 0, 0, 0, 0);
        System.Threading.Thread.Sleep(35);
        mouse_event(MOUSEEVENTF_ABSOLUTE | MOUSEEVENTF_MOVE | MOUSEEVENTF_LEFTUP, nx, ny, 0, 0);
        mouse_event(MOUSEEVENTF_LEFTUP, 0, 0, 0, 0);
    }

    public static void DoubleClick(int x, int y, int sw, int sh) {
        ClickLeft(x, y, sw, sh);
        System.Threading.Thread.Sleep(80);
        ClickLeft(x, y, sw, sh);
    }

    public static void ClickRight(int x, int y, int sw, int sh) {
        SetCursorPos(x, y);
        uint nx = (uint)Math.Round((double)x * 65535.0 / (double)(sw > 0 ? sw : 1920));
        uint ny = (uint)Math.Round((double)y * 65535.0 / (double)(sh > 0 ? sh : 1080));
        mouse_event(MOUSEEVENTF_ABSOLUTE | MOUSEEVENTF_MOVE | MOUSEEVENTF_RIGHTDOWN, nx, ny, 0, 0);
        mouse_event(MOUSEEVENTF_RIGHTDOWN, 0, 0, 0, 0);
        System.Threading.Thread.Sleep(35);
        mouse_event(MOUSEEVENTF_ABSOLUTE | MOUSEEVENTF_MOVE | MOUSEEVENTF_RIGHTUP, nx, ny, 0, 0);
        mouse_event(MOUSEEVENTF_RIGHTUP, 0, 0, 0, 0);
    }

    public static void MouseDown(int x, int y, int sw, int sh) {
        SetCursorPos(x, y);
        uint nx = (uint)Math.Round((double)x * 65535.0 / (double)(sw > 0 ? sw : 1920));
        uint ny = (uint)Math.Round((double)y * 65535.0 / (double)(sh > 0 ? sh : 1080));
        mouse_event(MOUSEEVENTF_ABSOLUTE | MOUSEEVENTF_MOVE | MOUSEEVENTF_LEFTDOWN, nx, ny, 0, 0);
        mouse_event(MOUSEEVENTF_LEFTDOWN, 0, 0, 0, 0);
    }

    public static void MouseUp(int x, int y, int sw, int sh) {
        SetCursorPos(x, y);
        uint nx = (uint)Math.Round((double)x * 65535.0 / (double)(sw > 0 ? sw : 1920));
        uint ny = (uint)Math.Round((double)y * 65535.0 / (double)(sh > 0 ? sh : 1080));
        mouse_event(MOUSEEVENTF_ABSOLUTE | MOUSEEVENTF_MOVE | MOUSEEVENTF_LEFTUP, nx, ny, 0, 0);
        mouse_event(MOUSEEVENTF_LEFTUP, 0, 0, 0, 0);
    }

    public static void MoveCursor(int x, int y, int sw, int sh) {
        SetCursorPos(x, y);
        uint nx = (uint)Math.Round((double)x * 65535.0 / (double)(sw > 0 ? sw : 1920));
        uint ny = (uint)Math.Round((double)y * 65535.0 / (double)(sh > 0 ? sh : 1080));
        mouse_event(MOUSEEVENTF_ABSOLUTE | MOUSEEVENTF_MOVE, nx, ny, 0, 0);
    }

    public static void Scroll(int delta) {
        mouse_event(MOUSEEVENTF_WHEEL, 0, 0, unchecked((uint)delta), 0);
    }

    public static void PressKey(byte vkCode) {
        keybd_event(vkCode, 0, 0, 0);
        System.Threading.Thread.Sleep(20);
        keybd_event(vkCode, 0, KEYEVENTF_KEYUP, 0);
    }

    public static void SendHotkey(byte mod, byte key) {
        keybd_event(mod, 0, 0, 0);
        keybd_event(key, 0, 0, 0);
        System.Threading.Thread.Sleep(25);
        keybd_event(key, 0, KEYEVENTF_KEYUP, 0);
        keybd_event(mod, 0, KEYEVENTF_KEYUP, 0);
    }

    public static void TypeChar(char c) {
        short vk = VkKeyScan(c);
        if (vk == -1) return;
        byte vkCode = (byte)(vk & 0xff);
        int shiftState = (vk >> 8) & 0xff;
        bool shift = (shiftState & 1) != 0;
        bool ctrl = (shiftState & 2) != 0;
        bool alt = (shiftState & 4) != 0;

        if (shift) keybd_event(0x10, 0, 0, 0);
        if (ctrl) keybd_event(0x11, 0, 0, 0);
        if (alt) keybd_event(0x12, 0, 0, 0);

        keybd_event(vkCode, 0, 0, 0);
        System.Threading.Thread.Sleep(15);
        keybd_event(vkCode, 0, KEYEVENTF_KEYUP, 0);

        if (alt) keybd_event(0x12, 0, KEYEVENTF_KEYUP, 0);
        if (ctrl) keybd_event(0x11, 0, KEYEVENTF_KEYUP, 0);
        if (shift) keybd_event(0x10, 0, KEYEVENTF_KEYUP, 0);
    }
}
"@

if (-not ([System.Management.Automation.PSTypeName]'Win32Input').Type) {
    Add-Type -TypeDefinition $win32Source
}

# Enable DPI Awareness for physical pixel coordinates
try { [Win32Input]::SetProcessDPIAware() | Out-Null } catch {}

# Resolve Session Code and Host URL
if (-not $s) { $s = $sessionCode }
if (-not $h) { $h = $hostUrl }

Clear-Host
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "       TASKFLOW ENTERPRISE REMOTE DESKTOP AGENT v2.0" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Cyan

if (-not $s) {
    Write-Host ""
    $s = Read-Host "Nhap Ma Phien Ket Noi hoac PIN 6 so (Vi du: 167274 hoac REM-167274)"
}

$s = $s.Trim().ToUpper()
if (-not $s.StartsWith("REM-")) {
    $s = "REM-" + $s
}

if (-not $h) {
    # Auto-detect local development server or cloud server
    try {
        $null = Invoke-RestMethod -Uri "http://localhost:3000/remote-support/api/control/status/$s" -TimeoutSec 1 -ErrorAction Stop
        $h = "http://localhost:3000"
    } catch {
        $h = "https://baodh.onrender.com"
    }
}
# Ensure https if on render
if ($h -match "onrender.com" -and $h.StartsWith("http://")) {
    $h = $h.Replace("http://", "https://")
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
    "ENTER"      = 0x0D
    "BACKSPACE"  = 0x08
    "TAB"        = 0x09
    "ESCAPE"     = 0x1B
    "SPACE"      = 0x20
    " "          = 0x20
    "WIN"        = 0x5B
    "META"       = 0x5B
    "WINDOWS"    = 0x5B
    "UP"         = 0x26
    "ARROWUP"    = 0x26
    "DOWN"       = 0x28
    "ARROWDOWN"  = 0x28
    "LEFT"       = 0x25
    "ARROWLEFT"  = 0x25
    "RIGHT"      = 0x27
    "ARROWRIGHT" = 0x27
    "DELETE"     = 0x2E
    "HOME"       = 0x24
    "END"        = 0x23
    "PAGEUP"     = 0x21
    "PAGEDOWN"   = 0x22
    "F1"         = 0x70
    "F2"         = 0x71
    "F3"         = 0x72
    "F4"         = 0x73
    "F5"         = 0x74
    "F6"         = 0x75
    "F7"         = 0x76
    "F8"         = 0x77
    "F9"         = 0x78
    "F10"        = 0x79
    "F11"        = 0x7A
    "F12"        = 0x7B
}

# Main Control Loop (45ms polling for real-time responsiveness)
while ($true) {
    try {
        # Send heartbeat every 4 seconds
        if (([DateTime]::Now - $lastHeartbeat).TotalSeconds -ge 4) {
            $lastHeartbeat = [DateTime]::Now
            $heartbeatBody = @{ resolution = $resStr } | ConvertTo-Json
            Invoke-RestMethod -Uri "$h/remote-support/api/control/heartbeat/$s" -Method Post -Body $heartbeatBody -ContentType "application/json" -TimeoutSec 3 | Out-Null
        }

        # Poll pending events
        $resp = Invoke-RestMethod -Uri "$h/remote-support/api/control/poll/$s?after_id=$lastId" -Method Get -TimeoutSec 3
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
                    [Win32Input]::MoveCursor($targetX, $targetY, $screenWidth, $screenHeight)
                }
                # MOUSE CLICK (LEFT, RIGHT, DOUBLE)
                elseif ($type -eq 'MOUSE_CLICK') {
                    $targetX = [Math]::Round($p.x * $screenWidth)
                    $targetY = [Math]::Round($p.y * $screenHeight)
                    if ($p.button -eq 'right') {
                        [Win32Input]::ClickRight($targetX, $targetY, $screenWidth, $screenHeight)
                        Write-Host " [ACTION] Chuot phai: ($targetX, $targetY)" -ForegroundColor DarkCyan
                    } elseif ($p.double) {
                        [Win32Input]::DoubleClick($targetX, $targetY, $screenWidth, $screenHeight)
                        Write-Host " [ACTION] Double Click: ($targetX, $targetY)" -ForegroundColor Cyan
                    } else {
                        [Win32Input]::ClickLeft($targetX, $targetY, $screenWidth, $screenHeight)
                        Write-Host " [ACTION] Chuot trai: ($targetX, $targetY)" -ForegroundColor Green
                    }
                }
                # MOUSE DOWN / UP (DRAG & SELECT)
                elseif ($type -eq 'MOUSE_DOWN') {
                    $targetX = [Math]::Round($p.x * $screenWidth)
                    $targetY = [Math]::Round($p.y * $screenHeight)
                    [Win32Input]::MouseDown($targetX, $targetY, $screenWidth, $screenHeight)
                }
                elseif ($type -eq 'MOUSE_UP') {
                    $targetX = [Math]::Round($p.x * $screenWidth)
                    $targetY = [Math]::Round($p.y * $screenHeight)
                    [Win32Input]::MouseUp($targetX, $targetY, $screenWidth, $screenHeight)
                }
                # KEY PRESS
                elseif ($type -eq 'KEY_PRESS') {
                    $rawKey = "$($p.key)"
                    $keyUpper = $rawKey.ToUpper()

                    # Handle Ctrl shortcuts
                    if ($keyUpper.StartsWith("CTRL_")) {
                        $charPart = $keyUpper.Substring(5)
                        if ($charPart.Length -eq 1) {
                            $c = [char]$charPart[0]
                            $vk = [int]$c
                            [Win32Input]::SendHotkey(0x11, [byte]$vk)
                            Write-Host " [ACTION] Ctrl+$charPart" -ForegroundColor Yellow
                        }
                    }
                    elseif ($vkMap.ContainsKey($keyUpper)) {
                        [Win32Input]::PressKey($vkMap[$keyUpper])
                        Write-Host " [ACTION] Phim dac biet: [$keyUpper]" -ForegroundColor Yellow
                    }
                    elseif ($rawKey.Length -eq 1) {
                        # Single character typing (letters, numbers, symbols, uppercase)
                        [Win32Input]::TypeChar($rawKey[0])
                        Write-Host " [ACTION] Go phim: '$rawKey'" -ForegroundColor DarkYellow
                    }
                }
                # TEXT INPUT (PASTE STRINGS)
                elseif ($type -eq 'TEXT_INPUT') {
                    $textToType = $p.text
                    if ($textToType) {
                        try {
                            if (Get-Command Set-Clipboard -ErrorAction SilentlyContinue) {
                                Set-Clipboard -Value $textToType
                            } else {
                                [System.Windows.Forms.Clipboard]::SetText($textToType)
                            }
                            [Win32Input]::SendHotkey(0x11, 0x56) # Ctrl + V
                            Write-Host " [ACTION] Dan van ban: `"$textToType`"" -ForegroundColor Magenta
                        } catch {
                            Write-Host " [LOI] Khong the dan clipboard: $($_.Exception.Message)" -ForegroundColor Red
                        }
                    }
                }
                # SHORTCUTS / HOTKEYS
                elseif ($type -eq 'HOTKEY') {
                    $hk = "$($p.hotkey)".ToLower()
                    if ($hk -eq 'win') {
                        [Win32Input]::PressKey(0x5B) # Windows key
                        Write-Host " [ACTION] Bat Start Menu" -ForegroundColor Yellow
                    } elseif ($hk -eq 'run') {
                        [Win32Input]::SendHotkey(0x5B, 0x52) # Win + R
                        Write-Host " [ACTION] Mo Run (Win+R)" -ForegroundColor Yellow
                    } elseif ($hk -eq 'taskmgr') {
                        Start-Process "taskmgr.exe"
                        Write-Host " [ACTION] Mo Task Manager" -ForegroundColor Yellow
                    } elseif ($hk -eq 'explorer') {
                        [Win32Input]::SendHotkey(0x5B, 0x45) # Win + E
                        Write-Host " [ACTION] Mo File Explorer" -ForegroundColor Yellow
                    } elseif ($hk -eq 'cmd') {
                        Start-Process "cmd.exe"
                        Write-Host " [ACTION] Mo CMD" -ForegroundColor Yellow
                    } elseif ($hk -eq 'powershell') {
                        Start-Process "powershell.exe"
                        Write-Host " [ACTION] Mo PowerShell" -ForegroundColor Yellow
                    } elseif ($hk -eq 'alt_f4') {
                        [Win32Input]::SendHotkey(0x12, 0x73) # Alt + F4
                        Write-Host " [ACTION] Dong Cua So (Alt+F4)" -ForegroundColor Yellow
                    } elseif ($hk -eq 'notepad') {
                        Start-Process "notepad.exe"
                        Write-Host " [ACTION] Mo Notepad" -ForegroundColor Yellow
                    } elseif ($hk -eq 'calc') {
                        Start-Process "calc.exe"
                        Write-Host " [ACTION] Mo Calculator" -ForegroundColor Yellow
                    } elseif ($hk -eq 'show_desktop') {
                        [Win32Input]::SendHotkey(0x5B, 0x44) # Win + D
                        Write-Host " [ACTION] Thu nho het / Hien man hinh Desktop (Win+D)" -ForegroundColor Yellow
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
    Start-Sleep -Milliseconds 45
}

Write-Host "Cam on ban da su dung TaskFlow Remote Assistance." -ForegroundColor Cyan
