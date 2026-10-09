# API conventions (agent contract)

## Envelope (locked)
- Success: `{"data": ...}` — auto-wrapped by `App\Http\Middleware\ApiEnvelope`; controllers return arrays/`response()->json(['data'=>…])` only when shaping custom status codes.
- Errors: `{"message": "...", "errors": {"field": ["…"]}}` via `App\Exceptions\ApiErrorRenderer` (single match table; all texts resolve through `lang/en/api.php` — no user-facing literals in code).
- Timestamps: UTC ISO-8601 in every payload (per-user timezone is a display/server-side concern).
- Pagination: `?cursor` opaque + `next_cursor` (or paginator `current_page/last_page` for admin tables). 429s always carry `Retry-After`.
- Route groups: public | authenticated `api/v1` | admin `admin/v1` (role-gated; add a vacuity-guarded arch rule the moment the plane opens).

## Errors quick table (start yours; keep en values stable — tests assert them)
| Situation | Status | Message |
|---|---|---|
| Unknown route / missing model | 404 | Not Found |
| No/expired token | 401 | Unauthenticated. |
| Authenticated but not allowed | 403 | This action is unauthorized. |
| Validation | 422 | The given data was invalid. (+errors) |
| Throttle | 429 | Too Many Requests |

## <PLANE> — copy per module as it lands
- …

## Tooling notes learned the hard way (keep; they apply to any Laravel 13 project)
- `Application::configure()` enables event auto-DISCOVERY — `->withEvents(discover: false)` when you wire listeners explicitly (else double-fire).
- Sanctum PAT: custom columns need `Sanctum::usePersonalAccessTokenModel()` + a subclass; controller scalar args after a FormRequest must declare ALL route params positionally.
- Predis returns cached scalars as STRINGS — never `=== 1` a cache value; use strings as markers.
- Pivot-model subclasses restore broken outside belongsToMany context — override `fresh()` to restore by real key if an audit/observer re-reads the row.
- Cache values must be serializer-stable (test stores don't catch this; redis does).
