#!/bin/bash
set -e

APP_DIR=/var/www/html

if [ -d "$APP_DIR/storage" ]; then
    mkdir -p "$APP_DIR/storage/app/livewire-tmp"
    chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
    chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
fi

# Self-heal dependencies. vendor/ (Composer) and node_modules/ (npm — `sharp`
# powers the catch OG cards) normally persist on the mounted volume, but a
# fresh checkout or a moved/rebuilt volume can lose them. Installed under a
# flock so the app/reverb/queue/dusk containers — which share this image —
# don't all install at once on first boot; whoever waits re-checks and skips.
if { [ -f "$APP_DIR/composer.json" ] && [ ! -f "$APP_DIR/vendor/autoload.php" ]; } \
   || { [ -f "$APP_DIR/package.json" ] && [ ! -d "$APP_DIR/node_modules/sharp" ]; }; then
    (
        flock 9

        if [ -f "$APP_DIR/composer.json" ] && [ ! -f "$APP_DIR/vendor/autoload.php" ]; then
            COMPOSER_ALLOW_SUPERUSER=1 composer install \
                --working-dir="$APP_DIR" --no-interaction --optimize-autoloader
        fi

        if [ -f "$APP_DIR/package.json" ] && [ ! -d "$APP_DIR/node_modules/sharp" ]; then
            npm install --prefix "$APP_DIR" --no-audit --no-fund
        fi
    ) 9>"$APP_DIR/storage/app/.deps-install.lock" || true
fi

exec "$@"
