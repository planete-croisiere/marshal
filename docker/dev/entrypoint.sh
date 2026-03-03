#!/bin/bash
set -euo pipefail

cd /app

if [ -f composer.json ]; then
    if [ ! -d vendor ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
        composer install --no-interaction --prefer-dist --optimize-autoloader
    fi
fi

if [ -f package.json ]; then
    if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
        npm install
    fi
fi

exec "$@"
