#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ -d "storage" ]; then
    mkdir -p storage/app/private \
             storage/app/public \
             storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/testing \
             storage/framework/views \
             storage/logs \
             bootstrap/cache || true
    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
fi

exec "$@"
