#!/usr/bin/env bash
# Bootstraps the Gaming application.
#
# On a fresh clone (no gaming/artisan):
#   1. Build Docker images
#   2. Create Laravel skeleton in /tmp inside container, then merge into gaming/
#      (cp -n preserves any pre-committed custom files)
#   3. Patch package.json scripts + routes/web.php (idempotent)
#   4. composer install
#   5. Generate APP_KEY, seed gaming/.env
#   6. Create gitignored storage runtime dirs
#   7. Bring up MySQL, wait for healthy
#   8. php artisan migrate
#   9. npm install + npm run build
#
# Re-runnable: every step is idempotent.
set -euo pipefail

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
    cp .env.example .env
fi

set -a
. <(grep -Ev '^(UID|GID|EUID|PPID|SHLVL|BASHPID|RANDOM|SECONDS|LINENO|HISTCMD|FUNCNAME|GROUPS|DIRSTACK|PIPESTATUS|BASH_[A-Z_]+|COMP_[A-Z_]+)=' .env || true)
set +a

DC="docker compose"

echo "==> Ensuring images are built"
$DC build

APP_RUN="$DC run --rm --no-deps app"
APP_RUN_DEPS="$DC run --rm app"

# ── Step 1: Create Laravel skeleton if missing ───────────────────────────────
if [ ! -f gaming/artisan ]; then
    echo "==> Creating Laravel project (skeleton into /tmp, then merge)"
    # Create in /tmp inside the container, then copy without overwriting
    # pre-committed custom files (controllers, migrations, resources/js/*, etc.)
    docker compose run --rm --no-deps --user root app sh -lc \
        "composer create-project laravel/laravel /tmp/laravel-base --no-interaction --prefer-dist \
         && cp -rn /tmp/laravel-base/. /var/www/html/ \
         && rm -f /var/www/html/vite.config.js \
         && chown -R app:app /var/www/html"
    echo "==> Laravel skeleton merged."
fi

# ── Step 2: Patch package.json scripts ───────────────────────────────────────
echo "==> Patching package.json scripts"
$APP_RUN node -e "
const fs = require('fs');
const pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'));
const custom = {
    dev: 'vite',
    build: 'tsc && vite build',
    typecheck: 'tsc --noEmit',
    lint: 'eslint resources/js --ext ts,tsx --report-unused-disable-directives --max-warnings 0',
};
let changed = false;
for (const [k, v] of Object.entries(custom)) {
    if (pkg.scripts[k] !== v) { pkg.scripts[k] = v; changed = true; }
}
if (changed) {
    fs.writeFileSync('package.json', JSON.stringify(pkg, null, 4) + '\n');
    console.log('package.json scripts updated');
} else {
    console.log('package.json scripts already up to date');
}
"

# ── Step 3: Patch routes/web.php — SPA catch-all (must be last) ──────────────
if [ -f gaming/routes/web.php ]; then
    if ! grep -q 'spa-catchall' gaming/routes/web.php 2>/dev/null; then
        echo "==> Adding SPA catch-all to routes/web.php"
        cat >> gaming/routes/web.php << 'PHP_EOF'

// SPA catch-all — must remain last
Route::get('/{any}', fn() => view('app'))->where('any', '.*')->name('spa-catchall');
PHP_EOF
    fi
fi

# ── Step 4: PHP dependencies ──────────────────────────────────────────────────
echo "==> Installing PHP dependencies (composer install)"
$APP_RUN composer install --no-interaction --no-progress --prefer-dist

# ── Step 5: APP_KEY + gaming/.env ─────────────────────────────────────────────
echo "==> Setting up gaming/.env"
if [ ! -f gaming/.env ]; then
    cp gaming/.env.example gaming/.env
    sed -i.bak \
        -e "s/^DB_HOST=.*/DB_HOST=db/" \
        -e "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-gaming}/" \
        -e "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-gaming}/" \
        -e "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-secret}/" \
        gaming/.env && rm -f gaming/.env.bak
fi
$APP_RUN sh -lc 'grep -q "^APP_KEY=base64:" .env || php artisan key:generate'

# ── Step 6: Storage dirs ──────────────────────────────────────────────────────
echo "==> Creating storage runtime dirs"
$APP_RUN sh -lc '
    mkdir -p \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache || true
    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
'
$APP_RUN php artisan storage:link 2>/dev/null || true

# ── Step 7: DB + migrations ───────────────────────────────────────────────────
echo "==> Bringing up DB"
$DC up -d db

echo "==> Waiting for MySQL..."
for i in $(seq 1 30); do
    if $DC exec -T db mysqladmin ping -h 127.0.0.1 -uroot -p"${DB_ROOT_PASSWORD:-root}" --silent >/dev/null 2>&1; then
        echo "    MySQL is ready."
        break
    fi
    sleep 2
done

echo "==> Running migrations"
$APP_RUN_DEPS php artisan migrate --force

# ── Step 8: Node dependencies + build ────────────────────────────────────────
echo "==> Installing Node dependencies"
$APP_RUN npm install \
    react react-dom react-router-dom \
    @vitejs/plugin-react \
    @tailwindcss/vite tailwindcss \
    typescript @types/react @types/react-dom \
    eslint

echo "==> Building front-end assets"
$APP_RUN npm run build

echo
echo "============================================================"
echo "  Gaming app is ready."
echo "  Run \`make up\`  → http://localhost:${APP_PORT:-8081}"
echo "  Run \`make dev\` → http://localhost:${APP_PORT:-8081} (HMR on :${VITE_PORT:-5173})"
echo "============================================================"
