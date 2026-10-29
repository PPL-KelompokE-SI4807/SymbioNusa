#!/usr/bin/env bash
set -e

# Load environment path untuk Node & NPM (SSH Non-Interactive Session)
export PATH=$PATH:/usr/bin:/usr/local/bin:~/.nvm/versions/node/$(ls ~/.nvm/versions/node 2>/dev/null | tail -n 1)/bin
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

echo "🚀 Memulai Deployment SymbioNusa Staging..."
# Dijalankan DI SERVER (OCI VM). Lokasi: /var/www/symbionusa/scripts/deploy.sh
set -euo pipefail

APP_DIR=/var/www/symbionusa
cd "$APP_DIR"

php artisan down --retry=30 || true

git fetch origin staging
git reset --hard origin/staging

composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

php artisan migrate --force          # JANGAN migrate:fresh di server
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

php artisan up

# Pemeriksaan kesehatan (rute /up bawaan Laravel 11)
curl --fail --silent --show-error http://127.0.0.1:20129/up > /dev/null
echo "Deploy selesai: $(date -u +%FT%TZ)"
