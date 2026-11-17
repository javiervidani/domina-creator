#!/bin/bash
# Plesk Git, Deployment settings, Post deployment actions. Paste this one line there:
#   bash deploy/post-deploy.sh >> storage/logs/deploy.log 2>&1
# It runs after every deploy of `main`. Stops at the first failure so a half-migrated site is easy to spot in the log.
set -euo pipefail

cd "$(dirname "$0")/.."
PHP=/opt/plesk/php/8.3/bin/php
COMPOSER=/usr/lib/plesk-9.0/composer.phar

echo "== deploy $(date -u +%F\ %T) =="

# Plesk's deploy does not run Composer, so it is done here.
export COMPOSER_HOME="${COMPOSER_HOME:-$PWD/storage/composer-home}"
$PHP $COMPOSER install --no-dev --optimize-autoloader --no-interaction

# Central tables and the provision function need the admin login; creator schemas run as app_user.
$PHP artisan migrate --database=pgsql_admin --force
$PHP artisan creators:migrate

$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan queue:restart
echo "== done =="
