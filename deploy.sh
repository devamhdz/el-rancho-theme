#!/bin/bash
# deploy.sh — El Rancho Theme → Synology NAS
# Uso: ./deploy.sh
# Se ejecuta automáticamente con: git push (via .git/hooks/post-push)

set -e

SSH_USER="ahernandez"
SSH_HOST="192.168.0.6"
SSH_PORT="22"
REMOTE_PATH="/volume1/web/wordpress/wp-content/themes/el-rancho-theme"
LOCAL_PATH="$(cd "$(dirname "$0")" && pwd)/"

echo "🚀 Desplegando el-rancho-theme → $SSH_HOST..."

rsync -avz --delete \
  --exclude='.git' \
  --exclude='.github' \
  --exclude='deploy.sh' \
  --exclude='*.md' \
  --exclude='node_modules' \
  --exclude='.DS_Store' \
  -e "ssh -p $SSH_PORT" \
  "$LOCAL_PATH" \
  "$SSH_USER@$SSH_HOST:$REMOTE_PATH"

echo "✅ Deploy completado."
