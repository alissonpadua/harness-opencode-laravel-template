---
description: Run the harness session-start ritual (AGENTS #4): journal tail, git log, init.sh, smoke.
---

Execute the session-start ritual exactly:
1. `tail -40 harness/progress.md`
2. `git log --oneline -15`
3. `bash harness/init.sh`
4. Smoke: `curl -s -o /dev/null -w '%{http_code}' localhost:8080/api/v1/ping` (adjust path if the app predates the ping route)
Then summarize: current module + in-progress task, anything red, and the single next action. One-question-at-a-time applies from here on (AGENTS #6).
