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

# ── Step 2: Remote Deploy ─────────────────────────────────────
echo "🌐 [SERVER] Deploying..."

MIGRATE_FLAG="$RUN_MIGRATE"

${SSH_CMD} -t "cd ~/${APP_DIR} && \
    echo '📥 Pulling latest code...' && \
    git pull origin ${BRANCH} && \
    echo '📦 Syncing composer...' && \
    composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3 && \
    echo '🗄  Running migrations...' && \
    php artisan migrate --force && \
    echo '📂 Syncing public assets...' && \
    rsync -a --delete \
        --exclude='storage' \
        --exclude='.htaccess' \
        --exclude='index.php' \
        --exclude='.user.ini' \
        public/ ~/${PUBLIC_HTML}/ && \
    cp -n public/.htaccess ~/${PUBLIC_HTML}/.htaccess 2>/dev/null || true && \
    echo '🔄 Rebuilding caches...' && \
    php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache 2>/dev/null || true && \
    php artisan queue:restart 2>/dev/null || true && \
    chmod -R 755 storage bootstrap/cache && \
    echo '🧹 Clearing OPcache...' && \
    echo '<?php opcache_reset(); echo \"cleared\"; @unlink(__FILE__);' > ~/${PUBLIC_HTML}/_op.php && \
    curl -skL ${SITE_URL}/_op.php 2>/dev/null && \
    rm -f ~/${PUBLIC_HTML}/_op.php 2>/dev/null || true && \
    echo '' && \
    echo '✅ All done!'"

echo ""
echo "═══════════════════════════════════════════════════"
echo "  ✅ Deploy complete! Check your live site."
echo "═══════════════════════════════════════════════════"
echo ""
