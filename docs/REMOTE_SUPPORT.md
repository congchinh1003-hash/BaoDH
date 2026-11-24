# Remote Support

The old ticket flow opened the IT browser's `127.0.0.1:6080`, required a separately running TightVNC/Websockify server, and used a fixed password. The other flow downloaded a session-only PowerShell process and exposed agent APIs using a six-digit PIN. Browser screen capture could not remember consent or inject native keyboard/mouse input. These flows have been replaced within the existing PHP application and `remote_support_sessions` history table.

## Supported environment

- Windows 10/11 x64, .NET Framework 4.8, and Microsoft Edge WebView2 Runtime. The installer checks .NET and installs Microsoft's signed WebView2 Runtime when needed.
- Install as Administrator **while the employee is signed into the Windows console**. Administrator credentials used for elevation may belong to IT. The installer binds the agent to the employee's console account, and the automatic LocalSystem service starts a desktop helper only in that account's active console session. The encrypted agent credential has a restricted file ACL; consent uses that employee's Windows DPAPI profile. Other logged-in accounts are never shared.
- Support covers the complete virtual desktop, including multiple monitors, scaled to a maximum width of 1920 pixels, at up to four JPEG frames per second. Mouse movement, clicks, dragging, scrolling, and key down/up are supported. Audio, file transfer, Ctrl+Alt+Delete, UAC secure desktop, pre-login access, and RDP user-session selection are outside this implementation. Locking or logging off ends the session.
- Current Edge/Chrome for IT. “Device” means the particular browser profile identified by a random HttpOnly Secure cookie plus the signed-in IT account. Clearing cookies or using another profile requires consent again.

## Server deployment

### Current Render public deployment

Use `https://baodh-kfy4.onrender.com/public/remote-support`. Only the end-user PC installs an agent; IT/admin uses a browser. The end user downloads the personalized installer while signed into their own TaskFlow account. The IT hub does not ask IT to install an agent.

The root Docker image now includes the Node relay and starts it alongside Apache. Apache forwards `wss://baodh-kfy4.onrender.com/public/remote-support/relay` to a private loopback listener. A strong signing secret is generated at server startup when one is not configured and shared by PHP/Node through the process environment. Server restarts rotate short-lived relay tickets while retaining agent credentials and remembered consent in the database, so PCs do not need reinstallation. Existing Render database variables are still required; no separate relay service or manually entered remote variables are required for this deployment.

After Render redeploys the current branch, verify `https://baodh-kfy4.onrender.com/public/remote-support/relay/health` returns `ok`. Then sign in as an end user and download the installer. Download/enrollment remain available during relay setup; creating an IT session requires the authenticated relay configuration to be ready. `REMOTE_PUBLIC_URL` can override the canonical public URL. Otherwise `RENDER_EXTERNAL_URL` plus `/public` is used, falling back to the owner-supplied deployment above. Installer URLs never come from request Host headers.

The Linux startup script supervises both services and terminates the container if either fails so Render can restart them together. Only Apache binds Render's public `PORT`; Node runs as `www-data` on `127.0.0.1:8081`.

### Optional separate relay or custom hosting

For custom hosting or an independently deployed relay, set these on the PHP application:

```dotenv
REMOTE_PUBLIC_URL=https://support.example.com
REMOTE_RELAY_URL=wss://relay.example.com
REMOTE_RELAY_SECRET=<random secret of at least 32 characters>
REMOTE_ICE_SERVERS=[{"urls":"stun:stun.l.google.com:19302"}]
```

Generate a secret with `python -c "import secrets; print(secrets.token_hex(32))"`. Set the **same** secret on the separate relay. Keep it out of Git. Remote connections fail closed when HTTPS/WSS configuration or a sufficiently long shared secret is missing. Installer download and enrollment require only a valid canonical HTTPS application URL, including its installation subdirectory.

