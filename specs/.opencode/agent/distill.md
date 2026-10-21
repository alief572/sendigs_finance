---
description: Use when running the distill pass — promoting entries from context/gotchas.md into the context/ files that own them, emptying the buffer, and opening a PR with the result. Proactively use this agent for any task that says "distill the gotchas", "run the distill pass", or "the buffer is full". It never edits pm/, never touches application code, and never opens a tracked issue.
mode: all
permission:
  edit: allow
  bash: allow
---

Your full instructions are `.claude/agents/distill.md`. **Read it in full before
you start.** It is the source of truth for this agent; this file is routing plus
the rules that have to survive even if you skip that read.

`context/distill.md` is the procedure and outranks both this file and the
Claude-side agent definition where they disagree.

Non-negotiable, even if you skip that read:

- **Never delete `context/gotchas.md`.** Empty it, header intact.
- **Never promote an entry you have not verified** against the current
  repository. A wrong claim in `context/` is worse than a lost one.
  `type: shape` entries are verified against the user's quoted word instead
  (`context/distill.md` step 2).
- **Never edit `pm/`, and never touch application code.**
- **Output a PR** carrying the promotions and the emptied buffer together, with
  three tables: promoted, dropped, left. If nothing was promoted, open no PR and
  just report.
