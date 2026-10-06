# ═══════════════════════════════════════════════════════════════
#  D2C Business OS - PowerShell Deploy Script [DREAM ATTITUDE]
# ═══════════════════════════════════════════════════════════════
param (
    [switch]$Migrate
)

$ErrorActionPreference = "Stop"

# Safety Check
$ExpectedRepo = "DREAM-attitude"
$ActualRepo = git remote get-url origin 2>$null
if ($ActualRepo -notmatch $ExpectedRepo) {
    Write-Host "Wrong repo! Expected $ExpectedRepo but got: $ActualRepo" -ForegroundColor Red
    exit 1
}

# Config
$RemoteUser = "u750823523"
$RemoteHost = "147.93.17.66"
$RemotePort = "65002"
$AppDir = "domains/dreamattitude.al-mhaf.com/dream-app"
$PublicHtml = "domains/dreamattitude.al-mhaf.com/public_html"
$Branch = "main"

Write-Host ""
Write-Host "===================================================" -ForegroundColor Cyan
Write-Host "  Deploying DREAM ATTITUDE..." -ForegroundColor Cyan
Write-Host "===================================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Local Stage, Commit & Push
Write-Host "[LOCAL] Staging and committing changes..." -ForegroundColor Yellow
git add -A

$status = git status --porcelain
if ($status) {
    $commitMsg = Read-Host "Commit message (press enter for default)"
    if ([string]::IsNullOrWhiteSpace($commitMsg)) {
        $commitMsg = "deploy update: product images, volume badges, and MRP"
    }
    git commit -m "$commitMsg"
} else {
    Write-Host "Nothing new to commit." -ForegroundColor Green
}

Write-Host "[LOCAL] Pushing to origin/$Branch..." -ForegroundColor Yellow
git push origin $Branch
Write-Host "Push complete." -ForegroundColor Green
Write-Host ""

# Step 2: Remote Server Execution via SSH
Write-Host "[SERVER] Connecting to Hostinger ($RemoteHost)..." -ForegroundColor Yellow

$migrateCmd = ""
if ($Migrate) {
    $migrateCmd = "php artisan migrate --force && "
}

$remoteCmd = "cd " + $AppDir + " && git pull origin " + $Branch + " && " + $migrateCmd + "rsync -av --delete --exclude=storage --exclude=.htaccess --exclude=index.php public/ ~/" + $PublicHtml + "/ && cp -n public/.htaccess ~/" + $PublicHtml + "/.htaccess 2>/dev/null || true && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache && php artisan queue:restart && chmod -R 755 storage bootstrap/cache && echo All remote tasks completed successfully!"

ssh -o StrictHostKeyChecking=no -p $RemotePort -t "$RemoteUser@$RemoteHost" $remoteCmd

Write-Host ""
Write-Host "===================================================" -ForegroundColor Green
Write-Host "  Deploy complete! Check your live site." -ForegroundColor Green
Write-Host "===================================================" -ForegroundColor Green
Write-Host ""
