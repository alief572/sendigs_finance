# Projectinator

> **Status: early.** A working template distilled from real projects. Its author has used it
> end to end; nobody else has tried it yet, so expect rough edges. Start at
> [`QUICKSTART.md`](QUICKSTART.md). (Earlier working title: bpmn-to-erp.)

A working setup for building an ERP — **context management, an index agents can
navigate, and lightweight project state** — with a documentation layer attached
for the parts of an ERP that have to be traceable to something a stakeholder
actually said.

Code is the deliverable. The artifacts exist to settle questions faster than
prose can and to keep the trail from "a stakeholder said this" to "this exists"
reconstructable months later. **Only the ERD is expected to stay current**;
BPMN, class, state and component diagrams are optional, drawn when they earn
their keep.

> **This README describes what exists and why. It does not state rules.** Each
> rule has exactly one owner, and pointing at that owner rather than summarising
> it is the point — three copies of a rule is three copies to update, and in
> practice only the one you happened to be reading gets updated.

## What this repo contains

| Path | What it is |
|---|---|
| `QUICKSTART.md` | What to do: start a project from this repo, a first session, a five-minute check, what to report if you are testing it. |
| `CLAUDE.md` | The procedure manual — what to do, and the few rules that must not be missed. |
| `AGENTS.md` | **Generated** from `CLAUDE.md` by `context/sync-manuals.py`, plus an explicit step-by-step operating procedure (`context/manual-opencode.md`) for runtimes that follow pointers less reliably. Never edited by hand. |
| `opencode.json` / `.opencode/` | Cross-model setup: loads `AGENTS.md` as the manual and mirrors the subagents, so a reviewer model starts with project context instead of blind, plus a `coder` agent that implements one confirmed issue per call. See `context/guide/cross-model-review.md`. |
| `context/RULES.md` | Session entry point: what to read first, in what order, what deliberately stays out. |
| `context/general-rules.md` | **Owner of the shared working rules** — sources of truth, work classification and the external tracker, planning gate, working discipline, version control, definition of done. |
| `context/gotchas.md` | The learning buffer. Raw, unverified observations, appended during work. Never read as guidance, never deleted. |
| `context/distill.md` | The pass that compiles the buffer into the files that own each fact, then empties it. |
| `context/shape/` | How each business area *thinks* — intent, reasons, leanings — in the user's own quoted words. Never overrides code. |
| `context/index/` | `README.md` (the three-file contract), `inventory.md` (generated, grep it), `vocabulary.md` (business term → our names), `map.md` (ownership, prose), `decisions.md` + `decisions/`. |
| `context/coder-only/` | Implementation conventions. Its README says what to create once a stack is chosen. |
| `context/document-writer-only/` | Per-artifact conventions: ERD first, then BPMN, class, state, workbook, plus the cross-cutting draw.io guide. |
| `context/document-writer-only/examples/` | Verified worked examples, and `elements.drawio` — the living shape palette. |
| `context/guide/` | The multi-agent baseline and the guard; component-diagram conventions; the cross-model review workflow; `building-the-layers.md` (how to start a layer). |
| `context/lint.py` | Consistency checks for the context layer: stale `AGENTS.md`, dangling paths, the decisions index, the learning buffer, uncredited skills. |
| `context/files/` | Published third-party specs the conventions are distilled from (OMG BPMN, UML). |
| `input/` | Raw client source material, one folder per intake event. Append-only. |
| `docs/` | Client deliverables: `workbook.xlsx`, `requests.md`, `fr-nfr.md`, `statuses.md`, `rbac-entities.csv`. |
| `pm/` | Work state: `tracker.yaml` (open only), `archive.yaml` (closed), `issues/{slug}/` (the capsule of anything open), `log.md`, `todo.md`, `active.json`. |
| `THIRD_PARTY.md` | Credits and licences for vendored files, what is deliberately not vendored, and recommended skills |
| `.claude/agents/` | Specialist subagents for the error-prone artifacts, plus the distill agent and `coder`. |
| `.claude/settings.json`, `.agents/plugins/repo-guard/`, `.opencode/plugin/` | The guard: one script that denies tool calls breaking the "Never" rules, wired into Claude Code and OpenCode. |
| `.githooks/` | The commit-message check (`Refs <id>` or "no issue"); turn on with `git config core.hooksPath .githooks`. |
| `.claude/skills/` | Tooling skills (`drawio`; credits and licences in `THIRD_PARTY.md`), and `harvest`, which drafts shape entries from the user's own messages in the transcripts. |