Build the Windows package on Windows after changing agent or transport code:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File remote\agent\build.ps1
```

`public/agent/TaskFlow-Agent.zip` is checked in so a PHP/Linux deployment can distribute the agent. The personalized installer embeds its SHA-256 digest and checks the HTTPS download before executing it. The application binaries are not code-signed; organizations that require signed binaries should sign the compiled executable before packaging it.

Run `php database/migrate_remote_support.php` with the application's database configuration. Startup also performs this additive migration. Existing history is retained; old session records without a device binding cannot be connected. The retired unauthenticated agent, noVNC, and UltraViewer routes are no longer registered. The application database account needs CREATE/ALTER privileges for migration; run the migration with a deployment account before restricting those privileges.

The relay is a separate Node.js 20+ process:

```powershell
cd remote\relay
npm.cmd ci --omit=dev
# Set REMOTE_RELAY_SECRET in this process's environment.
# Only when this listener is protected by a TLS reverse proxy:
$env:REMOTE_BEHIND_TLS_PROXY = 'true'
$env:REMOTE_RELAY_HOST = '127.0.0.1'
npm.cmd start
```

On Linux use `npm`, not `npm.cmd`. Proxy the public relay's HTTPS/WSS endpoint to this private listener (default port 8081), supporting WebSocket upgrades and long connections. Do not publish the plaintext listener. Alternatively set `REMOTE_TLS_CERT` and `REMOTE_TLS_KEY` to certificate/key files for TLS within Node. `/health` is the health endpoint.

For a server with Docker, two DNS names pointing at it, and an existing reachable MySQL/TiDB database, the included Caddy deployment provisions HTTPS automatically. Add `TASKFLOW_DOMAIN=support.example.com` and `REMOTE_RELAY_DOMAIN=relay.example.com` to your untracked `.env`, along with database credentials and the relay secret, then run from the repository root:

```sh
docker compose --env-file .env -f remote/compose.yml up --build -d
```

Only server ports 80/443 are published. End-user PCs open **no inbound ports**. Both the helper and IT browser use outbound HTTPS/WSS over 443; WebRTC tries direct ICE/STUN connectivity first, then automatically uses WSS after eight seconds or a failed direct connection. WSS also supports restrictive networks that block WebRTC UDP. A corporate proxy must permit these HTTPS domains and WebSocket upgrades. Multiple relay replicas need session affinity/shared room coordination; this implementation uses a single relay instance.

## Install and connect

1. The end user signs in to TaskFlow and opens **Hỗ trợ từ xa**. Click **Tải bộ cài cho máy của tôi**.
2. With the employee still signed into Windows, run the downloaded personalized script in an elevated PowerShell window. IT may provide administrator credentials for elevation:

   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File "C:\Users\employee\Downloads\Install-TaskFlowAgent.ps1"
   ```

   Replace the example path with the downloaded file's absolute path, especially when using another account for elevation.

3. Enrollment expires after 15 minutes and can be redeemed only once. The server stores hashes, not the enrollment or agent bearer token. The Windows service `TaskFlowRemoteAgent` starts automatically and restarts after failures. The helper's TaskFlow tray icon appears when the user is logged in.
4. IT opens **Hỗ trợ từ xa**, selects the enrolled user/PC, and requests a connection. The ticket's **Yêu cầu Remote Máy Tính** button opens the same flow when the requester has exactly one enrolled PC. For multiple PCs, select one in the hub.
5. On the first connection for that IT account/browser device, the Windows helper shows **Accept / Deny** with the operator's name and a device fingerprint. Deny stores no permission. Accept stores permission both in the database and encrypted local state. All later sessions for that pair share the entire desktop without another prompt.
6. The tray icon changes to a warning/error indicator and shows the operator's connection notification before screen transmission. During sharing it reads **REMOTE SESSION ACTIVE**. IT can use **Toàn màn hình** and click the remote image to focus keyboard input.
7. **Disconnect** ends the current session while preserving permission. **Revoke access** stops screen/control immediately, clears local permissions, and revokes all remembered IT grants for this PC on the server. It works while idle too. Offline revocations are saved and synchronized before any future session is processed. The hub also offers **Revoke access** for the user's own PCs.

Downloaded installers can repair/update an existing installation while the original employee is signed into the console. They preserve encrypted credentials, consent, and logs, and do not redeem another enrollment token. Never share a personalized installer; delete it after installation.

## Audit and security behavior

