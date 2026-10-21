# General Rules

Single owner of the shared working rules (work classification, planning gate,
working discipline, definition of done, version control). The manuals
(`CLAUDE.md`, `AGENTS.md`) keep only what must be impossible to miss: rules whose
violation destroys work. Change a rule here, not there.

## Current Scope

Implementation project with a documentation layer. Code is the deliverable;
`docs/` and diagrams keep it traceable to something a stakeholder said.

- **Only the ERD is a maintained artifact.** BPMN, class, state, component are
  optional — draw to settle or communicate something, skip otherwise. No code
  waits on a diagram.
- Code/tests/schema describe **implemented** behaviour; requirements describe
  **intended**. Surface conflicts; never silently rewrite one to match the other.

## Sources Of Truth

| What | Lives in |
|---|---|
| Durable facts and rules (what is *true*) | `context/` |
| Work state and history (what *happened*) | `pm/` — `tracker.yaml` open, `archive.yaml` closed, `issues/{slug}/` capsule, `log.md` narrative, `todo.md` unconfirmed |
| Requirements, specs, client deliverables | `docs/` |
| Raw client source material (append-only) | `input/` |
| Business intent per area (user's quoted word) | `context/shape/` |

- Historical plans explain past decisions, never override current code/schema/specs.
- **Every durable rule has exactly one owner in `context/`.** Update its owner,
  never restate it in an issue or a second conventions file.
- An observation with no owner yet goes in `context/gotchas.md` (the buffer the
  distill pass promotes and empties). It is intake, not truth: not read at
  session start, never deleted.

## Domain Language

Business terms follow the client's and source documents' language. **Do not
silently translate or normalise a load-bearing term.** Pick one language per
project at the start and record it here.

_This project:_ `{language, and how entity/table/column names are formed}`.
Structural and technical naming follows the stack's own conventions
(`context/coder-only/` territory).

A term whose business name and technical name diverge goes in
`context/index/vocabulary.md` — the first time someone asks what it maps to.

## Work Classification

| Kind | Entry |
|---|---|
| Implementation (features, bugs, refactors, security, upgrades, perf, data repairs) | `pm/tracker.yaml` directly |
| Business change (new capability or changed rule) | Confirm with user, then the tracker |
| Artifact work (diagram, workbook, requirements doc) | Load `context/document-writer-only/` only then |
| Questions / investigations / reviews | Create nothing; answer the question |

**No artifact is a prerequisite for code.** A change needs no workbook row or
diagram before it is built; keep an existing one current as upkeep, not a gate.

### The external tracker

`pm/` owns issue **state**; the external tracker (if used) owns issue
**identity**.

- **The issue id is the external tracker's id, and nothing else.** Create the
  external issue first; its id is used in the tracker row, capsule dir
  (`pm/issues/{id}-short-name/`), commit message (`Refs {id}`) and prose. Search
  before creating.
- This replaced per-type codes (`FEAT-12`…): codes got reused across issues and
  apps and duplicated when matched on code instead of id. An external tracker
  cannot reissue a number. Accepted cost: no offline issue creation; a
  `status: open` row cannot exist before its external issue.
- Labels and project are **derived, not chosen** — from the row's `type`, `epic`
  and app. Create a new label/project only when the user asks.
- Record the issue URL at the top of `plan.md` (`url:`); don't write the id twice.
- The external description stands alone (problem, change, verification, what's
  left). Point at `plan.md` rather than duplicating it.
- Update the external issue in the same pass as the row; close it when the row
  archives.
- **History keeps its own names.** If the id scheme changes, archived rows,
  `log.md`, `context/` notes and closed external issues stay as written.

Without an external tracker, `pm/` mints the id: one project-wide sequence,
never per-type, never reused.

## Planning Gate

**Scale the plan to the work.** Substantial work is agreed before it is built;
not every change acquires paperwork.

- **Contained fix** (no observable change: crash, bad log line, build break,
  broken script; one clear cause, testable): tracker row, then do it.
- **Observable change** (output, status, stored data; spans components; > ~1h):
  confirmed plan under `pm/issues/{slug}/` first. Unsure which side? This one.
  The line is *observable change*, not size or confidence.
- **Confirm dependencies are `done`** (open in `tracker.yaml`, closed in
  `archive.yaml`; `dropped` is not satisfied). A row's status can lag the code —
  grep for what the dependency was meant to produce before calling it unmet.
- **Inspect every affected table, constraint, relationship and nearby code path
  before changing persistence.** Does not scale down.
- The plan defines scope by observable behaviour, affected components,
  verification. A sequence diagram is required only when interactions,
  integrations or ordering change.
- **The plan's scope is the whole scope.** Nothing outside it gets built, nothing
  inside it silently skipped. A departure updates the plan and re-confirms.

## Working Discipline

Each rule is here because it went wrong.

- **Measure a causal claim before writing it into `context/`, and re-measure one
  you are about to act on.** A plausible mechanism is not evidence. Prefer a
  number to a narrative.
- **A delegated diagnosis is a claim until checked.** Reproduce the failures and
  re-check premises (hostnames, shared causes) against logs and code before
  accepting or closing on a delegated report. A shared cause masks the outlier.
- **A rule read off one reference document is a rule about that document.**
  Check the degenerate case (one group, groups of one) before generalising;
  express the rule as a property of the data.
- **A new rule must reproduce the existing worked example** in
  `context/document-writer-only/examples/` before it may replace or generalise
  an older one.
- **An explicit written rule outranks in-the-moment reasoning.** State the
  tension, propose the change, let the user decide.
- **A surprising result means check the rule first, the input second.** Reaching
  for the input explanation first becomes defending a broken rule.
- **Walk every branch to a terminal state before calling a process model done.**
  Failure branches included; a branch that stops with no continuation is a
  modelling bug.
- **Read current sources before recommending an architecture or API.** A
  recommendation assembled from memory has already anchored; inspect the
  installed version.
- **Preserve behaviour outside the confirmed scope.** No cleanup, renaming or
  architecture changes bundled into a contained fix.
- **In a design conversation, recommend in prose.** Reserve structured
  multiple-choice for a genuine fork. When the user restates their framing a
  second time, build on it.
- **Continue through to verification** unless asked only for analysis, planning,
  review or documentation.

## Version Control

- **Stage by path, never `git add -A` / `git add .`.** Several issues can sit
  uncommitted at once; name the files the task touched.
- **Commit message carries `Refs {id}`** — or says the work has no issue. Enforced by
  `.githooks/commit-msg`; once per clone: `git config core.hooksPath .githooks`.
- **Commit and push only when the user asks**; push only when "push" was said.
- **Record the branching model here, once**: trunk-only (commit straight to `main`, no
  pull requests) or branch-per-issue. An agent left to guess will branch "to be safe",
  which on a trunk-only project strands work on the remote. _This project:_
  `{trunk-only | branch-per-issue}`. The distill pass follows it (`context/distill.md`).

## Terminology Rule

Competing informal definitions: **don't invent our own.** Adopt the practitioner
standard closest to the context (SAP/Signavio for process architecture), cite it
in the owning file. This project says **"Level"**, not "Tier".

## Definition Of Done

1. **Verify** — narrowest test/reproduction, then the affected suite, then the
   formatter. Artifacts: rendered or reloaded and inspected against its
   convention file.
2. **Reconcile** what was built against the plan, where one exists.
3. **Update durable ownership** — `map.md` when ownership moved,
   `decisions/` + row when architectural, the ERD when the schema changed;
   `python3 context/lint.py` when `context/` changed. Then
   answer: **what did this teach us, and where does it go?** (`gotchas.md` entry,
   an update to an owner, or "nothing" — valid, but the question is not skippable).
4. **Move the row** `tracker.yaml` → `archive.yaml` as `done` (or `dropped`, with
   reason), with a concise build/verify summary.
5. **Append to `pm/log.md`**, newest first, tagged.
6. **Delete the capsule** from `pm/issues/` (git keeps the plan; find by slug).
7. **Point `pm/active.json`** at the next issue or clear it; close the external
   issue with the same summary.

Steps 1–3 are what "done" means; the rest is bookkeeping, but skipping to 7 is
what makes old work unreconstructable. Pre-existing failures are reported
separately. Never weaken a check, or alter real client data, to satisfy
verification.
