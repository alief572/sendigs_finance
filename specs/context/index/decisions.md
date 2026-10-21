# Architectural Decisions — index

Durable decisions and their consequences. **Read this index. Read a decision's
own file before reversing it — not before every task.**

Each body is its own file under `decisions/`, so a grep lands inside one
decision instead of somewhere in the middle of a long log. The index row carries
the claim in one bolded sentence; if you can only read one line, that line has
to be enough to stop you doing the wrong thing.

Closing an issue that settled an architectural question: **add the file, add the
row.** Both, in the same change — a file with no row is unfindable, and a row
with no file is a claim with no reasoning. `decisions/decision-template.md` has
the body shape.

| # | Decision | Body |
|---|---|---|
| — | _Empty._ | — |

## What belongs here, and what does not

An architectural choice **and its consequence** belongs here. Three tests, all
of which have to pass:

- **It was a choice.** Something else was possible, and a reader would
  plausibly try that something else.
- **It has a consequence someone will hit.** A decision with no downstream
  effect is a preference, and preferences belong in a conventions file.
- **Reversing it would be a project-level act**, not an edit.

What does not belong: a durable convention (that belongs to its owner in
`context/coder-only/` or `context/document-writer-only/`), which artifact or
code owns which behaviour (`context/index/map.md`), or narrative about when
something happened (`pm/log.md`). Do not restate one file's content in another.

## Amending one

A decision that turns out to be partly wrong is **amended in place, dated**, not
silently rewritten and not deleted. The original reasoning is the most valuable
thing in the file: it shows what was known at the time, and a reader reversing
the decision needs to know whether they have new information or are just
re-arguing it. Add an `**Amended YYYY-MM-DD.**` paragraph inside the relevant
section, and update the index row if the one-line claim changed.

A decision that is fully reversed keeps its file and its number, with the
reversal recorded in it, and gains a new decision that supersedes it. Numbers
are never reused.
