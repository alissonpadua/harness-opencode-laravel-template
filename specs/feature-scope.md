# Feature scope — LOCKED worksheet

This file is written by the human (with agent help during requirements chat) BEFORE module 001, and is the read-only reference for every spec author. Modules expand scope into EARS ACs — they never add features here silently; scope edits are dated entries below.

## Product in one paragraph
<what this API is for, who uses it>

## Module map (each becomes specs/active/NNN-<slug>/)
| # | Module | One-line scope | Status |
|---|--------|----------------|--------|
| 001 | <e.g. Identity & access> | <register/login/2fa/tokens…> | ⏳ next |
| 002 |  |  | ⏳ |

## Per-module scope notes
### 001 — <name>
- IN: …
- OUT (explicit cuts, keep them honest): …

## Cross-cutting locked decisions (link the ADR)
- Database: postgres (ADR-____) — tenancy: <yes/no> (ADR-____) — billing: <…> — realtime: <…> — file storage: <local + rustfs/S3 optional stack in compose>
- Auth: Sanctum bearer device tokens; one per device_type.
- Everything else: NOT in v1 (add here before any spec references it).

## Scope change log
- <date> — initial lock.
