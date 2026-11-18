<#
.SYNOPSIS
    Deploy Invoicy by RedMaple changes to Staging Environment.
.DESCRIPTION
    Safely packages and deploys code updates to the staging server (staging.redmapleagency.com)
    for QA verification and client review without touching production.
#>
param(
    [string]$StagingHost = "82.29.185.64",
    [string]$StagingUser = "u858731124.redmapleagency.com",
    [string]$StagingPass = '786@&^*(?1BmA238',
    [string]$StagingCore = "/home/u858731124/domains/redmapleagency.com/redmaple_stage"
)

$ErrorActionPreference = "Stop"
$repo = $PSScriptRoot
$pkgDir = Join-Path $repo "staging_build_tmp"
$zipPath = Join-Path $repo "invoicy_staging_build.zip"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   Invoicy Staging Pipeline Deployment   " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

if (Test-Path $pkgDir) { Remove-Item -Recurse -Force $pkgDir }
if (Test-Path $zipPath) { Remove-Item -Force $zipPath }
New-Item -ItemType Directory -Path $pkgDir | Out-Null

Write-Host "[1/4] Collecting tracked source files from git..."
$trackedFiles = git ls-files
foreach ($f in $trackedFiles) {
    # Skip environment sensitive files and repo docs
    if ($f -match '^\.env' -or $f -match '^\.git' -or $f -match '\.zip$') { continue }
    
    $src = Join-Path $repo $f
    $dst = Join-Path $pkgDir $f
    $dstDir = Split-Path -Parent $dst
    if (!(Test-Path $dstDir)) { New-Item -ItemType Directory -Path $dstDir -Force | Out-Null }
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination $dst -Force
    }
}

Write-Host "[2/4] Creating staging deployment archive..."
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($pkgDir, $zipPath)
Remove-Item -Recurse -Force $pkgDir

Write-Host "[3/4] Uploading staging payload to remote host..."
$ftpUri = "ftp://$StagingHost/invoicy_staging_build.zip"
$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($StagingUser, $StagingPass)
$webclient.UploadFile($ftpUri, $zipPath)

Write-Host "[4/4] Deploying to staging core and clearing caches..."
$bridgeCode = @"
<?php
header('Content-Type: text/plain; charset=utf-8');
`$stageCore = '$StagingCore';
`$zipPath = __DIR__ . '/invoicy_staging_build.zip';

if (!is_dir(`$stageCore)) {
    mkdir(`$stageCore, 0755, true);
}

`$zip = new ZipArchive();
if (`$zip->open(`$zipPath) === true) {
    `$zip->extractTo(`$stageCore);
    `$zip->close();
    @unlink(`$zipPath);
    echo "Staging archive unpacked successfully.\n";
} else {
    echo "FAILED to open staging archive.\n";
}

if (file_exists(`$stageCore . '/vendor/autoload.php')) {
    require `$stageCore . '/vendor/autoload.php';
    `$app = require_once `$stageCore . '/bootstrap/app.php';
    `$kernel = `$app->make(Illuminate\Contracts\Http\Kernel::class);
    `$kernel->handle(Illuminate\Http\Request::capture());
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo "Staging caches cleared.\n";
}
echo "Staging Deployment Completed.\n";
"@

$bridgeLocal = Join-Path $repo "deploy_stage_bridge.php"
[System.IO.File]::WriteAllText($bridgeLocal, $bridgeCode, [System.Text.Encoding]::UTF8)
$webclient.UploadFile("ftp://$StagingHost/deploy_stage_bridge.php", $bridgeLocal)
Remove-Item -Force $bridgeLocal
Remove-Item -Force $zipPath

Write-Host "`nStaging pipeline executed successfully!" -ForegroundColor Green
Write-Host "Verify your enhancements at staging subdomain before running promote_to_production.ps1." -ForegroundColor Yellow
