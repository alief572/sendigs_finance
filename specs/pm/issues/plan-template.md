---
id: {ID} # the external tracker's identifier — create that issue first; else the next project-wide number
type: feature # feature | fix | task | doc | system | integration
epic: # optional long-running track
status: planning # planning | planned | in_progress | in_review | blocked
url: # the external issue's URL, if one is used — the id is already `id:`
source: # intake folder, workbook Kode, review, or user request + date
uc: [] # workbook Kode(s) this implements, if any
depends_on: []
blocks: []
---

{One-line summary — this is what goes in the tracker row, and it is updated in
place as the issue moves.}

## Context

_Why this exists and what is already true. Cite evidence a reader can open: an
`input/` folder, a workbook row, a `file:line`, a rendered diagram, a
reproduction. A claim with no citable source is a claim, not context._

## Current State

_What exists today in the affected artifacts or code, relevant to this change.
Written from actually looking, not from memory of the last issue that touched
it._

## Scope

_Observable outcome, affected files, and what is explicitly OUT of scope. The
plan's scope is the whole scope: nothing outside this section gets built, and
nothing inside it gets silently skipped. If the work has to depart from it,
update this file and re-confirm before continuing._

_Add a sequence diagram when interactions, integrations, or process ordering
are changing — not for every contained fix. When one is present, it is part of
this section, and the as-built reconciliation at close checks against it._

## Verification

_How anyone else could confirm this is done. For a diagram: the export was
rendered and looked at, and the relevant convention file was checked against it.
For a workbook change: the sheet was reloaded and printed after writing. For
code: the narrowest test first, then the affected suite, then the formatter._

- [ ] ...
- [ ] Re-read against the owning convention file in `context/`
- [ ] Artifact rendered/reloaded and actually inspected, not just written

> OPEN DECISION: {anything unresolved. Do not implement past this line — take
> it to the user. Delete the marker when it is settled, and record the answer
> in Context.}
