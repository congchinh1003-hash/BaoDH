# Download the personalized version from /remote-support. Run as Administrator
# while the employee who will receive support is signed into the console.
$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
if (-not $TaskFlowInstallOptions) { throw 'Download a personalized installer from TaskFlow Remote Support.' }
$options = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($TaskFlowInstallOptions)) | ConvertFrom-Json
if (([Uri]$options.url).Scheme -ne 'https') { throw 'HTTPS is required.' }
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
if (-not ([Security.Principal.WindowsPrincipal]$identity).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    throw 'Open PowerShell as Administrator, then run this installer while the employee is signed into Windows.'
}
if (-not [Environment]::Is64BitOperatingSystem) { throw 'Windows x64 is required.' }
$release = (Get-ItemProperty 'HKLM:\SOFTWARE\Microsoft\NET Framework Setup\NDP\v4\Full').Release
if ($release -lt 528040) { throw 'Install .NET Framework 4.8, then run this installer.' }
$installDir = Join-Path $env:ProgramFiles 'TaskFlowAgent'
$consoleAccount = (Get-CimInstance -ClassName Win32_ComputerSystem).UserName
if (-not $consoleAccount) { throw 'The employee must be signed into the Windows console before installation.' }
$consoleSid = ([Security.Principal.NTAccount]$consoleAccount).Translate([Security.Principal.SecurityIdentifier]).Value
$stateDir = Join-Path ([Environment]::GetFolderPath('CommonApplicationData')) 'TaskFlowAgent'
$credentialPath = Join-Path $stateDir 'credentials.dat'
$ownerPath = Join-Path $installDir 'owner.sid'
if ((Test-Path -LiteralPath $ownerPath) -and [IO.File]::ReadAllText($ownerPath).Trim() -ne $consoleSid) {
    throw 'This agent belongs to another Windows account. Sign into the original employee account before repair.'
}
$temporaryDir = Join-Path ([IO.Path]::GetTempPath()) ('TaskFlowAgent-' + [Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $temporaryDir -Force | Out-Null
try {
    $zip = Join-Path $temporaryDir 'agent.zip'
    Invoke-WebRequest -UseBasicParsing -Uri ($options.url + '/remote-support/agent/package') -OutFile $zip
    if ((Get-FileHash -LiteralPath $zip -Algorithm SHA256).Hash.ToLowerInvariant() -ne $options.package_sha256) { throw 'Agent package checksum mismatch.' }
    $packageDir = Join-Path $temporaryDir 'package'
    Expand-Archive -LiteralPath $zip -DestinationPath $packageDir
    $runtimeCheck = Start-Process -FilePath (Join-Path $packageDir 'TaskFlowAgent.exe') -ArgumentList '--runtime-check' -PassThru -Wait -WindowStyle Hidden
    if ($runtimeCheck.ExitCode -ne 0) {
        $bootstrap = Join-Path $temporaryDir 'WebView2Setup.exe'
        Invoke-WebRequest -UseBasicParsing -Uri 'https://go.microsoft.com/fwlink/p/?LinkId=2124703' -OutFile $bootstrap
        $signature = Get-AuthenticodeSignature -LiteralPath $bootstrap
        if ($signature.Status -ne 'Valid' -or $signature.SignerCertificate.Subject -notmatch 'O=Microsoft Corporation') { throw 'WebView2 installer signature verification failed.' }
        $setup = Start-Process -FilePath $bootstrap -ArgumentList '/silent /install' -Wait -PassThru -WindowStyle Hidden
        if ($setup.ExitCode -ne 0) { throw 'WebView2 runtime installation failed.' }
        $runtimeCheck = Start-Process -FilePath (Join-Path $packageDir 'TaskFlowAgent.exe') -ArgumentList '--runtime-check' -PassThru -Wait -WindowStyle Hidden
        if ($runtimeCheck.ExitCode -ne 0) { throw 'WebView2 runtime is not available after installation.' }
    }
    Add-Type -AssemblyName System.Security
    if (Test-Path -LiteralPath $credentialPath) {
        $existing = [Text.Encoding]::UTF8.GetString([Security.Cryptography.ProtectedData]::Unprotect([IO.File]::ReadAllBytes($credentialPath),$null,[Security.Cryptography.DataProtectionScope]::LocalMachine)) | ConvertFrom-Json
        if ($existing.url -ne $options.url) { throw 'This PC is enrolled with another TaskFlow server.' }
        if ($existing.windows_sid -ne $consoleSid) { throw 'Sign into the originally enrolled employee account before repair.' }
    } else {
        $result = Invoke-RestMethod -Method Post -Uri ($options.url + '/remote-support/agent/enroll') -ContentType 'application/json' -Body (@{ enrollment=$options.enrollment; name=$env:COMPUTERNAME } | ConvertTo-Json)
        if (-not $result.success) { throw 'Enrollment failed.' }
        New-Item -ItemType Directory -Path $stateDir -Force | Out-Null
        # Machine DPAPI plus a restricted directory ACL permits only this employee,
        # administrators, and SYSTEM to read the credential after UAC elevation.
        $acl = New-Object Security.AccessControl.DirectorySecurity
        $acl.SetAccessRuleProtection($true,$false)
        foreach ($principalSid in @('S-1-5-18','S-1-5-32-544',$consoleSid)) {
            $rights = if ($principalSid -eq $consoleSid) { [Security.AccessControl.FileSystemRights]::ReadAndExecute } else { [Security.AccessControl.FileSystemRights]::FullControl }
            $rule = New-Object Security.AccessControl.FileSystemAccessRule(([Security.Principal.SecurityIdentifier]$principalSid),$rights,([Security.AccessControl.InheritanceFlags]'ContainerInherit,ObjectInherit'),[Security.AccessControl.PropagationFlags]::None,[Security.AccessControl.AccessControlType]::Allow)
            $acl.AddAccessRule($rule)
        }
        Set-Acl -LiteralPath $stateDir -AclObject $acl
        $credential = @{ token=$result.token; device_id=$result.device_id; url=$options.url; windows_sid=$consoleSid } | ConvertTo-Json -Compress
        $encrypted = [Security.Cryptography.ProtectedData]::Protect([Text.Encoding]::UTF8.GetBytes($credential),$null,[Security.Cryptography.DataProtectionScope]::LocalMachine)
        [IO.File]::WriteAllBytes($credentialPath,$encrypted)
    }
    $service = Get-Service -Name 'TaskFlowRemoteAgent' -ErrorAction SilentlyContinue
    if ($service) { Stop-Service -Name 'TaskFlowRemoteAgent' -Force }
    New-Item -ItemType Directory -Path $installDir -Force | Out-Null
    Copy-Item -Path (Join-Path $packageDir '*') -Destination $installDir -Recurse -Force
    [IO.File]::WriteAllText((Join-Path $installDir 'owner.sid'),$consoleSid)
    $agentExecutable = Join-Path $installDir 'TaskFlowAgent.exe'
    if ($service) { Set-Service -Name 'TaskFlowRemoteAgent' -StartupType Automatic }
    else { New-Service -Name 'TaskFlowRemoteAgent' -BinaryPathName ('"' + $agentExecutable + '" --service') -DisplayName 'TaskFlow Remote Support Agent' -StartupType Automatic | Out-Null }
    & sc.exe failure TaskFlowRemoteAgent reset= 86400 actions= restart/5000/restart/15000/restart/30000 | Out-Null
    Start-Service -Name 'TaskFlowRemoteAgent'
    Write-Host 'TaskFlow Agent installed. First IT connection asks for consent. Use the tray menu to disconnect or revoke access.'
} finally {
    $resolvedTemporary = [IO.Path]::GetFullPath($temporaryDir)
    $tempRoot = [IO.Path]::GetFullPath([IO.Path]::GetTempPath()).TrimEnd('\') + '\'
    if ($resolvedTemporary.StartsWith($tempRoot,[StringComparison]::OrdinalIgnoreCase) -and (Split-Path $resolvedTemporary -Leaf) -like 'TaskFlowAgent-*') {
        Remove-Item -LiteralPath $resolvedTemporary -Recurse -Force
    }
}
