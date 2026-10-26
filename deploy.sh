#!/usr/bin/env bash
# Updates the live site from GitHub. Run on the server as root:
#   bash /var/www/havakaladonya/deploy.sh
# GitHub Actions runs it automatically after every push to main (see .github/workflows/deploy.yml);
# the deploy SSH key is limited to this script in /root/.ssh/authorized_keys.
set -euo pipefail

APP_DIR=/var/www/havakaladonya
cd "$APP_DIR"
export COMPOSER_ALLOW_SUPERUSER=1

# Only one deploy at a time.
exec 9>/tmp/havakaladonya-deploy.lock
flock -n 9 || { echo "Another deploy is running."; exit 1; }

before=$(git rev-parse HEAD)
git fetch --quiet origin main
git reset --hard --quiet origin/main
after=$(git rev-parse HEAD)
echo "Deploying ${before:0:7} → ${after:0:7}"

changed() { [ "$before" != "$after" ] && git diff --name-only "$before" "$after" | grep -qE "$1"; }

if changed '^composer\.(json|lock)$' || [ ! -d vendor ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --quiet
fi

php artisan down --retry=15 || true
trap 'php artisan up || true' EXIT

php artisan migrate --force

if changed '^package(-lock)?\.json$' || [ ! -d node_modules ]; then
    npm ci --no-audit --no-fund --silent
fi
if [ "$before" = "$after" ] || changed '^(resources/(js|css)|vite\.config|package|tsconfig)'; then
    npm run build:ssr --silent
fi

php artisan optimize:clear >/dev/null
php artisan optimize >/dev/null
php artisan filament:optimize >/dev/null
chown -R www-data:www-data storage bootstrap/cache

php artisan up
trap - EXIT
supervisorctl restart all >/dev/null
sleep 3
supervisorctl status
echo "Done: $(git log --oneline -1)"
