# Agent-First Laravel Harness — generic starter

SDD + strict-TDD project harness for building a **Laravel API** 100% with coding agents.
This folder is project-agnostic: no features, no modules — only the rails.

Layout (identical to the proven boilerplate project it was extracted from):

| Path | Purpose |
|---|---|
| `constitution.md` | 11 immutable principles (TDD, evidence, 100% coverage, docker-only…) |
| `AGENTS.md` / `CLAUDE.md` | agent operating rules — read first, every session |
| `docs/` | architecture, conventions, api-conventions skeleton, `adr/` (3 seeded ADRs + template) |
| `specs/` | `feature-scope.md` worksheet, `active/<NNN-slug>/` contracts, `_template/` |
| `harness/` | `feature_list.json` (acceptance truth), `progress.md` journal, `init.sh`, `scripts/` (check = CI, gates, vendored cloudflare security-audit) |
| `.githooks/pre-commit` | wired security-audit gate (secret scan + validators + optional live agent audit) |
| `.agents/` | skills (tdd-cycle, new-feature, new-action, vendored `security-audit`) + reviewer subagents |
| `src/` | docker-compose stack, `bin/*` wrappers, `docker/` image defs, `bruno/` skeleton, phpstan.neon — **your Laravel app gets created here** |
| `toolkit/` | test rails (Pest.php, phpunit.xml, Architecture rules, SourceScan) + `env.example` — copied into `src/` during setup |

## Quick start

```bash
cp -r generic ~/projects/my-new-app && cd ~/projects/my-new-app
$EDITOR SETUP.md        # follow it top-to-bottom (~15 min)
```

Then every session: agents read `AGENTS.md` + `constitution.md`, you fill
`specs/feature-scope.md`, and module `001` starts via the `new-feature` skill.

## Using it with opencode

Everything is wired — just open opencode **at the project root** and restart it after any config edit (config is loaded once at startup):

| Piece | Where | What it does |
|---|---|---|
| `AGENTS.md` | root (auto-discovered) | hard + process rules injected into every session |
| `opencode.json → instructions` | constitution + docs + `src/AGENTS.md` | the whole rulebook is always in context |
| `opencode.json → permission` | enforcement | `php`/`composer` host calls, `git commit/push/rebase/reset/clean` are **denied** — the wrappers-only + human-commits rules become mechanical, not polite |
| Skills `.agents/skills/*/SKILL.md` | `new-feature`, `new-action`, `tdd-cycle`, vendored `security-audit` | auto-loaded; triggered by what you say ("new feature", "implement task T3.1", "audit") |
| Agents `.opencode/agent/*.md` | `spec-reviewer`, `test-reviewer`, `security-reviewer` | fresh-context subagents — ask "run the test-reviewer on this range" or they fire from the commands below |
| Commands `.opencode/command/*.md` | `/session-start` `/verify` `/feature` `/handoff` | the four ritual moments of a session |

Daily loop:

```
/session-start            # journal tail + init.sh + smoke + "what's next"
/feature 001 identity     # spec-first, ONE question per message, stops for your approval
... (iterate: implement task Tx → /verify keeps it honest) ...
/handoff                  # gates green, journal appended, PROPOSES commit blocks — you run them
```

Suggested session opener as the human: paste the module idea, say `/session-start` then
`/feature <NNN> <slug>`, approve/reject each spec draft, and let the agent work task-by-task.
The gate scripts (`harness/scripts/selftest.sh`) + pre-commit security hook make cheating loud.
