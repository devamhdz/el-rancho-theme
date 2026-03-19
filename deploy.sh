#!/bin/bash
# deploy.sh — El Rancho Theme → Synology NAS
# Uso: ./deploy.sh
# Se ejecuta automáticamente con: git push (via .git/hooks/post-push)

set -e

SSH_USER="ahernandez"
SSH_HOST="192.168.0.6"
SSH_PORT="22"
REMOTE_PATH="/volume1/web/wordpress/wp-content/themes/el-rancho-theme"
LOCAL_PATH="$(cd "$(dirname "$0")" && pwd)"

echo "🚀 Desplegando el-rancho-theme → $SSH_HOST..."

tar czf - \
  --exclude='./.git' \
  --exclude='./.github' \
  --exclude='./deploy.sh' \
  --exclude='./*.md' \
  --exclude='./node_modules' \
  --exclude='./app' \
  --exclude='./.DS_Store' \
  --exclude='./._*' \
  -C "$LOCAL_PATH" . \
| ssh -p "$SSH_PORT" -o StrictHostKeyChecking=no "$SSH_USER@$SSH_HOST" \
  "tar xzf - -C '$REMOTE_PATH' && find '$REMOTE_PATH' -name '._*' -delete && rm -rf '$REMOTE_PATH/.git'"

echo "✅ Deploy completado."
