# AGENTS.md

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

**This file is generated from `CLAUDE.md`** by `context/sync-manuals.py`, which
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

## OpenCode Operating Procedure

This section exists only in `AGENTS.md`. Claude Code gets this guidance from its
own tooling and per-user memory; OpenCode has neither, so it is written out
here step by step. **Follow it literally.** Where a step points at a file, the
file owns the rule: the step tells you *when*, the file tells you *what*.

(Source: `context/manual-opencode.md`, appended by `context/sync-manuals.py`.
Edit it there, never in `AGENTS.md`.)

### Step 1: Classify the message before doing anything

| The user says | You do | You do NOT |
|---|---|---|
| a question; "just answer", "explain", "what do you think", "don't do anything yet" | answer, reading files if needed | edit, migrate, commit, create issues |
| "check …", "look up {id}", "gather what you can", "look around" | investigate and report findings | edit |
| "plan", "write the plan", "make an issue" | write `pm/` and the external tracker only | touch application code |
| "go ahead", "do it", "implement", "fix it" | implement, after Step 3 | skip Step 3 |
| "commit", "push" | commit or push, as asked | push when only "commit" was said |

If you cannot tell which row applies, ask one short question.

### Step 2: Load context in this order

Stop as soon as you have enough; a question may need only 1–3.

1. `context/RULES.md`, once per session.
2. `pm/active.json`, then grep `pm/tracker.yaml` for the issue id or a keyword.
   Most work already has an issue.
3. **The shape note for the area** (table below), before reading code. It says
   what the area means and which way the user leans.
4. To find code: grep `context/index/inventory.md` for a name;
   `context/index/vocabulary.md` for a business term; `context/index/map.md`
   for ownership.
5. Implementation only: `context/coder-only/README.md`, then only the files it
   names as relevant.

#### Trigger words → shape note (generated from the Areas table in `context/shape/README.md`)

| Message mentions | Read `context/shape/…` |
|---|---|
| — | _No areas yet. Add a row to the Areas table in `context/shape/README.md`._ |

List the words the user actually types — UI labels and the business's own
terms, in the business's language — not the code's names for them. A message
can hit two rows; read both.

### Step 3: Before you change anything

- **Application code** needs the plan the Planning Gate asks for:
  `context/general-rules.md` → Planning Gate. No plan where one is required →
  stop, say so, and offer to draft one.
- **Data.** Follow the "Never" section above. Before any command that writes
  data, print the environment, connection, database name and target.
- **Tests.** Use exactly the commands the "Never" section names. Never start a
  test run while another one against the same test database is still running,
  including your own backgrounded one.
- **Formatter.** Only on files you changed. Never run it with no arguments.
- **Unrequested hardening, cleanup or refactors.** Do not disrupt a working
  environment for them. Mention what you saw and move on.
- **Scope.** If the change needs something outside the plan, stop and ask.
  Never fold it in silently.
- **Framework APIs.** Verify against the installed version or its current
  documentation. Never from memory.

### Step 4: How to answer

- **Short and plain first.** Lead with the answer in a few lines, and offer
  detail rather than dumping it.
- **Open decisions as numbered questions** the user can answer tersely ("1.
  yes 2. the most recent one").
- **Use the business's words** as they appear in the UI. Do not translate them.
- **Say what you verified and how.** If you did not run something, say so.

### Step 5: Before you finish

- **Capture what the user taught you.** If they explained how something works,
  why a rule exists, corrected you, or stated a preference about an area:
  draft a `type: shape` entry with their exact words and the date, show it to
  them, and append it to `context/gotchas.md` only if they agree. The format is
  in `gotchas.md` → Entry format; the `harvest` skill drafts these from the
  transcripts.
- **Capture what the code taught you.** A trap or a surprising fact goes into
  `context/gotchas.md` under its normal `type`.
- **Never write `context/shape/` directly.** The distill pass promotes
  (`context/distill.md`).
- **Commit or push only when asked.** How: `context/general-rules.md` →
  Version Control.

### Never

- Never let a shape note override the code. If they disagree, tell the user.
- Never edit `AGENTS.md` by hand. It is regenerated.
- Never skip the "Never" section above because a step here seemed to cover it.