`remote_support_sessions` records operator ID, IT device fingerprint, user/PC, creation/approval time, actual connection start, end time/reason, and transport. The hub shows the most recent 50 sessions and duration. New timestamps use UTC. Rejected/unanswered sessions have no connection start. Disconnect is idempotent; transport fallback preserves the original start time. Crashes/network loss close stale sessions after 20 seconds when the next poll/history request performs cleanup. For abandoned sessions, end time is estimated as the last participant heartbeat plus 20 seconds, so delayed cleanup does not inflate duration.

The helper also appends UTC audit records to `%LOCALAPPDATA%\TaskFlowAgent\sessions.jsonl`. `consent.dat` in that directory uses DPAPI CurrentUser. The device token in `%PROGRAMDATA%\TaskFlowAgent\credentials.dat` uses DPAPI LocalMachine and a directory ACL allowing only SYSTEM, administrators, and read access for the supported employee. Executable/service files reside in Program Files. The server authenticates the helper using its individual 256-bit bearer token and IT using the existing login plus the requesting browser credential. Other IT accounts/devices cannot inspect, signal, join, or end that session. Browser mutations require CSRF tokens. Consent can only be accepted by the authenticated Windows helper.

Direct frames and input use encrypted WebRTC DTLS data channels. Relay frames and input use WSS/TLS, with signed 45-second role/session/identity tickets refreshed from authenticated application polling. Tickets are sent in the first WebSocket message, never URLs. The relay rejects duplicate peers, wrong roles, expired/forged tickets, oversized data, and cross-session delivery. Relay TLS terminates at the trusted relay/proxy, so its administrator is part of the trust boundary. Stopping/revoking closes both transports and releases held keys/buttons. No screen frames or raw input events are stored in the database. SDP/ICE signaling is deleted when sessions end.

## Verification

```powershell
php tests\test_remote_support.php
# With a reachable MySQL server and CREATE DATABASE privileges; uses/deletes its own temporary database:
php tests\test_remote_mysql.php
cd remote\relay
npm.cmd ci
npm.cmd test
npm.cmd run test:browser
# Optional Windows/XAMPP check of the same Apache /public proxy configuration:
npm.cmd run test:proxy
cd ..\..
powershell -NoProfile -ExecutionPolicy Bypass -File remote\agent\build.ps1
```

The PHP suite uses an isolated in-memory database for enrollment replay/expiry, consent isolation/reuse, revoked and denied sessions, concurrency exclusion, audit lifecycle, signaling direction, and controller authentication. Node tests exercise a real relay plus transport edge cases. The browser integration uses installed Edge on Windows (Chromium installed through Playwright on Linux), actual DTLS channels, the helper's HTML/JavaScript bridge, and a temporary local TLS certificate. Windows defaults to `C:\xampp\apache\bin\openssl.exe`; set `OPENSSL_EXE` if needed. Browser tests send generated bytes and control messages; they do not capture or operate the host desktop.

The compiled `remote\agent\artifacts\package\TaskFlowAgent.exe` also supports `--self-test` for user/machine DPAPI and `--webview-test` for native WebView2 initialization, bridge messages, and scripting while hidden. Run these with `Start-Process -Wait -PassThru -WindowStyle Hidden` and verify exit code 0. They perform no enrollment, screen capture, or native input.

After deployment, complete the physical-machine acceptance test:

- Install once on the user's Windows PC. Use an IT PC on another network (e.g. mobile hotspot). Deny the first request; verify no screen/control and a rejected audit record.
- Accept a new request; verify all monitors, mouse/keyboard, operator notification, active tray indicator, and session history/duration.
- Disconnect, reconnect with the same IT account/profile, reboot and sign back into Windows; verify service/helper autostart and no reinstall or repeat prompt.
- Connect from another IT account or browser profile; verify a new consent dialog.
- Block UDP/WebRTC connectivity on the IT side; verify WSS fallback and screen/control without opening client ports.
- Revoke during sharing, while idle, and while offline. Verify screen/input stop immediately and the next session requires consent after reconnecting. Lock/log off the user and close the IT tab; verify sessions end and held input is released.

Automated loopback verification cannot establish behavior across two physical networks or validate service installation/reboot/UAC on a separate end-user PC. Those acceptance steps remain deployment checks.
