#!/usr/bin/env bash
set -euo pipefail

echo "==> Deploying Astronotify..."

TARGET_DIR="${DEPLOY_PATH:-astronotify.org}"
REMOTE_NAME="${GIT_REMOTE:-origin}"
BRANCH_NAME="${GIT_BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
if [ "${PHP_BIN}" = "php" ]; then
  if command -v ea-php84 &>/dev/null; then
    PHP_BIN="$(command -v ea-php84)"
  elif [ -x /usr/local/bin/ea-php84 ]; then
    PHP_BIN="/usr/local/bin/ea-php84"
  elif command -v php8.4 &>/dev/null; then
    PHP_BIN="$(command -v php8.4)"
  elif [ -x /usr/bin/php8.4 ]; then
    PHP_BIN="/usr/bin/php8.4"
  fi
fi
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

echo "==> Deploying Astronotify in directory: ${TARGET_DIR} using PHP: ${PHP_BIN}..."

# Navigate to target directory if not already inside the repository root
if [ ! -f artisan ] && [ -d "${TARGET_DIR}" ]; then
  cd "${TARGET_DIR}"
fi

# 1. Maintenance mode
if [ -f artisan ]; then
  "${PHP_BIN}" artisan down --retry=15 2>/dev/null || true
fi

# 2. Fetch and hard-reset to latest main
echo "==> Pulling latest changes from ${REMOTE_NAME}/${BRANCH_NAME}..."
git fetch "${REMOTE_NAME}" "${BRANCH_NAME}"
git reset --hard "${REMOTE_NAME}/${BRANCH_NAME}"

# 3. Production PHP dependencies
echo "==> Installing Composer dependencies..."
if command -v "${COMPOSER_BIN}" &>/dev/null; then
  "${PHP_BIN}" "$(command -v "${COMPOSER_BIN}")" install --no-dev --no-interaction --prefer-dist --optimize-autoloader
elif [ -f composer.phar ]; then
  "${PHP_BIN}" composer.phar install --no-dev --no-interaction --prefer-dist --optimize-autoloader
else
  ${COMPOSER_BIN} install --no-dev --no-interaction --prefer-dist --optimize-autoloader
fi

# 4. Database migrations
echo "==> Running database migrations..."
"${PHP_BIN}" artisan migrate --force

# 5. Public storage link
"${PHP_BIN}" artisan storage:link 2>/dev/null || true

# 6. Production caches
echo "==> Caching routes, configuration, and views..."
"${PHP_BIN}" artisan config:cache
"${PHP_BIN}" artisan route:cache
"${PHP_BIN}" artisan view:cache
"${PHP_BIN}" artisan event:cache

# 7. Restart queue workers if applicable
"${PHP_BIN}" artisan queue:restart 2>/dev/null || true

# 8. Disable maintenance mode
"${PHP_BIN}" artisan up

echo "==> Deployment completed successfully!"
