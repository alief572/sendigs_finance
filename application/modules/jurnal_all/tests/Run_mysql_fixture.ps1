param(
    [string]$PhpPath = 'C:\xampp74\php\php.exe',
    [string]$MysqlBin = 'C:\xampp74\mysql\bin',
    [int]$Port = 33317,
    [ValidateSet('Mysql_test.php','Payment_reference_mysql_test.php','Debt_posting_test.php')]
    [string]$TestScript = 'Mysql_test.php'
)
$ErrorActionPreference = 'Stop'
$journalWorkspace = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot '../../../..')).Path
$journalScratch = Join-Path $journalWorkspace 'scratch'
$journalFixture = Join-Path $journalScratch ('jurnal-all-db-' + [guid]::NewGuid().ToString('N'))
$journalPassword = [guid]::NewGuid().ToString('N')
$journalOldPort = $env:JOURNAL_TEST_PORT
$journalOldPassword = $env:JOURNAL_TEST_PASSWORD
$journalServer = $null
$journalExit = 1
try {
    if (Test-Path -LiteralPath $journalFixture) { throw 'Fixture directory already exists.' }
    & (Join-Path $MysqlBin 'mysql_install_db.exe') "--datadir=$journalFixture" "--port=$Port" "--password=$journalPassword" --silent
    if ($LASTEXITCODE -ne 0) { throw 'Disposable MariaDB initialization failed.' }
    $journalBase = Split-Path -Parent $MysqlBin
    $journalServer = Start-Process -FilePath (Join-Path $MysqlBin 'mysqld.exe') -ArgumentList @('--no-defaults',"--basedir=$journalBase","--datadir=$journalFixture",'--bind-address=127.0.0.1',"--port=$Port",'--console') -WindowStyle Hidden -PassThru -RedirectStandardOutput "$journalFixture\stdout.log" -RedirectStandardError "$journalFixture\stderr.log"
    $journalReady = $false
    for ($journalAttempt = 0; $journalAttempt -lt 40; $journalAttempt++) {
        & (Join-Path $MysqlBin 'mysqladmin.exe') --no-defaults --protocol=tcp --host=127.0.0.1 "--port=$Port" --user=root "--password=$journalPassword" ping --silent 2>$null >$null
        if ($LASTEXITCODE -eq 0) { $journalReady = $true; break }
        Start-Sleep -Milliseconds 100
    }
    if (-not $journalReady) { throw 'Disposable MariaDB is not reachable; check localhost socket permissions.' }
    $env:JOURNAL_TEST_PORT = [string]$Port
    $env:JOURNAL_TEST_PASSWORD = $journalPassword
    & $PhpPath (Join-Path $PSScriptRoot $TestScript)
    $journalExit = $LASTEXITCODE
} finally {
    if ($journalServer) {
        if (Get-Process -Id $journalServer.Id -ErrorAction SilentlyContinue) {
            & (Join-Path $MysqlBin 'mysqladmin.exe') --no-defaults --protocol=tcp --host=127.0.0.1 "--port=$Port" --user=root "--password=$journalPassword" shutdown 2>$null >$null
            if (Get-Process -Id $journalServer.Id -ErrorAction SilentlyContinue) { Stop-Process -Id $journalServer.Id }
        }
    }
    if ($journalServer) {
        if (-not $journalServer.WaitForExit(5000)) { throw 'Disposable MariaDB process did not exit after shutdown.' }
        $journalServer.WaitForExit()
        $journalServer.Dispose()
    }
    $env:JOURNAL_TEST_PORT = $journalOldPort
    $env:JOURNAL_TEST_PASSWORD = $journalOldPassword
    if (Test-Path -LiteralPath $journalFixture) {
        $journalResolved = (Resolve-Path -LiteralPath $journalFixture).Path
        $journalExpectedParent = [IO.Path]::GetFullPath($journalScratch)
        if ((Split-Path -Parent $journalResolved) -ne $journalExpectedParent -or (Split-Path -Leaf $journalResolved) -notmatch '^jurnal-all-db-[a-f0-9]{32}$') { throw 'Refusing cleanup outside the expected fixture directory.' }
        Remove-Item -LiteralPath $journalResolved -Recurse -Force
    }
}
exit $journalExit
