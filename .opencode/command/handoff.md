---
description: Session-end ritual: gates, journal entry, proposed commit blocks (never committing yourself).
---

Run the session-end flow (AGENTS #5):
1. Fresh `bash harness/scripts/check.sh` — must be ✔ green (openapi freshness pending-commit is the only sanctioned exception; say it out loud).
2. Ensure exactly one task in progress in tasks.md; verification.md rows written for what shipped.
3. Append the session entry to harness/progress.md (topic, decisions, gates numbers, lessons banked).
4. Propose exact commit command blocks per docs/conventions.md — every line prefixed `SECURITY_AUDIT=off`, `-S` signing, `Spec: specs/active/<F> (task X.Y)` bodies. The human executes them; NEVER run git commit.
5. List what waits for the human (approvals, live smokes, audit runs).
