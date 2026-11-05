# input/

Raw, unclassified source material from the client — the substrate every other layer of the
pipeline is derived from. Photos of whiteboards, scans of the forms they use today, exported
spreadsheets, screenshots of an existing system, email threads, meeting notes, recording
transcripts. Any format.

This is a **layer, not a folder**. Its job is to make one guarantee hold: *every downstream
artifact can name the thing that justified it.* Without it the pipeline's first step —
"elicitation → BPMN" — has no artifact behind it, and six months later the only surviving
record of why a rule exists is the rule itself.

## Where it sits

```
input/                        raw client material, any format, unclassified
   |  read / elicit
BPMN  |  FR doc  |  requests   the three formalised input layers
   |  derivation routes
docs/workbook.xlsx            use cases + entities
   |
ERD - class - state - component
```

`input/` is **below** the three input layers, not beside them. BPMN, an FR document, and a
stakeholder request are all already formalised to some degree; this is what they were formalised
*from*.

## What does NOT go here

| Material | Goes to |
|---|---|
| External standards and specs (OMG BPMN, UML, ISO) | `context/files/` |
| Text already extracted and classified from a source | `docs/requests.md` |
| Our own conventions, decisions, diagrams | `context/`, `docs/` |
| Anything we authored rather than received | not `input/` |

The line is **provenance**: `input/` holds what the client gave us, `context/files/` holds
published third-party reference material, and everything else in the repo is ours.

## Intake rule

One folder per **intake event** — a meeting, a delivery, an email thread. Group by *when and from
whom*, never by file type; provenance is naturally chronological and a `photos/` folder tells you
nothing about where a photo came from.

```
input/
  2026-08-13-kickoff/
    notes.md
    whiteboard-order-flow.jpg
    current-intake-form.pdf
  2026-08-27-finance-followup/
    notes.md
    approval-thresholds.xlsx
```

**Every folder has a `notes.md`.** It is the load-bearing file — without it a folder of
`IMG_2847.jpg` is worthless within a month. Template:

```markdown
# <intake event>

- **Source:** who provided this (name, role, organisation)
- **Date:** YYYY-MM-DD
- **Channel:** meeting / email / shared drive / site visit
- **Contents:** one line per file, what it actually shows

## Extracted

- REQ-nn -> docs/requests.md
- UC-nn / UC-Nnn -> docs/workbook.xlsx
- (empty = not yet mined)
```

The `## Extracted` list doubles as the work queue: **a folder whose Extracted section is empty
has not been mined yet.** No separate status tracking, no processed/unprocessed subfolders.

## Rules

- **Append-only.** Never edit or delete a source file. If the client corrects something, that is
  a new intake event that supersedes the old one — record the supersession in the new `notes.md`.
  The point of this layer is that it records what was actually said, including what was later
  wrong.
- **Pruning is the one exception, and only on the user's say-so.** Once `input/` has grown with
  material nothing ever used, a source may be deleted if **nothing in the repo cites it**: grep
  `context/`, `docs/`, `pm/`, the application code and the agent configs for its folder and file
  name first, and delete only what has no hit. A cited source stays, and so does anything the
  user names as reference material regardless of citations. Pruning removes; it never edits.
- **Never work directly from `input/`.** It is not a task queue. Material is extracted into
  `docs/requests.md` (or a BPMN, or an FR list) and classified there first — the same rule
  `requests.md` already has relative to the workbook.
- **Cite the folder, not the file.** Downstream artifacts reference
  `input/2026-08-13-kickoff/`, so a citation survives a file being renamed.
- **A cited source must exist.** If a conventions file or diagram cites a source document, that
  document — or a stub naming it and where it lives — must be present in `input/` or
  `context/files/`. A citation to a file nobody can open is not verifiable, and a rule whose
  justification cannot be checked eventually gets argued about from memory.

## Confidential and large files

Client material is frequently confidential and often binary. The default is:

- **Commit** text, notes, and small images — provenance should travel with the repo.
- **Git-ignore** large or sensitive binaries, and leave a **stub** in their place naming the
  file and where it actually lives (see `.gitignore`).

A one-line `contract-scan.pdf.txt` reading *"signed service contract, on the client's shared
drive, folder Legal/2026"* is worth far more than a silent absence — a missing file that nothing
points at is indistinguishable from a file that never existed.
