# Tasks — <Module name> (strict TDD: RED before GREEN per task)

## T0 — infra (if any; no app code)
- [ ] T0.1 …

## T1 — <slice> (AC-<NNN>.x)
- [ ] T1.1 RED: <exact test file + names, expected failure reason>
- [ ] T1.2 GREEN: <minimal implementation>
- [ ] T1.3 GATE: filtered suite + full suite green, pint/larastan clean

## T9 — Convergence
- [ ] T9.1 harness/scripts/check.sh green incl 100% coverage
- [ ] T9.2 verification rows; feature_list flip; README module map; journal; propose commits (SECURITY_AUDIT=off prefix)

## Gate discipline (AGENTS #7): no advance/commit-proposal without fresh ✔ check.sh.
