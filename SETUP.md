# SETUP — bootstrap a new project from this harness (one-time, ~15 min)

Human executes; agents may guide. Run from the new project root (a clone of this template —
for a clean history: `git clone <this-repo> my-app && cd my-app && rm -rf .git && git init -b main`).

> **Network note:** `src/docker/php/certs/` ships a corporate TLS-interception CA
> (see ADR-0010). If you're not behind Zscaler-style MITM, delete the `.crt` file before the first build.

## 1. Create the Laravel app inside `src/`
```bash
COMPOSE_PROJECT_NAME=app src/bin/php --version 2>/dev/null || true   # just to warm nothing; wrappers need the app later
docker run --rm -v "$PWD:/w" -w /w composer:2 create-project laravel/laravel src/app-tmp
rsync -a --ignore-existing src/app-tmp/ src/ && rm -rf src/app-tmp
```
(Alternative: any way you like — the only rule: app code ends up in `src/`, existing toolchain files (`docker/`, `bin/`, `docker-compose.yml`, `phpstan.neon`, `bruno/`) untouched.)

## 2. Overlay the harness rails
```bash
cp toolkit/phpunit.xml src/phpunit.xml
cp toolkit/env.example src/.env.example
cp -r toolkit/tests/Pest.php src/tests/
cp -r toolkit/tests/Architecture src/tests/
cp -r toolkit/tests/Support src/tests/
```

## 3. Boot + core packages (always via `src/bin/*`, never host)
```bash
harness/init.sh
src/bin/composer require laravel/sanctum spatie/laravel-data spatie/laravel-permission dedoc/scramble --no-interaction
src/bin/composer require --dev "larastan/larastan" pestphp/pest "laravel/pint" --no-interaction
src/bin/artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" 2>/dev/null || true
```
Install per your feature-scope: horizon? reverb? activitylog? — each addition = decide in `specs/feature-scope.md` first, then an ADR if architectural.
`spatie/laravel-settings` note: any new settings group class MUST ship a settings-migration seeding its defaults (package refuses `save()` of pure-default sets).

## 4. Wiring checks
- `bootstrap/app.php`: keep `->withEvents(discover: false)` if you wire listeners manually (L13 auto-discovery double-registers `handle()` listeners — learned the hard way).
- `tests/TestCase.php` must exist (Laravel default does).
- Add a throwaway route `GET /api/v1/ping` returning `['data'=>['pong'=>true]]` + one Feature test for it — this is your first GREEN.

## 5. Gates live
```bash
git add -A && SECURITY_AUDIT=off git commit -S -m "chore(harness): generic agent-first rails + laravel skeleton"
harness/scripts/selftest.sh          # both-direction gate checks
harness/scripts/check.sh             # the CI equivalent (coverage min=100 from day one)
```

## 6. Personalize the docs (10 min)
- `specs/feature-scope.md` — fill the locked-scope worksheet.
- `docs/api-conventions.md` — keep the envelope, delete the `<PLANE>` example, start your planes.
- `harness/feature_list.json` — add module `001` when (and only when) its spec is approved.
- Project name sweep: `AGENTS.md`, `src/AGENTS.md`, `src/docker-compose.yml` (`name:`), `.env` (`APP_NAME`, `DB_DATABASE`), `src/bruno/opencollection.yml`.
- Decide + write ADRs: DB (postgres kept?), tenancy yes/no, billing yes/no, realtime yes/no — the seeded ADR-000x files show the format.

## 7. Wire the hooks (once per clone)
```bash
harness/scripts/install-hooks.sh   # .githooks/pre-commit → security-audit gate
bash harness/scripts/selftest.sh   # prove the PR gates both directions
```

## 8. Daily loop afterwards
Session start: tail `harness/progress.md` → `git log --oneline -15` → `harness/init.sh` → smoke.
Module flow: `new-feature` skill → spec → **STOP for human approval** → `tdd-cycle` per task → verification evidence → human runs the proposed commits (prefixed `SECURITY_AUDIT=off`).
