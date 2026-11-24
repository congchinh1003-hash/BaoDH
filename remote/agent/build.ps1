$ErrorActionPreference = 'Stop'
$repoRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..'))
$output = Join-Path $PSScriptRoot 'artifacts\package'
dotnet build (Join-Path $PSScriptRoot 'TaskFlowAgent.csproj') --configuration Release --output $output
if ($LASTEXITCODE -ne 0) { throw 'Windows agent build failed' }
New-Item -ItemType Directory -Path (Join-Path $output 'web') -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'web\agent.html') -Destination (Join-Path $output 'web\agent.html')
Copy-Item -LiteralPath (Join-Path $repoRoot 'public\js\remote-transport.js') -Destination (Join-Path $output 'web\remote-transport.js')
$package = Join-Path $repoRoot 'public\agent\TaskFlow-Agent.zip'
Compress-Archive -Path (Join-Path $output '*') -DestinationPath $package -Force
Write-Host "Built $package"
