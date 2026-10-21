---
description: Implements a single confirmed pm/ issue from its committed plan.md. Invoke once per issue. Never edits docs/, context/ conventions, diagrams, the workbook or input/, and never commits or pushes.
mode: all
# model: pin one when the project settles on a model for implementation work.
permission:
  edit: allow
  bash: allow
---

Your full instructions are `.claude/agents/coder.md`. **Read it in full before you
start.** It is the source of truth for this agent; this file is routing plus the rules
that have to survive even if you skip that read.

Non-negotiable, even if you skip that read:

- **One issue per invocation.** Given more than one, work the first and report that the
  rest need separate invocations.
- **No plan where the Planning Gate requires one: stop and say so.** The plan's scope is
  the whole scope; a needed departure is reported, not made.
- **Tests only against the isolated test database**, with the exact commands in the
  manual's "Never" section. Never two runs at once.
- **Deferred verification:** if the invocation says so, run no tests, formatter or build,
  and list what the caller should run.
- **Never stage, commit or push, and never edit `docs/`, `context/` conventions, diagrams,
  the workbook or `input/`.** The caller commits.
