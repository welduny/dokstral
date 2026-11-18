param(
    [switch]$InstallStartupTask
)

$ErrorActionPreference = 'Stop'

$mysqlRoot = Join-Path $env:SystemDrive 'xampp\mysql'
$server = Join-Path $mysqlRoot 'bin\mysqld.exe'
$dataDir = Join-Path $mysqlRoot 'data_dokstral'
$port = 3307

if (!(Test-Path $server) -or !(Test-Path (Join-Path $dataDir 'my.ini'))) {
    throw "Dokstral MariaDB is not installed at $mysqlRoot."
}

if ($InstallStartupTask) {
    $scriptPath = $PSCommandPath
    $powerShell = Join-Path $PSHOME 'powershell.exe'
    $action = New-ScheduledTaskAction -Execute $powerShell -Argument "-NoProfile -ExecutionPolicy Bypass -File `"$scriptPath`""
    $trigger = New-ScheduledTaskTrigger -AtLogOn -User ([Security.Principal.WindowsIdentity]::GetCurrent().Name)
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable -ExecutionTimeLimit (New-TimeSpan -Minutes 2)

    Register-ScheduledTask -TaskName 'Dokstral MariaDB' -Action $action -Trigger $trigger -Settings $settings -Description 'Starts the local MariaDB instance used by Dokstral.' -Force | Out-Null
}

$listener = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
if ($listener) {
    $process = Get-CimInstance Win32_Process -Filter "ProcessId = $($listener[0].OwningProcess)"
    if ($process.ExecutablePath -eq $server -and $process.CommandLine -like "*$dataDir*") {
        Write-Output "Dokstral MariaDB is already listening on 127.0.0.1:$port."
        exit 0
    }

    throw "Port $port is already used by another process (PID $($listener[0].OwningProcess))."
}

$log = Join-Path $dataDir 'dokstral-mysql.log'
$arguments = @(
    "--defaults-file=$(Join-Path $dataDir 'my.ini')",
    "--basedir=$mysqlRoot",
    "--datadir=$dataDir",
    '--bind-address=127.0.0.1',
    "--pid-file=$(Join-Path $dataDir 'mysqld.pid')",
    "--log-error=$log"
)

Start-Process -FilePath $server -ArgumentList $arguments -WindowStyle Hidden

for ($attempt = 0; $attempt -lt 30; $attempt++) {
    Start-Sleep -Seconds 1
    $listener = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
    if ($listener) {
        $process = Get-CimInstance Win32_Process -Filter "ProcessId = $($listener[0].OwningProcess)"
        if ($process.ExecutablePath -eq $server -and $process.CommandLine -like "*$dataDir*") {
            Write-Output "Dokstral MariaDB is ready on 127.0.0.1:$port."
            exit 0
        }

        throw "Port $port was claimed by another process (PID $($listener[0].OwningProcess))."
    }
}

if (Test-Path $log) {
    Get-Content $log -Tail 30
}
throw "Dokstral MariaDB did not start. See $log."
