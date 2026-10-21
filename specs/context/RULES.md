# Session Entry Point

Read this first, every session. It says what to load and in what order — nothing
else. How to do the work lives in `CLAUDE.md` and the task-specific files it
points at.

## What this repository is

An implementation project with a documentation layer attached, not the other way
round. **Code is the primary deliverable**; the artifacts exist to make the code
traceable to something a stakeholder actually said.

Of those artifacts, **only the ERD is expected to exist and stay current** — it
is the one that describes the data every piece of code touches, and the one that
goes wrong silently when it drifts. BPMN, class, state and component diagrams
are **optional**: draw one when it is the fastest way to settle a question or
show a client something, skip it when it is not. Conventions for all of them are
in `context/document-writer-only/`, loaded only when you are actually drawing.

Nothing here gates code on a diagram existing.

## Startup

Read this small set before working:

1. **`pm/active.json`** — the primary issue right now, and its status.
2. **`pm/tracker.yaml`** — open work, statuses, dependencies. Closed work is in
   `pm/archive.yaml`; a dependency that is not in the tracker is not missing, it
   is resolved there.
3. **`context/general-rules.md`** — the shared working rules. This file owns
   them; nothing else restates them.
4. **`context/index/map.md`** — who owns what, so you navigate instead of
   re-discovering.
5. **The active issue's plan**, `pm/issues/{slug}/plan.md`, when one exists.
6. **`context/shape/{area}.md`** for the business area the task touches, when
   one exists (index: `context/shape/README.md`). It is how that area *thinks*
   — intent and leanings, in the user's own words — and it never overrides
   code. If the two disagree, raise it with the user.

Skim **`context/index/decisions.md`** — one line per architectural decision —
and read a decision's own file only before reversing it, not before every task.

That is the whole startup set. Everything else is loaded on demand.

## Finding things

Three files answer "where is it" and "what is this called". They are meant to be
searched, not read. A fourth answers "what does it mean":

| Question | File |
|---|---|
| Where does X live, and what does it touch? | `context/index/inventory.md` — **grep it**, one line per item |
| The ticket says "{business term}" — what is that here? | `context/index/vocabulary.md` |
| Who owns this behaviour, and why is it like that? | `context/index/map.md` |
| What does this area mean, and which way does the user lean? | `context/shape/{area}.md` |

`context/index/README.md` explains the split and why only the inventory can be
generated.

## Deliberately not in the startup set

**`context/gotchas.md`.** It is the raw learning buffer — unverified, unfiled
observations waiting to be compiled. Append to it when work teaches you
something; read it when running the distill pass (`context/distill.md`). Reading
it as guidance gives unreviewed claims the authority of `context/`, which is the
one thing the buffer is designed not to have.

## Then, per task type

- **Building, fixing, reviewing code** → `context/coder-only/README.md`, then
  only its task-relevant files.
- **Drawing or updating an artifact** → the matching file in
  `context/document-writer-only/`, and delegate `.drawio` work to the specialist
  agent that already knows its conventions.
- **Questions, investigations and reviews** may stop after the relevant reads.
  They do not require a plan, an issue, or any artifact unless the user asks.

Do not load the documentation conventions as general coding context, and do not
load the coding conventions to draw a picture. Both are large, and a session
that loads everything has spent its attention before it starts.
