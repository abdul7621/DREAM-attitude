#!/bin/bash
# ═══════════════════════════════════════════════════════════════
#  D2C Business OS — Deploy Script
# ═══════════════════════════════════════════════════════════════
#  Remote:   github.com/abdul7621/Ikhlas-Fragrance.git
#  Branch:   main
#  Server:   Hostinger Shared Hosting
#  SSH:      ssh -p 65002 u556611716@147.93.101.148
#  App Path: ~/domains/mediumaquamarine-jay-552970.hostingersite.com/ikhlas-app
# ═══════════════════════════════════════════════════════════════
#
#  Usage:
#    bash deploy.sh              → Normal deploy (pull + cache)
#    bash deploy.sh --migrate    → Deploy + run migrations
#
# ═══════════════════════════════════════════════════════════════

set -e

# ── Config ────────────────────────────────────────────────────
REMOTE_USER="u556611716"
REMOTE_HOST="147.93.101.148"
REMOTE_PORT="65002"
APP_DIR="domains/mediumaquamarine-jay-552970.hostingersite.com/ikhlas-app"
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

# ── Step 2: Remote — pull ─────────────────────────────────────
echo "📥 [SERVER] Pulling latest code…"
${SSH_CMD} -t "cd ${APP_DIR} && git pull origin ${BRANCH}"
echo "  ✓ Pull complete"
echo ""

# ── Step 3: Remote — migrate (optional) ──────────────────────
if [ "$RUN_MIGRATE" = true ]; then
    echo "🗄  [SERVER] Running migrations…"
    ${SSH_CMD} -t "cd ${APP_DIR} && php artisan migrate --force"
    echo "  ✓ Migrations complete"
    echo ""
fi

# ── Step 4: Remote — clear + rebuild caches ───────────────────
echo "🔄 [SERVER] Clearing and rebuilding caches…"
${SSH_CMD} -t "cd ${APP_DIR} && php artisan optimize:clear && php artisan config:cache && php artisan view:cache"
echo "  ✓ Caches rebuilt"
echo ""

echo "═══════════════════════════════════════════════════"
echo "  ✅ Deploy complete! Check your live site."
echo "═══════════════════════════════════════════════════"
echo ""
