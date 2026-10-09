---
name: new-action
description: Scaffold a new write endpoint the harness way: FormRequest -> Data DTO -> Action -> Event -> Resource. Use when adding ANY POST/PUT/PATCH/DELETE route, or when the user says 'new endpoint' / 'new action'. Enforces docs/architecture.md placement + tests/Architecture legality.
---

# New write endpoint
FormRequest (rules+authorize) → *Data DTO → Action::handle(Data): Model|void → Event → Resource.
Placement: app/Actions/<Area>/, app/Data/, app/Http/Requests/, app/Http/Controllers/<Area>/, app/Resources/.
Route in routes/api.php under auth+org middleware (or admin-v1.php). Add abilities/throttle rules when the security module lands (see docs/api-conventions.md).
Arch-test legality: no DB:: in controllers, no mailer/HTTP in Actions (dispatch instead).
