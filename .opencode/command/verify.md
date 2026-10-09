---
description: Run the full gate (harness/scripts/check.sh) and report honestly.
---

Run `bash harness/scripts/check.sh` and show the tail of its output. If it is not ✔ green: do NOT declare anything done, propose nothing, start nothing next — list the failures, fix scope, and re-run (AGENTS #7). If the only failure is openapi freshness "pending commit", say so explicitly and note the human commit that resolves it.
