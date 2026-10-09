# Agent-First Laravel Harness

**A repository template for building Laravel APIs 100% with coding agents — under rules the agents cannot cheat.**

Spec-Driven Development + strict TDD + mechanical gates. You steer (requirements, spec
approvals, commits); the agent codes, tests, journals, and proves — and every "done" must
survive `check.sh`, a fresh-context reviewer, and an evidence log.

Battle-tested: extracted from a production-shaped boilerplate where **seven modules**
(auth/2FA/passkeys, multi-tenancy, Stripe billing, notifications catalog, admin & audit,
security & API, onboarding) were shipped agent-first — 395 tests, 100% line coverage,
zero agent-executed commits. Project-agnostic: no features in here, only the rails.

## What's inside

| Layer | What it does |
|---|---|
| `constitution.md` | 11 immutable principles: spec-or-nothing, test-first, tests/specs are sacred, evidence over claims, 100% coverage, docker-only toolchain, human-only commits |
| `AGENTS.md` / `CLAUDE.md` | Operating manual auto-loaded by agentic CLIs (opencode, Claude Code, …): session-start/end rituals, one-question-at-a-time requirements, gate discipline |
| `specs/` | `feature-scope.md` (locked scope worksheet you fill first) + `active/NNN-slug/{spec,plan,tasks,verification}.md` per module + `_template/` |
| `harness/` | `feature_list.json` (acceptance truth agents may only tick), `progress.md` (append-only session journal), `init.sh` (idempotent boot), `scripts/` |
| `harness/scripts/check.sh` | **The CI-equivalent gate**: pint → Larastan L8 → composer audit → pest with `--coverage --min=100` → OpenAPI freshness. Nothing is "done" on red |
| `harness/scripts/gate-*.sh` + `selftest.sh` | PR gates (no-src-without-spec, test-before-impl commit order) — with both-direction self-tests, because a gate that can't fail proves nothing |
| `.githooks/pre-commit` | Wired security gate: secret scan (fail-closed) + [Cloudflare's security-audit skill](https://github.com/cloudflare/security-audit-skill) validators + optional live headless agent audit (vendored under MIT in `.agents/skills/security-audit/`) |
| `docs/` | architecture (the write-path pipeline), conventions, api-conventions envelope contract, ADRs (template + 3 seeded) |
| `.agents/skills/` | Agent procedures with frontmatter: `new-feature` (spec-first flow), `new-action` (endpoint scaffold), `tdd-cycle`, vendored `security-audit` |
| `.opencode/` + `opencode.json` | **opencode wiring**: commands, reviewer subagents, permissions that *mechanically deny* host `php`/`composer` and `git commit/push/reset` (see below) |
| `src/` | Docker toolchain waiting for your app: php-fpm image (GD, redis, xdebug), nginx, postgres, redis, mailpit + optional profiles (S3 via rustfs, reverb, horizon, scheduler), `bin/*` wrappers, bruno skeleton, phpstan.neon |
| `toolkit/` | Test rails overlaid into the app at setup: Pest bootstrap, phpunit (sqlite/array/sync), Architecture suites (FormRequest-on-writes, mass-assignment ban, no-writes-in-controllers, readonly-by-default, model style) with commented pattern-templates for per-module rules |

## Requirements

Docker (+ compose v2), `git`, any agentic CLI (opencode recommended — wired out of the box;
works with anything that reads `AGENTS.md`), `node` for the pre-commit security stage,
optional GPG for signed commits (`git commit -S`).

## Quick start

```bash
# 1. GitHub: green "Use this template" → new repo. Or:
git clone git@github.com:alissonpadua/harness-opencode-laravel-template.git my-app
cd my-app && rm -rf .git && git init -b main   # start history clean

# 2. Create the Laravel app + overlay rails (follow SETUP.md top-to-bottom, ~15 min)

# 3. Lock the product: fill specs/feature-scope.md with your agent (requirements chat)

# 4. Open your agent CLI at the repo root and start module 001:
/session-start        # ritual: journal, git log, init.sh, smoke
/feature 001 <slug>   # spec-first: ONE question per message → STOP for your approval
```

From there the loop repeats per module — agent drafts spec → **you approve** → tasks
executed test-first → `/verify` keeps honesty mechanical → `/handoff` proposes commit
blocks **you** execute (the agent literally cannot: opencode permission denies
`git commit*`, and `feature_list.json` step edits break the sacred-tests rule #3).

## opencode integration

Open opencode **at the project root** (config loads once at startup — restart after edits):

| Piece | File | Effect |
|---|---|---|
| Always-in-context rulebook | `opencode.json → instructions` | constitution + docs + `src/AGENTS.md` injected every session (AGENTS.md itself is auto-discovered) |
| Mechanical enforcement | `opencode.json → permission` | host `php *` / `composer *` denied (wrappers only), `git commit/push/rebase/reset/clean` denied (human-only commits), `git checkout` asks |
| Ritual commands | `.opencode/command/` | `/session-start` `/feature` `/verify` `/handoff` |
| Fresh-context reviewers | `.opencode/agent/` | `spec-reviewer` (impl-vs-contract drift), `test-reviewer` (test-first order + no weakened assertions, from git history), `security-reviewer` (per-diff IDOR/authz/secrets) |
| Procedures | `.agents/skills/*/SKILL.md` | auto-loaded skills that trigger on natural language |

## How the rails hold (defense in depth)

1. **Constitution + AGENTS.md** — the agent's standing orders (auto-injected).
2. **`check.sh` = CI** — identical locally, in PRs, on main; coverage floor 100% from the first commit.
3. **PR gates** — diffs touching `src/app` without a `specs/` change fail; impl commits without test changes fail; `selftest.sh` proves the gates themselves reject bad diffs **and** accept good ones.
4. **Pre-commit security audit** — deterministic secret scan + vendored Cloudflare skill validators on every human commit (`SECURITY_AUDIT=off` skips only the live-agent phase by policy; the scan still runs).
5. **Evidence log** — `verification.md` rows per acceptance criterion; `feature_list.json` flips only from those rows; journal per session. "Tests passed earlier" is not evidence — re-run.

## Built-in war stories (docs/api-conventions.md §Tooling notes)

The template ships the traps we already paid for, as standing notes: Laravel 13 event
auto-discovery double-firing explicit listeners, predis string-casting cached scalars,
`Pivot::fresh()` emitting pgsql-invalid SQL, positional route-arg splicing after
FormRequests, laravel-settings refusing pure-default saves, `--no-verify`-style bypasses
documented as visible escapes only. Read them before your first 2am.

## Trimming notes

- `src/docker/php/certs/` contains a corporate TLS-interception CA (Zscaler) — delete the
  `.crt` and its `update-ca-certificates` line if your network doesn't MITM TLS.
- Dockerfile installs GD/redis/xdebug — strip what you don't need; `docker/php/fetch-deps.sh`
  refreshes the vendored PECL tarballs (see ADR-0010 for why).
- Optional compose services (rustfs, reverb, horizon, scheduler) live behind profiles —
  they never boot unless a module asks for them.

## License

MIT — see [LICENSE](LICENSE). Vendored exception: `.agents/skills/security-audit/`
© Cloudflare, Inc., MIT, retained unmodified (its LICENSE travels with it).
