#!/usr/bin/env bash
# harness/init.sh — idempotent environment boot. Safe to run blindfolded every session.
set -euo pipefail
HARNESS_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$HARNESS_DIR/../src"

# parallel worktrees: export COMPOSE_PROJECT_NAME=<proj>-<branch> before calling
export COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-app}"

docker compose up -d --wait pgsql redis mailpit
docker compose up -d app nginx

[ -f .env ] || { cp .env.example .env; echo "=> .env created from example — review values"; }

docker compose exec -T app bash -lc '
  set -e
  export COMPOSER_ALLOW_SUPERUSER=1
  [ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist
  grep -q "^APP_KEY=base64:" .env || php artisan key:generate --force
'

bash "$HARNESS_DIR/scripts/install-hooks.sh"

docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan db:seed --force 2>/dev/null || true

cat <<'BANNER'
✔ Stack ready
  API:    http://localhost:8080/api/v1
  Docs:   http://localhost:8080/docs
  Mailpit http://localhost:8025
Optional services (start when a module needs them):
  S3:      docker compose --profile storage up -d
  Worker:  docker compose --profile worker up -d horizon
  Cron:    docker compose --profile scheduler up -d scheduler
  Echo:    docker compose --profile realtime up -d reverb  (+ composer require laravel/reverb)
Next: tail harness/progress.md, git log --oneline -15, run a smoke test.
BANNER
