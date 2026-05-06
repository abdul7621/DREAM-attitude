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
#    bash deploy.sh              → Normal deploy (pull + cache)
#    bash deploy.sh --migrate    → Deploy + run migrations
#
# ═══════════════════════════════════════════════════════════════

set -e

# ── Safety: verify correct repo before deploying ──────────────
EXPECTED_REPO="DREAM-attitude"
ACTUAL_REPO=$(git remote get-url origin 2>/dev/null || echo "unknown")
if [[ "$ACTUAL_REPO" != *"$EXPECTED_REPO"* ]]; then
    echo "❌ WRONG REPO! Expected $EXPECTED_REPO but got: $ACTUAL_REPO"
    echo "   You may be running this deploy script from the wrong project folder."
    exit 1
fi

# ── Config ────────────────────────────────────────────────────
REMOTE_USER="u750823523"
REMOTE_HOST="147.93.17.66"
REMOTE_PORT="65002"
APP_DIR="domains/dreamattitude.al-mhaf.com/dream-app"
BRANCH="main"
SSH_CMD="ssh -p ${REMOTE_PORT} ${REMOTE_USER}@${REMOTE_HOST}"

RUN_MIGRATE=false
if [ "$1" = "--migrate" ]; then
    RUN_MIGRATE=true
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

# ── Step 2-4: Remote Execution ────────────────────────────────
echo "🌐 [SERVER] Executing remote deployment commands..."

if [ "$RUN_MIGRATE" = true ]; then
    MIGRATE_CMD="php artisan migrate --force && "
else
    MIGRATE_CMD=""
fi

PUBLIC_HTML="domains/dreamattitude.al-mhaf.com/public_html"

${SSH_CMD} -t "cd ${APP_DIR} && \
    echo '📥 Pulling latest code...' && \
    git pull origin ${BRANCH} && \
    echo '🗄  Running migrations (if any)...' && \
    ${MIGRATE_CMD} \
    echo '📂 Syncing public assets to public_html...' && \
    rsync -av --delete \
        --exclude='storage' \
        --exclude='.htaccess' \
        --exclude='index.php' \
        public/ ~/${PUBLIC_HTML}/ && \
    cp -n public/.htaccess ~/${PUBLIC_HTML}/.htaccess 2>/dev/null || true && \
    echo '🔄 Rebuilding caches...' && \
    php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache && \
    php artisan queue:restart && \
    chmod -R 755 storage bootstrap/cache && \
    echo '✅ All remote tasks completed successfully!'"
    
echo ""

echo "═══════════════════════════════════════════════════"
echo "  ✅ Deploy complete! Check your live site."
echo "═══════════════════════════════════════════════════"
echo ""
