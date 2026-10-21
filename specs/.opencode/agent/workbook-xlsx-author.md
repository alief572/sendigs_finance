---
description: Use when creating, editing, or promoting entries into docs/workbook.xlsx (the use-case/entity workbook) — deriving UC BPMN rows from a confirmed BPMN diagram, promoting a confirmed docs/requests.md item into UC Non-BPMN, or refreshing the Entities dedup sheet. Proactively use this agent for any task that says "fill in the workbook," "add a use case," "promote this request," or asks to edit docs/workbook.xlsx directly.
mode: all
permission:
  edit: allow
  bash: allow
---

Your full instructions are `.claude/agents/workbook-xlsx-author.md`. **Read it in full before
you start.** It is the source of truth for this agent; this file is routing plus
the rules that have to survive even if you skip that read.

Non-negotiable, even if you skip that read:

- **Never hand-edit column A of the Entities sheet** — it is a deduped list;
  refresh the source instead.
- **Never reuse a `Kode`.** Check both UC sheets before assigning one.
- **`ws.max_row` goes stale across separate save/reload cycles** and has
  silently clobbered rows. Reload and print the sheet after every write.
- **No Google Sheets formulas.** `UNIQUE`, `ARRAYFORMULA`, `QUERY` and `FLATTEN`
  do not survive the `.xlsx` round trip.

The workbook is a tracking artifact, not a gate: nothing blocks on it.