## How work actually flows

```
input/  ── raw client material: photos, scans, exports, emails, transcripts
      │        (append-only; never worked from directly)
      │
      ▼
docs/requests.md ── extracted and classified, still unconfirmed
      │
      │  confirmed with the user
      ▼
pm/tracker.yaml row  ──(anything substantial)──→  pm/issues/{slug}/plan.md
      │                                                    │
      │←───────────────────── contained fix ───────────────┘
      ▼
   build it  ──→  verify  ──→  pm/archive.yaml + pm/log.md
      │
      ├─→ ERD updated when the schema moved          (expected)
      ├─→ context/ updated when durable truth moved  (expected)
      └─→ a BPMN / class / state diagram             (only when it helps)
```

Nothing in that path blocks on a diagram or a workbook row existing. Where one
already exists for the behaviour, keeping it current is part of the work.

### The input layer

`input/` is what everything else derives from, and it exists to make one
guarantee hold: **every downstream artifact can name the thing that justified
it.** Without it, the only surviving record of why a rule exists is the rule
itself — a spec says the hold is ten minutes, nobody can find who said ten, and
a year later it gets re-decided from memory.

One folder per intake event, each carrying a `notes.md` whose `## Extracted`
list doubles as the queue. Full rules: [`input/README.md`](input/README.md).
Client material and published standards stay apart on provenance —
`context/files/` is what a standards body published, `input/` is what the client
gave us.

## The self-learning loop

```
context/gotchas.md  →  distill pass  →  context/ (compiled truth)
```

A lesson learned mid-task is **appended to the buffer**, cheaply, without
stopping to find its proper home. Later, a manually-triggered pass verifies each
entry against the current repository, promotes what survives into the file that
owns the subject, drops what has gone stale, empties the buffer, and opens a PR
with an audit table accounting for every entry.

The buffer is deliberately **not** read at session start: unverified notes
carrying the authority of `context/` are worse than no notes. Closing an issue
asks "what did this teach us, and where does it go?" — and "nothing" is a valid
answer, as long as it is answered.

### Shape: the knowledge the code cannot verify

The loop above compiles facts about the system and verifies each against the
repository. That shuts out what the user says the business **means**, why a
rule exists, and which way to lean — so it used to live only in chat, and every
new session had to be told again.

```
user's own messages  →  harvest skill  →  gotchas.md (type: shape)  →  distill  →  context/shape/{area}.md
```

Shape travels through the same buffer, but its evidence is the user's quoted,
dated word rather than the code, and the user approves each draft before it is
appended. A shape note biases a reading and never overrides code; a conflict is
raised with the user. Retrieval over raw transcripts was rejected for this:
fragments cannot tell a settled position from a reversed one. Harvest early —
transcripts are pruned after 30 days by default.

## The index

Three files, split by **what can be generated**
([`context/index/README.md`](context/index/README.md)):

| Question | File | Maintained |
|---|---|---|
| Where does X live, and what does it touch? | `inventory.md` | Generated |
| The ticket says "{business term}" — what is that here? | `vocabulary.md` | By hand |
| Who owns this, and why is it like that? | `map.md` | By hand |

