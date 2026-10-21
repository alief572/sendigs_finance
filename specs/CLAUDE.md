# CLAUDE.md

The procedure manual for this repository — an implementation project with a
documentation layer attached. Code is the deliverable; the artifacts exist to
keep it traceable and to settle questions faster than prose can.

**Read `context/RULES.md` first.** It is the session entry point: what to load,
in what order, and what deliberately stays out.

This file is auto-loaded, so it carries only what a session must not be able to
miss. **The shared working rules are owned by `context/general-rules.md`** and
are not restated here:

| Rule | Owner |
|---|---|
| Classifying work, and the external tracker | `general-rules.md` → **Work Classification** |
| How much planning a task needs | `general-rules.md` → **Planning Gate** |
| Evidence, scope, and how to work | `general-rules.md` → **Working Discipline** |
| Closing an issue | `general-rules.md` → **Definition Of Done** |
| Where durable knowledge goes | `general-rules.md` → **Sources Of Truth** |
| Committing and pushing | `general-rules.md` → **Version Control** |
| Naming, and the Level/Tier rule | `general-rules.md` → **Domain Language**, **Terminology Rule** |
| Capturing what the user explains about an area | `context/shape/README.md`, via a `type: shape` entry in `context/gotchas.md` |

Change a rule in its owner, not here.

**`AGENTS.md` is generated from this file** by `context/sync-manuals.py`, which
appends an explicit operating procedure (`context/manual-opencode.md`) for
runtimes that follow pointers less reliably. Edit `CLAUDE.md` or
`context/manual-opencode.md`, then run `python3 context/sync-manuals.py` —
never edit `AGENTS.md` by hand.

## Diagrams are optional. The ERD is not.

No code waits on a diagram. Draw a BPMN, class, state or component diagram when
it is the fastest way to settle a question or show a client something — not
because a process demands one.

The **ERD is the exception**: it describes the data every piece of code touches,
and it is the artifact that goes wrong silently when it drifts. Keep it current
as part of the work that changes the schema, not as a later chore.

## Never — these destroy work, and a pointer is not enough

- **Never edit anything under `input/`, and delete only what its pruning rule
  allows.** It is append-only client material and the only record of what was
  actually said. A correction is a new intake event that supersedes the old
  one; pruning is limited to uncited sources, on the user's say-so
  (`input/README.md`).
- **Never cite a source document that is not in `input/` or `context/files/`.**
  If the file cannot be committed, commit a stub naming it and where it lives. A
  citation nobody can open is a claim, and the rule behind it ends up re-argued
  from memory.
- **Never delete `context/gotchas.md`.** The distill pass *empties* it. Deleting
  it breaks the learning loop quietly — the compiled output stays behind and
  looks healthy while nothing new accumulates.
- **Never invent a state transition or a business rule that was not stated
  during elicitation.** If you cannot point at why, it does not go in the
  diagram or the code.
- **Inspect every affected table, constraint and relationship before changing
  persistence behaviour**, and prefer a scoped, reversible change over a
  destructive one. Real client data is not a place to find out.
- **Never run tests against anything but the isolated test database.** Once the
  project has code, write its **exact** test commands here in full — including
  any flag that is load-bearing, and what happens without it. A command that
  truncates live data when typed the obvious way cannot rely on a reader
  following a pointer to `context/coder-only/testing.md`.

## When you do touch a `.drawio` file

Every non-trivial diagram built with these conventions has needed at least one
fix that was **invisible from reading the XML** — a label sitting on a
connector, an overlap, a blank element. So:

1. Author or edit the XML.
2. Validate well-formedness:
   `python3 -c "import xml.dom.minidom as m; m.parse('PATH')"`
3. `grep -c '<!--' file.drawio` — XML comments are forbidden here and
   well-formedness validation will not catch them.
4. Export and **actually look at the render**:
   `drawio -x -f png -o out.png file.drawio`
   (`draw.io.exe` on Windows; headless environments need `xvfb-run`. The CLI
   prints GPU and cache noise to stderr — check the output file, not the log.)
5. Crop tight on any crowded region, fix, re-export, delete throwaway PNGs.

**Never hand-derive a style string from memory or from general knowledge of the
notation** when `context/document-writer-only/examples/elements.drawio` exists.
The installed shape library has repeatedly diverged from spec-plausible
attribute names (gateway type is `gwType=`, not `symbol=exclusiveGw`), and
guessing has produced blank or wrong renders every single time it was tried
instead of checked.

Full mechanics: `context/document-writer-only/drawio-general-guide.md`.

## Working the workbook, if the project keeps one

`docs/workbook.xlsx` is a tracking artifact, not a gate — nothing blocks on it.
When you do edit it: never hand-edit column A of the Entities sheet (it is a
deduped list — refresh the source), never reuse a `Kode`, and never assume a
Google Sheets formula survives the `.xlsx` round trip (`UNIQUE`,
`ARRAYFORMULA`, `QUERY`, `FLATTEN` do not). `ws.max_row` goes stale across
separate save/reload cycles and has silently clobbered rows more than once:
reload and print the sheet after writing.

## Context — read only when relevant, not up front

- **Building or fixing code** → `context/coder-only/README.md`, then only its
  task-relevant files.
- **Locating anything** → `context/index/` — `inventory.md` (grep it) for where
  code lives, `vocabulary.md` for a business term, `map.md` for ownership.
  `context/index/README.md` explains the split.
- **Drawing an artifact** → the matching `context/document-writer-only/*.md`,
  and delegate `.drawio` work to the agent that already knows its conventions:
  `bpmn-drawio-author` for BPMN, `diagram-drawio-author` for ERD, class, state
  and component, `workbook-xlsx-author` for the workbook.
- **Understanding an area rather than locating its code** — what it means, why
  its rules exist, which way to lean → `context/shape/{area}.md` (index:
  `context/shape/README.md`). It biases a reading and never overrides code;
  raise a conflict with the user instead of resolving it yourself.
- **A second opinion or independent audit** → `context/guide/cross-model-review.md`
- **Delegating to a subagent, or the guard hook** → `context/guide/multi-agent.md`
- **Starting or reshaping a `context/` layer** → `context/guide/building-the-layers.md`
- **Documenting how modules communicate** → `context/guide/component-conventions.md`
- **Client handed over raw material** (photos, scans, exports, emails,
  transcripts) → file it under `input/<YYYY-MM-DD-slug>/` with a `notes.md`;
  `input/README.md` has the intake rule. Published third-party specs go to
  `context/files/` instead — the split is provenance, not format.
- **You learned something durable** → append it to `context/gotchas.md` with its
  **Why** line. Do not stop mid-task to file it properly; the distill pass
  (`context/distill.md`) compiles the buffer into the files that own it.
- **The user explained how the business works**, corrected you, or gave the
  reason behind a rule → the `harvest` skill drafts `type: shape` entries from
  their own words, for them to approve. Never write `context/shape/` directly.

## Demo versus real work

Framework tests and demos live under
`context/document-writer-only/examples/` and never touch `docs/`, `pm/`, or the
application. If it is unclear which one a request is, ask before writing to any
of them.
