# Todo — Inbox

Raw ideas, observations, and requests that are **not yet confirmed work**.
Nothing here is acted on directly. An item leaves this file in one of two
directions:

- **Confirmed** → an external issue if the project uses one, then a
  `pm/tracker.yaml` row, then a `pm/issues/{slug}/` capsule — in that order.
  The external issue comes first because the id it returns is the issue's
  name everywhere else. Items here have no id until then.
- **Dropped** → deleted, with a line in `pm/log.md` tagged `[TODO]` saying why,
  if the reason is worth keeping.

## How this differs from the other intake files

Three files capture unconfirmed things, and the difference is **whose words
they are**:

| File | Holds | Whose |
|---|---|---|
| `input/<intake>/` | Raw source material, any format, append-only | The client's, verbatim |
| `docs/requests.md` | Functional requests extracted and classified from `input/` | The client's, in our words |
| `pm/todo.md` | Our own ideas: cleanups, framework gaps, things noticed in passing | Ours |

A client request never starts here — it starts in `input/` and is promoted
through `docs/requests.md` to the workbook. This file is for the work nobody
asked for: a convention that needs writing, a diagram that has drifted, an
inconsistency spotted while doing something else.

## Rules

- **Tag each item with its kind** — `[task]`, `[fix]`, `[doc]`, `[system]` —
  so a reader can tell a two-minute cleanup from a redesign.
- **Say why it matters in one line.** An item that only names a thing
  ("workbook Entities sheet") is unactionable a month later and gets deleted
  unread.
- **This is not a queue and not a backlog.** The backlog is `status: open` in
  `pm/tracker.yaml` — work that has been confirmed as worth doing but not yet
  planned. Items here have not cleared that bar.
- **Delete freely.** An idea that has sat here through three passes without
  anyone wanting it is an answer, not a pending decision.

---

## Backlog

_Empty._