A scanner can produce the inventory and nothing else — it cannot know that users
call it one thing and the code another, or why a boundary sits where it does.
Equally, a hand-maintained inventory is stale within a month and nobody notices,
because nobody reads an inventory end to end. So the inventory is generated, one
line per item, designed so that a single grep answers *where it lives* and *what
calls it* at once — and its generator **refuses** rather than emitting a partial
result when it meets something unclassified.

## Work tracking

`pm/` is the single work-tracking system. Each file has one job; no fact is
duplicated across them.

| File | Holds |
|---|---|
| `pm/tracker.yaml` | **Open work only** |
| `pm/archive.yaml` | Every closed issue, moved verbatim on close |
| `pm/issues/{slug}/plan.md` | The capsule of an open issue — deleted on close, recoverable from git by slug |
| `pm/log.md` | Narrative history, newest first |
| `pm/todo.md` | Our own unconfirmed ideas |
| `pm/active.json` | The current primary issue |

A row in the tracker, a capsule under `pm/issues/`, and (if the project uses an
external tracker) an open issue there are three views of one set. The exception
is `status: open` — raw backlog with no plan and no capsule. Statuses, fields
and the close sequence live in `pm/tracker.yaml`'s own header and in
[`general-rules.md`](context/general-rules.md).

**With an external tracker, its identifier is the issue's only id** — in the
row, the capsule directory, and the commit message. Per-type codes (`FEAT-12`,
`FIX-7`) were tried and failed at being a stable handle: they got reused and
collided, and duplicated `type`.

**Plans scale to the work.** A contained fix needs a row and can be done; work
that changes observable behaviour, touches persistence, or spans components gets
a confirmed plan first.

## Working on a `.drawio` file

There is a mandatory loop — validate, check for XML comments, export, and
**actually look at the render** — because every non-trivial diagram built with
these conventions has needed at least one fix that was invisible from reading
the XML. The loop is in [`CLAUDE.md`](CLAUDE.md); the mechanics are in
[`drawio-general-guide.md`](context/document-writer-only/drawio-general-guide.md).

Style strings come from `examples/elements.drawio`. The installed shape library
has repeatedly diverged from spec-plausible attribute names (gateway type is
`gwType=`, not `symbol=exclusiveGw`), and guessing has produced blank or wrong
renders every single time it was tried instead of checked.

## Subagents

Delegate rather than hand-authoring; each already knows which conventions to
load.

| Agent | Use for |
|---|---|
| `diagram-drawio-author` | ERD, class, state, component |
| `bpmn-drawio-author` | BPMN diagrams |
| `workbook-xlsx-author` | Use cases, promoting requests, the Entities dedup |
| `distill` | The distill pass over the learning buffer |
| `coder` | Implementing one confirmed issue per invocation, with an optional deferred-verification mode for batches; never stages, commits or pushes |

## Environment notes

- **draw.io Desktop** provides the CLI export. The binary is `drawio` on Linux
  and `draw.io.exe` (under Program Files) on Windows; headless environments need
  `xvfb-run`. It prints GPU and cache warnings to stderr on every run — check
  the output file, not the log lines.
- **Workbook editing** uses `openpyxl`. No live formulas: Google Sheets'
  `UNIQUE`/`ARRAYFORMULA`/`QUERY`/`FLATTEN` do not survive the `.xlsx` round
  trip, so the Entities dedup is refreshed manually or by script.
- **`ws.max_row` goes stale across separate save/reload cycles.** This has
  silently clobbered rows and blanked a header three separate times. Reload and
  print the sheet after writing; clear an oversized range and `delete_rows()`
  down; rewrite headers last.
- **LibreOffice may be unavailable**, which disables formula recalculation (Anthropic's
  `xlsx` skill ships a `recalc.py` for it; not vendored, see `THIRD_PARTY.md`). Do not
  assume adjacent tooling can call it either.

There is **no CI here**, deliberately. The checks that matter — render the
diagram and look at it, regenerate the index, run the suite — are steps in the
definition of done, not a pipeline someone else maintains.
