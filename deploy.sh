#!/bin/bash
# ═══════════════════════════════════════════════════════
# D2C Business OS — Deploy to Hostinger
# ═══════════════════════════════════════════════════════
# Server: Hostinger Shared Hosting
# SSH:    ssh -p 65002 u556611716@147.93.101.148
# Path:   ~/domains/mediumaquamarine-jay-552970.hostingersite.com/ikhlas-app
# ═══════════════════════════════════════════════════════

set -e

REMOTE_USER="u556611716"
REMOTE_HOST="147.93.101.148"
REMOTE_PORT="65002"
REMOTE_PATH="domains/mediumaquamarine-jay-552970.hostingersite.com/ikhlas-app"
SSH_CMD="ssh -p ${REMOTE_PORT} ${REMOTE_USER}@${REMOTE_HOST}"

echo "═══════════════════════════════════════════════════"
echo "  D2C Business OS — Deploying to Hostinger"
echo "═══════════════════════════════════════════════════"

# ── Step 1: Local Git Push ────────────────────────────
echo ""
echo "📦 Step 1: Committing and pushing changes…"
git add -A
git commit -m "Phase 1: Sellable MVP – Admin Sidebar, Order Flow, Storefront UI, Settings, Dashboard, Reviews, CRM" 2>/dev/null || echo "  (nothing new to commit)"
git push origin main 2>/dev/null || git push origin master 2>/dev/null || echo "  ⚠️ Push failed – check remote"

# ── Step 2: Remote Pull + Optimize ───────────────────
echo ""
echo "🚀 Step 2: Pulling on server and optimizing…"
${SSH_CMD} -t "cd ${REMOTE_PATH} && \
    git pull origin main 2>/dev/null || git pull origin master 2>/dev/null && \
    php artisan migrate --force 2>/dev/null || true && \
    php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan view:cache && \
    echo '' && \
    echo '✅ Deployment complete!'"

echo ""
echo "═══════════════════════════════════════════════════"
echo "  ✅ Done! Check your live site."
echo "═══════════════════════════════════════════════════"
