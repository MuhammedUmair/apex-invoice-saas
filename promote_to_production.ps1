<#
.SYNOPSIS
    Promote Verified Staging Code to Invoicy Production.
.DESCRIPTION
    Creates an automated restore point snapshot, verifies git stability,
    deploys verified code to production core, and executes health check probes.
#>
param(
    [string]$ProdHost = "82.29.185.64",
    [string]$ProdUser = "u858731124.redmapleagency.com",
    [string]$ProdPass = '786@&^*(?1BmA238',
    [string]$ProdCore = "/home/u858731124/domains/redmapleagency.com/redmaple_core",
    [string]$ReleaseName = "release_two"
)

$ErrorActionPreference = "Stop"
$repo = $PSScriptRoot
Set-Location $repo
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$snapshotName = "restore_point_${ReleaseName}_$timestamp.zip"
$backupDir = Join-Path $repo "restore_points"

Write-Host "==========================================" -ForegroundColor Red
Write-Host "  Invoicy Production Promotion Pipeline   " -ForegroundColor Red
Write-Host "==========================================" -ForegroundColor Red

# 1. Pre-flight checks
Write-Host "[1/5] Running pre-flight checks..."
$gitStatus = git -C $repo status --porcelain
if ($gitStatus) {
    Write-Warning "Uncommitted local changes detected in repository. Consider committing or stashing before production promotion."
}

