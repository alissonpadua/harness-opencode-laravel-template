# Architecture

Laravel 13 · PHP 8.4 · API-only · PostgreSQL · Redis. Decisions: see `adr/` (seeded: docker-only toolchain, TDD-mandatory, vendored build deps — add yours: tenancy? billing? realtime?).

## Request lifecycle (write path)

```
Route (/api/v1|/admin/v1) → middleware (auth, [tenancy], throttle)
  → FormRequest           validation + authorization, nothing else
  → App\Data\*Data        DTO (spatie/laravel-data) built from validated()
  → App\Actions\*Action   ~30-60 lines, one public method, all domain writes
  → Event                 App\Events\* (dispatched by the Action)
  → Listeners             notifications / webhooks / side effects
  → API Resource / Data   response shape
```

## Read path
Controllers may query Models directly (with scopes + eager loads) and return Resources. No Action for reads unless multi-source.

## Hard rules (Pest arch tests enforce — src/tests/Architecture)
- Controllers: no `DB::`, no `->save(`, no `::create(`, no `new App\Models\*`
- Every write route type-hints a FormRequest (arch test over the live route table)
- No mass-assignment shortcuts: `$request->all()`, `unguard(`, `$guarded = []` banned in `app/`
- Actions never call mailers/HTTP directly — dispatch events instead
- `*Action` classes live only in `app/Actions`; stateless classes are `final readonly` (docs/conventions.md)
- Models: `protected $fillable`/`$hidden` + `casts(): array` method + `Attribute::make` accessors — never `$casts` property/attributes

## Patterns to (re)adopt via ADR when needed
- **Tenancy:** single DB + `organization_id` scope, membership pivot with role enum, current-org on users, authorizer reading a role→permission config map.
- **Billing:** gateway behind a `Contracts\PaymentGateway` adapter interface (fake in tests, Stripe/Fake drivers in one namespace; SDK imports confined to that namespace by an arch rule — see the commented example in `tests/Architecture/SourceRulesTest.php`).
- **Notifications:** catalog class per type (glob-discovered) + single dispatcher funnel + prefs; queued delivery job as the ONLY notify() surface (arch rule).
- **Settings:** spatie/laravel-settings typed groups + rules()/publicKeys contract; ALWAYS ship a settings-migration seeding defaults (package refuses save() of pure-default property sets).
- **Audit:** one write channel class (allow-listed by arch test); whitelists never contain credential-shaped attributes.

## Auth model (starting point)
Sanctum DB tokens; one active token per device_type; optional ability-scoped integration tokens. Roles: spatie global plane; org roles (if tenancy) on pivot. Passwords: model `hashed` cast owns hashing — seeders/factories pass raw.

## Realtime (optional)
Reverb + private `user.{id}` channels; also persist inbox rows (never trust socket delivery).
