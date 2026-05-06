#!/bin/bash
# ═══════════════════════════════════════════════════════════════
#  D2C Business OS — Deploy Script  [DREAM ATTITUDE]
# ═══════════════════════════════════════════════════════════════
#  Remote:   github.com/abdul7621/DREAM-attitude.git
#  Branch:   main
#  Server:   Hostinger Shared Hosting
#  SSH:      ssh -p 65002 u750823523@147.93.17.66
#  App Path: ~/domains/dreamattitude.al-mhaf.com/dream-app
# ═══════════════════════════════════════════════════════════════
#
#  Features:
#    ✅ Auto health-check after deploy
#    ✅ Auto-rollback if health-check fails
#    ✅ OPcache auto-clear
#    ✅ PHP error logging setup
#    ✅ Composer autoloader sync
#
#  Usage:
#    bash deploy.sh              → Normal deploy
#    bash deploy.sh --no-migrate → Skip migrations
#
# ═══════════════════════════════════════════════════════════════

set -e

# ── Safety: verify correct repo ───────────────────────────────
EXPECTED_REPO="DREAM-attitude"
ACTUAL_REPO=$(git remote get-url origin 2>/dev/null || echo "unknown")
if [[ "$ACTUAL_REPO" != *"$EXPECTED_REPO"* ]]; then
    echo "❌ WRONG REPO! Expected $EXPECTED_REPO but got: $ACTUAL_REPO"
    exit 1
fi

# ── Config ────────────────────────────────────────────────────
REMOTE_USER="u750823523"
REMOTE_HOST="147.93.17.66"
REMOTE_PORT="65002"
APP_DIR="domains/dreamattitude.al-mhaf.com/dream-app"
PUBLIC_HTML="domains/dreamattitude.al-mhaf.com/public_html"
BRANCH="main"
SITE_URL="https://dreamattitude.com"
SSH_CMD="ssh -p ${REMOTE_PORT} ${REMOTE_USER}@${REMOTE_HOST}"

RUN_MIGRATE=true
if [ "$1" = "--no-migrate" ]; then
    RUN_MIGRATE=false
fi

echo ""
echo "═══════════════════════════════════════════════════"
echo "  🚀 D2C Business OS — Deploy"
echo "═══════════════════════════════════════════════════"
echo ""

# ── Step 1: Local — stage, commit, push ───────────────────────
echo "📦 [LOCAL] Staging and committing…"
git add -A
git diff --cached --quiet && echo "  ✓ Nothing new to commit" || {
    read -p "  Commit message: " MSG
    git commit -m "${MSG:-deploy update}"
}

echo "📤 [LOCAL] Pushing to origin/${BRANCH}…"
git push origin ${BRANCH}
echo "  ✓ Push complete"
echo ""

# ── Step 2: Remote Deploy + Health Check ──────────────────────
echo "🌐 [SERVER] Deploying..."

MIGRATE_FLAG="$RUN_MIGRATE"

${SSH_CMD} bash <<REMOTE_SCRIPT
set -e
cd ~/${APP_DIR} || { echo '❌ Cannot cd into app dir'; exit 1; }

# Save current commit for rollback
PREV_COMMIT=\$(git rev-parse HEAD)
echo "📍 Current commit: \$PREV_COMMIT"

# Pull latest code
echo '📥 Pulling latest code...'
git pull origin ${BRANCH}
NEW_COMMIT=\$(git rev-parse HEAD)
echo "📍 New commit: \$NEW_COMMIT"

# Composer autoloader sync
echo '📦 Syncing composer autoloader...'
composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3

# Migrations
if [ "${MIGRATE_FLAG}" = "true" ]; then
    echo '🗄  Running migrations...'
    php artisan migrate --force
fi

# Sync public assets
echo '📂 Syncing public assets...'
rsync -a --delete \
    --exclude='storage' \
    --exclude='.htaccess' \
    --exclude='index.php' \
    --exclude='.user.ini' \
    public/ ~/${PUBLIC_HTML}/
cp -n public/.htaccess ~/${PUBLIC_HTML}/.htaccess 2>/dev/null || true

# Setup PHP error logging (catches fatal errors outside Laravel)
cat > ~/${PUBLIC_HTML}/.user.ini << 'PHPINI'
display_errors = Off
log_errors = On
error_reporting = E_ALL
error_log = /home/u750823523/domains/dreamattitude.al-mhaf.com/dream-app/storage/logs/php-errors.log
PHPINI

# Rebuild caches
echo '🔄 Rebuilding caches...'
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true
php artisan queue:restart 2>/dev/null || true
chmod -R 755 storage bootstrap/cache

# Clear OPcache via web request
echo '🧹 Clearing OPcache...'
echo '<?php opcache_reset(); echo "cleared"; @unlink(__FILE__);' > ~/${PUBLIC_HTML}/_op_reset.php
curl -skL ${SITE_URL}/_op_reset.php 2>/dev/null || true
rm -f ~/${PUBLIC_HTML}/_op_reset.php 2>/dev/null || true
echo ""

# ── Health Check ──────────────────────────────────────────
echo '🏥 Running health check...'
sleep 2
HTTP_CODE=\$(curl -skL -o /tmp/health_response.json -w "%{http_code}" ${SITE_URL}/health.php 2>/dev/null || echo "000")

if [ "\$HTTP_CODE" = "200" ]; then
    echo "✅ Health check PASSED (HTTP \$HTTP_CODE)"
    cat /tmp/health_response.json 2>/dev/null || true
    echo ""
else
    echo "❌ Health check FAILED (HTTP \$HTTP_CODE)"
    cat /tmp/health_response.json 2>/dev/null || true
    echo ""
    echo "🔄 AUTO-ROLLBACK to \$PREV_COMMIT ..."
    git reset --hard \$PREV_COMMIT
    composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -2
    php artisan optimize:clear 2>/dev/null
    php artisan config:cache 2>/dev/null
    php artisan route:cache 2>/dev/null
    php artisan view:cache 2>/dev/null || true
    chmod -R 755 storage bootstrap/cache
    echo '<?php opcache_reset(); echo "cleared"; @unlink(__FILE__);' > ~/${PUBLIC_HTML}/_op_reset.php
    curl -skL ${SITE_URL}/_op_reset.php 2>/dev/null || true
    rm -f ~/${PUBLIC_HTML}/_op_reset.php 2>/dev/null || true
    echo "⚠️  ROLLED BACK to \$PREV_COMMIT — site restored."
    exit 1
fi

echo '✅ All remote tasks completed successfully!'
REMOTE_SCRIPT

echo ""
echo "═══════════════════════════════════════════════════"
echo "  ✅ Deploy complete! Check your live site."
echo "═══════════════════════════════════════════════════"
echo ""