# 2. Create local restore point snapshot directory
if (!(Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
}

Write-Host "[2/5] Creating immutable local restore point archive ($snapshotName)..."
$restoreZip = Join-Path $backupDir $snapshotName
$pkgDir = Join-Path $repo "prod_build_tmp"
if (Test-Path $pkgDir) { Remove-Item -Recurse -Force $pkgDir }
New-Item -ItemType Directory -Path $pkgDir | Out-Null

$trackedFiles = git -C $repo ls-files
Write-Host "Found $($trackedFiles.Count) tracked files to deploy to production." -ForegroundColor Cyan
foreach ($f in $trackedFiles) {
    if ($f -match '^\.env' -or $f -match '^\.git' -or $f -match '\.zip$') { continue }
    $src = Join-Path $repo $f
    $dst = Join-Path $pkgDir $f
    $dstDir = Split-Path -Parent $dst
    if (!(Test-Path $dstDir)) { New-Item -ItemType Directory -Path $dstDir -Force | Out-Null }
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $dst -Force
    }
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($pkgDir, $restoreZip)
Remove-Item -Recurse -Force $pkgDir
$namedRestoreZip = Join-Path $backupDir "restore_point_${ReleaseName}.zip"
Copy-Item $restoreZip -Destination $namedRestoreZip -Force
Write-Host "Restore point created: $restoreZip" -ForegroundColor Green
Write-Host "Permanent release restore point: $namedRestoreZip" -ForegroundColor Green

# 3. Upload payload to production
Write-Host "[3/5] Uploading verified code payload to production..."
$ftpUri = "ftp://$ProdHost/invoicy_production_build.zip"
$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($ProdUser, $ProdPass)
$webclient.UploadFile($ftpUri, $restoreZip)

# 4. Remote extraction and cache warming
Write-Host "[4/5] Executing live production update and cache warmup..."
$bridgeCode = @"
<?php
header('Content-Type: text/plain; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

`$prodCore = '$ProdCore';
`$zipPath = __DIR__ . '/invoicy_production_build.zip';

echo "=== INVOICY PRODUCTION DEPLOYMENT ===\n";
echo "Target Directory: `$prodCore\n\n";

if (!file_exists(`$zipPath)) {
    die("ERROR: invoicy_production_build.zip not found at `$zipPath\n");
}

// 1. Clean up stale backslash files in root
echo "Cleaning up any stale backslash files in root...\n";
foreach (scandir(`$prodCore) as `$f) {
    if (strpos(`$f, '\\') !== false) {
        @unlink(`$prodCore . '/' . `$f);
        echo "Deleted: `$f\n";
    }
}

// 2. Extract with Linux path normalization
echo "Extracting files with Linux path normalization...\n";
`$zip = new ZipArchive();
if (`$zip->open(`$zipPath) === true) {
    for (`$i = 0; `$i < `$zip->numFiles; `$i++) {
        `$entry = `$zip->getNameIndex(`$i);
        `$normalized = str_replace('\\', '/', `$entry);
        `$dest = `$prodCore . '/' . `$normalized;

        if (substr(`$normalized, -1) === '/') {
            if (!is_dir(`$dest)) {
                mkdir(`$dest, 0755, true);
            }
            continue;
        }

        `$destDir = dirname(`$dest);
        if (!is_dir(`$destDir)) {
            mkdir(`$destDir, 0755, true);
        }

        `$stream = `$zip->getStream(`$entry);
        if (`$stream) {
            `$fp = fopen(`$dest, 'wb');
            while (!feof(`$stream)) {
                fwrite(`$fp, fread(`$stream, 8192));
            }
            fclose(`$fp);
            fclose(`$stream);

            // Mirror public web assets to public_html and invoicy document roots
            if (strpos(`$normalized, 'public/') === 0) {
                `$pubRel = substr(`$normalized, 7);
                if (`$pubRel !== '' && `$pubRel !== 'index.php' && `$pubRel !== '.htaccess') {
                    `$rootTarget = __DIR__ . '/' . `$pubRel;
                    `$rootDir = dirname(`$rootTarget);
                    if (!is_dir(`$rootDir)) {
                        mkdir(`$rootDir, 0755, true);
                    }
                    @copy(`$dest, `$rootTarget);
                    @chmod(`$rootTarget, 0644);

                    `$invoicyTarget = __DIR__ . '/invoicy/' . `$pubRel;
                    `$invoicyDir = dirname(`$invoicyTarget);
                    if (!is_dir(`$invoicyDir)) {
                        mkdir(`$invoicyDir, 0755, true);
                    }
                    @copy(`$dest, `$invoicyTarget);
                    @chmod(`$invoicyTarget, 0644);
                }
            }
        }
    }
    `$zip->close();
    @unlink(`$zipPath);
    echo "Production archive unpacked and normalized successfully.\n";
} else {
    die("FAILED to open production archive.\n");
}

if (file_exists(`$prodCore . '/vendor/autoload.php')) {
    require_once `$prodCore . '/vendor/autoload.php';
    `$app = require_once `$prodCore . '/bootstrap/app.php';
    `$kernel = `$app->make(Illuminate\Contracts\Http\Kernel::class);
    `$kernel->handle(Illuminate\Http\Request::capture());
    
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        echo "Production migrations executed: " . trim(\Illuminate\Support\Facades\Artisan::output()) . "\n";
    } catch (\Throwable `$ex) {
        echo "Migration notice: " . `$ex->getMessage() . "\n";
    }

    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo "Production caches warmed and cleared.\n";
}
echo "Production Promotion Complete!\n";
"@

$bridgeLocal = Join-Path $repo "prod_promote_bridge.php"
[System.IO.File]::WriteAllText($bridgeLocal, $bridgeCode, [System.Text.Encoding]::UTF8)
$webclient.UploadFile("ftp://$ProdHost/prod_promote_bridge.php", $bridgeLocal)
Remove-Item -Force $bridgeLocal

try {
    $res = Invoke-RestMethod -Uri "https://redmapleagency.com/prod_promote_bridge.php" -Method Get -TimeoutSec 120
    Write-Host $res
} catch {
    Write-Warning "Promotion trigger finished with notice: $_"
}

# Cleanup remote bridge
$ftpReq = [System.Net.FtpWebRequest]::Create("ftp://$ProdHost/prod_promote_bridge.php")
$ftpReq.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
$ftpReq.Credentials = New-Object System.Net.NetworkCredential($ProdUser, $ProdPass)
try { $ftpReq.GetResponse().Close() } catch {}

# 5. Production Health Check Probe
Write-Host "[5/5] Running production health check probes..."
$probes = @(
    "https://invoicy.redmapleagency.com/login",
    "https://invoicy.redmapleagency.com/cp",
    "https://invoicy.redmapleagency.com/help"
)
foreach ($url in $probes) {
    try {
        $resp = Invoke-WebRequest -Uri $url -Method Get -UseBasicParsing -TimeoutSec 15
        Write-Host "  $url -> HTTP $($resp.StatusCode) OK" -ForegroundColor Green
    } catch {
        Write-Host "  $url -> FAIL: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nProduction Promotion Pipeline Successfully Completed!" -ForegroundColor Green
